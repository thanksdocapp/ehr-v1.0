<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\LabReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class LabReportController extends Controller
{
    /**
     * Display a listing of the patient's lab reports.
     */
    public function index(Request $request): View
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
     * Display the specified lab report.
     */
    public function show(LabReport $labReport): View
    {
        $patient = Auth::guard('patient')->user();

        // Ensure the lab report belongs to the authenticated patient
        if ($labReport->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to lab report.');
        }

        $labReport->load(['doctor', 'appointment', 'medicalRecord']);

        return view('patient.medical-records.lab-report-show', compact('labReport'));
    }

    /**
     * Download lab report file.
     */
    public function download(LabReport $labReport)
    {
        $patient = Auth::guard('patient')->user();

        // Ensure the lab report belongs to the authenticated patient
        if ($labReport->patient_id !== $patient->id) {
            abort(403, 'Unauthorized access to lab report.');
        }

        // Check if file exists
        if (!$labReport->file_path || !Storage::disk('public')->exists($labReport->file_path)) {
            return redirect()->back()->with('error', 'Report file not found.');
        }

        return Storage::disk('public')->download(
            $labReport->file_path,
            $labReport->test_name . '_' . $labReport->patient->full_name . '_' . $labReport->test_date->format('Y-m-d') . '.pdf'
        );
    }
}
