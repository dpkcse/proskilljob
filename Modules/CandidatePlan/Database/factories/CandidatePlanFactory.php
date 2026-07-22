<?php

namespace Modules\CandidatePlan\Database\factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\CandidatePlan\Entities\CandidatePlan;

class CandidatePlanFactory extends Factory
{
    protected $model = CandidatePlan::class;

    public function definition()
    {
        return [
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'price' => $this->faker->randomFloat(2, 10, 1000),
            'job_apply_limit' => $this->faker->numberBetween(5, 100),
            'resume_download' => $this->faker->boolean,
            'profile_visibility' => $this->faker->boolean,
            'featured_profile' => $this->faker->boolean,
            'recommended' => $this->faker->boolean,
            'is_active' => true,
        ];
    }
}
