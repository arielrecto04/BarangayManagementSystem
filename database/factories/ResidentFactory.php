<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Resident>
 */
class ResidentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'email' => fake()->unique()->safeEmail(),
            'resident_number' => 'RES-' . fake()->unique()->numberBetween(1, 1000000),
            'birthday' => fake()->dateTimeBetween('-60 years', '-18 years'),
            'age' => fake()->numberBetween(18, 60),
            'gender' => fake()->randomElement(['Male', 'Female']),
            'address' => fake()->address(),
            'contact_number' => fake()->phoneNumber(),
            'family_member' => fake()->numberBetween(1, 10),
            'emergency_contact' => fake()->phoneNumber(),
        ];
    }
}
