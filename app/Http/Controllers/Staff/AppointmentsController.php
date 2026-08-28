<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Services\AppointmentCalendarInviteService;
use App\Services\SlotAvailabilityService;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class AppointmentsController extends Controller
{
    public function __construct(
        protected AppointmentCalendarInviteService $appointmentCalendarInviteService
    ) {}

    /**
     * Consultation report exclusion is limited to full admin users (not doctors).
     */
    private function userCanManageConsultationReportExclusion(): bool
    {
        $user = Auth::user();
        if (! $user) {
            return false;
        }

        return ($user->is_admin ?? false) || $user->role === 'admin';
    }

    public function index(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor', 'department']);

        // Apply visibility rules based on user role (uses patient-department-doctor logic)
        $user = Auth::user();
        if ($user) {
            $query->visibleTo($user);
        } else {
            $query->whereRaw('1 = 0'); // No results if no user
        }

        // ===== QUICK SEARCH (Multi-field) =====
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('appointment_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($pq) use ($search) {
                      $pq->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%")
                        ->orWhere('patient_id', 'like', "%{$search}%");
                  })
                  ->orWhereHas('doctor', function($dq) use ($search) {
                      $dq->whereHas('user', function($uq) use ($search) {
                          $uq->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('name', 'like', "%{$search}%");
                      });
                  });
            });
        }

        // ===== PATIENT FILTERS =====
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }
        if ($request->filled('patient_name')) {
            $patientName = $request->patient_name;
            $query->whereHas('patient', function($q) use ($patientName) {
                $q->where('first_name', 'like', "%{$patientName}%")
                  ->orWhere('last_name', 'like', "%{$patientName}%");
            });
        }

        // ===== DOCTOR & DEPARTMENT FILTERS =====
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // ===== APPOINTMENT STATUS & TYPE FILTERS =====
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('appointment_type')) {
            // Map frontend values to database values
            $typeMapping = [
                'follow_up' => 'followup',
                'consultation' => 'consultation',
            ];
            $dbType = $typeMapping[$request->appointment_type] ?? $request->appointment_type;
            $query->where('type', $dbType);
        }

        // ===== CONSULTATION TYPE FILTERS =====
        if ($request->filled('is_online')) {
            $query->where('is_online', $request->boolean('is_online'));
        }
        if ($request->filled('consultation_type')) {
            $ct = $request->consultation_type;
            if ($ct === 'phone') {
                $ct = 'telephone'; // legacy filter value
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
        }
        if ($request->filled('meeting_platform')) {
            $query->where('meeting_platform', $request->meeting_platform);
        }

        // ===== DATE & TIME FILTERS =====
        if ($request->filled('date_from')) {
            $dateFrom = Carbon::parse($request->date_from)->format('Y-m-d');
            $query->whereDate('appointment_date', '>=', $dateFrom);
        }
        if ($request->filled('date_to')) {
            $dateTo = Carbon::parse($request->date_to)->format('Y-m-d');
            $query->whereDate('appointment_date', '<=', $dateTo);
        }
        if ($request->filled('date')) {
            $date = Carbon::parse($request->date)->format('Y-m-d');
            $query->whereDate('appointment_date', $date);
        }
        if ($request->filled('time_from')) {
            $query->whereTime('appointment_time', '>=', $request->time_from);
        }
        if ($request->filled('time_to')) {
            $query->whereTime('appointment_time', '<=', $request->time_to);
        }

        // ===== DATE RANGE FILTERS (Today, This Week, This Month, etc.) =====
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('appointment_date', today());
                    break;
                case 'tomorrow':
                    $query->whereDate('appointment_date', today()->copy()->addDay());
                    break;
                case 'this_week':
                    $query->whereBetween('appointment_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'next_week':
                    $nextWeek = now()->copy()->addWeek();
                    $query->whereBetween('appointment_date', [$nextWeek->startOfWeek(), $nextWeek->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('appointment_date', now()->month)
                          ->whereYear('appointment_date', now()->year);
                    break;
                case 'next_month':
                    $nextMonth = now()->copy()->addMonth();
                    $query->whereMonth('appointment_date', $nextMonth->month)
                          ->whereYear('appointment_date', $nextMonth->year);
                    break;
                case 'upcoming':
                    $query->where('appointment_date', '>=', today())
                          ->where('status', '!=', 'cancelled');
                    break;
                case 'past':
                    $query->where('appointment_date', '<', today());
                    break;
            }
        }

        // Clone before overdue filter for counts (overdue restricts to past only)
        $queryForCounts = clone $query;

        // ===== OVERDUE / CONFLICT FILTERS =====
        if ($request->filled('overdue')) {
            // Pending appointments whose date/time has passed (doctor should take action)
            $query->pendingPast();
        }
        if ($request->filled('has_conflict')) {
            // Find appointments where same doctor has multiple appointments at same time
            $query->whereIn('id', function($subquery) {
                $subquery->select(DB::raw('MIN(id)'))
                    ->from('appointments')
                    ->whereIn('status', ['pending', 'confirmed'])
                    ->groupBy('doctor_id', 'appointment_date', 'appointment_time')
                    ->havingRaw('COUNT(*) > 1');
            });
        }

        // ===== FEE / FINANCIAL FILTERS =====
        if ($request->filled('fee_min')) {
            $query->where('fee', '>=', $request->fee_min);
        }
        if ($request->filled('fee_max')) {
            $query->where('fee', '<=', $request->fee_max);
        }
        if ($request->filled('has_fee')) {
            if ($request->has_fee === 'yes') {
                $query->whereNotNull('fee')->where('fee', '>', 0);
            } elseif ($request->has_fee === 'no') {
                $query->where(function($q) {
                    $q->whereNull('fee')->orWhere('fee', 0);
                });
            }
        }

        // ===== MEDICAL RECORD FILTERS =====
        if ($request->filled('has_medical_record')) {
            if ($request->has_medical_record === 'yes') {
                $query->whereHas('medicalRecord');
            } elseif ($request->has_medical_record === 'no') {
                $query->whereDoesntHave('medicalRecord');
            }
        }

        // ===== CHECK-IN/CHECK-OUT FILTERS =====
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

        // ===== REASON/SYMPTOMS FILTERS =====
        if ($request->filled('reason')) {
            $query->where('reason', 'like', "%{$request->reason}%");
        }
        if ($request->filled('symptoms')) {
            $query->where('symptoms', 'like', "%{$request->symptoms}%");
        }

        // Prepare data for view
        $doctors = Doctor::with('user')->get()->map(function($doctor) {
            return [
                'id' => $doctor->id,
                'name' => $doctor->user ? $doctor->user->name : 'Unknown'
            ];
        });
        $departments = Department::where('is_active', true)->orderBy('name')->get();

        // Pending appointments that have passed (flag for doctor to take action)
        $pendingPastCount = (clone $query)->pendingPast()->count();
        $pendingPastAppointments = (clone $query)->pendingPast()
            ->orderBy('appointment_date')->orderBy('appointment_time')
            ->take(10)->get();

        // Pending appointments today/future awaiting confirmation (use pre-overdue query)
        $pendingUpcomingCount = $queryForCounts->pendingUpcoming()->count();

        $consultationReportExclusionEnabled = Schema::hasColumn('appointments', 'exclude_from_consultation_report');
        $canManageConsultationReportExclusion = $this->userCanManageConsultationReportExclusion();

        // Sort by date and time
        $appointments = $query->orderBy('appointment_date', 'desc')
                              ->orderBy('appointment_time', 'desc')
                              ->paginate(15)->appends($request->query());

        return view('staff.appointments.index', compact('appointments', 'doctors', 'departments', 'pendingPastCount', 'pendingPastAppointments', 'pendingUpcomingCount', 'consultationReportExclusionEnabled', 'canManageConsultationReportExclusion'));
    }

    public function show($id)
    {
        $user = Auth::user();
        
        // Use the same visibility logic as index method
        $query = Appointment::with([
            'patient' => function($query) {
                $query->with(['alerts' => function($q) {
                    $q->with('creator')->latest();
                }]);
            },
            'doctor',
            'department',
            'service',
            'medicalRecord.prescriptions'
        ]);
        
        // Apply visibility rules based on user role (uses patient-department-doctor logic)
        if ($user) {
            $query->visibleTo($user);
        } else {
            $query->whereRaw('1 = 0'); // No results if no user
        }
        
        $appointment = $query->findOrFail($id);
        
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

        $calendarLinks = $this->appointmentCalendarInviteService->calendarLinksForAppointment($appointment);

        $consultationReportExclusionEnabled = Schema::hasColumn('appointments', 'exclude_from_consultation_report');
        $canManageConsultationReportExclusion = $this->userCanManageConsultationReportExclusion();

        return view('staff.appointments.show', compact('appointment', 'calendarLinks', 'consultationReportExclusionEnabled', 'canManageConsultationReportExclusion'));
    }

    public function create()
    {
        // Apply visibility filter based on user role
        // visibleTo() already handles department and created_by filtering for doctors
        $user = Auth::user();
        $patients = Patient::select('id', 'first_name', 'last_name', 'phone')
            ->visibleTo($user)
            ->distinct()
            ->get();
        $doctors = Doctor::orderBy('first_name')->get();
        $departments = Department::where('is_active', true)->get();
        
        // Get current user's doctor info if they are a doctor
        $currentDoctor = null;
        $currentDepartment = null;
        
        if ($user->role === 'doctor') {
            $currentDoctor = $user->doctor;
            if ($currentDoctor) {
                // Get the doctor's department
                $currentDepartment = $currentDoctor->department_id ? Department::find($currentDoctor->department_id) : $user->department;
            }
        }

        return view('staff.appointments.create', compact('patients', 'doctors', 'departments', 'currentDoctor', 'currentDepartment'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Auto-populate doctor and department if current user is a doctor
        if ($user->role === 'doctor' && $user->doctor) {
            if (!$request->filled('doctor_id')) {
                $request->merge(['doctor_id' => $user->doctor->id]);
            }
            if (!$request->filled('department_id')) {
                $departmentId = $user->doctor->department_id ?? $user->department_id;
                if ($departmentId) {
                    $request->merge(['department_id' => $departmentId]);
                }
            }
        }
        
        // Convert UK date format (dd/mm/yyyy) to Y-m-d if needed
        $appointmentDateInput = $request->appointment_date;
        if ($appointmentDateInput && preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $appointmentDateInput)) {
            $parts = explode('/', $appointmentDateInput);
            $appointmentDateInput = $parts[2] . '-' . $parts[1] . '-' . $parts[0];
            $request->merge(['appointment_date' => $appointmentDateInput]);
        }
        
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'department_id' => 'required|exists:departments,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'appointment_type' => 'required|in:consultation,follow_up',
            'estimated_duration' => ['required', 'integer', 'min:5', 'max:120', Rule::in(range(5, 120, 5))],
            'notes' => 'nullable|string',
            'consultation_type' => 'nullable|in:in_person,online,telephone',
            'is_online' => 'nullable|boolean',
            'meeting_link' => 'nullable|url|max:500',
            'meeting_platform' => 'nullable|in:zoom,google_meet,teams,whereby,custom'
        ]);

        $consultationType = $request->consultation_type ?? ($request->boolean('is_online') ? 'online' : 'in_person');
        $isOnline = $consultationType === 'online';

        $wherebyService = app(\App\Services\WherebyService::class);
        $meetingPlatform = $isOnline
            ? $wherebyService->resolvedOnlineMeetingPlatform($request->meeting_platform)
            : null;

        // Validate that meeting link is provided if online consultation (except for Whereby which auto-generates)
        if ($isOnline && empty($request->meeting_link) && $wherebyService->requiresManualMeetingLink($meetingPlatform)) {
            return redirect()->back()
                ->withErrors(['meeting_link' => 'Meeting link is required for online consultations.'])
                ->withInput();
        }

        // Update patient's department and assigned_doctor_id if not set
        $patient = Patient::findOrFail($request->patient_id);
        $updateData = [];
        
        // Set patient's department if not set
        if (!$patient->department_id && $request->department_id) {
            $updateData['department_id'] = $request->department_id;
        }
        
        // Set patient's assigned_doctor_id if not set
        if (!$patient->assigned_doctor_id && $request->doctor_id) {
            $updateData['assigned_doctor_id'] = $request->doctor_id;
        }
        
        // Set patient's created_by_doctor_id if not set and current user is a doctor creating the appointment
        if (!$patient->created_by_doctor_id && $user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor && $doctor->id == $request->doctor_id) {
                $updateData['created_by_doctor_id'] = $doctor->id;
                if (!isset($updateData['department_id'])) {
                    $updateData['department_id'] = $doctor->department_id;
                }
            }
        }
        
        // Update patient if needed
        if (!empty($updateData)) {
            $patient->update($updateData);
        }

        // Map appointment_type to database enum values
        $typeMapping = [
            'follow_up' => 'followup',
            'consultation' => 'consultation',
        ];
        $appointmentType = $typeMapping[$request->appointment_type] ?? $request->appointment_type;

        $appointment = Appointment::create([
            'appointment_number' => Appointment::generateAppointmentNumber(),
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'department_id' => $request->department_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'type' => $appointmentType,
            'status' => 'pending',
            'notes' => $request->notes,
            'reason' => $request->reason ?? null,
            'consultation_type' => $consultationType,
            'is_online' => $isOnline,
            'meeting_link' => $isOnline ? $request->meeting_link : null,
            'meeting_platform' => $meetingPlatform,
        ]);

        // Track if we need to send emails manually (for Whereby appointments where observer skipped email)
        $sendEmailsManually = false;
        $wherebyEnabled = $wherebyService->isEnabled();

        \Log::info('Staff appointment created - checking Whereby', [
            'appointment_id' => $appointment->id,
            'is_online' => $appointment->is_online,
            'meeting_platform' => $appointment->meeting_platform,
            'meeting_link' => $appointment->meeting_link,
            'whereby_enabled' => $wherebyEnabled,
        ]);

        // If this is an online appointment with Whereby platform and no meeting link, try to auto-generate
        if ($appointment->is_online && empty($appointment->meeting_link) && $appointment->meeting_platform === 'whereby') {
            \Log::info('Whereby: Conditions met for meeting creation', ['appointment_id' => $appointment->id]);

            if ($wherebyEnabled) {
                $sendEmailsManually = true; // Observer skipped email, we'll send after link is created
                \Log::info('Whereby: About to call createMeetingForAppointment', ['appointment_id' => $appointment->id]);

                try {
                    $result = $wherebyService->createMeetingForAppointment($appointment);
                    \Log::info('Whereby: createMeetingForAppointment returned', [
                        'appointment_id' => $appointment->id,
                        'result' => $result,
                    ]);

                    $appointment->refresh(); // Reload to get the updated meeting_link

                    \Log::info('Whereby meeting result for staff appointment', [
                        'appointment_id' => $appointment->id,
                        'meeting_link' => $appointment->meeting_link,
                        'whereby_host_url' => $appointment->whereby_host_url,
                        'success' => !empty($appointment->meeting_link),
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Failed to create Whereby meeting for staff appointment', [
                        'appointment_id' => $appointment->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            } else {
                // Whereby is not enabled but user selected it - log warning
                \Log::warning('Whereby platform selected but integration is not enabled', [
                    'appointment_id' => $appointment->id
                ]);
            }
        } else {
            \Log::info('Whereby: Conditions NOT met for meeting creation', [
                'appointment_id' => $appointment->id,
                'is_online' => $appointment->is_online,
                'has_meeting_link' => !empty($appointment->meeting_link),
                'meeting_platform' => $appointment->meeting_platform,
            ]);
        }

        // Send email notifications if Whereby was enabled and observer skipped email
        if ($sendEmailsManually) {
            if (empty($appointment->meeting_link)) {
                // Whereby link creation failed, still send emails but without meeting link
                \Log::warning('Sending appointment emails without Whereby link - link creation may have failed', [
                    'appointment_id' => $appointment->id
                ]);
            }

            try {
                $appointment->load(['patient.user', 'doctor.user']);
                $hospitalEmailService = app(\App\Services\HospitalEmailNotificationService::class);

                // Send confirmation email to patient
                if ($appointment->patient && $appointment->patient->email) {
                    $hospitalEmailService->sendAppointmentConfirmation($appointment);
                    \Log::info('Appointment confirmation email sent after Whereby processing', [
                        'appointment_id' => $appointment->id,
                        'patient_email' => $appointment->patient->email,
                        'has_meeting_link' => !empty($appointment->meeting_link)
                    ]);
                }

                // Send notification to doctor
                if ($appointment->doctor && $appointment->doctor->user) {
                    $hospitalEmailService->sendNewAppointmentToDoctor($appointment);
                    \Log::info('New appointment email sent to doctor after Whereby processing', [
                        'appointment_id' => $appointment->id,
                        'has_meeting_link' => !empty($appointment->meeting_link)
                    ]);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send appointment emails for staff appointment', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->route('staff.appointments.index')
            ->with('success', 'Appointment created successfully.');
    }

    public function edit($id)
    {
        $appointment = Appointment::findOrFail($id);
        
        // Staff can only edit pending appointments
        if ($appointment->status !== 'pending') {
            return redirect()->route('staff.appointments.index')
                ->with('error', 'Only pending appointments can be edited.');
        }

        $patients = Patient::select('id', 'first_name', 'last_name')
            ->visibleTo(Auth::user())
            ->get();
        $doctors = Doctor::orderBy('first_name')->get();
        $departments = Department::where('is_active', true)->get();
        
        // Get current user's doctor info if they are a doctor
        $currentDoctor = null;
        $currentDepartment = null;
        $user = Auth::user();
        
        if ($user->role === 'doctor') {
            $currentDoctor = $user->doctor;
            if ($currentDoctor) {
                // Get the doctor's department
                $currentDepartment = $currentDoctor->department_id ? Department::find($currentDoctor->department_id) : $user->department;
            }
        }

        $canManageConsultationReportExclusion = $this->userCanManageConsultationReportExclusion();
        $wherebyAutoGenerate = app(\App\Services\WherebyService::class)->isEnabled();

        return view('staff.appointments.edit', compact('appointment', 'patients', 'doctors', 'departments', 'currentDoctor', 'currentDepartment', 'canManageConsultationReportExclusion', 'wherebyAutoGenerate'));
    }

    public function update(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        // Staff can only edit pending appointments unless they're doctors
        $user = Auth::user();
        if ($appointment->status !== 'pending' && $user->role !== 'doctor') {
            return redirect()->route('staff.appointments.index')
                ->with('error', 'Only pending appointments can be edited.');
        }

        // Auto-populate doctor and department if current user is a doctor
        if ($user->role === 'doctor' && $user->doctor) {
            if (!$request->filled('doctor_id')) {
                $request->merge(['doctor_id' => $user->doctor->id]);
            }
            if (!$request->filled('department_id')) {
                $departmentId = $user->doctor->department_id ?? $user->department_id;
                if ($departmentId) {
                    $request->merge(['department_id' => $departmentId]);
                }
            }
        }

        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
            'department_id' => 'required|exists:departments,id',
            'appointment_date' => 'required|date',
            'appointment_time' => 'required',
            'appointment_type' => 'required|in:consultation,follow_up',
            'reason' => 'nullable|string',
            'priority' => 'nullable|in:normal,high,urgent',
            'estimated_duration' => ['nullable', 'integer', 'min:5', 'max:120', Rule::in(range(5, 120, 5))],
            'status' => 'nullable|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
            'edit_reason' => 'required|string|min:5',
            'consultation_type' => 'nullable|in:in_person,online,telephone',
            'is_online' => 'nullable|boolean',
            'meeting_link' => 'nullable|url|max:500',
            'meeting_platform' => 'nullable|in:zoom,google_meet,teams,whereby,custom',
            'exclude_from_consultation_report' => 'nullable|boolean',
        ]);

        $consultationType = $request->consultation_type ?? ($request->boolean('is_online') ? 'online' : ($appointment->consultation_type ?? 'in_person'));
        $isOnline = $consultationType === 'online';

        $wherebyService = app(\App\Services\WherebyService::class);
        $meetingPlatform = $isOnline
            ? $wherebyService->resolvedOnlineMeetingPlatform($request->meeting_platform)
            : null;

        // Validate that meeting link is provided if online consultation (except for Whereby which auto-generates)
        if ($isOnline && empty($request->meeting_link) && $wherebyService->requiresManualMeetingLink($meetingPlatform)) {
            return redirect()->back()
                ->withErrors(['meeting_link' => 'Meeting link is required for online consultations.'])
                ->withInput();
        }

        // Map appointment_type to database enum values
        $typeMapping = [
            'follow_up' => 'followup',
            'consultation' => 'consultation',
        ];
        $appointmentType = $typeMapping[$request->appointment_type] ?? $request->appointment_type;

        // Build update data
        $updateData = [
            'doctor_id' => $request->doctor_id,
            'department_id' => $request->department_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'type' => $appointmentType,
            'reason' => $request->reason,
            'consultation_type' => $consultationType,
            'is_online' => $isOnline,
            'meeting_link' => $isOnline ? $request->meeting_link : null,
            'meeting_platform' => $meetingPlatform,
        ];

        // Do not wipe patient "reason for booking" (stored in notes) when the notes field is left empty.
        if ($request->has('notes')) {
            $incomingNotes = $request->input('notes');
            $updateData['notes'] = (is_string($incomingNotes) && trim($incomingNotes) !== '')
                ? $incomingNotes
                : ($appointment->notes ?? '');
        }

        if (Schema::hasColumn('appointments', 'exclude_from_consultation_report') && $this->userCanManageConsultationReportExclusion()) {
            $updateData['exclude_from_consultation_report'] = $request->boolean('exclude_from_consultation_report');
        }

        // Only allow status changes for certain roles/conditions
        if ($request->filled('status')) {
            $newStatus = $request->status;
            
            // Only doctors can mark as completed
            if ($newStatus === 'completed' && $user->role !== 'doctor') {
                return redirect()->back()
                    ->with('error', 'Only doctors can mark appointments as completed.')
                    ->withInput();
            }
            
            $updateData['status'] = $newStatus;
        }

        // Handle priority and estimated_duration if they exist in the appointments table
        // Check if these columns exist before trying to update them
        $appointment->update($updateData);
        $appointment->refresh();
        $wherebyService->ensureMeetingForAppointment($appointment);

        // Log the edit reason (you might want to store this in an audit log table)
        // For now, we'll add it as a note in the appointment notes
        if ($request->filled('edit_reason')) {
            $editLog = "\n\n[EDIT " . now()->format('Y-m-d H:i') . " by " . $user->name . "]: " . $request->edit_reason;
            $appointment->update([
                'notes' => ($appointment->notes ?? '') . $editLog
            ]);
        }

        return redirect()->route('staff.appointments.index')
            ->with('success', 'Appointment updated successfully.');
    }

    /**
     * Set whether this appointment is excluded from the Admin Consultations Report (demo / training).
     */
    public function setConsultationReportExclusion(Request $request, $id)
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

        $appointment = Appointment::query()
            ->visibleTo(Auth::user())
            ->whereKey($id)
            ->firstOrFail();

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
     * Bulk set consultation report exclusion for appointments the user can see.
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

        $allowedIds = Appointment::query()
            ->visibleTo(Auth::user())
            ->whereIn('id', $ids)
            ->pluck('id');

        if ($allowedIds->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No matching appointments found for your account.',
            ], 404);
        }

        Appointment::whereIn('id', $allowedIds)->update(['exclude_from_consultation_report' => $excluded]);

        return response()->json([
            'success' => true,
            'excluded' => $excluded,
            'updated' => $allowedIds->count(),
            'message' => $excluded
                ? $allowedIds->count() . ' appointment(s) excluded from the consultation report.'
                : $allowedIds->count() . ' appointment(s) included in the consultation report again.',
        ]);
    }

    public function confirm($id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            
            if ($appointment->status === 'pending') {
                $appointment->update(['status' => 'confirmed']);
                return redirect()->back()->with('success', 'Appointment confirmed successfully.');
            }

            return redirect()->back()->with('error', 'Appointment cannot be confirmed because it is not pending.');

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating appointment status. Please try again.');
        }
    }

    public function cancel($id)
    {
        $appointment = Appointment::findOrFail($id);
        
        if (in_array($appointment->status, ['pending', 'confirmed'])) {
            $appointment->update(['status' => 'cancelled']);
            return redirect()->back()->with('success', 'Appointment cancelled successfully.');
        }

        return redirect()->back()->with('error', 'Appointment cannot be cancelled.');
    }

    public function updateStatus(Request $request, $id)
    {
        try {
            $appointment = Appointment::findOrFail($id);
            $user = Auth::user();

            $request->validate([
                'status' => 'required|in:pending,confirmed,completed,cancelled',
                'notes' => 'nullable|string'
            ]);

            $newStatus = $request->input('status');

            // If status is already the target, return success (no change needed)
            if ($appointment->status === $newStatus) {
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => 'Appointment is already ' . ucfirst($newStatus) . '.',
                        'status' => $newStatus
                    ]);
                }
                return redirect()->back()->with('info', 'Appointment is already ' . ucfirst($newStatus) . '.');
            }

            // Role-based restrictions
            if ($newStatus === 'completed' && $user->role !== 'doctor') {
                $message = 'Only doctors can mark appointments as completed.';
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $message], 403);
                }
                return redirect()->back()->with('error', $message);
            }

            // Status transition validation
            $validTransitions = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['completed', 'cancelled'],
                'completed' => [], // Completed appointments cannot be changed
                'cancelled' => [] // Cancelled appointments cannot be changed
            ];

            if (!in_array($newStatus, $validTransitions[$appointment->status] ?? [])) {
                $message = "Cannot change status from {$appointment->status} to {$newStatus}.";
                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['success' => false, 'message' => $message], 400);
                }
                return redirect()->back()->with('error', $message);
            }
            
            // Update appointment
            $appointment->update(['status' => $newStatus]);
            
            // Add notes if provided
            $notes = $request->input('notes');
            if (!empty($notes)) {
                $noteLog = "\n\n[STATUS UPDATE " . now()->format('Y-m-d H:i') . " by " . $user->name . "]: Status changed to {$newStatus}. Notes: " . $notes;
                $appointment->update([
                    'notes' => ($appointment->notes ?? '') . $noteLog
                ]);
            }

            // Handle both AJAX and form submissions
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Appointment status updated successfully.',
                    'status' => $newStatus
                ]);
            }

            return redirect()->route('staff.appointments.show', $id)
                ->with('success', 'Appointment status updated to ' . ucfirst($newStatus) . '.');

        } catch (\Exception $e) {
            if ($request->expectsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error updating appointment status. Please try again.'
                ], 500);
            }

            return redirect()->back()->with('error', 'Error updating appointment status. Please try again.');
        }
    }

    // Note: Staff cannot delete appointments - only admins can

    /**
     * Display calendar view for appointments
     */
    public function calendar()
    {
        return view('staff.appointments.calendar');
    }

    /**
     * Available appointment start times for staff scheduling (uses weekly availability rules).
     */
    public function getAvailableSlots(Request $request, Doctor $doctor, SlotAvailabilityService $slotAvailabilityService)
    {
        $user = Auth::user();
        if ($user?->role === 'doctor' && $user->doctor && (int) $user->doctor->id !== (int) $doctor->id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'date' => 'required|date',
            'duration' => 'nullable|integer|min:5|max:480',
            'service_id' => 'nullable|exists:booking_services,id',
            'modality' => 'nullable|in:in_person,online,telephone',
        ]);

        $slots = $slotAvailabilityService->getAvailableSlots(
            $doctor->id,
            Carbon::parse($validated['date'])->format('Y-m-d'),
            $validated['service_id'] ?? null,
            $validated['duration'] ?? null,
            $validated['modality'] ?? null,
        );

        return response()->json([
            'slots' => $slots,
            'date' => Carbon::parse($validated['date'])->format('Y-m-d'),
        ]);
    }

    /**
     * Get calendar data for DayPilot Lite (AJAX endpoint)
     * Filters appointments by doctor if user is a doctor
     */
    public function getCalendarData(Request $request)
    {
        try {
            $start = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
            $end = $request->get('end', now()->endOfMonth()->format('Y-m-d'));

            $user = Auth::user();
            $query = Appointment::with(['patient', 'doctor', 'department', 'service'])
                ->whereBetween('appointment_date', [$start, $end])
                ->visibleTo($user);

            $appointments = $query->get()
                ->filter(function ($appointment) {
                    // Filter out appointments with missing required relationships
                    return $appointment->patient && $appointment->doctor;
                })
                ->map(function ($appointment) use ($user) {
                    try {
                        $startDateTime = Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time->format('H:i:s'));
                        
                        // Calculate end time based on service duration or default to 1 hour
                        $duration = 60; // Default 60 minutes
                        if ($appointment->service) {
                            $duration = $appointment->service->default_duration_minutes ?? 60;
                        }
                        $endDateTime = $startDateTime->copy()->addMinutes($duration);
                        
                        $title = $appointment->patient->full_name ?? 'Unknown Patient';
                        if ($user->role !== 'doctor' && $appointment->doctor) {
                            $title .= ' - ' . ($appointment->doctor->full_name ?? 'Unknown Doctor');
                        }
                        
                        $statusColor = $this->getStatusColor($appointment->status);
                        $textColor = in_array($appointment->status, ['pending']) ? '#000' : '#fff';
                        
                        return [
                            'id' => $appointment->id,
                            'title' => $title,
                            'start' => $startDateTime->format('Y-m-d\TH:i:s'),
                            'end' => $endDateTime->format('Y-m-d\TH:i:s'),
                            'backgroundColor' => $statusColor,
                            'borderColor' => $statusColor,
                            'textColor' => $textColor,
                            'extendedProps' => [
                                'patient' => $appointment->patient->full_name ?? 'Unknown',
                                'patient_id' => $appointment->patient_id,
                                'doctor' => $appointment->doctor->full_name ?? 'Unknown',
                                'doctor_id' => $appointment->doctor_id,
                                'department' => $appointment->department->name ?? 'N/A',
                                'department_id' => $appointment->department_id,
                                'status' => $appointment->status,
                                'type' => $appointment->type ?? 'consultation',
                                'service_id' => $appointment->service_id,
                                'service_name' => $appointment->service->name ?? null,
                                'created_from' => $appointment->created_from ?? null,
                                'reason' => $appointment->reason ?? '',
                                'appointment_number' => $appointment->appointment_number ?? '',
                                'is_online' => $appointment->is_online ?? false
                            ]
                        ];
                    } catch (\Exception $e) {
                        \Log::error('Error processing appointment for calendar', [
                            'appointment_id' => $appointment->id,
                            'error' => $e->getMessage()
                        ]);
                        return null;
                    }
                })
                ->filter() // Remove null entries
                ->values(); // Re-index array

            return response()->json($appointments);
        } catch (\Exception $e) {
            \Log::error('Error loading calendar data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Failed to load calendar data',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get status color for calendar events
     */
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

    /**
     * Reschedule appointment (for calendar drag-and-drop)
     */
    public function reschedule(Request $request, $id)
    {
        $appointment = Appointment::findOrFail($id);
        
        // Check authorization - doctors can only reschedule their own appointments
        $user = Auth::user();
        if ($user->role === 'doctor' && $user->doctor && $appointment->doctor_id !== $user->doctor->id) {
            return response()->json([
                'success' => false,
                'message' => 'You can only reschedule your own appointments.'
            ], 403);
        }

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

        return response()->json([
            'success' => true,
            'message' => 'Appointment rescheduled successfully!'
        ]);
    }
}
