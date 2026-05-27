@php
    $pd = $req->patient_data ?? [];
    $patientName = trim(($pd['first_name'] ?? '').' '.($pd['last_name'] ?? '')) ?: '—';
    $capture = $req->bookingCapture();
    $doctorLabel = $req->assignedDoctorName();
    if ($doctorLabel === '—' && ! empty($capture['doctor_name'])) {
        $doctorLabel = $capture['doctor_name'];
    }
    $acceptorDisplay = $req->acceptorDisplay();
    $acceptorLabel = $acceptorDisplay['name'] !== '—' ? $acceptorDisplay['name'] : null;
    $acceptor = $req->acceptedByUser;
    $acceptedWhen = $req->accepted_at ?? $req->updated_at;
    $paymentAmount = (float) ($req->fee ?? 0);
    if ($paymentAmount <= 0 && $req->appointment) {
        $paymentAmount = (float) ($req->appointment->fee ?? 0);
    }
@endphp
<tr>
    <td><strong>{{ $req->request_number }}</strong></td>
    <td class="small">@include('admin.partials.booking-capture-cell', ['capture' => $capture])</td>
    <td>{{ $patientName }}</td>
    <td>{{ $doctorLabel }}</td>
    <td>{{ $req->service?->name ?? '—' }}</td>
    <td class="text-end text-nowrap">
        @if($paymentAmount > 0)
            <strong>£{{ number_format($paymentAmount, 2) }}</strong>
        @else
            <span class="text-muted">Free</span>
        @endif
    </td>
    <td>
        <span class="small">{{ $req->appointment_date->format('D j M Y') }}</span><br>
        <span class="small text-muted">{{ $req->appointment_time instanceof \DateTimeInterface ? $req->appointment_time->format('g:i A') : $req->appointment_time }}</span>
    </td>
    <td class="small">
        @if($acceptorLabel)
            <span class="d-block">{{ $acceptorLabel }}</span>
            @if($acceptor && $acceptor->email && ($acceptor->name ?? '') !== $acceptor->email)
                <span class="text-muted">{{ $acceptor->email }}</span>
            @elseif(!empty($acceptorDisplay['detail']))
                <span class="text-muted" style="font-size: 0.75rem;">{{ $acceptorDisplay['detail'] }}</span>
            @endif
        @else
            <span class="text-muted">—</span>
            @if(!empty($acceptorDisplay['detail']))
                <span class="d-block text-muted" style="font-size: 0.75rem;">{{ $acceptorDisplay['detail'] }}</span>
            @endif
        @endif
    </td>
    <td class="small text-nowrap">
        @if($req->accepted_at)
            {{ $req->accepted_at->format('d M Y, H:i') }}
        @else
            <span class="text-muted">{{ $acceptedWhen->format('d M Y, H:i') }}</span>
            <span class="d-block text-muted" style="font-size: 0.75rem;">from update time</span>
        @endif
    </td>
    <td class="text-end">
        @if($req->appointment_id)
            <a href="{{ route('admin.appointments.show', $req->appointment_id) }}" class="btn btn-sm btn-outline-primary">Appointment</a>
        @else
            <span class="text-muted small">—</span>
        @endif
    </td>
</tr>
