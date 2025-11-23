<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\UserNotification;
use App\Models\PatientNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $patient = Auth::guard('patient')->user();
        
        $notifications = PatientNotification::where('patient_id', $patient->id)
            ->latest()
            ->paginate(20);

        return view('patient.notifications.index', compact('notifications'));
    }

    public function show(PatientNotification $notification)
    {
        $patient = Auth::guard('patient')->user();
        
        if ($notification->patient_id !== $patient->id) {
            abort(403);
        }

        if (!$notification->read_at) {
            $notification->update(['read_at' => now()]);
        }

        return view('patient.notifications.show', compact('notification'));
    }

    /**
     * Get patient notifications for AJAX (notification bell)
     */
    public function getPatientNotifications()
    {
        $patient = Auth::guard('patient')->user();
        
        if (!$patient) {
            return response()->json([
                'notifications' => [],
                'total_count' => 0,
                'error' => 'Not authenticated'
            ], 401);
        }
        
        $notifications = PatientNotification::where('patient_id', $patient->id)
            ->where('is_read', false)
            ->latest()
            ->limit(10)
            ->get()
            ->map(function ($notification) {
                // Get dynamic icon based on notification type
                $icon = $this->getNotificationIcon($notification->type);
                
                // Get dynamic URL based on notification type and action_url
                $url = $notification->action_url ?: $this->getDefaultUrl($notification->type);
                
                return [
                    'id' => $notification->id,
                    'title' => $notification->title,
                    'message' => $notification->message,
                    'type' => $notification->type ?? 'info',
                    'icon' => $icon,
                    'url' => $url,
                    'created_at' => $notification->created_at->format('M j, g:i A'),
                    'notification_id' => $notification->id,
                ];
            });

        return response()->json([
            'notifications' => $notifications->values(),
            'total_count' => $notifications->count()
        ]);
    }

    public function markAsRead(Request $request)
    {
        $patient = Auth::guard('patient')->user();
        
        $notificationId = $request->input('id');
        $notification = PatientNotification::where('id', $notificationId)
                                      ->where('patient_id', $patient->id)
                                      ->first();

        if ($notification) {
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
        $patient = Auth::guard('patient')->user();
        
        $updated = PatientNotification::where('patient_id', $patient->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now()
            ]);

        return response()->json([
            'success' => true, 
            'message' => "All notifications marked as read. ({$updated} notifications updated)"
        ]);
    }

    public function destroy(PatientNotification $notification)
    {
        $patient = Auth::guard('patient')->user();
        
        if ($notification->patient_id !== $patient->id) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $notification->delete();

        return response()->json(['success' => true, 'message' => 'Notification deleted successfully.']);
    }

    /**
     * Get the appropriate icon for a notification type
     */
    private function getNotificationIcon($type)
    {
        $icons = [
            'appointment' => 'fas fa-calendar-alt',
            'medical_record' => 'fas fa-file-medical',
            'prescription' => 'fas fa-pills',
            'lab_result' => 'fas fa-flask',
            'billing' => 'fas fa-file-invoice-dollar',
            'system' => 'fas fa-cog',
        ];

        return $icons[$type] ?? 'fas fa-bell';
    }

    /**
     * Get the default URL for a notification type
     */
    private function getDefaultUrl($type)
    {
        $urls = [
            'appointment' => route('patient.appointments.index'),
            'medical_record' => route('patient.medical-records.index'),
            'prescription' => route('patient.prescriptions.index'),
            'lab_result' => route('patient.lab-reports.index'),
            'billing' => route('patient.billing.index'),
            'system' => route('patient.dashboard'),
        ];

        return $urls[$type] ?? route('patient.dashboard');
    }
}
