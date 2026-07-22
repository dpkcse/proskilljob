<?php

namespace Modules\CandidatePlan\Http\Controllers\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\CandidatePlan\Entities\CandidatePlan;
use Modules\CandidatePlan\Entities\CandidatePlanTransaction;
use Modules\CandidatePlan\Traits\CandidatePlanPaymentTrait;
use Stripe\Checkout\Session;
use Stripe\Stripe;

class CandidateStripeController extends Controller
{
    use CandidatePlanPaymentTrait;

    /**
     * Process Stripe payment for candidate plan.
     */
    public function processTransaction(Request $request)
    {
        try {
            $plan = CandidatePlan::findOrFail($request->plan_id);
            $user = auth()->user();

            Stripe::setApiKey(config('services.stripe.secret'));

            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'usd',
                        'product_data' => [
                            'name' => $plan->label,
                        ],
                        'unit_amount' => $plan->price * 100,
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => route('candidate.my.plan'),
                'cancel_url' => route('candidate.plan'),
            ]);

            // Create transaction record
            CandidatePlanTransaction::create([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_method' => 'stripe',
                'transaction_id' => $session->id,
                'amount' => $plan->price,
                'payment_status' => 'pending',
            ]);

            return redirect($session->url);
        } catch (\Exception $e) {
            return redirect()->route('candidate.plan')->with('error', $e->getMessage());
        }
    }
}
