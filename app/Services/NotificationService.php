<?php

namespace App\Services;

use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Models\Billing;
use App\Models\LabReport;
use App\Models\UserNotification;
use App\Models\PatientNotification;
use App\Services\EmailNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\URL;

class NotificationService
{
    protected $emailNotificationService;

    public function __construct(EmailNotificationService $emailNotificationService)
    {
        $this->emailNotificationService = $emailNotificationService;
    }

    /**
     * Send a notification for an appointment event.
     *
     * @param Appointment $appointment
     * @param string $eventType 'created', 'updated', 'cancelled', 'reminder'
     * @return void
     */
    public function sendAppointmentNotification(Appointment $appointment, string $eventType = 'created')
    {
        $notificationData = $this->getAppointmentNotificationData($appointment, $eventType);

        // Notify Patient - Use PatientNotification since patients have separate auth
        $this->createPatientNotification($appointment->patient, array_merge($notificationData['patient'], [
            'related_appointment_id' => $appointment->id,
            'related_patient_id' => $appointment->patient_id,
            'related_doctor_id' => $appointment->doctor_id,
        ]));

        // Notify Doctor - Use UserNotification for staff
        if ($appointment->doctor && $appointment->doctor->user_id) {
            $doctor = User::find($appointment->doctor->user_id);
            if ($doctor) {
                $this->createNotification($doctor, array_merge($notificationData['doctor'], [
                    'related_appointment_id' => $appointment->id,
                    'related_patient_id' => $appointment->patient_id,
                    'related_doctor_id' => $appointment->doctor_id,
                ]));
            }
        }

        Log::info("Appointment notification sent for event: {$eventType}", ['appointment_id' => $appointment->id]);
    }

    /**
     * Send a notification for an appointment deletion event.
     * This method doesn't reference the appointment ID to avoid foreign key constraint issues.
     *
     * @param Appointment $appointment
     * @return void
     */
    public function sendAppointmentDeletionNotification(Appointment $appointment)
    {
        $notificationData = $this->getAppointmentNotificationData($appointment, 'cancelled');

        // Notify Patient - Use PatientNotification but don't set related_appointment_id since it's being deleted
        $this->createPatientNotification($appointment->patient, array_merge($notificationData['patient'], [
            // DON'T set related_appointment_id since the appointment is being deleted
            'related_patient_id' => $appointment->patient_id,
            'related_doctor_id' => $appointment->doctor_id,
            'data' => json_encode([
                'deleted_appointment_id' => $appointment->id, // Store for reference but don't use as foreign key
                'appointment_date' => $appointment->appointment_date,
                'appointment_time' => $appointment->appointment_time,
                'doctor_name' => $appointment->doctor->first_name . ' ' . $appointment->doctor->last_name,
            ])
        ]));

        // Notify Doctor - Use UserNotification for staff but don't set related_appointment_id
        if ($appointment->doctor && $appointment->doctor->user_id) {
            $doctor = User::find($appointment->doctor->user_id);
            if ($doctor) {
                $this->createNotification($doctor, array_merge($notificationData['doctor'], [
                    // DON'T set related_appointment_id since the appointment is being deleted
                    'related_patient_id' => $appointment->patient_id,
                    'related_doctor_id' => $appointment->doctor_id,
                    'data' => json_encode([
                        'deleted_appointment_id' => $appointment->id, // Store for reference
                        'patient_name' => $appointment->patient->first_name . ' ' . $appointment->patient->last_name,
                        'appointment_date' => $appointment->appointment_date,
                        'appointment_time' => $appointment->appointment_time,
                    ])
                ]));
            }
        }

        Log::info("Appointment deletion notification sent", ['appointment_id' => $appointment->id]);
    }

    /**
     * Send a notification for a prescription event.
     *
     * @param Prescription $prescription
     * @param string $eventType 'created', 'updated', 'approved', 'dispensed', 'completed', 'cancelled', 'expired', 'refill_due'
     * @return void
     */
    public function sendPrescriptionNotification(Prescription $prescription, string $eventType = 'created')
    {
        $notificationData = $this->getPrescriptionNotificationData($prescription, $eventType);

        // Notify Patient - Use PatientNotification
        $this->createPatientNotification($prescription->patient, array_merge($notificationData['patient'], [
            'related_appointment_id' => $prescription->appointment_id,
            'related_patient_id' => $prescription->patient_id,
            'related_doctor_id' => $prescription->doctor_id,
        ]));

        Log::info("Prescription notification sent for event: {$eventType}", ['prescription_id' => $prescription->id]);
    }

    /**
     * Send a notification for a new lab result.
     *
     * @param LabReport $labReport
     * @param string $eventType
     * @return void
     */
    public function sendLabResultNotification(LabReport $labReport, string $eventType = 'completed')
    {
        // Create patient notification instead of user notification since patients have separate auth
        $this->createPatientNotification($labReport->patient, [
            'type' => PatientNotification::TYPE_LAB_RESULT,
            'category' => PatientNotification::CATEGORY_MEDICAL,
            'title' => 'Lab Results Available',
            'message' => "Your lab results for '{$labReport->test_name}' are now available for review.",
            'action_url' => '/patient/medical-records',
            'priority' => 'high',
            'data' => ['lab_report_id' => $labReport->id],
            'related_patient_id' => $labReport->patient_id,
            'related_doctor_id' => $labReport->doctor_id,
        ]);

        Log::info('Lab result patient notification sent', ['lab_report_id' => $labReport->id]);
    }

    /**
     * Send a medical record notification.
     *
     * @param MedicalRecord $medicalRecord
     * @param string $eventType 'created', 'updated', 'follow_up_required'
     * @return void
     */
    public function sendMedicalRecordNotification($medicalRecord, string $eventType = 'created')
    {
        $notificationData = $this->getMedicalRecordNotificationData($medicalRecord, $eventType);

        // Notify Patient - Use PatientNotification
        $this->createPatientNotification($medicalRecord->patient, array_merge($notificationData['patient'], [
            'related_appointment_id' => $medicalRecord->appointment_id,
            'related_patient_id' => $medicalRecord->patient_id,
            'related_doctor_id' => $medicalRecord->doctor_id,
        ]));

        Log::info("Medical record notification sent for event: {$eventType}", ['medical_record_id' => $medicalRecord->id]);
    }

    /**
     * Send a billing notification (e.g., new invoice, payment reminder).
     *
     * @param Billing $billing
     * @param string $eventType 'invoice_created', 'payment_due', 'payment_received'
     * @param float|null $amount Optional payment amount for payment_received events
     * @return void
     */
    public function sendBillingNotification(Billing $billing, string $eventType = 'invoice_created', $amount = null)
    {
        // Determine who should receive the notification based on event type
        if (in_array($eventType, ['payment_received', 'payment_completed'])) {
            // Payment-related events should notify admins
            $this->notifyAdminsForPayment($billing, $eventType, $amount);
        } else {
            // Invoice-related events should notify patients
            $this->notifyPatientForInvoice($billing, $eventType, $amount);
        }

        Log::info("Billing notification sent for event: {$eventType}", [
            'billing_id' => $billing->id,
            'event_type' => $eventType,
            'amount' => $amount
        ]);
    }

    /**
     * Create a notification record in the database.
     *
     * @param User $user
     * @param array $data
     * @return UserNotification
     */
    protected function createNotification(User $user, array $data): UserNotification
    {
        return UserNotification::create(array_merge([
            'user_id' => $user->id,
            'priority' => 'medium',
            'notification_channel' => UserNotification::CHANNEL_WEB,
        ], $data));
    }

    /**
     * Create a patient notification record in the database.
     *
     * @param Patient $patient
     * @param array $data
     * @return PatientNotification
     */
    protected function createPatientNotification($patient, array $data)
    {
        return PatientNotification::create(array_merge([
            'patient_id' => $patient->id,
            'priority' => 'medium',
            'notification_channel' => PatientNotification::CHANNEL_WEB,
        ], $data));
    }

    /**
     * Notify admins about payment-related events.
     *
     * @param Billing $billing
     * @param string $eventType
     * @param float|null $amount
     * @return void
     */
    protected function notifyAdminsForPayment(Billing $billing, string $eventType, $amount = null)
    {
        // Get admin notification data
        $notificationData = $this->getAdminBillingNotificationData($billing, $eventType, $amount);

        // Get all admin users using the proper scope and fields
        $adminUsers = User::where(function($query) {
            $query->where('is_admin', true)
                  ->orWhere('role', 'admin');
        })
        ->where('is_active', true)
        ->get();

        if ($adminUsers->isEmpty()) {
            Log::warning('No admin users found for payment notification', [
                'billing_id' => $billing->id,
                'event_type' => $eventType
            ]);
            return;
        }

        // Send notification to each admin
        foreach ($adminUsers as $admin) {
            $this->createNotification($admin, array_merge($notificationData, [
                'related_patient_id' => $billing->patient_id,
            ]));
        }
    }

    /**
     * Notify patient about invoice-related events.
     *
     * @param Billing $billing
     * @param string $eventType
     * @param float|null $amount
     * @return void
     */
    protected function notifyPatientForInvoice(Billing $billing, string $eventType, $amount = null)
    {
        // Patients use their own authentication model, not Users
        $patient = $billing->patient;
        $notificationData = $this->getBillingNotificationData($billing, $eventType, $amount);

        // Create notification directly for the patient
        $this->createPatientNotification($patient, array_merge($notificationData, [
            'related_patient_id' => $billing->patient_id,
        ]));

        Log::info('Patient billing notification created', [
            'billing_id' => $billing->id,
            'patient_id' => $patient->id,
            'event_type' => $eventType
        ]);
    }

    /**
     * Get billing notification data
     */
    protected function getBillingNotificationData(Billing $billing, string $eventType, $amount = null): array
    {
        $messages = [
            'invoice_created' => [
                'type' => PatientNotification::TYPE_BILLING,
                'category' => PatientNotification::CATEGORY_BILLING,
                'title' => 'New Invoice Available',
                'message' => "A new invoice of $" . number_format($billing->total_amount, 2) . " has been generated for your recent visit.",
                'action_url' => '/patient/billing',
                'priority' => 'medium',
            ],
            'payment_due' => [
                'type' => PatientNotification::TYPE_BILLING,
                'category' => PatientNotification::CATEGORY_BILLING,
                'title' => 'Payment Due Reminder',
                'message' => "Your payment of $" . number_format($billing->balance, 2) . " is due. Please make payment to avoid late fees.",
                'action_url' => '/patient/billing',
                'priority' => 'high',
            ],
        ];

        return $messages[$eventType] ?? $messages['invoice_created'];
    }

    /**
     * Get admin billing notification data
     */
    protected function getAdminBillingNotificationData(Billing $billing, string $eventType, $amount = null): array
    {
        $messages = [
            'payment_received' => [
                'type' => UserNotification::TYPE_BILLING,
                'category' => UserNotification::CATEGORY_BILLING,
                'title' => 'Payment Received',
                'message' => "Payment of $" . number_format($amount ?? $billing->paid_amount, 2) . " received from " . $billing->patient->name,
                'action_url' => '/admin/billing/' . $billing->id,
                'priority' => 'medium',
            ],
        ];

        return $messages[$eventType] ?? $messages['payment_received'];
    }

    /**
     * Get medical record notification data
     */
    protected function getMedicalRecordNotificationData($medicalRecord, string $eventType): array
    {
        $doctorName = $medicalRecord->doctor ? $medicalRecord->doctor->first_name . ' ' . $medicalRecord->doctor->last_name : 'Medical Team';
        
        $patientMessages = [
            'created' => [
                'type' => PatientNotification::TYPE_MEDICAL_RECORD,
                'category' => PatientNotification::CATEGORY_MEDICAL,
                'title' => 'New Medical Record',
                'message' => "A new medical record has been added to your file by Dr. {$doctorName}.",
                'action_url' => '/patient/medical-records',
                'priority' => 'medium',
            ],
            'updated' => [
                'type' => PatientNotification::TYPE_MEDICAL_RECORD,
                'category' => PatientNotification::CATEGORY_MEDICAL,
                'title' => 'Medical Record Updated',
                'message' => "Your medical record has been updated by Dr. {$doctorName}.",
                'action_url' => '/patient/medical-records',
                'priority' => 'medium',
            ],
            'follow_up_required' => [
                'type' => PatientNotification::TYPE_MEDICAL_RECORD,
                'category' => PatientNotification::CATEGORY_MEDICAL,
                'title' => 'Follow-up Required',
                'message' => "Dr. {$doctorName} has scheduled a follow-up for your recent visit.",
                'action_url' => '/patient/appointments',
                'priority' => 'high',
            ],
        ];

        return ['patient' => $patientMessages[$eventType] ?? $patientMessages['created']];
    }

    /**
     * Get prescription notification data
     */
    protected function getPrescriptionNotificationData(Prescription $prescription, string $eventType): array
    {
        $doctorName = $prescription->doctor ? $prescription->doctor->first_name . ' ' . $prescription->doctor->last_name : 'Your Doctor';
        
        $patientMessages = [
            'created' => [
                'type' => PatientNotification::TYPE_PRESCRIPTION,
                'category' => PatientNotification::CATEGORY_MEDICAL,
                'title' => 'New Prescription',
                'message' => "Dr. {$doctorName} has prescribed new medication for you.",
                'action_url' => '/patient/medical-records',
                'priority' => 'high',
            ],
            'dispensed' => [
                'type' => PatientNotification::TYPE_PRESCRIPTION,
                'category' => PatientNotification::CATEGORY_MEDICAL,
                'title' => 'Prescription Ready',
                'message' => "Your prescription is ready for pickup at the pharmacy.",
                'action_url' => '/patient/medical-records',
                'priority' => 'high',
            ],
        ];

        return ['patient' => $patientMessages[$eventType] ?? $patientMessages['created']];
    }

    /**
     * Helper methods to generate notification content
     */
    protected function getAppointmentNotificationData(Appointment $appointment, string $eventType): array
    {
        $patientName = $appointment->patient->first_name . ' ' . $appointment->patient->last_name;
        $doctorName = $appointment->doctor ? $appointment->doctor->first_name . ' ' . $appointment->doctor->last_name : 'Doctor';
        $appointmentDateTime = $appointment->appointment_date->format('F j, Y') . ' at ' . $appointment->appointment_time;

        $patientMessages = [
            'created' => [
                'title' => 'Appointment Booked',
                'message' => "Your appointment with Dr. {$doctorName} on {$appointmentDateTime} has been booked and is pending confirmation.",
                'action_url' => '/patient/appointments',
                'priority' => 'medium',
            ],
            'confirmed' => [
                'title' => 'Appointment Confirmed',
                'message' => "Your appointment with Dr. {$doctorName} on {$appointmentDateTime} has been confirmed.",
                'action_url' => '/patient/appointments',
                'priority' => 'high',
            ],
            'cancelled' => [
                'title' => 'Appointment Cancelled',
                'message' => "Your appointment with Dr. {$doctorName} on {$appointmentDateTime} has been cancelled.",
                'action_url' => '/patient/appointments',
                'priority' => 'high',
            ],
        ];

        $doctorMessages = [
            'created' => [
                'type' => UserNotification::TYPE_APPOINTMENT,
                'category' => UserNotification::CATEGORY_APPOINTMENT,
                'title' => 'New Appointment',
                'message' => "New appointment with {$patientName} on {$appointmentDateTime}",
                'action_url' => '/admin/appointments/' . $appointment->id,
                'priority' => 'medium',
            ],
            'confirmed' => [
                'type' => UserNotification::TYPE_APPOINTMENT,
                'category' => UserNotification::CATEGORY_APPOINTMENT,
                'title' => 'Appointment Confirmed',
                'message' => "Appointment with {$patientName} on {$appointmentDateTime} has been confirmed",
                'action_url' => '/admin/appointments/' . $appointment->id,
                'priority' => 'high',
            ],
            'cancelled' => [
                'type' => UserNotification::TYPE_APPOINTMENT,
                'category' => UserNotification::CATEGORY_APPOINTMENT,
                'title' => 'Appointment Cancelled',
                'message' => "Appointment with {$patientName} on {$appointmentDateTime} has been cancelled",
                'action_url' => '/admin/appointments',
                'priority' => 'high',
            ],
        ];

        return [
            'patient' => array_merge($patientMessages[$eventType] ?? $patientMessages['created'], [
                'type' => PatientNotification::TYPE_APPOINTMENT, 
                'category' => PatientNotification::CATEGORY_APPOINTMENT,
                'data' => ['appointment_id' => $appointment->id, 'status' => $appointment->status]]),
            'doctor' => $doctorMessages[$eventType] ?? $doctorMessages['created']
        ];
    }
}
