<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->string('certificate_number')->unique();
            $table->foreignId('resident_id')->constrained('residents')->onDelete('cascade');

            // Extended certificate types based on your categories
            $table->enum('certificate_type', [
                // Personal / Identity / Status
                'barangay_clearance',
                'certificate_of_residency',
                'certificate_of_good_moral_character',
                'certificate_of_indigency',
                'certificate_of_solo_parent',
                'certificate_of_senior_citizen_status',
                'certificate_of_disability_pwd_status',

                // Civil / Legal / Endorsement
                'certificate_of_no_pending_case',
                'certificate_of_guardianship',
                'certificate_of_non_employment',
                'certificate_of_separation_abandonment',
                'certificate_of_barangay_endorsement',
                'certificate_of_no_objection',

                // Property / Business / Livelihood
                'certificate_of_barangay_business_operation',
                'certificate_of_house_ownership_occupancy',
                'certificate_of_land_ownership_occupancy',
                'certificate_of_livelihood_income_source',
                'certificate_of_house_transfer_relocation',

                // Employment / Assistance
                'certificate_of_employment',
                'certificate_of_barangay_support',
                'certificate_of_death_burial_assistance',
                'certificate_of_calamity_victim',
                'certificate_of_adoption_foster_care',

                // Marriage / Family / Community
                'certificate_of_marriage_cohabitation',
                'certificate_of_community_participation',
                'certificate_of_voter_registration',

                // Other / Special Purpose
                'certificate_for_scholarship_educational_assistance',
                'certificate_for_travel_abroad_local_travel',
                'certificate_for_business_permit_application',

                // Legacy types from original migration
                'business_clearance',
                'travel_permit',
                'death_certificate',
                'birth_certificate'
            ]);

            $table->enum('status', ['pending', 'approved', 'released', 'cancelled'])->default('pending');
            $table->text('purpose')->nullable();
            $table->text('additional_details')->nullable();
            $table->decimal('fee_amount', 8, 2)->default(0);
            $table->enum('payment_status', ['unpaid', 'paid', 'waived'])->default('unpaid');
            $table->date('date_issued')->nullable();
            $table->date('date_released')->nullable();
            $table->string('issued_by')->nullable(); // Official who issued
            $table->string('released_by')->nullable(); // Official who released
            $table->text('remarks')->nullable();
            $table->json('certificate_data')->nullable(); // Store template-specific data

            // Additional fields for better tracking
            $table->string('requested_by')->nullable(); // Who requested the certificate
            $table->date('valid_until')->nullable(); // Validity period for certain certificates
            $table->boolean('requires_purpose')->default(true); // Flag to indicate if purpose is required
            $table->string('category')->nullable(); // Certificate category for grouping

            $table->timestamps();

            // Indexes for better performance
            $table->index(['resident_id', 'certificate_type']);
            $table->index(['status', 'date_issued']);
            $table->index('certificate_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
