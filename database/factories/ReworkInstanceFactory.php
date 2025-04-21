<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ReworkInstance>
 */
class ReworkInstanceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tag' => $this->faker->unique()->bothify('REW-####'),
            // rework_id e inspection_lot_id se asignan en el seeder para mantener la relación
        ];
    }
}
