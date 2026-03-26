@extends('admin.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Booking payments')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Booking payments</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">Booking payments</h1>
            <p class="text-muted mb-0">Completed payments on invoices linked to an appointment, pending booking, billing record, or booking discount. Use <strong>Source</strong> to see how each row is tied. Filter by doctor to match the same rules as the doctor portal.</p>
        </div>
        <div class="fw-semibold">Filtered total: {{ CurrencyHelper::format((float) $totalAmount) }}</div>
    </div>

    <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-3">
            <label class="form-label small mb-0">Doctor</label>
            <select name="doctor_id" class="form-select form-select-sm">
                <option value="">All doctors</option>
                @foreach($doctors as $d)
                    <option value="{{ $d->id }}" {{ (string) request('doctor_id') === (string) $d->id ? 'selected' : '' }}>
                        {{ $d->user->name ?? ($d->first_name.' '.$d->last_name) }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <label class="form-label small mb-0">From</label>
            <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
        </div>
        <div class="col-auto">
            <label class="form-label small mb-0">To</label>
            <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary">Apply</button>
            @if(request()->hasAny(['doctor_id','from','to']))
                <a href="{{ route('admin.booking-payments.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th class="text-end">Amount</th>
                            <th>Method</th>
                            <th>Source</th>
                            <th>Invoice</th>
                            <th>Doctor</th>
                            <th>Patient</th>
                            <th>Appointment</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            @php
                                $inv = $payment->invoice;
                                $patient = $inv?->patient;
                                $appt = $inv?->appointment;
                                $src = $bookingPaymentsService->labelForPayment($payment);
                                $badgeClass = match ($src) {
                                    'Appointment' => 'primary',
                                    'Pending booking' => 'info',
                                    'Visit billing' => 'success',
                                    'Billing' => 'secondary',
                                    'Doctor booking offer' => 'warning text-dark',
                                    'Clinic booking offer' => 'warning text-dark',
                                    default => 'light text-dark',
                                };
                                $doctorName = null;
                                if ($appt?->doctor) {
                                    $doctorName = $appt->doctor->user->name ?? trim(($appt->doctor->first_name ?? '').' '.($appt->doctor->last_name ?? ''));
                                } elseif ($inv?->billing?->doctor) {
                                    $bd = $inv->billing->doctor;
                                    $doctorName = $bd->user->name ?? trim(($bd->first_name ?? '').' '.($bd->last_name ?? ''));
                                } elseif ($inv && $inv->pendingBookings->isNotEmpty()) {
                                    $pbDoc = $inv->pendingBookings->first()?->doctor;
                                    $doctorName = $pbDoc?->user->name ?? ($pbDoc ? trim(($pbDoc->first_name ?? '').' '.($pbDoc->last_name ?? '')) : null);
                                }
                            @endphp
                            <tr>
                                <td>{{ $payment->payment_date ? $payment->payment_date->format('Y-m-d H:i') : '—' }}</td>
                                <td class="text-end">{{ CurrencyHelper::format((float) $payment->amount) }}</td>
                                <td>{{ $payment->payment_method ?? '—' }}</td>
                                <td><span class="badge bg-{{ $badgeClass }}">{{ $src }}</span></td>
                                <td>{{ $inv?->invoice_number ?? ('#'.$inv?->id) }}</td>
                                <td>{{ $doctorName ?? '—' }}</td>
                                <td>
                                    @if($patient)
                                        {{ trim(($patient->first_name ?? '').' '.($patient->last_name ?? '')) ?: '—' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>
                                    @if($appt)
                                        {{ $appt->appointment_date?->format('Y-m-d') }} {{ $appt->appointment_time ?? '' }}
                                    @elseif($inv?->billing?->appointment)
                                        {{ $inv->billing->appointment->appointment_date?->format('Y-m-d') }} {{ $inv->billing->appointment->appointment_time ?? '' }} <span class="text-muted small">(billing)</span>
                                    @elseif($inv && $inv->pendingBookings->isNotEmpty())
                                        <span class="text-muted">Pending booking</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">No payments match.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($payments->hasPages())
            <div class="card-footer">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection
