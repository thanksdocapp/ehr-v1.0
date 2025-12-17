<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\PatientFeedbackQuestion;
use App\Models\PatientFeedbackSurvey;
use App\Models\PatientFeedbackSurveyQuestion;
use App\Models\PatientFeedbackResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class PatientFeedbackService
{
    /**
     * Return average rating stats for one or more doctors based on submitted surveys.
     *
     * - Excludes "Not Applicable" (score=0)
     * - Count is the number of submitted feedback forms (surveys), not number of question responses
     * - Average is the average of each survey's average score (excluding N/A), so each feedback form counts once
     * - Includes anonymous + identified (anonymous hides patient identity, not ratings)
     *
     * @param array<int,int|string> $doctorIds
     * @return array<int,array{avg: float|null, count: int}>
     */
    public function getDoctorRatingStats(array $doctorIds): array
    {
        $doctorIds = array_values(array_filter(array_map(static function ($v) {
            return is_numeric($v) ? (int) $v : null;
        }, $doctorIds)));

        if (empty($doctorIds)) {
            return [];
        }

        // 1) Compute an average score per survey (excluding N/A), then
        // 2) Aggregate per doctor across surveys so each feedback form counts once.
        $surveyAverages = DB::table('patient_feedback_responses as r')
            ->join('patient_feedback_surveys as s', 's.id', '=', 'r.survey_id')
            ->whereIn('s.doctor_id', $doctorIds)
            ->whereNotNull('s.submitted_at')
            ->where('r.score', '>', 0)
            ->groupBy('s.doctor_id', 'r.survey_id')
            ->select([
                's.doctor_id as doctor_id',
                'r.survey_id as survey_id',
                DB::raw('AVG(r.score) as survey_avg_score'),
            ]);

        $rows = DB::query()
            ->fromSub($surveyAverages, 'x')
            ->groupBy('x.doctor_id')
            ->select([
                'x.doctor_id as doctor_id',
                DB::raw('AVG(x.survey_avg_score) as avg_score'),
                DB::raw('COUNT(*) as ratings_count'),
            ])
            ->get();

        $out = [];
        foreach ($rows as $row) {
            $did = (int) $row->doctor_id;
            $avg = $row->avg_score !== null ? (float) $row->avg_score : null;
            $count = (int) ($row->ratings_count ?? 0);
            $out[$did] = [
                'avg' => $avg,
                'count' => $count,
            ];
        }

        return $out;
    }

    /**
     * Create (or fetch existing) feedback survey for an appointment.
     * Snapshots up to 10 enabled questions into patient_feedback_survey_questions.
     */
    public function createSurveyForAppointment(Appointment $appointment): ?PatientFeedbackSurvey
    {
        // Only for completed appointments
        if ($appointment->status !== 'completed') {
            return null;
        }

        // Must have a patient email to send later (survey can still exist)
        if (!$appointment->patient) {
            return null;
        }

        return DB::transaction(function () use ($appointment) {
            $existing = PatientFeedbackSurvey::where('appointment_id', $appointment->id)->first();
            if ($existing) {
                $this->ensureSurveyHasQuestionSnapshots($existing);
                return $existing;
            }

            $plainToken = Str::random(64);
            $tokenHash = hash('sha256', $plainToken);

            $survey = PatientFeedbackSurvey::create([
                'appointment_id' => $appointment->id,
                'doctor_id' => $appointment->doctor_id,
                'patient_id' => $appointment->patient_id,
                'token_hash' => $tokenHash,
                'token_encrypted' => Crypt::encryptString($plainToken),
                'is_anonymous' => false,
                'meta' => [
                    'appointment_type' => $appointment->type,
                    'appointment_date' => optional($appointment->appointment_date)->format('Y-m-d'),
                ],
            ]);

            // Snapshot enabled questions (max 10)
            $questions = PatientFeedbackQuestion::enabled()->ordered()->limit(10)->get();

            $snapshots = [];
            foreach ($questions as $q) {
                $snapshots[] = [
                    'survey_id' => $survey->id,
                    'question_id' => $q->id,
                    'question_text' => $q->question_text,
                    'cqc_domain' => $q->cqc_domain,
                    'sort_order' => $q->sort_order,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($snapshots)) {
                PatientFeedbackSurveyQuestion::insert($snapshots);
            }

            return $survey;
        });
    }

    /**
     * If a survey exists (e.g., created earlier) but has no snapshots, create them
     * from the current enabled questions. This still satisfies "future only" because
     * the snapshots are created at the point of first survey creation/use.
     */
    public function ensureSurveyHasQuestionSnapshots(PatientFeedbackSurvey $survey): void
    {
        if ($survey->questions()->exists()) {
            return;
        }

        $questions = PatientFeedbackQuestion::enabled()->ordered()->limit(10)->get();
        $snapshots = [];
        foreach ($questions as $q) {
            $snapshots[] = [
                'survey_id' => $survey->id,
                'question_id' => $q->id,
                'question_text' => $q->question_text,
                'cqc_domain' => $q->cqc_domain,
                'sort_order' => $q->sort_order,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($snapshots)) {
            PatientFeedbackSurveyQuestion::insert($snapshots);
        }
    }
}


