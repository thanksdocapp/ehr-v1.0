<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorAvailabilityRule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class AvailabilityRuleController extends Controller
{
    private const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    private const MODALITIES = [
        DoctorAvailabilityRule::MODALITY_IN_PERSON,
        DoctorAvailabilityRule::MODALITY_ONLINE,
        DoctorAvailabilityRule::MODALITY_TELEPHONE,
        DoctorAvailabilityRule::MODALITY_ALL,
    ];

    /**
     * List the authenticated doctor's per-modality availability rules.
     */
    public function index()
    {
        $doctor = $this->resolveDoctor();
        if (!$doctor) {
            return redirect()->route('staff.dashboard')
                ->with('error', 'Doctor profile not found. Please contact administrator.');
        }

        $dayOrder = array_flip(self::DAYS);
        $rules = $doctor->availabilityRules()
            ->orderBy('start_time')
            ->get()
            ->sortBy(fn ($rule) => $dayOrder[$rule->day_of_week] ?? 99)
            ->groupBy('day_of_week');

        $needsReviewCount = $doctor->availabilityRules()->needsReview()->count();

        return view('staff.schedule.availability-rules', [
            'doctor' => $doctor,
            'rulesByDay' => $rules,
            'days' => self::DAYS,
            'modalities' => self::MODALITIES,
            'needsReviewCount' => $needsReviewCount,
        ]);
    }

    /**
     * Create a new availability rule.
     */
    public function store(Request $request)
    {
        $doctor = $this->resolveDoctor();
        if (!$doctor) {
            return redirect()->route('staff.dashboard')->with('error', 'Doctor profile not found.');
        }

        $data = $this->validateRule($request);

        $doctor->availabilityRules()->create([
            'day_of_week' => $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'modality' => $data['modality'],
            'is_active' => $request->boolean('is_active', true),
            'needs_review' => false,
            'source' => 'manual',
        ]);

        return redirect()->route('staff.schedule.availability-rules.index')
            ->with('success', 'Availability rule added.');
    }

    /**
     * Update an existing availability rule.
     */
    public function update(Request $request, DoctorAvailabilityRule $availabilityRule)
    {
        $doctor = $this->resolveDoctor();
        if (!$doctor || (int) $availabilityRule->doctor_id !== (int) $doctor->id) {
            abort(403);
        }

        $data = $this->validateRule($request);

        $availabilityRule->update([
            'day_of_week' => $data['day_of_week'],
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'modality' => $data['modality'],
            'is_active' => $request->boolean('is_active', true),
            // Editing a flagged rule clears the review flag — the doctor has confirmed it.
            'needs_review' => false,
        ]);

        return redirect()->route('staff.schedule.availability-rules.index')
            ->with('success', 'Availability rule updated.');
    }

    /**
     * Delete an availability rule.
     */
    public function destroy(DoctorAvailabilityRule $availabilityRule)
    {
        $doctor = $this->resolveDoctor();
        if (!$doctor || (int) $availabilityRule->doctor_id !== (int) $doctor->id) {
            abort(403);
        }

        $availabilityRule->delete();

        return redirect()->route('staff.schedule.availability-rules.index')
            ->with('success', 'Availability rule removed.');
    }

    private function resolveDoctor(): ?Doctor
    {
        return Doctor::where('user_id', Auth::id())->first();
    }

    // sortBy in index() keeps day ordering DB-agnostic (MySQL + SQLite).

    /**
     * @return array{day_of_week:string,start_time:string,end_time:string,modality:string}
     */
    private function validateRule(Request $request): array
    {
        $validated = $request->validate([
            'day_of_week' => ['required', Rule::in(self::DAYS)],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'modality' => ['required', Rule::in(self::MODALITIES)],
            'is_active' => ['sometimes', 'boolean'],
        ], [
            'end_time.after' => 'The end time must be later than the start time.',
        ]);

        return [
            'day_of_week' => strtolower($validated['day_of_week']),
            'start_time' => $validated['start_time'] . ':00',
            'end_time' => $validated['end_time'] . ':00',
            'modality' => $validated['modality'],
        ];
    }
}
