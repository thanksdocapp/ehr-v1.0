<?php

namespace App\Services;

use App\Models\ClinicBookingRequest;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Support\Facades\Schema;

class PatientBookingSourceService
{
    /**
     * Infer how this patient entered the system via public booking (admin visibility only).
     *
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
                ->whereIn('created_from', ['Public Booking Link', 'Clinic Booking (Doctor Accepted)'])
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
            if ($other && ! in_array($other->created_from, ['Public Booking Link', 'Clinic Booking (Doctor Accepted)'], true)) {
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
            if ($primaryLabel === 'Not determined' || $primaryLabel === 'Other appointment source') {
                $primaryLabel = 'Clinic public booking link';
            }
            $dept = $clinicReq->department?->name ?? 'Clinic';
            $evidence[] = 'Clinic request '.$clinicReq->request_number.' — '.$dept.' (status: '.$clinicReq->status.')';
        }

        $invClinic = Invoice::query()
            ->where('patient_id', $patient->id)
            ->whereHas('pendingClinicBookings')
            ->with(['pendingClinicBookings.department'])
            ->orderByDesc('created_at')
            ->first();

        if ($invClinic) {
            if ($primaryLabel === 'Not determined' || $primaryLabel === 'Other appointment source') {
                $primaryLabel = 'Clinic public booking (checkout)';
            }
            $pcb = $invClinic->pendingClinicBookings->first();
            $dn = $pcb?->department?->name;
            $evidence[] = 'Invoice '.$invClinic->invoice_number.' — online clinic checkout'
                .($dn ? ' ('.$dn.')' : '');
        }

        $invDoctor = Invoice::query()
            ->where('patient_id', $patient->id)
            ->whereHas('pendingBookings')
            ->with(['pendingBookings.doctor'])
            ->orderByDesc('created_at')
            ->first();

        if ($invDoctor) {
            if ($primaryLabel === 'Not determined' || $primaryLabel === 'Other appointment source') {
                $primaryLabel = 'Doctor public booking (checkout)';
            }
            $pb = $invDoctor->pendingBookings->first();
            $doc = $pb?->doctor;
            $evidence[] = 'Invoice '.$invDoctor->invoice_number.' — online doctor-link checkout'
                .($doc ? ' ('.$doc->full_name.')' : '');
        }

        if ($evidence === []) {
            $evidence[] = 'No doctor or clinic public booking trail found. The record may be manual, imported, or from an older flow.';
        }

        return ['primary_label' => $primaryLabel, 'evidence_lines' => $evidence];
    }
}
