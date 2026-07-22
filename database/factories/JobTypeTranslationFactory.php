<?php

namespace Database\Factories;

use App\Models\JobType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrganizationTypeTranslation>
 */
class JobTypeTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'job_type_id' => JobType::inRandomOrder()->value('id'),
            'name' => $this->faker->word(),
            'locale' => $this->faker->locale(),
        ];
    }
}
