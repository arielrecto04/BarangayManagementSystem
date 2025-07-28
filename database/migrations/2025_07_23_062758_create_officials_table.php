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
            $table->integer('no_of_per_term')->required();
            $table->date('elected_date')->required();
            $table->date('start_date')->required();
            $table->date('end_date')->required();
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
