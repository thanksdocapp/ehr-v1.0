<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\LabReport;
use App\Models\Prescription;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
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

    /**
     * Get the current user's doctor ID if they are a doctor
     */
    private function getUserDoctorId()
    {
        $user = Auth::user();
        
        if (!$user || $user->role !== 'doctor') {
            return null;
        }
        
        $doctor = Doctor::where('user_id', $user->id)->first();
        return $doctor ? $doctor->id : null;
    }
    public function index()
    {
        $user = Auth::user();
        
        // Get database notifications
        $userNotifications = $user->notifications()
            ->latest()
            ->get()
            ->map(function ($notification) {
                return (object) [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type ?? 'info',
                    'icon' => $this->getTypeIcon($notification->type),
                    'url' => $notification->action_url ?? route('staff.dashboard'),
                    'created_at' => $notification->created_at,
                    'is_read' => $notification->is_read,
                    'read_at' => $notification->read_at,
                    'is_system' => false,
                    'notification_id' => $notification->id,
                    'type_color' => $notification->type_color,
                ];
            });

        // Get user's department and doctor ID for filtering
        $departmentId = $this->getUserDepartmentId();
        $doctorId = $this->getUserDoctorId();
        
        // Get system notifications filtered by department/doctor
        $todayAppointmentsQuery = Appointment::whereDate('appointment_date', today())
            ->whereIn('status', ['confirmed', 'pending']);
        
        // Filter appointments by department or doctor
        if ($doctorId) {
            // For doctors, show only their appointments
            $todayAppointmentsQuery->where('doctor_id', $doctorId);
        } elseif ($departmentId) {
            // For other staff, show appointments in their department
            $todayAppointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        $todayAppointments = $todayAppointmentsQuery->count();
        
        // Filter lab reports by department or doctor
        $pendingLabReportsQuery = LabReport::where('status', 'pending');
        if ($doctorId) {
            $pendingLabReportsQuery->where('doctor_id', $doctorId);
        } elseif ($departmentId) {
            $pendingLabReportsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        $pendingLabReports = $pendingLabReportsQuery->count();
        
        // Filter prescriptions by department or doctor
        $pendingPrescriptionsQuery = Prescription::where('status', 'pending');
        if ($doctorId) {
            $pendingPrescriptionsQuery->where('doctor_id', $doctorId);
        } elseif ($departmentId) {
            $pendingPrescriptionsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }
        $pendingPrescriptions = $pendingPrescriptionsQuery->count();
        
        // Filter patients by department
        $todayPatientsQuery = Patient::whereDate('created_at', today());
        if ($departmentId) {
            $todayPatientsQuery->byDepartment($departmentId);
        }
        $todayPatients = $todayPatientsQuery->count();

        $systemNotifications = collect([
            [
                'id' => 'today_appointments',
                'title' => 'Today\'s Appointments',
                'message' => "$todayAppointments appointments scheduled for today",
                'type' => 'info',
                'icon' => 'fas fa-calendar-day',
                'count' => $todayAppointments,
                'url' => route('staff.appointments.index'),
                'created_at' => now(),
                'is_read' => true, // System notifications are always "read"
                'read_at' => now(),
                'is_system' => true,
                'type_color' => 'info',
            ],
            [
                'id' => 'pending_lab_reports',
                'title' => 'Pending Lab Reports',
                'message' => "$pendingLabReports lab reports need processing",
                'type' => 'warning',
                'icon' => 'fas fa-vial',
                'count' => $pendingLabReports,
                'url' => route('staff.lab-reports.index'),
                'created_at' => now(),
                'is_read' => true,
                'read_at' => now(),
                'is_system' => true,
                'type_color' => 'warning',
            ],
            [
                'id' => 'pending_prescriptions',
                'title' => 'Pending Prescriptions',
                'message' => "$pendingPrescriptions prescriptions need attention",
                'type' => 'warning',
                'icon' => 'fas fa-prescription',
                'count' => $pendingPrescriptions,
                'url' => route('staff.prescriptions.index'),
                'created_at' => now(),
                'is_read' => true,
                'read_at' => now(),
                'is_system' => true,
                'type_color' => 'warning',
            ],
            [
                'id' => 'new_patients',
                'title' => 'New Patients Today',
                'message' => "$todayPatients new patients registered today",
                'type' => 'success',
                'icon' => 'fas fa-user-plus',
                'count' => $todayPatients,
                'url' => route('staff.patients.index'),
                'created_at' => now(),
                'is_read' => true,
                'read_at' => now(),
                'is_system' => true,
                'type_color' => 'success',
            ]
        ])->filter(function ($notification) {
            return $notification['count'] > 0;
        })->map(function ($notification) {
            return (object) $notification;
        });

        // Merge all notifications and sort by created_at
        $allNotifications = $userNotifications->concat($systemNotifications)
            ->sortByDesc('created_at');

        // Manual pagination since we're combining different data sources
        $currentPage = request('page', 1);
        $perPage = 20;
        $total = $allNotifications->count();
        $notifications = $allNotifications->forPage($currentPage, $perPage);
        
        // Create a custom paginator
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $notifications,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );

        return view('staff.notifications.index', compact('paginator'));
    }

    public function show(UserNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            abort(403);
        }

        if (!$notification->is_read) {
            $notification->markAsRead();
        }

        return view('staff.notifications.show', compact('notification'));
    }

    /**
     * Get staff notifications for AJAX (notification bell)
     */
    public function getStaffNotifications()
    {
        $user = Auth::user();
        
        // Get database notifications
        $userNotifications = UserNotification::where('user_id', $user->id)
            ->where('is_read', false)
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => 'user_' . $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type ?? 'info',
                    'icon' => $this->getTypeIcon($notification->type),
                    'url' => $notification->action_url ?? route('staff.dashboard'),
                    'created_at' => $notification->created_at->format('M j, g:i A'),
                    'notification_id' => $notification->id,
                ];
            });

        // Get user's departments and doctor ID for filtering
        $departmentId = $this->getUserDepartmentId();
        $userDepartmentIds = $this->getUserDepartmentIds();
        $doctorId = $this->getUserDoctorId();
        
        // Get system notifications filtered by department/doctor
        $todayAppointmentsQuery = Appointment::whereDate('appointment_date', today())
            ->whereIn('status', ['confirmed', 'pending']);
        
        // Filter appointments by department or doctor
        if ($doctorId) {
            // For doctors, show only their appointments
            $todayAppointmentsQuery->where('doctor_id', $doctorId);
        } elseif (!empty($userDepartmentIds)) {
            // For other staff, show appointments in any of their departments
            $todayAppointmentsQuery->whereHas('doctor', function($q) use ($userDepartmentIds) {
                $q->byDepartments($userDepartmentIds);
            });
        } elseif ($departmentId) {
            // Fallback to single department
            $todayAppointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->byDepartment($departmentId);
            });
        }
        $todayAppointments = $todayAppointmentsQuery->count();
        
        // Filter lab reports by department or doctor
        $pendingLabReportsQuery = LabReport::where('status', 'pending');
        if ($doctorId) {
            $pendingLabReportsQuery->where('doctor_id', $doctorId);
        } elseif (!empty($userDepartmentIds)) {
            $pendingLabReportsQuery->whereHas('doctor', function($q) use ($userDepartmentIds) {
                $q->byDepartments($userDepartmentIds);
            });
        } elseif ($departmentId) {
            $pendingLabReportsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->byDepartment($departmentId);
            });
        }
        $pendingLabReports = $pendingLabReportsQuery->count();
        
        // Filter prescriptions by department or doctor
        $pendingPrescriptionsQuery = Prescription::where('status', 'pending');
        if ($doctorId) {
            $pendingPrescriptionsQuery->where('doctor_id', $doctorId);
        } elseif (!empty($userDepartmentIds)) {
            $pendingPrescriptionsQuery->whereHas('doctor', function($q) use ($userDepartmentIds) {
                $q->byDepartments($userDepartmentIds);
            });
        } elseif ($departmentId) {
            $pendingPrescriptionsQuery->whereHas('doctor', function($q) use ($departmentId) {
                $q->byDepartment($departmentId);
            });
        }
        $pendingPrescriptions = $pendingPrescriptionsQuery->count();
        
        // Filter patients by department
        $todayPatientsQuery = Patient::whereDate('created_at', today());
        if (!empty($userDepartmentIds)) {
            $todayPatientsQuery->byDepartments($userDepartmentIds);
        } elseif ($departmentId) {
            $todayPatientsQuery->byDepartment($departmentId);
        }
        $todayPatients = $todayPatientsQuery->count();

        $systemNotifications = collect([
            [
                'id' => 'today_appointments',
                'title' => 'Today\'s Appointments',
                'message' => "$todayAppointments appointments scheduled for today",
                'type' => 'info',
                'icon' => 'fas fa-calendar-day',
                'count' => $todayAppointments,
                'url' => route('staff.appointments.index'),
                'created_at' => 'Today',
            ],
            [
                'id' => 'pending_lab_reports',
                'title' => 'Pending Lab Reports',
                'message' => "$pendingLabReports lab reports need processing",
                'type' => 'warning',
                'icon' => 'fas fa-vial',
                'count' => $pendingLabReports,
                'url' => route('staff.lab-reports.index'),
                'created_at' => 'System',
            ],
            [
                'id' => 'pending_prescriptions',
                'title' => 'Pending Prescriptions',
                'message' => "$pendingPrescriptions prescriptions need attention",
                'type' => 'warning',
                'icon' => 'fas fa-prescription',
                'count' => $pendingPrescriptions,
                'url' => route('staff.prescriptions.index'),
                'created_at' => 'System',
            ],
            [
                'id' => 'new_patients',
                'title' => 'New Patients Today',
                'message' => "$todayPatients new patients registered today",
                'type' => 'success',
                'icon' => 'fas fa-user-plus',
                'count' => $todayPatients,
                'url' => route('staff.patients.index'),
                'created_at' => 'Today',
            ]
        ])->filter(function ($notification) {
            return $notification['count'] > 0;
        });

        // Merge all notifications
        $allNotifications = $userNotifications->concat($systemNotifications);
        $totalCount = $userNotifications->count() + $systemNotifications->sum('count');

        return response()->json([
            'notifications' => $allNotifications->values(),
            'total_count' => $totalCount
        ]);
    }

    public function markAsRead(Request $request)
    {
        $notificationId = $request->input('id');
        $notification = UserNotification::where('id', $notificationId)
                                      ->where('user_id', Auth::id())
                                      ->first();

        if ($notification && !$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now()
            ]);
            return response()->json(['success' => true, 'message' => 'Notification marked as read.']);
        }

        return response()->json(['success' => false, 'message' => 'Notification not found.'], 404);
    }

    public function markAllAsRead()
    {
        $updated = UserNotification::where('user_id', Auth::id())
            ->where('is_read', false)
            ->update(['is_read' => true, 'read_at' => now()]);

        return response()->json([
            'success' => true, 
            'message' => "All notifications marked as read. ({$updated} notifications updated)"
        ]);
    }

    public function destroy(UserNotification $notification)
    {
        if ($notification->user_id !== Auth::id()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['success' => true, 'message' => 'Notification deleted successfully.']);
    }

    /**
     * Get icon for notification type.
     */
    private function getTypeIcon($type)
    {
        $icons = [
            UserNotification::TYPE_APPOINTMENT => 'fas fa-calendar-check',
            UserNotification::TYPE_PRESCRIPTION => 'fas fa-file-prescription',
            UserNotification::TYPE_LAB_RESULT => 'fas fa-vial',
            UserNotification::TYPE_BILLING => 'fas fa-file-invoice-dollar',
            UserNotification::TYPE_MEDICAL_RECORD => 'fas fa-file-medical',
            UserNotification::TYPE_REMINDER => 'fas fa-bell',
            UserNotification::TYPE_ALERT => 'fas fa-exclamation-triangle',
            UserNotification::TYPE_WELCOME => 'fas fa-handshake',
            UserNotification::TYPE_SYSTEM => 'fas fa-cogs',
        ];
        
        return $icons[$type] ?? 'fas fa-bell';
    }
}
