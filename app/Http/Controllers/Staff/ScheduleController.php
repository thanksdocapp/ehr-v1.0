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

        // Get current availability settings
        $availability = $doctor->availability ?? $this->getDefaultAvailability();

        // Get upcoming blocked dates
        $blockedDates = DoctorAvailabilityException::forDoctor($doctor->id)
            ->upcoming()
            ->blocked()
            ->orderBy('exception_date')
            ->get();

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
            $isAvailable = $request->input("availability.{$day}.available", false);
            $startTime = $request->input("availability.{$day}.start", '09:00');
            $endTime = $request->input("availability.{$day}.end", '17:00');

            // Validate times
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

        return response()->json([
            'success' => true,
            'available' => true,
            'working_hours' => [
                'start' => $dayAvailability['start'],
                'end' => $dayAvailability['end']
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
