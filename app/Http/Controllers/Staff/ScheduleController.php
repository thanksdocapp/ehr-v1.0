<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorAvailabilityException;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScheduleController extends Controller
{
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
        $availability = $this->normalizeAvailabilityForForm($doctor->availability ?? $this->getDefaultAvailability());

        // Get upcoming blocked dates
        try {
            $blockedDates = DoctorAvailabilityException::forDoctor($doctor->id)
                ->upcoming()
                ->blocked()
                ->orderBy('exception_date')
                ->get();
        } catch (\Exception $e) {
            // Log the error and use empty collection as fallback
            \Log::error('Error fetching blocked dates: ' . $e->getMessage(), [
                'doctor_id' => $doctor->id,
                'exception' => $e
            ]);
            $blockedDates = collect([]);
        }

        // Get upcoming appointments count per day (next 7 days)
        $upcomingAppointments = Appointment::where('doctor_id', $doctor->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->whereBetween('appointment_date', [now()->toDateString(), now()->addDays(7)->toDateString()])
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

        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $availability = [];

        foreach ($daysOfWeek as $day) {
            $sessionsInput = $request->input("availability.{$day}.sessions", []);
            $isAvailable = $request->input("availability.{$day}.available", false);

            if ($isAvailable && is_array($sessionsInput) && !empty($sessionsInput)) {
                // New format: multiple sessions (time windows) per day
                $sessions = $this->parseSessions($sessionsInput);
                if (empty($sessions)) {
                    return redirect()->back()
                        ->with('error', ucfirst($day) . ": at least one valid time window (start before end) is required.")
                        ->withInput();
                }
                $availability[$day] = [
                    'available' => true,
                    'sessions' => $sessions
                ];
            } else {
                // Legacy format: single start/end + breaks (e.g. from old form or API)
                $startTime = $request->input("availability.{$day}.start", '09:00');
                $endTime = $request->input("availability.{$day}.end", '17:00');
                if ($isAvailable && $startTime >= $endTime) {
                    return redirect()->back()
                        ->with('error', "Invalid time range for " . ucfirst($day) . ". Start time must be before end time.")
                        ->withInput();
                }
                $availability[$day] = [
                    'available' => (bool) $isAvailable,
                    'start' => $startTime,
                    'end' => $endTime,
                    'breaks' => $this->parseBreaks($request->input("availability.{$day}.breaks", []))
                ];
            }
        }

        $doctor->update(['availability' => $availability]);

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
            'is_all_day' => 'boolean',
            'start_time' => 'nullable|required_if:is_all_day,false',
            'end_time' => 'nullable|required_if:is_all_day,false'
        ]);

        $exceptionDate = Carbon::parse($request->exception_date);

        // Check if date already blocked
        $existing = DoctorAvailabilityException::where('doctor_id', $doctor->id)
            ->whereDate('exception_date', $exceptionDate)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'This date is already blocked.'
            ], 422);
        }

        // Check for existing appointments on this date
        $appointmentsOnDate = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $exceptionDate)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $exception = DoctorAvailabilityException::create([
            'doctor_id' => $doctor->id,
            'exception_date' => $exceptionDate,
            'type' => 'blocked',
            'reason' => $request->reason,
            'is_all_day' => $request->is_all_day ?? true,
            'start_time' => $request->is_all_day ? null : $request->start_time,
            'end_time' => $request->is_all_day ? null : $request->end_time
        ]);

        $message = 'Date blocked successfully.';
        if ($appointmentsOnDate > 0) {
            $message .= " Note: You have {$appointmentsOnDate} existing appointment(s) on this date that may need to be rescheduled.";
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'exception' => $exception,
                'appointments_count' => $appointmentsOnDate
            ]);
        }

        return redirect()->back()->with('success', $message);
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

        // Check if date is blocked
        $isBlocked = DoctorAvailabilityException::where('doctor_id', $doctor->id)
            ->whereDate('exception_date', $dateObj)
            ->blocked()
            ->exists();

        if ($isBlocked) {
            return response()->json([
                'success' => true,
                'available' => false,
                'reason' => 'blocked',
                'message' => 'This date is blocked.'
            ]);
        }

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
     * Normalize availability so each day has 'sessions' (array of time windows) for the form.
     * Converts legacy start/end/breaks into sessions so the UI can show multiple windows.
     */
    private function normalizeAvailabilityForForm(array $availability): array
    {
        $daysOfWeek = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        foreach ($daysOfWeek as $day) {
            $dayData = $availability[$day] ?? ['available' => false, 'start' => '09:00', 'end' => '17:00', 'breaks' => []];
            if (!empty($dayData['sessions']) && is_array($dayData['sessions'])) {
                continue; // already has sessions
            }
            if (empty($dayData['available'])) {
                $availability[$day] = array_merge($dayData, ['sessions' => []]);
                continue;
            }
            $start = $dayData['start'] ?? $dayData['from'] ?? '09:00';
            $end = $dayData['end'] ?? $dayData['to'] ?? '17:00';
            $breaks = $dayData['breaks'] ?? [];
            $availability[$day]['sessions'] = $this->splitRangeByBreaks($start, $end, $breaks);
        }
        return $availability;
    }

    /**
     * Split a time range into sessions by subtracting break periods.
     */
    private function splitRangeByBreaks(string $start, string $end, array $breaks): array
    {
        if (empty($breaks)) {
            return $start < $end ? [['start' => $start, 'end' => $end]] : [];
        }
        $ranges = [['start' => $start, 'end' => $end]];
        foreach ($breaks as $break) {
            if (empty($break['start']) || empty($break['end'])) {
                continue;
            }
            $newRanges = [];
            foreach ($ranges as $r) {
                $bStart = $break['start'];
                $bEnd = $break['end'];
                if ($bEnd <= $r['start'] || $bStart >= $r['end']) {
                    $newRanges[] = $r;
                    continue;
                }
                if ($r['start'] < $bStart) {
                    $newRanges[] = ['start' => $r['start'], 'end' => $bStart];
                }
                if ($bEnd < $r['end']) {
                    $newRanges[] = ['start' => $bEnd, 'end' => $r['end']];
                }
            }
            $ranges = $newRanges;
        }
        return array_values(array_filter($ranges, fn($r) => $r['start'] < $r['end']));
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

    /**
     * Parse sessions (time windows) from request input.
     * Each session must have start and end (HH:MM), start < end.
     */
    private function parseSessions(array $sessions): array
    {
        $parsed = [];
        foreach ($sessions as $s) {
            $start = $s['start'] ?? $s['from'] ?? null;
            $end = $s['end'] ?? $s['to'] ?? null;
            if (!empty($start) && !empty($end) && $start < $end) {
                $parsed[] = ['start' => $start, 'end' => $end];
            }
        }
        return $parsed;
    }

    /**
     * Parse breaks from request input.
     */
    private function parseBreaks($breaks): array
    {
        if (!is_array($breaks)) {
            return [];
        }

        $parsedBreaks = [];
        foreach ($breaks as $break) {
            if (!empty($break['start']) && !empty($break['end'])) {
                $parsedBreaks[] = [
                    'start' => $break['start'],
                    'end' => $break['end']
                ];
            }
        }

        return $parsedBreaks;
    }
}
