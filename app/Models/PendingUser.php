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
        'password',
        'created_ip',
        'verification_token',
        'expires_at',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
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
