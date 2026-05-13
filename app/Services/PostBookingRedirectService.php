<?php

namespace App\Services;

use App\Helpers\CurrencyHelper;
use App\Models\Appointment;
use App\Models\ClinicBookingRequest;
use App\Models\Doctor;
use Illuminate\Support\Facades\Log;

/**
 * Optional redirect after successful public booking (doctor link or clinic link), for conversion pixels.
 * Not used for generic invoice /pay success.
 */
class PostBookingRedirectService
{
    /**
     * Full URL with query params (appointment + optional UTM/ad IDs from session), or null to use ThanksDoc UI.
     */
    public function buildRedirectUrlForAppointment(Appointment $appointment): ?string
    {
        $doctor = $appointment->relationLoaded('doctor')
            ? $appointment->doctor
            : $appointment->doctor()->first();

        if (! $doctor instanceof Doctor) {
            return null;
        }

        $base = $this->validatedDoctorRedirectUrl($doctor->post_booking_redirect_url);
        if ($base === null) {
            return null;
        }

        $params = [
            'appointment_number' => $appointment->appointment_number,
            'booking_value' => (string) round((float) ($appointment->fee ?? 0), 2),
            'currency' => strtolower(CurrencyHelper::getDefaultCurrency()),
            'source' => 'thanksdoc',
        ];

        $utm = session('booking_utm_params', []);
        if (is_array($utm)) {
            foreach ($utm as $key => $value) {
                if (is_string($key) && is_scalar($value) && $value !== '') {
                    $params[$key] = (string) $value;
                }
            }
        }

        return $this->mergeQueryString($base, $params);
    }

    /**
     * After public clinic booking (/book/clinic/...): URL from the doctor who owns tracking for this request.
     * Uses assigned doctor, else sole active doctor in the department, else primary doctor in the department.
     */
    public function buildRedirectUrlForClinicBookingRequest(ClinicBookingRequest $request): ?string
    {
        $doctor = $this->resolveDoctorForClinicBookingRedirect($request);
        if (! $doctor instanceof Doctor) {
            return null;
        }

        $base = $this->thankYouBaseUrlForDoctor($doctor);
        if ($base === null) {
            return null;
        }

        $request->loadMissing('appointment');
        $appointment = $request->appointment;

        $params = [
            'clinic_request_number' => $request->request_number,
            'booking_value' => (string) round((float) ($appointment?->fee ?? $request->fee ?? 0), 2),
            'currency' => strtolower(CurrencyHelper::getDefaultCurrency()),
            'source' => 'thanksdoc',
        ];

        if ($appointment && filled($appointment->appointment_number)) {
            $params['appointment_number'] = $appointment->appointment_number;
        }

        $utm = session('booking_utm_params', []);
        if (is_array($utm)) {
            foreach ($utm as $key => $value) {
                if (is_string($key) && is_scalar($value) && $value !== '') {
                    $params[$key] = (string) $value;
                }
            }
        }

        return $this->mergeQueryString($base, $params);
    }

    /**
     * Doctor for clinic redirect: assignee; else sole active doctor; else first active doctor
     * in department (primary first, then by id) who has a valid clinic or doctor thank-you URL.
     */
    public function resolveDoctorForClinicBookingRedirect(ClinicBookingRequest $request): ?Doctor
    {
        $request->loadMissing('doctor');

        if ($request->doctor_id && $request->doctor) {
            return $request->doctor;
        }

        $departmentId = (int) $request->department_id;
        if ($departmentId <= 0) {
            return null;
        }

        $active = Doctor::query()
            ->byDepartment($departmentId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($active->isEmpty()) {
            return null;
        }

        if ($active->count() === 1) {
            return $active->first();
        }

        $primary = Doctor::query()
            ->byDepartment($departmentId)
            ->where('is_active', true)
            ->whereHas('departments', function ($q) use ($departmentId) {
                $q->where('departments.id', $departmentId)
                    ->where('doctor_department.is_primary', true);
            })
            ->orderBy('id')
            ->first();

        $ordered = collect();
        if ($primary) {
            $ordered->push($primary);
        }
        foreach ($active as $d) {
            if ($primary && (int) $d->id === (int) $primary->id) {
                continue;
            }
            $ordered->push($d);
        }

        foreach ($ordered as $candidate) {
            if ($this->thankYouBaseUrlForDoctor($candidate) !== null) {
                return $candidate;
            }
        }

        return null;
    }

    /**
     * Clinic thank-you prefers clinic-specific URL, then doctor booking URL.
     */
    public function thankYouBaseUrlForDoctor(Doctor $doctor): ?string
    {
        return $this->validatedDoctorRedirectUrl($doctor->clinic_post_booking_redirect_url)
            ?? $this->validatedDoctorRedirectUrl($doctor->post_booking_redirect_url);
    }

    public function validatedDoctorRedirectUrl(?string $url): ?string
    {
        if ($url === null || trim($url) === '') {
            return null;
        }

        $url = trim($url);
        if (strlen($url) > 2048) {
            Log::warning('Post booking redirect rejected: URL too long');

            return null;
        }

        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            Log::warning('Post booking redirect rejected: invalid URL');

            return null;
        }

        $scheme = strtolower((string) parse_url($url, PHP_URL_SCHEME));
        if (! in_array($scheme, ['http', 'https'], true)) {
            Log::warning('Post booking redirect rejected: scheme not http(s)');

            return null;
        }

        if (app()->environment('production') && $scheme !== 'https') {
            Log::warning('Post booking redirect rejected: non-HTTPS in production');

            return null;
        }

        return $url;
    }

    /**
     * @param  array<string, scalar>  $newParams
     */
    private function mergeQueryString(string $url, array $newParams): string
    {
        $parts = parse_url($url);
        if ($parts === false || empty($parts['host'])) {
            return $url;
        }

        $query = [];
        if (! empty($parts['query'])) {
            parse_str($parts['query'], $query);
        }
        $query = array_merge($query, $newParams);

        $scheme = $parts['scheme'] ?? 'https';
        $rebuilt = $scheme.'://';
        if (! empty($parts['user'])) {
            $rebuilt .= rawurlencode($parts['user']);
            if (isset($parts['pass'])) {
                $rebuilt .= ':'.rawurlencode($parts['pass']);
            }
            $rebuilt .= '@';
        }
        $rebuilt .= $parts['host'];
        if (! empty($parts['port'])) {
            $rebuilt .= ':'.$parts['port'];
        }
        $rebuilt .= $parts['path'] ?? '';
        $qs = http_build_query($query);
        if ($qs !== '') {
            $rebuilt .= '?'.$qs;
        }
        if (! empty($parts['fragment'])) {
            $rebuilt .= '#'.$parts['fragment'];
        }

        return $rebuilt;
    }
}
