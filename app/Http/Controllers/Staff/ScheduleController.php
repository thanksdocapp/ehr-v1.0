<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorAvailabilityException;
use App\Models\Appointment;
use App\Services\DoctorWeeklyAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    public function __construct(
        private readonly DoctorWeeklyAvailabilityService $weeklyAvailabilityService
    ) {}

    /**
     * Display the doctor's schedule/availability management page.
     */
    public function index()
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return redirect()->route('staff.dashboard')
                ->with('error', 'Doctor profile not found. Please contact administrator.');
        }

        // Get current availability settings and normalize to sessions for the form
        $availability = $this->weeklyAvailabilityService->normalizeAvailabilityForForm($doctor->availability ?? $this->getDefaultAvailability());

        // Get upcoming blocked dates
        try {
            $blockedDates = DoctorAvailabilityException::forDoctor($doctor->id)
                ->upcoming()
                ->blocked()
                ->orderBy('exception_date')
                ->get();
        } catch (\Exception $e) {
            // Log the error and use empty collection as fallback
            Log::error('Error fetching blocked dates: ' . $e->getMessage(), [
                'doctor_id' => $doctor->id,
                'exception' => $e
            ]);
            $blockedDates = collect([]);
        }

        // Calendar days from today through the same calendar day next month (inclusive)
        $schedulePreviewDates = collect();
        $cursor = now()->startOfDay();
        $previewEnd = now()->copy()->addMonth()->startOfDay();
        while ($cursor->lte($previewEnd)) {
            $schedulePreviewDates->push($cursor->copy());
            $cursor->addDay();
        }

        // Get upcoming appointments count per day for that preview window
        $upcomingAppointments = Appointment::where('doctor_id', $doctor->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('appointment_date', [
                $schedulePreviewDates->first()->toDateString(),
                $schedulePreviewDates->last()->toDateString(),
            ])
            ->get()
            ->groupBy(function ($appointment) {
                return $appointment->appointment_date->format('Y-m-d');
            })
            ->map(function ($appointments) {
                return $appointments->count();
            });

        // Days of the week for the form
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

        return view('staff.schedule.index', compact(
            'doctor',
            'availability',
            'blockedDates',
            'upcomingAppointments',
            'schedulePreviewDates',
            'daysOfWeek'
        ));
    }

    /**
     * Update the doctor's weekly availability.
     */
    public function updateAvailability(Request $request)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return redirect()->back()->with('error', 'Doctor profile not found.');
        }

        try {
            $availability = $this->weeklyAvailabilityService->buildAvailabilityFromRequest(
                $request->input('availability', [])
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()
                ->with('error', $e->getMessage())
                ->withInput();
        }

        $doctor->update(['availability' => $availability]);
        $this->weeklyAvailabilityService->syncRulesFromWeeklySchedule($doctor->fresh());

        return redirect()->route('staff.schedule.index')
            ->with('success', 'Your weekly availability has been updated successfully.');
    }

    /**
     * Add a blocked date (vacation/day off).
     */
    public function addBlockedDate(Request $request)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor profile not found.'], 404);
        }

        $request->validate([
            'exception_date' => 'required|date|after_or_equal:today',
            'reason' => 'nullable|string|max:255',
            'is_all_day' => 'sometimes|boolean',
            // Times come either as a single start_time/end_time pair (legacy) or an
            // intervals[] array. Presence is enforced in the controller (empty-intervals
            // check) so a missing pair does not block the intervals[] path.
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i',
            'intervals' => 'sometimes|array',
            'intervals.*.start' => 'required_with:intervals|date_format:H:i',
            'intervals.*.end' => 'required_with:intervals|date_format:H:i',
        ]);

        $exceptionDate = Carbon::parse($request->exception_date);
        $dateStr = $exceptionDate->toDateString();
        $isAllDay = $request->boolean('is_all_day', true);

        // Existing blocks for this doctor + date.
        $existingBlocks = DoctorAvailabilityException::where('doctor_id', $doctor->id)
            ->where('exception_date', $dateStr)
            ->where('type', 'blocked')
            ->get();

        $existingAllDay = $existingBlocks->firstWhere('is_all_day', true);

        // ---- Whole-day block ----
        if ($isAllDay) {
            if ($existingBlocks->isNotEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => $existingAllDay
                        ? 'This date is already blocked for the whole day.'
                        : 'This date already has blocked time interval(s). Remove them before blocking the whole day.'
                ], 422);
            }

            $exception = DoctorAvailabilityException::create([
                'doctor_id' => $doctor->id,
                'exception_date' => $exceptionDate,
                'type' => 'blocked',
                'reason' => $request->reason,
                'is_all_day' => true,
                'start_time' => null,
                'end_time' => null,
            ]);

            $appointmentsOnDate = Appointment::where('doctor_id', $doctor->id)
                ->whereDate('appointment_date', $exceptionDate)
                ->whereIn('status', ['pending', 'confirmed'])
                ->count();

            $message = 'Date blocked successfully.';
            if ($appointmentsOnDate > 0) {
                $message .= " Note: You have {$appointmentsOnDate} existing appointment(s) on this date that may need to be rescheduled.";
            }

            return $this->blockedDateResponse($request, $message, collect([$exception]), $appointmentsOnDate);
        }

        // ---- Time-interval block ----
        if ($existingAllDay) {
            return response()->json([
                'success' => false,
                'message' => 'This date is already blocked for the whole day. Remove the whole-day block before adding intervals.'
            ], 422);
        }

        $intervals = $this->parseBlockIntervals($request);

        if (empty($intervals)) {
            return response()->json([
                'success' => false,
                'message' => 'Please provide at least one valid time interval (end time after start time).'
            ], 422);
        }

        // Reject intervals that overlap each other within this submission.
        if ($this->intervalsOverlapWithinSet($intervals)) {
            return response()->json([
                'success' => false,
                'message' => 'The time intervals overlap each other. Please use separate, non-overlapping intervals.'
            ], 422);
        }

        // Reject intervals overlapping any interval already stored for this date.
        $existingIntervals = $existingBlocks
            ->where('is_all_day', false)
            ->filter(fn($b) => $b->start_time && $b->end_time)
            ->map(fn($b) => [
                'start' => $b->start_time->format('H:i'),
                'end' => $b->end_time->format('H:i'),
            ])
            ->values()
            ->all();

        foreach ($intervals as $interval) {
            foreach ($existingIntervals as $existing) {
                if ($this->twoIntervalsOverlap($interval, $existing)) {
                    return response()->json([
                        'success' => false,
                        'message' => "Interval {$interval['start']}-{$interval['end']} overlaps an existing blocked interval ({$existing['start']}-{$existing['end']}) on this date."
                    ], 422);
                }
            }
        }

        $created = collect();
        foreach ($intervals as $interval) {
            $created->push(DoctorAvailabilityException::create([
                'doctor_id' => $doctor->id,
                'exception_date' => $exceptionDate,
                'type' => 'blocked',
                'reason' => $request->reason,
                'is_all_day' => false,
                'start_time' => $interval['start'],
                'end_time' => $interval['end'],
            ]));
        }

        // Flag (do not drop) existing appointments that fall inside the new interval(s).
        $conflicting = $this->appointmentsWithinIntervals($doctor->id, $exceptionDate, $intervals);

        $message = count($intervals) > 1
            ? 'Time intervals blocked successfully.'
            : 'Time interval blocked successfully.';
        if ($conflicting > 0) {
            $message .= " Warning: {$conflicting} existing appointment(s) fall inside the blocked time and may need to be rescheduled.";
        }

        return $this->blockedDateResponse($request, $message, $created, $conflicting);
    }

    /**
     * Build the add-blocked-date response (JSON for AJAX, redirect otherwise).
     */
    private function blockedDateResponse(Request $request, string $message, $exceptions, int $appointmentsCount)
    {
        if ($request->expectsJson()) {
            $first = $exceptions->first();

            return response()->json([
                'success' => true,
                'message' => $message,
                // Backwards-compatible single-exception payload (first created row).
                'exception' => $first ? [
                    'id' => $first->id,
                    'exception_date' => $first->exception_date->format('Y-m-d'),
                    'reason' => $first->reason,
                    'is_all_day' => (bool) $first->is_all_day,
                    'start_time' => $first->start_time?->format('H:i'),
                    'end_time' => $first->end_time?->format('H:i'),
                ] : null,
                'exceptions' => $exceptions->map(fn($e) => [
                    'id' => $e->id,
                    'exception_date' => $e->exception_date->format('Y-m-d'),
                    'reason' => $e->reason,
                    'is_all_day' => (bool) $e->is_all_day,
                    'start_time' => $e->start_time?->format('H:i'),
                    'end_time' => $e->end_time?->format('H:i'),
                ])->values(),
                'appointments_count' => $appointmentsCount,
            ]);
        }

        return redirect()->back()->with('success', $message);
    }

    /**
     * Normalize block intervals from the request.
     * Accepts either an `intervals` array ([{start,end}, ...]) or a single
     * legacy start_time/end_time pair. Returns only valid intervals (end > start).
     */
    private function parseBlockIntervals(Request $request): array
    {
        $raw = $request->input('intervals');
        if (!is_array($raw) || empty($raw)) {
            // Legacy single-interval payload.
            $raw = [[
                'start' => $request->input('start_time'),
                'end' => $request->input('end_time'),
            ]];
        }

        $intervals = [];
        foreach ($raw as $item) {
            $start = $item['start'] ?? null;
            $end = $item['end'] ?? null;
            if (!$start || !$end) {
                continue;
            }
            // end must be strictly after start.
            if ($this->timeToMinutes($end) <= $this->timeToMinutes($start)) {
                continue;
            }
            $intervals[] = ['start' => $start, 'end' => $end];
        }

        return $intervals;
    }

    /**
     * True if any two intervals in the set overlap.
     */
    private function intervalsOverlapWithinSet(array $intervals): bool
    {
        $count = count($intervals);
        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($this->twoIntervalsOverlap($intervals[$i], $intervals[$j])) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * True if two HH:MM intervals overlap (touching edges do not count).
     */
    private function twoIntervalsOverlap(array $a, array $b): bool
    {
        $aStart = $this->timeToMinutes($a['start']);
        $aEnd = $this->timeToMinutes($a['end']);
        $bStart = $this->timeToMinutes($b['start']);
        $bEnd = $this->timeToMinutes($b['end']);

        return $aStart < $bEnd && $aEnd > $bStart;
    }

    /**
     * Count pending/confirmed appointments whose start time falls inside any interval.
     */
    private function appointmentsWithinIntervals(int $doctorId, Carbon $date, array $intervals): int
    {
        if (empty($intervals)) {
            return 0;
        }

        $appointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();

        $count = 0;
        foreach ($appointments as $appointment) {
            if (!$appointment->appointment_time) {
                continue;
            }
            $apptMinutes = $this->timeToMinutes($appointment->appointment_time->format('H:i'));
            foreach ($intervals as $interval) {
                $start = $this->timeToMinutes($interval['start']);
                $end = $this->timeToMinutes($interval['end']);
                if ($apptMinutes >= $start && $apptMinutes < $end) {
                    $count++;
                    break;
                }
            }
        }

        return $count;
    }

    /**
     * Convert an HH:MM string to minutes since midnight.
     */
    private function timeToMinutes(?string $time): int
    {
        if (!$time) {
            return 0;
        }
        $parts = explode(':', $time);
        $hours = (int) ($parts[0] ?? 0);
        $minutes = (int) ($parts[1] ?? 0);
        return ($hours * 60) + $minutes;
    }

    /**
     * Remove a blocked date.
     */
    public function removeBlockedDate($id)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor profile not found.'], 404);
        }

        $exception = DoctorAvailabilityException::where('id', $id)
            ->where('doctor_id', $doctor->id)
            ->first();

        if (!$exception) {
            return response()->json(['success' => false, 'message' => 'Blocked date not found.'], 404);
        }

        $exception->delete();

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Blocked date removed successfully.'
            ]);
        }

        return redirect()->back()->with('success', 'Blocked date removed successfully.');
    }

    /**
     * Get available slots for a specific date (API endpoint).
     */
    public function getAvailableSlotsForDate(Request $request)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor profile not found.'], 404);
        }

        $date = $request->input('date');
        if (!$date) {
            return response()->json(['success' => false, 'message' => 'Date is required.'], 422);
        }

        $dateObj = Carbon::parse($date);
        $dayName = strtolower($dateObj->format('l'));
        $availability = $doctor->availability ?? $this->getDefaultAvailability();

        // Check if the whole date is blocked (all-day blocks only; partial-day
        // interval blocks leave the rest of the day bookable).
        $isBlocked = DoctorAvailabilityException::where('doctor_id', $doctor->id)
            ->where('exception_date', $dateObj->toDateString())
            ->blocked()
            ->where('is_all_day', true)
            ->exists();

        if ($isBlocked) {
            return response()->json([
                'success' => true,
                'available' => false,
                'reason' => 'blocked',
                'message' => 'This date is blocked.'
            ]);
        }

        // Partial-day blocked intervals for this date (so the preview can reflect them).
        $blockedIntervals = DoctorAvailabilityException::where('doctor_id', $doctor->id)
            ->where('exception_date', $dateObj->toDateString())
            ->blocked()
            ->where('is_all_day', false)
            ->whereNotNull('start_time')
            ->whereNotNull('end_time')
            ->get()
            ->map(fn($e) => [
                'start' => $e->start_time->format('H:i'),
                'end' => $e->end_time->format('H:i'),
            ])
            ->values();

        // Check regular availability
        $dayAvailability = $availability[$dayName] ?? null;
        if (!$dayAvailability || !$dayAvailability['available']) {
            return response()->json([
                'success' => true,
                'available' => false,
                'reason' => 'not_working',
                'message' => 'Not available on ' . ucfirst($dayName) . 's.'
            ]);
        }

        // Get existing appointments
        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $dateObj)
            ->whereIn('status', ['pending', 'confirmed'])
            ->get();

        $sessions = $dayAvailability['sessions'] ?? null;
        if (!empty($sessions) && is_array($sessions)) {
            return response()->json([
                'success' => true,
                'available' => true,
                'sessions' => $sessions,
                'working_hours' => [
                    'start' => min(array_column($sessions, 'start')),
                    'end' => max(array_column($sessions, 'end'))
                ],
                'blocked_intervals' => $blockedIntervals,
                'appointments_count' => $appointments->count()
            ]);
        }
        return response()->json([
            'success' => true,
            'available' => true,
            'working_hours' => [
                'start' => $dayAvailability['start'] ?? '09:00',
                'end' => $dayAvailability['end'] ?? '17:00'
            ],
            'breaks' => $dayAvailability['breaks'] ?? [],
            'blocked_intervals' => $blockedIntervals,
            'appointments_count' => $appointments->count()
        ]);
    }

    /**
     * Get blocked dates for calendar display.
     */
    public function getBlockedDates(Request $request)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->first();

        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor profile not found.'], 404);
        }

        $startDate = $request->input('start', now()->startOfMonth()->toDateString());
        $endDate = $request->input('end', now()->endOfMonth()->toDateString());

        $blockedDates = DoctorAvailabilityException::where('doctor_id', $doctor->id)
            ->whereBetween('exception_date', [$startDate, $endDate])
            ->get()
            ->map(function ($exception) {
                return [
                    'id' => $exception->id,
                    'date' => $exception->exception_date->format('Y-m-d'),
                    'title' => $exception->reason ?? 'Blocked',
                    'type' => $exception->type,
                    'is_all_day' => $exception->is_all_day,
                    'start_time' => $exception->start_time?->format('H:i'),
                    'end_time' => $exception->end_time?->format('H:i')
                ];
            });

        return response()->json([
            'success' => true,
            'blocked_dates' => $blockedDates
        ]);
    }

    /**
     * Get default availability settings.
     */
    private function getDefaultAvailability(): array
    {
        return [
            'monday' => ['available' => true, 'start' => '09:00', 'end' => '17:00', 'breaks' => []],
            'tuesday' => ['available' => true, 'start' => '09:00', 'end' => '17:00', 'breaks' => []],
            'wednesday' => ['available' => true, 'start' => '09:00', 'end' => '17:00', 'breaks' => []],
            'thursday' => ['available' => true, 'start' => '09:00', 'end' => '17:00', 'breaks' => []],
            'friday' => ['available' => true, 'start' => '09:00', 'end' => '17:00', 'breaks' => []],
            'saturday' => ['available' => false, 'start' => '09:00', 'end' => '13:00', 'breaks' => []],
            'sunday' => ['available' => false, 'start' => '09:00', 'end' => '13:00', 'breaks' => []]
        ];
    }
}
