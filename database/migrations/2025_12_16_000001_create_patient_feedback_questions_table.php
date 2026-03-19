<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('patient_feedback_questions')) {
            return;
        }

        Schema::create('patient_feedback_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question_text');
            $table->enum('cqc_domain', ['safe', 'effective', 'caring', 'responsive', 'well_led']);
            $table->boolean('is_enabled')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['is_enabled', 'sort_order']);
            $table->index('cqc_domain');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_feedback_questions');
    }
};


