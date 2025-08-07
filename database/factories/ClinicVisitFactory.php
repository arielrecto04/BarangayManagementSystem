<?php

namespace Database\Factories;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClinicVisit>
 */
class ClinicVisitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // A list of common reasons and diagnoses for more realistic data
        $reasons = ['Fever and cough', 'Follow-up checkup', 'Minor wound', 'Stomach ache', 'Headache', 'Prenatal Checkup'];
        $diagnoses = ['Common Cold', 'Hypertension', 'Abrasion', 'Gastritis', 'Migraine', 'Normal Pregnancy'];

        return [
            // This assumes you have already seeded your 'residents' and 'users' tables.
            'resident_id' => Resident::inRandomOrder()->first()->id,
            'health_worker_id' => User::inRandomOrder()->first()->id,

            'visit_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'reason_for_visit' => fake()->randomElement($reasons),
            'diagnosis' => fake()->randomElement($diagnoses),
            'treatment_notes' => fake()->sentence(),
            'prescription' => 'Paracetamol 500mg',
        ];
    }
}
