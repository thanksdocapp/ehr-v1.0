<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the patient's appointments.
     */
    public function index(Request $request): View
    {
        $patient = Auth::guard('patient')->user();
        
        $query = $patient->appointments()->with(['doctor', 'department']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('appointment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('appointment_date', '<=', $request->date_to);
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
                            ->orderBy('appointment_time', 'desc')
                            ->paginate(10);

        $stats = [
            'total' => $patient->appointments()->count(),
            'upcoming' => $patient->appointments()
                ->where('appointment_date', '>=', today())
                ->where('status', '!=', 'cancelled')
                ->count(),
            'completed' => $patient->appointments()->where('status', 'completed')->count(),
            'cancelled' => $patient->appointments()->where('status', 'cancelled')->count(),
        ];

        return view('patient.appointments.index', compact('appointments', 'stats'));
    }

    /**
     * Show the form for creating a new appointment.
     */
    public function create(): View
    {
        $departments = Department::where('is_active', true)->get();
        $doctors = Doctor::where('is_active', true)->with('department')->get();
        
        return view('patient.appointments.create', compact('departments', 'doctors'));
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request): RedirectResponse
    {
        $patient = Auth::guard('patient')->user();

        $validator = Validator::make($request->all(), [
            'department_id' => ['required', 'exists:departments,id'],
            'doctor_id' => ['required', 'exists:doctors,id'],
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'reason' => ['required', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'priority' => ['nullable', 'in:low,normal,high,urgent'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        // Check if the time slot is available
        $existingAppointment = Appointment::where('doctor_id', $request->doctor_id)
            ->where('appointment_date', $request->appointment_date)
            ->where('appointment_time', $request->appointment_time)
            ->where('status', '!=', 'cancelled')
            ->first();

        if ($existingAppointment) {
            return back()->withErrors(['appointment_time' => 'This time slot is already booked.'])->withInput();
        }

        // Generate appointment number
        $appointmentNumber = $this->generateAppointmentNumber();

        $appointment = Appointment::create([
            'appointment_number' => $appointmentNumber,
            'patient_id' => $patient->id,
            'department_id' => $request->department_id,
            'doctor_id' => $request->doctor_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'reason' => $request->reason,
            'notes' => $request->notes,
            'priority' => $request->priority ?? 'normal',
            'status' => 'pending',
            'booking_date' => now(),
        ]);

        return redirect()->route('patient.appointments.show', $appointment)
            ->with('success', 'Appointment booked successfully! Your appointment number is: ' . $appointmentNumber);
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment): View
    {
        $patient = Auth::guard('patient')->user();

        // Ensure the appointment belongs to the authenticated patient
        if ($appointment->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to appointment.');
        }

        $appointment->load(['doctor', 'department']);

        return view('patient.appointments.show', compact('appointment'));
    }

    /**
     * Cancel the specified appointment.
     */
    public function cancel(Appointment $appointment): RedirectResponse
    {
        $patient = Auth::guard('patient')->user();

        // Ensure the appointment belongs to the authenticated patient
        if ($appointment->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to appointment.');
        }

        // Check if appointment can be cancelled
        if ($appointment->status === 'cancelled') {
            return back()->with('error', 'This appointment is already cancelled.');
        }

        if ($appointment->status === 'completed') {
            return back()->with('error', 'Cannot cancel a completed appointment.');
        }

        // Check cancellation policy
        $appointmentDateTime = $appointment->appointment_date_time;
        $hoursUntilAppointment = now()->diffInHours($appointmentDateTime, false);
        
        // Allow cancellation if:
        // 1. Appointment is more than 24 hours away, OR
        // 2. Appointment was booked within the last 2 hours (grace period)
        $isWithinGracePeriod = $appointment->created_at->diffInHours(now()) <= 2;
        $isMoreThan24HoursAway = $hoursUntilAppointment > 24;
        
        if (!$isMoreThan24HoursAway && !$isWithinGracePeriod) {
            return back()->with('error', 'We apologize, but appointments can only be cancelled more than 24 hours in advance. For urgent changes within 24 hours, please contact our office directly at (555) 123-4567.');
        }

        $appointment->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => 'patient',
        ]);

        return redirect()->route('patient.appointments.index')
            ->with('success', 'Appointment cancelled successfully.');
    }

    /**
     * Delete a cancelled appointment from patient's view.
     */
    public function destroy(Appointment $appointment): RedirectResponse
    {
        $patient = Auth::guard('patient')->user();

        // Ensure the appointment belongs to the authenticated patient
        if ($appointment->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to appointment.');
        }

        // Only allow deletion of cancelled appointments
        if ($appointment->status !== 'cancelled') {
            return back()->with('error', 'Only cancelled appointments can be deleted.');
        }

        // Store appointment details for success message
        $appointmentNumber = $appointment->appointment_number;
        $appointmentDate = $appointment->appointment_date->format('M d, Y');

        // Delete the appointment
        $appointment->delete();

        return redirect()->route('patient.appointments.index')
            ->with('success', "Appointment #{$appointmentNumber} scheduled for {$appointmentDate} has been deleted successfully.");
    }

    /**
     * Get available time slots for a doctor on a specific date.
     */
    public function getAvailableSlots($doctorId, Request $request)
    {
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
        ]);

        $doctor = Doctor::findOrFail($doctorId);
        $date = Carbon::parse($request->date);

        // Get working hours (you might want to store this in doctor's profile)
        $workingHours = [
            'start' => '09:00',
            'end' => '17:00',
            'slot_duration' => 30, // minutes
        ];

        // Generate time slots
        $slots = [];
        $current = Carbon::parse($date->format('Y-m-d') . ' ' . $workingHours['start']);
        $end = Carbon::parse($date->format('Y-m-d') . ' ' . $workingHours['end']);

        while ($current->lt($end)) {
            $timeSlot = $current->format('H:i');
            
            // Check if slot is available
            $isBooked = Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $request->date)
                ->where('appointment_time', $timeSlot)
                ->where('status', '!=', 'cancelled')
                ->exists();

            if (!$isBooked) {
                $slots[] = [
                    'time' => $timeSlot,
                    'display' => $current->format('g:i A'),
                ];
            }

            $current->addMinutes($workingHours['slot_duration']);
        }

        return response()->json($slots);
    }

    /**
     * Get doctors by department.
     */
    public function getDoctorsByDepartment($departmentId)
    {
        // Validate department exists
        $department = Department::findOrFail($departmentId);

        $doctors = Doctor::where('department_id', $departmentId)
            ->where('is_active', true)
            ->select('id', 'first_name', 'last_name', 'specialization')
            ->get()
            ->map(function ($doctor) {
                return [
                    'id' => $doctor->id,
                    'name' => $doctor->full_name,
                    'specialization' => $doctor->specialization,
                ];
            });

        return response()->json($doctors);
    }

    /**
     * Generate a unique appointment number.
     */
    private function generateAppointmentNumber(): string
    {
        do {
            $number = 'APT' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Appointment::where('appointment_number', $number)->exists());

        return $number;
    }
}
