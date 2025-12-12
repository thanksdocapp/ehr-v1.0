<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class AppointmentObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Appointment "created" event.
     */
    public function created(Appointment $appointment)
    {
        try {
            // Only skip email notification for online appointments with Whereby platform
            // if Whereby is actually ENABLED - the controller will handle sending email
            // after the Whereby meeting link is created
            $wherebyService = app(\App\Services\WherebyService::class);
            $wherebyEnabled = $wherebyService->isEnabled();

            if ($appointment->is_online &&
                $appointment->meeting_platform === 'whereby' &&
                empty($appointment->meeting_link) &&
                $wherebyEnabled) {
                Log::info('Skipping observer notification for Whereby appointment - will be sent after meeting link is generated', [
                    'appointment_id' => $appointment->id,
                    'whereby_enabled' => true
                ]);

                // Still send in-app notifications, just skip the email
                $appointment->load(['patient.user', 'doctor.user']);
                $this->notificationService->sendAppointmentNotification($appointment, 'created', ['skip_email' => true]);
                return;
            }

            // Load relationships
            $appointment->load(['patient.user', 'doctor.user']);

            // Send appointment created notifications
            $this->notificationService->sendAppointmentNotification($appointment, 'created');

            Log::info('Appointment created notification sent', ['appointment_id' => $appointment->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send appointment created notification', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the Appointment "updated" event.
     */
    public function updated(Appointment $appointment)
    {
        try {
            // Load relationships
            $appointment->load(['patient.user', 'doctor.user']);
            
            // Check what was updated and send appropriate notifications
            if ($appointment->wasChanged(['appointment_date', 'appointment_time'])) {
                // Appointment was rescheduled
                $this->notificationService->sendAppointmentNotification($appointment, 'updated');
                Log::info('Appointment rescheduled notification sent', ['appointment_id' => $appointment->id]);
            }
            
            if ($appointment->wasChanged('status')) {
                $this->handleStatusChange($appointment);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to send appointment updated notification', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle appointment status changes
     */
    protected function handleStatusChange(Appointment $appointment)
    {
        $oldStatus = $appointment->getOriginal('status');
        $newStatus = $appointment->status;
        
        Log::info('Appointment status changed', [
            'appointment_id' => $appointment->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);
        
        switch ($newStatus) {
            case 'confirmed':
                if ($oldStatus === 'pending') {
                    // Ensure online Whereby appointments have a meeting link before sending confirmation emails.
                    // IMPORTANT: Only do this when the appointment is explicitly marked as a Whereby meeting.
                    try {
                        if ($appointment->is_online &&
                            $appointment->meeting_platform === 'whereby' &&
                            empty($appointment->meeting_link)) {
                            $wherebyService = app(\App\Services\WherebyService::class);
                            if ($wherebyService->isEnabled()) {
                                Log::info('Whereby: Creating meeting on appointment confirmation', [
                                    'appointment_id' => $appointment->id,
                                    'meeting_platform' => $appointment->meeting_platform,
                                ]);
                                $wherebyService->createMeetingForAppointment($appointment);
                                $appointment->refresh();
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('Whereby: Failed to create meeting on appointment confirmation', [
                            'appointment_id' => $appointment->id,
                            'error' => $e->getMessage(),
                        ]);
                    }

                    $this->notificationService->sendAppointmentNotification($appointment, 'confirmed');
                }
                break;
                
            case 'cancelled':
                $this->notificationService->sendAppointmentNotification($appointment, 'cancelled');
                break;
                
            case 'completed':
                $this->notificationService->sendAppointmentNotification($appointment, 'completed');
                break;
                
            case 'rescheduled':
                $this->notificationService->sendAppointmentNotification($appointment, 'rescheduled');
                break;
        }
    }

    /**
     * Handle the Appointment "deleted" event.
     */
    public function deleted(Appointment $appointment)
    {
        try {
            // Load relationships
            $appointment->load(['patient.user', 'doctor.user']);
            
            // Send cancellation notification when appointment is deleted
            // Use a special method that doesn't reference the appointment ID since it's being deleted
            $this->notificationService->sendAppointmentDeletionNotification($appointment);
            
            Log::info('Appointment deleted notification sent', ['appointment_id' => $appointment->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send appointment deleted notification', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
