<?php

namespace App\Services;

use App\Models\ClinicBookingRequest;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Support\Facades\Schema;

class PatientBookingSourceService
{
    /**
     * @return array{primary_label: string, evidence_lines: list<string>}
     */
    public function adminBookingSourceSummary(Patient $patient): array
    {
        $evidence = [];
        $primaryLabel = 'Not determined';

        $hasCreatedFrom = Schema::hasColumn('appointments', 'created_from');

        if ($hasCreatedFrom) {
            $appt = $patient->appointments()
                ->with(['doctor', 'department'])
                ->whereIn('created_from', ['Public Booking Link', 'Clinic Booking (Doctor Accepted)', 'Clinic Booking (Auto-assigned)'])
                ->orderByDesc('created_at')
                ->first();

            if ($appt) {
                if ($appt->created_from === 'Public Booking Link') {
                    $primaryLabel = 'Doctor public booking link';
                    $doc = $appt->doctor;
                    $evidence[] = 'Appointment '.$appt->appointment_number.' — booked via a doctor public link'
                        .($doc ? ' ('.$doc->full_name.')' : '');
                } else {
                    $primaryLabel = 'Clinic public booking link';
                    $dept = $appt->department;
                    $evidence[] = 'Appointment '.$appt->appointment_number.' — booked via clinic/department public link'
                        .($dept ? ' ('.$dept->name.')' : '');
                }

                return ['primary_label' => $primaryLabel, 'evidence_lines' => $evidence];
            }

            $other = $patient->appointments()
                ->whereNotNull('created_from')
                ->orderByDesc('created_at')
                ->first();
            if ($other && ! in_array($other->created_from, ['Public Booking Link', 'Clinic Booking (Doctor Accepted)', 'Clinic Booking (Auto-assigned)'], true)) {
                $evidence[] = 'Latest appointment '.$other->appointment_number.' — '.$other->created_from;
                $primaryLabel = 'Other appointment source';
            }
        }

        $clinicReq = ClinicBookingRequest::query()
            ->where(function ($q) use ($patient) {
                $q->where('patient_id', $patient->id);
                if (filled($patient->email)) {
                    $q->orWhere('patient_data->email', $patient->email);
                }
            })
            ->with('department')
            ->orderByDesc('created_at')
            ->first();

        if ($clinicReq) {
            $capture = $this->clinicBookingRequestCapture($clinicReq);
            if ($primaryLabel === 'Not determined' || $primaryLabel === 'Other appointment source') {
                $primaryLabel = $capture['primary_label'];
            }
            if ($capture['evidence_line']) {
                $evidence[] = $capture['evidence_line'];
            }
        }

        $invClinic = Invoice::query()
            ->where('patient_id', $patient->id)
            ->whereHas('pendingClinicBookings')
            ->with(['pendingClinicBookings.department'])
            ->orderByDesc('created_at')
            ->first();

        if ($invClinic) {
            $capture = $this->invoiceBookingCapture($invClinic);
            if ($primaryLabel === 'Not determined' || $primaryLabel === 'Other appointment source') {
                $primaryLabel = $capture['primary_label'];
            }
            if ($capture['evidence_line'] && ! in_array($capture['evidence_line'], $evidence, true)) {
                $evidence[] = $capture['evidence_line'];
            }
        }

        $invDoctor = Invoice::query()
            ->where('patient_id', $patient->id)
            ->whereHas('pendingBookings')
            ->with(['pendingBookings.doctor'])
            ->orderByDesc('created_at')
            ->first();

        if ($invDoctor) {
            $capture = $this->invoiceBookingCapture($invDoctor);
            if ($primaryLabel === 'Not determined' || $primaryLabel === 'Other appointment source') {
                $primaryLabel = $capture['primary_label'];
            }
            if ($capture['evidence_line'] && ! in_array($capture['evidence_line'], $evidence, true)) {
                $evidence[] = $capture['evidence_line'];
            }
        }

        if ($evidence === []) {
            $evidence[] = 'No doctor or clinic public booking trail found. The record may be manual, imported, or from an older flow.';
        }

        return ['primary_label' => $primaryLabel, 'evidence_lines' => $evidence];
    }

    /**
     * Same inference as the patient profile, scoped to one invoice (booking payments list).
     *
     * @return array{
     *     primary_label: string,
     *     clinic_name: ?string,
     *     doctor_name: ?string,
     *     department_id: ?int,
     *     evidence_line: ?string,
     *     invoice_number: ?string
     * }
     */
    public function invoiceBookingCapture(Invoice $invoice): array
    {
        $invoice->loadMissing([
            'appointment.doctor.user',
            'appointment.department',
            'pendingClinicBookings.department',
            'pendingBookings.doctor.user',
            'pendingBookings.department',
            'billing.doctor.user',
            'billing.doctor.departments',
            'billing.appointment.doctor.user',
            'billing.appointment.department',
            'clinicBookingDiscountCode.department',
            'doctorBookingDiscountCode.doctor.user',
            'patient',
        ]);

        $primaryLabel = 'Invoice';
        $clinicName = null;
        $doctorName = null;
        $departmentId = null;
        $evidenceLine = null;

        if ($invoice->appointment_id && $invoice->appointment) {
            $primaryLabel = 'Appointment';
            $clinicName = $invoice->appointment->department?->name;
            $departmentId = $invoice->appointment->department_id;
            $doctorName = $this->formatDoctorName($invoice->appointment->doctor);
            $evidenceLine = 'Appointment '.$invoice->appointment->appointment_number
                .($clinicName ? ' ('.$clinicName.')' : '');

            return $this->captureResult($primaryLabel, $clinicName, $doctorName, $departmentId, $evidenceLine, $invoice->invoice_number);
        }

        if ($invoice->pendingClinicBookings->isNotEmpty()) {
            $pcb = $invoice->pendingClinicBookings->first();
            $clinicName = $pcb->department?->name;
            $departmentId = $pcb->department_id;
            $primaryLabel = 'Clinic public booking (checkout)';
            $evidenceLine = 'Invoice '.$invoice->invoice_number.' — online clinic checkout'
                .($clinicName ? ' ('.$clinicName.')' : '');
            $doctorName = $this->soleDoctorNameForDepartment($departmentId);

            return $this->captureResult($primaryLabel, $clinicName, $doctorName, $departmentId, $evidenceLine, $invoice->invoice_number);
        }

        if ($invoice->pendingBookings->isNotEmpty()) {
            $pb = $invoice->pendingBookings->first();
            $doctorName = $this->formatDoctorName($pb->doctor);
            $clinicName = $pb->department?->name;
            $departmentId = $pb->department_id;
            $primaryLabel = 'Doctor public booking (checkout)';
            $evidenceLine = 'Invoice '.$invoice->invoice_number.' — online doctor-link checkout'
                .($doctorName ? ' ('.$doctorName.')' : '');

            return $this->captureResult($primaryLabel, $clinicName, $doctorName, $departmentId, $evidenceLine, $invoice->invoice_number);
        }

        if ($invoice->billing) {
            $billing = $invoice->billing;
            if ($billing->appointment_id && $billing->appointment) {
                $clinicName = $billing->appointment->department?->name;
                $departmentId = $billing->appointment->department_id;
                $doctorName = $this->formatDoctorName($billing->appointment->doctor);
                $primaryLabel = 'Visit billing';
                $evidenceLine = 'Billing linked to appointment '.($billing->appointment->appointment_number ?? '');
            } elseif ($billing->doctor) {
                $doc = $billing->doctor;
                $doctorName = $this->formatDoctorName($doc);
                $clinicName = $doc->departments->first()?->name ?? $doc->department?->name;
                $departmentId = $doc->departments->first()?->id ?? $doc->department_id;
                $primaryLabel = 'Billing';
            }
        }

        if ($invoice->clinicBookingDiscountCode?->department) {
            $clinicName = $invoice->clinicBookingDiscountCode->department->name;
            $departmentId = $invoice->clinicBookingDiscountCode->department_id;
            $primaryLabel = 'Clinic booking offer';
            $doctorName = $doctorName ?? $this->soleDoctorNameForDepartment($departmentId);
        }

        if ($invoice->doctorBookingDiscountCode?->doctor) {
            $doc = $invoice->doctorBookingDiscountCode->doctor;
            $doctorName = $this->formatDoctorName($doc);
            $primaryLabel = 'Doctor booking offer';
            $clinicName = $clinicName ?? ($doc->departments->first()?->name ?? $doc->department?->name);
            $departmentId = $departmentId ?? ($doc->departments->first()?->id ?? $doc->department_id);
        }

        if ($invoice->patient_id) {
            $clinicReq = ClinicBookingRequest::query()
                ->where('patient_id', $invoice->patient_id)
                ->where('status', 'accepted')
                ->with('department')
                ->orderByDesc('updated_at')
                ->first();

            if ($clinicReq) {
                $reqCapture = $this->clinicBookingRequestCapture($clinicReq);
                if (! $clinicName && $reqCapture['clinic_name']) {
                    $clinicName = $reqCapture['clinic_name'];
                }
                if (! $departmentId && $reqCapture['department_id']) {
                    $departmentId = $reqCapture['department_id'];
                }
                if (! $doctorName && $reqCapture['doctor_name']) {
                    $doctorName = $reqCapture['doctor_name'];
                }
                if ($primaryLabel === 'Invoice') {
                    $primaryLabel = $reqCapture['primary_label'];
                }
                if (! $evidenceLine && $reqCapture['evidence_line']) {
                    $evidenceLine = $reqCapture['evidence_line'];
                }
            }
        }

        if (! $evidenceLine && $invoice->invoice_number) {
            $evidenceLine = 'Invoice '.$invoice->invoice_number
                .($clinicName ? ' — '.$clinicName : '');
        }

        return $this->captureResult($primaryLabel, $clinicName, $doctorName, $departmentId, $evidenceLine, $invoice->invoice_number);
    }

    /**
     * @return array{
     *     primary_label: string,
     *     clinic_name: ?string,
     *     doctor_name: ?string,
     *     department_id: ?int,
     *     evidence_line: ?string,
     *     invoice_number: ?string
     * }
     */
    public function clinicBookingRequestCapture(ClinicBookingRequest $request): array
    {
        $request->loadMissing(['department', 'doctor.user', 'appointment.doctor.user', 'appointment.department']);

        $clinicName = $request->clinicName();
        $clinicName = $clinicName !== '—' ? $clinicName : ($request->department?->name);
        $departmentId = $request->department_id ?? $request->resolvedDepartment()?->id;
        $doctorName = $request->assignedDoctorName();
        $doctorName = $doctorName !== '—' ? $doctorName : null;

        $invoice = $this->findInvoiceForClinicRequest($request);
        if ($invoice) {
            $capture = $this->invoiceBookingCapture($invoice);
            if (! $capture['clinic_name'] && $clinicName) {
                $capture['clinic_name'] = $clinicName;
            }
            if (! $capture['department_id'] && $departmentId) {
                $capture['department_id'] = $departmentId;
            }
            if (! $capture['doctor_name'] && $doctorName) {
                $capture['doctor_name'] = $doctorName;
            }

            return $capture;
        }

        $primaryLabel = $request->status === 'accepted'
            ? 'Clinic public booking'
            : 'Clinic booking request';

        $evidenceLine = 'Clinic request '.$request->request_number
            .($clinicName ? ' ('.$clinicName.')' : '');

        return $this->captureResult($primaryLabel, $clinicName, $doctorName, $departmentId, $evidenceLine, null);
    }

    protected function findInvoiceForClinicRequest(ClinicBookingRequest $request): ?Invoice
    {
        if ($request->patient_id && $request->department_id) {
            $invoice = Invoice::query()
                ->where('patient_id', $request->patient_id)
                ->whereHas('pendingClinicBookings', fn ($q) => $q->where('department_id', $request->department_id))
                ->with(['pendingClinicBookings.department'])
                ->orderByDesc('created_at')
                ->first();

            if ($invoice) {
                return $invoice;
            }
        }

        $email = trim((string) ($request->patient_data['email'] ?? ''));
        if ($email !== '' && $request->department_id) {
            return Invoice::query()
                ->whereHas('pendingClinicBookings', function ($q) use ($request, $email) {
                    $q->where('department_id', $request->department_id)
                        ->where('patient_data->email', $email);
                })
                ->with(['pendingClinicBookings.department'])
                ->orderByDesc('created_at')
                ->first();
        }

        return null;
    }

    protected function soleDoctorNameForDepartment(?int $departmentId): ?string
    {
        if (! $departmentId) {
            return null;
        }

        $doctor = app(\App\Services\ClinicBookingService::class)->defaultDoctorForDepartment($departmentId);

        return $this->formatDoctorName($doctor);
    }

    protected function formatDoctorName(?\App\Models\Doctor $doctor): ?string
    {
        if (! $doctor) {
            return null;
        }

        $name = trim((string) ($doctor->user->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $name = trim($doctor->first_name.' '.$doctor->last_name);

        return $name !== '' ? $name : null;
    }

    /**
     * @return array{
     *     primary_label: string,
     *     clinic_name: ?string,
     *     doctor_name: ?string,
     *     department_id: ?int,
     *     evidence_line: ?string,
     *     invoice_number: ?string
     * }
     */
    protected function captureResult(
        string $primaryLabel,
        ?string $clinicName,
        ?string $doctorName,
        ?int $departmentId,
        ?string $evidenceLine,
        ?string $invoiceNumber
    ): array {
        return [
            'primary_label' => $primaryLabel,
            'clinic_name' => $clinicName,
            'doctor_name' => $doctorName,
            'department_id' => $departmentId,
            'evidence_line' => $evidenceLine,
            'invoice_number' => $invoiceNumber,
        ];
    }
}
