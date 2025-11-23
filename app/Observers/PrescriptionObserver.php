<?php

namespace App\Observers;

use App\Models\Prescription;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class PrescriptionObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the Prescription "created" event.
     */
    public function created(Prescription $prescription)
    {
        try {
            // Load relationships
            $prescription->load(['patient.user', 'doctor.user', 'pharmacist']);
            
            // Send prescription created notification
            $this->notificationService->sendPrescriptionNotification($prescription, 'created');
            
            Log::info('Prescription created notification sent', [
                'prescription_id' => $prescription->id,
                'patient_id' => $prescription->patient_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send prescription created notification', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle the Prescription "updated" event.
     */
    public function updated(Prescription $prescription)
    {
        try {
            // Load relationships
            $prescription->load(['patient.user', 'doctor.user', 'pharmacist']);
            
            // Check what was updated and send appropriate notifications
            if ($prescription->wasChanged('status')) {
                $this->handleStatusChange($prescription);
            }
            
            if ($prescription->wasChanged(['medications', 'notes', 'refills_allowed'])) {
                // Prescription details were updated
                $this->notificationService->sendPrescriptionNotification($prescription, 'updated');
                
                Log::info('Prescription updated notification sent', [
                    'prescription_id' => $prescription->id,
                    'patient_id' => $prescription->patient_id
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to send prescription updated notification', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Handle prescription status changes
     */
    protected function handleStatusChange(Prescription $prescription)
    {
        $oldStatus = $prescription->getOriginal('status');
        $newStatus = $prescription->status;
        
        Log::info('Prescription status changed', [
            'prescription_id' => $prescription->id,
            'old_status' => $oldStatus,
            'new_status' => $newStatus
        ]);
        
        switch ($newStatus) {
            case 'pending':
                if ($oldStatus !== 'pending') {
                    $this->notificationService->sendPrescriptionNotification($prescription, 'pending_approval');
                }
                break;
                
            case 'approved':
                $this->notificationService->sendPrescriptionNotification($prescription, 'approved');
                
                // Send to electronic dispenser API if enabled
                if (config('hospital.dispenser_api.send_on_approval', true)) {
                    try {
                        $dispenserService = app(\App\Services\ElectronicDispenserService::class);
                        $result = $dispenserService->sendPrescription($prescription);
                        
                        if ($result['sent']) {
                            Log::info('Prescription sent to electronic dispenser successfully', [
                                'prescription_id' => $prescription->id,
                                'result' => $result
                            ]);
                        } else {
                            Log::warning('Prescription could not be sent to electronic dispenser', [
                                'prescription_id' => $prescription->id,
                                'result' => $result
                            ]);
                        }
                    } catch (\Exception $e) {
                        // Log error but don't fail the approval process
                        Log::error('Failed to send prescription to electronic dispenser', [
                            'prescription_id' => $prescription->id,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                    }
                }
                break;
                
            case 'dispensed':
                $this->notificationService->sendPrescriptionNotification($prescription, 'dispensed');
                break;
                
            case 'completed':
                $this->notificationService->sendPrescriptionNotification($prescription, 'completed');
                break;
                
            case 'cancelled':
                $this->notificationService->sendPrescriptionNotification($prescription, 'cancelled');
                break;
                
            case 'expired':
                $this->notificationService->sendPrescriptionNotification($prescription, 'expired');
                break;
        }
    }

    /**
     * Handle the Prescription "deleted" event.
     */
    public function deleted(Prescription $prescription)
    {
        try {
            // Load relationships
            $prescription->load(['patient.user', 'doctor.user']);
            
            // Send prescription cancelled notification when deleted
            $this->notificationService->sendPrescriptionNotification($prescription, 'cancelled');
            
            Log::info('Prescription deleted notification sent', ['prescription_id' => $prescription->id]);
        } catch (\Exception $e) {
            Log::error('Failed to send prescription deleted notification', [
                'prescription_id' => $prescription->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
