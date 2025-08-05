<?php

use App\Models\Complaint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('complaints', function (Blueprint $table) {
            $table->id();
            $table->string('complainant_name');
            $table->string('respondent_name');
            $table->string('case_no');
            $table->string('title');
            $table->longText('description');
            $table->longText('resolution');
            $table->dateTime('filing_date');
            $table->string('status')->default('N/A');
            $table->foreignId('respondent_id')->constrained('residents')->nullOnDelete();
            $table->foreignId('complainant_id')->constrained('residents')->nullOnDelete();
            $table->string('nature_of_complaint')->nullable();
            $table->dateTime('incident_datetime')->nullable();
            $table->string('incident_location')->nullable();
            $table->json('supporting_documents')->nullable()->change();
            $table->string('witness')->nullable();
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
