<?php

namespace App\Services;

use App\Models\User;
use App\Models\Patient;
use App\Models\UserNotification;
use App\Models\PatientNotification;
use App\Services\EmailNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class NotificationDigestService
{
    protected $emailNotificationService;

    public function __construct(EmailNotificationService $emailNotificationService)
    {
        $this->emailNotificationService = $emailNotificationService;
    }

    /**
     * Send daily digest emails to all patients who have digest enabled.
     *
     * @return array Statistics about the digest run
     */
    public function sendDailyPatientDigests(): array
    {
        $stats = ['sent' => 0, 'skipped' => 0, 'errors' => 0];
        $yesterday = Carbon::yesterday();

        // Get patients with daily digest enabled
        $patients = Patient::where('is_active', true)
            ->whereNotNull('email')
            ->get();

        foreach ($patients as $patient) {
            try {
                $preferences = $patient->notification_preferences ?? [];

                // Skip if email disabled or digest not enabled
                if (!($preferences['email_enabled'] ?? true)) {
                    $stats['skipped']++;
                    continue;
                }

                // Get unread notifications from the last 24 hours
                $notifications = PatientNotification::where('patient_id', $patient->id)
                    ->where('is_read', false)
                    ->where('created_at', '>=', $yesterday->startOfDay())
                    ->where('created_at', '<=', Carbon::today()->startOfDay())
                    ->orderBy('priority', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();

                if ($notifications->isEmpty()) {
                    $stats['skipped']++;
                    continue;
                }

                // Send digest email
                $this->sendPatientDigestEmail($patient, $notifications, 'daily');
                $stats['sent']++;

            } catch (\Exception $e) {
                Log::error('Failed to send daily digest to patient', [
                    'patient_id' => $patient->id,
                    'error' => $e->getMessage()
                ]);
                $stats['errors']++;
            }
        }

        return $stats;
    }

    /**
     * Send weekly digest emails to all patients.
     *
     * @return array Statistics about the digest run
     */
    public function sendWeeklyPatientDigests(): array
    {
        $stats = ['sent' => 0, 'skipped' => 0, 'errors' => 0];
        $weekAgo = Carbon::now()->subWeek();

        // Get patients with email enabled
        $patients = Patient::where('is_active', true)
            ->whereNotNull('email')
            ->get();

        foreach ($patients as $patient) {
            try {
                $preferences = $patient->notification_preferences ?? [];

                // Skip if email disabled
                if (!($preferences['email_enabled'] ?? true)) {
                    $stats['skipped']++;
                    continue;
                }

                // Get all notifications from the last week
                $notifications = PatientNotification::where('patient_id', $patient->id)
                    ->where('created_at', '>=', $weekAgo)
                    ->orderBy('created_at', 'desc')
                    ->get();

                if ($notifications->isEmpty()) {
                    $stats['skipped']++;
                    continue;
                }

                // Send weekly summary email
                $this->sendPatientDigestEmail($patient, $notifications, 'weekly');
                $stats['sent']++;

            } catch (\Exception $e) {
                Log::error('Failed to send weekly digest to patient', [
                    'patient_id' => $patient->id,
                    'error' => $e->getMessage()
                ]);
                $stats['errors']++;
            }
        }

        return $stats;
    }

    /**
     * Send daily digest to staff users.
     *
     * @return array Statistics about the digest run
     */
    public function sendDailyStaffDigests(): array
    {
        $stats = ['sent' => 0, 'skipped' => 0, 'errors' => 0];
        $yesterday = Carbon::yesterday();

        // Get active staff users
        $users = User::where('is_active', true)
            ->whereNotNull('email')
            ->get();

        foreach ($users as $user) {
            try {
                $preferences = $user->notification_preferences ?? [];

                // Skip if email disabled
                if (!($preferences['email_enabled'] ?? true)) {
                    $stats['skipped']++;
                    continue;
                }

                // Get unread notifications from the last 24 hours
                $query = UserNotification::where('user_id', $user->id)
                    ->where('is_read', false)
                    ->where('created_at', '>=', $yesterday->startOfDay())
                    ->where('created_at', '<=', Carbon::today()->startOfDay())
                    ->orderBy('priority', 'desc')
                    ->orderBy('created_at', 'desc');

                $notifications = $query->get();

                if ($notifications->isEmpty()) {
                    $stats['skipped']++;
                    continue;
                }

                // Send digest email
                $this->sendStaffDigestEmail($user, $notifications, 'daily');
                $stats['sent']++;

            } catch (\Exception $e) {
                Log::error('Failed to send daily digest to staff', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage()
                ]);
                $stats['errors']++;
            }
        }

        return $stats;
    }

    /**
     * Send digest email to patient.
     *
     * @param Patient $patient
     * @param \Illuminate\Support\Collection $notifications
     * @param string $type 'daily' or 'weekly'
     * @return void
     */
    protected function sendPatientDigestEmail(Patient $patient, $notifications, string $type): void
    {
        $subject = $type === 'daily'
            ? 'Your Daily Health Update Summary'
            : 'Your Weekly Health Update Summary';

        $groupedNotifications = $notifications->groupBy('category');

        // Group notifications by type for better organization
        $summary = [
            'total' => $notifications->count(),
            'unread' => $notifications->where('is_read', false)->count(),
            'high_priority' => $notifications->where('priority', 'high')->count(),
            'by_category' => $groupedNotifications->map->count(),
        ];

        // Build email content
        $content = $this->buildPatientDigestContent($patient, $notifications, $summary, $type);

        // Send via email notification service
        try {
            Mail::send([], [], function ($message) use ($patient, $subject, $content) {
                $message->to($patient->email, $patient->first_name . ' ' . $patient->last_name)
                    ->subject($subject)
                    ->html($content);
            });

            Log::info("Sent {$type} digest to patient", [
                'patient_id' => $patient->id,
                'notification_count' => $notifications->count()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send {$type} digest email", [
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Send digest email to staff user.
     *
     * @param User $user
     * @param \Illuminate\Support\Collection $notifications
     * @param string $type 'daily' or 'weekly'
     * @return void
     */
    protected function sendStaffDigestEmail(User $user, $notifications, string $type): void
    {
        $subject = $type === 'daily'
            ? 'Your Daily EHR Notification Summary'
            : 'Your Weekly EHR Notification Summary';

        $summary = [
            'total' => $notifications->count(),
            'unread' => $notifications->where('is_read', false)->count(),
            'high_priority' => $notifications->where('priority', 'high')->count(),
            'by_category' => $notifications->groupBy('category')->map->count(),
        ];

        $content = $this->buildStaffDigestContent($user, $notifications, $summary, $type);

        try {
            Mail::send([], [], function ($message) use ($user, $subject, $content) {
                $message->to($user->email, $user->name)
                    ->subject($subject)
                    ->html($content);
            });

            Log::info("Sent {$type} digest to staff", [
                'user_id' => $user->id,
                'notification_count' => $notifications->count()
            ]);
        } catch (\Exception $e) {
            Log::error("Failed to send {$type} digest email to staff", [
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Build HTML content for patient digest email.
     *
     * @param Patient $patient
     * @param \Illuminate\Support\Collection $notifications
     * @param array $summary
     * @param string $type
     * @return string
     */
    protected function buildPatientDigestContent(Patient $patient, $notifications, array $summary, string $type): string
    {
        $hospitalName = config('app.name', 'Hospital EHR');
        $periodLabel = $type === 'daily' ? 'today' : 'this week';

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #4F46E5; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9fafb; padding: 20px; }
                .summary-box { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
                .notification-item { background: white; padding: 12px; margin-bottom: 10px; border-left: 4px solid #4F46E5; border-radius: 4px; }
                .notification-item.high { border-left-color: #EF4444; }
                .notification-title { font-weight: bold; margin-bottom: 5px; }
                .notification-time { color: #6B7280; font-size: 12px; }
                .footer { text-align: center; padding: 20px; color: #6B7280; font-size: 12px; }
                .btn { display: inline-block; background: #4F46E5; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; margin-top: 15px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='margin:0;'>{$hospitalName}</h1>
                    <p style='margin:5px 0 0;'>Your " . ucfirst($type) . " Health Summary</p>
                </div>
                <div class='content'>
                    <p>Hello {$patient->first_name},</p>
                    <p>Here's your health update summary for {$periodLabel}:</p>

                    <div class='summary-box'>
                        <h3 style='margin-top:0;'>Summary</h3>
                        <p><strong>{$summary['total']}</strong> notifications</p>
                        <p><strong>{$summary['unread']}</strong> unread</p>";

        if ($summary['high_priority'] > 0) {
            $html .= "<p style='color:#EF4444;'><strong>{$summary['high_priority']}</strong> high priority items</p>";
        }

        $html .= "</div>";

        // Group and display notifications
        $grouped = $notifications->groupBy('category');
        foreach ($grouped as $category => $categoryNotifications) {
            $categoryLabel = ucfirst(str_replace('_', ' ', $category));
            $html .= "<h4>{$categoryLabel}</h4>";

            foreach ($categoryNotifications->take(5) as $notification) {
                $priorityClass = $notification->priority === 'high' ? 'high' : '';
                $time = Carbon::parse($notification->created_at)->format('M j, g:i A');
                $html .= "
                    <div class='notification-item {$priorityClass}'>
                        <div class='notification-title'>{$notification->title}</div>
                        <div>{$notification->message}</div>
                        <div class='notification-time'>{$time}</div>
                    </div>";
            }

            if ($categoryNotifications->count() > 5) {
                $remaining = $categoryNotifications->count() - 5;
                $html .= "<p style='color:#6B7280;'>...and {$remaining} more</p>";
            }
        }

        $portalUrl = config('app.url') . '/patient/notifications';
        $html .= "
                    <div style='text-align:center;'>
                        <a href='{$portalUrl}' class='btn'>View All Notifications</a>
                    </div>
                </div>
                <div class='footer'>
                    <p>This is an automated message from {$hospitalName}.</p>
                    <p>To manage your notification preferences, visit your profile settings.</p>
                </div>
            </div>
        </body>
        </html>";

        return $html;
    }

    /**
     * Build HTML content for staff digest email.
     *
     * @param User $user
     * @param \Illuminate\Support\Collection $notifications
     * @param array $summary
     * @param string $type
     * @return string
     */
    protected function buildStaffDigestContent(User $user, $notifications, array $summary, string $type): string
    {
        $appName = config('app.name', 'Hospital EHR');
        $periodLabel = $type === 'daily' ? 'today' : 'this week';

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #059669; color: white; padding: 20px; text-align: center; border-radius: 8px 8px 0 0; }
                .content { background: #f9fafb; padding: 20px; }
                .summary-box { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
                .notification-item { background: white; padding: 12px; margin-bottom: 10px; border-left: 4px solid #059669; border-radius: 4px; }
                .notification-item.high { border-left-color: #EF4444; }
                .notification-title { font-weight: bold; margin-bottom: 5px; }
                .notification-time { color: #6B7280; font-size: 12px; }
                .footer { text-align: center; padding: 20px; color: #6B7280; font-size: 12px; }
                .btn { display: inline-block; background: #059669; color: white; padding: 10px 20px; text-decoration: none; border-radius: 6px; margin-top: 15px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1 style='margin:0;'>{$appName} - Staff Portal</h1>
                    <p style='margin:5px 0 0;'>Your " . ucfirst($type) . " Notification Summary</p>
                </div>
                <div class='content'>
                    <p>Hello {$user->name},</p>
                    <p>Here's your notification summary for {$periodLabel}:</p>

                    <div class='summary-box'>
                        <h3 style='margin-top:0;'>Summary</h3>
                        <p><strong>{$summary['total']}</strong> total notifications</p>
                        <p><strong>{$summary['unread']}</strong> unread</p>";

        if ($summary['high_priority'] > 0) {
            $html .= "<p style='color:#EF4444;'><strong>{$summary['high_priority']}</strong> require attention</p>";
        }

        $html .= "</div>";

        // Display notifications grouped by category
        $grouped = $notifications->groupBy('category');
        foreach ($grouped as $category => $categoryNotifications) {
            $categoryLabel = ucfirst(str_replace('_', ' ', $category));
            $html .= "<h4>{$categoryLabel}</h4>";

            foreach ($categoryNotifications->take(5) as $notification) {
                $priorityClass = $notification->priority === 'high' ? 'high' : '';
                $time = Carbon::parse($notification->created_at)->format('M j, g:i A');
                $html .= "
                    <div class='notification-item {$priorityClass}'>
                        <div class='notification-title'>{$notification->title}</div>
                        <div>{$notification->message}</div>
                        <div class='notification-time'>{$time}</div>
                    </div>";
            }

            if ($categoryNotifications->count() > 5) {
                $remaining = $categoryNotifications->count() - 5;
                $html .= "<p style='color:#6B7280;'>...and {$remaining} more</p>";
            }
        }

        $dashboardUrl = config('app.url') . '/admin/notifications';
        $html .= "
                    <div style='text-align:center;'>
                        <a href='{$dashboardUrl}' class='btn'>View Dashboard</a>
                    </div>
                </div>
                <div class='footer'>
                    <p>This is an automated message from {$appName}.</p>
                    <p>You're receiving this because you have digest notifications enabled.</p>
                </div>
            </div>
        </body>
        </html>";

        return $html;
    }
}
