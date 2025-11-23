<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\UserNotification;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get some users to assign notifications to
        $users = User::take(5)->get();
        
        $notificationTypes = [
            [
                'type' => 'transaction',
                'title' => 'Transaction Completed',
                'message' => 'Your wire transfer of $500 has been successfully processed.',
                'priority' => 'medium'
            ],
            [
                'type' => 'security',
                'title' => 'Security Alert',
                'message' => 'New login detected from a different device. If this wasn\'t you, please contact support immediately.',
                'priority' => 'high'
            ],
            [
                'type' => 'system',
                'title' => 'Account Update',
                'message' => 'Your account information has been updated successfully.',
                'priority' => 'low'
            ],
            [
                'type' => 'transaction',
                'title' => 'Deposit Confirmed',
                'message' => 'Your deposit of $1,200 has been confirmed and added to your account.',
                'priority' => 'medium'
            ],
            [
                'type' => 'security',
                'title' => 'KYC Verification',
                'message' => 'Please complete your KYC verification to access all account features.',
                'priority' => 'high',
                'action_url' => route('user.kyc.index')
            ],
            [
                'type' => 'marketing',
                'title' => 'New Feature Available',
                'message' => 'Check out our new investment portfolio feature now available in your dashboard.',
                'priority' => 'low'
            ]
        ];

        foreach ($users as $user) {
            // Create 2-4 notifications per user
            $notificationCount = rand(2, 4);
            $selectedNotifications = array_rand($notificationTypes, $notificationCount);
            
            foreach ((array)$selectedNotifications as $index) {
                $notification = $notificationTypes[$index];
                
                UserNotification::create([
                    'user_id' => $user->id,
                    'type' => $notification['type'],
                    'title' => $notification['title'],
                    'message' => $notification['message'],
                    'priority' => $notification['priority'],
                    'action_url' => $notification['action_url'] ?? null,
                    'is_read' => rand(0, 1), // Randomly mark some as read
                    'read_at' => rand(0, 1) ? now()->subHours(rand(1, 24)) : null,
                    'created_at' => now()->subHours(rand(1, 72))
                ]);
            }
        }
    }
}
