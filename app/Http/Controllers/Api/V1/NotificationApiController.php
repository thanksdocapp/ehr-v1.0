<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class NotificationApiController extends BaseApiController
{
    /**
     * Get user notifications.
     */
    public function index(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'nullable|integer|min:1|max:100',
            'unread_only' => 'nullable|boolean',
            'type' => 'nullable|in:appointment,general,reminder,announcement'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 20);

            // Build notifications query
            $query = $this->buildNotificationsQuery($user, $request);

            $notifications = $query->orderBy('created_at', 'desc')
                                   ->paginate($perPage);

            // Add formatted data
            $notifications->getCollection()->transform(function ($notification) {
                $notification->formatted_date = $notification->created_at->diffForHumans();
                $notification->is_today = $notification->created_at->isToday();
                return $notification;
            });

            return $this->sendPaginatedResponse($notifications, 'Notifications retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve notifications: ' . $e->getMessage());
        }
    }

    /**
     * Get unread notifications count.
     */
    public function getUnreadCount(Request $request)
    {
        try {
            $user = $request->user();
            
            $count = $this->buildNotificationsQuery($user)
                          ->where('read_at', null)
                          ->count();

            return $this->sendResponse(['unread_count' => $count], 'Unread count retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to get unread count: ' . $e->getMessage());
        }
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $notification = $this->buildNotificationsQuery($user)
                                 ->where('id', $id)
                                 ->first();

            if (!$notification) {
                return $this->sendNotFound('Notification');
            }

            if (!$notification->read_at) {
                $notification->update(['read_at' => now()]);
            }

            return $this->sendResponse($notification, 'Notification marked as read');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to mark notification as read: ' . $e->getMessage());
        }
    }

    /**
     * Mark all notifications as read.
     */
    public function markAllAsRead(Request $request)
    {
        try {
            $user = $request->user();
            
            $updated = $this->buildNotificationsQuery($user)
                            ->whereNull('read_at')
                            ->update(['read_at' => now()]);

            return $this->sendResponse(
                ['updated_count' => $updated],
                'All notifications marked as read'
            );

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to mark all notifications as read: ' . $e->getMessage());
        }
    }

    /**
     * Delete notification.
     */
    public function delete(Request $request, $id)
    {
        try {
            $user = $request->user();
            
            $notification = $this->buildNotificationsQuery($user)
                                 ->where('id', $id)
                                 ->first();

            if (!$notification) {
                return $this->sendNotFound('Notification');
            }

            $notification->delete();

            return $this->sendResponse(null, 'Notification deleted successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to delete notification: ' . $e->getMessage());
        }
    }

    /**
     * Get appointment reminders.
     */
    public function getAppointmentReminders(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user instanceof Patient) {
                return $this->sendUnauthorized('Access denied');
            }

            // Get upcoming appointments in the next 7 days
            $upcomingAppointments = $user->appointments()
                ->with(['doctor.department'])
                ->where('appointment_date', '>=', now()->format('Y-m-d'))
                ->where('appointment_date', '<=', now()->addDays(7)->format('Y-m-d'))
                ->where('status', '!=', 'cancelled')
                ->orderBy('appointment_date', 'asc')
                ->orderBy('appointment_time', 'asc')
                ->get();

            $reminders = $upcomingAppointments->map(function ($appointment) {
                $appointmentDateTime = now()->createFromFormat(
                    'Y-m-d H:i:s',
                    $appointment->appointment_date . ' ' . $appointment->appointment_time
                );

                return [
                    'id' => $appointment->id,
                    'type' => 'appointment_reminder',
                    'title' => 'Upcoming Appointment',
                    'message' => "You have an appointment with Dr. {$appointment->doctor->name} on " . 
                                $appointmentDateTime->format('M d, Y') . " at " . 
                                $appointmentDateTime->format('h:i A'),
                    'appointment' => $appointment,
                    'days_until' => now()->diffInDays($appointmentDateTime, false),
                    'hours_until' => now()->diffInHours($appointmentDateTime, false),
                    'is_today' => $appointmentDateTime->isToday(),
                    'is_tomorrow' => $appointmentDateTime->isTomorrow(),
                    'formatted_time' => $appointmentDateTime->diffForHumans()
                ];
            });

            return $this->sendResponse($reminders, 'Appointment reminders retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve appointment reminders: ' . $e->getMessage());
        }
    }

    /**
     * Get system announcements.
     */
    public function getAnnouncements(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'per_page' => 'nullable|integer|min:1|max:50',
            'active_only' => 'nullable|boolean'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $perPage = $request->get('per_page', 10);
            
            // This would typically come from an announcements table
            // For now, return sample announcements
            $announcements = [
                [
                    'id' => 1,
                    'title' => 'Hospital Hours Update',
                    'message' => 'Please note that our hospital hours have been extended during weekends.',
                    'type' => 'general',
                    'priority' => 'medium',
                    'valid_until' => now()->addDays(30)->toDateString(),
                    'created_at' => now()->subDays(2)->toISOString(),
                    'is_active' => true
                ],
                [
                    'id' => 2,
                    'title' => 'New Services Available',
                    'message' => 'We are excited to announce new telemedicine services are now available.',
                    'type' => 'announcement',
                    'priority' => 'high',
                    'valid_until' => now()->addDays(60)->toDateString(),
                    'created_at' => now()->subDays(5)->toISOString(),
                    'is_active' => true
                ]
            ];

            if ($request->boolean('active_only')) {
                $announcements = array_filter($announcements, function ($announcement) {
                    return $announcement['is_active'] && 
                           now()->lessThanOrEqualTo($announcement['valid_until']);
                });
            }

            return $this->sendResponse($announcements, 'Announcements retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve announcements: ' . $e->getMessage());
        }
    }

    /**
     * Update notification preferences.
     */
    public function updatePreferences(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'appointment_reminders' => 'nullable|boolean',
            'general_notifications' => 'nullable|boolean',
            'promotional_messages' => 'nullable|boolean',
            'appointment_confirmations' => 'nullable|boolean',
            'appointment_cancellations' => 'nullable|boolean',
            'reminder_hours_before' => 'nullable|integer|min:1|max:72'
        ]);

        if ($validator->fails()) {
            return $this->sendValidationError($validator);
        }

        try {
            $user = $request->user();
            
            if (!$user instanceof Patient) {
                return $this->sendUnauthorized('Access denied');
            }

            // Update user's notification preferences
            // This would typically be stored in a notification_preferences table
            // For now, we'll store it as JSON in the patient table
            $preferences = [
                'appointment_reminders' => $request->boolean('appointment_reminders', true),
                'general_notifications' => $request->boolean('general_notifications', true),
                'promotional_messages' => $request->boolean('promotional_messages', false),
                'appointment_confirmations' => $request->boolean('appointment_confirmations', true),
                'appointment_cancellations' => $request->boolean('appointment_cancellations', true),
                'reminder_hours_before' => $request->get('reminder_hours_before', 24),
                'updated_at' => now()->toISOString()
            ];

            $user->update([
                'notification_preferences' => json_encode($preferences)
            ]);

            return $this->sendResponse($preferences, 'Notification preferences updated successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to update notification preferences: ' . $e->getMessage());
        }
    }

    /**
     * Get notification preferences.
     */
    public function getPreferences(Request $request)
    {
        try {
            $user = $request->user();
            
            if (!$user instanceof Patient) {
                return $this->sendUnauthorized('Access denied');
            }

            $preferences = $user->notification_preferences 
                ? json_decode($user->notification_preferences, true)
                : [
                    'appointment_reminders' => true,
                    'general_notifications' => true,
                    'promotional_messages' => false,
                    'appointment_confirmations' => true,
                    'appointment_cancellations' => true,
                    'reminder_hours_before' => 24
                ];

            return $this->sendResponse($preferences, 'Notification preferences retrieved successfully');

        } catch (\Exception $e) {
            return $this->sendServerError('Failed to retrieve notification preferences: ' . $e->getMessage());
        }
    }

    /**
     * Build notifications query based on user type.
     */
    private function buildNotificationsQuery($user, Request $request = null)
    {
        // This is a simplified implementation
        // In a real application, you would have a notifications table
        // For now, return a dummy query structure

        return DB::table('notifications')
            ->where('user_id', $user->id)
            ->where('user_type', get_class($user))
            ->when($request && $request->has('unread_only') && $request->boolean('unread_only'), function ($query) {
                return $query->whereNull('read_at');
            })
            ->when($request && $request->has('type'), function ($query) use ($request) {
                return $query->where('type', $request->type);
            });
    }

    /**
     * Create a new notification (helper method).
     */
    public static function createNotification($userId, $userType, $type, $title, $message, $data = null)
    {
        return DB::table('notifications')->insert([
            'user_id' => $userId,
            'user_type' => $userType,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data ? json_encode($data) : null,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
