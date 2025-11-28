<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Services\HospitalEmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PrescriptionsController extends Controller
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
     * Display a listing of prescriptions.
     */
    public function index(Request $request): View
    {
        $query = Prescription::with(['patient', 'doctor', 'appointment', 'medicalRecord']);

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

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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
                $q->where('medication_name', 'like', "%{$search}%")
                  ->orWhere('instructions', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($patientQuery) use ($search) {
                      $patientQuery->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $prescriptions = $query->orderBy('created_at', 'desc')->paginate(15);

        // Filter patients by department
        $patientsQuery = Patient::query();
        if ($departmentId = $this->getUserDepartmentId()) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        $doctors = Doctor::orderBy('first_name')->get();

        $stats = [
            'total' => Prescription::count(),
            'active' => Prescription::where('status', 'active')->count(),
            'completed' => Prescription::where('status', 'completed')->count(),
            'this_month' => Prescription::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
        ];

        return view('admin.prescriptions.index', compact('prescriptions', 'patients', 'doctors', 'stats'));
    }

    /**
     * Show the form for creating a new prescription.
     */
    public function create(Request $request): View
    {
        // Filter patients by department
        $patientsQuery = Patient::query();
        if ($departmentId = $this->getUserDepartmentId()) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        $doctors = Doctor::orderBy('first_name')->get();
        
        // If appointment_id or medical_record_id is provided
        $selectedAppointment = null;
        $selectedMedicalRecord = null;
        
        if ($request->filled('appointment_id')) {
            $selectedAppointment = Appointment::with(['patient', 'doctor'])->find($request->appointment_id);
        }
        
        if ($request->filled('medical_record_id')) {
            $selectedMedicalRecord = MedicalRecord::with(['patient', 'doctor'])->find($request->medical_record_id);
        }

        return view('admin.prescriptions.create', compact('patients', 'doctors', 'selectedAppointment', 'selectedMedicalRecord'));
    }

    /**
     * Store a newly created prescription.
     */
    public function store(Request $request, HospitalEmailNotificationService $emailService): RedirectResponse
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'prescription_date' => 'required|date',
            'prescription_type' => 'required|string|in:new,refill,modification,emergency',
            'status' => 'nullable|string|in:pending,approved,dispensed,cancelled',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after:today',
            'refills_allowed' => 'nullable|integer|min:0|max:6',
            'medications' => 'required|array|min:1',
            'medications.*.name' => 'required|string|max:255',
            'medications.*.dosage' => 'required|string|max:100',
            'medications.*.frequency' => 'required|string|max:100',
            'medications.*.duration' => 'nullable|string|max:100',
            'medications.*.form' => 'nullable|string|max:50',
            'medications.*.instructions' => 'nullable|string|max:500',
        ]);

        // Filter out empty medications
        $medications = array_filter($request->medications, function($medication) {
            return !empty($medication['name']) && !empty($medication['dosage']) && !empty($medication['frequency']);
        });

        if (empty($medications)) {
            return back()->withErrors(['medications' => 'At least one valid medication is required.'])->withInput();
        }

        $prescription = Prescription::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'appointment_id' => $request->appointment_id,
            'medical_record_id' => $request->medical_record_id,
            'prescription_number' => Prescription::generatePrescriptionNumber(),
            'medications' => $medications, // Laravel will auto-cast to JSON
            'prescription_type' => $request->prescription_type,
            'prescription_date' => $request->prescription_date,
            'prescribed_date' => $request->prescription_date,
            'diagnosis' => $request->diagnosis,
            'notes' => $request->notes,
            'follow_up_date' => $request->follow_up_date,
            'refills_allowed' => $request->refills_allowed ?? 0,
            'status' => $request->status ?? 'pending',
            'created_by' => auth()->id(),
        ]);
        
        // Load relationships for email notifications
        $prescription->load(['patient', 'doctor']);
        
        // Send notification to patient when prescription is ready (dispensed status)
        if ($prescription->status === 'dispensed' && config('hospital.notifications.prescription_ready.enabled', true)) {
            try {
                $prescriptionInfo = [
                    'id' => $prescription->id,
                    'doctor_name' => $prescription->doctor ? $prescription->doctor->name : 'Your Doctor',
                    'pharmacy_hours' => config('hospital.pharmacy_hours', '8 AM - 8 PM'),
                ];
                $emailService->sendPrescriptionReady($prescription->patient, $prescriptionInfo);
            } catch (\Exception $e) {
                Log::error('Failed to send prescription ready notification', [
                    'prescription_id' => $prescription->id,
                    'patient_id' => $prescription->patient_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Send notification to patient for new prescription (pending/approved status)
        if (in_array($prescription->status, ['approved']) && config('hospital.notifications.prescription_ready.enabled', true)) {
            try {
                $prescriptionInfo = [
                    'id' => $prescription->id,
                    'doctor_name' => $prescription->doctor ? $prescription->doctor->name : 'Your Doctor',
                    'pharmacy_hours' => config('hospital.pharmacy_hours', '8 AM - 8 PM'),
                ];
                $emailService->sendPrescriptionReady($prescription->patient, $prescriptionInfo);
            } catch (\Exception $e) {
                Log::error('Failed to send new prescription notification', [
                    'prescription_id' => $prescription->id,
                    'patient_id' => $prescription->patient_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->route('admin.prescriptions.index')
            ->with('success', 'Prescription created successfully! Notifications have been sent.');
    }

    /**
     * Display the specified prescription.
     */
    public function show(Prescription $prescription): View
    {
        $prescription->load(['patient', 'doctor', 'appointment', 'medicalRecord']);
        
        return view('admin.prescriptions.show', compact('prescription'));
    }

    /**
     * Show the form for editing the prescription.
     */
    public function edit(Prescription $prescription): View
    {
        // Filter patients by department
        $patientsQuery = Patient::query();
        if ($departmentId = $this->getUserDepartmentId()) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        $doctors = Doctor::orderBy('first_name')->get();
        
        return view('admin.prescriptions.edit', compact('prescription', 'patients', 'doctors'));
    }

    /**
     * Update the specified prescription.
     */
    public function update(Request $request, Prescription $prescription, HospitalEmailNotificationService $emailService): RedirectResponse
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'prescription_date' => 'required|date',
            'prescription_type' => 'required|string|in:new,refill,modification,emergency',
            'medications' => 'required|array|min:1',
            'medications.*.name' => 'required|string|max:255',
            'medications.*.dosage' => 'required|string|max:100',
            'medications.*.frequency' => 'required|string|max:100',
            'medications.*.duration' => 'required|string|max:100',
            'medications.*.form' => 'nullable|string|max:50',
            'medications.*.instructions' => 'nullable|string|max:500',
        ]);

        // Filter out empty medications
        $medications = array_filter($request->medications, function($medication) {
            return !empty($medication['name']) && !empty($medication['dosage']) && !empty($medication['frequency']);
        });

        if (empty($medications)) {
            return back()->withErrors(['medications' => 'At least one valid medication is required.'])->withInput();
        }

        $oldStatus = $prescription->status;
        
        $prescription->update([
            'patient_id' => $request->patient_id,
            'doctor_id' => $request->doctor_id,
            'medications' => $medications, // Laravel will auto-cast to JSON
            'prescription_type' => $request->prescription_type,
            'prescription_date' => $request->prescription_date,
            'status' => $request->status ?? $prescription->status,
            'updated_by' => auth()->id(),
        ]);
        
        // Load relationships for email notifications
        $prescription->load(['patient', 'doctor']);
        
        // Send notifications if status changed to dispensed
        if ($oldStatus !== 'dispensed' && $prescription->status === 'dispensed') {
            if (config('hospital.notifications.prescription_ready.enabled', true)) {
                try {
                    $prescriptionInfo = [
                        'id' => $prescription->id,
                        'doctor_name' => $prescription->doctor ? $prescription->doctor->name : 'Your Doctor',
                        'pharmacy_hours' => config('hospital.pharmacy_hours', '8 AM - 8 PM'),
                    ];
                    $emailService->sendPrescriptionReady($prescription->patient, $prescriptionInfo);
                } catch (\Exception $e) {
                    Log::error('Failed to send prescription ready notification', [
                        'prescription_id' => $prescription->id,
                        'patient_id' => $prescription->patient_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }
        
        // Send notifications if status changed to approved
        if ($oldStatus !== 'approved' && $prescription->status === 'approved') {
            if (config('hospital.notifications.prescription_ready.enabled', true)) {
                try {
                    $prescriptionInfo = [
                        'id' => $prescription->id,
                        'doctor_name' => $prescription->doctor->name ?? 'Your Doctor',
                        'pharmacy_hours' => config('hospital.pharmacy_hours', '8 AM - 8 PM'),
                    ];
                    $emailService->sendPrescriptionReady($prescription->patient, $prescriptionInfo);
                } catch (\Exception $e) {
                    Log::error('Failed to send new prescription notification', [
                        'prescription_id' => $prescription->id,
                        'patient_id' => $prescription->patient_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Send to electronic dispenser API if enabled
            if (config('hospital.dispenser_api.send_on_approval', true)) {
                try {
                    $dispenserService = app(\App\Services\ElectronicDispenserService::class);
                    $result = $dispenserService->sendPrescription($prescription);
                    
                    if ($result['sent']) {
                        Log::info('Prescription sent to electronic dispenser successfully (Admin)', [
                            'prescription_id' => $prescription->id,
                            'result' => $result
                        ]);
                    } else {
                        Log::warning('Prescription could not be sent to electronic dispenser (Admin)', [
                            'prescription_id' => $prescription->id,
                            'result' => $result
                        ]);
                    }
                } catch (\Exception $e) {
                    // Log error but don't fail the update process
                    Log::error('Failed to send prescription to electronic dispenser (Admin)', [
                        'prescription_id' => $prescription->id,
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }
        }

        return redirect()->route('admin.prescriptions.index')
            ->with('success', 'Prescription updated successfully! Notifications have been sent.');
    }

    /**
     * Remove the specified prescription.
     */
    public function destroy(Prescription $prescription): RedirectResponse
    {
        $patientName = $prescription->patient->full_name;
        $prescription->delete();

        return redirect()->route('admin.prescriptions.index')
            ->with('success', "Prescription for {$patientName} deleted successfully!");
    }

    /**
     * Update prescription status.
     */
    public function updateStatus(Request $request, Prescription $prescription)
    {
        $request->validate([
            'status' => 'required|in:active,completed,cancelled'
        ]);

        $prescription->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Prescription status updated successfully!',
            'status' => $prescription->status
        ]);
    }

    /**
     * Show the import form for prescriptions
     */
    public function showImport(): View
    {
        return view('admin.prescriptions.import');
    }

    /**
     * Import prescriptions from CSV
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
                'medical record id' => 'medical_record_id',
                'prescription number' => 'prescription_number',
                'prescription date' => 'prescription_date',
                'prescribed date' => 'prescribed_date',
                'expiry date' => 'expiry_date',
                'follow up date' => 'follow_up_date',
                'prescription type' => 'prescription_type',
                'status' => 'status',
                'diagnosis' => 'diagnosis',
                'notes' => 'notes',
                'refills allowed' => 'refills_allowed',
                'medications' => 'medications',
                'medication name' => 'medication_name',
                'medication dosage' => 'medication_dosage',
                'medication frequency' => 'medication_frequency',
                'medication duration' => 'medication_duration',
                'medication form' => 'medication_form',
                'medication instructions' => 'medication_instructions',
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

                    if (empty($data['prescription_date'])) {
                        throw new \Exception("Row {$rowNumber}: Missing required field (prescription_date)");
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

                    // Find medical record if provided
                    if (!empty($data['medical_record_id'])) {
                        $medicalRecord = MedicalRecord::where('id', $data['medical_record_id'])->first();
                        if (!$medicalRecord) {
                            throw new \Exception("Row {$rowNumber}: Medical record not found");
                        }
                    }

                    // Handle prescription_date - enforce MM/DD/YYYY format
                    if (!empty($data['prescription_date'])) {
                        try {
                            $data['prescription_date'] = parseImportDate($data['prescription_date']);
                        } catch (\Exception $e) {
                            throw new \Exception("Row {$rowNumber}: Prescription Date - " . $e->getMessage());
                        }
                    }

                    // Handle prescribed_date - enforce MM/DD/YYYY format
                    if (!empty($data['prescribed_date'])) {
                        try {
                            $data['prescribed_date'] = parseImportDate($data['prescribed_date']);
                        } catch (\Exception $e) {
                            throw new \Exception("Row {$rowNumber}: Prescribed Date - " . $e->getMessage());
                        }
                    }

                    // Handle expiry_date - enforce MM/DD/YYYY format
                    if (!empty($data['expiry_date'])) {
                        try {
                            $data['expiry_date'] = parseImportDate($data['expiry_date']);
                        } catch (\Exception $e) {
                            throw new \Exception("Row {$rowNumber}: Expiry Date - " . $e->getMessage());
                        }
                    }

                    // Handle follow_up_date - enforce MM/DD/YYYY format
                    if (!empty($data['follow_up_date'])) {
                        try {
                            $data['follow_up_date'] = parseImportDate($data['follow_up_date']);
                        } catch (\Exception $e) {
                            throw new \Exception("Row {$rowNumber}: Follow Up Date - " . $e->getMessage());
                        }
                    }

                    // Handle prescription_type validation
                    $validPrescriptionTypes = ['new', 'refill', 'modification', 'emergency'];
                    if (!empty($data['prescription_type'])) {
                        if (!in_array(strtolower($data['prescription_type']), $validPrescriptionTypes)) {
                            throw new \Exception("Row {$rowNumber}: Invalid prescription_type. Must be one of: " . implode(', ', $validPrescriptionTypes));
                        }
                        $data['prescription_type'] = strtolower($data['prescription_type']);
                    } else {
                        $data['prescription_type'] = 'new';
                    }

                    // Handle status validation
                    $validStatuses = ['pending', 'approved', 'dispensed', 'cancelled'];
                    if (!empty($data['status'])) {
                        if (!in_array(strtolower($data['status']), $validStatuses)) {
                            throw new \Exception("Row {$rowNumber}: Invalid status. Must be one of: " . implode(', ', $validStatuses));
                        }
                        $data['status'] = strtolower($data['status']);
                    } else {
                        $data['status'] = 'pending';
                    }

                    // Handle medications
                    $medications = [];
                    
                    // If medications column exists (JSON or comma-separated)
                    if (!empty($data['medications'])) {
                        // Try to decode as JSON first
                        $decoded = json_decode($data['medications'], true);
                        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                            $medications = $decoded;
                        } else {
                            // Try comma-separated format
                            $meds = explode(',', $data['medications']);
                            foreach ($meds as $med) {
                                $parts = explode('|', trim($med));
                                if (count($parts) >= 2) {
                                    $medications[] = [
                                        'name' => $parts[0],
                                        'dosage' => $parts[1] ?? '',
                                        'frequency' => $parts[2] ?? '',
                                        'duration' => $parts[3] ?? '',
                                        'form' => $parts[4] ?? '',
                                        'instructions' => $parts[5] ?? '',
                                    ];
                                }
                            }
                        }
                    }
                    
                    // If individual medication columns exist (for single medication per row)
                    if (!empty($data['medication_name'])) {
                        $medications[] = [
                            'name' => $data['medication_name'],
                            'dosage' => $data['medication_dosage'] ?? '',
                            'frequency' => $data['medication_frequency'] ?? '',
                            'duration' => $data['medication_duration'] ?? '',
                            'form' => $data['medication_form'] ?? '',
                            'instructions' => $data['medication_instructions'] ?? '',
                        ];
                    }
                    
                    // Validate medications
                    if (empty($medications)) {
                        throw new \Exception("Row {$rowNumber}: At least one medication is required");
                    }
                    
                    $data['medications'] = $medications;
                    
                    // Remove medication keys from data
                    unset(
                        $data['medication_name'],
                        $data['medication_dosage'],
                        $data['medication_frequency'],
                        $data['medication_duration'],
                        $data['medication_form'],
                        $data['medication_instructions']
                    );

                    // Handle prescription_number (auto-generate if not provided)
                    if (empty($data['prescription_number'])) {
                        $data['prescription_number'] = 'PRES' . str_pad(Prescription::max('id') + 1, 6, '0', STR_PAD_LEFT);
                    }

                    // Handle refills_allowed
                    if (isset($data['refills_allowed'])) {
                        $data['refills_allowed'] = (int)$data['refills_allowed'];
                    } else {
                        $data['refills_allowed'] = 0;
                    }

                    // Set created_by
                    $data['created_by'] = Auth::id();

                    // Find existing prescription (if updating)
                    $existingPrescription = null;
                    if ($importMode !== 'insert') {
                        // Try to find by prescription_number or patient, doctor, and prescription_date
                        if (!empty($data['prescription_number'])) {
                            $existingPrescription = Prescription::where('prescription_number', $data['prescription_number'])->first();
                        }
                        if (!$existingPrescription) {
                            $existingPrescription = Prescription::where('patient_id', $data['patient_id'])
                                ->where('doctor_id', $data['doctor_id'])
                                ->whereDate('prescription_date', $data['prescription_date'])
                                ->first();
                        }
                    }

                    // Handle import modes
                    if ($existingPrescription) {
                        if ($importMode === 'insert' || $importMode === 'skip') {
                            $stats['skipped']++;
                            continue;
                        }
                        
                        if ($importMode === 'update' || $importMode === 'upsert') {
                            $data['updated_by'] = Auth::id();
                            $existingPrescription->update($data);
                            $stats['updated']++;
                        }
                    } else {
                        if ($importMode === 'update') {
                            $stats['skipped']++;
                            continue;
                        }
                        
                        // Create new
                        $prescription = Prescription::create($data);
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

            return redirect()->route('admin.prescriptions.index')
                ->with('success', $message)
                ->with('import_stats', $stats);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Prescriptions CSV Import Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to import prescriptions: ' . $e->getMessage())
                ->withInput();
        }
    }
}
