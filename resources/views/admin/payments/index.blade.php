@extends('admin.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp
@php
    $fmtApptSlot = static function ($appt) {
        if (!$appt || !$appt->appointment_date) {
            return '—';
        }
        $d = formatDateUk($appt->appointment_date);
        if (!empty($appt->appointment_time)) {
            $d .= ', '.formatTime($appt->appointment_time, 'g:i A');
        }
        return $d;
    };
    $statusBadge = static function (string $status): string {
        return match ($status) {
            'completed' => 'success',
            'pending' => 'warning text-dark',
            'failed' => 'danger',
            'cancelled' => 'secondary',
            'refunded' => 'info',
            default => 'light text-dark',
        };
    };
@endphp

@section('title', 'All payments')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">All payments</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">All payments</h1>
            <p class="text-muted mb-0">Full payment ledger (including historical rows recorded before booking-source rules). Filter by status, date, patient, invoice, or method. Use <strong>Booking payments</strong> for the same list with a doctor filter matching the doctor portal.</p>
        </div>
        <div class="fw-semibold">Filtered total: {{ CurrencyHelper::format((float) $totalAmount) }}</div>
    </div>

    <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-2">
            <label class="form-label small mb-0">Status</label>
            <select name="status" class="form-select form-select-sm">
                <option value="">All</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                <option value="refunded" {{ request('status') === 'refunded' ? 'selected' : '' }}>Refunded</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-0">Method</label>
            <select name="payment_method" class="form-select form-select-sm">
                <option value="">Any</option>
                <option value="credit_card" {{ request('payment_method') === 'credit_card' ? 'selected' : '' }}>Credit card</option>
                <option value="debit_card" {{ request('payment_method') === 'debit_card' ? 'selected' : '' }}>Debit card</option>
                <option value="bank_transfer" {{ request('payment_method') === 'bank_transfer' ? 'selected' : '' }}>Bank transfer</option>
                <option value="cash" {{ request('payment_method') === 'cash' ? 'selected' : '' }}>Cash</option>
                <option value="insurance" {{ request('payment_method') === 'insurance' ? 'selected' : '' }}>Insurance</option>
                <option value="online" {{ request('payment_method') === 'online' ? 'selected' : '' }}>Online</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-0">Invoice #</label>
            <input type="text" name="invoice_number" class="form-control form-control-sm" value="{{ request('invoice_number') }}" placeholder="Search">
        </div>
        <div class="col-md-2">
            <label class="form-label small mb-0">Patient</label>
            <input type="text" name="patient" class="form-control form-control-sm" value="{{ request('patient') }}" placeholder="Name">
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
            @if(request()->hasAny(['status','payment_method','invoice_number','patient','from','to']))
                <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
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
                            <th>Status</th>
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
                                    'Clinic booking checkout' => 'info',
                                    'Visit billing' => 'success',
                                    'Billing' => 'secondary',
                                    'Doctor booking offer' => 'warning text-dark',
                                    'Clinic booking offer' => 'warning text-dark',
                                    'Invoice' => 'secondary',
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
                                } elseif ($inv?->doctorBookingDiscountCode?->doctor) {
                                    $doc = $inv->doctorBookingDiscountCode->doctor;
                                    $doctorName = $doc->user->name ?? trim(($doc->first_name ?? '').' '.($doc->last_name ?? ''));
                                }
                            @endphp
                            <tr>
                                <td>{{ $payment->payment_date ? formatDateTimeUkAmPm($payment->payment_date) : '—' }}</td>
                                <td><span class="badge bg-{{ $statusBadge($payment->status) }}">{{ ucfirst($payment->status) }}</span></td>
                                <td class="text-end">{{ CurrencyHelper::format((float) $payment->amount) }}</td>
                                <td>{{ $payment->payment_method_label }}</td>
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
                                        {{ $fmtApptSlot($appt) }}
                                    @elseif($inv?->billing?->appointment)
                                        {{ $fmtApptSlot($inv->billing->appointment) }} <span class="text-muted small">(billing)</span>
                                    @elseif($inv && $inv->pendingBookings->isNotEmpty())
                                        <span class="text-muted">Pending booking</span>
                                    @else
                                        —
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">No payments match.</td>
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
