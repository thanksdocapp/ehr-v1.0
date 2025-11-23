<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserNotification;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Display a listing of all notifications.
     */
    public function index()
    {
        try {
            $user = Auth::user();
            
            // Get user notifications filtered by visibility scope
            $userNotifications = UserNotification::visibleToUser($user)
                ->orderBy('created_at', 'desc')
                ->get();
            
            // Combine with system notifications like the working getAdminNotifications method
            $systemNotifications = $this->getSystemNotifications();
            
            // Merge user notifications with system notifications
            $allNotifications = collect();
            
            // Add user notifications
            foreach ($userNotifications as $notification) {
                $allNotifications->push($notification);
            }
            
            // Add system notifications as objects
            foreach ($systemNotifications as $sysNotification) {
                // Convert system notification array to object for consistency
                $notificationObj = new \stdClass();
                $notificationObj->id = $sysNotification['id'];
                $notificationObj->type = $sysNotification['type'] ?? 'system';
                $notificationObj->title = $sysNotification['title'];
                $notificationObj->message = $sysNotification['message'];
                $notificationObj->created_at = now(); // System notifications are always "now"
                $notificationObj->is_read = false; // System notifications are always unread
                $notificationObj->action_url = $sysNotification['url'] ?? null;
                $notificationObj->user_id = null; // System notifications don't belong to specific users
                
                $allNotifications->push($notificationObj);
            }
            
            // Paginate the combined collection
            $currentPage = request()->get('page', 1);
            $perPage = 50;
            $notifications = new \Illuminate\Pagination\LengthAwarePaginator(
                $allNotifications->forPage($currentPage, $perPage),
                $allNotifications->count(),
                $perPage,
                $currentPage,
                ['path' => request()->url(), 'pageName' => 'page']
            );

            // Simple stats with defaults to avoid errors
            $pendingAppointments = 0;
            $newPatients = 0; 
            $pendingBills = 0;
            $newLabReports = 0;

            return view('admin.notifications.index', compact(
                'notifications',
                'pendingAppointments',
                'newPatients', 
                'pendingBills',
                'newLabReports'
            ));
            
        } catch (\Exception $e) {
            \Log::error('Error in NotificationController index: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            // Return with empty collection in case of error
            $notifications = new \Illuminate\Pagination\LengthAwarePaginator(
                collect([]), // items
                0, // total
                50, // perPage
                1, // currentPage
                ['path' => request()->url(), 'pageName' => 'page']
            );
            return view('admin.notifications.index', compact('notifications'))
                ->with('error', 'Unable to load notifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Get system notifications (same logic as getAdminNotifications)
     */
    private function getSystemNotifications()
    {
        try {
            $user = Auth::user();
            
            // Get pending actions filtered by user role
            $pendingAppointmentsQuery = \App\Models\Appointment::where('status', 'pending');
            $newPatientsQuery = \App\Models\Patient::whereDate('created_at', today());
            $pendingBillsQuery = \App\Models\Billing::where('status', 'pending');
            $newLabReportsQuery = \App\Models\LabReport::whereDate('created_at', today());
            
            // For doctors, filter by their appointments/patients
            if ($user->role === 'doctor') {
                $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
                if ($doctor) {
                    $pendingAppointmentsQuery->where('doctor_id', $doctor->id);
                    $newLabReportsQuery->where('doctor_id', $doctor->id);
                    
                    // Patients visible to this doctor (using Patient visibility scope)
                    $newPatientsQuery->visibleTo($user);
                }
            } elseif (!$user->is_admin && $user->role !== 'admin') {
                // For other staff, filter by department
                $departmentId = $user->department_id;
                if ($departmentId) {
                    $pendingAppointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    });
                    $newLabReportsQuery->whereHas('doctor', function($q) use ($departmentId) {
                        $q->where('department_id', $departmentId);
                    });
                    $newPatientsQuery->byDepartment($departmentId);
                }
            }
            // Admins see all (no additional filters)
            
            $pendingAppointments = $pendingAppointmentsQuery->count();
            $newPatients = $newPatientsQuery->count();
            $pendingBills = $pendingBillsQuery->count();
            $newLabReports = $newLabReportsQuery->count();
    
            $systemNotifications = collect([
                [
                    'id' => 'pending_appointments',
                    'title' => 'Pending Appointments',
                    'message' => "$pendingAppointments appointments awaiting approval",
                    'type' => 'warning',
                    'icon' => 'fas fa-calendar-check',
                    'count' => $pendingAppointments,
                    'url' => route('admin.appointments.index', ['status' => 'pending']),
                    'created_at' => 'System',
                ],
                [
                    'id' => 'new_patients',
                    'title' => 'New Patients Today',
                    'message' => "$newPatients new patients registered today",
                    'type' => 'success',
                    'icon' => 'fas fa-user-plus',
                    'count' => $newPatients,
                    'url' => route('admin.patients.index'),
                    'created_at' => 'Today',
                ],
                [
                    'id' => 'pending_bills',
                    'title' => 'Pending Bills',
                    'message' => "$pendingBills bills require attention",
                    'type' => 'info',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'count' => $pendingBills,
                    'url' => route('admin.billing.index', ['status' => 'pending']),
                    'created_at' => 'System',
                ],
                [
                    'id' => 'new_lab_reports',
                    'title' => 'New Lab Reports',
                    'message' => "$newLabReports new lab reports available",
                    'type' => 'primary',
                    'icon' => 'fas fa-vial',
                    'count' => $newLabReports,
                    'url' => route('admin.dashboard'),
                    'created_at' => 'Today',
                ]
            ])->filter(function ($notification) {
                return $notification['count'] > 0;
            });
            
            return $systemNotifications->toArray();
        } catch (\Exception $e) {
            \Log::error('Error getting system notifications: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get admin notifications (system notifications, pending actions, etc.)
     */
    public function getAdminNotifications()
    {
        $user = Auth::user();
        
        // Get billing notifications filtered by visibility scope
        $billingNotifications = UserNotification::visibleToUser($user)
            ->where('type', 'billing')
            ->where('is_read', false)
            ->with('user')
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                return [
                    'id' => 'billing_' . $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => 'info',
                    'icon' => 'fas fa-file-invoice-dollar',
                    'count' => 1,
                    'url' => $notification->action_url ?? route('admin.billing.index'),
                    'created_at' => $notification->created_at->format('M j, g:i A'),
                    'notification_id' => $notification->id, // For marking as read
                ];
            });

        // Get pending actions filtered by user role
        $pendingAppointmentsQuery = \App\Models\Appointment::where('status', 'pending');
        $newPatientsQuery = \App\Models\Patient::whereDate('created_at', today());
        $pendingBillsQuery = \App\Models\Billing::where('status', 'pending');
        $newLabReportsQuery = \App\Models\LabReport::whereDate('created_at', today());
        
        // For doctors, filter by their appointments/patients
        if ($user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                $pendingAppointmentsQuery->where('doctor_id', $doctor->id);
                $newLabReportsQuery->where('doctor_id', $doctor->id);
                
                // Patients visible to this doctor (using Patient visibility scope)
                $newPatientsQuery->visibleTo($user);
            }
        } elseif (!$user->is_admin && $user->role !== 'admin') {
            // For other staff, filter by department
            $departmentId = $user->department_id;
            if ($departmentId) {
                $pendingAppointmentsQuery->whereHas('doctor', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
                $newLabReportsQuery->whereHas('doctor', function($q) use ($departmentId) {
                    $q->where('department_id', $departmentId);
                });
                $newPatientsQuery->byDepartment($departmentId);
            }
        }
        // Admins see all (no additional filters)
        
        $pendingAppointments = $pendingAppointmentsQuery->count();
        $newPatients = $newPatientsQuery->count();
        $pendingBills = $pendingBillsQuery->count();
        $newLabReports = $newLabReportsQuery->count();

        $systemNotifications = collect([
            [
                'id' => 'pending_appointments',
                'title' => 'Pending Appointments',
                'message' => "$pendingAppointments appointments awaiting approval",
                'type' => 'warning',
                'icon' => 'fas fa-calendar-check',
                'count' => $pendingAppointments,
                'url' => route('admin.appointments.index', ['status' => 'pending']),
                'created_at' => 'System',
            ],
            [
                'id' => 'new_patients',
                'title' => 'New Patients Today',
                'message' => "$newPatients new patients registered today",
                'type' => 'success',
                'icon' => 'fas fa-user-plus',
                'count' => $newPatients,
                'url' => route('admin.patients.index'),
                'created_at' => 'Today',
            ],
            [
                'id' => 'pending_bills',
                'title' => 'Pending Bills',
                'message' => "$pendingBills bills require attention",
                'type' => 'info',
                'icon' => 'fas fa-file-invoice-dollar',
                'count' => $pendingBills,
                'url' => route('admin.billing.index', ['status' => 'pending']),
                'created_at' => 'System',
            ],
            [
                'id' => 'new_lab_reports',
                'title' => 'New Lab Reports',
                'message' => "$newLabReports new lab reports available",
                'type' => 'primary',
                'icon' => 'fas fa-vial',
                'count' => $newLabReports,
                'url' => route('admin.dashboard'),
                'created_at' => 'Today',
            ]
        ])->filter(function ($notification) {
            return $notification['count'] > 0;
        });

        // Merge billing notifications with system notifications
        $allNotifications = $billingNotifications->concat($systemNotifications);
        $totalCount = $billingNotifications->count() + $systemNotifications->sum('count');

        return response()->json([
            'notifications' => $allNotifications->values(),
            'total_count' => $totalCount
        ]);
    }

    /**
     * Create a new notification for a user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'sometimes|in:low,medium,high',
            'action_url' => 'sometimes|url',
        ]);

        $notification = UserNotification::create([
            'user_id' => $request->user_id,
            'type' => $request->type,
            'title' => $request->title,
            'message' => $request->message,
            'priority' => $request->priority ?? 'medium',
            'action_url' => $request->action_url,
            'data' => $request->data ? json_encode($request->data) : null,
        ]);

        return response()->json([
            'success' => true,
            'notification' => $notification
        ]);
    }

    /**
     * Send bulk notification to multiple users.
     */
    public function sendBulk(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'type' => 'required|string',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'priority' => 'sometimes|in:low,medium,high',
        ]);

        $notifications = [];
        foreach ($request->user_ids as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'type' => $request->type,
                'title' => $request->title,
                'message' => $request->message,
                'priority' => $request->priority ?? 'medium',
                'action_url' => $request->action_url,
                'data' => $request->data ? json_encode($request->data) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        UserNotification::insert($notifications);

        return response()->json([
            'success' => true,
            'message' => 'Notifications sent to ' . count($request->user_ids) . ' users'
        ]);
    }

    /**
     * Get notification statistics.
     */
    public function getStats()
    {
        $stats = [
            'total_notifications' => UserNotification::count(),
            'unread_notifications' => UserNotification::where('is_read', false)->count(),
            'notifications_today' => UserNotification::whereDate('created_at', today())->count(),
            'notifications_this_week' => UserNotification::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Show a specific notification (JSON for API).
     */
    public function show(UserNotification $notification)
    {
        $notification->load('user');
        
        // Add formatted dates and other computed properties
        $notification->created_at_formatted = $notification->created_at->format('M j, Y g:i A');
        $notification->read_at_formatted = $notification->read_at ? $notification->read_at->format('M j, Y g:i A') : null;
        
        // Add type-specific styling
        $notification->type_color = $this->getTypeColor($notification->type);
        $notification->type_icon = $this->getTypeIcon($notification->type);
        
        return response()->json($notification);
    }

    /**
     * View a specific notification (dedicated page).
     */
    public function view(UserNotification $notification)
    {
        $notification->load('user');
        
        // Mark as read when viewing
        if (!$notification->is_read) {
            $notification->markAsRead();
        }
        
        return view('admin.notifications.view', compact('notification'));
    }

    /**
     * Mark a notification as read.
     */
    public function markAsRead(UserNotification $notification)
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now()
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read'
        ]);
    }

    /**
     * Delete a notification.
     */
    public function destroy(UserNotification $notification)
    {
        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification deleted successfully'
        ]);
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead()
    {
        try {
            // Only mark actual UserNotification records as read
            // System notifications can't be marked as read since they're generated dynamically
            $updated = UserNotification::where('is_read', false)->update([
                'is_read' => true,
                'read_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => $updated > 0 ? "Marked $updated user notifications as read" : 'No user notifications to mark as read',
                'updated_count' => $updated
            ]);
        } catch (\Exception $e) {
            \Log::error('Error marking notifications as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to mark notifications as read'
            ], 500);
        }
    }

    /**
     * Get color for notification type.
     */
    private function getTypeColor($type)
    {
        $colors = [
            UserNotification::TYPE_APPOINTMENT => 'info',
            UserNotification::TYPE_PRESCRIPTION => 'primary',
            UserNotification::TYPE_LAB_RESULT => 'success',
            UserNotification::TYPE_BILLING => 'warning',
            UserNotification::TYPE_MEDICAL_RECORD => 'secondary',
            UserNotification::TYPE_REMINDER => 'info',
            UserNotification::TYPE_ALERT => 'danger',
            UserNotification::TYPE_WELCOME => 'success',
            UserNotification::TYPE_SYSTEM => 'dark',
        ];
        
        return $colors[$type] ?? 'secondary';
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
