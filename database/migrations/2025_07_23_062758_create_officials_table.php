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
        Schema::create('officials', function (Blueprint $table) {
            $table->id();
            $table->string('name')->required();
            $table->string('position')->required();
            $table->longtext('description')->required();
            $table->string('terms')->required();
            $table->string('no_of_per_term')->required();
            $table->string('elected_date')->required();
            $table->string('end_date')->required();
            $table->string('resident_id')->required();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('officials');
    }
};
