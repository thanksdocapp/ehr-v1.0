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
            <p class="text-muted mb-0">Completed payments for consultation bookings plus <strong>non-consultation service orders</strong> (screenings, kits, etc.). Free service orders appear as <strong>£0.00</strong> with source <strong>Service order</strong>. Filter by doctor and/or clinic.</p>
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
                        @forelse($rows as $row)
                            @include('admin.partials.booking-payment-row', ['row' => $row, 'bookingPaymentsService' => $bookingPaymentsService])
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">No payments match.</td>
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
