<?php

namespace App\Services;

use App\Models\EmailTemplate;
use App\Models\EmailLog;
use App\Models\User;
use App\Models\Patient;
use App\Models\MedicalRecord;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\ClinicBookingRequest;
use App\Models\Billing;
use App\Models\LabReport;
use App\Models\ContactMessage;
use App\Models\SiteSetting;
use App\Jobs\SendEmail;
use App\Services\EmailNotificationService;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Exception;

class HospitalEmailNotificationService
{
    protected $emailService;

    public function __construct(EmailNotificationService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Doctor name for email templates that already include "Dr." (e.g. "Hello Dr. {{ doctor_name }}").
     * Strips leading "Dr." to avoid "Dr. Dr. Smith".
     */
    protected function doctorNameForTemplate(?string $name): string
    {
        if (!$name) {
            return 'Doctor';
        }
        return trim(preg_replace('/^Dr\.?\s*/i', '', $name)) ?: 'Doctor';
    }

    /**
     * Send appointment confirmation email to patient.
     *
     * Pending appointments receive a provisional notice; confirmed appointments receive the full confirmation copy.
     *
     * @param  bool|null  $provisional  When null, derived from appointment status (pending = provisional).
     * @return EmailLog|null
     */
    public function sendAppointmentConfirmation(Appointment $appointment, ?bool $provisional = null)
    {
        $appointment->loadMissing(['patient', 'doctor', 'department']);

        $patient = $appointment->patient;
        if (!$patient && $appointment->patient_id) {
            $patient = Patient::query()->find($appointment->patient_id);
            if ($patient) {
                $appointment->setRelation('patient', $patient);
            }
        }

        if (!$patient) {
            Log::warning('Cannot send appointment confirmation: Patient not found', [
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
            ]);

            return null;
        }

        $recipientEmail = $this->resolvePatientNotificationEmail($patient);
        if (!$recipientEmail) {
            Log::warning('Cannot send appointment confirmation: Patient email not found', [
                'appointment_id' => $appointment->id,
                'patient_id' => $patient->id,
            ]);

            return null;
        }

        $doctor = $appointment->doctor;

        // Format appointment time properly
        $appointmentTime = $appointment->appointment_time;
        if ($appointmentTime) {
            try {
                $appointmentTime = \Carbon\Carbon::parse($appointmentTime)->format('g:i A');
            } catch (\Exception $e) {
                // Keep original value if parsing fails
            }
        }

        // Build online consultation section - patient gets PARTICIPANT link (meeting_link/roomUrl)
        $onlineConsultationSection = '';
        $notesWithVideoLink = $appointment->notes ?? 'Please arrive 15 minutes early.';
        $participantLink = $appointment->meeting_link ?? null;
        if ($appointment->is_online && $participantLink) {
            $platformName = $appointment->meeting_platform_name ?? 'Video Call';
            $onlineConsultationSection = "\n*** ONLINE CONSULTATION ***\nThis is an online video consultation.\nPlatform: {$platformName}\nParticipant link: {$participantLink}\n\nPlease join the meeting 5 minutes before your scheduled time.\n";
            $notesWithVideoLink = trim($notesWithVideoLink) . "\n\n---\nONLINE VIDEO CONSULTATION\nJoin your video call here: " . $participantLink . "\nPlease join 5 minutes before your scheduled time.";
        }

        if ($provisional === null) {
            $provisional = $appointment->status === 'pending';
        }

        if ($provisional) {
            $provisionalNotice = "Please note: this booking is provisional. Your clinician will confirm your appointment very shortly, and you will receive another email once it is fully confirmed.\n\n";
            $confirmationIntro = 'We have received your appointment request with the following details:';
            $confirmationEmailSubject = 'Provisional Appointment';
        } else {
            $provisionalNotice = '';
            $confirmationIntro = 'Your appointment has been confirmed with the following details:';
            $confirmationEmailSubject = 'Appointment Confirmation';
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'patient_email' => $recipientEmail,
            'doctor_name' => $doctor ? $doctor->name : 'TBD',
            'doctor_specialization' => $doctor ? $doctor->specialization : 'General',
            'doctor_phone' => $doctor ? ($doctor->phone ?? '') : '',
            'doctor_email' => $doctor ? ($doctor->email ?? '') : '',
            'appointment_date' => formatDateUkLong($appointment->appointment_date),
            'appointment_time' => $appointmentTime,
            'appointment_type' => $appointment->type ?? 'Consultation',
            'department' => $appointment->department ? $appointment->department->name : 'General',
            'department_phone' => $appointment->department ? ($appointment->department->phone ?? '') : '',
            'department_email' => $appointment->department ? ($appointment->department->email ?? '') : '',
            'hospital_name' => config('app.name', 'Hospital'),
            'hospital_address' => config('hospital.address', ''),
            'hospital_phone' => config('hospital.phone', ''),
            'appointment_id' => $appointment->id,
            'notes' => $notesWithVideoLink,
            'is_online' => $appointment->is_online ?? false,
            'meeting_link' => $participantLink,
            'participant_link' => $participantLink,
            'join_meeting_url' => $participantLink,
            'meeting_platform' => $appointment->meeting_platform_name ?? null,
            'online_consultation_section' => $onlineConsultationSection,
            'provisional_notice' => $provisionalNotice,
            'confirmation_intro' => $confirmationIntro,
            'confirmation_email_subject' => $confirmationEmailSubject,
        ];

        return $this->emailService->sendTemplateEmail(
            'appointment_confirmation',
            [$recipientEmail => $patient->full_name],
            $variables,
            [
                'event' => $provisional ? 'appointment.provisional_sent' : 'appointment.confirmation_sent',
                'patient_id' => $patient->id,
                'email_type' => 'appointment',
                'metadata' => [
                    'appointment_id' => $appointment->id,
                    'doctor_id' => $doctor ? $doctor->id : null,
                    'provisional' => $provisional,
                ]
            ]
        );
    }

    /**
     * Confirmation email for paid/free non-consultation service orders (no appointment).
     */
    public function sendNonConsultationBookingConfirmation(
        \App\Models\ServiceOrder $order,
        \App\Models\Patient $patient,
        ?string $overrideEmail = null
    ) {
        $order->loadMissing(['doctor', 'service', 'department']);
        $doctor = $order->doctor;
        $service = $order->service;
        $recipientEmail = null;

        if ($overrideEmail && $this->isValidNotificationEmail($overrideEmail)) {
            $recipientEmail = trim($overrideEmail);
        }

        if (! $recipientEmail) {
            $recipientEmail = $this->resolvePatientNotificationEmail($patient);
        }

        if (! $recipientEmail && is_array($order->patient_data) && ! empty($order->patient_data['email'])) {
            $snapshotEmail = trim((string) $order->patient_data['email']);
            if ($this->isValidNotificationEmail($snapshotEmail)) {
                $recipientEmail = $snapshotEmail;
            }
        }

        if (! $recipientEmail) {
            Log::warning('Cannot send non-consultation confirmation: patient email missing', [
                'order_id' => $order->id,
                'patient_id' => $patient->id,
            ]);

            return null;
        }

        $serviceName = $service?->name ?? 'your booking';
        $doctorName = $doctor?->name ?? 'your doctor';
        $clinicName = $order->department?->name ?? config('app.name', 'ThanksDoc');

        $variables = [
            'patient_name' => $patient->full_name,
            'service_name' => $serviceName,
            'doctor_name' => $doctorName,
            'clinic_name' => $clinicName,
            'order_number' => $order->order_number,
            'hospital_name' => config('app.name', 'ThanksDoc'),
            'hospital_phone' => config('hospital.phone', ''),
            'amount_paid' => $order->fee > 0 ? '£' . number_format((float) $order->fee, 2) : 'No payment required',
        ];

        $subject = 'Booking confirmation – ' . $serviceName;
        $body = view('emails.non-consultation-booking-confirmation', $variables)->render();

        $fromEmail = config('hospital.gp_from_email') ?: config('mail.from.address');
        $fromName = config('mail.from.name', config('app.name', 'ThanksDoc'));

        $log = EmailLog::create([
            'recipient_email' => $recipientEmail,
            'recipient_name' => $patient->full_name,
            'subject' => $subject,
            'body' => $body,
            'status' => 'pending',
            'patient_id' => $patient->id,
            'event' => 'service_order.confirmation_sent',
            'email_type' => $this->resolveEmailLogType('service_order'),
            'metadata' => [
                'service_order_id' => $order->id,
                'from_email' => $fromEmail,
                'from_name' => $fromName,
            ],
        ]);
        $this->emailService->sendImmediateEmail($log);

        Log::info('Non-consultation booking confirmation email queued', [
            'order_id' => $order->id,
            'email_log_id' => $log->id,
            'recipient' => $recipientEmail,
            'status' => $log->fresh()->status ?? $log->status,
        ]);

        return $log;
    }

    public function notifyDoctorNewServiceOrder(\App\Models\ServiceOrder $order, \App\Models\Patient $patient): ?EmailLog
    {
        $order->loadMissing(['doctor', 'service']);
        $doctor = $order->doctor;
        if (! $doctor) {
            return null;
        }

        $doctorEmail = $this->resolveDoctorNotificationEmail($doctor);
        if (! $doctorEmail) {
            return null;
        }

        $serviceName = $order->service?->name ?? 'Service';
        $patientName = $patient->full_name;
        $subject = 'New service order – please contact patient';
        $body = '<p>Dear ' . e($doctor->name) . ',</p>'
            . '<p>A patient has placed an order for <strong>' . e($serviceName) . '</strong> (order ' . e($order->order_number) . ').</p>'
            . '<p><strong>Patient:</strong> ' . e($patientName) . '<br>'
            . '<strong>Email:</strong> ' . e($patient->email ?? '') . '<br>'
            . '<strong>Phone:</strong> ' . e($patient->phone ?? '') . '</p>'
            . '<p>Please contact the patient regarding this booking.</p>';

        $log = EmailLog::create([
            'recipient_email' => $doctorEmail,
            'recipient_name' => $doctor->name,
            'subject' => $subject,
            'body' => $body,
            'status' => 'pending',
            'email_type' => $this->resolveEmailLogType('service_order'),
            'metadata' => ['service_order_id' => $order->id, 'event' => 'service_order.doctor_notified'],
        ]);
        $this->emailService->sendImmediateEmail($log);

        return $log;
    }

    /**
     * Use service_order when the DB enum supports it; otherwise general (avoids silent insert failures).
     */
    protected function resolveEmailLogType(string $preferred): string
    {
        if ($preferred !== 'service_order') {
            return $preferred;
        }

        static $supportsServiceOrder = null;
        if ($supportsServiceOrder === null) {
            $supportsServiceOrder = false;
            if (Schema::hasTable('email_logs') && Schema::hasColumn('email_logs', 'email_type')) {
                try {
                    $column = DB::selectOne("SHOW COLUMNS FROM `email_logs` WHERE Field = 'email_type'");
                    $type = $column->Type ?? '';
                    $supportsServiceOrder = str_contains($type, 'service_order');
                } catch (\Throwable $e) {
                    $supportsServiceOrder = false;
                }
            }
        }

        return $supportsServiceOrder ? 'service_order' : 'general';
    }

    /**
     * Send new appointment notification email to doctor.
     *
     * @param Appointment $appointment
     * @return EmailLog|null
     */
    public function sendNewAppointmentToDoctor(Appointment $appointment)
    {
        $doctor = $appointment->doctor;
        if (!$doctor) {
            Log::warning('Cannot send new appointment to doctor: Doctor not found', [
                'appointment_id' => $appointment->id
            ]);
            return null;
        }

        $doctor->loadMissing('user');
        $doctorEmail = $this->resolveDoctorNotificationEmail($doctor);
        if (!$doctorEmail) {
            Log::warning('Cannot send new appointment to doctor: Doctor email not found', [
                'appointment_id' => $appointment->id,
                'doctor_id' => $doctor->id,
                'context' => 'sendNewAppointmentToDoctor',
            ]);

            return null;
        }

        $patient = $appointment->patient;

        // Format appointment time properly
        $appointmentTime = $appointment->appointment_time;
        if ($appointmentTime) {
            try {
                $appointmentTime = \Carbon\Carbon::parse($appointmentTime)->format('g:i A');
            } catch (\Exception $e) {
                // Keep original value if parsing fails
            }
        }

        // Build online consultation section - doctor gets HOST link (whereby_host_url), patient gets participant
        $onlineConsultationSection = '';
        $hostLink = $appointment->whereby_host_url ?? $appointment->meeting_link ?? null;
        $participantLink = $appointment->meeting_link ?? null;
        if ($appointment->is_online && $hostLink) {
            $platformName = $appointment->meeting_platform_name ?? 'Video Call';
            $onlineConsultationSection = "\n*** ONLINE CONSULTATION ***\nPlatform: {$platformName}\nHost link (for you): {$hostLink}\n\n" . ($participantLink ? "Participant link (for patient): {$participantLink}\n" : '') . "\nPlease join as host 5 minutes before your scheduled time.\n";
        } elseif ($appointment->is_online) {
            $onlineConsultationSection = "\n*** ONLINE CONSULTATION ***\nThis is an online video consultation. Meeting link will be generated.\n";
        }

        $variables = [
            'doctor_name' => $this->doctorNameForTemplate($doctor->name),
            'doctor_title' => $doctor->title ?? 'Dr.',
            'patient_name' => $patient ? $patient->full_name : 'N/A',
            'patient_phone' => $patient ? ($patient->phone ?? 'N/A') : 'N/A',
            'patient_email' => $patient ? ($patient->email ?? 'N/A') : 'N/A',
            'appointment_date' => formatDateUkLong($appointment->appointment_date),
            'appointment_time' => $appointmentTime,
            'appointment_type' => $appointment->type ?? 'Consultation',
            'department' => $appointment->department ? $appointment->department->name : 'General',
            'hospital_name' => config('app.name', 'Hospital'),
            'appointment_id' => $appointment->id,
            'notes' => $appointment->notes ?? '',
            'reason' => $appointment->reason ?? 'Not specified',
            'is_online' => $appointment->is_online ?? false,
            'meeting_link' => $hostLink,
            'host_meeting_link' => $hostLink,
            'participant_link' => $participantLink,
            'meeting_platform' => $appointment->meeting_platform_name ?? null,
            'appointment_url' => url('/staff/appointments/' . $appointment->id),
            'online_consultation_section' => $onlineConsultationSection,
        ];

        return $this->emailService->sendTemplateEmail(
            'doctor_new_appointment',
            [$doctorEmail => $doctor->name],
            $variables,
            [
                'event' => 'appointment.new_doctor_notification',
                'doctor_id' => $doctor->id,
                'email_type' => 'appointment',
                'metadata' => [
                    'appointment_id' => $appointment->id,
                    'patient_id' => $patient ? $patient->id : null,
                ]
            ]
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
        $appointment->loadMissing(['patient', 'doctor', 'department']);

        $patient = $appointment->patient;
        if (!$patient) {
            return null;
        }

        $recipientEmail = $this->resolvePatientNotificationEmail($patient);
        if (!$recipientEmail) {
            return null;
        }

        $doctor = $appointment->doctor;

        // Format appointment time properly
        $appointmentTime = $appointment->appointment_time;
        if ($appointmentTime) {
            try {
                $appointmentTime = \Carbon\Carbon::parse($appointmentTime)->format('g:i A');
            } catch (\Exception $e) {
                // Keep original value if parsing fails
            }
        }

        // Build online consultation section - patient gets PARTICIPANT link
        $onlineConsultationSection = '';
        $participantLink = $appointment->meeting_link ?? null;
        if ($appointment->is_online && $participantLink) {
            $platformName = $appointment->meeting_platform_name ?? 'Video Call';
            $onlineConsultationSection = "\n*** ONLINE CONSULTATION ***\nThis is an online video consultation.\nPlatform: {$platformName}\nParticipant link: {$participantLink}\n\nPlease join the meeting 5 minutes before your scheduled time.\n";
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'doctor_name' => $doctor ? $doctor->name : 'TBD',
            'doctor_phone' => $doctor ? ($doctor->phone ?? '') : '',
            'doctor_email' => $doctor ? ($doctor->email ?? '') : '',
            'appointment_date' => formatDateUkLong($appointment->appointment_date),
            'appointment_time' => $appointmentTime,
            'days_before' => $daysBefore,
            'department' => $appointment->department ? $appointment->department->name : 'General',
            'department_phone' => $appointment->department ? ($appointment->department->phone ?? '') : '',
            'department_email' => $appointment->department ? ($appointment->department->email ?? '') : '',
            'hospital_name' => config('app.name', 'Hospital'),
            'hospital_phone' => config('hospital.phone', ''),
            'hospital_address' => config('hospital.address', ''),
            'appointment_id' => $appointment->id,
            'reschedule_url' => url('/patient/appointments/' . $appointment->id . '/reschedule'),
            'cancel_url' => url('/patient/appointments/' . $appointment->id . '/cancel'),
            'is_online' => $appointment->is_online ?? false,
            'meeting_link' => $participantLink,
            'participant_link' => $participantLink,
            'meeting_platform' => $appointment->meeting_platform_name ?? null,
            'join_meeting_url' => $participantLink,
            'online_consultation_section' => $onlineConsultationSection,
        ];

        return $this->emailService->sendTemplateEmail(
            'appointment_reminder',
            [$recipientEmail => $patient->full_name],
            $variables,
            [
                'event' => 'appointment.reminder_sent',
                'patient_id' => $patient->id,
                'email_type' => 'appointment',
                'metadata' => [
                    'appointment_id' => $appointment->id,
                    'days_before' => $daysBefore,
                    'reminder_recipient' => 'patient',
                ]
            ]
        );
    }

    /**
     * Reminder for the assigned doctor (clinician) — host link for online visits, staff calendar link.
     *
     * @return EmailLog|null
     */
    public function sendAppointmentReminderToDoctor(Appointment $appointment, int $daysBefore = 1)
    {
        $appointment->loadMissing(['patient', 'doctor.user', 'department']);

        $doctor = $appointment->doctor;
        if (!$doctor) {
            return null;
        }

        $doctorEmail = $this->resolveDoctorNotificationEmail($doctor);
        if (!$doctorEmail) {
            return null;
        }

        $patient = $appointment->patient;

        $appointmentTime = $appointment->appointment_time;
        if ($appointmentTime) {
            try {
                $appointmentTime = \Carbon\Carbon::parse($appointmentTime)->format('g:i A');
            } catch (\Exception $e) {
            }
        }

        $hostLink = $appointment->whereby_host_url ?? $appointment->meeting_link ?? null;
        $participantLink = $appointment->meeting_link ?? null;
        $onlineConsultationSection = '';
        if ($appointment->is_online && $hostLink) {
            $platformName = $appointment->meeting_platform_name ?? 'Video Call';
            $onlineConsultationSection = "\n*** ONLINE CONSULTATION ***\nPlatform: {$platformName}\nHost link (for you): {$hostLink}\n\n"
                .($participantLink ? "Patient participant link: {$participantLink}\n\n" : '')
                ."Please join as host 5 minutes before the scheduled time.\n";
        } elseif ($appointment->is_online) {
            $onlineConsultationSection = "\n*** ONLINE CONSULTATION ***\nVideo consultation — check the appointment in the staff portal for links.\n";
        }

        $variables = [
            'doctor_name' => $this->doctorNameForTemplate($doctor->name),
            'doctor_title' => $doctor->title ?? 'Dr.',
            'patient_name' => $patient ? $patient->full_name : 'N/A',
            'patient_phone' => $patient ? ($patient->phone ?? '') : '',
            'patient_email' => $patient ? ($this->resolvePatientNotificationEmail($patient) ?? '') : '',
            'appointment_date' => formatDateUkLong($appointment->appointment_date),
            'appointment_time' => $appointmentTime,
            'days_before' => $daysBefore,
            'department' => $appointment->department ? $appointment->department->name : 'General',
            'hospital_name' => config('app.name', 'Hospital'),
            'hospital_phone' => config('hospital.phone', ''),
            'hospital_address' => config('hospital.address', ''),
            'appointment_id' => $appointment->id,
            'appointment_url' => url('/staff/appointments/'.$appointment->id),
            'is_online' => $appointment->is_online ?? false,
            'meeting_link' => $hostLink,
            'host_meeting_link' => $hostLink,
            'participant_link' => $participantLink,
            'meeting_platform' => $appointment->meeting_platform_name ?? null,
            'online_consultation_section' => $onlineConsultationSection,
        ];

        return $this->emailService->sendTemplateEmail(
            'doctor_appointment_reminder',
            [$doctorEmail => $doctor->name],
            $variables,
            [
                'event' => 'appointment.reminder_sent_doctor',
                'email_type' => 'appointment',
                'metadata' => [
                    'appointment_id' => $appointment->id,
                    'doctor_id' => $doctor->id,
                    'days_before' => $daysBefore,
                    'reminder_recipient' => 'doctor',
                ],
            ]
        );
    }

    /**
     * Optional reminder to the department/clinic inbox (department email).
     *
     * @return EmailLog|null
     */
    public function sendAppointmentReminderToDepartment(Appointment $appointment, int $daysBefore = 1)
    {
        $appointment->loadMissing(['patient', 'doctor', 'department']);

        $department = $appointment->department;
        $deptEmail = $this->resolveDepartmentNotificationEmail($department);
        if (!$deptEmail) {
            return null;
        }

        $doctor = $appointment->doctor;
        $patient = $appointment->patient;

        $appointmentTime = $appointment->appointment_time;
        if ($appointmentTime) {
            try {
                $appointmentTime = \Carbon\Carbon::parse($appointmentTime)->format('g:i A');
            } catch (\Exception $e) {
            }
        }

        $variables = [
            'department_name' => $department ? $department->name : 'Clinic',
            'doctor_name' => $doctor ? $doctor->name : 'TBD',
            'patient_name' => $patient ? $patient->full_name : 'N/A',
            'patient_phone' => $patient ? ($patient->phone ?? '') : '',
            'appointment_date' => formatDateUkLong($appointment->appointment_date),
            'appointment_time' => $appointmentTime,
            'days_before' => $daysBefore,
            'hospital_name' => config('app.name', 'Hospital'),
            'hospital_phone' => config('hospital.phone', ''),
            'appointment_id' => $appointment->id,
            'appointment_url' => url('/staff/appointments/'.$appointment->id),
        ];

        return $this->emailService->sendTemplateEmail(
            'department_appointment_reminder',
            [$deptEmail => ($department ? $department->name : 'Clinic')],
            $variables,
            [
                'event' => 'appointment.reminder_sent_department',
                'email_type' => 'appointment',
                'metadata' => [
                    'appointment_id' => $appointment->id,
                    'department_id' => $department ? $department->id : null,
                    'days_before' => $daysBefore,
                    'reminder_recipient' => 'department',
                ],
            ]
        );
    }

    protected function resolveDepartmentNotificationEmail(?Department $department): ?string
    {
        if (!$department || empty($department->email)) {
            return null;
        }

        $email = trim((string) $department->email);

        return $this->isValidNotificationEmail($email) ? $email : null;
    }

    /** Used by reminder command — department inbox. */
    public function getDeliverableDepartmentEmail(?Department $department): ?string
    {
        return $this->resolveDepartmentNotificationEmail($department);
    }

    public function isNotificationEmailDeliverable(string $email): bool
    {
        return $this->isValidNotificationEmail($email);
    }

    /**
     * Whether an appointment reminder email was already logged today for this recipient + appointment (by event).
     */
    public function appointmentReminderAlreadySentToday(string $recipientEmail, int $appointmentId, string $event): bool
    {
        return EmailLog::query()
            ->where('recipient_email', $recipientEmail)
            ->whereDate('created_at', today())
            ->where('event', $event)
            ->where(function ($q) use ($appointmentId) {
                $q->whereJsonContains('variables->appointment_id', (string) $appointmentId)
                    ->orWhereJsonContains('variables->appointment_id', $appointmentId);
            })
            ->exists();
    }

    /**
     * Legacy-compatible check for patient reminders (subject line + variables).
     */
    public function patientAppointmentReminderLegacyAlreadySentToday(string $recipientEmail, int $appointmentId): bool
    {
        return EmailLog::query()
            ->where('recipient_email', $recipientEmail)
            ->where('subject', 'like', '%Appointment Reminder%')
            ->where(function ($q) use ($appointmentId) {
                $q->whereJsonContains('variables->appointment_id', (string) $appointmentId)
                    ->orWhereJsonContains('variables->appointment_id', $appointmentId);
            })
            ->whereDate('created_at', today())
            ->exists();
    }

    /**
     * Send welcome email to new doctor (EPR login details) when account is created.
     * Uses the editable email template "doctor_welcome_epr" and admin settings for URL/contact.
     *
     * @param User $user The doctor user (role must be doctor)
     * @param string $plainPassword The temporary password set at creation (plain text)
     * @return EmailLog|null
     */
    public function sendDoctorWelcomeEmail(User $user, string $plainPassword)
    {
        if (!$user->email) {
            Log::warning('Cannot send doctor welcome email: no email', ['user_id' => $user->id]);
            return null;
        }

        // Include soft-deleted so we don't block when template was trashed (resolveTemplate will restore)
        $templateExists = EmailTemplate::withTrashed()->where('name', 'doctor_welcome_epr')->exists();
        if (!$templateExists) {
            Log::error('Doctor welcome email not sent: template "doctor_welcome_epr" not found. Run migration 2026_02_01_120000_insert_doctor_welcome_epr_email_template or database/sql/doctor_welcome_epr_email_template.sql');
            return null;
        }

        $hospitalName = SiteSetting::get('hospital_name', config('app.name', 'ThanksDoc'));
        $loginUrl = rtrim(SiteSetting::get('app_url', config('app.url', url('/'))), '/');
        $supportEmail = SiteSetting::get('hospital_email', config('hospital.email', 'info@thanksdoc.co.uk'));
        $supportPhone = SiteSetting::get('hospital_phone', config('hospital.phone', '0800 246 5824'));
        $websiteUrl = SiteSetting::get('hospital_website', config('hospital.website', 'https://www.thanksdoc.co.uk'));

        $variables = [
            'doctor_name' => $user->name,
            'doctor_email' => $user->email,
            'password' => $plainPassword,
            'login_url' => $loginUrl,
            'support_email' => $supportEmail,
            'support_phone' => $supportPhone,
            'website_url' => $websiteUrl,
            'hospital_name' => $hospitalName,
        ];

        try {
            $log = $this->emailService->sendTemplateEmail(
                'doctor_welcome_epr',
                [$user->email => $user->name],
                $variables,
                ['email_type' => 'doctor_welcome', 'debug_throw' => true]
            );
            if ($log === null) {
                throw new \RuntimeException(
                    'Doctor welcome email could not be sent. Check Admin > Communication > Email Templates (template name must be exactly: doctor_welcome_epr) and storage/logs/laravel.log.'
                );
            }
            return $log;
        } catch (\Exception $e) {
            Log::error('Failed to send doctor welcome email', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
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
            'registration_date' => formatDateUkLong($patient->created_at),
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
            $variables,
            [
                'event' => 'prescription.ready_notification',
                'patient_id' => $patient->id,
                'email_type' => 'prescription',
                'metadata' => [
                    'prescription_id' => $prescriptionInfo['id'] ?? null,
                ]
            ]
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
            $variables,
            [
                'event' => 'payment.reminder_sent',
                'patient_id' => $patient->id,
                'email_type' => 'billing',
                'metadata' => [
                    'invoice_number' => $billingInfo['invoice_number'] ?? null,
                    'amount_due' => $billingInfo['amount_due'] ?? null,
                ]
            ]
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

            // Generate payment URL - ALWAYS use public payment link (never patient portal)
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
                            'has_token' => !empty($invoice->payment_token),
                            'payment_url' => $paymentUrl
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to generate public payment URL - invoice exists but token generation failed', [
                            'billing_id' => $billing->id,
                            'invoice_id' => $invoice->id ?? null,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        // Try to generate token again
                        try {
                            $invoice->refresh();
                            if (!$invoice->payment_token) {
                                $token = $invoice->generatePaymentToken();
                                if ($token) {
                                    $invoice->refresh();
                                    $paymentUrl = $invoice->getPublicPaymentUrl();
                                    \Log::info('Successfully generated payment token on retry', [
                                        'billing_id' => $billing->id,
                                        'invoice_id' => $invoice->id,
                                        'payment_url' => $paymentUrl
                                    ]);
                                }
                            }
                        } catch (\Exception $retryException) {
                            \Log::error('Retry also failed for payment token generation', [
                                'billing_id' => $billing->id,
                                'invoice_id' => $invoice->id,
                                'error' => $retryException->getMessage()
                            ]);
                            // Don't use patient portal - throw error instead
                            throw new \Exception('Failed to generate public payment link. Please ensure invoice has payment_token column.');
                        }
                    }
                } else {
                    // No invoice exists - create it first
                    \Log::info('No invoice found for billing, creating invoice to generate public payment link', [
                        'billing_id' => $billing->id
                    ]);
                    $billing->syncWithInvoice();
                    $billing->refresh();
                    $invoice = $billing->invoice;
                    
                    if ($invoice) {
                        try {
                            if (!$invoice->payment_token) {
                                $invoice->generatePaymentToken();
                                $invoice->refresh();
                            }
                            $paymentUrl = $invoice->getPublicPaymentUrl();
                            \Log::info('Generated public payment URL after creating invoice', [
                                'billing_id' => $billing->id,
                                'invoice_id' => $invoice->id,
                                'payment_url' => $paymentUrl
                            ]);
                        } catch (\Exception $e) {
                            \Log::error('Failed to generate payment URL even after creating invoice', [
                                'billing_id' => $billing->id,
                                'invoice_id' => $invoice->id,
                                'error' => $e->getMessage()
                            ]);
                            throw new \Exception('Failed to generate public payment link. Please check invoice payment_token configuration.');
                        }
                    } else {
                        \Log::error('Failed to create invoice for billing', [
                            'billing_id' => $billing->id
                        ]);
                        throw new \Exception('Failed to create invoice for billing. Cannot generate public payment link.');
                    }
                }
            }
            
            // Final validation - ensure payment URL is not empty and not patient portal
            if (empty($paymentUrl)) {
                \Log::error('Payment URL is empty - token generation may have failed', [
                    'billing_id' => $billing->id,
                    'invoice_id' => $invoice->id ?? null,
                    'has_invoice' => !empty($invoice),
                    'has_token' => !empty($invoice->payment_token ?? null)
                ]);
                throw new \Exception('Failed to generate payment URL. Payment token may not be configured. Please check invoice payment_token column exists.');
            }
            
            // Only reject if it's explicitly a patient portal URL
            // Accept any URL that is NOT a patient portal URL (including /pay/ URLs)
            if (strpos($paymentUrl, '/patient/billing') !== false) {
                \Log::error('Payment URL is patient portal link, not public link', [
                    'billing_id' => $billing->id,
                    'payment_url' => $paymentUrl
                ]);
                throw new \Exception('Payment URL must be a public payment link, not patient portal. Current URL: ' . $paymentUrl);
            }
            
            // Log successful URL generation for debugging
            \Log::info('Payment URL validated successfully', [
                'billing_id' => $billing->id,
                'payment_url' => $paymentUrl,
                'is_public_url' => strpos($paymentUrl, '/pay/') !== false || strpos($paymentUrl, '/public/billing') !== false
            ]);

            // Get doctor and department information
            $doctorName = 'N/A';
            $departmentName = 'N/A';
            if ($billing->doctor) {
                // Get doctor name without duplicate "Dr." prefix
                $rawName = $billing->doctor->name ?? $billing->doctor->full_name ?? 'N/A';
                // Remove "Dr." prefix if it exists (to avoid duplicate in email template)
                $doctorName = preg_replace('/^Dr\.\s*/i', '', $rawName);
                // Load department relationship if not already loaded
                if (!$billing->doctor->relationLoaded('department')) {
                    $billing->doctor->load('department');
                }
                if ($billing->doctor->department) {
                    $departmentName = $billing->doctor->department->name ?? 'N/A';
                }
            }

            $variables = [
                'patient_name' => $patient->full_name,
                'bill_number' => $billing->bill_number,
                'invoice_number' => $invoice ? $invoice->invoice_number : $billing->bill_number,
                'billing_date' => formatDateUkLong($billing->billing_date),
                'due_date' => $billing->due_date ? formatDateUkLong($billing->due_date) : 'N/A',
                'total_amount' => number_format($billing->total_amount, 2),
                'balance' => number_format($billing->balance, 2),
                'description' => $billing->description,
                'type' => $billing->type_display ?? ucfirst($billing->type),
                'doctor_name' => $doctorName,
                'department_name' => $departmentName,
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

            // Use direct email method (same as GP emails which work successfully)
            // This bypasses templates and sends directly, which is more reliable
            \Log::info('Sending billing notification via direct email method (same as GP emails)', [
                'billing_id' => $billing->id,
                'patient_email' => $patient->email
            ]);
            
            $subject = "New Invoice from {$variables['hospital_name']} - {$variables['bill_number']}";
            $body = $this->formatBillingEmailBody($variables);
            
            // Create email log entry directly (exactly like GP email method)
            // Use try-catch to handle any missing columns gracefully
            try {
                $logData = [
                    'recipient_email' => $patient->email,
                    'recipient_name' => $patient->full_name,
                    'subject' => $subject,
                    'body' => $body,
                    'variables' => $variables,
                    'status' => 'pending',
                    'metadata' => [
                        'email_type' => 'billing_notification',
                        'billing_id' => $billing->id,
                        'invoice_id' => $invoice ? $invoice->id : null,
                        'payment_url' => $paymentUrl,
                    ],
                ];
                
                // Add optional fields only if they exist in the database
                if (Schema::hasColumn('email_logs', 'patient_id')) {
                    $logData['patient_id'] = $patient->id;
                }
                if (Schema::hasColumn('email_logs', 'billing_id')) {
                    $logData['billing_id'] = $billing->id;
                }
                if (Schema::hasColumn('email_logs', 'invoice_id')) {
                    $logData['invoice_id'] = $invoice ? $invoice->id : null;
                }
                if (Schema::hasColumn('email_logs', 'event')) {
                    $logData['event'] = 'billing.invoice_sent';
                }
                if (Schema::hasColumn('email_logs', 'email_type')) {
                    $logData['email_type'] = 'billing';
                }
                
                $log = \App\Models\EmailLog::create($logData);
            } catch (\Exception $createException) {
                // Fallback: create without optional fields
                \Log::warning('Failed to create EmailLog with all fields, trying minimal fields', [
                    'error' => $createException->getMessage()
                ]);
                $log = \App\Models\EmailLog::create([
                    'recipient_email' => $patient->email,
                    'recipient_name' => $patient->full_name,
                    'subject' => $subject,
                    'body' => $body,
                    'variables' => $variables,
                    'status' => 'pending',
                    'metadata' => [
                        'email_type' => 'billing_notification',
                        'billing_id' => $billing->id,
                        'invoice_id' => $invoice ? $invoice->id : null,
                        'payment_url' => $paymentUrl,
                    ],
                ]);
            }

            \Log::info('Billing notification EmailLog created, attempting to send', [
                'email_log_id' => $log->id,
                'billing_id' => $billing->id,
                'patient_email' => $patient->email,
            ]);

            // Send email immediately (same method as GP email - this works!)
            try {
                $sendResult = $this->emailService->sendImmediateEmail($log);
                
                // Refresh to get updated status
                $log->refresh();
                
                if ($sendResult === true && $log->status === 'sent') {
                    \Log::info('Billing notification email sent successfully via direct method', [
                        'billing_id' => $billing->id,
                        'patient_email' => $patient->email,
                        'email_log_id' => $log->id,
                        'sent_at' => $log->sent_at
                    ]);
                } else {
                    \Log::error('Billing notification direct email failed', [
                        'billing_id' => $billing->id,
                        'patient_email' => $patient->email,
                        'email_log_id' => $log->id,
                        'status' => $log->status,
                        'error_message' => $log->error_message,
                        'send_result' => $sendResult
                    ]);
                }
            } catch (\Exception $sendException) {
                \Log::error('Exception while calling sendImmediateEmail for billing notification', [
                    'email_log_id' => $log->id,
                    'billing_id' => $billing->id,
                    'patient_email' => $patient->email,
                    'error' => $sendException->getMessage(),
                    'trace' => $sendException->getTraceAsString()
                ]);
                
                // Refresh to get updated status
                $log->refresh();
                
                // Re-throw to be caught by outer try-catch
                throw $sendException;
            }
            
            return $log;
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
            'registration_date' => formatDateTimeUkAmPm($patient->created_at),
            'patient_phone' => $patient->phone ?? 'Not provided',
            'patient_email' => $patient->email ?? 'Not provided',
            'hospital_name' => config('app.name', 'Hospital'),
            'patient_url' => url('/staff/patients/' . $patient->id),
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
        $doctor->loadMissing('user');
        $doctorEmail = $this->resolveDoctorNotificationEmail($doctor);
        if (!$doctorEmail) {
            Log::warning('Cannot send new appointment to doctor: no valid email on user or doctor profile', [
                'appointment_id' => $appointment->id,
                'doctor_id' => $doctor->id,
                'context' => 'notifyDoctorNewAppointment',
            ]);

            return null;
        }

        $patient = $appointment->patient;

        // Format appointment time properly
        $appointmentTime = $appointment->appointment_time;
        if ($appointmentTime) {
            try {
                $appointmentTime = \Carbon\Carbon::parse($appointmentTime)->format('g:i A');
            } catch (\Exception $e) {
                // Keep original value if parsing fails
            }
        }

        // Build online consultation section - doctor gets HOST link
        $onlineConsultationSection = '';
        $hostLink = $appointment->whereby_host_url ?? $appointment->meeting_link ?? null;
        $participantLink = $appointment->meeting_link ?? null;
        if ($appointment->is_online && $hostLink) {
            $platformName = $appointment->meeting_platform_name ?? 'Video Call';
            $onlineConsultationSection = "\n*** ONLINE CONSULTATION ***\nPlatform: {$platformName}\nHost link (for you): {$hostLink}\n\n" . ($participantLink ? "Participant link (for patient): {$participantLink}\n" : '') . "\nPlease join as host 5 minutes before your scheduled time.\n";
        } elseif ($appointment->is_online) {
            $onlineConsultationSection = "\n*** ONLINE CONSULTATION ***\nThis is an online video consultation. Meeting link will be generated.\n";
        }

        $variables = [
            'doctor_name' => $this->doctorNameForTemplate($doctor->name),
            'patient_name' => $patient->full_name,
            'patient_phone' => $patient->phone ?? 'Not provided',
            'appointment_date' => formatDateUkLong($appointment->appointment_date),
            'appointment_time' => $appointmentTime,
            'appointment_type' => $appointment->type ?? 'Consultation',
            'notes' => $appointment->notes ?? 'No additional notes',
            'hospital_name' => config('app.name', 'Hospital'),
            'appointment_url' => url('/staff/appointments/' . $appointment->id),
            'is_online' => $appointment->is_online ?? false,
            'meeting_link' => $hostLink,
            'host_meeting_link' => $hostLink,
            'participant_link' => $participantLink,
            'meeting_platform' => $appointment->meeting_platform_name ?? null,
            'online_consultation_section' => $onlineConsultationSection,
        ];

        return $this->emailService->sendTemplateEmail(
            'doctor_new_appointment',
            [$doctorEmail => $doctor->name],
            $variables
        );
    }

    /**
     * Email every active doctor in the clinic when a new pooled clinic booking request is created (awaiting acceptance).
     */
    public function notifyClinicDoctorsNewBookingRequest(ClinicBookingRequest $request): void
    {
        $request->loadMissing(['department', 'service']);

        $pd = $request->patient_data ?? [];
        $patientName = trim(($pd['first_name'] ?? '').' '.($pd['last_name'] ?? ''));
        if ($patientName === '') {
            $patientName = 'Patient';
        }

        $deptName = $request->department?->name ?? 'the clinic';
        $serviceName = $request->service?->name ?? 'Consultation';
        $dateStr = $request->appointment_date ? formatDateUkLong($request->appointment_date) : '';
        $timeStr = '';
        if ($request->appointment_time) {
            try {
                $timeStr = \Carbon\Carbon::parse($request->appointment_time)->format('g:i A');
            } catch (\Exception $e) {
                $timeStr = (string) $request->appointment_time;
            }
        }

        $consultationType = str_replace('_', ' ', (string) ($request->consultation_type ?? 'in_person'));
        $rawNotes = (string) ($pd['notes'] ?? $request->notes ?? '');
        $bookingNotes = $rawNotes !== '' ? \Illuminate\Support\Str::limit(strip_tags($rawNotes), 500) : '—';

        try {
            $acceptUrl = route('staff.clinic-booking-requests.index');
        } catch (\Exception $e) {
            $acceptUrl = url('/staff/clinic-booking-requests');
        }

        $hospitalName = config('app.name', 'Hospital');

        $doctors = Doctor::query()
            ->byDepartment($request->department_id)
            ->active()
            ->with('user')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        if ($doctors->isEmpty()) {
            Log::warning('Clinic booking doctor email skipped: no active doctors in department', [
                'clinic_booking_request_id' => $request->id,
                'department_id' => $request->department_id,
            ]);
        }

        $sentCount = 0;
        $skippedNoEmail = 0;

        foreach ($doctors as $doctor) {
            $toEmail = $this->resolveDoctorNotificationEmail($doctor);
            if (! $toEmail) {
                $skippedNoEmail++;

                continue;
            }

            $toName = $doctor->user?->name ?? trim(($doctor->first_name ?? '').' '.($doctor->last_name ?? ''));
            $doctorDisplay = $doctor->name ?? $toName;

            $variables = [
                'doctor_name' => $this->doctorNameForTemplate($doctorDisplay),
                'patient_name' => $patientName,
                'patient_phone' => $pd['phone'] ?? 'Not provided',
                'patient_email' => $pd['email'] ?? 'Not provided',
                'clinic_name' => $deptName,
                'service_name' => $serviceName,
                'appointment_date' => $dateStr,
                'appointment_time' => $timeStr,
                'consultation_type' => $consultationType,
                'request_number' => $request->request_number,
                'booking_notes' => $bookingNotes,
                'accept_requests_url' => $acceptUrl,
                'hospital_name' => $hospitalName,
            ];

            try {
                $log = $this->emailService->sendTemplateEmail(
                    'doctor_clinic_booking_request',
                    [$toEmail => $toName ?: 'Doctor'],
                    $variables,
                    ['body_format' => 'plain']
                );

                if ($log === null) {
                    Log::error('Clinic booking doctor email: send failed or template missing (sendTemplateEmail returned null)', [
                        'doctor_id' => $doctor->id,
                        'clinic_booking_request_id' => $request->id,
                        'recipient' => $toEmail,
                        'hint' => 'Ensure email template doctor_clinic_booking_request exists and is active; check logs for CRITICAL sendTemplateEmail errors.',
                    ]);
                } elseif ($log->status === 'failed') {
                    Log::error('Clinic booking doctor email: mail transport failed', [
                        'doctor_id' => $doctor->id,
                        'clinic_booking_request_id' => $request->id,
                        'email_log_id' => $log->id,
                        'error_message' => $log->error_message,
                    ]);
                } else {
                    $sentCount++;
                }
            } catch (\Throwable $e) {
                Log::error('Failed to send clinic booking request email to doctor', [
                    'doctor_id' => $doctor->id,
                    'clinic_booking_request_id' => $request->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($doctors->isNotEmpty() && $sentCount === 0 && $skippedNoEmail === $doctors->count()) {
            Log::warning('Clinic booking doctor email: no valid email addresses for any doctor in department', [
                'clinic_booking_request_id' => $request->id,
                'department_id' => $request->department_id,
                'doctor_ids' => $doctors->pluck('id')->all(),
            ]);
        }

        // Fallback: department inbox so the clinic still gets the request if doctor rows lack usable addresses.
        if ($sentCount === 0 && $request->department) {
            $fallbackEmail = trim((string) ($request->department->email ?? ''));
            if ($fallbackEmail !== '' && $this->isValidNotificationEmail($fallbackEmail)) {
                $fallbackVariables = [
                    'doctor_name' => 'Colleague',
                    'patient_name' => $patientName,
                    'patient_phone' => $pd['phone'] ?? 'Not provided',
                    'patient_email' => $pd['email'] ?? 'Not provided',
                    'clinic_name' => $deptName,
                    'service_name' => $serviceName,
                    'appointment_date' => $dateStr,
                    'appointment_time' => $timeStr,
                    'consultation_type' => $consultationType,
                    'request_number' => $request->request_number,
                    'booking_notes' => $bookingNotes,
                    'accept_requests_url' => $acceptUrl,
                    'hospital_name' => $hospitalName,
                ];
                try {
                    $log = $this->emailService->sendTemplateEmail(
                        'doctor_clinic_booking_request',
                        [$fallbackEmail => ($request->department->name ?? 'Clinic')],
                        $fallbackVariables,
                        ['body_format' => 'plain']
                    );
                    if ($log === null) {
                        Log::error('Clinic booking email: department fallback send returned null', [
                            'clinic_booking_request_id' => $request->id,
                            'department_id' => $request->department_id,
                            'recipient' => $fallbackEmail,
                        ]);
                    } elseif ($log->status === 'failed') {
                        Log::error('Clinic booking email: department fallback transport failed', [
                            'clinic_booking_request_id' => $request->id,
                            'email_log_id' => $log->id,
                            'error_message' => $log->error_message,
                        ]);
                    } else {
                        Log::info('Clinic booking email sent to department inbox (no doctor addresses)', [
                            'clinic_booking_request_id' => $request->id,
                            'department_id' => $request->department_id,
                            'recipient' => $fallbackEmail,
                        ]);
                    }
                } catch (\Throwable $e) {
                    Log::error('Clinic booking email: department fallback failed', [
                        'clinic_booking_request_id' => $request->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                Log::warning('Clinic booking email: no sends; set a valid department email as fallback or add emails to doctor profiles', [
                    'clinic_booking_request_id' => $request->id,
                    'department_id' => $request->department_id,
                    'department_email_empty_or_invalid' => $fallbackEmail === '',
                ]);
            }
        } elseif ($sentCount === 0) {
            Log::warning('Clinic booking email: no sends and request has no department record', [
                'clinic_booking_request_id' => $request->id,
                'department_id' => $request->department_id,
            ]);
        }
    }

    /**
     * Prefer linked user email, then doctor record email; require non-empty and valid format.
     */
    /**
     * Resolve a deliverable address for patient notifications (profile email, then linked user).
     */
    protected function resolvePatientNotificationEmail(Patient $patient): ?string
    {
        $patient->loadMissing('user');

        $candidates = [];
        if (filled($patient->email ?? null)) {
            $candidates[] = trim((string) $patient->email);
        }
        if ($patient->user && filled($patient->user->email ?? null)) {
            $candidates[] = trim((string) $patient->user->email);
        }

        foreach ($candidates as $email) {
            if ($this->isValidNotificationEmail($email)) {
                return $email;
            }
        }

        return null;
    }

    /** Used by scheduled reminder command (recipient resolution). */
    public function getDeliverablePatientEmail(Patient $patient): ?string
    {
        return $this->resolvePatientNotificationEmail($patient);
    }

    /** Used by scheduled reminder command. */
    public function getDeliverableDoctorEmail(Doctor $doctor): ?string
    {
        return $this->resolveDoctorNotificationEmail($doctor);
    }

    protected function resolveDoctorNotificationEmail(Doctor $doctor): ?string
    {
        $candidates = [];
        if ($doctor->user && filled($doctor->user->email)) {
            $candidates[] = trim((string) $doctor->user->email);
        }
        if (filled($doctor->email ?? null)) {
            $candidates[] = trim((string) $doctor->email);
        }

        foreach ($candidates as $email) {
            if ($this->isValidNotificationEmail($email)) {
                return $email;
            }
        }

        return null;
    }

    /**
     * filter_var rejects some valid addresses; Laravel's email rule is a practical second check.
     */
    protected function isValidNotificationEmail(string $email): bool
    {
        $email = trim($email);
        if ($email === '') {
            return false;
        }
        if (filter_var($email, FILTER_VALIDATE_EMAIL) !== false) {
            return true;
        }

        return Validator::make(['email' => $email], ['email' => 'email'])->passes();
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
            'doctor_appointment_reminder' => 'reminders',
            'department_appointment_reminder' => 'reminders',
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
            'patient_url' => url('/staff/patients/' . $patient->id),
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
            'patient_url' => url('/staff/patients/' . $patient->id),
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
            'doctor_name' => $this->doctorNameForTemplate($doctor->name),
            'patient_name' => $patient ? $patient->full_name : 'Patient',
            'test_name' => $labReport->test_name,
            'test_type' => $labReport->test_type,
            'test_date' => $labReport->test_date ? formatDateUkLong($labReport->test_date) : formatDateUkLong(now()),
            'priority' => strtoupper($labReport->priority ?? 'urgent'),
            'status' => ucfirst($labReport->status),
            'lab_technician' => $labReport->lab_technician ?? 'Laboratory',
            'notes' => $labReport->notes ?? 'Please review and advise next steps.',
            'hospital_name' => config('app.name', 'Hospital'),
            'lab_report_url' => url('/staff/lab-reports/' . $labReport->id),
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
            'record_date' => $medicalRecord->record_date ? formatDateUkLong($medicalRecord->record_date) : formatDateUkLong(now()),
            'record_type' => ucfirst($medicalRecord->record_type),
            'doctor_name' => $updateInfo['doctor_name'] ?? 'Your Doctor',
            'update_type' => $updateInfo['update_type'] ?? 'Record Updated',
            'changes_summary' => $updateInfo['changes_summary'] ?? 'Your medical record has been updated with new information.',
            'diagnosis' => $medicalRecord->diagnosis ?? 'Not specified',
            'treatment_plan' => $medicalRecord->treatment ?? 'Not specified',
            'follow_up_required' => $medicalRecord->follow_up_date ? 'Yes - ' . formatDateUkLong($medicalRecord->follow_up_date) : 'No',
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

        // Format appointment time properly
        $appointmentTime = $appointment->appointment_time;
        if ($appointmentTime) {
            try {
                $appointmentTime = \Carbon\Carbon::parse($appointmentTime)->format('g:i A');
            } catch (\Exception $e) {
                // Keep original value if parsing fails
            }
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'doctor_name' => $doctor ? $doctor->name : 'TBD',
            'appointment_date' => formatDateUkLong($appointment->appointment_date),
            'appointment_time' => $appointmentTime,
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

        // Format appointment time properly
        $appointmentTime = $appointment->appointment_time;
        if ($appointmentTime) {
            try {
                $appointmentTime = \Carbon\Carbon::parse($appointmentTime)->format('g:i A');
            } catch (\Exception $e) {
                // Keep original value if parsing fails
            }
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'doctor_name' => $doctor ? $doctor->name : 'TBD',
            'appointment_date' => formatDateUkLong($appointment->appointment_date),
            'appointment_time' => $appointmentTime,
            'department' => $appointment->department ? $appointment->department->name : 'General',
            'hospital_name' => config('app.name', 'Hospital'),
            'diagnosis' => $appointment->diagnosis ?? 'No diagnosis recorded',
            'prescription' => $appointment->prescription ?? 'No prescription issued',
            'follow_up_instructions' => $appointment->follow_up_instructions ?? 'Please schedule a follow-up if needed.',
            'next_appointment_date' => $appointment->next_appointment_date ? formatDateUkLong($appointment->next_appointment_date) : 'Not scheduled',
        ];

        return $this->emailService->sendTemplateEmail(
            'appointment_completion',
            [$patient->email => $patient->full_name],
            $variables
        );
    }

    /**
     * Send patient feedback request after a completed consultation.
     */
    public function sendPatientFeedbackRequest(Appointment $appointment, string $feedbackUrl)
    {
        if (!$appointment->patient || !$appointment->patient->email) {
            return null;
        }

        $doctor = $appointment->doctor;
        $patient = $appointment->patient;

        // Format appointment time properly
        $appointmentTime = $appointment->appointment_time;
        if ($appointmentTime) {
            try {
                $appointmentTime = \Carbon\Carbon::parse($appointmentTime)->format('g:i A');
            } catch (\Exception $e) {
                // Keep original value if parsing fails
            }
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'doctor_name' => $doctor ? $doctor->name : 'Your clinician',
            'appointment_date' => $appointment->appointment_date ? formatDateUkLong($appointment->appointment_date) : '',
            'appointment_time' => $appointmentTime,
            'department' => $appointment->department ? $appointment->department->name : 'General',
            'hospital_name' => config('app.name', 'Hospital'),
            'feedback_url' => $feedbackUrl,
        ];

        return $this->emailService->sendTemplateEmail(
            'patient_feedback_request',
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

        // Format appointment times properly
        $newTime = $appointment->appointment_time;
        if ($newTime) {
            try {
                $newTime = \Carbon\Carbon::parse($newTime)->format('g:i A');
            } catch (\Exception $e) {
                // Keep original value if parsing fails
            }
        }

        // Format old time if it looks like a raw time value
        if ($oldTime && preg_match('/^\d{2}:\d{2}/', $oldTime)) {
            try {
                $oldTime = \Carbon\Carbon::parse($oldTime)->format('g:i A');
            } catch (\Exception $e) {
                // Keep original value if parsing fails
            }
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'doctor_name' => $doctor ? $doctor->name : 'TBD',
            'old_date' => $oldDate,
            'old_time' => $oldTime,
            'new_date' => formatDateUkLong($appointment->appointment_date),
            'new_time' => $newTime,
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

        // Format appointment time properly
        $appointmentTime = $appointment->appointment_time;
        if ($appointmentTime) {
            try {
                $appointmentTime = \Carbon\Carbon::parse($appointmentTime)->format('g:i A');
            } catch (\Exception $e) {
                // Keep original value if parsing fails
            }
        }

        $variables = [
            'doctor_name' => $this->doctorNameForTemplate($doctor->name),
            'patient_name' => $patient ? $patient->full_name : 'Patient',
            'appointment_date' => formatDateUkLong($appointment->appointment_date),
            'appointment_time' => $appointmentTime,
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
            'doctor_name' => $this->doctorNameForTemplate($doctor->name),
            'patient_name' => $patient ? $patient->full_name : 'Patient',
            'old_date' => $oldDate,
            'old_time' => $oldTime,
            'new_date' => formatDateUkLong($appointment->appointment_date),
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
     * Notify doctor by email when their patient pays an invoice/bill.
     *
     * @param Billing $billing
     * @param float|null $amount Payment amount (defaults to billing paid_amount)
     * @return EmailLog|null
     */
    public function notifyDoctorPaymentReceived(Billing $billing, $amount = null)
    {
        $billing->loadMissing(['doctor.user', 'patient']);

        if (!$billing->doctor || !$billing->doctor->user || !$billing->doctor->user->email) {
            Log::debug('Cannot send doctor payment email: no doctor or email', [
                'billing_id' => $billing->id,
                'doctor_id' => $billing->doctor_id,
            ]);
            return null;
        }

        $doctor = $billing->doctor;
        $doctorEmail = $doctor->user->email;
        $currencySymbol = class_exists(\App\Helpers\CurrencyHelper::class)
            ? \App\Helpers\CurrencyHelper::getCurrencySymbol()
            : '£';
        $amountFormatted = $currencySymbol . number_format((float) ($amount ?? $billing->paid_amount), 2);
        $patientName = $billing->patient ? $billing->patient->full_name : 'Patient';
        $billingUrl = url('/staff/billing/' . $billing->id);

        $variables = [
            'doctor_name' => $this->doctorNameForTemplate($doctor->name),
            'patient_name' => $patientName,
            'amount' => $amountFormatted,
            'description' => $billing->description ?? 'Invoice',
            'billing_id' => (string) $billing->id,
            'billing_url' => $billingUrl,
            'hospital_name' => config('app.name', 'Hospital'),
        ];

        try {
            return $this->emailService->sendTemplateEmail(
                'doctor_payment_received',
                [$doctorEmail => $doctor->name],
                $variables
            );
        } catch (Exception $e) {
            Log::error('Failed to send doctor payment received email', [
                'billing_id' => $billing->id,
                'doctor_id' => $billing->doctor_id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Send email to patient's GP.
     *
     * @param Patient $patient
     * @param string $subject
     * @param string $message
     * @param string $emailType
     * @param User|null $sentBy
     * @param array $medicalRecordAttachments Array of MedicalRecordAttachment models
     * @param array $uploadedFiles Array of uploaded file objects
     * @param array $selectedMedicalRecords Array of MedicalRecord models (used to generate consultation summary PDF when no file attachments)
     * @return EmailLog|null
     */
    public function sendGpEmail(Patient $patient, string $subject, string $message, string $emailType = 'general', User $sentBy = null, array $medicalRecordAttachments = [], array $uploadedFiles = [], array $selectedMedicalRecords = [], ?string $doctorReplyEmail = null)
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

        // Clinic/department email for GP replies: doctor's or patient's department, else global config
        $department = $doctor ? $doctor->primaryDepartment() : $patient->primaryDepartment();
        $departmentEmail = $department && !empty($department->email)
            ? $department->email
            : config('hospital.gp_reply_to_email', 'gpsurgeryresponses@thanksdoc.co.uk');

        $doctorReplyEmail = $this->resolveDoctorReplyEmail($sentBy, $doctorReplyEmail, $doctor);

        $variables = [
            'gp_name' => $patient->gp_name ?? 'GP',
            'gp_email' => $patient->gp_email,
            'gp_phone' => $patient->gp_phone ?? '',
            'gp_address' => $patient->gp_address ?? '',
            'patient_name' => $patient->full_name,
            'patient_id' => $patient->patient_id,
            'patient_dob' => $patient->date_of_birth ? formatDateUkLong($patient->date_of_birth) : 'N/A',
            'doctor_name' => $doctor ? $doctor->name : ($sentBy ? $sentBy->name : 'Hospital Staff'),
            'doctor_specialization' => $doctor ? $doctor->specialization : 'General',
            'hospital_name' => config('app.name', 'Hospital'),
            'hospital_address' => config('hospital.address', ''),
            'hospital_phone' => config('hospital.phone', ''),
            'hospital_email' => config('hospital.email', ''),
            'gp_reply_to_email' => config('hospital.gp_reply_to_email', 'gpsurgeryresponses@thanksdoc.co.uk'),
            'department_email' => $departmentEmail,
            'doctor_reply_email' => $doctorReplyEmail,
            'gp_from_email' => config('hospital.gp_from_email', 'noreply@thanksdoc.co.uk'),
            'message' => $message,
            'email_type' => $emailType,
            'date' => formatDateUkLong(now()),
            'time' => now()->format('H:i:s'),
        ];

        // Always send direct email with custom subject and message
        return $this->sendDirectGpEmail($patient, $subject, $message, $variables, $sentBy, $emailType, $medicalRecordAttachments, $uploadedFiles, $selectedMedicalRecords, $doctorReplyEmail);
    }

    /**
     * Send direct email to GP without template.
     *
     * @param Patient $patient
     * @param string $subject
     * @param string $message
     * @param array $variables
     * @param User|null $sentBy
     * @param string $emailType
     * @param array $medicalRecordAttachments Array of MedicalRecordAttachment models
     * @param array $uploadedFiles Array of uploaded file objects
     * @param array $selectedMedicalRecords Array of MedicalRecord models (used to generate consultation summary PDF when no file attachments)
     * @return EmailLog|null
     */
    /**
     * Resolve the doctor reply-to email for GP communications.
     */
    public function resolveDoctorReplyEmail(?User $sentBy, ?string $override = null, ?Doctor $doctor = null): ?string
    {
        if (!empty($override) && filter_var($override, FILTER_VALIDATE_EMAIL)) {
            return $override;
        }

        if ($doctor && !empty($doctor->email) && filter_var($doctor->email, FILTER_VALIDATE_EMAIL)) {
            return $doctor->email;
        }

        if ($sentBy && !empty($sentBy->email) && filter_var($sentBy->email, FILTER_VALIDATE_EMAIL)) {
            return $sentBy->email;
        }

        return null;
    }

    private function sendDirectGpEmail(Patient $patient, string $subject, string $message, array $variables, User $sentBy = null, string $emailType = 'general', array $medicalRecordAttachments = [], array $uploadedFiles = [], array $selectedMedicalRecords = [], ?string $doctorReplyEmail = null)
    {
        try {
            $hospitalName = $variables['hospital_name'];
            $hospitalEmail = $variables['hospital_email'] ?? config('mail.from.address', 'noreply@hospital.com');
            $hospitalPhone = $variables['hospital_phone'] ?? '';
            $hospitalAddress = $variables['hospital_address'] ?? '';

            // Prepare attachments array for email log
            $attachments = [];
            $tempFiles = [];
            
            // Add medical record attachments
            foreach ($medicalRecordAttachments as $attachment) {
                if ($attachment instanceof \App\Models\MedicalRecordAttachment) {
                    try {
                        $disk = Storage::disk($attachment->storage_disk);
                        $filePath = $disk->path($attachment->file_path);
                        
                        if (file_exists($filePath)) {
                            $attachments[] = [
                                'path' => $filePath,
                                'name' => $attachment->file_name,
                                'type' => $attachment->file_type,
                            ];
                        } else {
                            Log::warning('Medical record attachment file not found', [
                                'attachment_id' => $attachment->id,
                                'file_path' => $attachment->file_path,
                                'storage_disk' => $attachment->storage_disk
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Error accessing medical record attachment', [
                            'attachment_id' => $attachment->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }
            
            // Add uploaded files (temporarily store them)
            foreach ($uploadedFiles as $file) {
                if ($file && $file->isValid()) {
                    try {
                        // Store file temporarily
                        $tempPath = $file->store('temp/gp-emails', 'private');
                        $fullPath = Storage::disk('private')->path($tempPath);
                        $tempFiles[] = $fullPath;
                        
                        $attachments[] = [
                            'path' => $fullPath,
                            'name' => $file->getClientOriginalName(),
                            'type' => $file->getMimeType(),
                        ];
                    } catch (\Exception $e) {
                        Log::error('Error storing uploaded file for GP email', [
                            'file_name' => $file->getClientOriginalName(),
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // If medical records were selected but no file attachments (e.g. consultation notes are text only), generate a PDF summary
            if (empty($attachments) && !empty($selectedMedicalRecords)) {
                try {
                    $summary = $this->generateGpConsultationSummaryPdf($patient, $selectedMedicalRecords);
                    if ($summary) {
                        $attachments[] = $summary;
                        $tempFiles[] = $summary['path'];
                    } else {
                        Log::warning('GP consultation summary PDF returned null', ['patient_id' => $patient->id]);
                        throw new \Exception('Could not generate consultation summary. Please try again or upload files manually.');
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to generate GP consultation summary PDF', [
                        'patient_id' => $patient->id,
                        'error' => $e->getMessage()
                    ]);
                    throw new \Exception('Could not generate consultation summary: ' . $e->getMessage() . '. Please try again or upload files manually.');
                }
            }

            // Create email log entry - check for column existence first
            $clinicCopyEmail = $variables['department_email'] ?? config('hospital.gp_reply_to_email', 'gpsurgeryresponses@thanksdoc.co.uk');
            $gpFromEmail = config('hospital.gp_from_email', 'noreply@thanksdoc.co.uk');
            $gpFromName = config('hospital.gp_from_name', config('hospital.name', 'ThanksDoc'));
            $doctorReply = $variables['doctor_reply_email'] ?? null;
            $replyToRecipients = array_values(array_filter([
                $doctorReply ? ['address' => $doctorReply, 'name' => $variables['doctor_name'] ?? 'Consultant'] : null,
                ['address' => $clinicCopyEmail, 'name' => config('hospital.name', 'ThanksDoc Clinic')],
            ]));
            $emailLogData = [
                'recipient_email' => $patient->gp_email,
                'recipient_name' => $patient->gp_name ?? 'GP',
                'subject' => $subject,
                'body' => $this->formatGpEmailBody($message, $variables),
                'variables' => $variables,
                'attachments' => $attachments,
                'cc_emails' => array_filter([$clinicCopyEmail]), // CC clinic (department) so they receive a copy and can receive replies
                'metadata' => [
                    'email_type' => 'gp_communication',
                    'sent_by' => $sentBy ? $sentBy->id : null,
                    'gp_email_type' => $emailType,
                    'medical_record_count' => count($medicalRecordAttachments),
                    'uploaded_file_count' => count($uploadedFiles),
                    'from_email' => $gpFromEmail,
                    'from_name' => $gpFromName,
                    'reply_to' => $replyToRecipients,
                ],
                'status' => 'pending'
            ];

            // Conditionally add fields if they exist in the database schema
            $emailLogSchema = Schema::getColumnListing('email_logs');
            if (in_array('patient_id', $emailLogSchema)) {
                $emailLogData['patient_id'] = $patient->id;
            }
            if (in_array('event', $emailLogSchema)) {
                $emailLogData['event'] = 'gp.communication_sent';
            }
            if (in_array('email_type', $emailLogSchema)) {
                $emailLogData['email_type'] = 'medical_record';
            }

            $log = EmailLog::create($emailLogData);

            // Send email immediately
            $this->emailService->sendImmediateEmail($log);
            
            // Clean up temporary files after email is sent
            foreach ($tempFiles as $tempFile) {
                if (file_exists($tempFile)) {
                    try {
                        @unlink($tempFile);
                    } catch (\Exception $e) {
                        Log::warning('Failed to delete temporary file', [
                            'file' => $tempFile,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

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
     * Generate a consultation summary PDF for selected medical records (when they have no file attachments).
     *
     * @param Patient $patient
     * @param array $medicalRecords Array of MedicalRecord models
     * @return array|null ['path' => string, 'name' => string, 'type' => string] or null on failure
     */
    private function generateGpConsultationSummaryPdf(Patient $patient, array $medicalRecords): ?array
    {
        $medicalRecords = array_filter($medicalRecords, fn ($r) => $r instanceof MedicalRecord);
        if (empty($medicalRecords)) {
            return null;
        }

        $medicalRecords = array_values($medicalRecords);
        foreach ($medicalRecords as $record) {
            $record->loadMissing(['doctor']);
        }

        $html = view('emails.gp-consultation-summary', [
            'patient' => $patient,
            'medicalRecords' => $medicalRecords,
        ])->render();

        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('defaultFont', 'sans-serif');
        $options->set('chroot', base_path());

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $patient->full_name ?? 'Patient');
        $fileName = 'Consultation_Notes_' . $safeName . '_' . now()->format('Y-m-d_His') . '.pdf';
        $tempPath = 'temp/gp-emails/' . uniqid('summary_', true) . '.pdf';
        $fullPath = Storage::disk('private')->path($tempPath);

        $dir = dirname($fullPath);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (file_put_contents($fullPath, $dompdf->output()) === false) {
            Log::warning('Failed to write GP consultation summary PDF to disk', ['path' => $fullPath]);
            return null;
        }

        return [
            'path' => $fullPath,
            'name' => $fileName,
            'type' => 'application/pdf',
        ];
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
            'doctor_reply_email' => $variables['doctor_reply_email'] ?? null,
            'hospital_name' => $hospitalName,
            'hospital_address' => $hospitalAddress,
            'hospital_phone' => $hospitalPhone,
            'hospital_email' => $hospitalEmail,
            'gp_from_email' => $variables['gp_from_email'] ?? config('hospital.gp_from_email', 'noreply@thanksdoc.co.uk'),
            'gp_reply_to_email' => $variables['gp_reply_to_email'] ?? config('hospital.gp_reply_to_email', 'gpsurgeryresponses@thanksdoc.co.uk'),
            'department_email' => $variables['department_email'] ?? config('hospital.gp_reply_to_email', 'gpsurgeryresponses@thanksdoc.co.uk'),
            'message' => nl2br(e($message)),
            'date' => $variables['date'],
            'time' => $variables['time'],
        ])->render();
    }

    /**
     * Format billing email body (fallback when template fails)
     */
    protected function formatBillingEmailBody(array $variables): string
    {
        $paymentUrl = $variables['payment_url'] ?? '#';
        $hospitalName = $variables['hospital_name'] ?? 'Hospital';
        
        // Ensure payment URL is public link, not patient portal
        if (strpos($paymentUrl, '/patient/billing') !== false) {
            // This is patient portal link, we need public link - log warning
            \Log::warning('Billing email using patient portal link instead of public payment link', [
                'payment_url' => $paymentUrl
            ]);
        }
        
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Invoice Notification</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f7fa; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f5f7fa;">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #f8f9fc; padding: 30px 20px; border-radius: 8px 8px 0 0; text-align: center;">
                            <h1 style="margin: 0; color: #1a202c; font-size: 24px; font-weight: 700;">' . htmlspecialchars($hospitalName) . '</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px 20px;">
                            <h2 style="margin: 0 0 20px 0; color: #1a202c; font-size: 20px; font-weight: 600;">Invoice Notification</h2>
                            
                            <p style="margin: 0 0 20px 0; color: #4a5568; font-size: 16px; line-height: 1.6;">Dear ' . htmlspecialchars($variables['patient_name'] ?? 'Patient') . ',</p>
                            
                            <p style="margin: 0 0 30px 0; color: #4a5568; font-size: 16px; line-height: 1.6;">We hope this message finds you well. This is to inform you that a new invoice has been generated.</p>
                            
                            <!-- Invoice Details Card -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f8f9fc; border-radius: 8px; margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h3 style="margin: 0 0 15px 0; color: #1a202c; font-size: 18px; font-weight: 600;">Invoice Details</h3>
                                        
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px; width: 40%;"><strong>Invoice Number:</strong></td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px; font-weight: 600;">' . htmlspecialchars($variables['invoice_number'] ?? $variables['bill_number'] ?? 'N/A') . '</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px;">Bill Number:</td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px;">' . htmlspecialchars($variables['bill_number'] ?? 'N/A') . '</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px;">Billing Date:</td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px;">' . htmlspecialchars($variables['billing_date'] ?? 'N/A') . '</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px;">Due Date:</td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px;">' . htmlspecialchars($variables['due_date'] ?? 'N/A') . '</td>
                                            </tr>
                                            ' . ($variables['doctor_name'] !== 'N/A' ? '<tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px;">Doctor:</td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px;">' . htmlspecialchars($variables['doctor_name']) . '</td>
                                            </tr>' : '') . '
                                            ' . ($variables['department_name'] !== 'N/A' ? '<tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px;">Department/Clinic:</td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px;">' . htmlspecialchars($variables['department_name']) . '</td>
                                            </tr>' : '') . '
                                            <tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px;">Service Type:</td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px;">' . htmlspecialchars($variables['type'] ?? 'N/A') . '</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px; vertical-align: top;">Description:</td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px;">' . htmlspecialchars($variables['description'] ?? 'N/A') . '</td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding: 15px 0 0 0; border-top: 1px solid #e2e8f0;"></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0; color: #4a5568; font-size: 16px;"><strong>Total Amount:</strong></td>
                                                <td style="padding: 12px 0; color: #1a202c; font-size: 20px; font-weight: 700;">£' . htmlspecialchars($variables['total_amount'] ?? '0.00') . '</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px;">Balance Due:</td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 16px; font-weight: 600;">£' . htmlspecialchars($variables['balance'] ?? '0.00') . '</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            ' . (!empty($variables['notes']) ? '<div style="background-color: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;">
                                <p style="margin: 0; color: #856404; font-size: 14px; line-height: 1.6;"><strong>Notes:</strong> ' . htmlspecialchars($variables['notes']) . '</p>
                            </div>' : '') . '
                            
                            <!-- Payment Button -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                <tr>
                                    <td align="center" style="padding: 30px 0;">
                                        <a href="' . htmlspecialchars($paymentUrl) . '" style="display: inline-block; background-color: #1cc88a; color: #ffffff; padding: 16px 40px; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 16px; text-align: center; min-width: 200px;">Pay Invoice Online</a>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 20px 0; color: #4a5568; font-size: 14px; line-height: 1.6; text-align: center;">You can pay this invoice securely online using the button above. No login required.</p>
                            
                            <p style="margin: 30px 0 10px 0; color: #4a5568; font-size: 16px; text-align: center;">Thank you for choosing ' . htmlspecialchars($variables['department_name'] ?? $hospitalName) . '.</p>
                            <p style="margin: 0; color: #a0aec0; font-size: 12px; text-align: center;">Powered by ThanksDoc</p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px; background-color: #f8f9fc; border-radius: 0 0 8px 8px; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; color: #718096; font-size: 12px; text-align: center; line-height: 1.5;">This is an automated message. Please do not reply to this email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
            </table>
</body>
</html>';
    }

    /**
     * Send payment receipt email to patient
     *
     * @param \App\Models\Invoice $invoice
     * @param \App\Models\Payment $payment
     * @return EmailLog|null
     */
    public function sendPaymentReceipt($invoice, $payment)
    {
        if (!$invoice->patient || !$invoice->patient->email) {
            Log::warning('Cannot send payment receipt: Patient email not found', [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id
            ]);
            return null;
        }

        $invoice->load(['patient', 'billing.doctor', 'billing.department', 'invoiceItems']);
        $billing = $invoice->billing;
        $patient = $invoice->patient;
        
        // Get currency
        $currency = $invoice->currency ?? ($billing->currency ?? 'GBP');
        $currencySymbol = $currency === 'GBP' ? '£' : ($currency === 'USD' ? '$' : $currency . ' ');

        // Strip "Dr." prefix if already present in doctorName
        $doctorName = $billing && $billing->doctor ? $billing->doctor->full_name : 'N/A';
        $doctorName = preg_replace('/^Dr\.\s*/i', '', $doctorName);
        if ($doctorName !== 'N/A' && !empty($doctorName)) {
            $doctorName = 'Dr. ' . $doctorName;
        }

        $variables = [
            'patient_name' => $patient->full_name,
            'patient_email' => $patient->email,
            'invoice_number' => $invoice->invoice_number,
            'bill_number' => $billing ? $billing->bill_number : $invoice->invoice_number,
            'payment_date' => $payment->payment_date ? formatDateTimeUkAmPm($payment->payment_date) : formatDateTimeUkAmPm(now()),
            'amount_paid' => number_format($payment->amount, 2),
            'currency_symbol' => $currencySymbol,
            'total_amount' => number_format($invoice->total_amount, 2),
            'transaction_id' => $payment->transaction_id ?? $payment->gateway_transaction_id ?? 'N/A',
            'payment_method' => ucfirst(str_replace('_', ' ', $payment->payment_method ?? 'Card')),
            'doctor_name' => $doctorName,
            'department' => ($billing && $billing->department) ? $billing->department->name : (config('app.name', 'Hospital')),
            'department_name' => ($billing && $billing->department) ? $billing->department->name : (config('app.name', 'Hospital')),
            'hospital_name' => config('app.name', 'Hospital'),
        ];

        $subject = 'Payment Receipt - Invoice #' . $invoice->invoice_number;
        $body = $this->formatPaymentReceiptBody($variables);

        try {
            // Create EmailLog entry first
            // Use try-catch to handle any missing columns gracefully
            try {
                $logData = [
                    'recipient_email' => $patient->email,
                    'recipient_name' => $patient->full_name,
                    'subject' => $subject,
                    'body' => $body,
                    'variables' => $variables,
                    'status' => 'pending',
                    'email_template_id' => null, // Payment receipt doesn't use a template
                    'metadata' => [
                        'email_type' => 'payment_receipt',
                        'invoice_id' => $invoice->id,
                        'payment_id' => $payment->id,
                        'billing_id' => $billing ? $billing->id : null,
                        'transaction_id' => $payment->transaction_id ?? $payment->gateway_transaction_id ?? null,
                    ],
                ];
                
                // Add optional fields only if they exist in the database
                if (Schema::hasColumn('email_logs', 'patient_id')) {
                    $logData['patient_id'] = $patient->id;
                }
                if (Schema::hasColumn('email_logs', 'billing_id')) {
                    $logData['billing_id'] = $billing ? $billing->id : null;
                }
                if (Schema::hasColumn('email_logs', 'invoice_id')) {
                    $logData['invoice_id'] = $invoice->id;
                }
                if (Schema::hasColumn('email_logs', 'payment_id')) {
                    $logData['payment_id'] = $payment->id;
                }
                if (Schema::hasColumn('email_logs', 'event')) {
                    $logData['event'] = 'payment.receipt_sent';
                }
                if (Schema::hasColumn('email_logs', 'email_type')) {
                    $logData['email_type'] = 'billing';
                }
                
                $emailLog = EmailLog::create($logData);
            } catch (\Exception $createException) {
                // Fallback: create without optional fields
                Log::warning('Failed to create EmailLog with all fields, trying minimal fields', [
                    'error' => $createException->getMessage()
                ]);
                $emailLog = EmailLog::create([
                    'recipient_email' => $patient->email,
                    'recipient_name' => $patient->full_name,
                    'subject' => $subject,
                    'body' => $body,
                    'variables' => $variables,
                    'status' => 'pending',
                    'email_template_id' => null,
                    'metadata' => [
                        'email_type' => 'payment_receipt',
                        'invoice_id' => $invoice->id,
                        'payment_id' => $payment->id,
                        'billing_id' => $billing ? $billing->id : null,
                        'transaction_id' => $payment->transaction_id ?? $payment->gateway_transaction_id ?? null,
                    ],
                ]);
            }

            Log::info('Payment receipt EmailLog created, attempting to send', [
                'email_log_id' => $emailLog->id,
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'patient_email' => $patient->email,
            ]);

            // Send the email using the EmailLog
            try {
                $sendResult = $this->emailService->sendImmediateEmail($emailLog);
                
                // Refresh to get updated status
                $emailLog->refresh();

                if ($sendResult === true && $emailLog->status === 'sent') {
                    Log::info('Payment receipt email sent successfully', [
                        'invoice_id' => $invoice->id,
                        'payment_id' => $payment->id,
                        'patient_email' => $patient->email,
                        'email_log_id' => $emailLog->id,
                        'sent_at' => $emailLog->sent_at
                    ]);
                } else {
                    Log::error('Payment receipt email failed to send', [
                        'invoice_id' => $invoice->id,
                        'payment_id' => $payment->id,
                        'patient_email' => $patient->email,
                        'email_log_id' => $emailLog->id,
                        'status' => $emailLog->status,
                        'error_message' => $emailLog->error_message,
                        'send_result' => $sendResult
                    ]);
                }
            } catch (\Exception $sendException) {
                Log::error('Exception while calling sendImmediateEmail for payment receipt', [
                    'email_log_id' => $emailLog->id,
                    'invoice_id' => $invoice->id,
                    'payment_id' => $payment->id,
                    'patient_email' => $patient->email,
                    'error' => $sendException->getMessage(),
                    'trace' => $sendException->getTraceAsString()
                ]);
                
                // Refresh to get updated status
                $emailLog->refresh();
                
                // Re-throw to be caught by outer try-catch
                throw $sendException;
            }

            return $emailLog;
        } catch (Exception $e) {
            Log::error('Failed to send payment receipt email', [
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'patient_email' => $patient->email,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Format payment receipt email body
     */
    protected function formatPaymentReceiptBody(array $variables): string
    {
        $hospitalName = $variables['hospital_name'] ?? 'Hospital';
        
        return '<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Payment Receipt</title>
    <!--[if mso]>
    <style type="text/css">
        body, table, td {font-family: Arial, sans-serif !important;}
    </style>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, \'Helvetica Neue\', Arial, sans-serif; background-color: #f5f7fa; -webkit-font-smoothing: antialiased; -moz-osx-font-smoothing: grayscale;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f5f7fa;">
        <tr>
            <td align="center" style="padding: 20px 10px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #1cc88a; padding: 30px 20px; border-radius: 8px 8px 0 0; text-align: center;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 24px; font-weight: 700;">Payment Receipt</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px 20px;">
                            <h2 style="margin: 0 0 20px 0; color: #1a202c; font-size: 20px; font-weight: 600;">Payment Confirmed</h2>
                            
                            <p style="margin: 0 0 20px 0; color: #4a5568; font-size: 16px; line-height: 1.6;">Dear ' . htmlspecialchars($variables['patient_name'] ?? 'Patient') . ',</p>
                            
                            <p style="margin: 0 0 30px 0; color: #4a5568; font-size: 16px; line-height: 1.6;">Thank you for your payment. This email serves as your receipt for the transaction.</p>
                            
                            <!-- Payment Details Card -->
                            <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f8f9fc; border-radius: 8px; margin-bottom: 20px;">
                                <tr>
                                    <td style="padding: 20px;">
                                        <h3 style="margin: 0 0 15px 0; color: #1a202c; font-size: 18px; font-weight: 600;">Payment Details</h3>
                                        
                                        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                            <tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px; width: 40%;"><strong>Invoice Number:</strong></td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px; font-weight: 600;">' . htmlspecialchars($variables['invoice_number'] ?? 'N/A') . '</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px;">Payment Date:</td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px;">' . htmlspecialchars($variables['payment_date'] ?? 'N/A') . '</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px;">Payment Method:</td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px;">' . htmlspecialchars($variables['payment_method'] ?? 'Card') . '</td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px;">Transaction ID:</td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px;">' . htmlspecialchars($variables['transaction_id'] ?? 'N/A') . '</td>
                                            </tr>
                                            ' . ($variables['doctor_name'] !== 'N/A' ? '<tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px;">Doctor:</td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px;">' . htmlspecialchars($variables['doctor_name']) . '</td>
                                            </tr>' : '') . '
                                            ' . (($variables['department'] ?? $variables['department_name'] ?? '') !== 'N/A' ? '<tr>
                                                <td style="padding: 8px 0; color: #4a5568; font-size: 14px;">Clinic:</td>
                                                <td style="padding: 8px 0; color: #1a202c; font-size: 14px;">' . htmlspecialchars($variables['department'] ?? $variables['department_name'] ?? 'N/A') . '</td>
                                            </tr>' : '') . '
                                            <tr>
                                                <td colspan="2" style="padding: 15px 0 0 0; border-top: 1px solid #e2e8f0;"></td>
                                            </tr>
                                            <tr>
                                                <td style="padding: 12px 0; color: #4a5568; font-size: 16px;"><strong>Amount Paid:</strong></td>
                                                <td style="padding: 12px 0; color: #1a202c; font-size: 20px; font-weight: 700;">' . htmlspecialchars($variables['currency_symbol'] ?? '£') . htmlspecialchars($variables['amount_paid'] ?? '0.00') . '</td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            
                            <p style="margin: 30px 0 10px 0; color: #4a5568; font-size: 16px; text-align: center;">Thank you for choosing ' . htmlspecialchars($variables['department_name'] ?? $hospitalName) . '.</p>
                            <p style="margin: 0; color: #a0aec0; font-size: 12px; text-align: center;">Powered by ThanksDoc</p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="padding: 20px; background-color: #f8f9fc; border-radius: 0 0 8px 8px; border-top: 1px solid #e2e8f0;">
                            <p style="margin: 0; color: #718096; font-size: 12px; text-align: center; line-height: 1.5;">This is an automated message. Please do not reply to this email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>';
    }
}
