<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use App\Models\Patient;
use App\Models\MedicalRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PrescriptionsController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Build query based on user role - use visibility scope
        $query = Prescription::with(['patient', 'doctor', 'medicalRecord']);
        
        // Apply visibility rules based on user role (uses patient-department-doctor logic)
        // Pharmacists see all, others filtered by visibility
        if ($user->role !== 'pharmacist') {
            $query->visibleTo($user);
        }
        
        $prescriptions = $query->latest()->paginate(15);
        
        return view('staff.prescriptions.index', compact('prescriptions'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Only doctors and pharmacists can create prescriptions
        if (!in_array($user->role, ['doctor', 'pharmacist'])) {
            return redirect()->route('staff.prescriptions.index')
                ->with('error', 'You do not have permission to create prescriptions.');
        }
        
        // Get selected patient_id and medical_record_id from query parameters
        $selectedPatientId = $request->get('patient_id');
        $selectedMedicalRecordId = $request->get('medical_record_id');
        
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
        
        // Get selected medical record for pre-filling
        $selectedMedicalRecord = null;
        if ($selectedMedicalRecordId) {
            $selectedMedicalRecord = MedicalRecord::with(['patient', 'appointment', 'doctor'])
                ->find($selectedMedicalRecordId);
            if ($selectedMedicalRecord && !$selectedPatientId) {
                $selectedPatientId = $selectedMedicalRecord->patient_id;
            }
        }
        
        $medicalRecordsQuery = MedicalRecord::with(['patient', 'appointment', 'doctor']);
        // Apply visibility rules for medical records
        $medicalRecordsQuery->visibleTo($user);
        
        // If patient_id is provided, filter medical records for that patient
        if ($selectedPatientId) {
            $medicalRecordsQuery->where('patient_id', $selectedPatientId);
        }
        
        $medicalRecords = $medicalRecordsQuery->latest()->get();
        
        return view('staff.prescriptions.create', compact(
            'patients', 
            'medicalRecords', 
            'selectedPatientId', 
            'selectedMedicalRecordId',
            'selectedMedicalRecord'
        ));
    }

    public function store(Request $request)
    {
        // Check if patient is a guest
        $patient = Patient::find($request->patient_id);
        if ($patient && $patient->is_guest) {
            return redirect()->back()
                ->with('error', 'Cannot create prescriptions for guest patients. Please convert the patient to a full patient first.')
                ->withInput();
        }
        $user = Auth::user();
        
        // Only doctors and pharmacists can create prescriptions
        if (!in_array($user->role, ['doctor', 'pharmacist'])) {
            return redirect()->route('staff.prescriptions.index')
                ->with('error', 'You do not have permission to create prescriptions.');
        }
        
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id' => 'nullable|exists:doctors,id',
            'medical_record_id' => 'nullable|exists:medical_records,id',
            'prescription_date' => 'required|date',
            'prescription_type' => 'required|string|in:new,refill,modification,emergency',
            'medications' => 'required|array|min:1',
            'medications.*.name' => 'required|string|max:255',
            'medications.*.dosage' => 'required|string|max:100',
            'medications.*.frequency' => 'required|string|max:100',
            'medications.*.duration' => 'required|string|max:100',
            'medications.*.form' => 'nullable|string|max:50',
            'medications.*.instructions' => 'nullable|string|max:500',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
            'follow_up_date' => 'nullable|date|after:today',
            'refills_allowed' => 'nullable|integer|min:0|max:6',
            'status' => 'nullable|string|in:pending,approved,dispensed',
        ]);
        
        // Get the doctor ID from the doctors table if user is a doctor
        $doctorId = null;
        if ($user->role === 'doctor') {
            $doctor = \DB::table('doctors')->where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : null;
        } elseif ($request->doctor_id) {
            // If a specific doctor is selected, use that doctor ID
            $doctorId = $request->doctor_id;
        }
        
        $prescription = Prescription::create([
            'patient_id' => $request->patient_id,
            'doctor_id' => $doctorId,
            'medical_record_id' => $request->medical_record_id,
            'prescription_number' => 'RX-' . str_pad(Prescription::count() + 1, 6, '0', STR_PAD_LEFT),
            'medications' => $request->medications, // Laravel will auto-cast to JSON
            'prescription_type' => $request->prescription_type,
            'prescription_date' => $request->prescription_date,
            'prescribed_date' => $request->prescription_date, // Set prescribed_date to the same as prescription_date
            'diagnosis' => $request->diagnosis,
            'notes' => $request->notes,
            'follow_up_date' => $request->follow_up_date,
            'refills_allowed' => $request->refills_allowed ?? 0,
            'status' => $request->status ?? 'pending',
            'created_by' => $user->id,
        ]);
        
        // Send notification to patient about new prescription
        try {
            app(\App\Services\NotificationService::class)->sendPrescriptionNotification($prescription, 'created');
        } catch (\Exception $e) {
            \Log::error('Failed to send prescription notification', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage()
            ]);
        }
        
        return redirect()->route('staff.prescriptions.show', $prescription)
            ->with('success', 'Prescription created successfully.');
    }

    public function show(Prescription $prescription)
    {
        $user = Auth::user();
        
        // Check if user can view this prescription
        if ($user->role === 'doctor') {
            $doctor = \DB::table('doctors')->where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : null;
            if ($prescription->doctor_id !== $doctorId) {
                return redirect()->route('staff.prescriptions.index')
                    ->with('error', 'You can only view your own prescriptions.');
            }
        } elseif (!in_array($user->role, ['doctor', 'pharmacist']) && 
                  $prescription->created_by !== $user->id) {
            return redirect()->route('staff.prescriptions.index')
                ->with('error', 'You do not have permission to view this prescription.');
        }
        
        $prescription->load(['patient', 'doctor', 'medicalRecord']);
        
        return view('staff.prescriptions.show', compact('prescription'));
    }

    public function edit(Prescription $prescription)
    {
        $user = Auth::user();
        
        // Only doctors can edit prescriptions, and only their own
        // Pharmacists can edit status but not medications
        if ($user->role === 'doctor') {
            $doctor = \DB::table('doctors')->where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : null;
            if ($prescription->doctor_id !== $doctorId) {
                return redirect()->route('staff.prescriptions.index')
                    ->with('error', 'You can only edit your own prescriptions.');
            }
        } elseif (!in_array($user->role, ['doctor', 'pharmacist'])) {
            return redirect()->route('staff.prescriptions.index')
                ->with('error', 'You do not have permission to edit prescriptions.');
        }
        
        // Check if prescription can be edited
        if (in_array($prescription->status, ['dispensed', 'cancelled'])) {
            return redirect()->route('staff.prescriptions.show', $prescription)
                ->with('error', 'This prescription cannot be edited as it has been ' . $prescription->status . '.');
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
        $medicalRecords = MedicalRecord::with(['patient', 'appointment'])
            ->where(function($q) use ($prescription) {
                $q->whereDoesntHave('prescriptions')
                  ->orWhere('id', $prescription->medical_record_id);
            })
            ->latest()
            ->get();
        
        return view('staff.prescriptions.edit', compact('prescription', 'patients', 'medicalRecords'));
    }

    public function update(Request $request, Prescription $prescription)
    {
        // Check if patient is a guest
        if ($prescription->patient && $prescription->patient->is_guest) {
            return redirect()->back()
                ->with('error', 'Cannot edit prescriptions for guest patients. Please convert the patient to a full patient first.')
                ->withInput();
        }
        $user = Auth::user();
        
        // Role-based permission checks
        if ($user->role === 'doctor') {
            $doctor = \DB::table('doctors')->where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : null;
            if ($prescription->doctor_id !== $doctorId) {
                return redirect()->route('staff.prescriptions.index')
                    ->with('error', 'You can only edit your own prescriptions.');
            }
        } elseif (!in_array($user->role, ['doctor', 'pharmacist'])) {
            return redirect()->route('staff.prescriptions.index')
                ->with('error', 'You do not have permission to edit prescriptions.');
        }
        
        // Check if prescription can be edited
        if (in_array($prescription->status, ['dispensed', 'cancelled'])) {
            return redirect()->route('staff.prescriptions.show', $prescription)
                ->with('error', 'This prescription cannot be edited as it has been ' . $prescription->status . '.');
        }
        
        if ($user->role === 'pharmacist' && $request->has('status_only')) {
            // Pharmacist updating status only
            $request->validate([
                'status' => 'required|in:pending,approved,dispensed,cancelled',
                'pharmacist_notes' => 'nullable|string',
            ]);
            
            $prescription->update([
                'status' => $request->status,
                'pharmacist_notes' => $request->pharmacist_notes,
                'pharmacist_id' => $user->id,
                'dispensed_at' => $request->status === 'dispensed' ? now() : null,
                'updated_by' => $user->id,
            ]);
            
            return redirect()->route('staff.prescriptions.show', $prescription)
                ->with('success', 'Prescription status updated successfully.');
        } else {
            // Doctor updating full prescription
            $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'medical_record_id' => 'nullable|exists:medical_records,id',
                'medications' => 'required|array|min:1',
                'medications.*.name' => 'required|string|max:255',
                'medications.*.dosage' => 'required|string|max:100',
                'medications.*.frequency' => 'required|string|max:100',
                'medications.*.duration' => 'required|string|max:100',
                'medications.*.instructions' => 'nullable|string|max:500',
                'notes' => 'nullable|string',
                'follow_up_date' => 'nullable|date|after:today',
            ]);
            
            $prescription->update([
                'patient_id' => $request->patient_id,
                'medical_record_id' => $request->medical_record_id,
                'medications' => json_encode($request->medications),
                'notes' => $request->notes,
                'follow_up_date' => $request->follow_up_date,
                'updated_by' => $user->id,
            ]);
            
            return redirect()->route('staff.prescriptions.show', $prescription)
                ->with('success', 'Prescription updated successfully.');
        }
    }

    public function updateStatus(Request $request, Prescription $prescription)
    {
        $user = Auth::user();
        
        $request->validate([
            'status' => 'required|in:pending,approved,dispensed,cancelled',
            'notes' => 'nullable|string',
        ]);
        
        // Role-based authorization for different status updates
        $requestedStatus = $request->status;
        
        if ($requestedStatus === 'approved') {
            // Only doctors can approve prescriptions, and only their own
            if ($user->role !== 'doctor') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only doctors can approve prescriptions.'
                ], 403);
            }
            
            $doctor = \DB::table('doctors')->where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : null;
            
            if ($prescription->doctor_id !== $doctorId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only approve your own prescriptions.'
                ], 403);
            }
            
            if ($prescription->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pending prescriptions can be approved.'
                ], 400);
            }
        } elseif ($requestedStatus === 'dispensed') {
            // Only pharmacists can dispense prescriptions
            if ($user->role !== 'pharmacist') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only pharmacists can mark prescriptions as dispensed.'
                ], 403);
            }
            
            if ($prescription->status !== 'approved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only approved prescriptions can be dispensed.'
                ], 400);
            }
        } elseif ($requestedStatus === 'cancelled') {
            // Doctors can cancel their own prescriptions, pharmacists can cancel any non-dispensed prescription
            if ($user->role === 'doctor') {
                $doctor = \DB::table('doctors')->where('user_id', $user->id)->first();
                $doctorId = $doctor ? $doctor->id : null;
                
                if ($prescription->doctor_id !== $doctorId) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only cancel your own prescriptions.'
                    ], 403);
                }
            } elseif ($user->role !== 'pharmacist') {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to cancel prescriptions.'
                ], 403);
            }
            
            if (in_array($prescription->status, ['dispensed', 'cancelled'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'This prescription cannot be cancelled as it has already been ' . $prescription->status . '.'
                ], 400);
            }
        } else {
            // For other status updates, only pharmacists are allowed
            if ($user->role !== 'pharmacist') {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to update prescription status.'
                ], 403);
            }
        }
        
        // Prepare update data based on status and user role
        $updateData = [
            'status' => $requestedStatus,
            'updated_by' => $user->id,
        ];
        
        if ($user->role === 'pharmacist') {
            $updateData['pharmacist_notes'] = $request->notes;
            $updateData['pharmacist_id'] = $user->id;
            
            if ($requestedStatus === 'dispensed') {
                $updateData['dispensed_at'] = now();
            }
        } elseif ($user->role === 'doctor' && $requestedStatus === 'approved') {
            // Set approved date for doctor approvals
            $updateData['approved_at'] = now();
        }
        
        $prescription->update($updateData);
        
        return response()->json([
            'success' => true,
            'message' => 'Prescription status updated successfully.',
            'prescription' => $prescription->fresh(),
        ]);
    }

    public function print(Prescription $prescription)
    {
        $user = Auth::user();
        
        // Check if user can view this prescription (same permissions as show method)
        if ($user->role === 'doctor') {
            $doctor = \DB::table('doctors')->where('user_id', $user->id)->first();
            $doctorId = $doctor ? $doctor->id : null;
            if ($prescription->doctor_id !== $doctorId) {
                return redirect()->route('staff.prescriptions.index')
                    ->with('error', 'You can only print your own prescriptions.');
            }
        } elseif (!in_array($user->role, ['doctor', 'pharmacist']) && 
                  $prescription->created_by !== $user->id) {
            return redirect()->route('staff.prescriptions.index')
                ->with('error', 'You do not have permission to print this prescription.');
        }
        
        $prescription->load(['patient', 'doctor', 'medicalRecord']);
        
        return view('staff.prescriptions.print', compact('prescription'));
    }
}
