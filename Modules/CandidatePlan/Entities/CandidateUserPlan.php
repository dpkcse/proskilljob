<?php

namespace Modules\CandidatePlan\Entities;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CandidateUserPlan extends Model
{
    protected $fillable = [
        'user_id',
        'plan_id',
        'job_apply_limit',
        'expire_date',
        'is_active',
    ];

    protected $casts = [
        'expire_date' => 'datetime',
        'is_active' => 'boolean',
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
