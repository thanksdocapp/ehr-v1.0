<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\LabReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MedicalRecordController extends Controller
{
    /**
     * Display a listing of the patient's medical records.
     */
    public function index(Request $request): View
    {
        $patient = Auth::guard('patient')->user();
        
        $query = MedicalRecord::where('patient_id', $patient->id)
            ->with(['doctor', 'appointment']);

        // Filter by record type
        if ($request->filled('type')) {
            $query->where('record_type', $request->type);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        // Search by diagnosis or symptoms
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('diagnosis', 'like', "%{$search}%")
                  ->orWhere('symptoms', 'like', "%{$search}%")
                  ->orWhere('treatment', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $records = $query->orderBy('created_at', 'desc')->paginate(10);

        // Get recent prescriptions
        $recentPrescriptions = Prescription::where('patient_id', $patient->id)
            ->with(['doctor', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get recent lab reports
        $recentLabReports = LabReport::where('patient_id', $patient->id)
            ->with(['doctor', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $stats = [
            'total_records' => MedicalRecord::where('patient_id', $patient->id)->count(),
            'prescriptions' => Prescription::where('patient_id', $patient->id)->count(),
            'lab_reports' => LabReport::where('patient_id', $patient->id)->count(),
            'consultations' => MedicalRecord::where('patient_id', $patient->id)
                ->where('record_type', 'consultation')->count(),
        ];

        return view('patient.medical-records.index', compact(
            'records', 
            'recentPrescriptions', 
            'recentLabReports', 
            'stats'
        ));
    }

    /**
     * Display the specified medical record.
     */
    public function show(MedicalRecord $record): View
    {
        $patient = Auth::guard('patient')->user();

        // Ensure the record belongs to the authenticated patient
        if ($record->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to medical record.');
        }

        $record->load(['doctor', 'appointment', 'prescriptions', 'labReports']);

        return view('patient.medical-records.show', compact('record'));
    }

    /**
     * Display prescriptions.
     */
    public function prescriptions(Request $request): View
    {
        $patient = Auth::guard('patient')->user();
        
        $query = Prescription::where('patient_id', $patient->id)
            ->with(['doctor', 'appointment']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('date_from')) {
            $query->where('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('created_at', '<=', $request->date_to . ' 23:59:59');
        }

        $prescriptions = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('patient.medical-records.prescriptions', compact('prescriptions'));
    }

    /**
     * Display lab reports.
     */
    public function labReports(Request $request): View
    {
        $patient = Auth::guard('patient')->user();
        
        $query = LabReport::where('patient_id', $patient->id)
            ->with(['doctor', 'appointment']);

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
            $query->where('test_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('test_date', '<=', $request->date_to);
        }

        $labReports = $query->orderBy('test_date', 'desc')->paginate(10);

        return view('patient.medical-records.lab-reports', compact('labReports'));
    }

    /**
     * Download a medical record as PDF.
     */
    public function download(MedicalRecord $record)
    {
        $patient = Auth::guard('patient')->user();

        // Ensure the record belongs to the authenticated patient
        if ($record->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to medical record.');
        }

        $record->load(['doctor', 'appointment', 'patient']);

        // You can implement PDF generation here using packages like dompdf or tcpdf
        // For now, we'll return a simple response
        return response()->json([
            'message' => 'PDF download functionality will be implemented',
            'record_id' => $record->id
        ]);
    }

    /**
     * Get medical record statistics for dashboard.
     */
    public function getStats()
    {
        $patient = Auth::guard('patient')->user();

        $stats = [
            'medical_records' => [
                'total' => MedicalRecord::where('patient_id', $patient->id)->count(),
                'this_month' => MedicalRecord::where('patient_id', $patient->id)
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
            'prescriptions' => [
                'total' => Prescription::where('patient_id', $patient->id)->count(),
                'active' => Prescription::where('patient_id', $patient->id)
                    ->where('status', 'active')
                    ->count(),
            ],
            'lab_reports' => [
                'total' => LabReport::where('patient_id', $patient->id)->count(),
                'pending' => LabReport::where('patient_id', $patient->id)
                    ->where('status', 'pending')
                    ->count(),
            ],
        ];

        return response()->json($stats);
    }
}
