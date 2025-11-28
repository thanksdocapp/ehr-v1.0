<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LabReport;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Services\HospitalEmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LabReportsController extends Controller
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
     * Display a listing of lab reports.
     */
    public function index(Request $request): View
    {
        $query = LabReport::with(['patient', 'doctor', 'appointment', 'medicalRecord']);

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

        // Filter by test type
        if ($request->filled('test_type')) {
            $query->where('test_type', $request->test_type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $dateFrom = parseDateInput($request->date_from);
            $query->where('test_date', '>=', $dateFrom);
        }

        if ($request->filled('date_to')) {
            $dateTo = parseDateInput($request->date_to);
            $query->where('test_date', '<=', $dateTo);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('test_name', 'like', "%{$search}%")
                  ->orWhere('test_type', 'like', "%{$search}%")
                  ->orWhere('results', 'like', "%{$search}%")
                  ->orWhereHas('patient', function ($patientQuery) use ($search) {
                      $patientQuery->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $labReports = $query->orderBy('test_date', 'desc')->paginate(15);

        // Filter patients by department
        $patientsQuery = Patient::query();
        if ($departmentId = $this->getUserDepartmentId()) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        $doctors = Doctor::orderBy('first_name')->get();

        // Get unique test types for filter
        $testTypes = LabReport::distinct()->pluck('test_type')->filter()->sort();

        $stats = [
            'total' => LabReport::count(),
            'pending' => LabReport::where('status', 'pending')->count(),
            'completed' => LabReport::where('status', 'completed')->count(),
            'this_month' => LabReport::whereMonth('test_date', now()->month)
                ->whereYear('test_date', now()->year)->count(),
        ];

        return view('admin.lab-reports.index', compact('labReports', 'patients', 'doctors', 'testTypes', 'stats'));
    }

    /**
     * Show the form for creating a new lab report.
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

        return view('admin.lab-reports.create', compact('patients', 'doctors', 'selectedAppointment', 'selectedMedicalRecord'));
    }

    /**
     * Store a newly created lab report.
     */
    public function store(Request $request, HospitalEmailNotificationService $emailService): RedirectResponse
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'test_name' => 'required|string|max:255',
            'test_type' => 'required|string|max:100',
            'test_date' => 'required|date',
            'results' => 'nullable|string',
            'reference_range' => 'nullable|string',
            'interpretation' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:normal,urgent,stat',
            'lab_technician' => 'nullable|string|max:255',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        $data = $request->all();
        
        // Generate unique report number
        $data['report_number'] = LabReport::generateReportNumber();

        // Handle file upload
        if ($request->hasFile('file_path')) {
            $file = $request->file('file_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('lab-reports', $filename, 'public');
            $data['file_path'] = $path;
        }

        $labReport = LabReport::create($data);
        $labReport->load(['patient', 'doctor']);
        
        // Send notification to patient if test results are completed
        if ($labReport->status === 'completed' && config('hospital.notifications.test_results.enabled', true)) {
            try {
                $labResult = [
                    'test_name' => $labReport->test_name,
                    'test_date' => $labReport->test_date->format('F d, Y'),
                    'doctor_name' => $labReport->doctor ? $labReport->doctor->name : 'Your Doctor',
                ];
                $emailService->sendTestResultsReady($labResult, $labReport->patient);
            } catch (\Exception $e) {
                \Log::error('Failed to send test results notification', [
                    'lab_report_id' => $labReport->id,
                    'patient_id' => $labReport->patient_id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        // Send notification to doctor for urgent/stat priority results
        if (in_array($labReport->priority, ['urgent', 'stat']) && $labReport->status === 'completed') {
            try {
                if ($labReport->doctor && config('hospital.staff_notifications.critical_results.enabled', true)) {
                    $emailService->notifyDoctorCriticalResults($labReport->doctor, $labReport);
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send critical results notification to doctor', [
                    'lab_report_id' => $labReport->id,
                    'doctor_id' => $labReport->doctor_id,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return redirect()->route('admin.lab-reports.index')
            ->with('success', 'Lab report created successfully! Notifications have been sent.');
    }

    /**
     * Display the specified lab report.
     */
    public function show(LabReport $labReport): View
    {
        $labReport->load(['patient', 'doctor', 'appointment', 'medicalRecord']);
        
        return view('admin.lab-reports.show', compact('labReport'));
    }

    /**
     * Show the form for editing the lab report.
     */
    public function edit(LabReport $labReport): View
    {
        // Filter patients by department
        $patientsQuery = Patient::query();
        if ($departmentId = $this->getUserDepartmentId()) {
            $patientsQuery->byDepartment($departmentId);
        }
        $patients = $patientsQuery->orderBy('first_name')->get();
        $doctors = Doctor::orderBy('first_name')->get();
        
        return view('admin.lab-reports.edit', compact('labReport', 'patients', 'doctors'));
    }

    /**
     * Update the specified lab report.
     */
    public function update(Request $request, LabReport $labReport, HospitalEmailNotificationService $emailService): RedirectResponse
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'test_name' => 'required|string|max:255',
            'test_type' => 'required|string|max:100',
            'test_date' => 'required|date',
            'results' => 'nullable|string',
            'reference_range' => 'nullable|string',
            'interpretation' => 'nullable|string',
            'notes' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'priority' => 'required|in:normal,urgent,stat',
            'lab_technician' => 'nullable|string|max:255',
            'file_path' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:10240', // 10MB max
        ]);

        $data = $request->all();
        $oldStatus = $labReport->status;

        // Handle file upload
        if ($request->hasFile('file_path')) {
            // Delete old file if exists
            if ($labReport->file_path && Storage::disk('public')->exists($labReport->file_path)) {
                Storage::disk('public')->delete($labReport->file_path);
            }
            
            $file = $request->file('file_path');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('lab-reports', $filename, 'public');
            $data['file_path'] = $path;
        }

        $labReport->update($data);
        $labReport->load(['patient', 'doctor']);
        
        // Send notifications if status changed to completed
        if ($oldStatus !== 'completed' && $labReport->status === 'completed') {
            // Send notification to patient
            if (config('hospital.notifications.test_results.enabled', true)) {
                try {
                    $labResult = [
                    'test_name' => $labReport->test_name,
                    'test_date' => $labReport->test_date->format('F d, Y'),
                    'doctor_name' => $labReport->doctor ? $labReport->doctor->name : 'Your Doctor',
                ];
                $emailService->sendTestResultsReady($labResult, $labReport->patient);
                } catch (\Exception $e) {
                    \Log::error('Failed to send test results notification', [
                        'lab_report_id' => $labReport->id,
                        'patient_id' => $labReport->patient_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
            
            // Send notification to doctor for urgent/stat priority results
            if (in_array($labReport->priority, ['urgent', 'stat'])) {
                try {
                    if ($labReport->doctor && config('hospital.staff_notifications.critical_results.enabled', true)) {
                        $emailService->notifyDoctorCriticalResults($labReport->doctor, $labReport);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send critical results notification to doctor', [
                        'lab_report_id' => $labReport->id,
                        'doctor_id' => $labReport->doctor_id,
                        'error' => $e->getMessage()
                    ]);
                }
            }
        }

        return redirect()->route('admin.lab-reports.index')
            ->with('success', 'Lab report updated successfully! Notifications have been sent.');
    }

    /**
     * Remove the specified lab report.
     */
    public function destroy(LabReport $labReport): RedirectResponse
    {
        $patientName = $labReport->patient->full_name;
        
        // Delete associated file if exists
        if ($labReport->file_path && Storage::disk('public')->exists($labReport->file_path)) {
            Storage::disk('public')->delete($labReport->file_path);
        }
        
        $labReport->delete();

        return redirect()->route('admin.lab-reports.index')
            ->with('success', "Lab report for {$patientName} deleted successfully!");
    }

    /**
     * Update lab report status.
     */
    public function updateStatus(Request $request, LabReport $labReport)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled'
        ]);

        $labReport->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Lab report status updated successfully!',
            'status' => $labReport->status
        ]);
    }

    /**
     * Download lab report file.
     */
    public function download(LabReport $labReport)
    {
        if (!$labReport->file_path || !Storage::disk('public')->exists($labReport->file_path)) {
            return redirect()->back()->with('error', 'Report file not found.');
        }

        return Storage::disk('public')->download(
            $labReport->file_path,
            $labReport->test_name . '_' . $labReport->patient->full_name . '.pdf'
        );
    }

    /**
     * Show CSV import form
     */
    public function showImport(): View
    {
        return view('admin.lab-reports.import');
    }

    /**
     * Import lab reports from CSV
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
                'report number' => 'report_number',
                'test type' => 'test_type',
                'test category' => 'test_category',
                'test name' => 'test_name',
                'test date' => 'test_date',
                'specimen type' => 'specimen_type',
                'collection date' => 'collection_date',
                'report date' => 'report_date',
                'results' => 'results',
                'normal range' => 'normal_range',
                'reference range' => 'reference_range',
                'reference values' => 'reference_values',
                'interpretation' => 'interpretation',
                'status' => 'status',
                'priority' => 'priority',
                'lab technician' => 'lab_technician',
                'technician name' => 'technician_name',
                'technician notes' => 'technician_notes',
                'notes' => 'notes',
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

                    if (empty($data['test_name'])) {
                        throw new \Exception("Row {$rowNumber}: Missing required field (test_name)");
                    }

                    if (empty($data['test_type'])) {
                        throw new \Exception("Row {$rowNumber}: Missing required field (test_type)");
                    }

                    if (empty($data['test_date'])) {
                        throw new \Exception("Row {$rowNumber}: Missing required field (test_date)");
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

                    // Handle test_date - enforce MM/DD/YYYY format
                    if (!empty($data['test_date'])) {
                        try {
                            $data['test_date'] = parseImportDate($data['test_date']);
                        } catch (\Exception $e) {
                            throw new \Exception("Row {$rowNumber}: Test Date - " . $e->getMessage());
                        }
                    }

                    // Handle collection_date - enforce MM/DD/YYYY format
                    if (!empty($data['collection_date'])) {
                        try {
                            $data['collection_date'] = parseImportDate($data['collection_date']);
                        } catch (\Exception $e) {
                            throw new \Exception("Row {$rowNumber}: Collection Date - " . $e->getMessage());
                        }
                    }

                    // Handle report_date - enforce MM/DD/YYYY format
                    if (!empty($data['report_date'])) {
                        try {
                            $data['report_date'] = parseImportDate($data['report_date']);
                        } catch (\Exception $e) {
                            throw new \Exception("Row {$rowNumber}: Report Date - " . $e->getMessage());
                        }
                    }

                    // Handle status validation
                    $validStatuses = ['pending', 'in_progress', 'completed', 'cancelled'];
                    if (!empty($data['status'])) {
                        if (!in_array(strtolower($data['status']), $validStatuses)) {
                            throw new \Exception("Row {$rowNumber}: Invalid status. Must be one of: " . implode(', ', $validStatuses));
                        }
                        $data['status'] = strtolower($data['status']);
                    } else {
                        $data['status'] = 'pending';
                    }

                    // Handle priority validation
                    $validPriorities = ['normal', 'urgent', 'stat'];
                    if (!empty($data['priority'])) {
                        if (!in_array(strtolower($data['priority']), $validPriorities)) {
                            throw new \Exception("Row {$rowNumber}: Invalid priority. Must be one of: " . implode(', ', $validPriorities));
                        }
                        $data['priority'] = strtolower($data['priority']);
                    } else {
                        $data['priority'] = 'normal';
                    }

                    // Generate report number if not provided
                    if (empty($data['report_number'])) {
                        $data['report_number'] = LabReport::generateReportNumber();
                    }

                    // Get fillable fields only
                    $fillableFields = (new LabReport)->getFillable();
                    $dataToSave = array_intersect_key($data, array_flip($fillableFields));

                    // Handle import mode
                    $existingReport = null;
                    if (!empty($data['report_number'])) {
                        $existingReport = LabReport::where('report_number', $data['report_number'])->first();
                    }

                    if ($importMode === 'insert') {
                        if ($existingReport) {
                            if ($skipErrors) {
                                $stats['skipped']++;
                                continue;
                            }
                            throw new \Exception("Row {$rowNumber}: Report number already exists");
                        }
                        LabReport::create($dataToSave);
                        $stats['created']++;
                    } elseif ($importMode === 'update') {
                        if (!$existingReport) {
                            if ($skipErrors) {
                                $stats['skipped']++;
                                continue;
                            }
                            throw new \Exception("Row {$rowNumber}: Report number not found for update");
                        }
                        $existingReport->update($dataToSave);
                        $stats['updated']++;
                    } elseif ($importMode === 'upsert') {
                        if ($existingReport) {
                            $existingReport->update($dataToSave);
                            $stats['updated']++;
                        } else {
                            LabReport::create($dataToSave);
                            $stats['created']++;
                        }
                    } elseif ($importMode === 'skip') {
                        if ($existingReport) {
                            $stats['skipped']++;
                            continue;
                        }
                        LabReport::create($dataToSave);
                        $stats['created']++;
                    }

                } catch (\Exception $e) {
                    if ($skipErrors) {
                        $stats['errors'][] = $e->getMessage();
                        $stats['skipped']++;
                        continue;
                    }
                    DB::rollBack();
                    fclose($handle);
                    return redirect()->back()->with('error', $e->getMessage())->with('import_stats', $stats);
                }
            }

            DB::commit();
            fclose($handle);

            return redirect()->route('admin.lab-reports.index')
                ->with('success', 'Lab reports imported successfully!')
                ->with('import_stats', $stats);

        } catch (\Exception $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            \Log::error('Lab Reports CSV Import Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
}
