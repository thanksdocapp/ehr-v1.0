<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('patient_feedback_surveys', function (Blueprint $table) {
            if (!Schema::hasColumn('patient_feedback_surveys', 'additional_comments')) {
                $table->text('additional_comments')->nullable()->after('submitted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('patient_feedback_surveys', function (Blueprint $table) {
            if (Schema::hasColumn('patient_feedback_surveys', 'additional_comments')) {
                $table->dropColumn('additional_comments');
            }
        });
    }
};


