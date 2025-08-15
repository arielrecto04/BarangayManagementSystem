<?php

namespace Database\Factories;

use App\Models\Resident;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\VaccinationRecord>
 */
class VaccinationRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // List of common vaccines in the Philippines
        $vaccines = ['BCG', 'Hepatitis B', 'Pentavalent Vaccine', 'Oral Polio Vaccine', 'MMR', 'COVID-19 Booster'];
        $healthWorkers = ['BHW Maria Santos', 'BHW Pedro Reyes', 'Nurse Ana Garcia'];

        return [
            // This assumes your 'residents' table is already populated
            'resident_id' => Resident::inRandomOrder()->first()->id,
            'vaccine_name' => fake()->randomElement($vaccines),
            'dose' => fake()->randomElement(['1 of 3', '2 of 3', '3 of 3', 'Booster', 'Annual']),
            'date_administered' => fake()->dateTimeBetween('-3 years', 'now'),
            'administered_by' => fake()->randomElement($healthWorkers),
            'remarks' => fake()->optional()->sentence(),
        ];
    }
}
