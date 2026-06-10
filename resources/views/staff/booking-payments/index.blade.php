@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')
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
@endphp

@section('title', 'Booking payments')
@section('page-title', 'Booking payments')
@section('page-subtitle', 'Payments on invoices tied to appointments, billing, public booking checkout, or booking discount codes')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Booking payments</li>
@endsection

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Booking payments</h1>
            <p class="text-muted mb-0">Completed payments that match your practice: visits, billings attributed to you, pending booking checkouts, and your booking offers. The <strong>Source</strong> column shows how each payment is linked.</p>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <div class="stat-number">{{ CurrencyHelper::format((float) ($stats['total_this_month'] ?? 0)) }}</div>
                        <div class="stat-label">This month (completed)</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <div class="stat-number">{{ CurrencyHelper::format((float) ($stats['total_this_week'] ?? 0)) }}</div>
                        <div class="stat-label">This week (completed)</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-info">
                        <div class="stat-number">{{ CurrencyHelper::format((float) ($stats['total_all_time'] ?? 0)) }}</div>
                        <div class="stat-label">All time ({{ (int) ($stats['payment_count'] ?? 0) }} payments)</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-auto">
            <label class="form-label small mb-0">From</label>
            <input type="date" name="from" class="form-control form-control-sm" value="{{ request('from') }}">
        </div>
        <div class="col-auto">
            <label class="form-label small mb-0">To</label>
            <input type="date" name="to" class="form-control form-control-sm" value="{{ request('to') }}">
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            @if(request()->hasAny(['from','to']))
                <a href="{{ route('staff.booking-payments.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card shadow-sm">
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
                            <th>Patient</th>
                            <th>Appointment / visit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
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
                                $inv = $row->payment?->invoice;
                                $appt = $inv?->appointment;
                                $patient = $inv?->patient ?? $row->serviceOrder?->patient;
                                $isProvisional = $patient && ($patient->is_guest ?? false);
                            @endphp
                            <tr>
                                <td>{{ $sortAt ? formatDateTimeUkAmPm($sortAt) : '—' }}</td>
                                <td class="text-end fw-semibold">{{ CurrencyHelper::format($row->amount()) }}</td>
                                <td>{{ $bookingPaymentsService->methodLabelForRow($row) }}</td>
                                <td><span class="badge bg-{{ $badgeClass }}">{{ $src }}</span></td>
                                <td>{{ $bookingPaymentsService->invoiceLabelForRow($row) }}</td>
                                <td>
                                    {{ $bookingPaymentsService->patientNameForRow($row) }}
                                    @if($isProvisional)
                                        <span class="badge bg-secondary ms-1" style="font-size: 0.65rem;">Provisional</span>
                                    @endif
                                </td>
                                <td>
                                    @if($appt)
                                        <a href="{{ route('staff.appointments.show', $appt->id) }}">{{ $fmtApptSlot($appt) }}</a>
                                    @elseif($inv?->billing?->appointment_id && $inv->billing->appointment)
                                        <span class="text-muted">Via billing</span>
                                        <a href="{{ route('staff.appointments.show', $inv->billing->appointment_id) }}" class="d-block small">{{ $fmtApptSlot($inv->billing->appointment) }}</a>
                                    @elseif($src === 'Service order')
                                        <span class="text-muted">{{ $bookingPaymentsService->appointmentSlotLabelForRow($row) }}</span>
                                    @elseif($inv && $inv->pendingBookings->isNotEmpty())
                                        <span class="text-muted">Pending booking checkout</span>
                                    @elseif($inv && $inv->pendingClinicBookings->isNotEmpty())
                                        @php $pcb = $inv->pendingClinicBookings->first(); @endphp
                                        <span class="text-muted">Clinic booking checkout</span>
                                        <span class="d-block small">{{ $fmtApptSlot((object) ['appointment_date' => $pcb->appointment_date, 'appointment_time' => $pcb->appointment_time]) }}</span>
                                    @else
                                        {{ $bookingPaymentsService->appointmentSlotLabelForRow($row) }}
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No payments match this filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rows->hasPages())
            <div class="card-footer">{{ $rows->links() }}</div>
        @endif
    </div>
</div>
@endsection
