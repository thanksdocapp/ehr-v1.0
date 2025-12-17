<?php

namespace App\Http\Controllers;

use App\Models\PatientFeedbackResponse;
use App\Models\PatientFeedbackSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicFeedbackController extends Controller
{
    public function show(string $token)
    {
        if (!Schema::hasTable('patient_feedback_surveys')) {
            abort(500, 'Patient Feedback is not installed on this database yet. Please run migrations.');
        }

        $tokenHash = hash('sha256', $token);

        $survey = PatientFeedbackSurvey::where('token_hash', $tokenHash)
            ->with(['questions' => function ($q) {
                $q->orderBy('sort_order');
            }, 'appointment', 'doctor', 'patient'])
            ->firstOrFail();

        if ($survey->isSubmitted()) {
            return view('feedback.already-completed', compact('survey'));
        }

        return view('feedback.fill', compact('survey', 'token'));
    }

    public function submit(Request $request, string $token)
    {
        if (!Schema::hasTable('patient_feedback_surveys')) {
            abort(500, 'Patient Feedback is not installed on this database yet. Please run migrations.');
        }

        $tokenHash = hash('sha256', $token);

        $survey = PatientFeedbackSurvey::where('token_hash', $tokenHash)
            ->with(['questions', 'appointment', 'patient'])
            ->firstOrFail();

        if ($survey->isSubmitted()) {
            return view('feedback.already-completed', compact('survey'));
        }

        $questions = $survey->questions()->orderBy('sort_order')->get();

        $rules = [
            'submission_mode' => 'required|in:identified,anonymous',
            'additional_comments' => 'nullable|string|max:2000',
        ];

        foreach ($questions as $q) {
            // 0 = Not Applicable
            $rules['q_' . $q->id] = 'required|integer|min:0|max:5';
        }

        $validated = $request->validate($rules);

        DB::transaction(function () use ($survey, $questions, $validated) {
            // Save responses
            foreach ($questions as $q) {
                PatientFeedbackResponse::updateOrCreate(
                    [
                        'survey_id' => $survey->id,
                        'survey_question_id' => $q->id,
                    ],
                    [
                        'score' => (int) $validated['q_' . $q->id],
                    ]
                );
            }

            $isAnonymous = $validated['submission_mode'] === 'anonymous';

            // Lock in anonymity choice on the survey record
            $survey->is_anonymous = $isAnonymous;
            $survey->submitted_at = now();
            $survey->additional_comments = isset($validated['additional_comments'])
                ? trim((string) $validated['additional_comments'])
                : null;

            // If anonymous, remove patient_id so identity is hidden from UI and DB joins
            if ($isAnonymous) {
                $survey->patient_id = null;
            }

            $survey->save();
        });

        return view('feedback.thank-you', compact('survey'));
    }
}


