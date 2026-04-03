@extends('layouts.doctor')

@section('title', 'Clinic Booking Requests')
@section('page-subtitle', 'Accept booking requests from your clinic')

@section('content')
    <div class="card shadow-sm">
        <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-stretch align-items-md-center gap-2 gap-md-3">
            <h5 class="mb-0"><i class="fas fa-inbox me-2"></i>Pending Clinic Bookings</h5>
            <a href="{{ route('staff.appointments.index') }}" class="btn btn-outline-primary btn-sm w-100 w-md-auto">
                <i class="fas fa-calendar-alt me-1"></i>My Appointments
            </a>
        </div>
        <div class="card-body">
            @if($requests->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">No pending clinic booking requests.</p>
                <small class="text-muted">When patients book into {{ $doctor->departments->first()?->name ?? 'your clinic' }}, requests will appear here for you to accept.</small>
            </div>
            @else
            <p class="text-muted small d-md-none mb-2"><i class="fas fa-arrows-alt-h me-1"></i>Swipe the table sideways to see all columns.</p>

            <div class="d-none d-md-block doctor-table-horizontal-scroll">
                <table class="table table-hover table-sm align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Request #</th>
                            <th>Patient</th>
                            <th>Reason for booking</th>
                            <th>Service</th>
                            <th>Date & Time</th>
                            <th>Contact &amp; address</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($requests as $req)
                        <tr>
                            <td><strong>{{ $req->request_number }}</strong></td>
                            <td>
                                {{ ($req->patient_data['first_name'] ?? '') . ' ' . ($req->patient_data['last_name'] ?? '') }}
                            </td>
                            <td>
                                @php
                                    $bookingReason = $req->patient_data['notes'] ?? $req->notes ?? '';
                                @endphp
                                @if($bookingReason !== '' && $bookingReason !== null)
                                    <span class="text-dark small" title="{{ e($bookingReason) }}">{{ \Illuminate\Support\Str::limit($bookingReason, 55) }}</span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td>{{ $req->service?->name ?? 'Consultation' }}</td>
                            <td>
                                {{ $req->appointment_date->format('D j M Y') }}<br>
                                <small>{{ $req->appointment_time instanceof \DateTimeInterface ? $req->appointment_time->format('g:i A') : $req->appointment_time }}</small>
                            </td>
                            <td>
                                @php
                                    $pd = $req->patient_data ?? [];
                                    $cbPostcode = strtoupper(trim((string) ($pd['postal_code'] ?? '')));
                                    $cbCity = trim((string) ($pd['city'] ?? ''));
                                    $cbCounty = trim((string) ($pd['state'] ?? ''));
                                    $cbAddr = trim((string) ($pd['address'] ?? ''));
                                @endphp
                                <small>{{ $pd['email'] ?? '-' }}</small><br>
                                <small>
                                    {{ $pd['phone'] ?? '-' }}
                                    @if($cbPostcode !== '')
                                        <span class="text-muted"> · {{ $cbPostcode }}</span>
                                    @endif
                                </small>
                                @if($cbCity !== '' || $cbCounty !== '')
                                    <br><small class="text-muted">{{ $cbCity }}{{ $cbCity !== '' && $cbCounty !== '' ? ', ' : '' }}{{ $cbCounty }}</small>
                                @elseif($cbAddr !== '' && $cbPostcode === '')
                                    <br><small class="text-muted" title="{{ e($cbAddr) }}">{{ \Illuminate\Support\Str::limit(str_replace(["\r\n", "\n", "\r"], ', ', $cbAddr), 48) }}</small>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('staff.clinic-booking-requests.accept', $req) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm text-nowrap">
                                        <i class="fas fa-check me-1"></i>Accept
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-md-none">
                @foreach($requests as $req)
                    @php
                        $bookingReason = $req->patient_data['notes'] ?? $req->notes ?? '';
                        $pd = $req->patient_data ?? [];
                        $cbPostcode = strtoupper(trim((string) ($pd['postal_code'] ?? '')));
                        $cbCity = trim((string) ($pd['city'] ?? ''));
                        $cbCounty = trim((string) ($pd['state'] ?? ''));
                        $cbAddr = trim((string) ($pd['address'] ?? ''));
                    @endphp
                    <div class="card border mb-3">
                        <div class="card-body py-3">
                            <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                                <span class="badge bg-secondary">#{{ $req->request_number }}</span>
                                <strong>{{ ($pd['first_name'] ?? '') . ' ' . ($pd['last_name'] ?? '') }}</strong>
                            </div>
                            <p class="small mb-1"><span class="text-muted">Service:</span> {{ $req->service?->name ?? 'Consultation' }}</p>
                            <p class="small mb-1">
                                <span class="text-muted">When:</span>
                                {{ $req->appointment_date->format('D j M Y') }}
                                · {{ $req->appointment_time instanceof \DateTimeInterface ? $req->appointment_time->format('g:i A') : $req->appointment_time }}
                            </p>
                            @if($bookingReason !== '' && $bookingReason !== null)
                                <p class="small mb-2"><span class="text-muted">Reason:</span> {{ $bookingReason }}</p>
                            @endif
                            <div class="small text-muted mb-3">
                                <div>{{ $pd['email'] ?? '-' }}</div>
                                <div>{{ $pd['phone'] ?? '-' }}@if($cbPostcode !== '') · {{ $cbPostcode }}@endif</div>
                                @if($cbCity !== '' || $cbCounty !== '')
                                    <div>{{ $cbCity }}{{ $cbCity !== '' && $cbCounty !== '' ? ', ' : '' }}{{ $cbCounty }}</div>
                                @elseif($cbAddr !== '' && $cbPostcode === '')
                                    <div>{{ str_replace(["\r\n", "\n", "\r"], ', ', $cbAddr) }}</div>
                                @endif
                            </div>
                            <form action="{{ route('staff.clinic-booking-requests.accept', $req) }}" method="POST" class="d-grid">
                                @csrf
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-check me-1"></i>Accept request
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <div class="alert alert-info mt-3 small">
        <i class="fas fa-info-circle me-2"></i>
        <strong>How it works:</strong> When a patient books into your clinic, the request appears here. The first doctor to click "Accept" gets the patient. Once accepted, the booking is removed from other doctors' view and the patient receives a confirmation email.
    </div>
@endsection
