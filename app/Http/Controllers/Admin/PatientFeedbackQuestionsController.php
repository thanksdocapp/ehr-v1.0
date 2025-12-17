<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PatientFeedbackQuestion;
use Illuminate\Http\Request;

class PatientFeedbackQuestionsController extends Controller
{
    public function index()
    {
        $questions = PatientFeedbackQuestion::ordered()->get();
        $enabledCount = PatientFeedbackQuestion::where('is_enabled', true)->count();

        return view('admin.patient-feedback.questions.index', compact('questions', 'enabledCount'));
    }

    public function create()
    {
        $nextOrder = (int) (PatientFeedbackQuestion::max('sort_order') ?? 0) + 1;
        return view('admin.patient-feedback.questions.create', compact('nextOrder'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question_text' => 'required|string|max:500',
            'cqc_domain' => 'required|in:safe,effective,caring,responsive,well_led',
            'is_enabled' => 'nullable|boolean',
            'sort_order' => 'required|integer|min:0|max:10000',
        ]);

        $data['is_enabled'] = (bool) ($data['is_enabled'] ?? false);

        $this->enforceMaxEnabled($data['is_enabled']);

        PatientFeedbackQuestion::create($data);

        return redirect()->route('admin.patient-feedback.questions.index')
            ->with('success', 'Feedback question created.');
    }

    public function edit(PatientFeedbackQuestion $question)
    {
        return view('admin.patient-feedback.questions.edit', compact('question'));
    }

    public function update(Request $request, PatientFeedbackQuestion $question)
    {
        $data = $request->validate([
            'question_text' => 'required|string|max:500',
            'cqc_domain' => 'required|in:safe,effective,caring,responsive,well_led',
            'is_enabled' => 'nullable|boolean',
            'sort_order' => 'required|integer|min:0|max:10000',
        ]);

        $data['is_enabled'] = (bool) ($data['is_enabled'] ?? false);

        $this->enforceMaxEnabled($data['is_enabled'], $question);

        $question->update($data);

        return redirect()->route('admin.patient-feedback.questions.index')
            ->with('success', 'Feedback question updated.');
    }

    public function toggle(PatientFeedbackQuestion $question)
    {
        $newEnabled = !$question->is_enabled;
        $this->enforceMaxEnabled($newEnabled, $question);

        $question->update(['is_enabled' => $newEnabled]);

        return redirect()->route('admin.patient-feedback.questions.index')
            ->with('success', 'Question status updated.');
    }

    public function moveUp(PatientFeedbackQuestion $question)
    {
        $prev = PatientFeedbackQuestion::where('sort_order', '<', $question->sort_order)
            ->orderBy('sort_order', 'desc')
            ->first();

        if ($prev) {
            $tmp = $question->sort_order;
            $question->sort_order = $prev->sort_order;
            $prev->sort_order = $tmp;
            $question->save();
            $prev->save();
        }

        return redirect()->route('admin.patient-feedback.questions.index');
    }

    public function moveDown(PatientFeedbackQuestion $question)
    {
        $next = PatientFeedbackQuestion::where('sort_order', '>', $question->sort_order)
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($next) {
            $tmp = $question->sort_order;
            $question->sort_order = $next->sort_order;
            $next->sort_order = $tmp;
            $question->save();
            $next->save();
        }

        return redirect()->route('admin.patient-feedback.questions.index');
    }

    /**
     * Max 10 enabled questions (as per requirements).
     */
    protected function enforceMaxEnabled(bool $willBeEnabled, ?PatientFeedbackQuestion $current = null): void
    {
        if (!$willBeEnabled) {
            return;
        }

        $enabledCount = PatientFeedbackQuestion::where('is_enabled', true)->count();
        if ($current && $current->is_enabled) {
            // Editing/toggling an already-enabled question doesn't increase enabled count
            return;
        }

        if ($enabledCount >= 10) {
            abort(422, 'You can only enable up to 10 feedback questions. Disable one before enabling another.');
        }
    }
}


