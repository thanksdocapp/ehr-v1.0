<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\MedicalRecordAttachment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\Department;
use App\Services\HospitalEmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class MedicalRecordsController extends Controller
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
     * Display a listing of medical records.
     */
    public function index(Request $request): View
    {
        $query = MedicalRecord::with(['patient', 'doctor', 'appointment']);

        // Apply visibility rules based on user role (uses patient-department-doctor logic)
        // Admins will see all records, doctors/staff will see only their department records
        $user = Auth::user();
        if ($user) {
            $query->visibleTo($user);
        } else {
            $query->whereRaw('1 = 0'); // No results if no user
        }

        // Filter by patient
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        // Filter by doctor
        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        // Filter by record type
        if ($request->filled('record_type')) {
            $query->where('record_type', $request->record_type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $dateFrom = parseDateInput($request->date_from) . ' 00:00:00';
            $query->where('created_at', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = parseDateInput($request->date_to) . ' 23:59:59';
            $query->where('created_at', '<=', $dateTo);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('diagnosis', 'like', "%{$search}%")
                  ->orWhere('symptoms', 'like', "%{$search}%")
                  ->orWhere('treatment', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($patientQuery) use ($search) {
                      $patientQuery->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%")
                                  ->orWhere('patient_id', 'like', "%{$search}%");
                  });
            });
        }

        $records = $query->orderBy('created_at', 'desc')->paginate(15);

        // Filter patients and doctors by department
        $patientsQuery = Patient::query();
        if ($departmentId) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        
        $doctorsQuery = Doctor::query();
        if ($departmentId) {
            $doctorsQuery->where('department_id', $departmentId);
        }
        $doctors = $doctorsQuery->orderBy('first_name')->get();

        $stats = [
            'total' => MedicalRecord::count(),
            'this_month' => MedicalRecord::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
            'consultations' => MedicalRecord::where('record_type', 'consultation')->count(),
            'prescriptions' => MedicalRecord::where('record_type', 'prescription')->count(),
        ];

        return view('admin.medical-records.index', compact('records', 'patients', 'doctors', 'stats'));
    }

    /**
     * Show the form for creating a new medical record.
     */
    public function create(Request $request): View
    {
        // Filter patients and doctors by department
        $departmentId = $this->getUserDepartmentId();
        
        $patientsQuery = Patient::query();
        if ($departmentId) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        
        $doctorsQuery = Doctor::query();
        if ($departmentId) {
            $doctorsQuery->where('department_id', $departmentId);
        }
        $doctors = $doctorsQuery->orderBy('first_name')->get();
        $appointments = [];

        // If appointment_id is provided, get the appointment details
        $selectedAppointment = null;
        if ($request->filled('appointment_id')) {
            $selectedAppointment = Appointment::with(['patient', 'doctor'])->find($request->appointment_id);
            if ($selectedAppointment) {
                $appointments = collect([$selectedAppointment]);
            }
        }

        return view('admin.medical-records.create', compact('patients', 'doctors', 'appointments', 'selectedAppointment'));
    }

    /**
     * Store a newly created medical record.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'record_type' => 'required|in:consultation,diagnosis,prescription,lab_result,follow_up,discharge',
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
            'diagnosis' => 'nullable|string|max:500',
            'symptoms' => 'nullable|string',
            'treatment' => 'nullable|string',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after:today',
            'is_private' => 'boolean',
            'vital_signs.blood_pressure' => 'nullable|string|max:20',
            'vital_signs.temperature' => 'nullable|string|max:10',
            'vital_signs.pulse' => 'nullable|string|max:10',
            'vital_signs.respiratory_rate' => 'nullable|string|max:10',
            'vital_signs.oxygen_saturation' => 'nullable|string|max:10',
            'vital_signs.weight' => 'nullable|string|max:10',
            'vital_signs.height' => 'nullable|string|max:10',
        ]);

        $data = $request->all();
        
        // Handle vital signs
        $vitalSigns = [];
        if ($request->filled('vital_signs')) {
            foreach ($request->vital_signs as $key => $value) {
                if (!empty($value)) {
                    $vitalSigns[$key] = $value;
                }
            }
        }
        $data['vital_signs'] = empty($vitalSigns) ? null : $vitalSigns;
        $data['is_private'] = $request->boolean('is_private');

        $medicalRecord = MedicalRecord::create($data);
        
        // Load relationships for notifications
        $medicalRecord->load(['patient', 'doctor']);
        
        // Get patient name for audit log
        $patientName = $medicalRecord->patient ? ($medicalRecord->patient->first_name . ' ' . $medicalRecord->patient->last_name) : 'Unknown Patient';
        $user = auth()->user();
        
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
        
        // Send email notification to patient
        $this->handleNewMedicalRecordNotifications($medicalRecord, app(HospitalEmailNotificationService::class));

        return redirect()->route('admin.medical-records.index')
            ->with('success', 'Medical record created successfully! Patient has been notified.');
    }

    /**
     * Display the specified medical record.
     */
    public function show(MedicalRecord $medicalRecord): View
    {
        $user = Auth::user();
        
        // Check department-based access
        $departmentId = $this->getUserDepartmentId();
        if ($departmentId && $medicalRecord->doctor && $medicalRecord->doctor->department_id !== $departmentId) {
            abort(403, 'You do not have permission to view this medical record.');
        }
        
        $medicalRecord->load(['patient', 'doctor', 'appointment', 'prescriptions', 'labReports', 'attachments.uploader']);
        
        // Load audit activities for this medical record
        $auditActivities = \App\Models\UserActivity::where('model_type', MedicalRecord::class)
            ->where('model_id', $medicalRecord->id)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return view('admin.medical-records.show', compact('medicalRecord', 'auditActivities'));
    }

    /**
     * Show the form for editing the medical record.
     */
    public function edit(MedicalRecord $medicalRecord): View
    {
        // Check department-based access
        $departmentId = $this->getUserDepartmentId();
        if ($departmentId && $medicalRecord->doctor && $medicalRecord->doctor->department_id !== $departmentId) {
            abort(403, 'You do not have permission to edit this medical record.');
        }
        
        // Filter patients and doctors by department
        $patientsQuery = Patient::query();
        if ($departmentId) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        
        $doctorsQuery = Doctor::query();
        if ($departmentId) {
            $doctorsQuery->where('department_id', $departmentId);
        }
        $doctors = $doctorsQuery->orderBy('first_name')->get();
        
        // Get appointments for the selected patient, filtered by department
        $appointments = [];
        if ($medicalRecord->patient_id) {
            $appointmentsQuery = Appointment::where('patient_id', $medicalRecord->patient_id)
                ->with(['doctor'])
                ->orderBy('appointment_date', 'desc');
            
            if ($departmentId) {
                $appointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
            }
            
            $appointments = $appointmentsQuery->get();
        }

        return view('admin.medical-records.edit', compact('medicalRecord', 'patients', 'doctors', 'appointments'));
    }

    /**
     * Update the specified medical record.
     */
    public function update(Request $request, MedicalRecord $medicalRecord, HospitalEmailNotificationService $emailService): RedirectResponse
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'record_date' => 'nullable|date', // Allow nullable to preserve existing date
            'record_type' => 'required|in:consultation,diagnosis,prescription,lab_result,follow_up,discharge',
            'presenting_complaint' => 'required|string|max:1000',
            'history_of_presenting_complaint' => 'required|string',
            'past_medical_history' => 'required|string',
            'drug_history' => 'required|string',
            'allergies' => 'required|string',
            'social_history' => 'nullable|string',
            'family_history' => 'nullable|string',
            'ideas_concerns_expectations' => 'required|string',
            'plan' => 'required|string',
            'diagnosis' => 'nullable|string|max:500',
            'symptoms' => 'nullable|string',
            'treatment' => 'nullable|string',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after:today',
            'is_private' => 'boolean',
            'vital_signs.blood_pressure' => 'nullable|string|max:20',
            'vital_signs.temperature' => 'nullable|string|max:10',
            'vital_signs.pulse' => 'nullable|string|max:10',
            'vital_signs.respiratory_rate' => 'nullable|string|max:10',
            'vital_signs.oxygen_saturation' => 'nullable|string|max:10',
            'vital_signs.weight' => 'nullable|string|max:10',
            'vital_signs.height' => 'nullable|string|max:10',
        ]);

        // Handle vital signs
        $vitalSigns = [];
        if ($request->filled('vital_signs')) {
            foreach ($request->vital_signs as $key => $value) {
                if (!empty($value)) {
                    $vitalSigns[$key] = $value;
                }
            }
        }

        // Store original values to detect significant changes
        $originalDiagnosis = $medicalRecord->diagnosis;
        $originalTreatment = $medicalRecord->treatment;
        $originalRecordType = $medicalRecord->record_type;
        
        // Verify patient exists
        $patientId = $request->patient_id;
        $patient = Patient::find($patientId);
        if (!$patient) {
            \Log::error('Invalid patient_id in update request', [
                'patient_id' => $patientId,
                'medical_record_id' => $medicalRecord->id,
            ]);
            throw new \Exception('Invalid patient selected. Please select a valid patient.');
        }
        
        // Verify doctor exists and is accessible
        $doctorId = $request->doctor_id;
        $departmentId = $this->getUserDepartmentId();
        
        $doctor = Doctor::find($doctorId);
        if (!$doctor) {
            \Log::error('Invalid doctor_id in update request', [
                'doctor_id' => $doctorId,
                'medical_record_id' => $medicalRecord->id,
            ]);
            throw new \Exception('Invalid doctor selected. Please select a valid doctor.');
        }
        
        // If user has department restriction, verify the doctor is in the same department
        if ($departmentId && $doctorId) {
            if ($doctor->department_id !== $departmentId) {
                // Doctor is from different department, preserve existing doctor_id
                \Log::warning('Attempted to assign doctor from different department', [
                    'doctor_id' => $doctorId,
                    'doctor_department' => $doctor->department_id,
                    'user_department' => $departmentId,
                    'medical_record_id' => $medicalRecord->id,
                ]);
                $doctorId = $medicalRecord->doctor_id;
                // Verify the existing doctor still exists
                if (!$medicalRecord->doctor) {
                    throw new \Exception('Cannot update: Original doctor no longer exists. Please contact administrator.');
                }
            }
        }
        
        // Verify appointment exists and belongs to the same patient (if provided)
        $appointmentId = $request->appointment_id;
        if ($appointmentId && $patientId) {
            $appointment = Appointment::where('id', $appointmentId)
                ->where('patient_id', $patientId)
                ->first();
            
            if (!$appointment) {
                // Appointment doesn't exist or doesn't belong to patient, set to null
                \Log::warning('Invalid appointment_id in update request', [
                    'appointment_id' => $appointmentId,
                    'patient_id' => $patientId,
                    'medical_record_id' => $medicalRecord->id,
                ]);
                $appointmentId = null;
            }
        }
        
        // Build update data array explicitly - only include fillable fields
        // Ensure patient_id and doctor_id are never null (foreign key constraints require them)
        $updateData = [
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'appointment_id' => $appointmentId,
            'record_type' => $request->record_type,
            'record_date' => $request->record_date ?? $medicalRecord->record_date, // Preserve existing if not provided
            'presenting_complaint' => $request->presenting_complaint,
            'history_of_presenting_complaint' => $request->history_of_presenting_complaint,
            'past_medical_history' => $request->past_medical_history,
            'drug_history' => $request->drug_history,
            'allergies' => $request->allergies,
            'social_history' => $request->social_history,
            'family_history' => $request->family_history,
            'ideas_concerns_expectations' => $request->ideas_concerns_expectations,
            'plan' => $request->plan,
            'diagnosis' => $request->diagnosis,
            'symptoms' => $request->symptoms,
            'treatment' => $request->treatment,
            'notes' => $request->notes,
            'follow_up_date' => $request->follow_up_date,
            'vital_signs' => empty($vitalSigns) ? null : $vitalSigns,
            'is_private' => $request->boolean('is_private'),
            'updated_by' => auth()->id(),
        ];
        
        // Use database transaction to ensure atomicity
        try {
            DB::beginTransaction();
            
            // Log before update
            \Log::info('Starting medical record update', [
                'medical_record_id' => $medicalRecord->id,
                'exists_before' => $medicalRecord->exists,
                'patient_id' => $updateData['patient_id'],
                'doctor_id' => $updateData['doctor_id'],
            ]);
            
            // Perform the update
            $updateResult = $medicalRecord->update($updateData);
            
            if (!$updateResult) {
                DB::rollBack();
                \Log::error('Medical record update failed', [
                    'medical_record_id' => $medicalRecord->id,
                ]);
                throw new \Exception('Failed to update medical record in database.');
            }
            
            \Log::info('Medical record update completed', [
                'medical_record_id' => $medicalRecord->id,
                'update_result' => $updateResult,
                'exists_after_update' => $medicalRecord->exists,
            ]);
            
            // Get a fresh instance from database to verify it still exists
            // Don't use refresh() as it might cause issues with relationships
            $freshRecord = MedicalRecord::with(['patient', 'doctor'])->find($medicalRecord->id);
            if (!$freshRecord) {
                DB::rollBack();
                \Log::error('Medical record disappeared after update - cannot find in database - transaction rolled back', [
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
                \Log::error('Patient was deleted causing medical record cascade delete - transaction rolled back', [
                    'medical_record_id' => $medicalRecord->id,
                    'patient_id' => $updateData['patient_id'],
                ]);
                throw new \Exception('Patient was deleted, causing the medical record to be deleted. Transaction rolled back.');
            }
            
            if (!$medicalRecord->doctor) {
                DB::rollBack();
                \Log::error('Doctor was deleted causing medical record cascade delete - transaction rolled back', [
                    'medical_record_id' => $medicalRecord->id,
                    'doctor_id' => $updateData['doctor_id'],
                ]);
                throw new \Exception('Doctor was deleted, causing the medical record to be deleted. Transaction rolled back.');
            }
            
            \Log::info('Medical record verified after update', [
                'medical_record_id' => $medicalRecord->id,
                'exists' => $medicalRecord->exists,
                'patient_id' => $medicalRecord->patient_id,
                'doctor_id' => $medicalRecord->doctor_id,
            ]);
            
            // Verify doctor is still accessible if department restricted
            if ($departmentId && $medicalRecord->doctor && $medicalRecord->doctor->department_id !== $departmentId) {
                // This shouldn't happen if our validation above worked, but handle it gracefully
                \Log::warning('Medical record doctor changed to different department after update', [
                    'medical_record_id' => $medicalRecord->id,
                    'doctor_id' => $medicalRecord->doctor_id,
                    'department_id' => $departmentId
                ]);
            }
            
            // Handle file uploads - must happen inside transaction
            $uploadError = null;
            try {
                // Verify record still exists before file upload
                $verifyBeforeUpload = MedicalRecord::find($medicalRecord->id);
                if (!$verifyBeforeUpload) {
                    throw new \Exception('Medical record was deleted before file upload. Transaction will be rolled back.');
                }
                
                $this->handleFileUploads($request, $medicalRecord, auth()->user());
                
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
                    \Log::error('Medical record was deleted during file upload - transaction rolled back', [
                        'medical_record_id' => $medicalRecord->id,
                        'error' => $e->getMessage()
                    ]);
                    throw new \Exception('Medical record was deleted during file upload. Update aborted and rolled back.');
                }
                // For other file upload errors, log but don't fail the update
                \Log::error('File upload error during medical record update: ' . $e->getMessage());
                $uploadError = $e->getMessage();
            }
            
            // Send notifications for medical record updates
            try {
                $this->handleMedicalRecordUpdateNotifications(
                    $medicalRecord, 
                    $originalDiagnosis, 
                    $originalTreatment, 
                    $originalRecordType,
                    $emailService
                );
            } catch (\Exception $e) {
                // Log error but don't fail the update
                \Log::error('Notification error during medical record update: ' . $e->getMessage());
            }

            // Final verification before redirect - ensure record still exists
            $finalCheck = MedicalRecord::find($medicalRecord->id);
            if (!$finalCheck) {
                \Log::error('Medical record was deleted during update process', [
                    'medical_record_id' => $medicalRecord->id,
                    'original_patient_id' => $updateData['patient_id'],
                    'original_doctor_id' => $updateData['doctor_id'],
                ]);
                return redirect()->route('admin.medical-records.index')
                    ->with('error', 'Medical record was deleted during update. This may have occurred due to the patient or doctor being deleted. Please check the logs for details.');
            }
            
            $successMessage = 'Medical record updated successfully!';
            if ($uploadError) {
                $successMessage .= ' However, there was an error uploading files: ' . $uploadError;
            } else {
                $successMessage .= ' Patient has been notified of the changes.';
            }

            // Commit transaction if everything succeeded
            DB::commit();
            
            \Log::info('Medical record update transaction committed', [
                'medical_record_id' => $finalCheck->id,
            ]);
            
            // Use the verified record for redirect
            return redirect()->route('admin.medical-records.show', $finalCheck)
                ->with('success', $successMessage);
                
        } catch (\Exception $e) {
            // Rollback transaction on any error
            DB::rollBack();
            
            \Log::error('Medical record update transaction rolled back', [
                'medical_record_id' => $medicalRecord->id ?? 'unknown',
                'error' => $e->getMessage(),
            ]);
            \Log::error('Medical record update error: ' . $e->getMessage(), [
                'medical_record_id' => $medicalRecord->id ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Check if record still exists before redirecting to edit
            $checkRecord = MedicalRecord::find($medicalRecord->id ?? null);
            if (!$checkRecord) {
                // Record was deleted, redirect to index
                return redirect()->route('admin.medical-records.index')
                    ->with('error', 'Medical record was deleted during update. Error: ' . $e->getMessage());
            }
            
            return redirect()->route('admin.medical-records.edit', $checkRecord)
                ->with('error', 'Failed to update medical record. Please try again. Error: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified medical record.
     */
    public function destroy(MedicalRecord $medicalRecord): RedirectResponse
    {
        $patientName = $medicalRecord->patient->full_name;
        $medicalRecord->delete();

        return redirect()->route('admin.medical-records.index')
            ->with('success', "Medical record for {$patientName} deleted successfully!");
    }

    /**
     * Create medical record from appointment.
     */
    public function createFromAppointment(Appointment $appointment): View
    {
        $appointment->load(['patient', 'doctor']);
        
        // Filter patients and doctors by department
        $departmentId = $this->getUserDepartmentId();
        
        $patientsQuery = Patient::query();
        if ($departmentId) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        
        $doctorsQuery = Doctor::query();
        if ($departmentId) {
            $doctorsQuery->where('department_id', $departmentId);
        }
        $doctors = $doctorsQuery->orderBy('first_name')->get();
        $appointments = collect([$appointment]);

        return view('admin.medical-records.create', compact('patients', 'doctors', 'appointments', 'appointment'));
    }

    /**
     * Get appointments by patient (AJAX).
     */
    public function getAppointmentsByPatient(Request $request)
    {
        $patientId = $request->patient_id;
        
        $appointments = Appointment::where('patient_id', $patientId)
            ->with(['doctor'])
            ->orderBy('appointment_date', 'desc')
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'appointment_number' => $appointment->appointment_number,
                    'date' => $appointment->appointment_date->format('M d, Y'),
                    'time' => $appointment->appointment_time,
                    'doctor' => $appointment->doctor->full_name,
                    'status' => $appointment->status,
                    'display' => "#{$appointment->appointment_number} - {$appointment->appointment_date->format('M d, Y')} - Dr. {$appointment->doctor->full_name}"
                ];
            });

        return response()->json($appointments);
    }
    
    /**
     * Handle notifications for new medical records
     */
    private function handleNewMedicalRecordNotifications(
        MedicalRecord $medicalRecord,
        HospitalEmailNotificationService $emailService
    ) {
        try {
            // Don't send notifications for private records unless explicitly allowed
            if ($medicalRecord->is_private && !config('hospital.notifications.medical_record_updates.include_private', false)) {
                return;
            }
            
            $patient = $medicalRecord->patient;
            $doctor = $medicalRecord->doctor;
            
            // Prepare notification information
            $recordInfo = [
                'doctor_name' => $doctor ? $doctor->full_name : 'Medical Team',
                'record_type' => ucfirst($medicalRecord->record_type),
                'creation_date' => $medicalRecord->created_at->format('F d, Y'),
                'has_diagnosis' => !empty($medicalRecord->diagnosis),
                'has_treatment' => !empty($medicalRecord->treatment),
                'follow_up_required' => !empty($medicalRecord->follow_up_date),
            ];
            
            // Send new medical record email notification
            $this->sendNewMedicalRecordNotification($patient, $medicalRecord, $recordInfo, $emailService);
            
            \Log::info('New medical record notifications sent', [
                'medical_record_id' => $medicalRecord->id,
                'patient_id' => $patient->id,
                'record_type' => $medicalRecord->record_type
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to send new medical record notifications', [
                'medical_record_id' => $medicalRecord->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Send new medical record notification to patient
     */
    private function sendNewMedicalRecordNotification(
        Patient $patient,
        MedicalRecord $medicalRecord,
        array $recordInfo,
        HospitalEmailNotificationService $emailService
    ) {
        if (!$patient->email) {
            \Log::warning('Cannot send new medical record email notification - patient has no email', [
                'patient_id' => $patient->id,
                'medical_record_id' => $medicalRecord->id
            ]);
            return;
        }
        
        // Determine the appropriate email template and content based on record type
        $emailContent = $this->getNewRecordEmailContent($medicalRecord, $recordInfo);
        
        // For significant diagnoses, use the specific diagnosis template
        if ($medicalRecord->diagnosis && $this->isDiagnosisSignificant($medicalRecord->diagnosis)) {
            $diagnosisInfo = [
                'diagnosis' => $medicalRecord->diagnosis,
                'doctor_name' => $recordInfo['doctor_name'],
                'diagnosis_date' => $recordInfo['creation_date'],
                'explanation' => $emailContent['explanation'],
                'treatment_options' => $medicalRecord->treatment ?? 'Treatment options will be discussed with your doctor.',
                'follow_up_instructions' => $medicalRecord->follow_up_date ? 
                    'Please schedule a follow-up appointment for ' . $medicalRecord->follow_up_date->format('F d, Y') : 
                    'Follow-up care will be discussed with your healthcare provider.',
                'urgency_level' => $emailContent['urgency_level'],
                'next_steps' => $emailContent['next_steps']
            ];
            
            $emailService->sendSignificantDiagnosisNotification($patient, $diagnosisInfo);
        } else {
            // Use general medical record update template for other records
            $updateInfo = [
                'doctor_name' => $recordInfo['doctor_name'],
                'update_type' => 'New ' . $recordInfo['record_type'],
                'changes_summary' => $emailContent['summary'],
            ];
            
            $emailService->sendMedicalRecordUpdateNotification($patient, $medicalRecord, $updateInfo);
        }
    }
    
    /**
     * Get email content based on record type
     */
    private function getNewRecordEmailContent(MedicalRecord $medicalRecord, array $recordInfo): array
    {
        $content = [];
        
        switch ($medicalRecord->record_type) {
            case 'diagnosis':
                $content = [
                    'summary' => 'A new diagnosis has been added to your medical record.',
                    'explanation' => 'Your healthcare provider has documented a new diagnosis based on your recent visit.',
                    'urgency_level' => $this->isDiagnosisSignificant($medicalRecord->diagnosis) ? 'High' : 'Standard',
                    'next_steps' => 'Please review your updated medical record and contact your healthcare provider if you have any questions.'
                ];
                break;
                
            case 'consultation':
                $content = [
                    'summary' => 'Your consultation notes have been added to your medical record.',
                    'explanation' => 'Your healthcare provider has documented the details from your recent consultation.',
                    'urgency_level' => 'Standard',
                    'next_steps' => 'Review your consultation notes and follow any recommendations provided.'
                ];
                break;
                
            case 'prescription':
                $content = [
                    'summary' => 'A new prescription record has been added to your file.',
                    'explanation' => 'Your healthcare provider has documented prescription information.',
                    'urgency_level' => 'Standard',
                    'next_steps' => 'Please review your prescription details and follow the medication instructions.'
                ];
                break;
                
            case 'lab_result':
                $content = [
                    'summary' => 'Lab result information has been added to your medical record.',
                    'explanation' => 'Your healthcare provider has documented lab results from your recent tests.',
                    'urgency_level' => 'Standard',
                    'next_steps' => 'Please review your lab results and discuss them with your healthcare provider if needed.'
                ];
                break;
                
            case 'follow_up':
                $content = [
                    'summary' => 'A follow-up care plan has been created for you.',
                    'explanation' => 'Your healthcare provider has scheduled follow-up care based on your recent visit.',
                    'urgency_level' => 'Medium',
                    'next_steps' => 'Please schedule your follow-up appointment as recommended.'
                ];
                break;
                
            case 'discharge':
                $content = [
                    'summary' => 'Your discharge summary has been added to your medical record.',
                    'explanation' => 'Your healthcare provider has documented your discharge instructions and care plan.',
                    'urgency_level' => 'Medium',
                    'next_steps' => 'Please follow all discharge instructions and attend scheduled follow-up appointments.'
                ];
                break;
                
            default:
                $content = [
                    'summary' => 'A new medical record has been added to your file.',
                    'explanation' => 'Your healthcare provider has added new information to your medical record.',
                    'urgency_level' => 'Standard',
                    'next_steps' => 'Please review your updated medical record.'
                ];
        }
        
        return $content;
    }
    
    /**
     * Check if a diagnosis is considered significant
     */
    private function isDiagnosisSignificant(?string $diagnosis): bool
    {
        // Return false if diagnosis is null or empty
        if (empty($diagnosis)) {
            return false;
        }
        
        $significantKeywords = ['cancer', 'tumor', 'cardiac', 'stroke', 'diabetes', 'hypertension', 'chronic'];
        
        foreach ($significantKeywords as $keyword) {
            if (stripos($diagnosis, $keyword) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Handle notifications for medical record updates
     */
    private function handleMedicalRecordUpdateNotifications(
        MedicalRecord $medicalRecord,
        $originalDiagnosis,
        $originalTreatment,
        $originalRecordType,
        HospitalEmailNotificationService $emailService
    ) {
        try {
            if (!config('hospital.notifications.medical_record_updates.enabled', true)) {
                return;
            }
            
            // Don't send notifications for private records unless explicitly allowed
            if ($medicalRecord->is_private && !config('hospital.notifications.medical_record_updates.include_private', false)) {
                return;
            }
            
            $patient = $medicalRecord->patient;
            $doctor = $medicalRecord->doctor;
            
            // Determine what changed and create appropriate notifications
            $changes = $this->detectMedicalRecordChanges(
                $medicalRecord, 
                $originalDiagnosis, 
                $originalTreatment, 
                $originalRecordType
            );
            
            if (empty($changes)) {
                return; // No significant changes to notify about
            }
            
            // Prepare update information
            $updateInfo = [
                'doctor_name' => $doctor ? $doctor->full_name : 'Medical Team',
                'update_type' => $this->getUpdateType($changes),
                'changes_summary' => $this->formatChangesSummary($changes),
            ];
            
            // Send general medical record update notification
            $emailService->sendMedicalRecordUpdateNotification($patient, $medicalRecord, $updateInfo);
            
            // Send specific notifications based on change type
            $this->sendSpecificNotifications($medicalRecord, $changes, $emailService);
            
            \Log::info('Medical record update notifications sent', [
                'medical_record_id' => $medicalRecord->id,
                'patient_id' => $patient->id,
                'changes' => array_keys($changes)
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Failed to send medical record update notifications', [
                'medical_record_id' => $medicalRecord->id,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    /**
     * Detect what changed in the medical record
     */
    private function detectMedicalRecordChanges(
        MedicalRecord $medicalRecord,
        $originalDiagnosis,
        $originalTreatment,
        $originalRecordType
    ) {
        $changes = [];
        
        // Check diagnosis changes
        if ($originalDiagnosis !== $medicalRecord->diagnosis) {
            $changes['diagnosis'] = [
                'old' => $originalDiagnosis,
                'new' => $medicalRecord->diagnosis,
                'significance' => $this->assessDiagnosisSignificance($originalDiagnosis, $medicalRecord->diagnosis)
            ];
        }
        
        // Check treatment changes
        if ($originalTreatment !== $medicalRecord->treatment) {
            $changes['treatment'] = [
                'old' => $originalTreatment,
                'new' => $medicalRecord->treatment,
                'significance' => $this->assessTreatmentSignificance($originalTreatment, $medicalRecord->treatment)
            ];
        }
        
        // Check record type changes
        if ($originalRecordType !== $medicalRecord->record_type) {
            $changes['record_type'] = [
                'old' => $originalRecordType,
                'new' => $medicalRecord->record_type
            ];
        }
        
        return $changes;
    }
    
    /**
     * Assess the significance of diagnosis changes
     */
    private function assessDiagnosisSignificance($oldDiagnosis, $newDiagnosis)
    {
        // Simple keyword-based significance assessment
        $significantKeywords = ['cancer', 'tumor', 'cardiac', 'stroke', 'diabetes', 'hypertension', 'chronic'];
        
        $oldSignificant = false;
        $newSignificant = false;
        
        foreach ($significantKeywords as $keyword) {
            if (stripos($oldDiagnosis ?? '', $keyword) !== false) {
                $oldSignificant = true;
            }
            if (stripos($newDiagnosis ?? '', $keyword) !== false) {
                $newSignificant = true;
            }
        }
        
        if ($newSignificant && !$oldSignificant) {
            return 'high'; // New significant diagnosis
        } elseif ($newSignificant || $oldSignificant) {
            return 'medium'; // Changes to significant diagnosis
        }
        
        return 'low';
    }
    
    /**
     * Assess the significance of treatment changes
     */
    private function assessTreatmentSignificance($oldTreatment, $newTreatment)
    {
        $significantKeywords = ['surgery', 'chemotherapy', 'radiation', 'medication', 'hospitalization'];
        
        foreach ($significantKeywords as $keyword) {
            if (stripos($newTreatment ?? '', $keyword) !== false) {
                return 'high';
            }
        }
        
        return 'medium';
    }
    
    /**
     * Get update type based on changes
     */
    private function getUpdateType($changes)
    {
        if (isset($changes['diagnosis']['significance']) && $changes['diagnosis']['significance'] === 'high') {
            return 'Significant Diagnosis Update';
        }
        
        if (isset($changes['treatment']['significance']) && $changes['treatment']['significance'] === 'high') {
            return 'Treatment Plan Update';
        }
        
        if (isset($changes['diagnosis'])) {
            return 'Diagnosis Update';
        }
        
        if (isset($changes['treatment'])) {
            return 'Treatment Update';
        }
        
        return 'Medical Record Update';
    }
    
    /**
     * Format changes summary for email
     */
    private function formatChangesSummary($changes)
    {
        $summary = [];
        
        if (isset($changes['diagnosis'])) {
            if ($changes['diagnosis']['old'] && $changes['diagnosis']['new']) {
                $summary[] = 'Diagnosis has been updated';
            } elseif ($changes['diagnosis']['new']) {
                $summary[] = 'New diagnosis has been added';
            }
        }
        
        if (isset($changes['treatment'])) {
            if ($changes['treatment']['old'] && $changes['treatment']['new']) {
                $summary[] = 'Treatment plan has been modified';
            } elseif ($changes['treatment']['new']) {
                $summary[] = 'New treatment plan has been added';
            }
        }
        
        if (isset($changes['record_type'])) {
            $summary[] = 'Record type has been changed to ' . $changes['record_type']['new'];
        }
        
        return !empty($summary) ? implode('. ', $summary) . '.' : 'Your medical record has been updated with new information.';
    }
    
    /**
     * Send specific notifications based on change type
     */
    private function sendSpecificNotifications(MedicalRecord $medicalRecord, $changes, HospitalEmailNotificationService $emailService)
    {
        $patient = $medicalRecord->patient;
        $doctor = $medicalRecord->doctor;
        
        // Send significant diagnosis notification
        if (isset($changes['diagnosis']['significance']) && $changes['diagnosis']['significance'] === 'high') {
            $diagnosisInfo = [
                'diagnosis' => $medicalRecord->diagnosis,
                'doctor_name' => $doctor ? $doctor->full_name : 'Medical Team',
                'diagnosis_date' => $medicalRecord->updated_at->format('F d, Y'),
                'explanation' => 'A significant diagnosis has been added to your medical record.',
                'treatment_options' => $medicalRecord->treatment ?? 'Treatment options will be discussed with your doctor.',
                'follow_up_instructions' => $medicalRecord->follow_up_date ? 
                    'Please schedule a follow-up appointment for ' . $medicalRecord->follow_up_date->format('F d, Y') : 
                    'Follow-up care will be discussed with your healthcare provider.',
                'urgency_level' => $changes['diagnosis']['significance'] === 'high' ? 'High' : 'Standard',
                'next_steps' => 'Please contact your healthcare provider to discuss this diagnosis in detail.'
            ];
            
            $emailService->sendSignificantDiagnosisNotification($patient, $diagnosisInfo);
        }
        
        // Send treatment plan update notification
        if (isset($changes['treatment']['significance']) && $changes['treatment']['significance'] === 'high') {
            $treatmentInfo = [
                'doctor_name' => $doctor ? $doctor->full_name : 'Medical Team',
                'changes' => 'Your treatment plan has been updated with new recommendations.',
                'new_medications' => 'Please refer to your updated medical record for medication details.',
                'special_instructions' => $medicalRecord->notes ?? 'Follow the updated care instructions provided.',
                'next_appointment' => $medicalRecord->follow_up_date ? 
                    $medicalRecord->follow_up_date->format('F d, Y') : 
                    'Please schedule as recommended by your doctor.',
                'monitoring_requirements' => 'Follow up with your healthcare provider as scheduled.'
            ];
            
            $emailService->sendTreatmentPlanUpdateNotification($patient, $treatmentInfo);
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

        // Check total number of files (including existing)
        $existingCount = $medicalRecord->attachments()->count();
        $newCount = is_array($files) ? count($files) : 1;
        
        if ($existingCount + $newCount > 10) {
            throw new \Exception('Maximum 10 files allowed per medical record. Current: ' . $existingCount . ', Attempting to add: ' . $newCount);
        }

        // Ensure files is an array
        if (!is_array($files)) {
            $files = [$files];
        }

        foreach ($files as $index => $file) {
            if (!$file->isValid()) {
                continue;
            }

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
                $currentRecord = MedicalRecord::with(['patient', 'doctor'])->find($medicalRecord->id);
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
                $verifyRecord = MedicalRecord::find($currentRecord->id);
                if (!$verifyRecord) {
                    \Log::error('Medical record disappeared after file storage', [
                        'medical_record_id' => $currentRecord->id,
                        'filename' => $displayFileName,
                    ]);
                    // Delete the uploaded file if record doesn't exist
                    try {
                        Storage::disk('private')->delete($path);
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

    /**
     * Show the import form for medical records
     */
    public function showImport(): View
    {
        return view('admin.medical-records.import');
    }

    /**
     * Import medical records from CSV
     */
    public function importCsv(Request $request): RedirectResponse
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
                'patient email' => 'patient_email',
                'doctor id' => 'doctor_id',
                'doctor email' => 'doctor_email',
                'appointment id' => 'appointment_id',
                'record type' => 'record_type',
                'record date' => 'record_date',
                'follow up date' => 'follow_up_date',
                'presenting complaint' => 'presenting_complaint',
                'history of presenting complaint' => 'history_of_presenting_complaint',
                'past medical history' => 'past_medical_history',
                'drug history' => 'drug_history',
                'allergies' => 'allergies',
                'social history' => 'social_history',
                'family history' => 'family_history',
                'ideas concerns expectations' => 'ideas_concerns_expectations',
                'plan' => 'plan',
                'diagnosis' => 'diagnosis',
                'symptoms' => 'symptoms',
                'treatment' => 'treatment',
                'notes' => 'notes',
                'blood pressure' => 'vital_signs_blood_pressure',
                'temperature' => 'vital_signs_temperature',
                'pulse' => 'vital_signs_pulse',
                'respiratory rate' => 'vital_signs_respiratory_rate',
                'oxygen saturation' => 'vital_signs_oxygen_saturation',
                'weight' => 'vital_signs_weight',
                'height' => 'vital_signs_height',
                'is private' => 'is_private',
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
                    if (empty($data['patient_id']) && empty($data['patient_email'])) {
                        throw new \Exception("Row {$rowNumber}: Missing required field (patient_id or patient_email)");
                    }

                    if (empty($data['doctor_id']) && empty($data['doctor_email'])) {
                        throw new \Exception("Row {$rowNumber}: Missing required field (doctor_id or doctor_email)");
                    }

                    if (empty($data['record_type'])) {
                        throw new \Exception("Row {$rowNumber}: Missing required field (record_type)");
                    }

                    // Find patient
                    $patient = null;
                    if (!empty($data['patient_id'])) {
                        $patient = Patient::where('id', $data['patient_id'])
                            ->orWhere('patient_id', $data['patient_id'])
                            ->first();
                    } elseif (!empty($data['patient_email'])) {
                        $patient = Patient::where('email', $data['patient_email'])->first();
                    }
                    
                    if (!$patient) {
                        throw new \Exception("Row {$rowNumber}: Patient not found");
                    }
                    unset($data['patient_id'], $data['patient_email']);
                    $data['patient_id'] = $patient->id;

                    // Find doctor
                    $doctor = null;
                    if (!empty($data['doctor_id'])) {
                        $doctor = Doctor::where('id', $data['doctor_id'])->first();
                    } elseif (!empty($data['doctor_email'])) {
                        $user = \App\Models\User::where('email', $data['doctor_email'])->where('role', 'doctor')->first();
                        if ($user) {
                            $doctor = Doctor::where('user_id', $user->id)->first();
                        }
                    }
                    
                    if (!$doctor) {
                        throw new \Exception("Row {$rowNumber}: Doctor not found");
                    }
                    unset($data['doctor_id'], $data['doctor_email']);
                    $data['doctor_id'] = $doctor->id;

                    // Find appointment if provided
                    if (!empty($data['appointment_id'])) {
                        $appointment = Appointment::where('id', $data['appointment_id'])->first();
                        if (!$appointment) {
                            throw new \Exception("Row {$rowNumber}: Appointment not found");
                        }
                    }

                    // Handle record_type validation
                    $validRecordTypes = ['consultation', 'diagnosis', 'prescription', 'lab_result', 'follow_up', 'discharge'];
                    if (!in_array(strtolower($data['record_type']), $validRecordTypes)) {
                        throw new \Exception("Row {$rowNumber}: Invalid record_type. Must be one of: " . implode(', ', $validRecordTypes));
                    }
                    $data['record_type'] = strtolower($data['record_type']);

                    // Handle record_date - enforce MM/DD/YYYY format
                    if (!empty($data['record_date'])) {
                        try {
                            $data['record_date'] = parseImportDate($data['record_date']);
                        } catch (\Exception $e) {
                            throw new \Exception("Row {$rowNumber}: Record Date - " . $e->getMessage());
                        }
                    } else {
                        $data['record_date'] = now()->format('Y-m-d');
                    }

                    // Handle follow_up_date - enforce MM/DD/YYYY format
                    if (!empty($data['follow_up_date'])) {
                        try {
                            $data['follow_up_date'] = parseImportDate($data['follow_up_date']);
                        } catch (\Exception $e) {
                            throw new \Exception("Row {$rowNumber}: Follow Up Date - " . $e->getMessage());
                        }
                    }

                    // Handle vital signs
                    $vitalSigns = [];
                    if (!empty($data['vital_signs_blood_pressure'])) {
                        $vitalSigns['blood_pressure'] = $data['vital_signs_blood_pressure'];
                    }
                    if (!empty($data['vital_signs_temperature'])) {
                        $vitalSigns['temperature'] = $data['vital_signs_temperature'];
                    }
                    if (!empty($data['vital_signs_pulse'])) {
                        $vitalSigns['pulse'] = $data['vital_signs_pulse'];
                    }
                    if (!empty($data['vital_signs_respiratory_rate'])) {
                        $vitalSigns['respiratory_rate'] = $data['vital_signs_respiratory_rate'];
                    }
                    if (!empty($data['vital_signs_oxygen_saturation'])) {
                        $vitalSigns['oxygen_saturation'] = $data['vital_signs_oxygen_saturation'];
                    }
                    if (!empty($data['vital_signs_weight'])) {
                        $vitalSigns['weight'] = $data['vital_signs_weight'];
                    }
                    if (!empty($data['vital_signs_height'])) {
                        $vitalSigns['height'] = $data['vital_signs_height'];
                    }
                    $data['vital_signs'] = !empty($vitalSigns) ? $vitalSigns : null;
                    
                    // Remove vital signs keys from data
                    unset(
                        $data['vital_signs_blood_pressure'],
                        $data['vital_signs_temperature'],
                        $data['vital_signs_pulse'],
                        $data['vital_signs_respiratory_rate'],
                        $data['vital_signs_oxygen_saturation'],
                        $data['vital_signs_weight'],
                        $data['vital_signs_height']
                    );

                    // Handle is_private
                    if (isset($data['is_private'])) {
                        $data['is_private'] = in_array(strtolower($data['is_private']), ['yes', 'true', '1', 'private']);
                    } else {
                        $data['is_private'] = false;
                    }

                    // Set created_by
                    $data['created_by'] = Auth::id();

                    // Find existing record (if updating)
                    $existingRecord = null;
                    if ($importMode !== 'insert') {
                        // Try to find by patient, doctor, and record_date
                        $existingRecord = MedicalRecord::where('patient_id', $data['patient_id'])
                            ->where('doctor_id', $data['doctor_id'])
                            ->whereDate('record_date', $data['record_date'])
                            ->where('record_type', $data['record_type'])
                            ->first();
                    }

                    // Handle import modes
                    if ($existingRecord) {
                        if ($importMode === 'insert' || $importMode === 'skip') {
                            $stats['skipped']++;
                            continue;
                        }
                        
                        if ($importMode === 'update' || $importMode === 'upsert') {
                            $data['updated_by'] = Auth::id();
                            $existingRecord->update($data);
                            $stats['updated']++;
                        }
                    } else {
                        if ($importMode === 'update') {
                            $stats['skipped']++;
                            continue;
                        }
                        
                        // Create new
                        $medicalRecord = MedicalRecord::create($data);
                        $stats['created']++;
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

            return redirect()->route('admin.medical-records.index')
                ->with('success', $message)
                ->with('import_stats', $stats);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Medical Records CSV Import Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to import medical records: ' . $e->getMessage())
                ->withInput();
        }
    }
}
