<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Only seed defaults if the table is empty (admin can edit afterwards).
        $exists = DB::table('patient_feedback_questions')->exists();
        if ($exists) {
            return;
        }

        $now = now();

        $defaults = [
            // SAFE
            [
                'question_text' => 'I felt safe during my consultation.',
                'cqc_domain' => 'safe',
                'is_enabled' => true,
                'sort_order' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question_text' => 'I felt my personal information was kept private.',
                'cqc_domain' => 'safe',
                'is_enabled' => true,
                'sort_order' => 2,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // EFFECTIVE
            [
                'question_text' => 'The clinician explained things in a way I could understand.',
                'cqc_domain' => 'effective',
                'is_enabled' => true,
                'sort_order' => 3,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question_text' => 'I left knowing what to do next.',
                'cqc_domain' => 'effective',
                'is_enabled' => true,
                'sort_order' => 4,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // CARING
            [
                'question_text' => 'The clinician listened to me.',
                'cqc_domain' => 'caring',
                'is_enabled' => true,
                'sort_order' => 5,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question_text' => 'I was treated with kindness and respect.',
                'cqc_domain' => 'caring',
                'is_enabled' => true,
                'sort_order' => 6,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // RESPONSIVE
            [
                'question_text' => 'I could get help when I needed it.',
                'cqc_domain' => 'responsive',
                'is_enabled' => true,
                'sort_order' => 7,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question_text' => 'My appointment was organised in a way that worked for me.',
                'cqc_domain' => 'responsive',
                'is_enabled' => true,
                'sort_order' => 8,
                'created_at' => $now,
                'updated_at' => $now,
            ],

            // WELL-LED
            [
                'question_text' => 'I felt the service was well run and professional.',
                'cqc_domain' => 'well_led',
                'is_enabled' => true,
                'sort_order' => 9,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'question_text' => 'I would recommend this service to friends or family.',
                'cqc_domain' => 'well_led',
                'is_enabled' => true,
                'sort_order' => 10,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        DB::table('patient_feedback_questions')->insert($defaults);
    }

    public function down(): void
    {
        // Do not delete admin-modified data on rollback; keep schema rollback only.
    }
};


