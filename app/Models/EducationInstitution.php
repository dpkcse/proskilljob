<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EducationInstitution extends Model
{
    protected $fillable = [
        'name', 'type', 'district', 'is_active', 'is_featured',
    ];
}
