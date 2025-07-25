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
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complainant_name')->required();
            $table->string('respondent_name')->required();
            $table->string('case_no');
            $table->string('title');
            $table->longText('description');
            $table->longText('resolution');
            $table->string('date');
            $table->string('filing_date');
            $table->string('respondent_id')->nullable();
            $table->string('complainant_id')->required();
            

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('complaints');
    }
};
