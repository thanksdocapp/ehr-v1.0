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
