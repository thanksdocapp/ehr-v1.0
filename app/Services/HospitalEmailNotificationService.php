<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\EmailLog;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\LabReport;
use App\Models\ContactMessage;
use App\Jobs\SendEmail;
use App\Services\EmailNotificationService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Exception;

class HospitalEmailNotificationService
{
    protected $emailService;

    public function __construct(EmailNotificationService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Send appointment confirmation email to patient.
     *
     * @param Appointment $appointment
     * @return EmailLog|null
     */
    public function sendAppointmentConfirmation(Appointment $appointment)
    {
        if (!$appointment->patient || !$appointment->patient->email) {
            Log::warning('Cannot send appointment confirmation: Patient email not found', [
                'appointment_id' => $appointment->id
            ]);
            return null;
        }

        $doctor = $appointment->doctor;
        $patient = $appointment->patient;

        $variables = [
            'patient_name' => $patient->full_name,
            'patient_email' => $patient->email,
            'doctor_name' => $doctor ? $doctor->name : 'TBD',
            'doctor_specialization' => $doctor ? $doctor->specialization : 'General',
            'appointment_date' => $appointment->appointment_date->format('F d, Y'),
            'appointment_time' => $appointment->appointment_time,
            'appointment_type' => $appointment->type ?? 'Consultation',
            'department' => $appointment->department ? $appointment->department->name : 'General',
            'hospital_name' => config('app.name', 'Hospital'),
            'hospital_address' => config('hospital.address', ''),
            'hospital_phone' => config('hospital.phone', ''),
            'appointment_id' => $appointment->id,
            'notes' => $appointment->notes ?? 'Please arrive 15 minutes early.',
            'is_online' => $appointment->is_online ?? false,
            'meeting_link' => $appointment->meeting_link ?? null,
            'meeting_platform' => $appointment->meeting_platform_name ?? null,
            'join_meeting_url' => $appointment->meeting_link ?? null,
        ];

        return $this->emailService->sendTemplateEmail(
            'appointment_confirmation',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Send appointment reminder email to patient.
     *
     * @param Appointment $appointment
     * @param int $daysBefore Number of days before appointment
     * @return EmailLog|null
     */
    public function sendAppointmentReminder(Appointment $appointment, int $daysBefore = 1)
    {
        if (!$appointment->patient || !$appointment->patient->email) {
            return null;
        }

        $doctor = $appointment->doctor;
        $patient = $appointment->patient;

        $variables = [
            'patient_name' => $patient->full_name,
            'doctor_name' => $doctor ? $doctor->name : 'TBD',
            'appointment_date' => $appointment->appointment_date->format('F d, Y'),
            'appointment_time' => $appointment->appointment_time,
            'days_before' => $daysBefore,
            'hospital_name' => config('app.name', 'Hospital'),
            'appointment_id' => $appointment->id,
            'reschedule_url' => url('/patient/appointments/' . $appointment->id . '/reschedule'),
            'cancel_url' => url('/patient/appointments/' . $appointment->id . '/cancel'),
            'is_online' => $appointment->is_online ?? false,
            'meeting_link' => $appointment->meeting_link ?? null,
            'meeting_platform' => $appointment->meeting_platform_name ?? null,
            'join_meeting_url' => $appointment->meeting_link ?? null,
        ];

        return $this->emailService->sendTemplateEmail(
            'appointment_reminder',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Send welcome email to new patient.
     *
     * @param Patient $patient
     * @return EmailLog|null
     */
    public function sendPatientWelcomeEmail(Patient $patient)
    {
        if (!$patient->email) {
            return null;
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'patient_id' => $patient->id,
            'registration_date' => $patient->created_at->format('F d, Y'),
            'hospital_name' => config('app.name', 'Hospital'),
            'patient_portal_url' => url('/patient/login'),
            'contact_email' => config('mail.from.address'),
            'contact_phone' => config('hospital.phone', ''),
            'services_offered' => 'Comprehensive healthcare services',
        ];

        return $this->emailService->sendTemplateEmail(
            'patient_welcome',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Send test results ready notification to patient.
     *
     * @param array $labResult
     * @param Patient $patient
     * @return EmailLog|null
     */
    public function sendTestResultsReady($labResult, Patient $patient)
    {
        if (!$patient->email) {
            return null;
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'test_name' => $labResult['test_name'] ?? ($labResult['test_type'] ?? 'Medical Test'),
            'test_type' => $labResult['test_type'] ?? ($labResult['test_name'] ?? 'Medical Test'),
            'test_date' => $labResult['test_date'] ?? date('F d, Y'),
            'result_date' => date('F d, Y'),
            'hospital_name' => config('app.name', 'Hospital'),
            'patient_portal_url' => url('/patient/lab-reports'),
            'contact_phone' => config('hospital.phone', ''),
            'doctor_name' => $labResult['doctor_name'] ?? 'Your Doctor',
        ];

        return $this->emailService->sendTemplateEmail(
            'test_results_ready',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Send discharge instructions to patient.
     *
     * @param Patient $patient
     * @param array $dischargeInfo
     * @return EmailLog|null
     */
    public function sendDischargeInstructions(Patient $patient, array $dischargeInfo)
    {
        if (!$patient->email) {
            return null;
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'discharge_date' => $dischargeInfo['discharge_date'] ?? date('F d, Y'),
            'attending_doctor' => $dischargeInfo['doctor_name'] ?? 'Your Doctor',
            'follow_up_date' => $dischargeInfo['follow_up_date'] ?? '',
            'instructions' => $dischargeInfo['instructions'] ?? 'Follow prescribed medications and rest.',
            'medications' => $dischargeInfo['medications'] ?? 'As prescribed',
            'emergency_contact' => config('hospital.emergency_phone', ''),
            'hospital_name' => config('app.name', 'Hospital'),
        ];

        return $this->emailService->sendTemplateEmail(
            'discharge_instructions',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Send prescription ready notification.
     *
     * @param Patient $patient
     * @param array $prescriptionInfo
     * @return EmailLog|null
     */
    public function sendPrescriptionReady(Patient $patient, array $prescriptionInfo)
    {
        if (!$patient->email) {
            return null;
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'prescription_id' => $prescriptionInfo['id'] ?? '',
            'doctor_name' => $prescriptionInfo['doctor_name'] ?? 'Your Doctor',
            'ready_date' => date('F d, Y'),
            'pickup_instructions' => 'Please bring a valid ID for prescription pickup.',
            'pharmacy_hours' => $prescriptionInfo['pharmacy_hours'] ?? '8 AM - 8 PM',
            'hospital_name' => config('app.name', 'Hospital'),
            'pharmacy_phone' => config('hospital.pharmacy_phone', ''),
        ];

        return $this->emailService->sendTemplateEmail(
            'prescription_ready',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Send payment reminder to patient.
     *
     * @param Patient $patient
     * @param array $billingInfo
     * @return EmailLog|null
     */
    public function sendPaymentReminder(Patient $patient, array $billingInfo)
    {
        if (!$patient->email) {
            return null;
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'invoice_number' => $billingInfo['invoice_number'] ?? '',
            'amount_due' => $billingInfo['amount_due'] ?? '0.00',
            'due_date' => $billingInfo['due_date'] ?? date('F d, Y', strtotime('+30 days')),
            'service_description' => $billingInfo['service_description'] ?? 'Medical Services',
            'payment_url' => $billingInfo['payment_url'] ?? url('/patient/billing'),
            'hospital_name' => config('app.name', 'Hospital'),
            'billing_phone' => config('hospital.billing_phone', ''),
        ];

        return $this->emailService->sendTemplateEmail(
            'payment_reminder',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Send billing/invoice notification to patient with payment link.
     *
     * @param \App\Models\Billing $billing
     * @param string|null $paymentUrl Public payment URL (if null, will use patient portal)
     * @return EmailLog|null
     */
    public function sendBillingNotification(\App\Models\Billing $billing, ?string $paymentUrl = null)
    {
        try {
            // Refresh billing to ensure we have the latest data and relationships
            $billing->refresh();
            $billing->load(['patient', 'doctor', 'invoice']);
            
            $patient = $billing->patient;
            $invoice = $billing->invoice;

            if (!$patient || !$patient->email) {
                \Log::warning('Cannot send billing notification: Patient email not found', [
                    'billing_id' => $billing->id,
                    'patient_id' => $billing->patient_id
                ]);
                return null;
            }

            // Ensure invoice exists - sync it if it doesn't
            if (!$invoice) {
                \Log::info('Invoice not found for billing, syncing invoice', [
                    'billing_id' => $billing->id
                ]);
                $billing->syncWithInvoice();
                $billing->refresh();
                $invoice = $billing->invoice;
            }

            // Generate payment URL - use public payment link if invoice exists, otherwise patient portal
            if (!$paymentUrl) {
                if ($invoice) {
                    try {
                        // Ensure payment token exists
                        if (!$invoice->payment_token) {
                            $invoice->generatePaymentToken();
                            $invoice->refresh();
                        }
                        $paymentUrl = $invoice->getPublicPaymentUrl();
                        \Log::info('Generated public payment URL', [
                            'billing_id' => $billing->id,
                            'invoice_id' => $invoice->id,
                            'has_token' => !empty($invoice->payment_token)
                        ]);
                    } catch (\Exception $e) {
                        \Log::warning('Failed to generate payment URL, using patient portal', [
                            'billing_id' => $billing->id,
                            'invoice_id' => $invoice->id ?? null,
                            'error' => $e->getMessage()
                        ]);
                        $paymentUrl = url('/patient/billing');
                    }
                } else {
                    \Log::warning('No invoice found, using patient portal URL', [
                        'billing_id' => $billing->id
                    ]);
                    $paymentUrl = url('/patient/billing');
                }
            }

            $variables = [
                'patient_name' => $patient->full_name,
                'bill_number' => $billing->bill_number,
                'invoice_number' => $invoice ? $invoice->invoice_number : $billing->bill_number,
                'billing_date' => $billing->billing_date->format('F d, Y'),
                'due_date' => $billing->due_date ? $billing->due_date->format('F d, Y') : 'N/A',
                'total_amount' => number_format($billing->total_amount, 2),
                'balance' => number_format($billing->balance, 2),
                'description' => $billing->description,
                'type' => $billing->type_display ?? ucfirst($billing->type),
                'doctor_name' => $billing->doctor ? ($billing->doctor->name ?? $billing->doctor->full_name ?? 'N/A') : 'N/A',
                'payment_url' => $paymentUrl,
                'hospital_name' => config('app.name', 'Hospital'),
                'hospital_address' => config('hospital.address', ''),
                'hospital_phone' => config('hospital.phone', ''),
                'billing_phone' => config('hospital.billing_phone', config('hospital.phone', '')),
                'notes' => $billing->notes ?? '',
            ];

            \Log::info('Sending billing notification email', [
                'billing_id' => $billing->id,
                'patient_email' => $patient->email,
                'payment_url' => $paymentUrl
            ]);

            // Ensure email template exists, create if it doesn't
            $this->ensureBillingNotificationTemplate();

            $result = $this->emailService->sendTemplateEmail(
                'billing_notification',
                [$patient->email => $patient->full_name],
                $variables
            );

            if ($result) {
                // Check if email was actually sent or failed
                $result->refresh();
                $emailStatus = $result->status;
                
                if ($emailStatus === 'sent') {
                    \Log::info('Billing notification email sent successfully', [
                        'billing_id' => $billing->id,
                        'patient_email' => $patient->email,
                        'email_log_id' => $result->id,
                        'status' => $emailStatus,
                        'sent_at' => $result->sent_at
                    ]);
                } else {
                    \Log::warning('Billing notification email status is not "sent"', [
                        'billing_id' => $billing->id,
                        'patient_email' => $patient->email,
                        'email_log_id' => $result->id,
                        'status' => $emailStatus,
                        'error_message' => $result->error_message
                    ]);
                }
            } else {
                \Log::error('Billing notification email failed to send - sendTemplateEmail returned null', [
                    'billing_id' => $billing->id,
                    'patient_email' => $patient->email,
                    'possible_causes' => [
                        'Email template not found',
                        'SMTP configuration error',
                        'Exception during email sending'
                    ]
                ]);
            }

            return $result;
        } catch (\Exception $e) {
            \Log::error('Exception in sendBillingNotification', [
                'billing_id' => $billing->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e; // Re-throw to let caller handle it
        }
    }

    /**
     * Ensure billing_notification email template exists, create if missing
     */
    protected function ensureBillingNotificationTemplate()
    {
        $templateName = 'billing_notification';
        $template = \App\Models\EmailTemplate::where('name', $templateName)->first();
        
        if (!$template) {
            \Log::info('Creating missing billing_notification email template');
            
            $template = \App\Models\EmailTemplate::create([
                'name' => $templateName,
                'subject' => 'New Invoice from {{hospital_name}} - {{bill_number}}',
                'body' => '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Billing Notification</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background-color: #f8f9fc; padding: 20px; border-radius: 8px; margin-bottom: 20px;">
        <h1 style="color: #1a202c; margin: 0;">{{hospital_name}}</h1>
    </div>
    
    <div style="background-color: #ffffff; padding: 30px; border: 1px solid #e2e8f0; border-radius: 8px;">
        <h2 style="color: #1a202c; margin-top: 0;">Invoice Notification</h2>
        
        <p>Dear {{patient_name}},</p>
        
        <p>We hope this message finds you well. This is to inform you that a new invoice has been generated for your recent visit.</p>
        
        <div style="background-color: #f8f9fc; padding: 20px; border-radius: 8px; margin: 20px 0;">
            <h3 style="color: #1a202c; margin-top: 0;">Invoice Details</h3>
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 8px 0; color: #4a5568;"><strong>Invoice Number:</strong></td>
                    <td style="padding: 8px 0; color: #1a202c;"><strong>{{invoice_number}}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #4a5568;">Bill Number:</td>
                    <td style="padding: 8px 0; color: #1a202c;">{{bill_number}}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #4a5568;">Billing Date:</td>
                    <td style="padding: 8px 0; color: #1a202c;">{{billing_date}}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #4a5568;">Due Date:</td>
                    <td style="padding: 8px 0; color: #1a202c;">{{due_date}}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #4a5568;">Service Type:</td>
                    <td style="padding: 8px 0; color: #1a202c;">{{type}}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #4a5568;">Description:</td>
                    <td style="padding: 8px 0; color: #1a202c;">{{description}}</td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #4a5568;"><strong>Total Amount:</strong></td>
                    <td style="padding: 8px 0; color: #1a202c; font-size: 18px;"><strong>£{{total_amount}}</strong></td>
                </tr>
                <tr>
                    <td style="padding: 8px 0; color: #4a5568;">Balance Due:</td>
                    <td style="padding: 8px 0; color: #1a202c;"><strong>£{{balance}}</strong></td>
                </tr>
            </table>
        </div>
        
        @if(isset($notes) && !empty($notes))
        <div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <p style="margin: 0; color: #856404;"><strong>Notes:</strong> {{notes}}</p>
        </div>
        @endif
        
        <div style="text-align: center; margin: 30px 0;">
            <a href="{{payment_url}}" style="display: inline-block; background-color: #1cc88a; color: #ffffff; padding: 15px 30px; text-decoration: none; border-radius: 8px; font-weight: bold; font-size: 16px;">Pay Invoice Online</a>
        </div>
        
        <p style="color: #4a5568; font-size: 14px;">You can pay this invoice securely online using the button above. No login required.</p>
        
        <p style="color: #4a5568; font-size: 14px;">If you have any questions about this invoice, please contact our billing department:</p>
        <ul style="color: #4a5568; font-size: 14px;">
            <li>Phone: {{billing_phone}}</li>
            <li>Address: {{hospital_address}}</li>
        </ul>
        
        <p style="margin-top: 30px;">Thank you for choosing {{hospital_name}}.</p>
        
        <p style="color: #4a5568; font-size: 12px; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0;">
            This is an automated message. Please do not reply to this email.
        </p>
    </div>
</body>
</html>',
                'variables' => json_encode([
                    'patient_name', 'bill_number', 'invoice_number', 'billing_date', 'due_date',
                    'total_amount', 'balance', 'description', 'type', 'doctor_name',
                    'payment_url', 'hospital_name', 'hospital_address', 'hospital_phone',
                    'billing_phone', 'notes'
                ]),
                'status' => 'active',
                'sender_email' => config('mail.from.address', config('mail.username')),
                'sender_name' => config('mail.from.name', config('app.name', 'Hospital')),
            ]);
            
            \Log::info('Created billing_notification email template', [
                'template_id' => $template->id
            ]);
        } elseif ($template->status !== 'active') {
            $template->update(['status' => 'active']);
            \Log::info('Activated billing_notification email template');
        }
        
        return $template;
    }

    /**
     * Send emergency contact notification.
     *
     * @param Patient $patient
     * @param array $emergencyInfo
     * @param string $contactEmail
     * @param string $contactName
     * @return EmailLog|null
     */
    public function sendEmergencyContactNotification(Patient $patient, array $emergencyInfo, string $contactEmail, string $contactName)
    {
        $variables = [
            'contact_name' => $contactName,
            'patient_name' => $patient->full_name,
            'emergency_type' => $emergencyInfo['type'] ?? 'Medical Emergency',
            'hospital_name' => config('app.name', 'Hospital'),
            'hospital_address' => config('hospital.address', ''),
            'hospital_phone' => config('hospital.phone', ''),
            'emergency_date' => date('F d, Y g:i A'),
            'attending_doctor' => $emergencyInfo['doctor_name'] ?? 'Emergency Team',
            'patient_condition' => $emergencyInfo['condition'] ?? 'Stable',
            'admission_date' => date('F d, Y'),
            'admission_time' => date('g:i A'),
            'department' => $emergencyInfo['department'] ?? 'Emergency Department',
            'room_number' => $emergencyInfo['room_number'] ?? 'Emergency Ward',
            'visiting_hours' => $emergencyInfo['visiting_hours'] ?? 'Please contact the hospital',
            'parking_info' => $emergencyInfo['parking_info'] ?? 'Parking available on site',
            'nurses_station_phone' => $emergencyInfo['nurses_station_phone'] ?? config('hospital.phone', ''),
            'patient_services_phone' => $emergencyInfo['patient_services_phone'] ?? config('hospital.phone', ''),
        ];

        return $this->emailService->sendTemplateEmail(
            'emergency_contact_notification',
            [$contactEmail => $contactName],
            $variables
        );
    }

    /**
     * Send notification to staff about new patient registration.
     *
     * @param Patient $patient
     * @param User $staff
     * @return EmailLog|null
     */
    public function notifyStaffNewPatientRegistration(Patient $patient, User $staff)
    {
        if (!$staff->email) {
            return null;
        }

        $variables = [
            'staff_name' => $staff->name,
            'patient_name' => $patient->full_name,
            'patient_id' => $patient->id,
            'registration_date' => $patient->created_at->format('F d, Y g:i A'),
            'patient_phone' => $patient->phone ?? 'Not provided',
            'patient_email' => $patient->email ?? 'Not provided',
            'hospital_name' => config('app.name', 'Hospital'),
            'patient_url' => url('/admin/patients/' . $patient->id),
        ];

        return $this->emailService->sendTemplateEmail(
            'staff_new_patient_registration',
            [$staff->email => $staff->name],
            $variables
        );
    }

    /**
     * Send notification to doctor about new appointment.
     *
     * @param Appointment $appointment
     * @param Doctor $doctor
     * @return EmailLog|null
     */
    public function notifyDoctorNewAppointment(Appointment $appointment, Doctor $doctor)
    {
        $user = $doctor->user;
        if (!$user || !$user->email) {
            return null;
        }

        $patient = $appointment->patient;

        $variables = [
            'doctor_name' => $doctor->name,
            'patient_name' => $patient->full_name,
            'patient_phone' => $patient->phone ?? 'Not provided',
            'appointment_date' => $appointment->appointment_date->format('F d, Y'),
            'appointment_time' => $appointment->appointment_time,
            'appointment_type' => $appointment->type ?? 'Consultation',
            'notes' => $appointment->notes ?? 'No additional notes',
            'hospital_name' => config('app.name', 'Hospital'),
            'appointment_url' => url('/admin/appointments/' . $appointment->id),
            'is_online' => $appointment->is_online ?? false,
            'meeting_link' => $appointment->meeting_link ?? null,
            'meeting_platform' => $appointment->meeting_platform_name ?? null,
        ];

        return $this->emailService->sendTemplateEmail(
            'doctor_new_appointment',
            [$user->email => $doctor->name],
            $variables
        );
    }

    /**
     * Get the appropriate queue for the email type
     *
     * @param string $emailType
     * @return string
     */
    protected function getQueueForEmailType(string $emailType): string
    {
        $queueMapping = [
            // High priority emails (emergencies, critical results)
            'emergency_contact_notification' => 'high-priority',
            'emergency_admission_alert' => 'high-priority',
            'critical_care_notification' => 'high-priority',
            'test_results_ready' => 'high-priority',
            'discharge_instructions' => 'high-priority',
            'doctor_unavailable' => 'high-priority',
            'significant_diagnosis_notification' => 'high-priority',
            
            // Regular emails
            'appointment_confirmation' => 'emails',
            'patient_welcome' => 'emails',
            'prescription_ready' => 'emails',
            'doctor_room_change' => 'emails',
            'doctor_contact_update' => 'emails',
            'doctor_department_change' => 'emails',
            'doctor_schedule_update' => 'emails',
            'medical_record_update' => 'emails',
            'treatment_plan_update' => 'emails',
            
            // Reminders (lower priority, can wait)
            'appointment_reminder' => 'reminders',
            'payment_reminder' => 'reminders',
        ];
        
        return $queueMapping[$emailType] ?? config('hospital.queue.email_notifications', 'emails');
    }

    /**
     * Queue email for sending with appropriate priority
     *
     * @param string $templateName
     * @param array $recipients
     * @param array $variables
     * @param string $queue
     * @param int $delay
     * @return mixed
     */
    public function queueEmail(string $templateName, array $recipients, array $variables, string $queue = null, int $delay = 0)
    {
        $queue = $queue ?? $this->getQueueForEmailType($templateName);
        
        Log::info('Queueing hospital email', [
            'template' => $templateName,
            'recipients_count' => count($recipients),
            'queue' => $queue,
            'delay' => $delay
        ]);
        
        // Here you would dispatch your email job with the specific queue
        // For now, we'll use the existing email service but with queue info
        return $this->emailService->sendTemplateEmail(
            $templateName,
            $recipients,
            $variables
        );
    }

    /**
     * Send immediate high-priority email (bypassing queue)
     *
     * @param string $templateName
     * @param array $recipients
     * @param array $variables
     * @return mixed
     */
    public function sendImmediateEmail(string $templateName, array $recipients, array $variables)
    {
        Log::info('Sending immediate hospital email', [
            'template' => $templateName,
            'recipients_count' => count($recipients)
        ]);
        
        return $this->emailService->sendTemplateEmail(
            $templateName,
            $recipients,
            $variables
        );
    }

    /**
     * Get queue statistics for monitoring
     *
     * @return array
     */
    public function getQueueStats(): array
    {
        try {
            $stats = [];
            $queues = ['high-priority', 'emails', 'reminders', 'default'];
            
            foreach ($queues as $queue) {
                $stats[$queue] = [
                    'pending' => Queue::size($queue),
                    'name' => $queue,
                    'description' => $this->getQueueDescription($queue)
                ];
            }
            
            return $stats;
        } catch (Exception $e) {
            Log::error('Failed to get queue statistics', ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Get queue description for monitoring
     *
     * @param string $queueName
     * @return string
     */
    protected function getQueueDescription(string $queueName): string
    {
        $descriptions = [
            'high-priority' => 'Urgent notifications (emergencies, critical results)',
            'emails' => 'Regular email notifications',
            'reminders' => 'Appointment and payment reminders',
            'default' => 'Default queue for miscellaneous emails'
        ];
        
        return $descriptions[$queueName] ?? 'Unknown queue';
    }

    /**
     * Send doctor room change notification to patient.
     *
     * @param string $patientEmail
     * @param string $patientName
     * @param string $doctorName
     * @param string $oldRoom
     * @param string $newRoom
     * @return EmailLog|null
     */
    public function sendDoctorRoomChangeNotification(string $patientEmail, string $patientName, string $doctorName, $oldRoom, $newRoom)
    {
        $variables = [
            'patient_name' => $patientName,
            'doctor_name' => $doctorName,
            'old_room' => $oldRoom ?? 'Not specified',
            'new_room' => $newRoom ?? 'Not specified',
            'hospital_name' => config('app.name', 'Hospital'),
            'contact_phone' => config('hospital.phone', ''),
            'notification_date' => date('F d, Y g:i A'),
        ];

        return $this->emailService->sendTemplateEmail(
            'doctor_room_change',
            [$patientEmail => $patientName],
            $variables
        );
    }

    /**
     * Send doctor contact update notification to patient.
     *
     * @param string $patientEmail
     * @param string $patientName
     * @param string $doctorName
     * @param string $newPhone
     * @return EmailLog|null
     */
    public function sendDoctorContactUpdateNotification(string $patientEmail, string $patientName, string $doctorName, $newPhone)
    {
        $variables = [
            'patient_name' => $patientName,
            'doctor_name' => $doctorName,
            'new_phone' => $newPhone ?? 'Not provided',
            'hospital_name' => config('app.name', 'Hospital'),
            'contact_phone' => config('hospital.phone', ''),
            'notification_date' => date('F d, Y g:i A'),
        ];

        return $this->emailService->sendTemplateEmail(
            'doctor_contact_update',
            [$patientEmail => $patientName],
            $variables
        );
    }

    /**
     * Send doctor department change notification to patient.
     *
     * @param string $patientEmail
     * @param string $patientName
     * @param string $doctorName
     * @param string $newDepartment
     * @return EmailLog|null
     */
    public function sendDoctorDepartmentChangeNotification(string $patientEmail, string $patientName, string $doctorName, $newDepartment)
    {
        $variables = [
            'patient_name' => $patientName,
            'doctor_name' => $doctorName,
            'new_department' => $newDepartment,
            'hospital_name' => config('app.name', 'Hospital'),
            'contact_phone' => config('hospital.phone', ''),
            'notification_date' => date('F d, Y g:i A'),
        ];

        return $this->emailService->sendTemplateEmail(
            'doctor_department_change',
            [$patientEmail => $patientName],
            $variables
        );
    }

    /**
     * Send doctor unavailable notification to patient.
     *
     * @param string $patientEmail
     * @param string $patientName
     * @param string $doctorName
     * @return EmailLog|null
     */
    public function sendDoctorUnavailableNotification(string $patientEmail, string $patientName, string $doctorName)
    {
        $variables = [
            'patient_name' => $patientName,
            'doctor_name' => $doctorName,
            'hospital_name' => config('app.name', 'Hospital'),
            'contact_phone' => config('hospital.phone', ''),
            'notification_date' => date('F d, Y g:i A'),
            'rebooking_url' => url('/patient/appointments'),
            'support_email' => config('mail.from.address'),
        ];

        return $this->emailService->sendTemplateEmail(
            'doctor_unavailable',
            [$patientEmail => $patientName],
            $variables
        );
    }

    /**
     * Send doctor schedule update notification to patient.
     *
     * @param string $patientEmail
     * @param string $patientName
     * @param string $doctorName
     * @param array $availability
     * @return EmailLog|null
     */
    public function sendDoctorScheduleUpdateNotification(string $patientEmail, string $patientName, string $doctorName, $availability)
    {
        // Format availability for email display
        $scheduleText = $this->formatAvailabilityForEmail($availability);
        
        $variables = [
            'patient_name' => $patientName,
            'doctor_name' => $doctorName,
            'new_schedule' => $scheduleText,
            'hospital_name' => config('app.name', 'Hospital'),
            'contact_phone' => config('hospital.phone', ''),
            'notification_date' => date('F d, Y g:i A'),
            'rebooking_url' => url('/patient/appointments'),
        ];

        return $this->emailService->sendTemplateEmail(
            'doctor_schedule_update',
            [$patientEmail => $patientName],
            $variables
        );
    }

    /**
     * Format availability array for email display.
     *
     * @param array|null $availability
     * @return string
     */
    private function formatAvailabilityForEmail($availability)
    {
        if (!$availability || !is_array($availability)) {
            return 'Schedule will be updated soon. Please contact the hospital for current availability.';
        }

        $schedule = [];
        $days = [
            'monday' => 'Monday',
            'tuesday' => 'Tuesday', 
            'wednesday' => 'Wednesday',
            'thursday' => 'Thursday',
            'friday' => 'Friday',
            'saturday' => 'Saturday',
            'sunday' => 'Sunday'
        ];

        foreach ($days as $key => $dayName) {
            if (isset($availability[$key]) && $availability[$key]['available']) {
                $times = $availability[$key]['times'] ?? [];
                if (!empty($times)) {
                    $timeSlots = implode(', ', $times);
                    $schedule[] = "$dayName: $timeSlots";
                } else {
                    $schedule[] = "$dayName: Available (times to be confirmed)";
                }
            }
        }

        return !empty($schedule) 
            ? implode("\n", $schedule)
            : 'Please contact the hospital to confirm availability.';
    }

    /**
     * Send emergency admission alert to critical staff.
     *
     * @param Patient $patient
     * @param array $admissionInfo
     * @param User $staffMember
     * @return EmailLog|null
     */
    public function sendEmergencyAdmissionAlert(Patient $patient, array $admissionInfo, User $staffMember)
    {
        if (!$staffMember->email) {
            return null;
        }

        $variables = [
            'staff_name' => $staffMember->name,
            'patient_name' => $patient->full_name,
            'patient_id' => $patient->id,
            'patient_age' => $patient->age ?? 'Unknown',
            'emergency_type' => $admissionInfo['emergency_type'] ?? 'Medical Emergency',
            'priority_level' => $admissionInfo['priority_level'] ?? 'High',
            'symptoms' => $admissionInfo['symptoms'] ?? 'Not specified',
            'admission_time' => date('F d, Y g:i A'),
            'attending_doctor' => $admissionInfo['doctor_name'] ?? 'Emergency Team',
            'room_assigned' => $admissionInfo['room_number'] ?? 'Emergency Ward',
            'vital_signs' => $this->formatVitalSignsForEmail($admissionInfo['vital_signs'] ?? []),
            'emergency_contact' => $admissionInfo['emergency_contact'] ?? 'Not provided',
            'hospital_name' => config('app.name', 'Hospital'),
            'patient_url' => url('/admin/patients/' . $patient->id),
            'medical_history' => $admissionInfo['medical_history'] ?? 'No known allergies or conditions',
        ];

        return $this->emailService->sendTemplateEmail(
            'emergency_admission_alert',
            [$staffMember->email => $staffMember->name],
            $variables
        );
    }

    /**
     * Send critical care notification to department heads and specialists.
     *
     * @param Patient $patient
     * @param array $admissionInfo
     * @param User $departmentHead
     * @return EmailLog|null
     */
    public function sendCriticalCareNotification(Patient $patient, array $admissionInfo, User $departmentHead)
    {
        if (!$departmentHead->email) {
            return null;
        }

        $variables = [
            'department_head_name' => $departmentHead->name,
            'patient_name' => $patient->full_name,
            'patient_id' => $patient->id,
            'emergency_type' => $admissionInfo['emergency_type'] ?? 'Critical Condition',
            'priority_level' => $admissionInfo['priority_level'] ?? 'Critical',
            'condition_summary' => $admissionInfo['condition_summary'] ?? 'Requires immediate attention',
            'admission_time' => date('F d, Y g:i A'),
            'attending_doctor' => $admissionInfo['doctor_name'] ?? 'Emergency Team',
            'department_name' => $admissionInfo['department_name'] ?? 'Emergency Department',
            'specialist_required' => $admissionInfo['specialist_required'] ?? 'General',
            'estimated_treatment_time' => $admissionInfo['estimated_treatment_time'] ?? 'Unknown',
            'hospital_name' => config('app.name', 'Hospital'),
            'patient_url' => url('/admin/patients/' . $patient->id),
            'emergency_protocol' => $admissionInfo['emergency_protocol'] ?? 'Standard emergency protocol in effect',
        ];

        return $this->emailService->sendTemplateEmail(
            'critical_care_notification',
            [$departmentHead->email => $departmentHead->name],
            $variables
        );
    }

    /**
     * Notify doctor when critical lab results are available.
     *
     * @param Doctor $doctor
     * @param LabReport $labReport
     * @return EmailLog|null
     */
    public function notifyDoctorCriticalResults(Doctor $doctor, LabReport $labReport)
    {
        $user = $doctor->user;
        if (!$user || !$user->email) {
            return null;
        }

        $patient = $labReport->patient;

        $variables = [
            'doctor_name' => $doctor->name,
            'patient_name' => $patient ? $patient->full_name : 'Patient',
            'test_name' => $labReport->test_name,
            'test_type' => $labReport->test_type,
            'test_date' => $labReport->test_date ? $labReport->test_date->format('F d, Y') : date('F d, Y'),
            'priority' => strtoupper($labReport->priority ?? 'urgent'),
            'status' => ucfirst($labReport->status),
            'lab_technician' => $labReport->lab_technician ?? 'Laboratory',
            'notes' => $labReport->notes ?? 'Please review and advise next steps.',
            'hospital_name' => config('app.name', 'Hospital'),
            'lab_report_url' => url('/admin/lab-reports/' . $labReport->id),
        ];

        return $this->emailService->sendTemplateEmail(
            'doctor_critical_results',
            [$user->email => $doctor->name],
            $variables
        );
    }

    /**
     * Send auto-reply to contact form submission.
     */
    public function sendContactAutoReply(ContactMessage $contactMessage)
    {
        if (!$contactMessage->email) {
            return null;
        }

        $variables = [
            'full_name' => trim($contactMessage->first_name . ' ' . $contactMessage->last_name),
            'subject' => $contactMessage->subject,
            'message' => $contactMessage->message,
            'hospital_name' => config('app.name', 'Hospital'),
            'contact_email' => config('mail.from.address'),
            'contact_phone' => config('hospital.phone'),
        ];

        return $this->emailService->sendTemplateEmail(
            'contact_auto_reply',
            [$contactMessage->email => $variables['full_name']],
            $variables
        );
    }

    /**
     * Notify staff of a new contact message.
     */
    public function notifyStaffNewContactMessage(ContactMessage $contactMessage, User $staff)
    {
        if (!$staff->email) {
            return null;
        }

        $variables = [
            'staff_name' => $staff->name,
            'full_name' => trim($contactMessage->first_name . ' ' . $contactMessage->last_name),
            'email' => $contactMessage->email,
            'phone' => $contactMessage->phone ?? 'Not provided',
            'subject' => $contactMessage->subject,
            'message' => $contactMessage->message,
            'hospital_name' => config('app.name', 'Hospital'),
            'inbox_url' => url('/admin/contact-messages/' . $contactMessage->id),
        ];

        return $this->emailService->sendTemplateEmail(
            'staff_new_contact_message',
            [$staff->email => $staff->name],
            $variables
        );
    }

    /**
     * Send a reply to a contact message sender.
     */
    public function sendContactReply(ContactMessage $contactMessage, string $subject, string $message)
    {
        if (!$contactMessage->email) {
            return null;
        }

        $variables = [
            'full_name' => trim($contactMessage->first_name . ' ' . $contactMessage->last_name),
            'original_subject' => $contactMessage->subject,
            'reply_subject' => $subject,
            'reply_message' => $message,
            'hospital_name' => config('app.name', 'Hospital'),
            'support_email' => config('mail.from.address'),
            'support_phone' => config('hospital.phone'),
        ];

        return $this->emailService->sendTemplateEmail(
            'contact_reply',
            [$contactMessage->email => $variables['full_name']],
            $variables
        );
    }

    /**
     * Send medical record update notification to patient.
     *
     * @param Patient $patient
     * @param MedicalRecord $medicalRecord
     * @param array $updateInfo
     * @return EmailLog|null
     */
    public function sendMedicalRecordUpdateNotification(Patient $patient, $medicalRecord, array $updateInfo)
    {
        if (!$patient->email) {
            return null;
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'record_date' => $medicalRecord->record_date ? $medicalRecord->record_date->format('F d, Y') : date('F d, Y'),
            'record_type' => ucfirst($medicalRecord->record_type),
            'doctor_name' => $updateInfo['doctor_name'] ?? 'Your Doctor',
            'update_type' => $updateInfo['update_type'] ?? 'Record Updated',
            'changes_summary' => $updateInfo['changes_summary'] ?? 'Your medical record has been updated with new information.',
            'diagnosis' => $medicalRecord->diagnosis ?? 'Not specified',
            'treatment_plan' => $medicalRecord->treatment ?? 'Not specified',
            'follow_up_required' => $medicalRecord->follow_up_date ? 'Yes - ' . $medicalRecord->follow_up_date->format('F d, Y') : 'No',
            'update_date' => date('F d, Y g:i A'),
            'hospital_name' => config('app.name', 'Hospital'),
            'patient_portal_url' => url('/patient/medical-records'),
            'contact_phone' => config('hospital.phone', ''),
            'privacy_note' => 'This information is confidential and for your personal medical records only.',
        ];

        return $this->emailService->sendTemplateEmail(
            'medical_record_update',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Send significant diagnosis notification to patient.
     *
     * @param Patient $patient
     * @param array $diagnosisInfo
     * @return EmailLog|null
     */
    public function sendSignificantDiagnosisNotification(Patient $patient, array $diagnosisInfo)
    {
        if (!$patient->email) {
            return null;
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'diagnosis_title' => $diagnosisInfo['diagnosis'] ?? 'New Diagnosis',
            'doctor_name' => $diagnosisInfo['doctor_name'] ?? 'Your Doctor',
            'diagnosis_date' => $diagnosisInfo['diagnosis_date'] ?? date('F d, Y'),
            'condition_explanation' => $diagnosisInfo['explanation'] ?? 'Please schedule a consultation to discuss your diagnosis.',
            'treatment_options' => $diagnosisInfo['treatment_options'] ?? 'Treatment options will be discussed during your appointment.',
            'follow_up_instructions' => $diagnosisInfo['follow_up_instructions'] ?? 'Please schedule a follow-up appointment.',
            'urgency_level' => $diagnosisInfo['urgency_level'] ?? 'Standard',
            'next_steps' => $diagnosisInfo['next_steps'] ?? 'Contact our office to schedule your next appointment.',
            'hospital_name' => config('app.name', 'Hospital'),
            'appointment_scheduling_url' => url('/patient/appointments'),
            'support_phone' => config('hospital.phone', ''),
            'patient_portal_url' => url('/patient/medical-records'),
        ];

        return $this->emailService->sendTemplateEmail(
            'significant_diagnosis_notification',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Send treatment plan update notification to patient.
     *
     * @param Patient $patient
     * @param array $treatmentInfo
     * @return EmailLog|null
     */
    public function sendTreatmentPlanUpdateNotification(Patient $patient, array $treatmentInfo)
    {
        if (!$patient->email) {
            return null;
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'doctor_name' => $treatmentInfo['doctor_name'] ?? 'Your Doctor',
            'update_date' => date('F d, Y'),
            'treatment_changes' => $treatmentInfo['changes'] ?? 'Your treatment plan has been updated.',
            'new_medications' => $treatmentInfo['new_medications'] ?? 'No new medications prescribed.',
            'discontinued_medications' => $treatmentInfo['discontinued_medications'] ?? 'No medications discontinued.',
            'dosage_changes' => $treatmentInfo['dosage_changes'] ?? 'No dosage changes.',
            'special_instructions' => $treatmentInfo['special_instructions'] ?? 'Continue following your current care plan.',
            'next_appointment' => $treatmentInfo['next_appointment'] ?? 'Please schedule as needed.',
            'monitoring_requirements' => $treatmentInfo['monitoring_requirements'] ?? 'Follow standard monitoring procedures.',
            'hospital_name' => config('app.name', 'Hospital'),
            'pharmacy_phone' => config('hospital.pharmacy_phone', ''),
            'patient_portal_url' => url('/patient/prescriptions'),
            'emergency_instructions' => 'Contact emergency services if you experience severe side effects.',
        ];

        return $this->emailService->sendTemplateEmail(
            'treatment_plan_update',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Format vital signs array for email display.
     *
     * @param array $vitalSigns
     * @return string
     */
    private function formatVitalSignsForEmail(array $vitalSigns)
    {
        if (empty($vitalSigns)) {
            return 'No vital signs recorded.';
        }

        $formatted = [];
        $labels = [
            'blood_pressure' => 'Blood Pressure',
            'temperature' => 'Temperature',
            'pulse' => 'Pulse',
            'respiratory_rate' => 'Respiratory Rate',
            'oxygen_saturation' => 'Oxygen Saturation',
            'weight' => 'Weight',
            'height' => 'Height'
        ];

        foreach ($vitalSigns as $key => $value) {
            if (!empty($value)) {
                $label = $labels[$key] ?? ucwords(str_replace('_', ' ', $key));
                $formatted[] = "$label: $value";
            }
        }

        return !empty($formatted) ? implode("\n", $formatted) : 'No vital signs recorded.';
    }

    /**
     * Send appointment cancellation notification to patient.
     *
     * @param Appointment $appointment
     * @return EmailLog|null
     */
    public function sendAppointmentCancellation(Appointment $appointment)
    {
        if (!$appointment->patient || !$appointment->patient->email) {
            return null;
        }

        $doctor = $appointment->doctor;
        $patient = $appointment->patient;

        $variables = [
            'patient_name' => $patient->full_name,
            'doctor_name' => $doctor ? $doctor->name : 'TBD',
            'appointment_date' => $appointment->appointment_date->format('F d, Y'),
            'appointment_time' => $appointment->appointment_time,
            'department' => $appointment->department ? $appointment->department->name : 'General',
            'hospital_name' => config('app.name', 'Hospital'),
            'hospital_phone' => config('hospital.phone', ''),
            'reschedule_url' => url('/patient/appointments'),
            'cancellation_reason' => $appointment->notes ?? 'Appointment cancelled',
        ];

        return $this->emailService->sendTemplateEmail(
            'appointment_cancellation',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Send appointment completion notification to patient.
     *
     * @param Appointment $appointment
     * @return EmailLog|null
     */
    public function sendAppointmentCompletion(Appointment $appointment)
    {
        if (!$appointment->patient || !$appointment->patient->email) {
            return null;
        }

        $doctor = $appointment->doctor;
        $patient = $appointment->patient;

        $variables = [
            'patient_name' => $patient->full_name,
            'doctor_name' => $doctor ? $doctor->name : 'TBD',
            'appointment_date' => $appointment->appointment_date->format('F d, Y'),
            'appointment_time' => $appointment->appointment_time,
            'department' => $appointment->department ? $appointment->department->name : 'General',
            'hospital_name' => config('app.name', 'Hospital'),
            'diagnosis' => $appointment->diagnosis ?? 'No diagnosis recorded',
            'prescription' => $appointment->prescription ?? 'No prescription issued',
            'follow_up_instructions' => $appointment->follow_up_instructions ?? 'Please schedule a follow-up if needed.',
            'next_appointment_date' => $appointment->next_appointment_date ? $appointment->next_appointment_date->format('F d, Y') : 'Not scheduled',
        ];

        return $this->emailService->sendTemplateEmail(
            'appointment_completion',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Send appointment reschedule notification to patient.
     *
     * @param Appointment $appointment
     * @param string $oldDate
     * @param string $oldTime
     * @return EmailLog|null
     */
    public function sendAppointmentReschedule(Appointment $appointment, $oldDate, $oldTime)
    {
        if (!$appointment->patient || !$appointment->patient->email) {
            return null;
        }

        $doctor = $appointment->doctor;
        $patient = $appointment->patient;

        $variables = [
            'patient_name' => $patient->full_name,
            'doctor_name' => $doctor ? $doctor->name : 'TBD',
            'old_date' => $oldDate,
            'old_time' => $oldTime,
            'new_date' => $appointment->appointment_date->format('F d, Y'),
            'new_time' => $appointment->appointment_time,
            'department' => $appointment->department ? $appointment->department->name : 'General',
            'hospital_name' => config('app.name', 'Hospital'),
            'hospital_phone' => config('hospital.phone', ''),
            'reschedule_reason' => $appointment->notes ?? 'Appointment rescheduled',
        ];

        return $this->emailService->sendTemplateEmail(
            'appointment_reschedule',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Notify doctor about appointment cancellation.
     *
     * @param Appointment $appointment
     * @param Doctor $doctor
     * @return EmailLog|null
     */
    public function notifyDoctorAppointmentCancelled(Appointment $appointment, Doctor $doctor)
    {
        if (!$doctor->user || !$doctor->user->email) {
            return null;
        }

        $patient = $appointment->patient;

        $variables = [
            'doctor_name' => $doctor->name,
            'patient_name' => $patient ? $patient->full_name : 'Patient',
            'appointment_date' => $appointment->appointment_date->format('F d, Y'),
            'appointment_time' => $appointment->appointment_time,
            'department' => $appointment->department ? $appointment->department->name : 'General',
            'hospital_name' => config('app.name', 'Hospital'),
            'cancellation_reason' => $appointment->notes ?? 'Appointment cancelled',
        ];

        return $this->emailService->sendTemplateEmail(
            'doctor_appointment_cancelled',
            [$doctor->user->email => $doctor->name],
            $variables
        );
    }

    /**
     * Notify doctor about appointment rescheduling.
     *
     * @param Appointment $appointment
     * @param Doctor $doctor
     * @param string $oldDate
     * @param string $oldTime
     * @return EmailLog|null
     */
    public function notifyDoctorAppointmentRescheduled(Appointment $appointment, Doctor $doctor, $oldDate, $oldTime)
    {
        if (!$doctor->user || !$doctor->user->email) {
            return null;
        }

        $patient = $appointment->patient;

        $variables = [
            'doctor_name' => $doctor->name,
            'patient_name' => $patient ? $patient->full_name : 'Patient',
            'old_date' => $oldDate,
            'old_time' => $oldTime,
            'new_date' => $appointment->appointment_date->format('F d, Y'),
            'new_time' => $appointment->appointment_time,
            'department' => $appointment->department ? $appointment->department->name : 'General',
            'hospital_name' => config('app.name', 'Hospital'),
            'reschedule_reason' => $appointment->notes ?? 'Appointment rescheduled',
        ];

        return $this->emailService->sendTemplateEmail(
            'doctor_appointment_rescheduled',
            [$doctor->user->email => $doctor->name],
            $variables
        );
    }

    /**
     * Send email to patient's GP.
     *
     * @param Patient $patient
     * @param string $subject
     * @param string $message
     * @param string $emailType
     * @param User|null $sentBy
     * @return EmailLog|null
     */
    public function sendGpEmail(Patient $patient, string $subject, string $message, string $emailType = 'general', User $sentBy = null)
    {
        // Check if patient has GP consent and GP email
        if (!$patient->consent_share_with_gp) {
            Log::warning('Cannot send email to GP: Patient has not consented to share information with GP', [
                'patient_id' => $patient->id
            ]);
            throw new \Exception('Patient has not consented to share information with their GP.');
        }

        if (!$patient->gp_email) {
            Log::warning('Cannot send email to GP: GP email not found', [
                'patient_id' => $patient->id
            ]);
            throw new \Exception('GP email address is not available for this patient.');
        }

        $doctor = $sentBy && $sentBy->role === 'doctor' 
            ? Doctor::where('user_id', $sentBy->id)->first() 
            : null;

        $variables = [
            'gp_name' => $patient->gp_name ?? 'GP',
            'gp_email' => $patient->gp_email,
            'gp_phone' => $patient->gp_phone ?? '',
            'gp_address' => $patient->gp_address ?? '',
            'patient_name' => $patient->full_name,
            'patient_id' => $patient->patient_id,
            'patient_dob' => $patient->date_of_birth ? $patient->date_of_birth->format('F d, Y') : 'N/A',
            'doctor_name' => $doctor ? $doctor->name : ($sentBy ? $sentBy->name : 'Hospital Staff'),
            'doctor_specialization' => $doctor ? $doctor->specialization : 'General',
            'hospital_name' => config('app.name', 'Hospital'),
            'hospital_address' => config('hospital.address', ''),
            'hospital_phone' => config('hospital.phone', ''),
            'hospital_email' => config('hospital.email', ''),
            'message' => $message,
            'email_type' => $emailType,
            'date' => now()->format('F d, Y'),
            'time' => now()->format('H:i:s'),
        ];

        // Always send direct email with custom subject and message
        return $this->sendDirectGpEmail($patient, $subject, $message, $variables, $sentBy);
    }

    /**
     * Send direct email to GP without template.
     *
     * @param Patient $patient
     * @param string $subject
     * @param string $message
     * @param array $variables
     * @param User|null $sentBy
     * @return EmailLog|null
     */
    private function sendDirectGpEmail(Patient $patient, string $subject, string $message, array $variables, User $sentBy = null)
    {
        try {
            $hospitalName = $variables['hospital_name'];
            $hospitalEmail = $variables['hospital_email'] ?? config('mail.from.address', 'noreply@hospital.com');
            $hospitalPhone = $variables['hospital_phone'] ?? '';
            $hospitalAddress = $variables['hospital_address'] ?? '';

            // Create email log entry
            $log = EmailLog::create([
                'recipient_email' => $patient->gp_email,
                'recipient_name' => $patient->gp_name ?? 'GP',
                'subject' => $subject,
                'body' => $this->formatGpEmailBody($message, $variables),
                'variables' => $variables,
                'patient_id' => $patient->id,
                'metadata' => [
                    'email_type' => 'gp_communication',
                    'sent_by' => $sentBy ? $sentBy->id : null,
                ],
                'status' => 'pending'
            ]);

            // Send email immediately
            $this->emailService->sendImmediateEmail($log);

            return $log;
        } catch (\Exception $e) {
            Log::error('Failed to send direct GP email', [
                'patient_id' => $patient->id,
                'gp_email' => $patient->gp_email,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Format GP email body with HTML template.
     *
     * @param string $message
     * @param array $variables
     * @return string
     */
    private function formatGpEmailBody(string $message, array $variables): string
    {
        $hospitalName = $variables['hospital_name'];
        $hospitalAddress = $variables['hospital_address'] ?? '';
        $hospitalPhone = $variables['hospital_phone'] ?? '';
        $hospitalEmail = $variables['hospital_email'] ?? '';

        return view('emails.gp-communication', [
            'gp_name' => $variables['gp_name'],
            'patient_name' => $variables['patient_name'],
            'patient_id' => $variables['patient_id'],
            'patient_dob' => $variables['patient_dob'],
            'doctor_name' => $variables['doctor_name'],
            'hospital_name' => $hospitalName,
            'hospital_address' => $hospitalAddress,
            'hospital_phone' => $hospitalPhone,
            'hospital_email' => $hospitalEmail,
            'message' => nl2br(e($message)),
            'date' => $variables['date'],
            'time' => $variables['time'],
        ])->render();
    }
}
