<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Template;
use App\Models\Patient;
use Illuminate\Http\Request;

class TemplatesController extends Controller
{
    public function index(Request $request)
    {
        $query = Template::visibleTo(auth()->user())->with('creator');

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $templates = $query->orderBy('name')->paginate(20);

        return view('staff.templates.index', compact('templates'));
    }

    public function create()
    {
        $this->authorize('create', Template::class);
        $type = request()->get('type', 'letter');
        if (!in_array($type, ['letter', 'form'], true)) {
            $type = 'letter';
        }

        return view('staff.templates.create', compact('type'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Template::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:letter,form',
            'content' => 'required|string',
        ]);

        $template = Template::create([
            'name' => $validated['name'],
            'type' => $validated['type'],
            'content' => $validated['content'],
            'created_by' => auth()->id(),
            'is_system' => false,
            'is_active' => true,
        ]);

        return redirect()->route('staff.templates.show', $template)
            ->with('success', 'Template created successfully.');
    }

    public function show(Template $template)
    {
        $this->authorize('view', $template);
        return view('staff.templates.show', compact('template'));
    }

    public function edit(Template $template)
    {
        $this->authorize('update', $template);
        return view('staff.templates.edit', compact('template'));
    }

    public function update(Request $request, Template $template)
    {
        $this->authorize('update', $template);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:letter,form',
            'content' => 'required|string',
        ]);

        $template->update($validated);

        return redirect()->route('staff.templates.show', $template)
            ->with('success', 'Template updated successfully.');
    }

    public function destroy(Template $template)
    {
        $this->authorize('delete', $template);

        $template->delete();

        return redirect()->route('staff.templates.index')
            ->with('success', 'Template deleted successfully.');
    }

    public function duplicate(Template $template)
    {
        $this->authorize('view', $template);

        $newTemplate = $template->replicate();
        $newTemplate->name = $template->name . ' (Copy)';
        $newTemplate->created_by = auth()->id();
        $newTemplate->is_system = false;
        $newTemplate->save();

        return redirect()->route('staff.templates.edit', $newTemplate)
            ->with('success', 'Template duplicated successfully.');
    }

    /**
     * Search patients (AJAX endpoint for Select2).
     * Returns Select2-compatible format.
     * Only returns patients belonging to the logged-in user's clinic/department.
     */
    public function searchPatients(Request $request)
    {
        $search = $request->get('q', '');
        $departmentId = auth()->user()->department_id;

        // Use cross-database compatible query (no CONCAT)
        // Filter to only show patients in the same clinic/department as the logged-in user
        $query = Patient::where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")
                ->orWhere('patient_id', 'like', "%{$search}%");
        })
        ->where('is_active', true);

        // Filter by department if the user has one assigned
        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $patients = $query->limit(20)
            ->get(['id', 'first_name', 'last_name', 'email', 'date_of_birth', 'patient_id']);

        return response()->json($patients->map(function ($patient) {
            $fullName = trim($patient->first_name . ' ' . $patient->last_name);
            $identifier = $patient->patient_id ?? ('ID: ' . $patient->id);
            $dob = $patient->date_of_birth ? $patient->date_of_birth->format('d/m/Y') : 'No DOB';

            return [
                'id' => $patient->id,
                'text' => "{$fullName} ({$identifier}) - {$dob}",
                'email' => $patient->email,
            ];
        }));
    }
}
