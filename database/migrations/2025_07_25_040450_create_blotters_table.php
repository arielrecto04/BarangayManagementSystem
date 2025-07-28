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
            $table->string('blotter_no')->required();
            $table->string('filing_date')->required();
            $table->string('title_case')->required();
            $table->string('nature_of_case')->required();
            $table->morphs('complainants');
            $table->morphs('respondents');
            $table->string('place')->required();
            $table->string('datetime_of_incident')->required();
            $table->longText('blotter_type')->required();
            $table->string('barangay_case_no')->required();
            $table->string('total_cases')->required();
            $table->string('open_cases')->required();
            $table->string('in_progress')->required();
            $table->string('resolved')->required();

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
