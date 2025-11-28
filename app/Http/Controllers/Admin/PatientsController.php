<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\User;
use App\Models\Doctor;
use App\Models\Department;
use App\Services\HospitalEmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\QueryException;
use Carbon\Carbon;

class PatientsController extends Controller
{
    /**
     * Get the current user's department ID for any role
     */
    private function getUserDepartmentId()
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }
        
        // For doctors, get department from doctors table
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            return $doctor ? $doctor->department_id : null;
        }
        
        // For other roles (admin, nurse, staff, etc.), get from users table
        return $user->department_id;
    }

    /**
     * Display a listing of patients.
     */
    public function index(Request $request)
    {
        $query = Patient::query();
        
        // Filter by department for all roles (except super admins without department)
        $departmentId = $this->getUserDepartmentId();
        if ($departmentId) {
            $query->byDepartment($departmentId);
        }

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
        // First Name
        if ($request->filled('first_name')) {
            $query->where('first_name', 'like', "%{$request->first_name}%");
        }

        // Last Name
        if ($request->filled('last_name')) {
            $query->where('last_name', 'like', "%{$request->last_name}%");
        }

        // Date of Birth (exact or range)
        if ($request->filled('dob_from')) {
            $dobFrom = Carbon::parse($request->dob_from)->format('Y-m-d');
            $query->whereDate('date_of_birth', '>=', $dobFrom);
        }
        if ($request->filled('dob_to')) {
            $dobTo = Carbon::parse($request->dob_to)->format('Y-m-d');
            $query->whereDate('date_of_birth', '<=', $dobTo);
        }

        // Age Range (calculated from DOB)
        if ($request->filled('age_min')) {
            $maxDOB = now()->subYears($request->age_min)->format('Y-m-d');
            $query->whereDate('date_of_birth', '<=', $maxDOB);
        }
        if ($request->filled('age_max')) {
            $minDOB = now()->subYears($request->age_max + 1)->addDay()->format('Y-m-d');
            $query->whereDate('date_of_birth', '>=', $minDOB);
        }

        // Gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // City / Country / Postal Code
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
        // Patient Status
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'archived') {
                // If you have soft deletes, use: $query->onlyTrashed();
                // For now, we'll treat archived as inactive
                $query->where('is_active', false);
            }
        }

        // Registration Date Range
        if ($request->filled('reg_from')) {
            $regFrom = parseDateInput($request->reg_from) . ' 00:00:00';
            $query->where('created_at', '>=', $regFrom);
        }
        if ($request->filled('reg_to')) {
            $regTo = parseDateInput($request->reg_to) . ' 23:59:59';
            $query->where('created_at', '<=', $regTo);
        }

        // Patient Type (Insurance Provider)
        if ($request->filled('patient_type')) {
            if ($request->patient_type === 'insurance') {
                $query->whereNotNull('insurance_provider');
            } elseif ($request->patient_type === 'private') {
                $query->whereNull('insurance_provider');
            }
        }

        // Insurance Provider
        if ($request->filled('insurance_provider')) {
            $query->where('insurance_provider', 'like', "%{$request->insurance_provider}%");
        }

        // Assigned Doctor
        if ($request->filled('assigned_doctor_id')) {
            $query->where('assigned_doctor_id', $request->assigned_doctor_id);
        }

        // Department/Clinic
        if ($request->filled('department_id')) {
            $query->byDepartment($request->department_id);
        }

        // ===== ALERTS FILTERS =====
        // Has Alerts
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

        // Alert Severity
        if ($request->filled('alert_severity')) {
            $query->whereHas('alerts', function($q) use ($request) {
                $q->where('active', true)->where('severity', $request->alert_severity);
            });
        }

        // Alert Type
        if ($request->filled('alert_type')) {
            $query->whereHas('alerts', function($q) use ($request) {
                $q->where('active', true)->where('type', $request->alert_type);
            });
        }

        // ===== APPOINTMENTS/ENCOUNTERS FILTERS =====
        // Last Appointment Date
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

        // Next Appointment Date
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

        // Appointment Type
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
                // Assuming phone appointments are stored differently or use is_online=false with a type
                $query->whereHas('appointments', function($q) {
                    $q->where('type', 'phone');
                });
            }
        }

        // Visit Frequency - Visits in last X months
        if ($request->filled('visits_in_last')) {
            $months = (int)$request->visits_in_last;
            $cutoffDate = now()->subMonths($months);
            $query->whereHas('appointments', function($q) use ($cutoffDate) {
                $q->where('appointment_date', '>=', $cutoffDate);
            });
        }

        // Visit Count Range
        if ($request->filled('visit_count_min')) {
            $minCount = (int)$request->visit_count_min;
            $query->withCount('appointments')->having('appointments_count', '>=', $minCount);
        }
        if ($request->filled('visit_count_max')) {
            $maxCount = (int)$request->visit_count_max;
            $query->withCount('appointments')->having('appointments_count', '<=', $maxCount);
        }

        // ===== ADMIN/DOCUMENTATION FILTERS =====
        // Has ID Document
        if ($request->filled('has_id_document')) {
            if ($request->has_id_document === 'true' || $request->has_id_document === '1') {
                $query->whereNotNull('patient_id_document_path');
            } else {
                $query->whereNull('patient_id_document_path');
            }
        }

        // Has Consent Recorded
        if ($request->filled('has_consent')) {
            if ($request->has_consent === 'true' || $request->has_consent === '1') {
                $query->where('consent_share_with_gp', true);
            } else {
                $query->where(function($q) {
                    $q->where('consent_share_with_gp', false)->orWhereNull('consent_share_with_gp');
                });
            }
        }

        // Has GP Details
        if ($request->filled('has_gp_details')) {
            if ($request->has_gp_details === 'true' || $request->has_gp_details === '1') {
                $query->whereNotNull('gp_name')->whereNotNull('gp_email');
            } else {
                $query->where(function($q) {
                    $q->whereNull('gp_name')->orWhereNull('gp_email');
                });
            }
        }

        // Missing Data Filters
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
        // Email Verified
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
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        $patients = $query->with(['departments', 'department', 'assignedDoctor', 'createdByDoctor', 'activeAlerts.creator'])
            ->orderBy('created_at', 'desc')
            ->paginate(20)->appends($request->query());
            
        return view('admin.patients.index', compact('patients', 'doctors', 'departments'));
    }

    /**
     * Show the form for creating a new patient.
     */
    public function create()
    {
        $departments = \App\Models\Department::orderBy('name')->get();
        return view('admin.patients.create', compact('departments'));
    }

    /**
     * Store a newly created patient.
     */
    public function store(Request $request, HospitalEmailNotificationService $emailService)
    {
        // Calculate age from DOB to determine if guardian ID is required
        $dateOfBirth = $request->date_of_birth ? Carbon::parse($request->date_of_birth) : null;
        $age = $dateOfBirth ? $dateOfBirth->age : null;
        $isUnder18 = $age !== null && $age < 18;
        
        // Build validation rules
        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:patients',
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|max:10',
            'patient_id' => 'required|string|max:255|unique:patients',
            'blood_group' => 'nullable|string|max:10',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
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
        
        // Prepare data for database insertion
        $patientData = [
            'patient_id' => $request->patient_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'blood_group' => $request->blood_group,
            'emergency_contact' => $request->emergency_contact_name,
            'emergency_phone' => $request->emergency_contact_phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'insurance_provider' => $request->insurance_provider,
            'insurance_number' => $request->insurance_number,
            'allergies' => $request->allergies ? array_filter($request->allergies) : null,
            'notes' => $request->medical_history,
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
            $patientData['department_id'] = (int)$request->department_id;
            $departmentIds[] = (int)$request->department_id;
        }
        
        // Support multiple departments (new implementation)
        if ($request->has('department_ids') && is_array($request->department_ids)) {
            $departmentIds = array_unique(array_filter(array_map('intval', $request->department_ids)));
            // If multiple departments provided, set first as primary for backward compatibility
            if (!empty($departmentIds) && !isset($patientData['department_id'])) {
                $patientData['department_id'] = $departmentIds[0];
            }
        }
        
        // Track which doctor created this patient and set department (only if not already set)
        $user = Auth::user();
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $patientData['created_by_doctor_id'] = $doctor->id;
                // Only set department if not already set
                if (empty($departmentIds)) {
                    $primaryDept = $doctor->primaryDepartment();
                    if ($primaryDept) {
                        $departmentIds[] = $primaryDept->id;
                        $patientData['department_id'] = $primaryDept->id;
                    } elseif ($doctor->department_id) {
                        $departmentIds[] = $doctor->department_id;
                        $patientData['department_id'] = $doctor->department_id;
                    }
                }
            }
        } elseif ($user->department_id && empty($departmentIds)) {
            // For other roles, set department from user's department only if not already set
            $departmentIds[] = $user->department_id;
            $patientData['department_id'] = $user->department_id;
        }

        $patient = Patient::create($patientData);
        
        // Sync departments to many-to-many relationship
        if (!empty($departmentIds)) {
            $syncData = [];
            foreach ($departmentIds as $index => $deptId) {
                $syncData[$deptId] = ['is_primary' => $index === 0]; // First department is primary
            }
            $patient->departments()->sync($syncData);
        }
        
        // Send welcome email to patient if enabled
        if (config('hospital.notifications.patient_welcome.enabled', true)) {
            try {
                $emailService->sendPatientWelcomeEmail($patient);
            } catch (\Exception $e) {
                \Log::error('Failed to send patient welcome email', [
                    'patient_id' => $patient->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Notify staff if enabled
        if (config('hospital.staff_notifications.new_patient_registration.enabled', true)) {
            try {
                $staffRoles = config('hospital.staff_notifications.new_patient_registration.roles', ['admin', 'receptionist']);
                $staffMembers = User::whereIn('role', $staffRoles)
                    ->where('is_active', true)
                    ->whereNotNull('email')
                    ->get();
                    
                foreach ($staffMembers as $staff) {
                    $emailService->notifyStaffNewPatientRegistration($patient, $staff);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send staff notification for new patient', [
                    'patient_id' => $patient->id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->route('admin.patients.index')
                         ->with('success', 'Patient created successfully! Welcome email has been sent.');
    }

    /**
     * Display the specified patient.
     */
    /**
     * View patient ID document or guardian ID document (view-only, no download).
     */
    public function downloadDocument(Patient $patient, $type)
    {
        // Check if user has permission to view this patient
        if (!Auth::user()->is_admin && !$patient->isVisibleTo(Auth::user())) {
            abort(403, 'You do not have permission to access this document.');
        }

        $documentPath = null;

        if ($type === 'patient_id') {
            $documentPath = $patient->patient_id_document_path;
        } elseif ($type === 'guardian_id') {
            $documentPath = $patient->guardian_id_document_path;
        } else {
            abort(404, 'Invalid document type.');
        }

        if (!$documentPath || !Storage::disk('private')->exists($documentPath)) {
            abort(404, 'Document not found.');
        }

        // Serve file inline (view-only) instead of downloading
        $file = Storage::disk('private')->get($documentPath);
        $mimeType = Storage::disk('private')->mimeType($documentPath);
        
        return response($file, 200)
            ->header('Content-Type', $mimeType)
            ->header('Content-Disposition', 'inline; filename="' . basename($documentPath) . '"')
            ->header('X-Content-Type-Options', 'nosniff');
    }

    public function show(Patient $patient)
    {
        // Check if user can access this patient using policy
        // Admins can see all patients, others see based on visibility rules
        $this->authorize('view', $patient);
        
        // Load relationships for display
        $patient->load([
            'assignedDoctor' => function($query) {
                $query->with('departments', 'department');
            },
            'departments', // Many-to-many relationship
            'department', // Legacy relationship for backward compatibility
            'createdByDoctor',
            'alerts' => function($query) {
                $query->with('creator')->latest();
            }
        ]);
        
        return view('admin.patients.show', compact('patient'));
    }

    /**
     * Show the form for editing the specified patient.
     */
    public function edit(Patient $patient)
    {
        $departments = \App\Models\Department::orderBy('name')->get();
        return view('admin.patients.edit', compact('patient', 'departments'));
    }

    /**
     * Update the specified patient.
     */
    public function update(Request $request, Patient $patient)
    {
        // Calculate age from DOB to determine if guardian ID is required
        $dateOfBirth = $request->date_of_birth ? Carbon::parse($request->date_of_birth) : null;
        $age = $dateOfBirth ? $dateOfBirth->age : null;
        $isUnder18 = $age !== null && $age < 18;
        
        // Build validation rules
        $validationRules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:patients,email,' . $patient->id,
            'phone' => 'required|string|max:20',
            'date_of_birth' => 'required|date',
            'gender' => 'required|string|max:10',
            'patient_id' => 'required|string|max:255|unique:patients,patient_id,' . $patient->id,
            'blood_group' => 'nullable|string|max:10',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
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
        
        // Prepare data for database update
        $patientData = [
            'patient_id' => $request->patient_id,
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
            'blood_group' => $request->blood_group,
            'emergency_contact' => $request->emergency_contact_name,
            'emergency_phone' => $request->emergency_contact_phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'postal_code' => $request->postal_code,
            'insurance_provider' => $request->insurance_provider,
            'insurance_number' => $request->insurance_number,
            'allergies' => $request->allergies ? array_filter($request->allergies) : null,
            'notes' => $request->medical_history,
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
        $hasDepartmentIds = $request->has('department_ids') && is_array($request->department_ids);
        $hasDepartmentId = $request->has('department_id') && $request->department_id;
        
        // Priority: department_ids (multiple) takes precedence over department_id (single)
        if ($hasDepartmentIds) {
            // Multiple departments (new implementation)
            $departmentIds = array_unique(array_filter(array_map('intval', $request->department_ids)));
            // Set first as primary for backward compatibility
            if (!empty($departmentIds)) {
                $patientData['department_id'] = $departmentIds[0];
            } else {
                $patientData['department_id'] = null;
            }
        } elseif ($hasDepartmentId) {
            // Single department (backward compatibility)
            $patientData['department_id'] = (int)$request->department_id;
            $departmentIds[] = (int)$request->department_id;
        }

        $patient->update($patientData);
        
        // Always sync departments relationship when department fields are present in request
        // This ensures changes are properly applied
        if ($hasDepartmentIds || $hasDepartmentId) {
            if (!empty($departmentIds)) {
                $syncData = [];
                foreach ($departmentIds as $index => $deptId) {
                    $syncData[$deptId] = ['is_primary' => $index === 0]; // First department is primary
                }
                $patient->departments()->sync($syncData);
            } else {
                // If empty array or null, detach all departments
                $patient->departments()->detach();
            }
            // Refresh the model to ensure relationships are up to date
            $patient->refresh();
            // Clear any cached relationships
            $patient->unsetRelation('departments');
        }

        return redirect()->route('admin.patients.index')
                         ->with('success', 'Patient updated successfully.');
    }

    /**
     * Remove the specified patient.
     */
    public function destroy(Patient $patient)
    {
        // Check authorization - only admins can delete patients
        $this->authorize('delete', $patient);
        
        try {
            // Store patient name and ID before deletion
            $patientName = $patient->full_name;
            $patientId = $patient->id;
            
            \Log::info('Attempting to delete patient', [
                'patient_id' => $patientId,
                'patient_name' => $patientName,
                'deleted_by' => Auth::id()
            ]);
            
            // Use database transaction to ensure atomicity
            DB::beginTransaction();
            
            try {
                // Delete related records first (in proper order to avoid foreign key violations)
                // We delete explicitly to handle all cases, even if cascade is set
                
                // Delete appointments first (may have foreign keys to other tables)
                $appointmentIds = DB::table('appointments')->where('patient_id', $patientId)->pluck('id');
                if ($appointmentIds->isNotEmpty()) {
                    // Delete any records that reference appointments
                    DB::table('medical_records')->whereIn('appointment_id', $appointmentIds)->update(['appointment_id' => null]);
                    DB::table('billings')->whereIn('appointment_id', $appointmentIds)->update(['appointment_id' => null]);
                    DB::table('invoices')->whereIn('appointment_id', $appointmentIds)->update(['appointment_id' => null]);
                    // Now delete appointments
                    DB::table('appointments')->where('patient_id', $patientId)->delete();
                }
                
                // Delete medical records
                DB::table('medical_records')->where('patient_id', $patientId)->delete();
                
                // Delete prescriptions
                DB::table('prescriptions')->where('patient_id', $patientId)->delete();
                
                // Delete lab reports
                DB::table('lab_reports')->where('patient_id', $patientId)->delete();
                
                // Delete patient notifications
                DB::table('patient_notifications')->where('patient_id', $patientId)->delete();
                
                // Delete patient alerts
                DB::table('patient_alerts')->where('patient_id', $patientId)->delete();
                
                // Delete patient documents and their deliveries
                $documentIds = DB::table('patient_documents')->where('patient_id', $patientId)->pluck('id');
                if ($documentIds->isNotEmpty()) {
                    DB::table('document_deliveries')->whereIn('patient_document_id', $documentIds)->delete();
                    DB::table('patient_documents')->where('patient_id', $patientId)->delete();
                }
                
                // Delete email logs
                DB::table('email_logs')->where('patient_id', $patientId)->delete();
                
                // Delete email bounces
                DB::table('email_bounces')->where('patient_id', $patientId)->delete();
                
                // Delete patient email consent
                DB::table('patient_email_consent')->where('patient_id', $patientId)->delete();
                
                // Delete department-patient pivot records
                DB::table('department_patient')->where('patient_id', $patientId)->delete();
                
                // Delete invoices (which may have payments)
                $invoiceIds = DB::table('invoices')->where('patient_id', $patientId)->pluck('id');
                if ($invoiceIds->isNotEmpty()) {
                    // Delete payments associated with invoices
                    DB::table('payments')->whereIn('invoice_id', $invoiceIds)->delete();
                    // Delete invoice items
                    DB::table('invoice_items')->whereIn('invoice_id', $invoiceIds)->delete();
                    // Delete invoices
                    DB::table('invoices')->where('patient_id', $patientId)->delete();
                }
                
                // Delete billings
                DB::table('billings')->where('patient_id', $patientId)->delete();
                
                // Refresh patient model to ensure we have latest state
                $patient->refresh();
                
                // Now delete the patient
                $deleted = $patient->delete();
                
                if (!$deleted) {
                    throw new \Exception('Patient deletion returned false - patient may have been already deleted or model deletion failed');
                }
                
                // Verify deletion
                $stillExists = Patient::find($patientId);
                if ($stillExists) {
                    throw new \Exception('Patient still exists after deletion attempt - database constraint may have prevented deletion');
                }
                
                DB::commit();
                
                $message = "Patient '{$patientName}' has been successfully deleted.";
                
                \Log::info('Patient deleted successfully', [
                    'patient_id' => $patientId,
                    'patient_name' => $patientName,
                    'deleted_by' => Auth::id()
                ]);
                
                if (request()->wantsJson() || request()->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message
                    ]);
                }
                
                return redirect()->route('admin.patients.index')
                                 ->with('success', $message);
                                 
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error('Patient deletion failed in transaction', [
                    'patient_id' => $patientId,
                    'patient_name' => $patientName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                throw $e; // Re-throw to be caught by outer catch block
            }
                             
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database constraint violations
            $errorCode = $e->getCode();
            $errorMessage = $e->getMessage();
            
            // Check if it's a foreign key constraint violation
            if (str_contains($errorMessage, 'foreign key constraint') || str_contains($errorMessage, '1451') || str_contains($errorMessage, 'Cannot delete')) {
                $message = "Cannot delete patient {$patient->full_name}. There are still related records in the database that prevent deletion. Please check for billing records, invoices, or other related data.";
            } else {
                $message = 'Failed to delete patient: ' . $errorMessage;
            }
            
            \Log::error('Patient deletion failed - Database constraint', [
                'patient_id' => $patient->id,
                'patient_name' => $patient->full_name,
                'error_code' => $errorCode,
                'error' => $errorMessage,
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'error_type' => 'database_constraint'
                ], 422);
            }
            
            return redirect()->route('admin.patients.index')
                             ->with('error', $message);
        } catch (\Exception $e) {
            $message = 'Failed to delete patient: ' . $e->getMessage();
            
            \Log::error('Patient deletion failed', [
                'patient_id' => $patient->id,
                'patient_name' => $patient->full_name,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $message
                ], 500);
            }
            
            return redirect()->route('admin.patients.index')
                             ->with('error', $message);
        }
    }

    /**
     * Export patients to CSV
     */
    public function exportCsv(Request $request)
    {
        try {
            $query = Patient::query();
            
            // Apply same filters as index method
            $departmentId = $this->getUserDepartmentId();
            if ($departmentId) {
                $query->byDepartment($departmentId);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%")
                      ->orWhere('patient_id', 'like', "%{$search}%");
                });
            }

            if ($request->filled('gender')) {
                $query->where('gender', $request->gender);
            }

            if ($request->filled('age_range')) {
                $now = now();
                switch ($request->age_range) {
                    case 'child':
                        $query->whereDate('date_of_birth', '>=', $now->copy()->subYears(17)->format('Y-m-d'));
                        break;
                    case 'adult':
                        $query->whereDate('date_of_birth', '>=', $now->copy()->subYears(64)->format('Y-m-d'))
                              ->whereDate('date_of_birth', '<=', $now->copy()->subYears(18)->format('Y-m-d'));
                        break;
                    case 'senior':
                        $query->whereDate('date_of_birth', '<=', $now->copy()->subYears(65)->format('Y-m-d'));
                        break;
                }
            }

            if ($request->filled('date_from')) {
                $dateFrom = parseDateInput($request->date_from) . ' 00:00:00';
                $query->where('created_at', '>=', $dateFrom);
            }

            $patients = $query->with(['departments', 'department', 'createdByDoctor'])
                ->orderBy('created_at', 'desc')
                ->get();

            $filename = 'patients_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function() use ($patients) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // CSV Headers
                fputcsv($file, [
                    'Patient ID',
                    'First Name',
                    'Last Name',
                    'Full Name',
                    'Email',
                    'Phone',
                    'Date of Birth',
                    'Age',
                    'Gender',
                    'Blood Group',
                    'Address',
                    'City',
                    'State',
                    'Country',
                    'Postal Code',
                    'Emergency Contact Name',
                    'Emergency Contact Phone',
                    'Insurance Provider',
                    'Insurance Number',
                    'Allergies',
                    'Medical Conditions',
                    'Assigned Clinics',
                    'Created By Doctor',
                    'Status',
                    'Registration Date',
                    'Last Updated'
                ]);

                // CSV Data
                foreach ($patients as $patient) {
                    $departments = $patient->departments->pluck('name')->join(', ') ?: ($patient->department ? $patient->department->name : '');
                    $allergies = is_array($patient->allergies) ? implode(', ', $patient->allergies) : ($patient->allergies ?? '');
                    $conditions = is_array($patient->medical_conditions) ? implode(', ', $patient->medical_conditions) : ($patient->medical_conditions ?? '');
                    
                    $age = $patient->date_of_birth ? Carbon::parse($patient->date_of_birth)->age : '';
                    
                    fputcsv($file, [
                        $patient->patient_id,
                        $patient->first_name,
                        $patient->last_name,
                        $patient->full_name,
                        $patient->email ?? '',
                        $patient->phone ?? '',
                        $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '',
                        $age,
                        ucfirst($patient->gender ?? ''),
                        $patient->blood_group ?? '',
                        $patient->address ?? '',
                        $patient->city ?? '',
                        $patient->state ?? '',
                        $patient->country ?? '',
                        $patient->postal_code ?? '',
                        $patient->emergency_contact ?? '',
                        $patient->emergency_phone ?? '',
                        $patient->insurance_provider ?? '',
                        $patient->insurance_number ?? '',
                        $allergies,
                        $conditions,
                        $departments,
                        $patient->createdByDoctor ? $patient->createdByDoctor->full_name : '',
                        $patient->is_active ? 'Active' : 'Inactive',
                        $patient->created_at->format('Y-m-d H:i:s'),
                        $patient->updated_at->format('Y-m-d H:i:s')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            \Log::error('Patients CSV Export Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to export patients: ' . $e->getMessage());
        }
    }

    /**
     * Show CSV import form
     */
    public function showImport()
    {
        return view('admin.patients.import');
    }

    /**
     * Import patients from CSV
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'import_mode' => 'required|in:insert,update,upsert,skip',
            'skip_errors' => 'nullable|boolean'
        ]);

        try {
            $file = $request->file('csv_file');
            $importMode = $request->import_mode;
            $skipErrors = $request->boolean('skip_errors');
            
            $handle = fopen($file->getRealPath(), 'r');
            
            if ($handle === false) {
                throw new \Exception('Could not open CSV file');
            }

            // Skip BOM if present
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }

            // Read headers
            $headers = fgetcsv($handle);
            if ($headers === false) {
                throw new \Exception('CSV file is empty or invalid');
            }

            // Normalize headers (trim and lowercase)
            $headers = array_map(function($header) {
                return strtolower(trim($header));
            }, $headers);

            // Map headers to database columns
            $headerMap = [
                'patient id' => 'patient_id',
                'first name' => 'first_name',
                'last name' => 'last_name',
                'email' => 'email',
                'phone' => 'phone',
                'date of birth' => 'date_of_birth',
                'gender' => 'gender',
                'blood group' => 'blood_group',
                'address' => 'address',
                'city' => 'city',
                'state' => 'state',
                'country' => 'country',
                'postal code' => 'postal_code',
                'emergency contact name' => 'emergency_contact',
                'emergency contact phone' => 'emergency_phone',
                'insurance provider' => 'insurance_provider',
                'insurance number' => 'insurance_number',
                'allergies' => 'allergies',
                'medical conditions' => 'medical_conditions',
                'assigned clinics' => 'department_names',
                'status' => 'status',
            ];

            $stats = [
                'total' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => []
            ];

            DB::beginTransaction();

            $rowNumber = 1; // Start from 1 (header row)
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $stats['total']++;

                try {
                    $data = [];
                    foreach ($headers as $index => $header) {
                        $columnName = $headerMap[$header] ?? null;
                        if ($columnName && isset($row[$index])) {
                            $data[$columnName] = trim($row[$index]);
                        }
                    }

                    // Validate required fields
                    if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
                        throw new \Exception("Row {$rowNumber}: Missing required fields (first_name, last_name, email)");
                    }

                    // Handle patient_id
                    if (empty($data['patient_id'])) {
                        // Generate patient ID if not provided
                        $data['patient_id'] = 'PAT' . str_pad(Patient::max('id') + 1, 6, '0', STR_PAD_LEFT);
                    }

                    // Handle date_of_birth - enforce MM/DD/YYYY format
                    if (!empty($data['date_of_birth'])) {
                        try {
                            $data['date_of_birth'] = parseImportDate($data['date_of_birth']);
                        } catch (\Exception $e) {
                            throw new \Exception("Row {$rowNumber}: Date of Birth - " . $e->getMessage());
                        }
                    }

                    // Handle allergies
                    if (!empty($data['allergies'])) {
                        $allergies = array_filter(array_map('trim', explode(',', $data['allergies'])));
                        $data['allergies'] = !empty($allergies) ? $allergies : null;
                    } else {
                        $data['allergies'] = null;
                    }

                    // Handle medical_conditions
                    if (!empty($data['medical_conditions'])) {
                        $conditions = array_filter(array_map('trim', explode(',', $data['medical_conditions'])));
                        $data['medical_conditions'] = !empty($conditions) ? $conditions : null;
                    } else {
                        $data['medical_conditions'] = null;
                    }

                    // Handle status
                    $data['is_active'] = true;
                    if (isset($data['status'])) {
                        $data['is_active'] = strtolower($data['status']) === 'active';
                        unset($data['status']);
                    }

                    // Handle department assignment
                    $departmentIds = [];
                    if (!empty($data['department_names'])) {
                        $departmentNames = array_filter(array_map('trim', explode(',', $data['department_names'])));
                        foreach ($departmentNames as $deptName) {
                            $dept = Department::where('name', $deptName)->first();
                            if ($dept) {
                                $departmentIds[] = $dept->id;
                            }
                        }
                        unset($data['department_names']);
                    }

                    // Find existing patient
                    $existingPatient = Patient::where('email', $data['email'])
                        ->orWhere('patient_id', $data['patient_id'])
                        ->first();

                    // Handle import modes
                    if ($existingPatient) {
                        if ($importMode === 'insert' || $importMode === 'skip') {
                            $stats['skipped']++;
                            continue;
                        }
                        
                        if ($importMode === 'update' || $importMode === 'upsert') {
                            // Update existing
                            $existingPatient->update($data);
                            $patient = $existingPatient;
                            $stats['updated']++;
                        }
                    } else {
                        if ($importMode === 'update') {
                            $stats['skipped']++;
                            continue;
                        }
                        
                        // Create new
                        $patient = Patient::create($data);
                        $stats['created']++;
                    }

                    // Sync departments
                    if (!empty($departmentIds)) {
                        $syncData = [];
                        foreach ($departmentIds as $index => $deptId) {
                            $syncData[$deptId] = ['is_primary' => $index === 0];
                        }
                        $patient->departments()->sync($syncData);
                        $patient->department_id = $departmentIds[0]; // Set primary for backward compatibility
                        $patient->save();
                    }

                } catch (\Exception $e) {
                    $errorMsg = "Row {$rowNumber}: " . $e->getMessage();
                    $stats['errors'][] = $errorMsg;
                    
                    if (!$skipErrors) {
                        DB::rollBack();
                        fclose($handle);
                        return redirect()->back()
                            ->with('error', $errorMsg)
                            ->withInput();
                    }
                }
            }

            fclose($handle);
            DB::commit();

            $message = "Import completed! Created: {$stats['created']}, Updated: {$stats['updated']}, Skipped: {$stats['skipped']}";
            if (!empty($stats['errors'])) {
                $message .= ". Errors: " . count($stats['errors']);
            }

            return redirect()->route('admin.patients.index')
                ->with('success', $message)
                ->with('import_stats', $stats);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Patients CSV Import Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to import patients: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show the form for sending email to patient's GP.
     */
    public function showGpEmailForm(Patient $patient)
    {
        // Check if patient has GP consent and GP email
        if (!$patient->consent_share_with_gp) {
            return redirect()->route('admin.patients.show', $patient)
                             ->with('error', 'Patient has not consented to share information with their GP.');
        }

        if (!$patient->gp_email) {
            return redirect()->route('admin.patients.show', $patient)
                             ->with('error', 'GP email address is not available for this patient.');
        }

        return view('admin.patients.gp-email', compact('patient'));
    }

    /**
     * Send email to patient's GP.
     */
    public function sendGpEmail(Request $request, Patient $patient, HospitalEmailNotificationService $emailService)
    {
        // Validate request
        $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
            'email_type' => 'nullable|string|in:general,consultation,referral,update,other',
            'medical_record_ids' => 'nullable|array',
            'medical_record_ids.*' => 'exists:medical_records,id',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240|mimes:pdf,doc,docx,jpg,jpeg,png,gif,txt,xls,xlsx',
        ]);

        // Check if patient has GP consent and GP email
        if (!$patient->consent_share_with_gp) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Patient has not consented to share information with their GP.'
                ], 422);
            }
            return redirect()->back()
                             ->with('error', 'Patient has not consented to share information with their GP.')
                             ->withInput();
        }

        if (!$patient->gp_email) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'GP email address is not available for this patient.'
                ], 422);
            }
            return redirect()->back()
                             ->with('error', 'GP email address is not available for this patient.')
                             ->withInput();
        }

        try {
            $emailType = $request->email_type ?? 'general';
            $sentBy = Auth::user();

            // Process medical record attachments
            $medicalRecordIds = $request->medical_record_ids ?? [];
            $medicalRecordAttachments = [];
            if (!empty($medicalRecordIds)) {
                $medicalRecords = \App\Models\MedicalRecord::whereIn('id', $medicalRecordIds)
                    ->where('patient_id', $patient->id)
                    ->with('attachments')
                    ->get();
                
                foreach ($medicalRecords as $record) {
                    foreach ($record->attachments as $attachment) {
                        $medicalRecordAttachments[] = $attachment;
                    }
                }
            }

            // Process uploaded files
            $uploadedFiles = [];
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $uploadedFiles[] = $file;
                }
            }

            $emailLog = $emailService->sendGpEmail(
                $patient,
                $request->subject,
                $request->message,
                $emailType,
                $sentBy,
                $medicalRecordAttachments,
                $uploadedFiles
            );

            if ($emailLog) {
                $message = "Email has been successfully sent to {$patient->gp_name} at {$patient->gp_email}.";

                if ($request->wantsJson() || $request->ajax()) {
                    return response()->json([
                        'success' => true,
                        'message' => $message
                    ]);
                }

                return redirect()->route('admin.patients.show', $patient)
                                 ->with('success', $message);
            } else {
                throw new \Exception('Failed to send email. Please try again.');
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send GP email', [
                'patient_id' => $patient->id,
                'gp_email' => $patient->gp_email,
                'error' => $e->getMessage()
            ]);

            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage()
                ], 500);
            }

            return redirect()->back()
                             ->with('error', 'Failed to send email: ' . $e->getMessage())
                             ->withInput();
        }
    }
}

