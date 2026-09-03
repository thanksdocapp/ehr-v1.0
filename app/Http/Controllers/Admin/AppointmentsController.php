<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use App\Services\HospitalEmailNotificationService;
use App\Services\SlotAvailabilityService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class AppointmentsController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor', 'department']);

        // Apply visibility rules based on user role (uses patient-department-doctor logic)
        // Admins will see all records, doctors/staff will see only their department records
        $user = Auth::user();
        if ($user) {
            $query->visibleTo($user);
        } else {
            $query->whereRaw('1 = 0'); // No results if no user
        }

        // Stats for the header cards (visibility-aware, not filter-dependent)
        $statsBase = clone $query;
        $stats = [
            'total_appointments' => (clone $statsBase)->count(),
            'pending_appointments' => (clone $statsBase)->where('status', 'pending')->count(),
            'confirmed_appointments' => (clone $statsBase)->where('status', 'confirmed')->count(),
            'today_appointments' => (clone $statsBase)->whereDate('appointment_date', Carbon::today())->count(),
        ];

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filter by consultation type
        if ($request->filled('consultation_type')) {
            $ct = $request->consultation_type;
            if ($ct === 'phone') {
                $ct = 'telephone';
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('appointments', 'consultation_type')) {
                $query->where('consultation_type', $ct);
            } else {
                if ($ct === 'online') {
                    $query->where('is_online', true);
                } elseif ($ct === 'in_person') {
                    $query->where('is_online', false);
                }
            }
        } elseif ($request->filled('is_online')) {
            $query->where('is_online', $request->boolean('is_online'));
        }

        if ($request->filled('meeting_platform')) {
            $query->where('meeting_platform', $request->meeting_platform);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Patient name/email/phone quick filter (separate from global "search")
        if ($request->filled('patient_name')) {
            $term = trim((string) $request->patient_name);
            $query->whereHas('patient', function ($q) use ($term) {
                $q->where(function ($qq) use ($term) {
                    $qq->where('first_name', 'like', "%{$term}%")
                       ->orWhere('last_name', 'like', "%{$term}%")
                       ->orWhere('email', 'like', "%{$term}%")
                       ->orWhere('phone', 'like', "%{$term}%")
                       ->orWhere('patient_id', 'like', "%{$term}%");
                });
            });
        }

        // Date range shortcuts
        if ($request->filled('date_range')) {
            $range = (string) $request->date_range;
            $today = Carbon::today();
            $start = null;
            $end = null;

            switch ($range) {
                case 'today':
                    $start = $today->copy();
                    $end = $today->copy();
                    break;
                case 'tomorrow':
                    $start = $today->copy()->addDay();
                    $end = $today->copy()->addDay();
                    break;
                case 'this_week':
                    $start = $today->copy()->startOfWeek();
                    $end = $today->copy()->endOfWeek();
                    break;
                case 'next_week':
                    $start = $today->copy()->addWeek()->startOfWeek();
                    $end = $today->copy()->addWeek()->endOfWeek();
                    break;
                case 'this_month':
                    $start = $today->copy()->startOfMonth();
                    $end = $today->copy()->endOfMonth();
                    break;
                case 'next_month':
                    $start = $today->copy()->addMonthNoOverflow()->startOfMonth();
                    $end = $today->copy()->addMonthNoOverflow()->endOfMonth();
                    break;
                case 'upcoming':
                    $start = $today->copy();
                    break;
                case 'past':
                    $end = $today->copy()->subDay();
                    break;
            }

            if ($start) {
                $query->whereDate('appointment_date', '>=', $start);
            }
            if ($end) {
                $query->whereDate('appointment_date', '<=', $end);
            }
        }

        if ($request->filled('date_from')) {
            $dateFrom = parseDateInput($request->date_from);
            $query->whereDate('appointment_date', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = parseDateInput($request->date_to);
            $query->whereDate('appointment_date', '<=', $dateTo);
        }

        if ($request->filled('date')) {
            $query->whereDate('appointment_date', $request->date);
        }

        if ($request->filled('time_from')) {
            $query->whereTime('appointment_time', '>=', $request->time_from);
        }

        if ($request->filled('time_to')) {
            $query->whereTime('appointment_time', '<=', $request->time_to);
        }

        if ($request->filled('has_medical_record')) {
            if ($request->has_medical_record === 'yes') {
                $query->whereHas('medicalRecord');
            } elseif ($request->has_medical_record === 'no') {
                $query->whereDoesntHave('medicalRecord');
            }
        }

        if ($request->filled('checked_in')) {
            if ($request->checked_in === 'yes') {
                $query->whereNotNull('check_in_time');
            } elseif ($request->checked_in === 'no') {
                $query->whereNull('check_in_time');
            }
        }

        if ($request->filled('checked_out')) {
            if ($request->checked_out === 'yes') {
                $query->whereNotNull('check_out_time');
            } elseif ($request->checked_out === 'no') {
                $query->whereNull('check_out_time');
            }
        }

        if ($request->filled('overdue')) {
            $query->whereDate('appointment_date', '<', Carbon::today())
                ->whereIn('status', ['pending', 'confirmed']);
        }

        if ($request->filled('fee_min')) {
            $query->where('fee', '>=', (float) $request->fee_min);
        }
        if ($request->filled('fee_max')) {
            $query->where('fee', '<=', (float) $request->fee_max);
        }

        if ($request->filled('reason')) {
            $term = trim((string) $request->reason);
            $query->where('reason', 'like', "%{$term}%");
        }
        if ($request->filled('symptoms')) {
            $term = trim((string) $request->symptoms);
            $query->where('symptoms', 'like', "%{$term}%");
        }

        if ($request->filled('search')) {
            $term = trim((string) $request->search);
            $query->where(function ($q) use ($term) {
                $q->where('appointment_number', 'like', "%{$term}%")
                  ->orWhereHas('patient', function ($pq) use ($term) {
                      $pq->where(function ($pp) use ($term) {
                          $pp->where('first_name', 'like', "%{$term}%")
                             ->orWhere('last_name', 'like', "%{$term}%")
                             ->orWhere('email', 'like', "%{$term}%")
                             ->orWhere('phone', 'like', "%{$term}%")
                             ->orWhere('patient_id', 'like', "%{$term}%");
                      });
                  })
                  ->orWhereHas('doctor', function ($dq) use ($term) {
                      $dq->where(function ($dd) use ($term) {
                          $dd->where('first_name', 'like', "%{$term}%")
                             ->orWhere('last_name', 'like', "%{$term}%")
                             ->orWhere('name', 'like', "%{$term}%");
                      });
                  });
            });
        }

        $appointments = $query->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->paginate(20);

        $departments = Department::active()->ordered()->get();
        $doctors = Doctor::ordered()->get();

        $consultationReportExclusionEnabled = Schema::hasColumn('appointments', 'exclude_from_consultation_report');
        $canManageConsultationReportExclusion = $this->userCanManageConsultationReportExclusion();

        return view('admin.appointments.index', compact('appointments', 'departments', 'doctors', 'stats', 'consultationReportExclusionEnabled', 'canManageConsultationReportExclusion'));
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

    /**
     * Consultation report exclusion (demo/training) is limited to full admin users, not doctors.
     */
    private function userCanManageConsultationReportExclusion(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return ($user->is_admin ?? false) || $user->role === 'admin';
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
            'type' => 'required|in:consultation,followup',
            'reason' => 'required|string',
            'symptoms' => 'nullable|string',
            'notes' => 'nullable|string',
            'fee' => 'nullable|numeric|min:0',
            'consultation_type' => 'nullable|in:in_person,online,telephone',
            'is_online' => 'boolean',
            'meeting_link' => 'nullable|url|max:500',
            'meeting_platform' => 'nullable|in:zoom,google_meet,teams,whereby,custom'
        ]);

        $consultationType = in_array($request->consultation_type, ['in_person', 'online', 'telephone'], true)
            ? $request->consultation_type
            : ($request->boolean('is_online') ? 'online' : 'in_person');
        $isOnline = $consultationType === 'online';

        // Check if Whereby is enabled - if so, we don't require manual meeting link
        $wherebyService = app(\App\Services\WherebyService::class);
        $wherebyEnabled = $wherebyService->isEnabled();

        // Validate that meeting link is provided if online consultation (except for Whereby which auto-generates)
        if ($isOnline && empty($request->meeting_link) && $request->meeting_platform !== 'whereby' && !$wherebyEnabled) {
            return redirect()->back()
                ->withErrors(['meeting_link' => 'Meeting link is required for online consultations.'])
                ->withInput();
        }

        try {
            app(SlotAvailabilityService::class)->assertSlotBookable(
                (int) $request->doctor_id,
                (string) $request->appointment_date,
                (string) $request->appointment_time,
                null,
                30,
                $consultationType,
                ignoreMinimumAdvance: true
            );
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        $data = $request->except(['consultation_type', 'is_online']);
        $data['appointment_number'] = Appointment::generateAppointmentNumber();
        $data['status'] = 'pending';
        $data['consultation_type'] = $consultationType;
        $data['is_online'] = $isOnline;
        if (!$isOnline) {
            $data['meeting_link'] = null;
            $data['meeting_platform'] = null;
        }

        $appointment = Appointment::create($data);

        // Track if we need to send emails manually (for Whereby appointments)
        $sendEmailsManually = false;

        // If this is an online appointment with Whereby platform and no meeting link, auto-generate
        if ($appointment->is_online && empty($appointment->meeting_link) && $appointment->meeting_platform === 'whereby' && $wherebyEnabled) {
            $sendEmailsManually = true; // Observer skipped email, we'll send after link is created
            try {
                $wherebyService->createMeetingForAppointment($appointment);
                $appointment->refresh(); // Reload to get the updated meeting_link
                \Log::info('Whereby meeting created for admin appointment', [
                    'appointment_id' => $appointment->id,
                    'meeting_link' => $appointment->meeting_link,
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create Whereby meeting for admin appointment', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        // Load relationships for email notifications
        $appointment->load(['patient', 'doctor', 'department']);

        // Handle emergency admission notifications
        if ($appointment->type === 'emergency') {
            $this->handleEmergencyAdmissionNotifications($appointment, $emailService);
        }

        // Send email notifications if enabled
        // For Whereby appointments, the observer skipped email so we send manually here
        // For non-Whereby appointments, the observer already sent emails
        $shouldSendEmails = config('hospital.notifications.appointment_confirmation.enabled', true);

        if ($shouldSendEmails && $sendEmailsManually) {
            // For Whereby appointments where observer skipped email
            if (empty($appointment->meeting_link)) {
                // Whereby link creation failed, still send emails but without meeting link
                \Log::warning('Sending appointment emails without Whereby link - link creation may have failed', [
                    'appointment_id' => $appointment->id
                ]);
            }

            try {
                // Send confirmation to patient
                if (config('hospital.notifications.appointment_confirmation.send_to_patient', true) && $appointment->patient && $appointment->patient->email) {
                    $emailService->sendAppointmentConfirmation($appointment);
                    \Log::info('Appointment confirmation email sent after Whereby processing', [
                        'appointment_id' => $appointment->id,
                        'has_meeting_link' => !empty($appointment->meeting_link)
                    ]);
                }

                // Send notification to doctor
                if (config('hospital.notifications.appointment_confirmation.send_to_doctor', true) && $appointment->doctor) {
                    $emailService->sendNewAppointmentToDoctor($appointment);
                    \Log::info('Doctor notification email sent after Whereby processing', [
                        'appointment_id' => $appointment->id
                    ]);
                }
            } catch (\Exception $e) {
                // Log error but don't fail the appointment creation
                \Log::error('Failed to send appointment confirmation emails', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        // Note: For non-Whereby appointments, emails are sent by AppointmentObserver

        return redirect()
            ->route('admin.appointments.index')
            ->with('success', 'Appointment created successfully! Email confirmations have been sent.');
    }

    public function show($id)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'department'])->findOrFail($id);
        
        // If AJAX request, return JSON
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json([
                'id' => $appointment->id,
                'patient' => [
                    'id' => $appointment->patient_id,
                    'full_name' => $appointment->patient->full_name ?? 'N/A'
                ],
                'doctor' => [
                    'id' => $appointment->doctor_id,
                    'full_name' => $appointment->doctor->full_name ?? 'N/A'
                ],
                'department' => [
                    'id' => $appointment->department_id,
                    'name' => $appointment->department->name ?? 'N/A'
                ],
                'appointment_date' => formatDateUk($appointment->appointment_date),
                'appointment_time' => $appointment->appointment_time->format('h:i A'),
                'status' => $appointment->status,
                'type' => $appointment->type ?? 'consultation',
                'reason' => $appointment->reason ?? '',
                'appointment_number' => $appointment->appointment_number ?? '',
                'is_online' => $appointment->is_online ?? false,
                'notes' => $appointment->notes ?? ''
            ]);
        }

        $consultationReportExclusionEnabled = Schema::hasColumn('appointments', 'exclude_from_consultation_report');
        $canManageConsultationReportExclusion = $this->userCanManageConsultationReportExclusion();

        return view('admin.appointments.show', compact('appointment', 'consultationReportExclusionEnabled', 'canManageConsultationReportExclusion'));
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

        $canManageConsultationReportExclusion = $this->userCanManageConsultationReportExclusion();
        $wherebyAutoGenerate = app(\App\Services\WherebyService::class)->isEnabled();

        return view('admin.appointments.edit', compact('appointment', 'patients', 'departments', 'doctors', 'canManageConsultationReportExclusion', 'wherebyAutoGenerate'));
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
            'type' => 'required|in:consultation,followup',
            'status' => 'required|in:pending,confirmed,completed,cancelled,rescheduled',
            'reason' => 'required|string',
            'symptoms' => 'nullable|string',
            'notes' => 'nullable|string',
            'fee' => 'nullable|numeric|min:0',
            'consultation_type' => 'nullable|in:in_person,online,telephone',
            'is_online' => 'boolean',
            'meeting_link' => 'nullable|url|max:500',
            'meeting_platform' => 'nullable|in:zoom,google_meet,teams,whereby,custom',
            'prescription' => 'nullable|string',
            'diagnosis' => 'nullable|string',
            'follow_up_instructions' => 'nullable|string',
            'next_appointment_date' => 'nullable|date|after:appointment_date',
            'exclude_from_consultation_report' => 'nullable|boolean',
        ]);

        $consultationType = in_array($request->consultation_type, ['in_person', 'online', 'telephone'], true)
            ? $request->consultation_type
            : ($request->boolean('is_online') ? 'online' : ($appointment->consultation_type ?? 'in_person'));
        $isOnline = $consultationType === 'online';

        $wherebyService = app(\App\Services\WherebyService::class);
        $meetingPlatform = $isOnline
            ? $wherebyService->resolvedOnlineMeetingPlatform($request->meeting_platform)
            : null;

        // Validate that meeting link is provided if online consultation (except for Whereby which auto-generates)
        if ($isOnline && empty($request->meeting_link) && $wherebyService->requiresManualMeetingLink($meetingPlatform)) {
            return redirect()->back()
                ->withErrors(['meeting_link' => 'Meeting link is required for online (video) consultations.'])
                ->withInput();
        }

        // Store original values to detect changes
        $oldStatus = $appointment->status;
        $oldDate = $appointment->appointment_date;
        $oldTime = $appointment->appointment_time;

        $data = $request->except(['consultation_type', 'is_online']);
        $data['consultation_type'] = $consultationType;
        $data['is_online'] = $isOnline;
        if ($this->userCanManageConsultationReportExclusion()) {
            $data['exclude_from_consultation_report'] = $request->boolean('exclude_from_consultation_report');
        } else {
            unset($data['exclude_from_consultation_report']);
        }
        if (!$isOnline) {
            $data['meeting_link'] = null;
            $data['meeting_platform'] = null;
        } else {
            $data['meeting_platform'] = $meetingPlatform;
        }
        $appointment->update($data);
        $appointment->refresh();
        $wherebyService->ensureMeetingForAppointment($appointment);
        $appointment->load(['patient', 'doctor', 'department']);
        
        // Status change patient/doctor emails are sent by AppointmentObserver (avoid duplicate sends).
        
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

    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled,rescheduled'
        ]);

        $appointment->update(['status' => $request->status]);
        $appointment->load(['patient', 'doctor', 'department']);
        
        // Status emails: AppointmentObserver handles these (do not duplicate here).

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

    /**
     * Set whether this appointment is excluded from the Admin Consultations Report (demo / training).
     */
    public function setConsultationReportExclusion(Request $request, Appointment $appointment)
    {
        if (! $this->userCanManageConsultationReportExclusion()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can change consultation report exclusion.',
            ], 403);
        }

        if (! Schema::hasColumn('appointments', 'exclude_from_consultation_report')) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a database migration. Run php artisan migrate.',
            ], 400);
        }

        $validated = $request->validate([
            'excluded' => 'required|boolean',
        ]);

        $appointment->update([
            'exclude_from_consultation_report' => $validated['excluded'],
        ]);

        return response()->json([
            'success' => true,
            'excluded' => (bool) $appointment->exclude_from_consultation_report,
            'message' => $validated['excluded']
                ? 'This appointment is excluded from the consultation report.'
                : 'This appointment is included in the consultation report again.',
        ]);
    }

    /**
     * Bulk set consultation report exclusion for selected appointment IDs.
     */
    public function bulkSetConsultationReportExclusion(Request $request)
    {
        if (! $this->userCanManageConsultationReportExclusion()) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can change consultation report exclusion.',
            ], 403);
        }

        if (! Schema::hasColumn('appointments', 'exclude_from_consultation_report')) {
            return response()->json([
                'success' => false,
                'message' => 'This feature requires a database migration. Run php artisan migrate.',
            ], 400);
        }

        $validated = $request->validate([
            'appointment_ids' => 'required|array|min:1',
            'appointment_ids.*' => 'integer|exists:appointments,id',
            'excluded' => 'required|boolean',
        ]);

        $ids = $validated['appointment_ids'];
        $excluded = $validated['excluded'];

        Appointment::whereIn('id', $ids)->update(['exclude_from_consultation_report' => $excluded]);

        return response()->json([
            'success' => true,
            'excluded' => $excluded,
            'updated' => count($ids),
            'message' => $excluded
                ? count($ids) . ' appointment(s) excluded from the consultation report.'
                : count($ids) . ' appointment(s) included in the consultation report again.',
        ]);
    }

    public function calendar()
    {
        return view('admin.appointments.calendar');
    }

    /**
     * Get calendar data for DayPilot Lite (AJAX endpoint)
     */
    public function getCalendarData(Request $request)
    {
        $start = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
        $end = $request->get('end', now()->endOfMonth()->format('Y-m-d'));

        $appointments = Appointment::with(['patient', 'doctor', 'department', 'service'])
            ->whereBetween('appointment_date', [$start, $end])
            ->get()
            ->map(function ($appointment) {
                $startDateTime = Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time->format('H:i:s'));
                
                // Calculate end time based on service duration or default to 1 hour
                $duration = 60; // Default 60 minutes
                if ($appointment->service) {
                    $duration = $appointment->service->default_duration_minutes ?? 60;
                }
                $endDateTime = $startDateTime->copy()->addMinutes($duration);
                
                $statusColor = $this->getStatusColor($appointment->status);
                $textColor = in_array($appointment->status, ['pending']) ? '#000' : '#fff';
                
                return [
                    'id' => $appointment->id,
                    'title' => $appointment->patient->full_name . ' - ' . $appointment->doctor->full_name,
                    'start' => $startDateTime->format('Y-m-d\TH:i:s'),
                    'end' => $endDateTime->format('Y-m-d\TH:i:s'),
                    'backgroundColor' => $statusColor,
                    'borderColor' => $statusColor,
                    'textColor' => $textColor,
                    'extendedProps' => [
                        'patient' => $appointment->patient->full_name,
                        'patient_id' => $appointment->patient_id,
                        'doctor' => $appointment->doctor->full_name,
                        'doctor_id' => $appointment->doctor_id,
                        'department' => $appointment->department->name ?? 'N/A',
                        'department_id' => $appointment->department_id,
                        'status' => $appointment->status,
                        'type' => $appointment->type ?? 'consultation',
                        'reason' => $appointment->reason ?? '',
                        'appointment_number' => $appointment->appointment_number ?? '',
                        'is_online' => $appointment->is_online ?? false,
                        'service_id' => $appointment->service_id,
                        'service_name' => $appointment->service->name ?? null,
                        'created_from' => $appointment->created_from ?? null
                    ]
                ];
            });

        return response()->json($appointments);
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

        $oldDate = $appointment->appointment_date;
        $oldTime = $appointment->appointment_time;

        $appointment->update([
            'appointment_date' => $request->new_date,
            'appointment_time' => $request->new_time,
            'status' => 'rescheduled',
            'notes' => ($appointment->notes ?? '') . "\n\nRescheduled: " . ($request->reason ?? 'No reason provided')
        ]);

        // Send reschedule notification
        $emailService = app(HospitalEmailNotificationService::class);
        $this->handleRescheduleNotifications($appointment, $oldDate, $oldTime, $emailService);

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
