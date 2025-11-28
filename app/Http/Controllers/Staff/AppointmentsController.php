<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AppointmentsController extends Controller
{
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
                'routine_checkup' => 'checkup',
                'consultation' => 'consultation',
                'emergency' => 'emergency',
            ];
            $dbType = $typeMapping[$request->appointment_type] ?? $request->appointment_type;
            $query->where('type', $dbType);
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

        // ===== OVERDUE / CONFLICT FILTERS =====
        if ($request->filled('overdue')) {
            $query->where('appointment_date', '<', today())
                  ->whereIn('status', ['pending', 'confirmed']);
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

        // Sort by date and time
        $appointments = $query->orderBy('appointment_date', 'desc')
                              ->orderBy('appointment_time', 'desc')
                              ->paginate(15)->appends($request->query());

        return view('staff.appointments.index', compact('appointments', 'doctors', 'departments'));
    }

    public function show($id)
    {
        $appointment = Appointment::with([
            'patient' => function($query) {
                $query->with(['alerts' => function($q) {
                    $q->with('creator')->latest();
                }]);
            },
            'doctor',
            'department',
            'medicalRecord.prescriptions'
        ])->findOrFail($id);
        
        // Check authorization - doctors can only view their own appointments
        $user = Auth::user();
        if ($user->role === 'doctor' && $user->doctor && $appointment->doctor_id !== $user->doctor->id) {
            abort(403, 'You can only view your own appointments.');
        }
        
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
                'appointment_date' => $appointment->appointment_date->format('M d, Y'),
                'appointment_time' => $appointment->appointment_time->format('h:i A'),
                'status' => $appointment->status,
                'type' => $appointment->type ?? 'consultation',
                'reason' => $appointment->reason ?? '',
                'appointment_number' => $appointment->appointment_number ?? '',
                'is_online' => $appointment->is_online ?? false,
                'notes' => $appointment->notes ?? ''
            ]);
        }

        return view('staff.appointments.show', compact('appointment'));
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
        
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'department_id' => 'required|exists:departments,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'appointment_type' => 'required|in:consultation,follow_up,routine_checkup,emergency',
            'notes' => 'nullable|string',
            'is_online' => 'nullable|boolean',
            'meeting_link' => 'nullable|url|max:500',
            'meeting_platform' => 'nullable|in:zoom,google_meet,teams,whereby,custom'
        ]);

        // Validate that meeting link is provided if online consultation
        if ($request->boolean('is_online') && empty($request->meeting_link)) {
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
            'routine_checkup' => 'checkup',
            'consultation' => 'consultation',
            'emergency' => 'emergency',
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
            'is_online' => $request->boolean('is_online', false),
            'meeting_link' => $request->meeting_link,
            'meeting_platform' => $request->meeting_platform,
        ]);

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

        return view('staff.appointments.edit', compact('appointment', 'patients', 'doctors', 'departments', 'currentDoctor', 'currentDepartment'));
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
            'appointment_type' => 'required|in:consultation,follow_up,routine_checkup,emergency',
            'reason' => 'nullable|string',
            'priority' => 'nullable|in:normal,high,urgent',
            'estimated_duration' => 'nullable|integer|min:15|max:480',
            'status' => 'nullable|in:pending,confirmed,completed,cancelled',
            'notes' => 'nullable|string',
            'edit_reason' => 'required|string|min:5',
            'is_online' => 'nullable|boolean',
            'meeting_link' => 'nullable|url|max:500',
            'meeting_platform' => 'nullable|in:zoom,google_meet,teams,whereby,custom'
        ]);

        // Validate that meeting link is provided if online consultation
        if ($request->boolean('is_online') && empty($request->meeting_link)) {
            return redirect()->back()
                ->withErrors(['meeting_link' => 'Meeting link is required for online consultations.'])
                ->withInput();
        }

        // Map appointment_type to database enum values
        $typeMapping = [
            'follow_up' => 'followup',
            'routine_checkup' => 'checkup',
            'consultation' => 'consultation',
            'emergency' => 'emergency',
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
            'notes' => $request->notes,
            'is_online' => $request->boolean('is_online', false),
            'meeting_link' => $request->meeting_link,
            'meeting_platform' => $request->meeting_platform,
        ];

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
            
            $newStatus = $request->status;
            
            // Role-based restrictions
            if ($newStatus === 'completed' && $user->role !== 'doctor') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only doctors can mark appointments as completed.'
                ], 403);
            }
            
            // Status transition validation
            $validTransitions = [
                'pending' => ['confirmed', 'cancelled'],
                'confirmed' => ['completed', 'cancelled'],
                'completed' => [], // Completed appointments cannot be changed
                'cancelled' => [] // Cancelled appointments cannot be changed
            ];
            
            if (!in_array($newStatus, $validTransitions[$appointment->status] ?? [])) {
                return response()->json([
                    'success' => false,
                    'message' => "Cannot change status from {$appointment->status} to {$newStatus}."
                ], 400);
            }
            
            // Update appointment
            $appointment->update(['status' => $newStatus]);
            
            // Add notes if provided
            if ($request->filled('notes')) {
                $noteLog = "\n\n[STATUS UPDATE " . now()->format('Y-m-d H:i') . " by " . $user->name . "]: Status changed to {$newStatus}. Notes: " . $request->notes;
                $appointment->update([
                    'notes' => ($appointment->notes ?? '') . $noteLog
                ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Appointment status updated successfully.',
                'status' => $newStatus
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating appointment status. Please try again.'
            ], 500);
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
     * Get calendar data for DayPilot Lite (AJAX endpoint)
     * Filters appointments by doctor if user is a doctor
     */
    public function getCalendarData(Request $request)
    {
        try {
            $start = $request->get('start', now()->startOfMonth()->format('Y-m-d'));
            $end = $request->get('end', now()->endOfMonth()->format('Y-m-d'));

            $query = Appointment::with(['patient', 'doctor', 'department'])
                ->whereBetween('appointment_date', [$start, $end]);

            // Filter for doctor's own appointments if user is a doctor
            $user = Auth::user();
            if ($user->role === 'doctor' && $user->doctor) {
                $query->where('doctor_id', $user->doctor->id);
            }

            $appointments = $query->get()
                ->filter(function ($appointment) {
                    // Filter out appointments with missing required relationships
                    return $appointment->patient && $appointment->doctor;
                })
                ->map(function ($appointment) use ($user) {
                    try {
                        $startDateTime = Carbon::parse($appointment->appointment_date->format('Y-m-d') . ' ' . $appointment->appointment_time->format('H:i:s'));
                        $endDateTime = $startDateTime->copy()->addHour(); // Default 1 hour duration
                        
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
