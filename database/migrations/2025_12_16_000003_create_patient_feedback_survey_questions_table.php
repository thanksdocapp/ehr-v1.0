<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_feedback_survey_questions', function (Blueprint $table) {
            $table->id();

            $table->foreignId('survey_id')->constrained('patient_feedback_surveys')->cascadeOnDelete();

            // Reference back to the master question (optional, since we snapshot text)
            $table->foreignId('question_id')->nullable()->constrained('patient_feedback_questions')->nullOnDelete();

            $table->text('question_text');
            $table->enum('cqc_domain', ['safe', 'effective', 'caring', 'responsive', 'well_led']);
            $table->unsignedInteger('sort_order')->default(0);

            $table->timestamps();

            $table->index(['survey_id', 'sort_order']);
            $table->index('cqc_domain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_feedback_survey_questions');
    }
};


