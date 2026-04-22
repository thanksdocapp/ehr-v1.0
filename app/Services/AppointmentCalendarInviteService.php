<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ClinicBookingRequest;
use Carbon\Carbon;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\URL;

class AppointmentCalendarInviteService
{
    /**
     * @return array{google_url:string,ics_url:string}
     */
    public function calendarLinksForAppointment(Appointment $appointment): array
    {
        $event = $this->buildAppointmentCalendarEvent($appointment);

        return [
            'google_url' => $this->buildGoogleCalendarUrl($event),
            'ics_url' => URL::temporarySignedRoute(
                'public.booking.calendar.appointment',
                now()->addDays(14),
                ['appointmentNumber' => $appointment->appointment_number]
            ),
        ];
    }

    /**
     * @return array{google_url:string,ics_url:string}
     */
    public function calendarLinksForClinicRequest(ClinicBookingRequest $clinicRequest): array
    {
        $event = $this->buildClinicRequestCalendarEvent($clinicRequest);

        return [
            'google_url' => $this->buildGoogleCalendarUrl($event),
            'ics_url' => URL::temporarySignedRoute(
                'public.booking.calendar.clinic-request',
                now()->addDays(14),
                ['requestNumber' => $clinicRequest->request_number]
            ),
        ];
    }

    public function appointmentIcsResponse(Appointment $appointment): Response
    {
        $event = $this->buildAppointmentCalendarEvent($appointment);
        $filename = 'appointment-'.$appointment->appointment_number.'.ics';

        return $this->calendarIcsResponse($event, $filename);
    }

    public function clinicRequestIcsResponse(ClinicBookingRequest $clinicRequest): Response
    {
        $event = $this->buildClinicRequestCalendarEvent($clinicRequest);
        $filename = 'clinic-request-'.$clinicRequest->request_number.'.ics';

        return $this->calendarIcsResponse($event, $filename);
    }

    /**
     * @return array{title:string,start:Carbon,end:Carbon,description:string,location:string}
     */
    private function buildAppointmentCalendarEvent(Appointment $appointment): array
    {
        $start = $this->calendarStartDateTime($appointment->appointment_date, $appointment->appointment_time);
        $durationMinutes = max(
            15,
            (int) ($appointment->service?->getDurationForDoctor((int) $appointment->doctor_id)
                ?? $appointment->service?->default_duration_minutes
                ?? 30)
        );
        $end = (clone $start)->addMinutes($durationMinutes);

        $serviceName = (string) ($appointment->service?->name ?? 'Consultation');
        $doctorName = (string) ($appointment->doctor->full_name ?? 'Doctor');
        $appointmentNumber = (string) $appointment->appointment_number;
        $consultationType = (string) ($appointment->consultation_type ?? ($appointment->is_online ? 'online' : 'in_person'));
        $location = $consultationType === 'online'
            ? ((string) ($appointment->meeting_link ?? 'Online consultation'))
            : ((string) optional($appointment->doctor?->primaryDepartment())->name ?: getAppName());

        $descriptionLines = [
            'Appointment Number: '.$appointmentNumber,
            'Service: '.$serviceName,
            'Doctor: '.$doctorName,
            'Consultation Type: '.str_replace('_', ' ', $consultationType),
        ];
        if (!empty($appointment->meeting_link)) {
            $descriptionLines[] = 'Meeting Link: '.$appointment->meeting_link;
        }

        return [
            'title' => $serviceName.' with '.$doctorName,
            'start' => $start,
            'end' => $end,
            'description' => implode("\n", $descriptionLines),
            'location' => $location,
        ];
    }

    /**
     * @return array{title:string,start:Carbon,end:Carbon,description:string,location:string}
     */
    private function buildClinicRequestCalendarEvent(ClinicBookingRequest $clinicRequest): array
    {
        $start = $this->calendarStartDateTime($clinicRequest->appointment_date, $clinicRequest->appointment_time);
        $durationMinutes = max(15, (int) ($clinicRequest->service?->default_duration_minutes ?? 30));
        $end = (clone $start)->addMinutes($durationMinutes);

        $serviceName = (string) ($clinicRequest->service?->name ?? 'Consultation');
        $clinicName = (string) ($clinicRequest->department?->name ?? getAppName());
        $description = implode("\n", [
            'Booking Number: '.$clinicRequest->request_number,
            'Service: '.$serviceName,
            'Clinic: '.$clinicName,
            'Status: Awaiting doctor acceptance',
        ]);

        return [
            'title' => 'Clinic booking: '.$serviceName,
            'start' => $start,
            'end' => $end,
            'description' => $description,
            'location' => $clinicName,
        ];
    }

    private function calendarStartDateTime(mixed $date, mixed $time): Carbon
    {
        $datePart = $date instanceof \DateTimeInterface
            ? $date->format('Y-m-d')
            : Carbon::parse((string) $date)->format('Y-m-d');
        $timePart = $time instanceof \DateTimeInterface
            ? $time->format('H:i:s')
            : Carbon::parse((string) $time)->format('H:i:s');

        return Carbon::parse($datePart.' '.$timePart, config('app.timezone', 'UTC'));
    }

    /**
     * @param  array{title:string,start:Carbon,end:Carbon,description:string,location:string}  $event
     */
    private function buildGoogleCalendarUrl(array $event): string
    {
        $params = [
            'action' => 'TEMPLATE',
            'text' => $event['title'],
            'dates' => $this->icsDateTimeUtc($event['start']).'/'.$this->icsDateTimeUtc($event['end']),
            'details' => $event['description'],
            'location' => $event['location'],
        ];

        return 'https://calendar.google.com/calendar/render?'.http_build_query($params);
    }

    /**
     * @param  array{title:string,start:Carbon,end:Carbon,description:string,location:string}  $event
     */
    private function calendarIcsResponse(array $event, string $filename): Response
    {
        $lines = [
            'BEGIN:VCALENDAR',
            'VERSION:2.0',
            'PRODID:-//ThanksDoc//Booking Calendar//EN',
            'CALSCALE:GREGORIAN',
            'METHOD:PUBLISH',
            'BEGIN:VEVENT',
            'UID:'.uniqid('thanksdoc-', true),
            'DTSTAMP:'.$this->icsDateTimeUtc(now()),
            'DTSTART:'.$this->icsDateTimeUtc($event['start']),
            'DTEND:'.$this->icsDateTimeUtc($event['end']),
            'SUMMARY:'.$this->icsEscape($event['title']),
            'DESCRIPTION:'.$this->icsEscape($event['description']),
            'LOCATION:'.$this->icsEscape($event['location']),
            'END:VEVENT',
            'END:VCALENDAR',
        ];

        /** @var Response $response */
        $response = response(implode("\r\n", $lines)."\r\n", 200, [
            'Content-Type' => 'text/calendar; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'private, no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);

        return $response;
    }

    private function icsDateTimeUtc(Carbon $dateTime): string
    {
        return $dateTime->copy()->utc()->format('Ymd\THis\Z');
    }

    private function icsEscape(string $value): string
    {
        $escaped = str_replace('\\', '\\\\', $value);
        $escaped = str_replace(';', '\;', $escaped);
        $escaped = str_replace(',', '\,', $escaped);
        $escaped = str_replace("\r\n", '\n', $escaped);
        $escaped = str_replace("\n", '\n', $escaped);

        return trim($escaped);
    }
}
