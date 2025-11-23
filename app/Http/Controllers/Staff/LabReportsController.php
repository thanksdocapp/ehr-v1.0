<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\LabReport;
use App\Models\Patient;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LabReportsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Build query based on user role
        $query = LabReport::with(['patient', 'doctor', 'medicalRecord']);
        
        // Role-based filtering
        if ($user->role === 'doctor') {
            // Get the doctor ID from the doctors table
            $doctor = Doctor::where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : null;
            
            if ($doctorId) {
                // Doctors can see all lab reports they ordered or are related to their patients
                $query->where(function($q) use ($doctorId) {
                    $q->where('doctor_id', $doctorId)
                      ->orWhereHas('medicalRecord', function($subQuery) use ($doctorId) {
                          $subQuery->where('doctor_id', $doctorId);
                      });
                });
            } else {
                // If no doctor record found, show only reports they created
                $query->where('created_by', $user->id);
            }
        } elseif ($user->role === 'technician') {
            // Technicians can see all lab reports
            // No additional filtering needed
        } else {
            // Other staff can see lab reports they created or are involved with
            $query->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('medicalRecord', function($subQuery) use ($user) {
                      $subQuery->where('created_by', $user->id);
                  });
            });
        }
        
        $labReports = $query->latest()->paginate(15);
        
        return view('staff.lab-reports.index', compact('labReports'));
    }

    public function create()
    {
        $user = Auth::user();
        
        // Filter patients by department for all roles
        $query = Patient::active()->visibleTo(Auth::user());
        $departmentId = null;
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            $departmentId = $doctor ? $doctor->department_id : null;
        } else {
            $departmentId = $user->department_id;
        }
        if ($departmentId) {
            $query->byDepartment($departmentId);
        }
        $patients = $query->orderBy('first_name')->get();
        
        // Get medical records visible to the user
        $medicalRecordsQuery = MedicalRecord::with(['patient', 'appointment']);
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : null;
            if ($doctorId) {
                $medicalRecordsQuery->where('doctor_id', $doctorId);
            } else {
                $medicalRecordsQuery->where('created_by', $user->id);
            }
        } else {
            // For non-doctors, show records they created or are associated with their patients
            $medicalRecordsQuery->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('patient', function($patientQuery) use ($user) {
                      $patientQuery->visibleTo($user);
                  });
            });
        }
        $medicalRecords = $medicalRecordsQuery->latest()->get();
        
        // Get doctors list for non-doctor users who need to assign a doctor
        $doctors = null;
        if ($user->role !== 'doctor') {
            $doctorsQuery = Doctor::with('user')->where('status', 'active');
            if ($departmentId) {
                $doctorsQuery->where('department_id', $departmentId);
            }
            $doctors = $doctorsQuery->orderBy('first_name')->get();
        }
        
        return view('staff.lab-reports.create', compact('patients', 'medicalRecords', 'doctors'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'test_name' => 'required|string|max:255',
            'test_type' => 'required|string|max:100',
            'test_category' => 'required|string|max:100',
            'specimen_type' => 'required|string|max:100',
            'collection_date' => 'required|date',
            'report_date' => 'nullable|date|after_or_equal:collection_date',
            'results' => 'nullable|string',
            'normal_range' => 'nullable|string',
            'interpretation' => 'nullable|string',
            'technician_notes' => 'nullable|string',
            'report_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        
        $reportFile = null;
        if ($request->hasFile('report_file')) {
            $reportFile = $request->file('report_file')->store('lab-reports', 'public');
        }
        
        // Get the doctor ID - use provided doctor_id or get from user if they're a doctor
        $doctorId = $request->doctor_id;
        if ($user->role === 'doctor') {
            // If user is a doctor, use their doctor ID (override provided doctor_id)
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : $request->doctor_id;
        }
        
        // Determine status based on role
        $status = 'pending';
        $technicianId = null;
        if ($user->role === 'technician') {
            $status = 'completed';
            $technicianId = $user->id;
        }
        
        $labReport = LabReport::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $doctorId,
            'medical_record_id' => $request->medical_record_id,
            'report_number' => LabReport::generateReportNumber(),
            'test_name' => $request->test_name,
            'test_type' => $request->test_type,
            'test_category' => $request->test_category,
            'specimen_type' => $request->specimen_type,
            'test_date' => $request->collection_date, // Use collection_date as test_date
            'collection_date' => $request->collection_date,
            'report_date' => $request->report_date ?? now(),
            'results' => $request->results,
            'normal_range' => $request->normal_range,
            'interpretation' => $request->interpretation,
            'technician_notes' => $request->technician_notes,
            'report_file' => $reportFile,
            'status' => $status,
            'technician_id' => $technicianId,
            'created_by' => $user->id,
        ]);
        
        // Send notification if lab report is completed
        if ($labReport->status === 'completed') {
            try {
                app(\App\Services\NotificationService::class)->sendLabResultNotification($labReport, 'completed');
            } catch (\Exception $e) {
                \Log::error('Failed to send lab result notification', [
                    'lab_report_id' => $labReport->id,
                    'error' => $e->getMessage()
                ]);
            }
        }
        
        return redirect()->route('staff.lab-reports.show', $labReport)
            ->with('success', 'Lab report created successfully.');
    }

    public function show(LabReport $labReport)
    {
        $user = Auth::user();
        
        // Check if user can view this lab report
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : null;
            
            if ($labReport->doctor_id !== $doctorId && 
                (!$labReport->medicalRecord || $labReport->medicalRecord->doctor_id !== $doctorId)) {
                return redirect()->route('staff.lab-reports.index')
                    ->with('error', 'You can only view lab reports you ordered or related to your patients.');
            }
        } elseif (!in_array($user->role, ['doctor', 'technician']) && 
                  $labReport->created_by !== $user->id) {
            return redirect()->route('staff.lab-reports.index')
                ->with('error', 'You do not have permission to view this lab report.');
        }
        
        $labReport->load(['patient', 'doctor', 'medicalRecord', 'technician']);
        
        return view('staff.lab-reports.show', compact('labReport'));
    }

    public function edit(LabReport $labReport)
    {
        $user = Auth::user();
        
        $canEdit = false;
        $errorMessage = 'You do not have permission to edit this lab report.';
        
        if ($user->role === 'technician') {
            // Technicians can edit any report that's pending or in progress
            $canEdit = in_array($labReport->status, ['pending', 'in_progress']);
            if (!$canEdit) {
                $errorMessage = 'This lab report cannot be edited as it has been completed or cancelled.';
            }
        } elseif ($user->role === 'doctor') {
            // Doctors can only edit their own reports if still pending
            $doctor = Doctor::where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : null;
            
            if ($labReport->doctor_id === $doctorId && $labReport->status === 'pending') {
                $canEdit = true;
            } else {
                $errorMessage = 'You can only edit your own lab reports while they are still pending.';
            }
        } else {
            // Other users can edit reports they created if still pending
            if ($labReport->created_by === $user->id && $labReport->status === 'pending') {
                $canEdit = true;
            } else {
                $errorMessage = 'You can only edit lab reports you created while they are still pending.';
            }
        }
        
        if (!$canEdit) {
            return redirect()->route('staff.lab-reports.index')
                ->with('error', $errorMessage);
        }
        
        // Filter patients by department for all roles
        $query = Patient::active()->visibleTo(Auth::user());
        $departmentId = null;
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            $departmentId = $doctor ? $doctor->department_id : null;
        } else {
            $departmentId = $user->department_id;
        }
        if ($departmentId) {
            $query->byDepartment($departmentId);
        }
        $patients = $query->orderBy('first_name')->get();
        
        // Get medical records visible to the user
        $medicalRecordsQuery = MedicalRecord::with(['patient', 'appointment']);
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : null;
            if ($doctorId) {
                $medicalRecordsQuery->where('doctor_id', $doctorId);
            } else {
                $medicalRecordsQuery->where('created_by', $user->id);
            }
        } else {
            // For non-doctors, show records they created or are associated with their patients
            $medicalRecordsQuery->where(function($q) use ($user) {
                $q->where('created_by', $user->id)
                  ->orWhereHas('patient', function($patientQuery) use ($user) {
                      $patientQuery->visibleTo($user);
                  });
            });
        }
        $medicalRecords = $medicalRecordsQuery->where(function($q) use ($labReport) {
                $q->whereDoesntHave('labReports')
                  ->orWhere('id', $labReport->medical_record_id);
            })
            ->latest()
            ->get();
        
        // Get doctors list for non-doctor users who need to assign a doctor
        $doctors = null;
        if ($user->role !== 'doctor') {
            $doctorsQuery = Doctor::with('user')->where('status', 'active');
            if ($departmentId) {
                $doctorsQuery->where('department_id', $departmentId);
            }
            $doctors = $doctorsQuery->orderBy('first_name')->get();
        }
        
        return view('staff.lab-reports.edit', compact('labReport', 'patients', 'medicalRecords', 'doctors'));
    }

    public function update(Request $request, LabReport $labReport)
    {
        $user = Auth::user();
        
        // Check permissions
        $canUpdate = false;
        if ($user->role === 'technician') {
            $canUpdate = in_array($labReport->status, ['pending', 'in_progress']);
        } elseif ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : null;
            $canUpdate = ($labReport->doctor_id === $doctorId && $labReport->status === 'pending');
        } else {
            // Other users can update reports they created if still pending
            $canUpdate = ($labReport->created_by === $user->id && $labReport->status === 'pending');
        }
        
        if (!$canUpdate) {
            return redirect()->route('staff.lab-reports.index')
                ->with('error', 'You do not have permission to edit this lab report.');
        }
        
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'test_name' => 'required|string|max:255',
            'test_type' => 'required|string|max:100',
            'test_category' => 'required|string|max:100',
            'specimen_type' => 'required|string|max:100',
            'collection_date' => 'required|date',
            'report_date' => 'nullable|date|after_or_equal:collection_date',
            'results' => 'nullable|string',
            'normal_range' => 'nullable|string',
            'interpretation' => 'nullable|string',
            'technician_notes' => 'nullable|string',
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'report_file' => 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);
        
        $reportFile = $labReport->report_file;
        if ($request->hasFile('report_file')) {
            // Delete old file if exists
            if ($reportFile) {
                Storage::disk('public')->delete($reportFile);
            }
            $reportFile = $request->file('report_file')->store('lab-reports', 'public');
        }
        
        // Get the doctor ID - use provided doctor_id or keep existing if user is a doctor
        $doctorId = $labReport->doctor_id;
        if ($user->role === 'doctor') {
            // If user is a doctor, keep their doctor ID (override provided doctor_id)
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : $labReport->doctor_id;
        } elseif ($request->has('doctor_id')) {
            $doctorId = $request->doctor_id;
        }
        
        $labReport->update([
            'patient_id' => $request->patient_id,
            'doctor_id' => $doctorId,
            'medical_record_id' => $request->medical_record_id,
            'test_name' => $request->test_name,
            'test_type' => $request->test_type,
            'test_category' => $request->test_category,
            'specimen_type' => $request->specimen_type,
            'test_date' => $request->collection_date, // Use collection_date as test_date
            'collection_date' => $request->collection_date,
            'report_date' => $request->report_date ?? now(),
            'results' => $request->results,
            'normal_range' => $request->normal_range,
            'interpretation' => $request->interpretation,
            'technician_notes' => $request->technician_notes,
            'report_file' => $reportFile,
            'status' => $request->status,
            'technician_id' => $user->id,
            'updated_by' => $user->id,
        ]);
        
        return redirect()->route('staff.lab-reports.show', $labReport)
            ->with('success', 'Lab report updated successfully.');
    }

    public function updateStatus(Request $request, LabReport $labReport)
    {
        $user = Auth::user();
        
        // Only technicians can update lab report status
        if ($user->role !== 'technician') {
            return response()->json(['error' => 'Unauthorized'], 403);
        }
        
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
        ]);
        
        $labReport->update([
            'status' => $request->status,
            'technician_notes' => $request->notes,
            'technician_id' => $user->id,
            'report_date' => $request->status === 'completed' ? now() : $labReport->report_date,
            'updated_by' => $user->id,
        ]);
        
        return response()->json([
            'success' => true,
            'message' => 'Lab report status updated successfully.',
            'labReport' => $labReport->fresh(),
        ]);
    }

    public function download(LabReport $labReport)
    {
        $user = Auth::user();
        
        // Check if user can download this lab report
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : null;
            
            if ($labReport->doctor_id !== $doctorId && 
                (!$labReport->medicalRecord || $labReport->medicalRecord->doctor_id !== $doctorId)) {
                abort(403, 'You can only download lab reports you ordered or related to your patients.');
            }
        } elseif (!in_array($user->role, ['doctor', 'technician']) && 
                  $labReport->created_by !== $user->id) {
            abort(403, 'You do not have permission to download this lab report.');
        }
        
        if (!$labReport->report_file || !Storage::disk('public')->exists($labReport->report_file)) {
            return redirect()->back()->with('error', 'Report file not found.');
        }
        
        return Storage::disk('public')->download(
            $labReport->report_file,
            $labReport->report_number . '_' . $labReport->test_name . '.' . pathinfo($labReport->report_file, PATHINFO_EXTENSION)
        );
    }
}
