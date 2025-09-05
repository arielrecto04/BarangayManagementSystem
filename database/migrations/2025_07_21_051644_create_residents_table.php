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
        Schema::create('residents', function (Blueprint $table) {
            $table->id();

            // 🧑 Basic Information
            $table->string('avatar')->default('https://ionicframework.com/docs/img/demos/avatar.svg');
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->enum('suffix', ['Jr', 'Sr', 'III', 'IV', 'V'])->nullable();
            $table->string('resident_number')->unique();
            $table->date('birthday')->nullable();
            $table->integer('age');
            $table->enum('gender', ['Male', 'Female']); // No default

            // 🏠 Address (divided into parts)
            $table->string('house_no')->nullable();
            $table->string('street')->nullable();
            $table->string('barangay')->nullable();
            $table->string('city')->nullable();
            $table->string('province')->nullable();
            $table->string('zipcode')->nullable();

            $table->string('email')->unique()->nullable();
            $table->string('contact_number')->nullable();
            $table->string('family_member')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('contact_person')->nullable();

            // 🏠 Demographic & Residency Details
            $table->string('place_of_birth')->nullable();
            $table->enum('civil_status', [
                'Single',
                'Married',
                'Widowed',
                'Divorced',
                'Separated',
                'Common Law'
            ])->default('Single');
            $table->string('citizenship')->default('Filipino');
            $table->string('religion')->nullable();
            $table->integer('years_of_residency')->nullable()->default(0);
            $table->enum('voter_status', [
                'Registered',
                'Not Registered',
                'Suspended',
                'Transferred'
            ])->default('Not Registered');
            $table->string('voter_precinct_number')->nullable();

            // 📄 Government & ID References
            $table->enum('valid_id_type', [
                'PhilHealth ID',
                'UMID',
                'Drivers License',
                'Passport',
                'Postal ID',
                'Voters ID',
                'TIN ID',
                'Senior Citizen ID',
                'PWD ID',
                'Barangay ID',
                'Student ID',
                'Company ID',
                'Other'
            ])->nullable();
            $table->string('valid_id_number')->nullable();
            $table->string('sss_number')->nullable();
            $table->string('philhealth_number')->nullable();
            $table->string('tin_number')->nullable();
            $table->string('pagibig_number')->nullable();

            // 🧑‍💼 Employment & Education
            $table->string('occupation')->nullable()->default('N/A');
            $table->enum('educational_attainment', [
                'No Formal Education',
                'Elementary Level',
                'Elementary Graduate',
                'High School Level',
                'High School Graduate',
                'Senior High School Graduate',
                'Vocational Graduate',
                'College Level',
                'College Graduate',
                'Post Graduate'
            ])->nullable()->default('High School Graduate');
            $table->decimal('monthly_income', 10, 2)->nullable()->default(0);
            $table->string('employer_name')->nullable();
            $table->string('employer_address')->nullable();

            // 🧾 Barangay-Specific Flags
            $table->boolean('is_pwd')->default(false);
            $table->string('pwd_id_number')->nullable();
            $table->string('disability_type')->nullable();
            $table->boolean('is_senior_citizen')->default(false);
            $table->string('senior_citizen_id_number')->nullable();
            $table->boolean('is_4ps_beneficiary')->default(false);
            $table->string('4ps_household_id')->nullable();
            $table->boolean('is_registered_voter')->default(false);
            $table->boolean('is_solo_parent')->default(false);
            $table->string('solo_parent_id_number')->nullable();
            $table->boolean('is_indigenous')->default(false);
            $table->string('indigenous_group')->nullable();

            // Additional Barangay Flags
            $table->boolean('is_ofw')->default(false);
            $table->string('ofw_country')->nullable();
            $table->boolean('is_teen_parent')->default(false);
            $table->boolean('is_lactating_mother')->default(false);
            $table->boolean('is_pregnant')->default(false);

            // 📌 Status and Notes
            $table->enum('resident_status', [
                'Active',
                'Inactive',
                'Deceased',
                'Transferred',
                'Temporary'
            ])->default('Active');
            $table->text('medical_conditions')->nullable();
            $table->text('allergies')->nullable();
            $table->text('notes')->nullable();
            $table->date('date_registered')->nullable();
            $table->string('registered_by')->nullable();

            $table->timestamps();

            // 📊 Indexes
            $table->index(['last_name', 'first_name']);
            $table->index('resident_status');
            $table->index('civil_status');
            $table->index('voter_status');
            $table->index(['is_pwd', 'is_senior_citizen', 'is_4ps_beneficiary']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('residents');
    }
};
