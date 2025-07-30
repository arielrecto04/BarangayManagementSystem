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
            $table->string('date');
            $table->string('filing_date');
            $table->foreignIdFor(Complaint::class, 'respondent_id')->constrained('respondent_id')->nullOnDelete();
            $table->foreignIdFor(Complaint::class, 'complainant_id')->constrained('complainant_id')->nullOnDelete();
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
