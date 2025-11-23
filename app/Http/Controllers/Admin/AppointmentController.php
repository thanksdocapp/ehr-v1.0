<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    /**
     * Display admin appointments dashboard
     */
    public function index(Request $request)
    {
        $stats = $this->getAppointmentStats();
        
        // Get appointments with filters
        $query = Appointment::with(['patient', 'doctor', 'department']);
        
        // ===== QUICK SEARCH (Multi-field) =====
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('appointment_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('patient', function($pq) use ($search) {
                      $pq->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('phone', 'LIKE', "%{$search}%")
                        ->orWhere('patient_id', 'LIKE', "%{$search}%");
                  })
                  ->orWhereHas('doctor', function($dq) use ($search) {
                      $dq->whereHas('user', function($uq) use ($search) {
                          $uq->where('first_name', 'LIKE', "%{$search}%")
                            ->orWhere('last_name', 'LIKE', "%{$search}%")
                            ->orWhere('name', 'LIKE', "%{$search}%");
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

        // ===== CONSULTATION TYPE FILTERS =====
        if ($request->filled('is_online')) {
            $query->where('is_online', $request->boolean('is_online'));
        }
        if ($request->filled('consultation_type')) {
            if ($request->consultation_type === 'online') {
                $query->where('is_online', true);
            } elseif ($request->consultation_type === 'in_person') {
                $query->where('is_online', false);
            } elseif ($request->consultation_type === 'phone') {
                $query->where('type', 'phone')->orWhere(function($q) {
                    $q->where('is_online', false)->where('type', 'consultation');
                });
            }
        }
        if ($request->filled('meeting_platform')) {
            $query->where('meeting_platform', $request->meeting_platform);
        }

        // ===== DATE & TIME FILTERS =====
        if ($request->filled('date_from')) {
            $dateFrom = parseDateInput($request->date_from);
            if ($dateFrom) {
                $query->whereDate('appointment_date', '>=', $dateFrom);
            }
        }
        if ($request->filled('date_to')) {
            $dateTo = parseDateInput($request->date_to);
            if ($dateTo) {
                $query->whereDate('appointment_date', '<=', $dateTo);
            }
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

        // ===== DATE RANGE FILTERS =====
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

        // ===== OVERDUE / CONFLICT FILTERS =====
        if ($request->filled('overdue')) {
            $query->where('appointment_date', '<', today())
                  ->whereIn('status', ['pending', 'confirmed']);
        }
        if ($request->filled('has_conflict')) {
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
        
        // Sort by date and time
        $query->orderBy('appointment_date', 'desc')
              ->orderBy('appointment_time', 'desc');
        
        // Get paginated appointments
        $appointments = $query->paginate(15)->appends($request->query());
        
        $data = [
            'stats' => $stats,
            'appointments' => $appointments,
            'departments' => Department::active()->ordered()->get(),
            'doctors' => Doctor::with('department')->ordered()->get(),
        ];

        return view('admin.appointments.index', $data);
    }

    /**
     * Get all appointments for admin (AJAX)
     */
    public function getAppointments(Request $request)
    {
        $query = Appointment::with(['patient', 'doctor', 'department']);

        // Apply filters
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        if ($request->has('department_id') && $request->department_id != '') {
            $query->where('department_id', $request->department_id);
        }

        if ($request->has('doctor_id') && $request->doctor_id != '') {
            $query->where('doctor_id', $request->doctor_id);
        }

        if ($request->has('date_from') && $request->date_from != '') {
            $dateFrom = parseDateInput($request->date_from);
            $query->whereDate('appointment_date', '>=', $dateFrom);
        }

        if ($request->has('date_to') && $request->date_to != '') {
            $dateTo = parseDateInput($request->date_to);
            $query->whereDate('appointment_date', '<=', $dateTo);
        }

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('appointment_number', 'LIKE', "%{$search}%")
                  ->orWhereHas('patient', function($pq) use ($search) {
                      $pq->where('first_name', 'LIKE', "%{$search}%")
                        ->orWhere('last_name', 'LIKE', "%{$search}%")
                        ->orWhere('email', 'LIKE', "%{$search}%")
                        ->orWhere('patient_id', 'LIKE', "%{$search}%");
                  });
            });
        }

        // Sort by date and time
        $query->orderBy('appointment_date', 'asc')
              ->orderBy('appointment_time', 'asc');

        // Pagination
        $perPage = $request->get('per_page', 20);
        $appointments = $query->paginate($perPage);

        return response()->json([
            'appointments' => $appointments->items(),
            'pagination' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new appointment
     */
    /**
     * Get the current user's department ID for any role
     */
    private function getUserDepartmentId()
    {
        $user = Auth::user();
        
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
        
        $departments = Department::active()->ordered()->get();
        
        $doctorsQuery = Doctor::with('department');
        if ($departmentId) {
            $doctorsQuery->where('department_id', $departmentId);
        }
        $doctors = $doctorsQuery->ordered()->get();
        
        $patientsQuery = Patient::active();
        if ($departmentId) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        
        $data = [
            'departments' => $departments,
            'doctors' => $doctors,
            'patients' => $patients,
        ];

        return view('admin.appointments.create', $data);
    }

    /**
     * Store a newly created appointment
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id', 
            'department_id' => 'required|exists:departments,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|string',
            'type' => 'required|in:consultation,followup,emergency,checkup,surgery',
            'reason' => 'nullable|string|max:1000',
            'symptoms' => 'nullable|string|max:1000',
            'notes' => 'nullable|string|max:1000',
            'fee' => 'nullable|numeric|min:0',
            'is_online' => 'boolean',
            'meeting_link' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Check for appointment conflicts
            $conflictingAppointment = Appointment::where('doctor_id', $request->doctor_id)
                ->whereDate('appointment_date', $request->appointment_date)
                ->where('appointment_time', $request->appointment_time)
                ->whereIn('status', ['pending', 'confirmed'])
                ->exists();

            if ($conflictingAppointment) {
                DB::rollback();
                return redirect()->back()
                    ->with('error', 'This time slot is already booked. Please choose another time.')
                    ->withInput();
            }

            // Generate unique appointment number
            $appointmentNumber = Appointment::generateAppointmentNumber();

            // Create appointment
            $appointment = Appointment::create([
                'appointment_number' => $appointmentNumber,
                'patient_id' => $request->patient_id,
                'doctor_id' => $request->doctor_id,
                'department_id' => $request->department_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $request->appointment_time,
                'type' => $request->type,
                'status' => 'pending',
                'reason' => $request->reason,
                'symptoms' => $request->symptoms,
                'notes' => $request->notes,
                'fee' => $request->fee,
                'is_online' => $request->boolean('is_online', false),
                'meeting_link' => $request->is_online ? $request->meeting_link : null,
            ]);

            // Load relationships for response
            $appointment->load(['patient', 'doctor', 'department']);

            DB::commit();

            return redirect()->route('admin.appointments.index')
                ->with('success', 'Appointment created successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Admin appointment creation failed', [
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'An error occurred while creating the appointment. Please try again.')
                ->withInput();
        }
    }

    /**
     * Update appointment status (Admin)
     */
    public function updateStatus(Request $request, $appointmentId)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'admin_notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $appointment = Appointment::findOrFail($appointmentId);
            
            $oldStatus = $appointment->status;
            $appointment->status = $request->status;
            
            if ($request->admin_notes) {
                $appointment->notes = ($appointment->notes ? $appointment->notes . "\n\n" : '') 
                                   . "Admin Note (" . now()->format('Y-m-d H:i') . "): " . $request->admin_notes;
            }
            
            $appointment->save();

            // Log the status change
            Log::info('Appointment status updated by admin', [
                'appointment_id' => $appointment->id,
                'appointment_number' => $appointment->appointment_number,
                'old_status' => $oldStatus,
                'new_status' => $appointment->status,
                'admin_notes' => $request->admin_notes,
                'updated_by' => auth()->user()->id ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Appointment status updated successfully',
                'appointment' => [
                    'id' => $appointment->id,
                    'status' => $appointment->status,
                    'notes' => $appointment->notes
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to update appointment status', [
                'appointment_id' => $appointmentId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update appointment status'
            ], 500);
        }
    }

    /**
     * Get doctor schedule for a specific date
     */
    public function getDoctorSchedule(Request $request, $doctorId)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        $doctor = Doctor::with('department')->findOrFail($doctorId);
        
        $appointments = Appointment::with(['patient'])
            ->where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed', 'completed'])
            ->orderBy('appointment_time', 'asc')
            ->get();

        // Generate time slots (9 AM to 5 PM, 30-minute intervals)
        $timeSlots = [];
        $startTime = Carbon::parse($date . ' 09:00:00');
        $endTime = Carbon::parse($date . ' 17:00:00');

        while ($startTime < $endTime) {
            $timeString = $startTime->format('H:i:s');
            $displayTime = $startTime->format('g:i A');
            
            // Check if this slot has an appointment
            $appointment = $appointments->where('appointment_time', $timeString)->first();
            
            $timeSlots[] = [
                'time' => $timeString,
                'display_time' => $displayTime,
                'is_booked' => $appointment !== null,
                'appointment' => $appointment ? [
                    'id' => $appointment->id,
                    'appointment_number' => $appointment->appointment_number,
                    'patient_name' => $appointment->patient->full_name,
                    'patient_id' => $appointment->patient->patient_id,
                    'status' => $appointment->status,
                    'symptoms' => $appointment->symptoms,
                ] : null
            ];
            
            $startTime->addMinutes(30);
        }

        return response()->json([
            'doctor' => [
                'id' => $doctor->id,
                'name' => $doctor->full_name,
                'specialization' => $doctor->specialization,
                'department' => $doctor->department->name
            ],
            'date' => $date,
            'time_slots' => $timeSlots,
            'total_appointments' => $appointments->count(),
            'pending_appointments' => $appointments->where('status', 'pending')->count(),
            'confirmed_appointments' => $appointments->where('status', 'confirmed')->count(),
        ]);
    }

    /**
     * Get conflict detection and resolution suggestions
     */
    public function getConflicts(Request $request)
    {
        $date = $request->get('date', now()->format('Y-m-d'));
        
        $conflicts = [];
        
        // Find time slot conflicts (multiple appointments at same time)
        $timeConflicts = Appointment::select('doctor_id', 'appointment_date', 'appointment_time', DB::raw('COUNT(*) as count'))
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->groupBy('doctor_id', 'appointment_date', 'appointment_time')
            ->having('count', '>', 1)
            ->with(['doctor'])
            ->get();

        foreach ($timeConflicts as $conflict) {
            $conflictingAppointments = Appointment::with(['patient'])
                ->where('doctor_id', $conflict->doctor_id)
                ->whereDate('appointment_date', $conflict->appointment_date)
                ->where('appointment_time', $conflict->appointment_time)
                ->whereIn('status', ['pending', 'confirmed'])
                ->get();

            $conflicts[] = [
                'type' => 'time_conflict',
                'doctor' => $conflict->doctor->full_name,
                'doctor_id' => $conflict->doctor_id,
                'date' => $conflict->appointment_date,
                'time' => Carbon::parse($conflict->appointment_time)->format('g:i A'),
                'appointments' => $conflictingAppointments->map(function($apt) {
                    return [
                        'id' => $apt->id,
                        'appointment_number' => $apt->appointment_number,
                        'patient_name' => $apt->patient->full_name,
                        'patient_id' => $apt->patient->patient_id,
                        'status' => $apt->status,
                    ];
                }),
                'severity' => 'high'
            ];
        }

        // Find doctors with excessive bookings
        $overBookings = Appointment::select('doctor_id', DB::raw('COUNT(*) as count'))
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->groupBy('doctor_id')
            ->having('count', '>', 12) // More than 12 appointments per day
            ->with(['doctor'])
            ->get();

        foreach ($overBookings as $overBooking) {
            $conflicts[] = [
                'type' => 'over_booking',
                'doctor' => $overBooking->doctor->full_name,
                'doctor_id' => $overBooking->doctor_id,
                'date' => $date,
                'appointment_count' => $overBooking->count,
                'severity' => 'medium'
            ];
        }

        return response()->json([
            'conflicts' => $conflicts,
            'date' => $date,
            'total_conflicts' => count($conflicts)
        ]);
    }

    /**
     * Resolve appointment conflict
     */
    public function resolveConflict(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conflict_type' => 'required|in:time_conflict,over_booking',
            'resolution_type' => 'required|in:reschedule,cancel,priority',
            'appointment_id' => 'required|exists:appointments,id',
            'new_date' => 'nullable|date',
            'new_time' => 'nullable|string',
            'notes' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $appointment = Appointment::findOrFail($request->appointment_id);

            switch ($request->resolution_type) {
                case 'reschedule':
                    if (!$request->new_date || !$request->new_time) {
                        return response()->json([
                            'success' => false,
                            'message' => 'New date and time are required for rescheduling'
                        ], 422);
                    }

                    // Check if new slot is available
                    $conflictCheck = Appointment::where('doctor_id', $appointment->doctor_id)
                        ->whereDate('appointment_date', $request->new_date)
                        ->where('appointment_time', $request->new_time)
                        ->whereIn('status', ['pending', 'confirmed'])
                        ->exists();

                    if ($conflictCheck) {
                        return response()->json([
                            'success' => false,
                            'message' => 'The new time slot is already booked'
                        ], 422);
                    }

                    $appointment->appointment_date = $request->new_date;
                    $appointment->appointment_time = $request->new_time;
                    $appointment->status = 'confirmed';
                    break;

                case 'cancel':
                    $appointment->status = 'cancelled';
                    break;

                case 'priority':
                    $appointment->status = 'confirmed';
                    $appointment->is_emergency = true;
                    break;
            }

            if ($request->notes) {
                $appointment->notes = ($appointment->notes ? $appointment->notes . "\n\n" : '') 
                                   . "Conflict Resolution (" . now()->format('Y-m-d H:i') . "): " . $request->notes;
            }

            $appointment->save();

            DB::commit();

            Log::info('Appointment conflict resolved', [
                'appointment_id' => $appointment->id,
                'conflict_type' => $request->conflict_type,
                'resolution_type' => $request->resolution_type,
                'notes' => $request->notes,
                'resolved_by' => auth()->user()->id ?? 'system'
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Conflict resolved successfully',
                'appointment' => [
                    'id' => $appointment->id,
                    'status' => $appointment->status,
                    'appointment_date' => $appointment->appointment_date,
                    'appointment_time' => $appointment->appointment_time,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Failed to resolve appointment conflict', [
                'appointment_id' => $request->appointment_id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve conflict'
            ], 500);
        }
    }

    /**
     * Get appointment statistics for admin dashboard
     */
    private function getAppointmentStats()
    {
        $today = now()->format('Y-m-d');
        $thisWeek = [now()->startOfWeek(), now()->endOfWeek()];
        $thisMonth = now()->format('Y-m');

        return [
            'total_appointments' => Appointment::count(),
            'today_appointments' => Appointment::whereDate('appointment_date', $today)->count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
            'confirmed_appointments' => Appointment::where('status', 'confirmed')->count(),
            'completed_appointments' => Appointment::where('status', 'completed')->count(),
            'cancelled_appointments' => Appointment::where('status', 'cancelled')->count(),
            'this_week_appointments' => Appointment::whereBetween('appointment_date', $thisWeek)->count(),
            'this_month_appointments' => Appointment::where('appointment_date', 'LIKE', $thisMonth . '%')->count(),
            'conflicts_today' => $this->getConflictCount($today),
        ];
    }

    /**
     * Get conflict count for a specific date
     */
    private function getConflictCount($date)
    {
        return Appointment::select('doctor_id', 'appointment_date', 'appointment_time', DB::raw('COUNT(*) as count'))
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->groupBy('doctor_id', 'appointment_date', 'appointment_time')
            ->having('count', '>', 1)
            ->count();
    }

    /**
     * Export appointments data
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'csv');
        
        $query = Appointment::with(['patient', 'doctor', 'department']);
        
        // Apply same filters as getAppointments method
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        // ... other filters ...

        $appointments = $query->orderBy('appointment_date', 'asc')
                            ->orderBy('appointment_time', 'asc')
                            ->get();

        if ($format === 'csv') {
            return $this->exportCSV($appointments);
        }

        return response()->json(['error' => 'Unsupported format'], 400);
    }

    /**
     * Export appointments to CSV
     */
    private function exportCSV($appointments)
    {
        $filename = 'appointments_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($appointments) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'Appointment Number',
                'Patient Name',
                'Patient ID',
                'Patient Email',
                'Doctor Name',
                'Department',
                'Date',
                'Time',
                'Status',
                'Symptoms',
                'Created At'
            ]);

            // CSV Data
            foreach ($appointments as $appointment) {
                fputcsv($file, [
                    $appointment->appointment_number,
                    $appointment->patient->full_name,
                    $appointment->patient->patient_id,
                    $appointment->patient->email,
                    $appointment->doctor->full_name,
                    $appointment->department->name,
                    $appointment->appointment_date,
                    Carbon::parse($appointment->appointment_time)->format('g:i A'),
                    ucfirst($appointment->status),
                    $appointment->symptoms,
                    $appointment->created_at->format('Y-m-d H:i:s')
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
