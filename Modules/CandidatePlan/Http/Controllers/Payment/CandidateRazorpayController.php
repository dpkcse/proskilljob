<?php

namespace Modules\CandidatePlan\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\CandidatePlan\Entities\CandidatePlan;
use Modules\CandidatePlan\Traits\CandidatePlanPaymentTrait;
use Razorpay\Api\Api;

class CandidateRazorpayController extends Controller
{
    use CandidatePlanPaymentTrait;

    /**
     * Process Razorpay payment for candidate plan.
     */
    public function processTransaction(Request $request)
    {
        try {
            $plan = CandidatePlan::findOrFail($request->plan_id);
            $user = Auth::guard('user')->user();

            if (! $user) {
                return redirect()->route('login')->with('error', __('You must be logged in to complete the purchase.'));
            }

            // Handle free plans
            if ($plan->price == 0) {
                return $this->processFreePlan($request);
            }

            // Amount conversion
            $amount = currencyConversion($plan->price, 'INR', 1);
            $converted_amount = usdAmount($plan->price);

            // Store payment info in session
            session(['candidate_plan_payment' => [
                'plan_id' => $plan->id,
                'payment_provider' => 'razorpay',
                'amount' => $amount,
                'currency_symbol' => '₹',
                'usd_amount' => $converted_amount,
            ]]);

            // Create Razorpay order
            $api = new Api(config('templatecookie.razorpay_key'), config('templatecookie.razorpay_secret'));

            $order = $api->order->create([
                'amount' => $amount * 100, // Amount in paise
                'currency' => 'INR',
                'receipt' => 'candidate_plan_'.$plan->id.'_'.time(),
                'notes' => [
                    'plan_id' => $plan->id,
                    'user_id' => $user->id,
                    'plan_name' => $plan->name,
                ],
            ]);

            return response()->json([
                'success' => true,
                'order_id' => $order['id'],
                'amount' => $amount * 100,
                'currency' => 'INR',
                'key' => config('templatecookie.razorpay_key'),
                'name' => config('app.name'),
                'description' => 'Payment for '.$plan->name.' plan',
                'prefill' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'contact' => $user->phone ?? '',
                ],
                'theme' => [
                    'color' => '#3399cc',
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Razorpay order creation error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment order. Please try again.',
            ], 500);
        }
    }

    /**
     * Handle Razorpay payment success callback.
     */
    public function successTransaction(Request $request)
    {
        try {
            $input = $request->all();
            $api = new Api(config('templatecookie.razorpay_key'), config('templatecookie.razorpay_secret'));

            if (count($input) && ! empty($input['razorpay_payment_id'])) {
                $payment = $api->payment->fetch($input['razorpay_payment_id']);

                if ($payment['status'] === 'captured') {
                    $session = session('candidate_plan_payment');

                    // Validate session
                    if (! $session || ! isset($session['plan_id'])) {
                        return redirect()->route('candidate.plan')->with('error', __('Session expired. Please try again.'));
                    }

                    $plan = CandidatePlan::findOrFail($session['plan_id']);
                    $user = Auth::guard('user')->user();

                    if (! $user) {
                        return redirect()->route('login')->with('error', __('You must be logged in to complete the purchase.'));
                    }

                    if ($this->assignCandidatePlan($user, $plan, 'razorpay', $input['razorpay_payment_id'])) {
                        // Clear session
                        session()->forget('candidate_plan_payment');

                        return redirect()->route('candidate.my.plan')
                            ->with('success', __('Plan purchased successfully.'));
                    }

                    return back()->with('error', __('Failed to assign plan. Please contact support.'));
                }
            }

            return back()->with('error', __('Payment verification failed.'));
        } catch (\Exception $e) {
            Log::error('Razorpay success transaction error: '.$e->getMessage());

            return back()->with('error', __('An error occurred while processing your payment.'));
        }
    }

    /**
     * Handle Razorpay payment failure callback.
     */
    public function failureTransaction(Request $request)
    {
        Log::info('Razorpay payment failed', $request->all());

        return redirect()->route('candidate.plan')->with('error', __('Payment failed. Please try again.'));
    }

    /**
     * Process free plan purchase.
     */
    private function processFreePlan(Request $request)
    {
        try {
            $plan = CandidatePlan::findOrFail($request->plan_id);
            $user = Auth::guard('user')->user();

            if (! $user) {
                return redirect()->route('login')->with('error', __('You must be logged in to complete the purchase.'));
            }

            if ($this->assignCandidatePlan($user, $plan, 'free', 'free_'.time())) {
                return redirect()->route('candidate.my.plan')
                    ->with('success', __('Free plan activated successfully.'));
            }

            return back()->with('error', __('Failed to activate free plan. Please contact support.'));
        } catch (\Exception $e) {
            Log::error('Free plan activation error: '.$e->getMessage());

            return back()->with('error', __('An error occurred while activating the plan.'));
        }
    }
}
