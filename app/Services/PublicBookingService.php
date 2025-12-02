<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Billing;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\BookingService as BookingServiceModel;
use App\Models\User;
use App\Models\UserNotification;
use App\Services\GuestPatientService;
use App\Services\HospitalEmailNotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PublicBookingService
{
    protected $guestPatientService;
    protected $emailService;

    public function __construct(GuestPatientService $guestPatientService, HospitalEmailNotificationService $emailService)
    {
        $this->guestPatientService = $guestPatientService;
        $this->emailService = $emailService;
    }

    /**
     * Create an appointment from public booking.
     *
     * @param array $data
     * @return Appointment
     */
    public function createFromPublicBooking(array $data)
    {
        return DB::transaction(function () use ($data) {
            // Get doctor and service first to determine department
            $doctor = Doctor::findOrFail($data['doctor_id']);
            $service = BookingServiceModel::find($data['service_id'] ?? null);
            
            // Determine the department/clinic for this booking
            // Priority: 1) Explicit department_id from booking link, 2) Doctor's department
            $departmentId = $data['department_id'] ?? $doctor->department_id ?? $doctor->primaryDepartment()?->id;
            
            // Find or create patient (guest if needed)
            $patient = $this->guestPatientService->findOrCreateGuest([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'address' => $data['address'] ?? null,
            ]);
            
            // Prepare patient update data
            $patientUpdateData = [];
            
            // Update date of birth and gender if provided
            if (isset($data['date_of_birth']) && !empty($data['date_of_birth'])) {
                $patientUpdateData['date_of_birth'] = $data['date_of_birth'];
            }
            if (isset($data['gender']) && !empty($data['gender'])) {
                $patientUpdateData['gender'] = $data['gender'];
            }
            
            // Handle GP consent and details
            if (isset($data['consent_share_with_gp']) && $data['consent_share_with_gp']) {
                $patientUpdateData['consent_share_with_gp'] = true;
                if (isset($data['gp_name']) && !empty($data['gp_name'])) {
                    $patientUpdateData['gp_name'] = $data['gp_name'];
                }
                if (isset($data['gp_email']) && !empty($data['gp_email'])) {
                    $patientUpdateData['gp_email'] = $data['gp_email'];
                }
                if (isset($data['gp_phone']) && !empty($data['gp_phone'])) {
                    $patientUpdateData['gp_phone'] = $data['gp_phone'];
                }
                if (isset($data['gp_address']) && !empty($data['gp_address'])) {
                    $patientUpdateData['gp_address'] = $data['gp_address'];
                }
            } else {
                // Only set to false if explicitly not provided (don't overwrite existing consent)
                if (isset($data['consent_share_with_gp'])) {
                    $patientUpdateData['consent_share_with_gp'] = false;
                }
            }
            
            // Assign patient to the clinic/department from the booking link
            if ($departmentId) {
                // Set legacy department_id if not already set
                if (!$patient->department_id) {
                    $patientUpdateData['department_id'] = $departmentId;
                }
            }
            
            // Update patient with all changes at once
            if (!empty($patientUpdateData)) {
                $patient->update($patientUpdateData);
            }
            
            // Also attach to departments pivot table (many-to-many relationship) if department exists
            if ($departmentId) {
                // Check if already attached to avoid duplicates
                if (!$patient->departments()->where('departments.id', $departmentId)->exists()) {
                    // Check if patient has any primary department
                    $hasPrimary = $patient->departments()->wherePivot('is_primary', true)->exists();
                    
                    // Attach with is_primary = true if this is the first department, or false if others exist
                    $patient->departments()->attach($departmentId, [
                        'is_primary' => !$hasPrimary, // Set as primary if no primary exists
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Calculate fee
            $fee = null;
            if ($service) {
                $fee = $service->getPriceForDoctor($doctor->id);
            }

            // Generate appointment number
            $appointmentNumber = $this->generateAppointmentNumber();

            // Create appointment data
            // Use department_id from booking link (clinic) if provided, otherwise use doctor's department
            $appointmentDepartmentId = $departmentId ?? $doctor->department_id ?? $doctor->primaryDepartment()?->id;
            
            $appointmentData = [
                'appointment_number' => $appointmentNumber,
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'department_id' => $appointmentDepartmentId,
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'type' => $data['type'] ?? 'consultation',
                'status' => 'pending',
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'fee' => $fee,
                'is_online' => isset($data['consultation_type']) && $data['consultation_type'] === 'online',
            ];
            
            // Only add service_id and created_from if columns exist
            if (\Illuminate\Support\Facades\Schema::hasColumn('appointments', 'service_id')) {
                $appointmentData['service_id'] = $service?->id;
            }
            if (\Illuminate\Support\Facades\Schema::hasColumn('appointments', 'created_from')) {
                $appointmentData['created_from'] = 'Public Booking Link';
            }
            
            $appointment = Appointment::create($appointmentData);

            // Always create billing for the appointment (even if fee is 0)
            // Get first admin user for created_by, or use 1 as fallback
            $createdBy = \App\Models\User::where('role', 'admin')->orWhere('is_admin', true)->first()?->id ?? 1;
            
            $billingFee = $fee ?? 0;
            
            // Create billing
            $billing = Billing::create([
                'bill_number' => Billing::generateBillNumber(),
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'appointment_id' => $appointment->id,
                'billing_date' => now(),
                'due_date' => now()->addDays(7),
                'type' => 'consultation',
                'description' => $service ? $service->name : 'Appointment Consultation',
                'subtotal' => $billingFee,
                'discount' => 0,
                'tax' => 0,
                'total_amount' => $billingFee,
                'paid_amount' => 0,
                'balance' => $billingFee,
                'status' => $billingFee > 0 ? 'pending' : 'paid', // If no fee, mark as paid
                'created_by' => $createdBy,
            ]);

            // Create invoice only if fee exists and > 0
            $invoice = null;
            if ($billingFee > 0) {
                // Create invoice
                $invoice = Invoice::create([
                    'billing_id' => $billing->id,
                    'patient_id' => $patient->id,
                    'appointment_id' => $appointment->id,
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'invoice_date' => now(),
                    'due_date' => now()->addDays(7),
                    'subtotal' => $billingFee,
                    'tax_amount' => 0,
                    'discount_amount' => 0,
                    'total_amount' => $billingFee,
                    'status' => 'pending',
                    'description' => $service ? $service->name : 'Appointment Consultation',
                ]);

                // Create invoice item
                $serviceName = $service ? $service->name : 'Appointment Consultation';
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'consultation', // consultation, medication, lab_test, procedure, etc.
                    'item_name' => $serviceName,
                    'description' => $serviceName,
                    'quantity' => 1,
                    'unit_price' => $billingFee,
                    'total_price' => $billingFee,
                ]);

                // Generate payment token
                $paymentToken = $invoice->generatePaymentToken();
                
                // Refresh invoice to get the token
                $invoice->refresh();
                
                // Verify token was generated, if not, create one manually
                if (empty($invoice->payment_token)) {
                    \Log::warning('Payment token generation returned empty, creating manually', [
                        'invoice_id' => $invoice->id,
                        'appointment_id' => $appointment->id,
                        'generated_token' => $paymentToken
                    ]);
                    
                    // Try to generate manually
                    try {
                        $manualToken = bin2hex(random_bytes(32));
                        $invoice->update([
                            'payment_token' => $manualToken,
                            'payment_token_expires_at' => now()->addDays(90),
                        ]);
                        $invoice->refresh();
                        \Log::info('Payment token created manually', [
                            'invoice_id' => $invoice->id,
                            'token_preview' => substr($manualToken, 0, 10) . '...'
                        ]);
                    } catch (\Exception $e) {
                        \Log::error('Failed to manually set payment token', [
                            'invoice_id' => $invoice->id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Send confirmation email
            try {
                $this->emailService->sendAppointmentConfirmation($appointment);
            } catch (\Exception $e) {
                // Log error but don't fail the booking
                \Log::error('Failed to send appointment confirmation email', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage()
                ]);
            }

            // Create notifications for new pending public booking
            try {
                $this->createPublicBookingNotifications($appointment, $patient);
            } catch (\Exception $e) {
                // Log error but don't fail the booking
                \Log::error('Failed to create public booking notifications', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage()
                ]);
            }

            return [
                'appointment' => $appointment,
                'invoice' => $invoice,
                'billing' => $billing
            ];
        });
    }

    /**
     * Generate unique appointment number.
     *
     * @return string
     */
    private function generateAppointmentNumber()
    {
        do {
            $number = 'A' . date('Ymd') . strtoupper(Str::random(4));
        } while (Appointment::where('appointment_number', $number)->exists());

        return $number;
    }

    /**
     * Create notifications for new pending public booking.
     *
     * @param Appointment $appointment
     * @param Patient $patient
     * @return void
     */
    private function createPublicBookingNotifications(Appointment $appointment, Patient $patient)
    {
        $patientName = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
        $appointmentDate = \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y');
        $appointmentTime = \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A');
        
        $notificationData = [
            'type' => UserNotification::TYPE_APPOINTMENT,
            'category' => UserNotification::CATEGORY_APPOINTMENT,
            'title' => 'New Public Booking - Pending Approval',
            'message' => "New appointment booking from {$patientName} on {$appointmentDate} at {$appointmentTime} requires your approval.",
            'priority' => 'high',
            'action_url' => route('staff.appointments.show', $appointment->id),
            'related_appointment_id' => $appointment->id,
            'related_patient_id' => $patient->id,
            'related_doctor_id' => $appointment->doctor_id,
            'data' => [
                'appointment_number' => $appointment->appointment_number,
                'source' => 'public_booking',
                'is_guest' => $patient->is_guest ?? false,
            ],
        ];

        // Notify all admin users
        $adminUsers = User::where(function($query) {
            $query->where('is_admin', true)
                  ->orWhere('role', 'admin');
        })
        ->where('is_active', true)
        ->get();

        foreach ($adminUsers as $admin) {
            UserNotification::create(array_merge($notificationData, [
                'user_id' => $admin->id,
            ]));
        }

        // Notify the doctor if they have a user account
        if ($appointment->doctor && $appointment->doctor->user_id) {
            $doctorUser = User::find($appointment->doctor->user_id);
            if ($doctorUser && $doctorUser->is_active) {
                UserNotification::create(array_merge($notificationData, [
                    'user_id' => $doctorUser->id,
                    'title' => 'New Appointment Booking - Pending',
                    'message' => "You have a new appointment booking from {$patientName} on {$appointmentDate} at {$appointmentTime}.",
                ]));
            }
        }

        // Notify staff in the department (if department exists)
        if ($appointment->department_id) {
            $departmentStaff = User::where('department_id', $appointment->department_id)
                ->where('is_active', true)
                ->where('role', '!=', 'admin') // Don't duplicate admin notifications
                ->where(function($query) {
                    $query->where('is_admin', false)
                          ->orWhereNull('is_admin');
                })
                ->get();

            foreach ($departmentStaff as $staff) {
                // Skip if already notified (doctor)
                if ($appointment->doctor && $appointment->doctor->user_id == $staff->id) {
                    continue;
                }

                UserNotification::create(array_merge($notificationData, [
                    'user_id' => $staff->id,
                    'title' => 'New Public Booking in Your Department',
                    'message' => "New appointment booking from {$patientName} in your department on {$appointmentDate} at {$appointmentTime}.",
                ]));
            }
        }

        \Log::info('Public booking notifications created', [
            'appointment_id' => $appointment->id,
            'patient_id' => $patient->id,
            'notified_admins' => $adminUsers->count(),
            'notified_doctor' => $appointment->doctor && $appointment->doctor->user_id ? 1 : 0,
        ]);
    }
}

