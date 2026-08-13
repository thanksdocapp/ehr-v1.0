@php
    use App\Helpers\CurrencyHelper;
    $line = $entry['line'];
    $row = $entry['row'];
@endphp
@if($row)
    @php
        $src = $bookingPaymentsService->labelForRow($row);
        $badgeClass = match ($src) {
            'Appointment' => 'primary',
            'Pending booking' => 'info',
            'Clinic booking checkout' => 'info',
            'Visit billing' => 'success',
            'Billing' => 'secondary',
            'Doctor booking offer' => 'warning text-dark',
            'Clinic booking offer' => 'warning text-dark',
            'Service order' => 'dark',
            'Invoice' => 'secondary',
            default => 'light text-dark',
        };
        $sortAt = $row->sortAt();
        $patient = $row->payment?->invoice?->patient ?? $row->serviceOrder?->patient;
        $isProvisional = $patient && ($patient->is_guest ?? false);
        $comments = $bookingPaymentsService->commentsForRow($row);
    @endphp
    <tr>
        <td>{{ $sortAt ? formatDateTimeUkAmPm($sortAt) : '—' }}</td>
        <td class="text-end">{{ CurrencyHelper::format($row->amount()) }}</td>
        <td>{{ $bookingPaymentsService->methodLabelForRow($row) }}</td>
        <td><span class="badge bg-{{ $badgeClass }}">{{ $src }}</span></td>
        <td>{{ $bookingPaymentsService->invoiceLabelForRow($row) }}</td>
        <td>
            {{ $bookingPaymentsService->patientNameForRow($row) }}
            @if($isProvisional)
                <span class="badge bg-secondary ms-1" style="font-size: 0.65rem;" title="Provisional profile from public booking">Provisional</span>
            @endif
        </td>
        <td>{{ $bookingPaymentsService->appointmentSlotLabelForRow($row) }}</td>
        <td class="small text-break" style="max-width: 220px;">{{ $comments !== '' ? $comments : '—' }}</td>
    </tr>
@else
    @php
        $patient = $line->billing?->patient;
        $patientName = $patient ? trim(($patient->first_name ?? '').' '.($patient->last_name ?? '')) : '';
        $patientName = $patientName !== '' ? $patientName : '—';
    @endphp
    <tr>
        <td>—</td>
        <td class="text-end">{{ CurrencyHelper::format((float) $line->amount) }}</td>
        <td>—</td>
        <td><span class="badge bg-light text-dark">—</span></td>
        <td>{{ $line->billing?->bill_number ?? '—' }}</td>
        <td>{{ $patientName }}</td>
        <td>—</td>
        <td class="small text-break" style="max-width: 220px;">{{ $line->description }}</td>
    </tr>
@endif
