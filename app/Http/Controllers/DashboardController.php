<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;

class DashboardController extends Controller
{
    /**
     * Show the unified dashboard based on user role.
     */
    public function index()
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }

        // Redirect based on user role
        if ($user->is_admin || $user->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }

        if (in_array($user->role, ['staff', 'nurse', 'receptionist', 'doctor', 'pharmacist', 'technician'])) {
            return redirect()->route('staff.dashboard');
        }
        
        // Default fallback
        return redirect()->route('staff.dashboard');
    }

    /**
     * Get dashboard data based on user role.
     */
    private function getDashboardData($user, $role)
    {
        switch ($role) {
            case 'super_admin':
            case 'admin':
                return $this->getAdminDashboardData($user);
            
            case 'doctor':
                return $this->getDoctorDashboardData($user);
            
            case 'nurse':
                return $this->getNurseDashboardData($user);
            
            case 'receptionist':
                return $this->getReceptionistDashboardData($user);
            
            case 'pharmacist':
                return $this->getPharmacistDashboardData($user);
            
            case 'technician':
                return $this->getTechnicianDashboardData($user);
            
            default:
                return $this->getDefaultDashboardData($user);
        }
    }

    /**
     * Admin dashboard data.
     */
    private function getAdminDashboardData($user)
    {
        return [
            'total_users' => User::count(),
            'total_patients' => Patient::count(),
            'total_appointments' => Appointment::count(),
            'pending_appointments' => Appointment::where('status', 'pending')->count(),
            'today_appointments' => Appointment::whereDate('appointment_date', today())->count(),
            'recent_activities' => [], // You can implement this
            'system_alerts' => [], // You can implement this
            'quick_actions' => [
                ['title' => 'Manage Users', 'url' => route('admin.users.index'), 'icon' => 'fas fa-users', 'permission' => 'users.view'],
                ['title' => 'View Patients', 'url' => route('admin.patients.index'), 'icon' => 'fas fa-user-injured', 'permission' => 'patients.view'],
                ['title' => 'Appointments', 'url' => route('admin.appointments.index'), 'icon' => 'fas fa-calendar-alt', 'permission' => 'appointments.view'],
                ['title' => 'Settings', 'url' => route('admin.settings.index'), 'icon' => 'fas fa-cog', 'permission' => 'settings.view'],
            ]
        ];
    }

    /**
     * Doctor dashboard data.
     */
    private function getDoctorDashboardData($user)
    {
        return [
            'today_appointments' => Appointment::where('doctor_id', $user->id)
                                              ->whereDate('appointment_date', today())
                                              ->count(),
            'pending_consultations' => Appointment::where('doctor_id', $user->id)
                                                  ->where('status', 'confirmed')
                                                  ->count(),
            'total_patients' => Appointment::where('doctor_id', $user->id)
                                          ->distinct('patient_id')
                                          ->count(),
            'completed_today' => Appointment::where('doctor_id', $user->id)
                                           ->whereDate('appointment_date', today())
                                           ->where('status', 'completed')
                                           ->count(),
            'upcoming_appointments' => Appointment::where('doctor_id', $user->id)
                                                 ->where('appointment_date', '>=', now())
                                                 ->orderBy('appointment_date')
                                                 ->limit(5)
                                                 ->get(),
            'quick_actions' => [
                ['title' => 'View Appointments', 'url' => route('admin.appointments.index'), 'icon' => 'fas fa-calendar-check', 'permission' => 'appointments.view'],
                ['title' => 'Patient Records', 'url' => route('admin.medical-records.index'), 'icon' => 'fas fa-file-medical', 'permission' => 'medical_records.view'],
                ['title' => 'Prescriptions', 'url' => route('admin.prescriptions.index'), 'icon' => 'fas fa-prescription-bottle-alt', 'permission' => 'prescriptions.view'],
                ['title' => 'Lab Reports', 'url' => route('admin.lab-reports.index'), 'icon' => 'fas fa-flask', 'permission' => 'lab_reports.view'],
            ]
        ];
    }

    /**
     * Nurse dashboard data.
     */
    private function getNurseDashboardData($user)
    {
        return [
            'assigned_patients' => 0, // Implement based on your patient assignment logic
            'pending_medications' => 0,
            'completed_tasks' => 0,
            'urgent_alerts' => 0,
            'quick_actions' => [
                ['title' => 'Patient Care', 'url' => route('admin.patients.index'), 'icon' => 'fas fa-user-md', 'permission' => 'patients.view'],
                ['title' => 'Medications', 'url' => route('admin.prescriptions.index'), 'icon' => 'fas fa-pills', 'permission' => 'prescriptions.view'],
                ['title' => 'Vital Signs', 'url' => route('admin.medical-records.index'), 'icon' => 'fas fa-heartbeat', 'permission' => 'medical_records.update'],
                ['title' => 'Appointments', 'url' => route('admin.appointments.index'), 'icon' => 'fas fa-calendar-alt', 'permission' => 'appointments.view'],
            ]
        ];
    }

    /**
     * Receptionist dashboard data.
     */
    private function getReceptionistDashboardData($user)
    {
        return [
            'today_appointments' => Appointment::whereDate('appointment_date', today())->count(),
            'pending_registrations' => Patient::where('status', 'pending')->count(),
            'walk_ins' => 0,
            'total_patients' => Patient::count(),
            'quick_actions' => [
                ['title' => 'Book Appointment', 'url' => route('admin.appointments.create'), 'icon' => 'fas fa-calendar-plus', 'permission' => 'appointments.create'],
                ['title' => 'Register Patient', 'url' => route('admin.patients.create'), 'icon' => 'fas fa-user-plus', 'permission' => 'patients.create'],
                ['title' => 'Patient Search', 'url' => route('admin.patients.index'), 'icon' => 'fas fa-search', 'permission' => 'patients.view'],
                ['title' => 'Today Schedule', 'url' => route('admin.appointments.index'), 'icon' => 'fas fa-calendar-day', 'permission' => 'appointments.view'],
            ]
        ];
    }

    /**
     * Pharmacist dashboard data.
     */
    private function getPharmacistDashboardData($user)
    {
        return [
            'pending_prescriptions' => 0,
            'dispensed_today' => 0,
            'low_stock_items' => 0,
            'total_prescriptions' => 0,
            'quick_actions' => [
                ['title' => 'Pending Prescriptions', 'url' => route('admin.prescriptions.index'), 'icon' => 'fas fa-prescription', 'permission' => 'prescriptions.view'],
                ['title' => 'Inventory', 'url' => route('admin.prescriptions.index'), 'icon' => 'fas fa-boxes', 'permission' => 'prescriptions.update'],
                ['title' => 'Drug Database', 'url' => route('admin.prescriptions.index'), 'icon' => 'fas fa-database', 'permission' => 'prescriptions.view'],
                ['title' => 'Reports', 'url' => route('admin.prescriptions.index'), 'icon' => 'fas fa-chart-bar', 'permission' => 'reports.view'],
            ]
        ];
    }

    /**
     * Technician dashboard data.
     */
    private function getTechnicianDashboardData($user)
    {
        return [
            'pending_tests' => 0,
            'completed_today' => 0,
            'urgent_results' => 0,
            'equipment_alerts' => 0,
            'quick_actions' => [
                ['title' => 'Lab Queue', 'url' => route('admin.lab-reports.index'), 'icon' => 'fas fa-list-ul', 'permission' => 'lab_reports.view'],
                ['title' => 'Test Results', 'url' => route('admin.lab-reports.create'), 'icon' => 'fas fa-file-medical-alt', 'permission' => 'lab_reports.create'],
                ['title' => 'Equipment Status', 'url' => route('admin.lab-reports.index'), 'icon' => 'fas fa-tools', 'permission' => 'lab_reports.update'],
                ['title' => 'Quality Control', 'url' => route('admin.lab-reports.index'), 'icon' => 'fas fa-check-circle', 'permission' => 'lab_reports.approve'],
            ]
        ];
    }

    /**
     * Default dashboard data for users without specific roles.
     */
    private function getDefaultDashboardData($user)
    {
        return [
            'message' => 'Welcome to the Hospital Management System',
            'quick_actions' => [
                ['title' => 'Profile', 'url' => route('profile.edit'), 'icon' => 'fas fa-user', 'permission' => null],
                ['title' => 'Help', 'url' => '#', 'icon' => 'fas fa-question-circle', 'permission' => null],
            ]
        ];
    }

    /**
     * Get dashboard statistics via AJAX.
     */
    public function getStats(Request $request)
    {
        $user = Auth::user();
        $role = $user->roles->first()->name ?? 'default';
        
        $data = $this->getDashboardData($user, $role);
        
        return response()->json($data);
    }
}
