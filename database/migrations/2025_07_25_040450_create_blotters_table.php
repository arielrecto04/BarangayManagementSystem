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
        Schema::create('blotters', function (Blueprint $table) {
            $table->id();
            $table->string('blotter_no');
            $table->date('filing_date');
            $table->string('title_case');
            $table->string('nature_of_case');
            $table->string('complainants_name');
            $table->string('complainants_id');

            $table->string('respondents_name');
            $table->string('respondents_id');
            $table->string('complainant_address');
            $table->string('respondent_address');
            $table->string('place');
            $table->date('datetime_of_incident');
            $table->longText('blotter_type');
            $table->string('barangay_case_no');
            $table->string('total_cases');
            $table->string('status')->default('Open');
            $table->longText('description');
            $table->string('witness')->nullable();
            // Store both file paths and original names
            $table->json('supporting_documents')->nullable(); // Array of objects with 'path
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blotters');
    }
};
