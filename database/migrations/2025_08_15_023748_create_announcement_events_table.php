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
        Schema::create('announcement_events', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['announcement', 'event']); // To distinguish the type
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('location')->nullable();
            $table->string('author')->nullable();
            $table->string('image')->nullable();
            $table->enum('status', ['Upcoming', 'Ongoing', 'Past'])
                ->default('Upcoming')
                ->after('author'); // Adjust position if needed
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('announcement_events');
    }
};
