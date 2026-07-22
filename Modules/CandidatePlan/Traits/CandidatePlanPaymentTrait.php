<?php

namespace Modules\CandidatePlan\Traits;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Modules\CandidatePlan\Entities\CandidatePlan;
use Modules\CandidatePlan\Entities\CandidateUserPlan;

trait CandidatePlanPaymentTrait
{
    /**
     * Assign a plan to a user and record the transaction.
     */
    public function assignCandidatePlan(User $user, CandidatePlan $plan, $paymentMethod, $transactionId = null)
    {
        DB::beginTransaction();
        try {
            // Create or update user plan in candidate_user_plans table

            // Record transaction
            DB::table('candidate_plan_transactions')->insert([
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'payment_method' => $paymentMethod,
                'transaction_id' => $transactionId,
                'amount' => $plan->price,
                'payment_status' => 'Paid',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Optionally send notification
            // Notification::send($user, new PlanPurchasedNotification($plan));

            $userPlan = CandidateUserPlan::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'plan_id' => $plan->id,
                    'job_apply_limit' => $plan->job_apply_limit,
                    'expire_date' => now()->addDays($plan->duration ?? 30),
                    'is_active' => true,
                ]
            );

            DB::commit();

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Candidate plan payment error: '.$e->getMessage());

            return false;
        }
    }
}
