<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\PatientFeedbackSurvey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PatientFeedbackController extends Controller
{
    protected function currentDoctor(): Doctor
    {
        $user = auth()->user();
        if (!$user) {
            abort(403);
        }

        $doctor = Doctor::where('user_id', $user->id)->first();
        if (!$doctor) {
            abort(403);
        }

        return $doctor;
    }

    public function index()
    {
        if (!Schema::hasTable('patient_feedback_surveys')) {
            abort(500, 'Patient Feedback is not installed on this database yet. Please run migrations.');
        }

        $doctor = $this->currentDoctor();

        $surveys = PatientFeedbackSurvey::query()
            ->where('doctor_id', $doctor->id)
            ->whereNotNull('submitted_at')
            ->with(['patient', 'appointment'])
            // Exclude "Not Applicable" (stored as score=0) from averages
            ->withAvg(['responses as responses_avg_score' => function ($q) {
                $q->where('score', '>', 0);
            }], 'score')
            ->orderByDesc('submitted_at')
            ->paginate(25);

        return view('staff.feedback.index', compact('surveys'));
    }

    public function show(PatientFeedbackSurvey $survey)
    {
        if (!Schema::hasTable('patient_feedback_surveys')) {
            abort(500, 'Patient Feedback is not installed on this database yet. Please run migrations.');
        }

        $doctor = $this->currentDoctor();
        if ((int) $survey->doctor_id !== (int) $doctor->id) {
            abort(403);
        }

        $survey->load(['patient', 'appointment', 'questions', 'responses.surveyQuestion']);

        return view('staff.feedback.show', compact('survey'));
    }
}


