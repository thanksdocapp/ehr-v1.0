<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_feedback_responses', function (Blueprint $table) {
            $table->id();

            $table->foreignId('survey_id')->constrained('patient_feedback_surveys')->cascadeOnDelete();
            $table->foreignId('survey_question_id')->constrained('patient_feedback_survey_questions')->cascadeOnDelete();

            // Likert 1–5
            $table->unsignedTinyInteger('score');

            $table->timestamps();

            $table->unique(['survey_id', 'survey_question_id']);
            $table->index(['survey_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_feedback_responses');
    }
};


