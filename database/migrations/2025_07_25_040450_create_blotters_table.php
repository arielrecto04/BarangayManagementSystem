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
            $table->dateTime('filing_date');
            $table->string('title_case');
            $table->string('nature_of_case');
            $table->string('complainants_name');
            $table->foreignId('complainants_id')->constrained('residents')->nullOnDelete();
            $table->string('respondents_name');
            $table->foreignId('respondents_id')->constrained('residents')->nullOnDelete();
            $table->string('incident_location');
            $table->dateTime('datetime_of_incident');
            $table->longText('blotter_type');
            $table->string('barangay_case_no');
            $table->string('total_cases')->nullable();
            $table->string('status')->default('Open');
            $table->longText('description');
            $table->string('witness')->nullable();
            $table->json('supporting_documents')->nullable();
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
