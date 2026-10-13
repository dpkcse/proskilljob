<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PendingUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'role',
        'name',
        'username',
        'email',
        'company_registration_number',
        'password',
        'created_ip',
        'verification_token',
        'expires_at',
        'last_verification_sent_at',
        'verification_send_attempts',
        'verification_last_error',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'last_verification_sent_at' => 'datetime',
        'verification_send_attempts' => 'integer',
    ];

    /**
     * Check if the pending user registration has expired
     */
    public function isExpired()
    {
        return $this->expires_at && now()->isAfter($this->expires_at);
    }

    /**
     * Scope for non-expired records
     */
    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now());
    }
}
