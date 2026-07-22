<?php

namespace Modules\CandidatePlan\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\CandidatePlan\Entities\CandidatePlan;
use Modules\CandidatePlan\Traits\CandidatePlanPaymentTrait;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class CandidatePayPalController extends Controller
{
    use CandidatePlanPaymentTrait;

    /**
     * Check if user has used a free plan
     */
    private function hasUsedFreePlan($userId)
    {
        return DB::table('candidate_plan_transactions')
            ->where('user_id', $userId)
            ->where('amount', 0)
            ->where('payment_method', 'free')
            ->exists();
    }

    /**
     * Process free plan purchase with invoice generation.
     */
    public function processFreePlan(Request $request)
    {
        try {
            $request->validate([
                'plan_id' => 'required|exists:candidate_plans,id',
            ]);

            $plan = CandidatePlan::findOrFail($request->plan_id);
            $user = Auth::guard('user')->user();

            if (! $user) {
                return redirect()->route('login')->with('error', __('You must be logged in to get a free plan.'));
            }

            // Check if plan is actually free
            if ($plan->price > 0) {
                return back()->with('error', __('This plan is not free.'));
            }

            // Check if user has already used a free plan
            if ($this->hasUsedFreePlan($user->id)) {
                return back()->with('error', __('You have already used your free plan.'));
            }

            // Check if user already has an active plan
            if ($user->candidatePlan && $user->candidatePlan->is_active) {
                return back()->with('error', __('You already have an active plan.'));
            }

            DB::beginTransaction();
            try {
                // Create or update user plan in candidate_user_plans table

                // Create transaction record
                $transactionId = 'FREE-'.strtoupper(uniqid());
                DB::table('candidate_plan_transactions')->insert([
                    'user_id' => $user->id,
                    'plan_id' => $plan->id,
                    'payment_method' => 'free',
                    'transaction_id' => $transactionId,
                    'amount' => 0,
                    'payment_status' => 'Paid',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                // Create or update user plan
                DB::table('candidate_user_plans')->updateOrInsert(
                    ['user_id' => $user->id],
                    [
                        'plan_id' => $plan->id,
                        'job_apply_limit' => $plan->job_apply_limit,
                        'expire_date' => now()->addDays($plan->duration ?? 30),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );

                DB::commit();

                return redirect()->route('candidate.plan.details', $plan->id)
                    ->with('success', __('Free plan activated successfully. Transaction ID: ').$transactionId);

            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Free plan activation error: '.$e->getMessage());

                return back()->with('error', __('Failed to activate free plan. Please try again.'));
            }

        } catch (\Exception $e) {
            Log::error('Free plan processing error: '.$e->getMessage());

            return back()->with('error', __('An error occurred while processing your free plan.'));
        }
    }

    /**
     * Start PayPal transaction for candidate plan.
     */
    public function processTransaction(Request $request)
    {
        try {
            $plan = CandidatePlan::findOrFail($request->plan_id);

            // Handle free plans
            if ($plan->price == 0) {
                return $this->processFreePlan($request);
            }

            $converted_amount = currencyConversion($plan->price);

            // Store payment info in session
            session(['candidate_plan_payment' => [
                'plan_id' => $plan->id,
                'payment_provider' => 'paypal',
                'amount' => $converted_amount,
                'currency_symbol' => '$',
                'usd_amount' => $converted_amount,
            ]]);

            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $paypalToken = $provider->getAccessToken();

            if (! $paypalToken) {
                return back()->with('error', 'Failed to authenticate with PayPal. Please try again.');
            }

            $response = $provider->createOrder([
                'intent' => 'CAPTURE',
                'application_context' => [
                    'return_url' => route('candidate.paypal.success'),
                    'cancel_url' => route('candidate.paypal.cancel'),
                ],
                'purchase_units' => [
                    0 => [
                        'amount' => [
                            'currency_code' => 'USD',
                            'value' => number_format($converted_amount, 2, '.', ''),
                        ],
                        'description' => 'Payment for '.$plan->name.' plan',
                    ],
                ],
            ]);

            if (isset($response['id']) && $response['id'] != null) {
                foreach ($response['links'] as $links) {
                    if ($links['rel'] == 'approve') {
                        return redirect()->away($links['href']);
                    }
                }

                return back()->with('error', 'Unable to find the approval link. Please try again later.');
            } else {
                return back()->with('error', 'Failed to initiate the PayPal transaction. Please try again.');
            }
        } catch (\Exception $e) {
            Log::error('PayPal transaction error: '.$e->getMessage());

            return back()->with('error', 'An error occurred while processing your payment. Please try again.');
        }
    }

    /**
     * Handle PayPal success callback.
     */
    public function successTransaction(Request $request)
    {
        try {
            $provider = new PayPalClient;
            $provider->setApiCredentials(config('paypal'));
            $provider->getAccessToken();
            $response = $provider->capturePaymentOrder($request['token']);

            if (isset($response['status']) && $response['status'] == 'COMPLETED') {
                $session = session('candidate_plan_payment');

                // 💡 Validate session
                if (! $session || ! isset($session['plan_id'])) {
                    return redirect()->route('candidate.plan')->with('error', __('Session expired. Please try again.'));
                }

                $plan = CandidatePlan::findOrFail($session['plan_id']);

                // ✅ Make sure user is authenticated
                $user = Auth::guard('user')->user();
                if (! $user) {
                    return redirect()->route('login')->with('error', __('You must be logged in to complete the purchase.'));
                }

                if ($this->assignCandidatePlan($user, $plan, 'paypal', $response['id'] ?? null)) {
                    return redirect()->route('candidate.plan.details', $plan->id)
                        ->with('success', __('Plan purchased successfully.'));
                }

                return back()->with('error', __('Failed to assign plan. Please contact support.'));
            }

            return back()->with('error', __('payment_was_failed'));
        } catch (\Exception $e) {
            Log::error('PayPal success transaction error: '.$e->getMessage());

            return back()->with('error', __('An error occurred while processing your payment.'));
        }
    }

    /**
     * Handle PayPal cancel callback.
     */
    public function cancelTransaction(Request $request)
    {
        Log::info('PayPal payment cancelled by user');

        return back()->with('error', __('payment_was_cancelled'));
    }
}
