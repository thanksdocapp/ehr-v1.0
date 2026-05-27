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
            <p class="text-muted mb-0">All completed patient payments (including legacy invoices that only have a patient or generic invoice link). Use <strong>Source</strong> to see how each row is tied—older rows may show as <strong>Invoice</strong>. Filter by doctor and/or clinic (department).</p>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <span class="fw-semibold">Filtered total: {{ CurrencyHelper::format((float) $totalAmount) }}</span>
            <a href="{{ route('admin.booking-payments.export-pdf', request()->query()) }}" class="btn btn-sm btn-danger">
                <i class="fas fa-file-pdf me-1"></i>Export PDF
            </a>
            <a href="{{ route('admin.booking-payments.export-csv', request()->query()) }}" class="btn btn-sm btn-success">
                <i class="fas fa-file-csv me-1"></i>Export CSV
            </a>
        </div>
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
        <div class="col-md-3">
            <label class="form-label small mb-0">Clinic</label>
            <select name="department_id" class="form-select form-select-sm">
                <option value="">All clinics</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ (string) request('department_id') === (string) $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
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
            @if(request()->hasAny(['doctor_id','department_id','from','to']))
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
                            <th>Booking capture</th>
                            <th>Patient</th>
                            <th>Appointment</th>
                            <th>Comments</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            @php
                                $inv = $payment->invoice;
                                $patient = $inv?->patient;
                                $src = $bookingPaymentsService->labelForPayment($payment);
                                $badgeClass = match ($src) {
                                    'Appointment' => 'primary',
                                    'Pending booking' => 'info',
                                    'Clinic booking checkout' => 'info',
                                    'Visit billing' => 'success',
                                    'Billing' => 'secondary',
                                    'Doctor booking offer' => 'warning text-dark',
                                    'Clinic booking offer' => 'warning text-dark',
                                    'Invoice' => 'secondary',
                                    default => 'light text-dark',
                                };
                                $capture = $bookingPaymentsService->bookingCaptureForPayment($payment);
                                $doctorName = $bookingPaymentsService->doctorNameForBookingPayment($payment);
                                $comments = $bookingPaymentsService->commentsForBookingPayment($payment);
                            @endphp
                            <tr>
                                <td>{{ $payment->payment_date ? formatDateTimeUkAmPm($payment->payment_date) : '—' }}</td>
                                <td class="text-end">{{ CurrencyHelper::format((float) $payment->amount) }}</td>
                                <td>{{ $payment->payment_method_label }}</td>
                                <td><span class="badge bg-{{ $badgeClass }}">{{ $src }}</span></td>
                                <td>{{ $inv?->invoice_number ?? ('#'.$inv?->id) }}</td>
                                <td>{{ $doctorName ?? '—' }}</td>
                                <td class="small">@include('admin.partials.booking-capture-cell', ['capture' => $capture])</td>
                                <td>
                                    @if($patient)
                                        {{ trim(($patient->first_name ?? '').' '.($patient->last_name ?? '')) ?: '—' }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $bookingPaymentsService->appointmentSlotLabelForBookingPayment($payment) }}</td>
                                <td class="small text-break" style="max-width: 220px;">{{ $comments !== '' ? $comments : '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No payments match.</td>
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
