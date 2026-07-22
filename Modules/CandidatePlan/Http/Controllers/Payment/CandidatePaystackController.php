<?php

namespace Modules\CandidatePlan\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Modules\CandidatePlan\Entities\CandidatePlan;
use Modules\CandidatePlan\Traits\CandidatePlanPaymentTrait;

class CandidatePaystackController extends Controller
{
    use CandidatePlanPaymentTrait;

    /**
     * Process Paystack payment for candidate plan.
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

            // Amount conversion to ZAR (South African Rand - Paystack's currency)
            $amount = currencyConversion($plan->price, 'ZAR');
            $converted_amount = usdAmount($plan->price);

            // Store payment info in session
            session(['candidate_plan_payment' => [
                'plan_id' => $plan->id,
                'payment_provider' => 'paystack',
                'amount' => $amount,
                'currency_symbol' => '₦',
                'usd_amount' => $converted_amount,
            ]]);

            // Paystack payment process
            $secret_key = config('templatecookie.paystack_key');
            $curl = curl_init();
            $callback_url = route('candidate.paystack.success');

            curl_setopt_array($curl, [
                CURLOPT_URL => 'https://api.paystack.co/transaction/initialize',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => json_encode([
                    'amount' => $amount * 100, // Amount in kobo (smallest currency unit)
                    'email' => $user->email,
                    'callback_url' => $callback_url,
                    'metadata' => [
                        'plan_id' => $plan->id,
                        'user_id' => $user->id,
                        'plan_name' => $plan->name,
                    ],
                ]),
                CURLOPT_HTTPHEADER => [
                    'authorization: Bearer '.$secret_key,
                    'content-type: application/json',
                    'cache-control: no-cache',
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                Log::error('Paystack API error: '.$err);

                return redirect()->back()->with('error', __('Failed to connect to payment gateway. Please try again.'));
            }

            $tranx = json_decode($response, true);

            if (! $tranx['status']) {
                Log::error('Paystack transaction initialization failed', ['response' => $tranx]);

                return redirect()->back()->with('error', $tranx['message'] ?? __('Failed to initialize payment.'));
            }

            return redirect($tranx['data']['authorization_url']);

        } catch (\Exception $e) {
            Log::error('Paystack transaction error: '.$e->getMessage());

            return back()->with('error', __('An error occurred while processing your payment. Please try again.'));
        }
    }

    /**
     * Handle Paystack success callback.
     */
    public function successTransaction(Request $request)
    {
        try {
            // Verify the transaction with Paystack
            $reference = $request->query('reference');

            if (! $reference) {
                return redirect()->route('candidate.plan')->with('error', __('Invalid payment reference.'));
            }

            $secret_key = config('templatecookie.paystack_key');
            $curl = curl_init();

            curl_setopt_array($curl, [
                CURLOPT_URL => "https://api.paystack.co/transaction/verify/{$reference}",
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'authorization: Bearer '.$secret_key,
                    'cache-control: no-cache',
                ],
            ]);

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                Log::error('Paystack verification error: '.$err);

                return redirect()->route('candidate.plan')->with('error', __('Failed to verify payment. Please contact support.'));
            }

            $result = json_decode($response, true);

            if (isset($result['status']) && $result['status'] && $result['data']['status'] === 'success') {
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

                if ($this->assignCandidatePlan($user, $plan, 'paystack', $result['data']['reference'])) {
                    // Clear session
                    session()->forget('candidate_plan_payment');

                    return redirect()->route('candidate.plan.details', $plan->id)
                        ->with('success', __('Plan purchased successfully.'));
                }

                return back()->with('error', __('Failed to assign plan. Please contact support.'));
            }

            return redirect()->route('candidate.plan')->with('error', __('Payment verification failed.'));

        } catch (\Exception $e) {
            Log::error('Paystack success transaction error: '.$e->getMessage());

            return back()->with('error', __('An error occurred while processing your payment.'));
        }
    }

    /**
     * Handle Paystack cancel callback.
     */
    public function cancelTransaction(Request $request)
    {
        Log::info('Paystack payment cancelled by user');

        return redirect()->route('candidate.plan')->with('error', __('Payment was cancelled.'));
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
