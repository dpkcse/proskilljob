<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CandidateReference extends Model
{
    use HasFactory;

    protected $fillable = [
        'candidate_id',
        'name',
        'designation',
        'organization',
        'email',
        'relation',
        'mobile',
        'phone_off',
        'phone_res',
        'address',
    ];
}