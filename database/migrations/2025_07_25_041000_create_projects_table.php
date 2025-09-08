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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->longText('description')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->default('pending');
            $table->dateTime('start_date')->nullable();
            $table->dateTime('target_completion')->nullable();
            $table->dateTime('actual_completion')->nullable();
            $table->string('funding_source')->nullable();
            $table->string('barangay_zone')->nullable();
            $table->unsignedInteger('completion_percentage')->default(0);
            $table->text('milestone_achieved')->nullable();
            $table->json('files')->nullable(); // store multiple files as JSON
            $table->string('project_lead')->nullable();
            $table->text('assigned_organizations')->nullable(); // can store JSON or comma-separated
            $table->unsignedInteger('number_of_members')->nullable();
            $table->string('site_address')->nullable();
            $table->text('disbursement_schedule')->nullable();
            $table->text('challenges_encountered')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
