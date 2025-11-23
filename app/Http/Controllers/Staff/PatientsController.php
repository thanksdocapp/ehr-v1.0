<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Exception;

class PatientsController extends Controller
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
        
        // Apply visibility filter based on user role
        // Admins see all, doctors see based on department/created_by, staff see by department
        $query = Patient::query()->visibleTo($user);

        // ===== QUICK SEARCH (Multi-field) =====
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('patient_id', 'like', "%{$search}%")
                  ->orWhere('insurance_number', 'like', "%{$search}%");
            });
        }

        // ===== DEMOGRAPHIC FILTERS =====
        if ($request->filled('first_name')) {
            $query->where('first_name', 'like', "%{$request->first_name}%");
        }
        if ($request->filled('last_name')) {
            $query->where('last_name', 'like', "%{$request->last_name}%");
        }
        if ($request->filled('dob_from')) {
            $dobFrom = Carbon::parse($request->dob_from)->format('Y-m-d');
            $query->whereDate('date_of_birth', '>=', $dobFrom);
        }
        if ($request->filled('dob_to')) {
            $dobTo = Carbon::parse($request->dob_to)->format('Y-m-d');
            $query->whereDate('date_of_birth', '<=', $dobTo);
        }
        if ($request->filled('age_min')) {
            $maxDOB = now()->subYears($request->age_min)->format('Y-m-d');
            $query->whereDate('date_of_birth', '<=', $maxDOB);
        }
        if ($request->filled('age_max')) {
            $minDOB = now()->subYears($request->age_max + 1)->addDay()->format('Y-m-d');
            $query->whereDate('date_of_birth', '>=', $minDOB);
        }
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }
        if ($request->filled('city')) {
            $query->where('city', 'like', "%{$request->city}%");
        }
        if ($request->filled('country')) {
            $query->where('country', 'like', "%{$request->country}%");
        }
        if ($request->filled('postal_code')) {
            $query->where('postal_code', 'like', "%{$request->postal_code}%");
        }

        // ===== REGISTRATION & STATUS FILTERS =====
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }
        if ($request->filled('reg_from')) {
            $regFrom = Carbon::parse($request->reg_from)->format('Y-m-d') . ' 00:00:00';
            $query->where('created_at', '>=', $regFrom);
        }
        if ($request->filled('reg_to')) {
            $regTo = Carbon::parse($request->reg_to)->format('Y-m-d') . ' 23:59:59';
            $query->where('created_at', '<=', $regTo);
        }
        if ($request->filled('patient_type')) {
            if ($request->patient_type === 'insurance') {
                $query->whereNotNull('insurance_provider');
            } elseif ($request->patient_type === 'private') {
                $query->whereNull('insurance_provider');
            }
        }
        if ($request->filled('assigned_doctor_id')) {
            $query->where('assigned_doctor_id', $request->assigned_doctor_id);
        }
        if ($request->filled('department_id')) {
            $query->byDepartment($request->department_id);
        }

        // ===== ALERTS FILTERS =====
        if ($request->filled('has_alert')) {
            if ($request->has_alert === 'true' || $request->has_alert === '1') {
                $query->whereHas('alerts', function($q) {
                    $q->where('active', true);
                });
            } elseif ($request->has_alert === 'false' || $request->has_alert === '0') {
                $query->whereDoesntHave('alerts', function($q) {
                    $q->where('active', true);
                });
            }
        }
        if ($request->filled('alert_severity')) {
            $query->whereHas('alerts', function($q) use ($request) {
                $q->where('active', true)->where('severity', $request->alert_severity);
            });
        }
        if ($request->filled('alert_type')) {
            $query->whereHas('alerts', function($q) use ($request) {
                $q->where('active', true)->where('type', $request->alert_type);
            });
        }

        // ===== APPOINTMENTS/ENCOUNTERS FILTERS =====
        if ($request->filled('last_appt_from')) {
            $lastApptFrom = Carbon::parse($request->last_appt_from)->format('Y-m-d');
            $query->whereHas('appointments', function($q) use ($lastApptFrom) {
                $q->whereDate('appointment_date', '>=', $lastApptFrom);
            });
        }
        if ($request->filled('last_appt_to')) {
            $lastApptTo = Carbon::parse($request->last_appt_to)->format('Y-m-d');
            $query->whereHas('appointments', function($q) use ($lastApptTo) {
                $q->whereDate('appointment_date', '<=', $lastApptTo);
            });
        }
        if ($request->filled('next_appt_from')) {
            $nextApptFrom = Carbon::parse($request->next_appt_from)->format('Y-m-d');
            $query->whereHas('appointments', function($q) use ($nextApptFrom) {
                $q->whereDate('appointment_date', '>=', $nextApptFrom)
                  ->where('status', '!=', 'cancelled');
            });
        }
        if ($request->filled('next_appt_to')) {
            $nextApptTo = Carbon::parse($request->next_appt_to)->format('Y-m-d');
            $query->whereHas('appointments', function($q) use ($nextApptTo) {
                $q->whereDate('appointment_date', '<=', $nextApptTo)
                  ->where('status', '!=', 'cancelled');
            });
        }
        if ($request->filled('appointment_type')) {
            if ($request->appointment_type === 'online') {
                $query->whereHas('appointments', function($q) {
                    $q->where('is_online', true);
                });
            } elseif ($request->appointment_type === 'in_person') {
                $query->whereHas('appointments', function($q) {
                    $q->where('is_online', false);
                });
            } elseif ($request->appointment_type === 'phone') {
                $query->whereHas('appointments', function($q) {
                    $q->where('type', 'phone');
                });
            }
        }
        if ($request->filled('visits_in_last')) {
            $months = (int)$request->visits_in_last;
            $cutoffDate = now()->subMonths($months);
            $query->whereHas('appointments', function($q) use ($cutoffDate) {
                $q->where('appointment_date', '>=', $cutoffDate);
            });
        }
        if ($request->filled('visit_count_min')) {
            $minCount = (int)$request->visit_count_min;
            $query->withCount('appointments')->having('appointments_count', '>=', $minCount);
        }
        if ($request->filled('visit_count_max')) {
            $maxCount = (int)$request->visit_count_max;
            $query->withCount('appointments')->having('appointments_count', '<=', $maxCount);
        }

        // ===== ADMIN/DOCUMENTATION FILTERS =====
        if ($request->filled('has_id_document')) {
            if ($request->has_id_document === 'true' || $request->has_id_document === '1') {
                $query->whereNotNull('patient_id_document_path');
            } else {
                $query->whereNull('patient_id_document_path');
            }
        }
        if ($request->filled('has_consent')) {
            if ($request->has_consent === 'true' || $request->has_consent === '1') {
                $query->where('consent_share_with_gp', true);
            } else {
                $query->where(function($q) {
                    $q->where('consent_share_with_gp', false)->orWhereNull('consent_share_with_gp');
                });
            }
        }
        if ($request->filled('has_gp_details')) {
            if ($request->has_gp_details === 'true' || $request->has_gp_details === '1') {
                $query->whereNotNull('gp_name')->whereNotNull('gp_email');
            } else {
                $query->where(function($q) {
                    $q->whereNull('gp_name')->orWhereNull('gp_email');
                });
            }
        }
        if ($request->filled('missing_phone')) {
            $query->where(function($q) {
                $q->whereNull('phone')->orWhere('phone', '');
            });
        }
        if ($request->filled('missing_email')) {
            $query->where(function($q) {
                $q->whereNull('email')->orWhere('email', '');
            });
        }
        if ($request->filled('missing_address')) {
            $query->where(function($q) {
                $q->whereNull('address')->orWhere('address', '');
            });
        }

        // ===== COMMUNICATION & PORTAL FILTERS =====
        if ($request->filled('email_verified')) {
            if ($request->email_verified === 'true' || $request->email_verified === '1') {
                $query->whereNotNull('email_verified_at');
            } else {
                $query->whereNull('email_verified_at');
            }
        }

        // Prepare data for view
        $doctors = Doctor::with('user')->get()->map(function($doctor) {
            return [
                'id' => $doctor->id,
                'name' => $doctor->user ? $doctor->user->name : 'Unknown'
            ];
        });
        $departments = \App\Models\Department::where('is_active', true)->orderBy('name')->get();
        
        $patients = $query->with(['departments', 'department', 'appointments', 'assignedDoctor', 'createdByDoctor', 'activeAlerts.creator'])
            ->latest()
            ->paginate(15)->appends($request->query());

        return view('staff.patients.index', compact('patients', 'doctors', 'departments'));
    }

    public function show(Patient $patient)
    {
        $user = Auth::user();
        
        // Check if patient is visible to this user using policy
        $this->authorize('view', $patient);
        
        // Load relationships with proper ordering
        $patient->load([
            'departments',
            'department',
            'appointments' => function($query) {
                $query->with('doctor')->latest('appointment_date')->latest('appointment_time');
            },
            'medicalRecords' => function($query) {
                $query->with('doctor')->latest('record_date')->latest('created_at');
            },
            'prescriptions' => function($query) {
                $query->with('doctor')->latest('prescription_date');
            },
            'labReports' => function($query) {
                $query->with('doctor')->latest('test_date');
            },
            'alerts' => function($query) {
                $query->with('creator')->latest();
            }
        ]);
        
        return view('staff.patients.show', compact('patient'));
    }

    public function create()
    {
        // Check authorization - only doctors and admins can create patients
        $user = Auth::user();
        if ($user->role !== 'doctor' && !$user->is_admin && $user->role !== 'admin') {
            abort(403, 'You do not have permission to create patients. Only doctors and admins can create patients.');
        }
        
        // Filter departments based on user role
        $departmentsQuery = \App\Models\Department::where('is_active', true);
        
        // Admin users see all departments
        if ($user->role === 'admin' || $user->is_admin) {
            $departments = $departmentsQuery->orderBy('name')->get();
        } else {
            // For doctors: get departments from doctor_department pivot or department_id
            if ($user->role === 'doctor') {
                $doctor = Doctor::where('user_id', $user->id)->with('departments')->first();
                $departmentIds = [];
                
                if ($doctor) {
                    if ($doctor->departments->isNotEmpty()) {
                        $departmentIds = $doctor->departments->pluck('id')->toArray();
                    } elseif ($doctor->department_id) {
                        $departmentIds = [$doctor->department_id];
                    }
                }
                
                if (!empty($departmentIds)) {
                    $departments = $departmentsQuery->whereIn('id', $departmentIds)->orderBy('name')->get();
                } else {
                    $departments = collect([]); // No departments assigned
                }
            } else {
                // For other staff users: get departments from user_department pivot or department_id
                $user->load('departments');
                $departmentIds = [];
                
                if ($user->departments->isNotEmpty()) {
                    $departmentIds = $user->departments->pluck('id')->toArray();
                } elseif ($user->department_id) {
                    $departmentIds = [$user->department_id];
                }
                
                if (!empty($departmentIds)) {
                    $departments = $departmentsQuery->whereIn('id', $departmentIds)->orderBy('name')->get();
                } else {
                    $departments = collect([]); // No departments assigned
                }
            }
        }
        
        return view('staff.patients.create', compact('departments'));
    }

    public function store(Request $request)
    {
        // Check authorization - only doctors and admins can create patients
        $user = Auth::user();
        if ($user->role !== 'doctor' && !$user->is_admin && $user->role !== 'admin') {
            abort(403, 'You do not have permission to create patients. Only doctors and admins can create patients.');
        }
        
        // Calculate age from DOB to determine if guardian ID is required
        $dateOfBirth = $request->date_of_birth ? Carbon::parse($request->date_of_birth) : null;
        $age = $dateOfBirth ? $dateOfBirth->age : null;
        $isUnder18 = $age !== null && $age < 18;
        
        // Build validation rules
        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:patients',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'patient_id' => 'nullable|string|max:255|unique:patients',
            'blood_group' => 'nullable|string|max:10',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'insurance_provider' => 'nullable|string|max:255',
            'insurance_number' => 'nullable|string|max:255',
            'allergies' => 'nullable|array',
            'allergies.*' => 'nullable|string|max:255',
            'medical_history' => 'nullable|string|max:2000',
            'is_active' => 'nullable|boolean',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'department_id' => 'nullable|exists:departments,id', // Backward compatibility
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'nullable|exists:departments,id',
            // ID Documents
            'patient_id_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'guardian_id_document' => $isUnder18 ? 'required|file|mimes:pdf,jpg,jpeg,png|max:5120' : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            // GP Consent and Details
            'consent_share_with_gp' => 'nullable|boolean',
            'gp_name' => 'nullable|required_if:consent_share_with_gp,1|string|max:255',
            'gp_email' => 'nullable|required_if:consent_share_with_gp,1|email|max:255',
            'gp_phone' => 'nullable|required_if:consent_share_with_gp,1|string|max:20',
            'gp_address' => 'nullable|required_if:consent_share_with_gp,1|string|max:1000',
        ];
        
        $request->validate($validationRules);

        // Handle file uploads - Patient ID Document
        $patientIdDocumentPath = null;
        if ($request->hasFile('patient_id_document')) {
            $file = $request->file('patient_id_document');
            $filename = 'patient_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $patientIdDocumentPath = $file->storeAs('patients/documents', $filename, 'private');
        }
        
        // Handle file uploads - Guardian ID Document
        $guardianIdDocumentPath = null;
        if ($request->hasFile('guardian_id_document')) {
            $file = $request->file('guardian_id_document');
            $filename = 'guardian_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $guardianIdDocumentPath = $file->storeAs('patients/documents', $filename, 'private');
        }
        
        // Handle GP consent and details
        $consentShareWithGp = $request->has('consent_share_with_gp') ? (bool)$request->consent_share_with_gp : false;
        $gpName = null;
        $gpEmail = null;
        $gpPhone = null;
        $gpAddress = null;
        
        if ($consentShareWithGp) {
            $gpName = $request->gp_name;
            $gpEmail = $request->gp_email;
            $gpPhone = $request->gp_phone;
            $gpAddress = $request->gp_address;
        }

        // Prepare data with auto-generated patient_id if not provided
        $patientId = $request->patient_id ?: Patient::generatePatientId();
        
        // Prepare data with auto-generated patient_id
        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'patient_id' => $patientId,
            'blood_group' => $request->blood_group,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'insurance_provider' => $request->insurance_provider,
            'insurance_number' => $request->insurance_number,
            'allergies' => $request->allergies ? array_filter($request->allergies) : null,
            'notes' => $request->medical_history,
            'emergency_contact' => $request->emergency_contact_name,
            'emergency_phone' => $request->emergency_contact_phone,
            'is_active' => $request->has('is_active') ? true : false,
            // ID Documents
            'patient_id_document_path' => $patientIdDocumentPath,
            'guardian_id_document_path' => $guardianIdDocumentPath,
            // GP Consent and Details
            'consent_share_with_gp' => $consentShareWithGp,
            'gp_name' => $gpName,
            'gp_email' => $gpEmail,
            'gp_phone' => $gpPhone,
            'gp_address' => $gpAddress,
        ];
        
        // Handle department assignment (support both single and multiple departments)
        $departmentIds = [];
        if ($request->has('department_id') && $request->department_id) {
            // Single department (backward compatibility)
            $data['department_id'] = (int)$request->department_id;
            $departmentIds[] = (int)$request->department_id;
        }
        
        // Support multiple departments (new implementation)
        if ($request->has('department_ids') && is_array($request->department_ids)) {
            $departmentIds = array_unique(array_filter(array_map('intval', $request->department_ids)));
            // If multiple departments provided, set first as primary for backward compatibility
            if (!empty($departmentIds) && !isset($data['department_id'])) {
                $data['department_id'] = $departmentIds[0];
            }
        }
        
        // Track which doctor created this patient and set department (only if not already set)
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->with('departments')->first();
            if ($doctor) {
                $data['created_by_doctor_id'] = $doctor->id;
                // Only set departments if not already set
                if (empty($departmentIds)) {
                    // Auto-assign doctor's departments
                    if ($doctor->departments->isNotEmpty()) {
                        $departmentIds = $doctor->departments->pluck('id')->toArray();
                    } elseif ($doctor->department_id) {
                        $departmentIds = [$doctor->department_id];
                    }
                    if (!empty($departmentIds)) {
                        $data['department_id'] = $departmentIds[0]; // First as primary
                    }
                } else {
                    // Use provided departments, ensure department_id is set
                    if (!isset($data['department_id'])) {
                        $data['department_id'] = $departmentIds[0];
                    }
                }
            }
        } elseif ($user->department_id && empty($departmentIds)) {
            // For other roles, set department from user's department only if not already set
            $departmentIds[] = $user->department_id;
            $data['department_id'] = $user->department_id;
        }

        $patient = Patient::create($data);
        
        // Sync departments to many-to-many relationship
        if (!empty($departmentIds)) {
            $syncData = [];
            foreach ($departmentIds as $index => $deptId) {
                $syncData[$deptId] = ['is_primary' => $index === 0]; // First department is primary
            }
            $patient->departments()->sync($syncData);
        }

        return redirect()->route('staff.patients.index')
            ->with('success', 'Patient created successfully with ID: ' . $patient->patient_id);
    }

    public function edit(Patient $patient)
    {
        $user = Auth::user();
        
        // Check if patient is visible to this user using policy
        $this->authorize('view', $patient);
        
        // Check authorization - only doctors and admins can edit patients
        if ($user->role !== 'doctor' && !$user->is_admin && $user->role !== 'admin') {
            abort(403, 'You do not have permission to edit patients. Only doctors and admins can edit patients.');
        }
        
        // Filter departments based on user role
        $departmentsQuery = \App\Models\Department::where('is_active', true);
        
        // Admin users see all departments
        if ($user->role === 'admin' || $user->is_admin) {
            $departments = $departmentsQuery->orderBy('name')->get();
        } else {
            // For doctors: get departments from doctor_department pivot or department_id
            if ($user->role === 'doctor') {
                $doctor = Doctor::where('user_id', $user->id)->with('departments')->first();
                $departmentIds = [];
                
                if ($doctor) {
                    if ($doctor->departments->isNotEmpty()) {
                        $departmentIds = $doctor->departments->pluck('id')->toArray();
                    } elseif ($doctor->department_id) {
                        $departmentIds = [$doctor->department_id];
                    }
                }
                
                if (!empty($departmentIds)) {
                    $departments = $departmentsQuery->whereIn('id', $departmentIds)->orderBy('name')->get();
                } else {
                    $departments = collect([]); // No departments assigned
                }
            } else {
                // For other staff users: get departments from user_department pivot or department_id
                $user->load('departments');
                $departmentIds = [];
                
                if ($user->departments->isNotEmpty()) {
                    $departmentIds = $user->departments->pluck('id')->toArray();
                } elseif ($user->department_id) {
                    $departmentIds = [$user->department_id];
                }
                
                if (!empty($departmentIds)) {
                    $departments = $departmentsQuery->whereIn('id', $departmentIds)->orderBy('name')->get();
                } else {
                    $departments = collect([]); // No departments assigned
                }
            }
        }
        
        return view('staff.patients.edit', compact('patient', 'departments'));
    }

    public function update(Request $request, Patient $patient)
    {
        $user = Auth::user();
        
        // Check if patient is visible to this user using policy
        $this->authorize('view', $patient);
        
        // Check authorization - only doctors and admins can edit patients
        if ($user->role !== 'doctor' && !$user->is_admin && $user->role !== 'admin') {
            abort(403, 'You do not have permission to edit patients. Only doctors and admins can edit patients.');
        }
        
        // Calculate age from DOB to determine if guardian ID is required
        $dateOfBirth = $request->date_of_birth ? Carbon::parse($request->date_of_birth) : null;
        $age = $dateOfBirth ? $dateOfBirth->age : null;
        $isUnder18 = $age !== null && $age < 18;
        
        // Build validation rules
        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:patients,email,' . $patient->id,
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|in:male,female,other',
            'address' => 'required|string',
            'emergency_contact_name' => 'required|string|max:255',
            'emergency_contact_phone' => 'required|string|max:20',
            'department_id' => 'nullable|exists:departments,id', // Backward compatibility
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'nullable|exists:departments,id',
            // ID Documents (only validate if new file is provided)
            'patient_id_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
            'guardian_id_document' => $isUnder18 && !$patient->guardian_id_document_path ? 'required|file|mimes:pdf,jpg,jpeg,png|max:5120' : 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            // GP Consent and Details
            'consent_share_with_gp' => 'nullable|boolean',
            'gp_name' => 'nullable|required_if:consent_share_with_gp,1|string|max:255',
            'gp_email' => 'nullable|required_if:consent_share_with_gp,1|email|max:255',
            'gp_phone' => 'nullable|required_if:consent_share_with_gp,1|string|max:20',
            'gp_address' => 'nullable|required_if:consent_share_with_gp,1|string|max:1000',
        ];
        
        $request->validate($validationRules);

        // Handle file uploads - Patient ID Document (only if new file is provided)
        $patientIdDocumentPath = $patient->patient_id_document_path;
        if ($request->hasFile('patient_id_document')) {
            // Delete old file if exists
            if ($patient->patient_id_document_path && Storage::disk('private')->exists($patient->patient_id_document_path)) {
                Storage::disk('private')->delete($patient->patient_id_document_path);
            }
            
            $file = $request->file('patient_id_document');
            $filename = 'patient_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $patientIdDocumentPath = $file->storeAs('patients/documents', $filename, 'private');
        }
        
        // Handle file uploads - Guardian ID Document (only if new file is provided)
        $guardianIdDocumentPath = $patient->guardian_id_document_path;
        if ($request->hasFile('guardian_id_document')) {
            // Delete old file if exists
            if ($patient->guardian_id_document_path && Storage::disk('private')->exists($patient->guardian_id_document_path)) {
                Storage::disk('private')->delete($patient->guardian_id_document_path);
            }
            
            $file = $request->file('guardian_id_document');
            $filename = 'guardian_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $guardianIdDocumentPath = $file->storeAs('patients/documents', $filename, 'private');
        }
        
        // Handle GP consent and details
        $consentShareWithGp = $request->has('consent_share_with_gp') ? (bool)$request->consent_share_with_gp : false;
        $gpName = null;
        $gpEmail = null;
        $gpPhone = null;
        $gpAddress = null;
        
        if ($consentShareWithGp) {
            $gpName = $request->gp_name;
            $gpEmail = $request->gp_email;
            $gpPhone = $request->gp_phone;
            $gpAddress = $request->gp_address;
        }

        // Prepare data with mapped emergency contact fields
        $data = [
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'patient_id' => $request->patient_id ?: $patient->patient_id,
            'blood_group' => $request->blood_group,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'insurance_provider' => $request->insurance_provider,
            'insurance_number' => $request->insurance_number,
            'allergies' => $request->allergies ? array_filter($request->allergies) : null,
            'notes' => $request->medical_history,
            'emergency_contact' => $request->emergency_contact_name,
            'emergency_phone' => $request->emergency_contact_phone,
            'is_active' => $request->has('is_active') ? true : false,
            // ID Documents
            'patient_id_document_path' => $patientIdDocumentPath,
            'guardian_id_document_path' => $guardianIdDocumentPath,
            // GP Consent and Details
            'consent_share_with_gp' => $consentShareWithGp,
            'gp_name' => $gpName,
            'gp_email' => $gpEmail,
            'gp_phone' => $gpPhone,
            'gp_address' => $gpAddress,
        ];
        
        // Handle department assignment (support both single and multiple departments)
        $departmentIds = [];
        if ($request->has('department_id') && $request->department_id) {
            // Single department (backward compatibility)
            $data['department_id'] = (int)$request->department_id;
            $departmentIds[] = (int)$request->department_id;
        }
        
        // Support multiple departments (new implementation)
        if ($request->has('department_ids') && is_array($request->department_ids)) {
            $departmentIds = array_unique(array_filter(array_map('intval', $request->department_ids)));
            // If multiple departments provided, set first as primary for backward compatibility
            if (!empty($departmentIds) && !isset($data['department_id'])) {
                $data['department_id'] = $departmentIds[0];
            } elseif (empty($departmentIds)) {
                $data['department_id'] = null;
            }
        }

        $patient->update($data);
        
        // Sync departments to many-to-many relationship
        if (!empty($departmentIds)) {
            $syncData = [];
            foreach ($departmentIds as $index => $deptId) {
                $syncData[$deptId] = ['is_primary' => $index === 0]; // First department is primary
            }
            $patient->departments()->sync($syncData);
        } elseif ($request->has('department_id') && !$request->department_id && !$request->has('department_ids')) {
            // If department_id is explicitly set to empty/null and no department_ids, remove all departments
            $patient->departments()->detach();
        }

        return redirect()->route('staff.patients.index')
            ->with('success', 'Patient updated successfully.');
    }

    // Note: Staff cannot delete patients - only view, create, and edit
}
