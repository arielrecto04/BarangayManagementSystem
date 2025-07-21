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
            $table->string('First_Name')->required();
            $table->string('Last_Name')->required();
            $table->string('Birthday')->required();
            $table->string('Age')->required();
            $table->string('Gender')->required();
            $table->string('Address')->required();
            $table->string('Contact_Number')->required();
            $table->string('Family_Member')->nullable();
            $table->string('Emergency_Contact')->required;

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
