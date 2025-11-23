<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use App\Services\HospitalEmailNotificationService;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AppointmentsController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor', 'department']);

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filter by online vs in-person
        if ($request->filled('is_online')) {
            $query->where('is_online', $request->boolean('is_online'));
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('date_from')) {
            $dateFrom = parseDateInput($request->date_from);
            $query->where('appointment_date', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = parseDateInput($request->date_to);
            $query->where('appointment_date', '<=', $dateTo);
        }

        if ($request->filled('search')) {
            $query->whereHas('patient', function($q) use ($request) {
                $q->where('first_name', 'like', "%{$request->search}%")
                  ->orWhere('last_name', 'like', "%{$request->search}%")
                  ->orWhere('patient_id', 'like', "%{$request->search}%");
            })->orWhere('appointment_number', 'like', "%{$request->search}%");
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(20);

        $departments = Department::active()->ordered()->get();
        $doctors = Doctor::ordered()->get();

        return view('admin.appointments.index', compact('appointments', 'departments', 'doctors'));
    }

    /**
     * Get the current user's department ID for any role
     */
    private function getUserDepartmentId()
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        if (!$user) {
            return null;
        }
        
        // Admins should see all doctors regardless of department
        if ($user->role === 'admin' || ($user->is_admin ?? false)) {
            return null;
        }
        
        // For doctors, get department from doctors table
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            return $doctor ? $doctor->department_id : null;
        }
        
        // For other roles (nurse, staff, etc.), get from users table
        return $user->department_id;
    }


    public function create()
    {
        $departmentId = $this->getUserDepartmentId();
        
        $patientsQuery = Patient::active();
        if ($departmentId) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        
        $departments = Department::active()->ordered()->get();
        
        $doctorsQuery = Doctor::query();
        if ($departmentId) {
            $doctorsQuery->where('department_id', $departmentId);
        }
        $doctors = $doctorsQuery->ordered()->get();

        return view('admin.appointments.create', compact('patients', 'departments', 'doctors'));
    }

    public function store(Request $request, HospitalEmailNotificationService $emailService)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'department_id' => 'required|exists:departments,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|date_format:H:i',
            'type' => 'required|in:consultation,followup,emergency,checkup,surgery',
            'reason' => 'required|string',
            'symptoms' => 'nullable|string',
            'notes' => 'nullable|string',
            'fee' => 'nullable|numeric|min:0',
            'is_online' => 'boolean',
            'meeting_link' => 'nullable|url|max:500',
            'meeting_platform' => 'nullable|in:zoom,google_meet,teams,whereby,custom'
        ]);

        // Validate that meeting link is provided if online consultation
        if ($request->boolean('is_online') && empty($request->meeting_link)) {
            return redirect()->back()
                ->withErrors(['meeting_link' => 'Meeting link is required for online consultations.'])
                ->withInput();
        }

        $data = $request->all();
        $data['appointment_number'] = Appointment::generateAppointmentNumber();
        $data['status'] = 'pending';
        $data['is_online'] = $request->boolean('is_online', false);

        $appointment = Appointment::create($data);
        
        // Load relationships for email notifications
        $appointment->load(['patient', 'doctor', 'department']);
        
        // Handle emergency admission notifications
        if ($appointment->type === 'emergency') {
            $this->handleEmergencyAdmissionNotifications($appointment, $emailService);
        }
        
        // Send email notifications if enabled
        if (config('hospital.notifications.appointment_confirmation.enabled', true)) {
            try {
                // Send confirmation to patient
                if (config('hospital.notifications.appointment_confirmation.send_to_patient', true)) {
                    $emailService->sendAppointmentConfirmation($appointment);
                }
                
                // Send notification to doctor
                if (config('hospital.notifications.appointment_confirmation.send_to_doctor', true) && $appointment->doctor) {
                    $emailService->notifyDoctorNewAppointment($appointment, $appointment->doctor);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the appointment creation
                \Log::error('Failed to send appointment confirmation emails', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()
            ->route('admin.appointments.index')
            ->with('success', 'Appointment created successfully! Email confirmations have been sent.');
    }

    public function show($id)
    {
        $appointment = Appointment::with(['patient', 'doctor.department'])->findOrFail($id);
        
        return view('admin.appointments.show', compact('appointment'));
    }

    public function edit($id)
    {
        $appointment = Appointment::with(['patient', 'doctor.department'])->findOrFail($id);
        $departmentId = $this->getUserDepartmentId();
        
        // Filter patients by department
        $patientsQuery = Patient::active();
        if ($departmentId) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        
        $departments = Department::active()->ordered()->get();
        
        // Filter doctors by department
        $doctorsQuery = Doctor::query();
        if ($departmentId) {
            $doctorsQuery->where('department_id', $departmentId);
        }
        $doctors = $doctorsQuery->ordered()->get();

        return view('admin.appointments.edit', compact('appointment', 'patients', 'departments', 'doctors'));
    }

    public function update(Request $request, $id, HospitalEmailNotificationService $emailService)
    {
        $appointment = Appointment::findOrFail($id);
        
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'department_id' => 'required|exists:departments,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required|date_format:H:i',
            'type' => 'required|in:consultation,followup,emergency,checkup,surgery',
            'status' => 'required|in:pending,confirmed,completed,cancelled,rescheduled',
            'reason' => 'required|string',
            'symptoms' => 'nullable|string',
            'notes' => 'nullable|string',
            'fee' => 'nullable|numeric|min:0',
            'is_online' => 'boolean',
            'meeting_link' => 'nullable|url|max:500',
            'meeting_platform' => 'nullable|in:zoom,google_meet,teams,whereby,custom',
            'prescription' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'follow_up_instructions' => 'nullable|string',
            'next_appointment_date' => 'nullable|date|after:appointment_date'
        ]);

        // Validate that meeting link is provided if online consultation
        if ($request->boolean('is_online') && empty($request->meeting_link)) {
            return redirect()->back()
                ->withErrors(['meeting_link' => 'Meeting link is required for online consultations.'])
                ->withInput();
        }

        // Store original values to detect changes
        $oldStatus = $appointment->status;
        $oldDate = $appointment->appointment_date;
        $oldTime = $appointment->appointment_time;
        
        $appointment->update($request->all());
        $appointment->load(['patient', 'doctor', 'department']);
        
        // Send notifications for status changes
        if ($oldStatus !== $appointment->status) {
            $this->handleStatusChangeNotifications($appointment, $oldStatus, $emailService);
        }
        
        // Send notifications for rescheduling
        if ($oldDate !== $appointment->appointment_date || $oldTime !== $appointment->appointment_time) {
            $this->handleRescheduleNotifications($appointment, $oldDate, $oldTime, $emailService);
        }

        return redirect()
            ->route('admin.appointments.index')
            ->with('success', 'Appointment updated successfully! Notifications have been sent.');
    }

    public function destroy(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        // Only prevent deletion of completed appointments
        if ($appointment->status === 'completed') {
            return response()->json([
                'success' => false,
                'message' => 'Completed appointments cannot be deleted!'
            ], 400);
        }

        $appointment->delete();
        
        return response()->json([
            'success' => true,
            'message' => 'Appointment deleted successfully!'
        ]);
    }

    public function updateStatus(Request $request, Appointment $appointment, HospitalEmailNotificationService $emailService)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled,rescheduled'
        ]);

        $oldStatus = $appointment->status;
        $appointment->update(['status' => $request->status]);
        $appointment->load(['patient', 'doctor', 'department']);
        
        // Send notifications for status changes
        $this->handleStatusChangeNotifications($appointment, $oldStatus, $emailService);

        return response()->json([
            'success' => true,
            'message' => 'Appointment status updated successfully!',
            'status' => $appointment->status
        ]);
    }

    public function checkIn(Appointment $appointment)
    {
        if ($appointment->canBeCheckedIn()) {
            $appointment->checkIn();
            
            return response()->json([
                'success' => true,
                'message' => 'Patient checked in successfully!',
                'check_in_time' => $appointment->check_in_time->format('Y-m-d H:i:s')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Cannot check in this appointment!'
        ], 400);
    }

    public function checkOut(Appointment $appointment)
    {
        if ($appointment->canBeCheckedOut()) {
            $appointment->checkOut();
            
            return response()->json([
                'success' => true,
                'message' => 'Patient checked out successfully!',
                'check_out_time' => $appointment->check_out_time->format('Y-m-d H:i:s')
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Cannot check out this appointment!'
        ], 400);
    }

    public function calendar()
    {
        $appointments = Appointment::with(['patient', 'doctor'])
            ->whereBetween('appointment_date', [
                now()->startOfMonth()->subDays(7),
                now()->endOfMonth()->addDays(7)
            ])
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'title' => $appointment->patient->full_name . ' - ' . $appointment->doctor->full_name,
                    'start' => $appointment->appointment_date->format('Y-m-d') . 'T' . $appointment->appointment_time->format('H:i:s'),
                    'backgroundColor' => $this->getStatusColor($appointment->status),
                    'borderColor' => $this->getStatusColor($appointment->status),
                    'extendedProps' => [
                        'patient' => $appointment->patient->full_name,
                        'doctor' => $appointment->doctor->full_name,
                        'department' => $appointment->department->name,
                        'status' => $appointment->status,
                        'type' => $appointment->type,
                        'reason' => $appointment->reason
                    ]
                ];
            });

        return view('admin.appointments.calendar', compact('appointments'));
    }

    public function todayAppointments()
    {
        $appointments = Appointment::with(['patient', 'doctor', 'department'])
            ->today()
            ->orderBy('appointment_time')
            ->get();

        return view('admin.appointments.today', compact('appointments'));
    }

    public function reschedule(Request $request, Appointment $appointment)
    {
        $request->validate([
            'new_date' => 'required|date|after_or_equal:today',
            'new_time' => 'required|date_format:H:i',
            'reason' => 'nullable|string'
        ]);

        $appointment->update([
            'appointment_date' => $request->new_date,
            'appointment_time' => $request->new_time,
            'status' => 'rescheduled',
            'notes' => ($appointment->notes ?? '') . "\n\nRescheduled: " . ($request->reason ?? 'No reason provided')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Appointment rescheduled successfully!'
        ]);
    }

    private function getStatusColor($status)
    {
        $colors = [
            'pending' => '#ffc107',
            'confirmed' => '#17a2b8',
            'completed' => '#28a745',
            'cancelled' => '#dc3545',
            'rescheduled' => '#6c757d'
        ];

        return $colors[$status] ?? '#6c757d';
    }

    public function getDoctorsByDepartment(Department $department)
    {
        $doctors = $department->doctors()->active()->ordered()->get();
        
        return response()->json($doctors);
    }

    public function getDoctorAvailability(Doctor $doctor, Request $request)
    {
        $date = Carbon::parse($request->date);
        $dayOfWeek = strtolower($date->format('l'));
        
        $availability = $doctor->getAvailableTimesOn($dayOfWeek);
        
        // Get existing appointments for this doctor on this date
        $existingAppointments = $doctor->appointments()
            ->whereDate('appointment_date', $date)
            ->where('status', '!=', 'cancelled')
            ->pluck('appointment_time')
            ->map(function($time) {
                return Carbon::parse($time)->format('H:i');
            })
            ->toArray();
        
        // Filter out booked times
        $availableTimes = array_diff($availability, $existingAppointments);
        
        return response()->json(array_values($availableTimes));
    }

    public function confirm(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        // Allow confirmation of pending appointments and reconfirmation of cancelled appointments
        if (!in_array($appointment->status, ['pending', 'cancelled'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or cancelled appointments can be confirmed!'
            ], 400);
        }
        
        $originalStatus = $appointment->status;
        $updateData = ['status' => 'confirmed'];
        
        // Add a note if this is a reconfirmation of a cancelled appointment
        if ($originalStatus === 'cancelled') {
            $updateData['notes'] = ($appointment->notes ?? '') . "\n\nReconfirmed from cancelled status on " . now()->format('Y-m-d H:i:s');
        }
        
        $appointment->update($updateData);
        
        $message = $originalStatus === 'cancelled' ? 'Appointment reconfirmed successfully!' : 'Appointment confirmed successfully!';
        
        return response()->json([
            'success' => true,
            'message' => $message,
            'status' => $appointment->status
        ]);
    }

    public function cancel(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return response()->json([
                'success' => false,
                'message' => 'Only pending or confirmed appointments can be cancelled!'
            ], 400);
        }
        
        $updateData = ['status' => 'cancelled'];
        
        // Add cancellation reason if provided
        if ($request->has('reason') && $request->reason) {
            $updateData['notes'] = ($appointment->notes ?? '') . "\n\nCancellation reason: " . $request->reason;
        }
        
        $appointment->update($updateData);
        
        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully!',
            'status' => $appointment->status
        ]);
    }

    public function complete(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        if ($appointment->status !== 'confirmed') {
            return response()->json([
                'success' => false,
                'message' => 'Only confirmed appointments can be completed!'
            ], 400);
        }
        
        $appointment->update(['status' => 'completed']);
        
        return response()->json([
            'success' => true,
            'message' => 'Appointment completed successfully!',
            'status' => $appointment->status
        ]);
    }
    
    /**
     * Handle notifications for appointment status changes
     */
    private function handleStatusChangeNotifications($appointment, $oldStatus, $emailService)
    {
        try {
            switch ($appointment->status) {
                case 'cancelled':
                    if (config('hospital.notifications.appointment_cancellation.enabled', true)) {
                        $emailService->sendAppointmentCancellation($appointment);
                        
                        // Notify doctor about cancellation
                        if ($appointment->doctor && config('hospital.staff_notifications.appointment_changes.enabled', true)) {
                            $emailService->notifyDoctorAppointmentCancelled($appointment, $appointment->doctor);
                        }
                    }
                    break;
                    
                case 'confirmed':
                    if (config('hospital.notifications.appointment_confirmation.enabled', true)) {
                        $emailService->sendAppointmentConfirmation($appointment);
                    }
                    break;
                    
                case 'completed':
                    if (config('hospital.notifications.appointment_completion.enabled', true)) {
                        $emailService->sendAppointmentCompletion($appointment);
                    }
                    break;
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send appointment status change notifications', [
                'appointment_id' => $appointment->id,
                'old_status' => $oldStatus,
                'new_status' => $appointment->status,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle notifications for appointment rescheduling
     */
    private function handleRescheduleNotifications($appointment, $oldDate, $oldTime, $emailService)
    {
        try {
            if (config('hospital.notifications.appointment_reschedule.enabled', true)) {
                $emailService->sendAppointmentReschedule($appointment, $oldDate, $oldTime);
                
                // Notify doctor about rescheduling
                if ($appointment->doctor && config('hospital.staff_notifications.appointment_changes.enabled', true)) {
                    $emailService->notifyDoctorAppointmentRescheduled($appointment, $appointment->doctor, $oldDate, $oldTime);
                }
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send appointment reschedule notifications', [
                'appointment_id' => $appointment->id,
                'old_date' => $oldDate,
                'old_time' => $oldTime,
                'new_date' => $appointment->appointment_date,
                'new_time' => $appointment->appointment_time,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Handle notifications for emergency admissions
     */
    private function handleEmergencyAdmissionNotifications($appointment, $emailService)
    {
        try {
            if (!config('hospital.notifications.emergency_admissions.enabled', true)) {
                return;
            }
            
            $patient = $appointment->patient;
            
            // Prepare admission information
            $admissionInfo = [
                'emergency_type' => $appointment->reason ?? 'Medical Emergency',
                'symptoms' => $appointment->symptoms ?? 'Emergency condition requiring immediate attention',
                'priority_level' => $this->determinePriorityLevel($appointment),
                'doctor_name' => $appointment->doctor ? $appointment->doctor->full_name : 'Emergency Team',
                'department_name' => $appointment->department ? $appointment->department->name : 'Emergency Department',
                'room_number' => 'Emergency Ward', // Could be dynamic based on availability
                'vital_signs' => $appointment->vital_signs ?? [],
                'emergency_contact' => $patient->emergency_contact ?? 'Not provided',
                'medical_history' => $this->getPatientMedicalHistorySummary($patient),
                'condition_summary' => $appointment->notes ?? 'Requires immediate medical evaluation',
                'specialist_required' => $this->getRequiredSpecialist($appointment),
                'estimated_treatment_time' => 'To be determined',
                'emergency_protocol' => 'Emergency admission protocol activated'
            ];
            
            // Get critical staff to notify
            $criticalStaff = $this->getCriticalStaffForEmergency($appointment);
            
            foreach ($criticalStaff as $staff) {
                if ($staff->role === 'emergency_staff' || $staff->role === 'nurse') {
                    $emailService->sendEmergencyAdmissionAlert($patient, $admissionInfo, $staff);
                } elseif ($staff->role === 'department_head' || $staff->role === 'specialist') {
                    $emailService->sendCriticalCareNotification($patient, $admissionInfo, $staff);
                }
            }
            
            // Log emergency admission
            \Log::info('Emergency admission notifications sent', [
                'appointment_id' => $appointment->id,
                'patient_id' => $patient->id,
                'staff_notified' => count($criticalStaff),
                'priority_level' => $admissionInfo['priority_level']
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to send emergency admission notifications', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Determine priority level based on appointment details
     */
    private function determinePriorityLevel($appointment)
    {
        // Simple priority determination logic
        $symptoms = strtolower($appointment->symptoms ?? '');
        $reason = strtolower($appointment->reason ?? '');
        
        $criticalKeywords = ['cardiac', 'stroke', 'bleeding', 'unconscious', 'chest pain', 'respiratory distress', 'trauma'];
        $highKeywords = ['severe pain', 'high fever', 'difficulty breathing', 'allergic reaction'];
        
        foreach ($criticalKeywords as $keyword) {
            if (strpos($symptoms . ' ' . $reason, $keyword) !== false) {
                return 'Critical';
            }
        }
        
        foreach ($highKeywords as $keyword) {
            if (strpos($symptoms . ' ' . $reason, $keyword) !== false) {
                return 'High';
            }
        }
        
        return 'Moderate';
    }
    
    /**
     * Get required specialist based on emergency type
     */
    private function getRequiredSpecialist($appointment)
    {
        $symptoms = strtolower($appointment->symptoms ?? '');
        $reason = strtolower($appointment->reason ?? '');
        
        $specialistMapping = [
            'cardiology' => ['cardiac', 'heart', 'chest pain', 'cardiovascular'],
            'neurology' => ['stroke', 'neurological', 'seizure', 'head injury'],
            'orthopedics' => ['fracture', 'trauma', 'bone', 'joint'],
            'surgery' => ['surgical', 'appendicitis', 'internal bleeding']
        ];
        
        foreach ($specialistMapping as $specialty => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($symptoms . ' ' . $reason, $keyword) !== false) {
                    return ucfirst($specialty);
                }
            }
        }
        
        return 'General Emergency Medicine';
    }
    
    /**
     * Get patient medical history summary
     */
    private function getPatientMedicalHistorySummary($patient)
    {
        // Get recent medical records or basic patient info
        $conditions = [];
        
        if ($patient->date_of_birth) {
            $age = $patient->date_of_birth->age;
            $conditions[] = "Age: {$age}";
        }
        
        // You could expand this to include:
        // - Known allergies
        // - Chronic conditions
        // - Current medications
        // - Recent medical records
        
        return !empty($conditions) ? implode(', ', $conditions) : 'No known allergies or conditions';
    }
    
    /**
     * Get critical staff members to notify for emergency
     */
    private function getCriticalStaffForEmergency($appointment)
    {
        // In a real implementation, you would query the User model
        // For now, we'll return a mock array - you should replace this with actual database queries
        
        // This is a placeholder - in reality you'd query users with specific roles
        $criticalStaff = [];
        
        // Get emergency staff
        $emergencyStaff = \App\Models\User::where('role', 'admin') // Replace with actual emergency staff role
            ->orWhere('email', config('hospital.emergency_notifications.primary_contact'))
            ->get();
        
        foreach ($emergencyStaff as $staff) {
            $staff->role = 'emergency_staff'; // Set role for notification type determination
            $criticalStaff[] = $staff;
        }
        
        // Add department head if available
        if ($appointment->department) {
            // You could have a department_head_id in the departments table
            // For now, we'll use admin users as a placeholder
            $departmentHeads = \App\Models\User::where('role', 'admin')->take(1)->get();
            foreach ($departmentHeads as $head) {
                $head->role = 'department_head';
                $criticalStaff[] = $head;
            }
        }
        
        return collect($criticalStaff)->unique('id')->all();
    }
}
