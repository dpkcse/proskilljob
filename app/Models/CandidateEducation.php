<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CandidateEducation extends Model
{
    protected $table = 'candidate_education';

    protected $guarded = [];

    public function skills()
    {
        return $this->belongsToMany(
            \App\Models\Skill::class,
            'candidate_education_skill',
            'candidate_education_id',
            'skill_id'
        );
    }
}
