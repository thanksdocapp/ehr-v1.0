<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get the current user's primary department ID for any role
     */
    private function getUserDepartmentId()
    {
        $user = Auth::user();
        
        if (!$user) {
            return null;
        }
        
        // For doctors, get primary department from doctors pivot table or department_id
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->with('departments')->first();
            if ($doctor) {
                // Get primary department from pivot table or fallback to department_id
                $primaryDept = $doctor->primaryDepartment();
                return $primaryDept ? $primaryDept->id : $doctor->department_id;
            }
            return null;
        }
        
        // For other roles, get primary department from users pivot table or department_id
        $user->load('departments');
        $primaryDept = $user->primaryDepartment();
        return $primaryDept ? $primaryDept->id : $user->department_id;
    }

    /**
     * Get all department IDs for the current user (supports multiple departments)
     */
    private function getUserDepartmentIds()
    {
        $user = Auth::user();
        
        if (!$user) {
            return [];
        }
        
        $departmentIds = [];
        
        // For doctors, get all departments from doctors pivot table or department_id
        if ($user->role === 'doctor') {
            $doctor = Doctor::where('user_id', $user->id)->with('departments')->first();
            if ($doctor) {
                if ($doctor->departments->isNotEmpty()) {
                    $departmentIds = $doctor->departments->pluck('id')->toArray();
                } elseif ($doctor->department_id) {
                    $departmentIds = [$doctor->department_id];
                }
            }
        } else {
            // For other roles, get all departments from users pivot table or department_id
            $user->load('departments');
            if ($user->departments->isNotEmpty()) {
                $departmentIds = $user->departments->pluck('id')->toArray();
            } elseif ($user->department_id) {
                $departmentIds = [$user->department_id];
            }
        }
        
        return $departmentIds;
    }

    public function index()
    {
        $user = Auth::user();
        $departmentId = $this->getUserDepartmentId();
        $userDepartmentIds = $this->getUserDepartmentIds();
        
        // Build queries filtered by visibility (visibleTo already handles department filtering)
        $patientsQuery = Patient::query()->visibleTo($user);
        $appointmentsQuery = Appointment::query();
        
        // Filter appointments by department if user has one (support multiple departments)
        if (!empty($userDepartmentIds)) {
            $appointmentsQuery->whereHas('doctor', function($q) use ($userDepartmentIds) {
                $q->byDepartments($userDepartmentIds);
            });
        } elseif ($departmentId) {
            // Fallback to single department
            $appointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->byDepartment($departmentId);
            });
        }
        
        // Calculate accurate stats filtered by department
        $stats = [
            'total_patients' => $patientsQuery->count(),
            'total_appointments' => $appointmentsQuery->count(),
            'pending_appointments' => (clone $appointmentsQuery)->where('status', 'pending')->count(),
            'today_appointments' => (clone $appointmentsQuery)->whereDate('appointment_date', Carbon::today())->count(),
        ];

        // Get recent appointments filtered by department
        $recentAppointmentsQuery = Appointment::with(['patient', 'doctor']);
        if (!empty($userDepartmentIds)) {
            $recentAppointmentsQuery->whereHas('doctor', function($q) use ($userDepartmentIds) {
                $q->byDepartments($userDepartmentIds);
            });
        } elseif ($departmentId) {
            $recentAppointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->byDepartment($departmentId);
            });
        }
        $recentAppointments = $recentAppointmentsQuery->latest()->limit(5)->get();

        // Get today's appointments filtered by department for the Today's Schedule widget
        $todayAppointmentsQuery = Appointment::with(['patient', 'doctor'])
            ->whereDate('appointment_date', Carbon::today());
        if (!empty($userDepartmentIds)) {
            $todayAppointmentsQuery->whereHas('doctor', function($q) use ($userDepartmentIds) {
                $q->byDepartments($userDepartmentIds);
            });
        } elseif ($departmentId) {
            $todayAppointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->byDepartment($departmentId);
            });
        }
        $todayAppointments = $todayAppointmentsQuery->orderBy('appointment_time', 'asc')->get();

        // Use doctor-specific layout for doctors
        if ($user->role === 'doctor') {
            // Get doctor model with department relationship for booking link
            $doctor = Doctor::where('user_id', $user->id)
                ->with(['departments', 'department'])
                ->first();
            
            return view('doctor.dashboard.index', compact('stats', 'recentAppointments', 'todayAppointments', 'doctor'));
        }

        return view('staff.dashboard.index', compact('stats', 'recentAppointments', 'todayAppointments'));
    }

    public function getStats()
    {
        $departmentId = $this->getUserDepartmentId();
        $userDepartmentIds = $this->getUserDepartmentIds();
        
        // Build queries filtered by department (support multiple departments)
        $patientsQuery = Patient::query()->visibleTo(Auth::user());
        $appointmentsQuery = Appointment::query();
        $doctorsQuery = Doctor::where('status', 'active');
        
        // Filter by department if user has one (support multiple departments)
        if (!empty($userDepartmentIds)) {
            $patientsQuery->byDepartments($userDepartmentIds);
            $appointmentsQuery->whereHas('doctor', function($q) use ($userDepartmentIds) {
                $q->byDepartments($userDepartmentIds);
            });
            $doctorsQuery->byDepartments($userDepartmentIds);
        } elseif ($departmentId) {
            // Fallback to single department
            $patientsQuery->byDepartment($departmentId);
            $appointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->byDepartment($departmentId);
            });
            $doctorsQuery->byDepartment($departmentId);
        }
        
        // Limited stats for staff - no financial or sensitive data, filtered by department
        return response()->json([
            'patients' => [
                'total' => $patientsQuery->count(),
                'new_this_month' => (clone $patientsQuery)->whereMonth('created_at', Carbon::now()->month)->count(),
            ],
            'appointments' => [
                'total' => $appointmentsQuery->count(),
                'pending' => (clone $appointmentsQuery)->where('status', 'pending')->count(),
                'confirmed' => (clone $appointmentsQuery)->where('status', 'confirmed')->count(),
                'today' => (clone $appointmentsQuery)->whereDate('appointment_date', Carbon::today())->count(),
            ],
            'doctors' => [
                'total' => $doctorsQuery->count(),
                'available' => (clone $doctorsQuery)->where('availability_status', 'available')->count(),
            ],
        ]);
    }

    /**
     * Toggle dark mode for doctor users
     * DISABLED - Dark mode feature has been disabled
     */
    public function toggleDarkMode(Request $request)
    {
        // Dark mode feature is disabled
        return response()->json([
            'success' => false,
            'message' => 'Dark mode feature has been disabled.'
        ], 403);
    }
}
