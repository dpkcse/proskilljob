<?php

namespace Modules\CandidatePlan\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidatePlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'job_apply_limit',
        'recommended',
        'is_active',
    ];

    protected $casts = [
        'recommended' => 'boolean',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected static function newFactory()
    {
        return \Modules\CandidatePlan\Database\factories\CandidatePlanFactory::new();
    }
}
