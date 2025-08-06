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
            $table->string('first_name');
            $table->string('email')->unique()->nullable();
            $table->string('resident_number')->unique();
            $table->string('last_name');
            $table->string('middle_name')->nullable();
            $table->string('birthday')->nullable();
            $table->integer('age')->required();
            $table->string('gender')->nullable()->default('Not Specified');
            $table->string('address')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('family_member')->nullable();
            $table->string('emergency_contact')->nullable();
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
