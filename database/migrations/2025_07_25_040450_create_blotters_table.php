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
            $table->string('complainant_type');
            $table->string('complainant_id');
            $table->string('respondent_type');
            $table->string('respondent_id');
            $table->string('place');
            $table->date('datetime_of_incident');
            $table->longText('blotter_type');
            $table->string('barangay_case_no');
            $table->string('total_cases');
            $table->string('status')->default('Open');
            $table->longText('description');
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
