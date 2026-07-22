<?php

namespace Modules\CandidatePlan\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\CandidatePlan\Entities\CandidatePlan;
use Modules\CandidatePlan\Entities\CandidatePlanTransaction;

class CandidatePlanController extends Controller
{
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

    public function pricing()
    {
        $candidatePlans = CandidatePlan::where('is_active', true)->get();
        $current_currency = currentCurrency();

        return view('candidateplan::frontend.pricing', compact('candidatePlans', 'current_currency'));
    }

    public function purchaseFreePlan(Request $request)
    {
        try {
            $request->validate([
                'plan' => 'required|exists:candidate_plans,id',
            ]);

            $plan = CandidatePlan::findOrFail($request->plan);
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

                return redirect()->route('candidate.my.plan')
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

    public function planDetails($id)
    {
        $plan = CandidatePlan::findOrFail($id);
        $current_currency = currentCurrency();

        return view('candidateplan::frontend.plan-details', compact('plan', 'current_currency'));
    }

    public function myPlan()
    {
        $user = auth()->user();
        $userplan = $user->candidatePlan;
        $transactions = CandidatePlanTransaction::where('user_id', $user->id)
            ->with('plan')
            ->latest()
            ->paginate(10);
        $current_language_code = currentLanguage();

        return view('candidateplan::frontend.plan', compact('userplan', 'transactions', 'current_language_code'));
    }

    public function viewInvoice($id)
    {
        $transaction = CandidatePlanTransaction::with(['plan', 'user'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        $setting = setting();

        return view('candidateplan::frontend.invoice', compact('transaction', 'setting'));
    }

    public function downloadInvoice($id)
    {
        $transaction = CandidatePlanTransaction::with(['plan', 'user'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);
        $setting = setting();

        $pdf = PDF::loadView('candidateplan::frontend.download-invoice', compact('transaction', 'setting'));

        return $pdf->download('invoice-'.$transaction->id.'.pdf');
    }

    public function applyCoupon(Request $request)
    {
        $request->validate([
            'coupon_code' => 'required|string',
            'plan_id' => 'required|integer',
        ]);

        $coupon = Coupon::where('code', $request->coupon_code)
            ->where('status', 1)

            ->first();

        if (! $coupon) {
            return response()->json(['success' => false, 'message' => 'Invalid or expired coupon.']);
        }
        if ($coupon->max_uses && $coupon->used >= $coupon->max_uses) {
            return response()->json(['success' => false, 'message' => 'Coupon usage limit reached.']);
        }

        $plan = CandidatePlan::findOrFail($request->plan_id);
        $discount = $coupon->type == 'percent'
            ? ($plan->price * $coupon->value / 100)
            : $coupon->value;
        $discount = min($discount, $plan->price);
        $newTotal = $plan->price - $discount;

        $coupon->used++;
        $coupon->save();
        // Optionally: store coupon in session for checkout
        session(['applied_coupon' => $coupon->code, 'coupon_discount' => $discount]);

        return response()->json([
            'success' => true,
            'message' => 'Coupon applied! Discount: '.currencyPosition($discount, true),
            'discount' => $discount,
            'new_total' => currencyPosition($newTotal, true),
        ]);
    }
}
