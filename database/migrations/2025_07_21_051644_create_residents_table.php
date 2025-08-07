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
            $table->string('avatar')->default('https://ionicframework.com/docs/img/demos/avatar.svg');

            // REQUIRED fields based on controller validation
            $table->string('first_name');
            $table->string('last_name');
            $table->string('birthday');
            $table->integer('age');
            $table->string('gender'); // No longer nullable
            $table->string('address', 500); // match max:500
            $table->string('contact_number'); // required in controller
            $table->string('resident_number')->unique();

            // OPTIONAL fields
            $table->string('middle_name')->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('family_member')->nullable();
            $table->string('emergency_contact')->nullable();
            $table->string('contact_person')->nullable();
            $table->foreignId('household_id')->nullable()->after('id')->constrained('households')->onDelete('set null');

            $table->timestamps();
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
