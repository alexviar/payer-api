<?php

namespace Database\Factories;

use App\Models\Inspection;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InspectionLot>
 */
class InspectionLotFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pn' => $this->faker->bothify('PN-####'),
            'inspect_date' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'total_units' => $this->faker->numberBetween(100, 1000),
            'total_rejects' => $this->faker->numberBetween(0, 50),
            'total_reworks' => $this->faker->numberBetween(0, 30),
            'inspection_id' => Inspection::factory(),
        ];
    }
}
