<?php

namespace App\Observers;

use App\Models\MedicalRecord;
use App\Services\NotificationService;
use Illuminate\Support\Facades\Log;

class MedicalRecordObserver
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Handle the MedicalRecord "created" event.
     */
    public function created(MedicalRecord $medicalRecord)
    {
        try {
            // Load relationships
            $medicalRecord->load(['patient', 'doctor', 'appointment']);
            
            // Send medical record created notification
            $this->notificationService->sendMedicalRecordNotification($medicalRecord, 'created');
            
            Log::info('Medical record created notification sent', [
                'medical_record_id' => $medicalRecord->id,
                'patient_id' => $medicalRecord->patient_id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send medical record created notification', [
                'medical_record_id' => $medicalRecord->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Handle the MedicalRecord "updated" event.
     */
    public function updated(MedicalRecord $medicalRecord)
    {
        try {
            // Load relationships
            $medicalRecord->load(['patient.user', 'doctor.user', 'appointment']);
            
            // Check what was updated and send appropriate notifications
            if ($this->hasSignificantChanges($medicalRecord)) {
                $this->notificationService->sendMedicalRecordNotification($medicalRecord, 'updated');
                
                Log::info('Medical record updated notification sent', [
                    'medical_record_id' => $medicalRecord->id,
                    'patient_id' => $medicalRecord->patient_id
                ]);
            }
            
            // Check for follow-up requirements
            if ($medicalRecord->wasChanged('follow_up_date') && $medicalRecord->follow_up_date) {
                $this->notificationService->sendMedicalRecordNotification($medicalRecord, 'follow_up_required');
                
                Log::info('Follow-up required notification sent', [
                    'medical_record_id' => $medicalRecord->id,
                    'follow_up_date' => $medicalRecord->follow_up_date
                ]);
            }
            
        } catch (\Exception $e) {
            Log::error('Failed to send medical record updated notification', [
                'medical_record_id' => $medicalRecord->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Check if the medical record has significant changes that warrant notification
     */
    protected function hasSignificantChanges(MedicalRecord $medicalRecord): bool
    {
        $significantFields = [
            'diagnosis',
            'assessment',
            'plan',
            'treatment',
            'physical_examination',
            'vital_signs'
        ];

        foreach ($significantFields as $field) {
            if ($medicalRecord->wasChanged($field)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Handle the MedicalRecord "deleted" event.
     */
    public function deleted(MedicalRecord $medicalRecord)
    {
        try {
            // Load relationships
            $medicalRecord->load(['patient.user', 'doctor.user']);
            
            // Send medical record deleted notification (if needed)
            // This might be rare, but could be useful for audit purposes
            Log::info('Medical record deleted', [
                'medical_record_id' => $medicalRecord->id,
                'patient_id' => $medicalRecord->patient_id
            ]);
        } catch (\Exception $e) {
            Log::error('Error handling medical record deletion', [
                'medical_record_id' => $medicalRecord->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}
