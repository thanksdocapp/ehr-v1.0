<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentApiController extends BaseApiController
{
    /**
     * Get patient's appointments.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'nullable|integer|min:1|max:100',
            'status' => 'nullable|in:pending,confirmed,cancelled,completed',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'upcoming' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $user = $request->user();
            $query = Appointment::with(['doctor.department']);

            // Filter by user type
            if ($user instanceof Patient) {
                $query->where('patient_id', $user->id);
            } elseif (isset($user->id)) {
                // For staff/admin, they can see all appointments
                // Or you might want to restrict this based on roles
            }

            // Filter by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filter by date range
            if ($request->has('date_from')) {
                $query->where('appointment_date', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('appointment_date', '<=', $request->date_to);
            }

            // Show only upcoming appointments
            if ($request->boolean('upcoming')) {
                $query->where(function ($q) {
                    $q->where('appointment_date', '>', now()->format('Y-m-d'))
                      ->orWhere(function ($subQ) {
                          $subQ->where('appointment_date', '=', now()->format('Y-m-d'))
                               ->where('appointment_time', '>', now()->format('H:i:s'));
                      });
                });
            }

            $perPage = $request->get('per_page', 15);
            $appointments = $query->orderBy('appointment_date', 'desc')
                                  ->orderBy('appointment_time', 'desc')
                                  ->paginate($perPage);

            // Add additional information to each appointment
            $appointments->getCollection()->transform(function ($appointment) {
                $appointment->is_past = $this->isAppointmentPast($appointment);
                $appointment->can_cancel = $this->canCancelAppointment($appointment);
                $appointment->can_reschedule = $this->canRescheduleAppointment($appointment);
                return $appointment;
            });

            return $this->sendPaginatedResponse($appointments, 'Appointments retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve appointments: ' . $e->getMessage());
        }
    }

    /**
     * Get specific appointment details.
     */
    public function show($id, Request $request)
    {
        try {
            $user = $request->user();
            $query = Appointment::with(['doctor.department', 'patient']);

            // Access control
            if ($user instanceof Patient) {
                $query->where('patient_id', $user->id);
            }

            $appointment = $query->find($id);

            if (!$appointment) {
                return $this->sendNotFound('Appointment');
            }

            // Add additional information
            $appointment->is_past = $this->isAppointmentPast($appointment);
            $appointment->can_cancel = $this->canCancelAppointment($appointment);
            $appointment->can_reschedule = $this->canRescheduleAppointment($appointment);
            $appointment->time_until_appointment = $this->getTimeUntilAppointment($appointment);

            return $this->sendResponse($appointment, 'Appointment details retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve appointment details: ' . $e->getMessage());
        }
    }

    /**
     * Create a new appointment.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'symptoms' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:500',
            // If booking for someone else (admin/staff feature)
            'patient_name' => 'nullable|string|max:255',
            'patient_phone' => 'nullable|string|max:20',
            'patient_email' => 'nullable|email',
            'is_online' => 'nullable|boolean',
            'meeting_link' => 'nullable|url|max:500',
            'meeting_platform' => 'nullable|in:zoom,google_meet,teams,whereby,custom'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $user = $request->user();
            
            // Verify doctor exists and is active
            $doctor = Doctor::where('id', $request->doctor_id)
                           ->where('is_active', true)
                           ->first();

            if (!$doctor) {
                return $this->sendError('Doctor not found or not available', [], 404);
            }

            // Check if doctor has schedule for the requested day
            $dayOfWeek = date('w', strtotime($request->appointment_date));
            $doctorSchedule = $doctor->schedules()
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->where('start_time', '<=', $request->appointment_time)
                ->where('end_time', '>', $request->appointment_time)
                ->first();

            if (!$doctorSchedule) {
                return $this->sendError('Doctor is not available at the requested time', [], 400);
            }

            // Check for existing appointment at the same time
            $existingAppointment = Appointment::where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $request->appointment_date)
                ->where('appointment_time', $request->appointment_time)
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($existingAppointment) {
                return $this->sendError('Time slot is already booked', [], 409);
            }

            // Prepare appointment data
            $appointmentData = [
                'appointment_number' => $this->generateAppointmentNumber(),
                'doctor_id' => $request->doctor_id,
                'department_id' => $doctor->department_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'symptoms' => $request->symptoms,
                'notes' => $request->notes,
                'status' => 'pending'
            ];

            // Handle patient information
            if ($user instanceof Patient) {
                $appointmentData['patient_id'] = $user->id;
                $appointmentData['patient_name'] = $user->first_name . ' ' . $user->last_name;
                $appointmentData['patient_phone'] = $user->phone;
                $appointmentData['patient_email'] = $user->email;
            } else {
                // Admin/staff booking for walk-in patient
                $appointmentData['patient_name'] = $request->patient_name;
                $appointmentData['patient_phone'] = $request->patient_phone;
                $appointmentData['patient_email'] = $request->patient_email;
            }

            $appointment = Appointment::create($appointmentData);

            // Load relationships for response
            $appointment->load(['doctor.department']);

            return $this->sendCreated($appointment, 'Appointment booked successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to book appointment: ' . $e->getMessage());
        }
    }

    /**
     * Update appointment (reschedule).
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'symptoms' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:500',
            'is_online' => 'nullable|boolean',
            'meeting_link' => 'nullable|url|max:500',
            'meeting_platform' => 'nullable|in:zoom,google_meet,teams,whereby,custom'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $user = $request->user();
            $query = Appointment::query();

            // Access control
            if ($user instanceof Patient) {
                $query->where('patient_id', $user->id);
            }

            $appointment = $query->find($id);

            if (!$appointment) {
                return $this->sendNotFound('Appointment');
            }

            // Check if appointment can be rescheduled
            if (!$this->canRescheduleAppointment($appointment)) {
                return $this->sendError('This appointment cannot be rescheduled', [], 400);
            }

            // Verify doctor availability for new time
            $doctor = $appointment->doctor;
            $dayOfWeek = date('w', strtotime($request->appointment_date));
            
            $doctorSchedule = $doctor->schedules()
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->where('start_time', '<=', $request->appointment_time)
                ->where('end_time', '>', $request->appointment_time)
                ->first();

            if (!$doctorSchedule) {
                return $this->sendError('Doctor is not available at the requested time', [], 400);
            }

            // Check for conflicts with other appointments
            $conflictingAppointment = Appointment::where('doctor_id', $appointment->doctor_id)
                ->where('appointment_date', $request->appointment_date)
                ->where('appointment_time', $request->appointment_time)
                ->where('id', '!=', $appointment->id)
                ->where('status', '!=', 'cancelled')
                ->first();

            if ($conflictingAppointment) {
                return $this->sendError('Time slot is already booked', [], 409);
            }

            // Update appointment
            $appointment->update([
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'symptoms' => $request->symptoms,
                'notes' => $request->notes,
                'status' => 'pending' // Reset to pending after reschedule
            ]);

            $appointment->load(['doctor.department']);

            return $this->sendUpdated($appointment, 'Appointment rescheduled successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to reschedule appointment: ' . $e->getMessage());
        }
    }

    /**
     * Cancel appointment.
     */
    public function cancel(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'cancellation_reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $user = $request->user();
            $query = Appointment::query();

            // Access control
            if ($user instanceof Patient) {
                $query->where('patient_id', $user->id);
            }

            $appointment = $query->find($id);

            if (!$appointment) {
                return $this->sendNotFound('Appointment');
            }

            // Check if appointment can be cancelled
            if (!$this->canCancelAppointment($appointment)) {
                return $this->sendError('This appointment cannot be cancelled', [], 400);
            }

            // Update appointment status
            $appointment->update([
                'status' => 'cancelled',
                'cancellation_reason' => $request->cancellation_reason,
                'cancelled_at' => now(),
                'cancelled_by' => $user->id ?? null
            ]);

            return $this->sendResponse(null, 'Appointment cancelled successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to cancel appointment: ' . $e->getMessage());
        }
    }

    /**
     * Get available time slots for a doctor on a specific date.
     */
    public function getAvailableSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'date' => 'required|date|after_or_equal:today'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $doctor = Doctor::where('id', $request->doctor_id)
                           ->where('is_active', true)
                           ->first();

            if (!$doctor) {
                return $this->sendNotFound('Doctor');
            }

            $date = $request->date;
            $dayOfWeek = date('w', strtotime($date));

            // Get doctor's schedules for the day
            $schedules = $doctor->schedules()
                ->where('day_of_week', $dayOfWeek)
                ->where('is_active', true)
                ->orderBy('start_time')
                ->get();

            if ($schedules->isEmpty()) {
                return $this->sendResponse([], 'No availability for the selected date');
            }

            // Get existing appointments
            $existingAppointments = Appointment::where('doctor_id', $request->doctor_id)
                ->where('appointment_date', $date)
                ->where('status', '!=', 'cancelled')
                ->pluck('appointment_time')
                ->toArray();

            $availableSlots = [];
            $slotDuration = 30; // 30 minutes per slot

            foreach ($schedules as $schedule) {
                $startTime = Carbon::createFromFormat('H:i:s', $schedule->start_time);
                $endTime = Carbon::createFromFormat('H:i:s', $schedule->end_time);

                while ($startTime->lt($endTime)) {
                    $timeSlot = $startTime->format('H:i:s');
                    $formattedTime = $startTime->format('h:i A');

                    if (!in_array($timeSlot, $existingAppointments)) {
                        // Check if slot is not in the past for today's date
                        if ($date > now()->format('Y-m-d') || 
                            ($date == now()->format('Y-m-d') && $startTime->gt(now()))) {
                            $availableSlots[] = [
                                'time' => $timeSlot,
                                'formatted_time' => $formattedTime,
                                'is_available' => true
                            ];
                        }
                    }

                    $startTime->addMinutes($slotDuration);
                }
            }

            $data = [
                'doctor' => $doctor->only(['id', 'name', 'specialization']),
                'date' => $date,
                'available_slots' => $availableSlots
            ];

            return $this->sendResponse($data, 'Available slots retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve available slots: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique appointment number.
     */
    private function generateAppointmentNumber()
    {
        do {
            $number = 'APT' . date('Ymd') . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Appointment::where('appointment_number', $number)->exists());

        return $number;
    }

    /**
     * Check if appointment is in the past.
     */
    private function isAppointmentPast($appointment)
    {
        $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i:s', 
            $appointment->appointment_date . ' ' . $appointment->appointment_time);
        
        return $appointmentDateTime->lt(now());
    }

    /**
     * Check if appointment can be cancelled.
     */
    private function canCancelAppointment($appointment)
    {
        // Can't cancel if already cancelled or completed
        if (in_array($appointment->status, ['cancelled', 'completed'])) {
            return false;
        }

        // Can't cancel if appointment is in the past
        if ($this->isAppointmentPast($appointment)) {
            return false;
        }

        // Can't cancel if appointment is within 2 hours
        $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i:s', 
            $appointment->appointment_date . ' ' . $appointment->appointment_time);
        
        return $appointmentDateTime->gt(now()->addHours(2));
    }

    /**
     * Check if appointment can be rescheduled.
     */
    private function canRescheduleAppointment($appointment)
    {
        // Can't reschedule if cancelled or completed
        if (in_array($appointment->status, ['cancelled', 'completed'])) {
            return false;
        }

        // Can't reschedule if appointment is in the past
        if ($this->isAppointmentPast($appointment)) {
            return false;
        }

        // Can't reschedule if appointment is within 4 hours
        $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i:s', 
            $appointment->appointment_date . ' ' . $appointment->appointment_time);
        
        return $appointmentDateTime->gt(now()->addHours(4));
    }

    /**
     * Get time until appointment.
     */
    private function getTimeUntilAppointment($appointment)
    {
        $appointmentDateTime = Carbon::createFromFormat('Y-m-d H:i:s', 
            $appointment->appointment_date . ' ' . $appointment->appointment_time);
        
        if ($appointmentDateTime->lt(now())) {
            return null;
        }

        return $appointmentDateTime->diffForHumans();
    }
}
