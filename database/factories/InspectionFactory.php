<?php

namespace Database\Factories;

use App\Models\Inspection;
use App\Models\Plant;
use App\Models\Product;
use App\Models\SalesAgent;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Inspection>
 */
class InspectionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'submit_date' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'description' => $this->faker->sentence(),
            'start_date' => $this->faker->optional()->dateTimeBetween('-2 years', 'now')?->format('Y-m-d'),
            'complete_date' => $this->faker->optional()->dateTimeBetween('-2 years', 'now')?->format('Y-m-d'),
            'status' => $this->faker->randomElement([
                Inspection::PENDING_STATUS,
                Inspection::ACTIVE_STATUS,
                Inspection::ON_HOLD_STATUS,
                Inspection::UNDER_REVIEW_STATUS,
                Inspection::COMPLETED_STATUS,
            ]),
            'plant_id' => Plant::factory(),
            'product_id' => Product::factory(),
            'group_leader_id' => User::factory()->groupLeader()
        ];
    }
}
