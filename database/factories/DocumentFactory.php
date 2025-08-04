<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'file_name' => fake()->word(),
            'file_sizes' => fake()->randomNumber(5, true),
            'uploaded_by' => User::factory()->create()->id,
            'file_path' => fake()->url(),
            'file_type' => fake()->randomElement(['pdf', 'docx', 'xlsx', 'pptx', 'txt', 'zip', 'rar', '7z', 'iso', 'img']),
        ];
    }
}
