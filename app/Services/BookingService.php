<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\BookingService as BookingServiceModel;
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

            // Get doctor and service
            $doctor = Doctor::findOrFail($data['doctor_id']);
            $service = BookingServiceModel::find($data['service_id'] ?? null);

            // Calculate fee
            $fee = null;
            if ($service) {
                $fee = $service->getPriceForDoctor($doctor->id);
            }

            // Generate appointment number
            $appointmentNumber = $this->generateAppointmentNumber();

            // Create appointment
            $appointment = Appointment::create([
                'appointment_number' => $appointmentNumber,
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'department_id' => $doctor->department_id ?? $doctor->primaryDepartment()?->id,
                'service_id' => $service?->id,
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'type' => $data['type'] ?? 'consultation',
                'status' => 'pending',
                'created_from' => 'Public Booking Link',
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,
                'fee' => $fee,
                'is_online' => $data['is_online'] ?? false,
            ]);

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

            return $appointment;
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
}

