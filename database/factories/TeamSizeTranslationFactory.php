<?php

namespace Database\Factories;

use App\Models\TeamSize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\OrganizationTypeTranslation>
 */
class TeamSizeTranslationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'team_size_id' => TeamSize::inRandomOrder()->value('id'),
            'name' => $this->faker->word(),
            'locale' => $this->faker->locale(),
        ];
    }
}
