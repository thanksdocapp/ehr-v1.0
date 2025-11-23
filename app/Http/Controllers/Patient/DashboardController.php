<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the patient dashboard.
     */
    public function index(): View
    {
        $patient = Auth::guard('patient')->user();
        
        // Get patient's appointments
        $upcomingAppointments = Appointment::where('patient_id', $patient->id)
            ->where('appointment_date', '>=', today())
            ->where('status', '!=', 'cancelled')
            ->with(['doctor', 'department'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(5)
            ->get();
            
        $recentAppointments = Appointment::where('patient_id', $patient->id)
            ->where('appointment_date', '<', today())
            ->with(['doctor', 'department'])
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->limit(5)
            ->get();
            
        // Get recent medical records
        $recentMedicalRecords = MedicalRecord::where('patient_id', $patient->id)
            ->with(['doctor', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
            
        // Get active prescriptions
        $activePrescriptions = Prescription::where('patient_id', $patient->id)
            ->where('status', 'active')
            ->with(['doctor'])
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get();
            
        // Get pending invoices
        $pendingInvoices = Invoice::where('patient_id', $patient->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->orderBy('due_date')
            ->limit(3)
            ->get();
            
        // Get comprehensive statistics
        $stats = [
            'appointments' => [
                'total' => Appointment::where('patient_id', $patient->id)->count(),
                'upcoming' => $upcomingAppointments->count(),
                'completed' => Appointment::where('patient_id', $patient->id)
                    ->where('status', 'completed')
                    ->count(),
                'cancelled' => Appointment::where('patient_id', $patient->id)
                    ->where('status', 'cancelled')
                    ->count(),
                'this_month' => Appointment::where('patient_id', $patient->id)
                    ->whereMonth('appointment_date', now()->month)
                    ->whereYear('appointment_date', now()->year)
                    ->count(),
            ],
            'medical' => [
                'records' => MedicalRecord::where('patient_id', $patient->id)->count(),
                'prescriptions' => Prescription::where('patient_id', $patient->id)->count(),
                'active_prescriptions' => Prescription::where('patient_id', $patient->id)
                    ->where('status', 'active')
                    ->count(),
            ],
            'billing' => [
                'total_invoices' => Invoice::where('patient_id', $patient->id)->count(),
                'pending_invoices' => Invoice::where('patient_id', $patient->id)
                    ->where('status', 'pending')
                    ->count(),
                'overdue_invoices' => Invoice::where('patient_id', $patient->id)
                    ->where('status', 'pending')
                    ->where('due_date', '<', today())
                    ->count(),
                'outstanding_amount' => Invoice::where('patient_id', $patient->id)
                    ->whereIn('status', ['pending', 'overdue'])
                    ->sum('total_amount'),
            ],
        ];
        
        // Get available departments for quick booking
        $departments = Department::where('is_active', true)
            ->withCount(['doctors' => function($query) {
                $query->where('is_active', true);
            }])
            ->orderBy('name')
            ->get()
            ->filter(function($department) {
                return $department->doctors_count > 0;
            });
            
        // Get next appointment
        $nextAppointment = Appointment::where('patient_id', $patient->id)
            ->where('appointment_date', '>=', today())
            ->where('status', '!=', 'cancelled')
            ->with(['doctor', 'department'])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->first();
            
        return view('patient.dashboard.index', compact(
            'patient',
            'upcomingAppointments',
            'recentAppointments',
            'recentMedicalRecords',
            'activePrescriptions',
            'pendingInvoices',
            'stats',
            'departments',
            'nextAppointment'
        ));
    }
    
    /**
     * Get dashboard statistics for AJAX requests.
     */
    public function getStats()
    {
        $patient = Auth::guard('patient')->user();
        
        $stats = [
            'appointments' => [
                'total' => Appointment::where('patient_id', $patient->id)->count(),
                'upcoming' => Appointment::where('patient_id', $patient->id)
                    ->where('appointment_date', '>=', today())
                    ->where('status', '!=', 'cancelled')
                    ->count(),
                'this_week' => Appointment::where('patient_id', $patient->id)
                    ->whereBetween('appointment_date', [now()->startOfWeek(), now()->endOfWeek()])
                    ->count(),
            ],
            'medical' => [
                'records' => MedicalRecord::where('patient_id', $patient->id)->count(),
                'active_prescriptions' => Prescription::where('patient_id', $patient->id)
                    ->where('status', 'active')
                    ->count(),
            ],
            'billing' => [
                'pending_amount' => Invoice::where('patient_id', $patient->id)
                    ->whereIn('status', ['pending', 'overdue'])
                    ->sum('total_amount'),
                'overdue_count' => Invoice::where('patient_id', $patient->id)
                    ->where('status', 'pending')
                    ->where('due_date', '<', today())
                    ->count(),
            ],
        ];
        
        return response()->json($stats);
    }
}
