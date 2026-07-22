<?php

namespace Modules\CandidatePlan\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidatePlanTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'payment_method',
        'transaction_id',
        'amount',
        'payment_status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(CandidatePlan::class);
    }
}
