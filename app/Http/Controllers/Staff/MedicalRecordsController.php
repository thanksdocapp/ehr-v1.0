<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordAttachment;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Carbon\Carbon;

class MedicalRecordsController extends Controller
{
    /**
     * Get the current user's primary department ID for any role
     */
    private function getUserDepartmentId()
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }
        
        // For doctors, get primary department from doctors pivot table or department_id
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->with('departments')->first();
            if ($doctor) {
                // Get primary department from pivot table or fallback to department_id
                $primaryDept = $doctor->primaryDepartment();
                return $primaryDept ? $primaryDept->id : $doctor->department_id;
            }
            return null;
        }
        
        // For other roles, get primary department from users pivot table or department_id
        $user->load('departments');
        $primaryDept = $user->primaryDepartment();
        return $primaryDept ? $primaryDept->id : $user->department_id;
    }

    /**
     * Get all department IDs for the current user (supports multiple departments)
     */
    private function getUserDepartmentIds()
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }
        
        $departmentIds = [];
        
        // For doctors, get all departments from doctors pivot table or department_id
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->with('departments')->first();
            if ($doctor) {
                if ($doctor->departments->isNotEmpty()) {
                    $departmentIds = $doctor->departments->pluck('id')->toArray();
                } elseif ($doctor->department_id) {
                    $departmentIds = [$doctor->department_id];
                }
            }
        } else {
            // For other roles, get all departments from users pivot table or department_id
            $user->load('departments');
            if ($user->departments->isNotEmpty()) {
                $departmentIds = $user->departments->pluck('id')->toArray();
            } elseif ($user->department_id) {
                $departmentIds = [$user->department_id];
            }
        }
        
        return $departmentIds;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Build query based on user role
        $query = MedicalRecord::with(['patient', 'doctor', 'appointment']);
        
        // Apply visibility rules based on user role (uses patient-department-doctor logic)
        // This handles department filtering, doctor relationships, and patient visibility
        $query->visibleTo($user);

        // ===== QUICK SEARCH (Multi-field) =====
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('diagnosis', 'like', "%{$search}%")
                  ->orWhere('symptoms', 'like', "%{$search}%")
                  ->orWhere('treatment', 'like', "%{$search}%")
                  ->orWhere('presenting_complaint', 'like', "%{$search}%")
                  ->orWhere('history_of_presenting_complaint', 'like', "%{$search}%")
                  ->orWhere('past_medical_history', 'like', "%{$search}%")
                  ->orWhere('drug_history', 'like', "%{$search}%")
                  ->orWhere('allergies', 'like', "%{$search}%")
                  ->orWhere('plan', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
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
            $query->whereHas('doctor', function($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }

        // ===== RECORD TYPE FILTERS =====
        if ($request->filled('record_type')) {
            $query->where('record_type', $request->record_type);
        }

        // ===== DATE & TIME FILTERS =====
        if ($request->filled('date_from')) {
            $dateFrom = \Carbon\Carbon::parse($request->date_from)->format('Y-m-d');
            $query->whereDate('record_date', '>=', $dateFrom);
        }
        if ($request->filled('date_to')) {
            $dateTo = \Carbon\Carbon::parse($request->date_to)->format('Y-m-d');
            $query->whereDate('record_date', '<=', $dateTo);
        }
        if ($request->filled('record_date')) {
            $date = \Carbon\Carbon::parse($request->record_date)->format('Y-m-d');
            $query->whereDate('record_date', $date);
        }
        if ($request->filled('created_from')) {
            $query->where('created_at', '>=', $request->created_from . ' 00:00:00');
        }
        if ($request->filled('created_to')) {
            $query->where('created_at', '<=', $request->created_to . ' 23:59:59');
        }

        // ===== DATE RANGE FILTERS =====
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('record_date', today());
                    break;
                case 'yesterday':
                    $query->whereDate('record_date', today()->copy()->subDay());
                    break;
                case 'this_week':
                    $query->whereBetween('record_date', [now()->startOfWeek(), now()->endOfWeek()]);
                    break;
                case 'last_week':
                    $lastWeek = now()->copy()->subWeek();
                    $query->whereBetween('record_date', [$lastWeek->startOfWeek(), $lastWeek->endOfWeek()]);
                    break;
                case 'this_month':
                    $query->whereMonth('record_date', now()->month)
                          ->whereYear('record_date', now()->year);
                    break;
                case 'last_month':
                    $lastMonth = now()->copy()->subMonth();
                    $query->whereMonth('record_date', $lastMonth->month)
                          ->whereYear('record_date', $lastMonth->year);
                    break;
                case 'this_year':
                    $query->whereYear('record_date', now()->year);
                    break;
            }
        }

        // ===== MEDICAL INFORMATION FILTERS =====
        if ($request->filled('diagnosis')) {
            $query->where('diagnosis', 'like', "%{$request->diagnosis}%");
        }
        if ($request->filled('symptoms')) {
            $query->where('symptoms', 'like', "%{$request->symptoms}%");
        }
        if ($request->filled('treatment')) {
            $query->where('treatment', 'like', "%{$request->treatment}%");
        }
        if ($request->filled('presenting_complaint')) {
            $query->where('presenting_complaint', 'like', "%{$request->presenting_complaint}%");
        }

        // ===== RELATIONSHIP FILTERS =====
        if ($request->filled('has_appointment')) {
            if ($request->has_appointment === 'yes') {
                $query->whereNotNull('appointment_id');
            } elseif ($request->has_appointment === 'no') {
                $query->whereNull('appointment_id');
            }
        }
        if ($request->filled('has_prescriptions')) {
            if ($request->has_prescriptions === 'yes') {
                $query->whereHas('prescriptions');
            } elseif ($request->has_prescriptions === 'no') {
                $query->whereDoesntHave('prescriptions');
            }
        }
        if ($request->filled('has_lab_reports')) {
            if ($request->has_lab_reports === 'yes') {
                $query->whereHas('labReports');
            } elseif ($request->has_lab_reports === 'no') {
                $query->whereDoesntHave('labReports');
            }
        }
        if ($request->filled('has_attachments')) {
            if ($request->has_attachments === 'yes') {
                $query->whereHas('attachments');
            } elseif ($request->has_attachments === 'no') {
                $query->whereDoesntHave('attachments');
            }
        }

        // ===== FOLLOW-UP FILTERS =====
        if ($request->filled('has_follow_up')) {
            if ($request->has_follow_up === 'yes') {
                $query->whereNotNull('follow_up_date');
            } elseif ($request->has_follow_up === 'no') {
                $query->whereNull('follow_up_date');
            }
        }
        if ($request->filled('follow_up_from')) {
            $query->where('follow_up_date', '>=', $request->follow_up_from);
        }
        if ($request->filled('follow_up_to')) {
            $query->where('follow_up_date', '<=', $request->follow_up_to);
        }
        if ($request->filled('follow_up_overdue')) {
            $query->where('follow_up_date', '<', today())
                  ->whereNotNull('follow_up_date');
        }

        // ===== PRIVACY FILTERS =====
        if ($request->filled('is_private')) {
            if ($request->is_private === 'yes') {
                $query->where('is_private', true);
            } elseif ($request->is_private === 'no') {
                $query->where('is_private', false);
            }
        }

        // ===== CREATOR FILTERS =====
        if ($request->filled('created_by')) {
            $query->where('created_by', $request->created_by);
        }
        if ($request->filled('my_records') && $user->id) {
            $query->where('created_by', $user->id);
        }

        // Prepare data for view
        $doctors = Doctor::with('user')->get()->map(function($doctor) {
            return [
                'id' => $doctor->id,
                'name' => $doctor->user ? $doctor->user->name : 'Unknown'
            ];
        });
        
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        $recordTypes = ['consultation', 'follow_up', 'emergency', 'routine_checkup', 'procedure'];

        // Sort by date and time
        $medicalRecords = $query->orderBy('record_date', 'desc')
                                ->orderBy('created_at', 'desc')
                                ->paginate(15)->appends($request->query());
        
        return view('staff.medical-records.index', compact('medicalRecords', 'doctors', 'departments', 'recordTypes'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Only doctors and nurses can create medical records
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            return redirect()->route('staff.medical-records.index')
                ->with('error', 'You do not have permission to create medical records.');
        }
        
        // Filter patients by department for all roles
        $query = Patient::active()->visibleTo(Auth::user());
        $departmentId = $this->getUserDepartmentId();
        if ($departmentId) {
            $query->byDepartment($departmentId);
        }
        $patients = $query->orderBy('first_name')->get();
        
        // Get selected patient_id and appointment_id from query parameters
        $selectedPatientId = $request->get('patient_id');
        $selectedAppointmentId = $request->get('appointment_id');
        
        // Get previous medical record for copy-forward if source_record_id is provided
        $previousRecord = null;
        $sourceRecordId = $request->get('source_record_id');
        if ($sourceRecordId) {
            $previousRecord = MedicalRecord::with(['patient', 'appointment'])
                ->where('patient_id', $selectedPatientId ?? $request->get('patient_id'))
                ->find($sourceRecordId);
        }
        
        // If appointment_id is provided, auto-select patient and pre-fill data
        $selectedAppointment = null;
        if ($selectedAppointmentId) {
            $selectedAppointment = Appointment::with(['patient', 'doctor'])
                ->find($selectedAppointmentId);
            if ($selectedAppointment && !$selectedPatientId) {
                $selectedPatientId = $selectedAppointment->patient_id;
            }
        }
        
        // Medical records should be created for appointments that have occurred or are confirmed to occur
        $appointmentsQuery = Appointment::with(['patient', 'doctor'])
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereDoesntHave('medicalRecord');
        
        // Filter appointments by department for doctors
        if ($departmentId) {
            $appointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        // If patient_id is provided, filter appointments for that patient
        if ($selectedPatientId) {
            $appointmentsQuery->where('patient_id', $selectedPatientId);
        }
        
        $appointments = $appointmentsQuery->latest()->get();
        
        // Get all doctors for dropdown (not filtered by active status)
        $doctors = Doctor::orderBy('first_name')->get();
        
        // Get previous medical records for this patient (for copy-forward)
        // Always return a Collection, even if empty, to avoid type issues
        $previousRecords = collect([]);
        if ($selectedPatientId) {
            $previousRecords = MedicalRecord::where('patient_id', $selectedPatientId)
                ->latest()
                ->limit(5)
                ->get(['id', 'record_date', 'record_type', 'presenting_complaint', 'diagnosis', 'past_medical_history', 'drug_history', 'allergies', 'social_history', 'family_history']);
        }
        
        return view('staff.medical-records.create', compact(
            'patients', 
            'appointments', 
            'doctors', 
            'selectedPatientId', 
            'selectedAppointmentId',
            'selectedAppointment',
            'previousRecord',
            'previousRecords'
        ));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Only doctors and nurses can create medical records
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            return redirect()->route('staff.medical-records.index')
                ->with('error', 'You do not have permission to create medical records.');
        }
        
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'doctor_id' => 'nullable|exists:users,id',
            'record_date' => 'required|date',
            'record_type' => 'required|string|in:consultation,follow_up,emergency,routine_checkup,procedure',
            'pre_consultation_verified' => 'required|accepted',
            'presenting_complaint' => 'required|string|max:1000',
            'history_of_presenting_complaint' => 'required|string',
            'past_medical_history' => 'required|string',
            'drug_history' => 'required|string',
            'allergies' => 'required|string',
            'social_history' => 'nullable|string',
            'family_history' => 'nullable|string',
            'ideas_concerns_expectations' => 'required|string',
            'plan' => 'required|string',
            'vital_signs' => 'nullable|array',
            'vital_signs.temperature' => 'nullable|numeric',
            'vital_signs.blood_pressure' => 'nullable|string',
            'vital_signs.pulse' => 'nullable|integer',
            'vital_signs.respiratory_rate' => 'nullable|integer',
            'vital_signs.oxygen_saturation' => 'nullable|numeric',
            'vital_signs.weight' => 'nullable|numeric',
            'vital_signs.height' => 'nullable|numeric',
            'vital_signs.bmi' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,txt,zip,rar|max:10240', // 10MB max per file
            'attachments_category.*' => 'nullable|in:photo,results,documents,other',
            'attachments_description.*' => 'nullable|string|max:500',
        ]);
        
        // Handle doctor_id - need to map to doctors table if constraint still exists
        $doctorId = null;
        if ($user->role === 'doctor') {
            // For doctors, try to find their corresponding doctor record
            $doctorRecord = \App\Models\Doctor::where('user_id', $user->id)->first();
            $doctorId = $doctorRecord ? $doctorRecord->id : null;
        } elseif ($request->doctor_id) {
            // For non-doctors, get the doctor record ID from the selected user
            $selectedUser = \App\Models\User::find($request->doctor_id);
            if ($selectedUser && $selectedUser->role === 'doctor') {
                $doctorRecord = \App\Models\Doctor::where('user_id', $selectedUser->id)->first();
                $doctorId = $doctorRecord ? $doctorRecord->id : null;
            }
        }

        $medicalRecord = MedicalRecord::create([
            'patient_id' => $request->patient_id,
            'appointment_id' => $request->appointment_id,
            'doctor_id' => $doctorId,
            'record_date' => $request->record_date,
            'record_type' => $request->record_type,
            'presenting_complaint' => $request->presenting_complaint,
            'history_of_presenting_complaint' => $request->history_of_presenting_complaint,
            'past_medical_history' => $request->past_medical_history,
            'drug_history' => $request->drug_history,
            'allergies' => $request->allergies,
            'social_history' => $request->social_history,
            'family_history' => $request->family_history,
            'ideas_concerns_expectations' => $request->ideas_concerns_expectations,
            'plan' => $request->plan,
            'vital_signs' => $request->vital_signs,
            'notes' => $request->notes,
            'created_by' => $user->id,
        ]);
        
        // Get patient and update department/doctor assignment if needed
        $patient = \App\Models\Patient::find($request->patient_id);
        $updateData = [];
        
        // Set patient's department from doctor if not set
        if ($doctorId) {
            $doctorRecord = Doctor::find($doctorId);
            if ($doctorRecord && $doctorRecord->department_id) {
                if (!$patient->department_id) {
                    $updateData['department_id'] = $doctorRecord->department_id;
                }
                // Set assigned_doctor_id if not set
                if (!$patient->assigned_doctor_id) {
                    $updateData['assigned_doctor_id'] = $doctorId;
                }
                // Set created_by_doctor_id if not set and current user is a doctor
                if (!$patient->created_by_doctor_id && $user->role === 'doctor') {
                    $userDoctor = Doctor::where('user_id', $user->id)->first();
                    if ($userDoctor && $userDoctor->id == $doctorId) {
                        $updateData['created_by_doctor_id'] = $doctorId;
                    }
                }
            }
        }
        
        // Update patient if needed
        if (!empty($updateData)) {
            $patient->update($updateData);
        }
        
        $patientName = $patient ? ($patient->first_name . ' ' . $patient->last_name) : 'Unknown Patient';
        
        // Log pre-consultation verification confirmation
        \App\Models\UserActivity::log([
            'user_id' => $user->id,
            'action' => 'pre_consultation_verified',
            'model_type' => MedicalRecord::class,
            'model_id' => $medicalRecord->id,
            'description' => "Pre-consultation verification confirmed by {$user->name} for patient {$patientName}",
            'severity' => 'medium',
        ]);
        
        // Handle file uploads
        $this->handleFileUploads($request, $medicalRecord, $user);
        
        return redirect()->route('staff.medical-records.show', $medicalRecord)
            ->with('success', 'Medical record created successfully.');
    }

    public function show(MedicalRecord $medicalRecord)
    {
        $user = Auth::user();
        
        // Check if user can view this record
        $departmentId = $this->getUserDepartmentId();
        if ($departmentId && $medicalRecord->doctor && $medicalRecord->doctor->department_id !== $departmentId) {
            // Department-based access check
            return redirect()->route('staff.medical-records.index')
                ->with('error', 'You do not have permission to view this medical record.');
        } elseif (!$departmentId && $medicalRecord->created_by !== $user->id && 
            (!$medicalRecord->appointment || $medicalRecord->appointment->staff_id !== $user->id)) {
            return redirect()->route('staff.medical-records.index')
                ->with('error', 'You do not have permission to view this medical record.');
        }
        
        $medicalRecord->load(['patient', 'doctor', 'appointment', 'prescriptions', 'labReports', 'attachments.uploader']);
        
        return view('staff.medical-records.show', compact('medicalRecord'));
    }

    public function edit(MedicalRecord $medicalRecord)
    {
        $user = Auth::user();
        
        // Only doctors can edit medical records, and only their own
        if ($user->role !== 'doctor') {
            return redirect()->route('staff.medical-records.index')
                ->with('error', 'You do not have permission to edit this medical record.');
        }
        
        // Check if this doctor can edit this record
        if ($medicalRecord->doctor_id) {
            $doctorRecord = \App\Models\Doctor::where('user_id', $user->id)->first();
            if (!$doctorRecord || $medicalRecord->doctor_id !== $doctorRecord->id) {
                return redirect()->route('staff.medical-records.index')
                    ->with('error', 'You can only edit your own medical records.');
            }
        }
        
        // Filter patients by department for all roles
        $query = Patient::active()->visibleTo(Auth::user());
        $departmentId = $this->getUserDepartmentId();
        if ($departmentId) {
            $query->byDepartment($departmentId);
        }
        $patients = $query->orderBy('first_name')->get();
        
        // For editing, include both confirmed and completed appointments
        // Also include the currently linked appointment regardless of status
        $appointmentsQuery = Appointment::with(['patient', 'doctor'])
            ->where('patient_id', $medicalRecord->patient_id); // Only appointments for this patient
        
        // Filter appointments by department for doctors
        if ($departmentId) {
            $appointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        
        $appointments = $appointmentsQuery->where(function($q) use ($medicalRecord) {
                // Show appointments that don't have medical records OR the current one
                $q->where(function($subQ) {
                    $subQ->whereIn('status', ['confirmed', 'completed'])
                         ->whereDoesntHave('medicalRecord');
                })->orWhere('id', $medicalRecord->appointment_id);
            })
            ->latest()
            ->get();
        
        // Load attachments
        $medicalRecord->load('attachments.uploader');
        
        // Get all doctors for dropdown (not filtered by active status)
        $doctors = Doctor::orderBy('first_name')->get();
        
        return view('staff.medical-records.edit', compact('medicalRecord', 'patients', 'appointments', 'doctors'));
    }

    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        $user = Auth::user();
        
        // Only doctors can edit medical records, and only their own
        if ($user->role !== 'doctor') {
            return redirect()->route('staff.medical-records.index')
                ->with('error', 'You do not have permission to edit this medical record.');
        }
        
        // Check if this doctor can edit this record
        // Allow editing if doctor_id is null or if it matches the current doctor, or if user created it
        if ($medicalRecord->doctor_id) {
            $doctorRecord = \App\Models\Doctor::where('user_id', $user->id)->first();
            // Only restrict if doctor_id exists and doesn't match
            if ($doctorRecord && $medicalRecord->doctor_id !== $doctorRecord->id) {
                // Check if user created the record instead
                if ($medicalRecord->created_by !== $user->id) {
                    return redirect()->route('staff.medical-records.edit', $medicalRecord)
                        ->with('error', 'You can only edit your own medical records.');
                }
            }
        }
        
        try {
            $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'record_date' => 'required|date',
            'record_type' => 'required|string|in:consultation,follow_up,emergency,routine_checkup,procedure',
            'presenting_complaint' => 'required|string|max:1000',
            'history_of_presenting_complaint' => 'required|string',
            'past_medical_history' => 'required|string',
            'drug_history' => 'required|string',
            'allergies' => 'required|string',
            'social_history' => 'nullable|string',
            'family_history' => 'nullable|string',
            'ideas_concerns_expectations' => 'required|string',
            'plan' => 'required|string',
            'vital_signs' => 'nullable|array',
            'vital_signs.temperature' => 'nullable|numeric',
            'vital_signs.blood_pressure' => 'nullable|string',
            'vital_signs.pulse' => 'nullable|integer',
            'vital_signs.respiratory_rate' => 'nullable|integer',
            'vital_signs.oxygen_saturation' => 'nullable|numeric',
            'vital_signs.weight' => 'nullable|numeric',
            'vital_signs.height' => 'nullable|numeric',
            'vital_signs.bmi' => 'nullable|numeric',
            'notes' => 'nullable|string',
            'edit_reason' => 'required|string|min:10|max:500',
            'attachments.*' => 'nullable|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png,gif,txt,zip,rar|max:10240', // 10MB max per file
            'attachments_category.*' => 'nullable|in:photo,results,documents,other',
            'attachments_description.*' => 'nullable|string|max:500',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->route('staff.medical-records.edit', $medicalRecord)
                ->withErrors($e->errors())
                ->withInput();
        }
        
        // Verify doctor_id is valid and accessible
        $departmentId = $this->getUserDepartmentId();
        $doctorId = $request->doctor_id ?? $medicalRecord->doctor_id;
        
        // If user has department restriction, verify the doctor is in the same department
        if ($departmentId && $doctorId) {
            $doctor = \App\Models\Doctor::find($doctorId);
            if ($doctor && $doctor->department_id !== $departmentId) {
                // Doctor is from different department, preserve existing doctor_id
                $doctorId = $medicalRecord->doctor_id;
            }
        }
        
        // Verify appointment exists and belongs to the same patient (if provided)
        $appointmentId = $request->appointment_id;
        if ($appointmentId && $request->patient_id) {
            $appointment = \App\Models\Appointment::where('id', $appointmentId)
                ->where('patient_id', $request->patient_id)
                ->first();
            
            if (!$appointment) {
                // Appointment doesn't exist or doesn't belong to patient, set to null
                $appointmentId = null;
            }
        }
        
        // Build update data array explicitly - preserve doctor_id if not provided
        $updateData = [
            'patient_id' => $request->patient_id,
            'doctor_id' => $doctorId,
            'appointment_id' => $appointmentId,
            'record_date' => $request->record_date,
            'record_type' => $request->record_type,
            'presenting_complaint' => $request->presenting_complaint,
            'history_of_presenting_complaint' => $request->history_of_presenting_complaint,
            'past_medical_history' => $request->past_medical_history,
            'drug_history' => $request->drug_history,
            'allergies' => $request->allergies,
            'social_history' => $request->social_history,
            'family_history' => $request->family_history,
            'ideas_concerns_expectations' => $request->ideas_concerns_expectations,
            'plan' => $request->plan,
            'vital_signs' => $request->vital_signs,
            'notes' => $request->notes,
            'updated_by' => $user->id,
        ];
        
        // Use database transaction to ensure atomicity
        try {
            DB::beginTransaction();
            
            // Log before update
            \Log::info('Starting medical record update (Staff)', [
                'medical_record_id' => $medicalRecord->id,
                'exists_before' => $medicalRecord->exists,
                'patient_id' => $updateData['patient_id'],
                'doctor_id' => $updateData['doctor_id'],
            ]);
            
            // Perform the update
            $updateResult = $medicalRecord->update($updateData);
            
            if (!$updateResult) {
                DB::rollBack();
                \Log::error('Medical record update failed (Staff)', [
                    'medical_record_id' => $medicalRecord->id,
                ]);
                throw new \Exception('Failed to update medical record in database.');
            }
            
            \Log::info('Medical record update completed (Staff)', [
                'medical_record_id' => $medicalRecord->id,
                'update_result' => $updateResult,
                'exists_after_update' => $medicalRecord->exists,
            ]);
            
            // Get a fresh instance from database to verify it still exists
            // Don't use refresh() as it might cause issues with relationships
            $freshRecord = MedicalRecord::with(['patient', 'doctor'])->find($medicalRecord->id);
            if (!$freshRecord) {
                DB::rollBack();
                \Log::error('Medical record disappeared after update - cannot find in database - transaction rolled back (Staff)', [
                    'medical_record_id' => $medicalRecord->id,
                    'patient_id' => $updateData['patient_id'],
                    'doctor_id' => $updateData['doctor_id'],
                ]);
                throw new \Exception('Medical record was deleted during update - record not found in database. Transaction rolled back. This may be due to the patient or doctor being deleted.');
            }
            
            // Use the fresh record instead of refreshing the old one
            $medicalRecord = $freshRecord;
            
            // Verify patient and doctor still exist (cascade delete would have removed record if they were deleted)
            if (!$medicalRecord->patient) {
                DB::rollBack();
                \Log::error('Patient was deleted causing medical record cascade delete - transaction rolled back (Staff)', [
                    'medical_record_id' => $medicalRecord->id,
                    'patient_id' => $updateData['patient_id'],
                ]);
                throw new \Exception('Patient was deleted, causing the medical record to be deleted. Transaction rolled back.');
            }
            
            if (!$medicalRecord->doctor) {
                DB::rollBack();
                \Log::error('Doctor was deleted causing medical record cascade delete - transaction rolled back (Staff)', [
                    'medical_record_id' => $medicalRecord->id,
                    'doctor_id' => $updateData['doctor_id'],
                ]);
                throw new \Exception('Doctor was deleted, causing the medical record to be deleted. Transaction rolled back.');
            }
            
            \Log::info('Medical record verified after update (Staff)', [
                'medical_record_id' => $medicalRecord->id,
                'exists' => $medicalRecord->exists,
                'patient_id' => $medicalRecord->patient_id,
                'doctor_id' => $medicalRecord->doctor_id,
            ]);
            
            // Get patient name for audit log
            $patient = $medicalRecord->patient;
            $patientName = $patient ? ($patient->first_name . ' ' . $patient->last_name) : 'Unknown Patient';
            
            // Log the edit in audit trail
            \App\Models\UserActivity::log([
                'user_id' => $user->id,
                'action' => 'update',
                'model_type' => MedicalRecord::class,
                'model_id' => $medicalRecord->id,
                'description' => "Medical record updated by {$user->name} for patient {$patientName}. Reason: {$request->edit_reason}",
                'new_values' => [
                    'edit_reason' => $request->edit_reason,
                    'updated_fields' => array_keys($request->except(['_token', '_method', 'attachments', 'attachments_category', 'attachments_description', 'edit_reason'])),
                ],
                'severity' => 'medium',
            ]);
            
            // Handle file uploads - must happen inside transaction
            $uploadError = null;
            try {
                // Verify record still exists before file upload
                $verifyBeforeUpload = MedicalRecord::find($medicalRecord->id);
                if (!$verifyBeforeUpload) {
                    throw new \Exception('Medical record was deleted before file upload. Transaction will be rolled back.');
                }
                
                $this->handleFileUploads($request, $medicalRecord, $user);
                
                // Verify record still exists after file upload
                $verifyAfterUpload = MedicalRecord::find($medicalRecord->id);
                if (!$verifyAfterUpload) {
                    throw new \Exception('Medical record was deleted during file upload. Transaction will be rolled back.');
                }
            } catch (\Exception $e) {
                // If record was deleted, rollback and abort
                if (str_contains($e->getMessage(), 'no longer exists') || 
                    str_contains($e->getMessage(), 'was deleted') ||
                    str_contains($e->getMessage(), 'not found')) {
                    DB::rollBack();
                    \Log::error('Medical record was deleted during file upload - transaction rolled back (Staff)', [
                        'medical_record_id' => $medicalRecord->id,
                        'error' => $e->getMessage()
                    ]);
                    throw new \Exception('Medical record was deleted during file upload. Update aborted and rolled back.');
                }
                // For other file upload errors, log but don't fail the update
                \Log::error('File upload error during medical record update (Staff): ' . $e->getMessage());
                $uploadError = $e->getMessage();
            }
            
            // Final verification before commit - ensure record still exists
            $finalCheck = MedicalRecord::find($medicalRecord->id);
            if (!$finalCheck) {
                DB::rollBack();
                \Log::error('Medical record was deleted during update process (Staff)', [
                    'medical_record_id' => $medicalRecord->id,
                    'original_patient_id' => $updateData['patient_id'],
                    'original_doctor_id' => $updateData['doctor_id'],
                ]);
                throw new \Exception('Medical record was deleted during update. Transaction rolled back. This may have occurred due to the patient or doctor being deleted. Please check the logs for details.');
            }
            
            // Commit transaction if everything succeeded
            DB::commit();
            
            \Log::info('Medical record update transaction committed (Staff)', [
                'medical_record_id' => $finalCheck->id,
            ]);
            
            $successMessage = 'Medical record updated successfully.';
            if ($uploadError) {
                $successMessage .= ' However, there was an error uploading files: ' . $uploadError;
            }
            
            // Use the verified record for redirect
            return redirect()->route('staff.medical-records.show', $finalCheck)
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            // Rollback transaction on any error
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            
            \Log::error('Medical record update transaction rolled back (Staff)', [
                'medical_record_id' => $medicalRecord->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            \Log::error('Medical record update error (Staff): ' . $e->getMessage(), [
                'medical_record_id' => $medicalRecord->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Check if record still exists before redirecting to edit
            $checkRecord = MedicalRecord::find($medicalRecord->id ?? null);
            if (!$checkRecord) {
                // Record was deleted, redirect to index
                return redirect()->route('staff.medical-records.index')
                    ->with('error', 'Medical record was deleted during update. Error: ' . $e->getMessage());
            }
            
            return redirect()->route('staff.medical-records.edit', $checkRecord)
                ->with('error', 'Failed to update medical record. Please try again. Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function createFromAppointment(Appointment $appointment)
    {
        $user = Auth::user();
        
        // Only doctors and nurses can create medical records
        if (!in_array($user->role, ['doctor', 'nurse'])) {
            return redirect()->route('staff.medical-records.index')
                ->with('error', 'You do not have permission to create medical records.');
        }
        
        // Check if medical record already exists for this appointment
        if ($appointment->medicalRecord) {
            return redirect()->route('staff.medical-records.show', $appointment->medicalRecord)
                ->with('info', 'Medical record already exists for this appointment.');
        }
        
        $appointment->load(['patient', 'doctor']);
        
        return view('staff.medical-records.create-from-appointment', compact('appointment'));
    }

    public function getAppointmentsByPatient(Request $request)
    {
        $patientId = $request->get('patient_id');
        
        if (!$patientId) {
            return response()->json([]);
        }
        
        // Only show confirmed and completed appointments for medical records
        $appointments = Appointment::with(['doctor'])
            ->where('patient_id', $patientId)
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereDoesntHave('medicalRecord')
            ->latest()
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'text' => "#{$appointment->appointment_number} - " . 
                             $appointment->appointment_date->format('M d, Y') . 
                             " at {$appointment->appointment_time}" .
                             ($appointment->doctor ? " with Dr. {$appointment->doctor->name}" : ''),
                    'date' => $appointment->appointment_date->format('Y-m-d'),
                    'time' => $appointment->appointment_time,
                    'doctor' => $appointment->doctor ? $appointment->doctor->name : null,
                ];
            });
        
        return response()->json($appointments);
    }

    public function destroy(Request $request, MedicalRecord $medicalRecord)
    {
        $user = Auth::user();
        
        // Only doctors can delete medical records, and only their own
        if ($user->role !== 'doctor') {
            return redirect()->route('staff.medical-records.index')
                ->with('error', 'You do not have permission to delete medical records.');
        }
        
        // Check if this doctor can delete this record
        if ($medicalRecord->doctor_id) {
            $doctorRecord = \App\Models\Doctor::where('user_id', $user->id)->first();
            if (!$doctorRecord || $medicalRecord->doctor_id !== $doctorRecord->id) {
                return redirect()->route('staff.medical-records.index')
                    ->with('error', 'You can only delete your own medical records.');
            }
        }
        
        try {
            // Store record information for logging
            $recordInfo = [
                'record_id' => $medicalRecord->id,
                'patient_name' => $medicalRecord->patient->first_name . ' ' . $medicalRecord->patient->last_name,
                'record_date' => $medicalRecord->record_date,
                'record_type' => $medicalRecord->record_type,
                'deleted_by' => $user->id,
                'deleted_at' => now(),
            ];
            
            // Log the deletion for audit purposes
            \Log::info('Medical record deleted', $recordInfo);
            
            // Delete the medical record
            $medicalRecord->delete();
            
            return redirect()->route('staff.medical-records.index')
                ->with('success', 'Medical record has been permanently deleted.');
                
        } catch (\Exception $e) {
            \Log::error('Failed to delete medical record: ' . $e->getMessage());
            
            return redirect()->route('staff.medical-records.edit', $medicalRecord)
                ->with('error', 'Failed to delete medical record. Please try again.');
        }
    }

    /**
     * Handle file uploads for medical records.
     * 
     * @param Request $request
     * @param MedicalRecord $medicalRecord
     * @param \App\Models\User $user
     * @return void
     */
    private function handleFileUploads(Request $request, MedicalRecord $medicalRecord, $user): void
    {
        // Check if files are uploaded
        if (!$request->hasFile('attachments')) {
            return;
        }

        // Ensure the medical record still exists
        if (!$medicalRecord->exists || !$medicalRecord->id) {
            \Log::error('Cannot upload files: Medical record does not exist', [
                'medical_record_id' => $medicalRecord->id ?? 'null'
            ]);
            throw new \Exception('Medical record not found. Cannot upload files.');
        }

        // Load patient relationship if not already loaded
        if (!$medicalRecord->relationLoaded('patient')) {
            $medicalRecord->load('patient');
        }
        
        // Verify patient exists
        if (!$medicalRecord->patient) {
            \Log::error('Cannot upload files: Patient not found for medical record', [
                'medical_record_id' => $medicalRecord->id,
                'patient_id' => $medicalRecord->patient_id
            ]);
            throw new \Exception('Patient not found for this medical record. Cannot upload files.');
        }

        $files = $request->file('attachments');
        $categories = $request->input('attachments_category', []);
        $descriptions = $request->input('attachments_description', []);

        // Filter out empty file inputs (when no file is selected)
        if (is_array($files)) {
            $files = array_filter($files, function($file) {
                return $file && $file->isValid();
            });
        } else {
            $files = $files && $files->isValid() ? [$files] : [];
        }

        // If no valid files, return early
        if (empty($files)) {
            return;
        }

        // Check total number of files (including existing)
        $existingCount = $medicalRecord->attachments()->count();
        $newCount = count($files);
        
        if ($existingCount + $newCount > 10) {
            throw new \Exception('Maximum 10 files allowed per medical record. Current: ' . $existingCount . ', Attempting to add: ' . $newCount);
        }

        // Ensure files is an array (should already be at this point)
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $index => $file) {
            if (!$file || !$file->isValid()) {
                continue;
            }

            // CRITICAL: Verify record still exists before processing EACH file
            // This prevents deletion during multi-file upload
            $currentRecordCheck = \App\Models\MedicalRecord::with(['patient', 'doctor'])->find($medicalRecord->id);
            if (!$currentRecordCheck || !$currentRecordCheck->exists) {
                \Log::error('Medical record disappeared during multi-file upload loop', [
                    'medical_record_id' => $medicalRecord->id,
                    'file_index' => $index,
                    'filename' => $file->getClientOriginalName(),
                ]);
                throw new \Exception('Medical record was deleted during file upload process. Cannot continue.');
            }
            
            // Verify patient and doctor still exist for this record
            if (!$currentRecordCheck->patient || !$currentRecordCheck->doctor) {
                \Log::error('Patient or doctor missing during multi-file upload', [
                    'medical_record_id' => $currentRecordCheck->id,
                    'patient_id' => $currentRecordCheck->patient_id,
                    'doctor_id' => $currentRecordCheck->doctor_id,
                    'file_index' => $index,
                ]);
                throw new \Exception('Patient or doctor was deleted during file upload. Cannot continue.');
            }
            
            // Update the medical record reference to the fresh instance
            $medicalRecord = $currentRecordCheck;

            // Validate file type and size - SECURITY: Validate actual MIME type, not just extension
            $allowedMimeTypes = [
                // Documents
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document', // .docx
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
                'text/plain',
                // Images
                'image/jpeg',
                'image/jpg',
                'image/png',
                'image/gif',
                // Archives (restricted - should be scanned)
                'application/zip',
                'application/x-rar-compressed',
                'application/x-rar',
            ];
            
            $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'jpg', 'jpeg', 'png', 'gif', 'txt', 'zip', 'rar'];
            
            // Get actual MIME type and extension
            $mimeType = $file->getMimeType();
            $extension = strtolower($file->getClientOriginalExtension());
            
            // Validate both MIME type and extension for security
            if (!in_array($mimeType, $allowedMimeTypes) || !in_array($extension, $allowedExtensions)) {
                \Log::warning('Invalid file upload attempt', [
                    'filename' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'extension' => $extension,
                    'user_id' => $user->id ?? null,
                ]);
                continue; // Skip invalid file types
            }
            
            // Additional security: Verify extension matches MIME type
            $mimeToExtension = [
                'application/pdf' => 'pdf',
                'application/msword' => 'doc',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
                'application/vnd.ms-excel' => 'xls',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
                'text/plain' => 'txt',
                'image/jpeg' => ['jpg', 'jpeg'],
                'image/png' => 'png',
                'image/gif' => 'gif',
                'application/zip' => 'zip',
                'application/x-rar-compressed' => 'rar',
                'application/x-rar' => 'rar',
            ];
            
            $expectedExtensions = is_array($mimeToExtension[$mimeType] ?? null) 
                ? $mimeToExtension[$mimeType] 
                : [$mimeToExtension[$mimeType] ?? ''];
            
            if (!in_array($extension, $expectedExtensions)) {
                \Log::warning('File extension does not match MIME type', [
                    'filename' => $file->getClientOriginalName(),
                    'mime_type' => $mimeType,
                    'extension' => $extension,
                    'expected_extensions' => $expectedExtensions,
                ]);
                continue; // Skip suspicious files
            }

            // Check file size (10MB max)
            if ($file->getSize() > 10 * 1024 * 1024) {
                \Log::warning('File too large', [
                    'filename' => $file->getClientOriginalName(),
                    'size' => $file->getSize(),
                ]);
                continue; // Skip files larger than 10MB
            }

            // Get category and description
            $category = $categories[$index] ?? 'documents';
            $description = $descriptions[$index] ?? null;

            // Generate filename: PatientFullName-Type-Date.extension
            $originalName = $file->getClientOriginalName();
            
            // Get patient full name - patient relationship is already loaded and verified above
            $patient = $medicalRecord->patient;
            $patientFullName = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
            if (empty($patientFullName)) {
                $patientFullName = 'Unknown';
            }
            
            // Sanitize patient name (remove special characters, replace spaces with underscore)
            $patientName = preg_replace('/[^a-zA-Z0-9\s]/', '', $patientFullName);
            $patientName = preg_replace('/\s+/', '_', trim($patientName));
            
            // Format category (capitalize first letter)
            $categoryFormatted = ucfirst(strtolower($category));
            
            // Format date as dd-mm-yyyy
            $dateFormatted = now()->format('d-m-Y');
            
            // Build display filename: PatientFullName-Type-Date.extension (without timestamp for cleaner display)
            $displayFileName = $patientName . '-' . $categoryFormatted . '-' . $dateFormatted . '.' . $extension;
            
            // Build filename with timestamp and random string to ensure uniqueness for storage
            $filename = $patientName . '-' . $categoryFormatted . '-' . $dateFormatted . '-' . time() . '_' . Str::random(6) . '.' . $extension;
            
            try {
                // Verify medical record still exists BEFORE storing file
                // Get fresh instance from database to ensure it exists
                $currentRecord = \App\Models\MedicalRecord::with(['patient', 'doctor'])->find($medicalRecord->id);
                if (!$currentRecord || !$currentRecord->exists) {
                    \Log::error('Medical record disappeared before creating attachment', [
                        'medical_record_id' => $medicalRecord->id ?? 'null',
                        'filename' => $displayFileName
                    ]);
                    throw new \Exception('Medical record no longer exists. Cannot upload file.');
                }
                
                // Verify patient and doctor still exist (prevent cascade delete issues)
                if (!$currentRecord->patient || !$currentRecord->doctor) {
                    \Log::error('Patient or doctor missing for medical record before file upload', [
                        'medical_record_id' => $currentRecord->id,
                        'patient_id' => $currentRecord->patient_id,
                        'doctor_id' => $currentRecord->doctor_id,
                    ]);
                    throw new \Exception('Patient or doctor no longer exists for this medical record. Cannot upload file.');
                }
                
                // Store file in private disk - use current record ID
                $path = $file->storeAs('medical-records/' . $currentRecord->id, $filename, 'private');

                // Double-check record still exists after file storage
                $verifyRecord = \App\Models\MedicalRecord::find($currentRecord->id);
                if (!$verifyRecord) {
                    \Log::error('Medical record disappeared after file storage', [
                        'medical_record_id' => $currentRecord->id,
                        'filename' => $displayFileName,
                    ]);
                    // Delete the uploaded file if record doesn't exist
                    try {
                        \Illuminate\Support\Facades\Storage::disk('private')->delete($path);
                    } catch (\Exception $e) {
                        // Ignore file deletion errors
                    }
                    throw new \Exception('Medical record was deleted after file storage. File removed.');
                }

                // Create attachment record using verified record ID
                $attachment = MedicalRecordAttachment::create([
                    'medical_record_id' => $verifyRecord->id,
                    'uploaded_by' => $user->id,
                    'file_name' => $displayFileName,
                    'file_path' => $path,
                    'file_type' => $file->getMimeType(),
                    'file_extension' => $extension,
                    'file_size' => $file->getSize(),
                    'storage_disk' => 'private',
                    'file_category' => $category,
                    'description' => $description,
                    'is_private' => true,
                    'virus_scan_status' => 'pending', // Will be scanned asynchronously
                ]);
            } catch (\Exception $e) {
                \Log::error('Failed to create attachment record', [
                    'medical_record_id' => $medicalRecord->id ?? 'null',
                    'filename' => $displayFileName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // Delete the uploaded file if attachment creation failed
                if (isset($path)) {
                    try {
                        Storage::disk('private')->delete($path);
                    } catch (\Exception $deleteException) {
                        // Ignore file deletion errors
                    }
                }
                
                // Continue with next file instead of failing entire upload
                continue;
            }

            // Log file upload in audit trail
            \App\Models\UserActivity::log([
                'user_id' => $user->id,
                'action' => 'file_upload',
                'model_type' => MedicalRecordAttachment::class,
                'model_id' => $attachment->id,
                'description' => "File uploaded: {$displayFileName} ({$attachment->file_size_human}) - Category: {$category}",
                'new_values' => [
                    'file_name' => $displayFileName,
                    'file_type' => $file->getMimeType(),
                    'file_size' => $file->getSize(),
                    'file_category' => $category,
                ],
                'severity' => 'medium',
            ]);
        }
    }
}
