<?php

namespace Database\Seeders;

use App\Models\Project;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class ProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing projects to avoid conflicts with file uploads
        Project::truncate();

        // Create a few test projects without files for testing
        Project::create([
            'title' => 'Sample Infrastructure Project',
            'description' => 'A sample project for testing purposes',
            'category' => 'Infrastructure',
            'status' => 'planning',
            'start_date' => now(),
            'target_completion' => now()->addMonths(6),
            'funding_source' => 'Government',
            'barangay_zone' => 'Zone 1',
            'completion_percentage' => 0,
            'project_lead' => 'John Doe',
            'assigned_organizations' => ['Local Government', 'Community Group'],
            'number_of_members' => 5,
            'site_address' => '123 Main Street',
            'files' => [], // Empty array for files
        ]);

        Project::create([
            'title' => 'Sample Education Project',
            'description' => 'Another sample project for testing',
            'category' => 'Education',
            'status' => 'in_progress',
            'start_date' => now()->subMonth(),
            'target_completion' => now()->addMonths(3),
            'funding_source' => 'Private',
            'barangay_zone' => 'Zone 2',
            'completion_percentage' => 25,
            'project_lead' => 'Jane Smith',
            'assigned_organizations' => ['School Board'],
            'number_of_members' => 8,
            'site_address' => '456 School Avenue',
            'files' => [], // Empty array for files
        ]);
    }
}
