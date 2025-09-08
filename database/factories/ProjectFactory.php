<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'description' => fake()->paragraph(3),
            'category' => fake()->randomElement(['Infrastructure', 'Health', 'Education', 'Environment']),
            'status' => fake()->randomElement(['Pending', 'Approved', 'Rejected']),
            'start_date' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'target_completion' => fake()->dateTimeBetween('+1 month', '+6 months'),
            'actual_completion' => fake()->optional()->dateTimeBetween('+1 month', '+6 months'),
            'funding_source' => fake()->company(),
            'barangay_zone' => 'Zone ' . fake()->numberBetween(1, 10),
            'completion_percentage' => fake()->numberBetween(0, 100),
            'milestone_achieved' => fake()->sentence(5),
            'files' => [], // empty array by default
            'project_lead' => fake()->name(),
            'assigned_organizations' => [], // empty array by default
            'number_of_members' => fake()->numberBetween(1, 20),
            'site_address' => fake()->address(),
            'disbursement_schedule' => fake()->paragraph(1),
            'challenges_encountered' => fake()->paragraph(1),
        ];
    }
}
