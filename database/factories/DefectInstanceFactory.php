<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DefectInstance>
 */
class DefectInstanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tag' => $this->faker->unique()->bothify('DEF-####'),
            'defect_id' => \App\Models\Defect::factory(),
        ];
    }
}
