@extends('layouts.doctor')

@section('title', 'Clinic Booking Requests')
@section('page-subtitle', 'Accept booking requests from your clinic')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-inbox me-2"></i>Pending Clinic Bookings</h5>
            <a href="{{ route('staff.appointments.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-calendar-alt me-1"></i>My Appointments
            </a>
        </div>
        <div class="card-body">
            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @if($requests->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">No pending clinic booking requests.</p>
                <small class="text-muted">When patients book into {{ $doctor->departments->first()?->name ?? 'your clinic' }}, requests will appear here for you to accept.</small>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Request #</th>
                            <th>Patient</th>
                            <th>Service</th>
                            <th>Date & Time</th>
                            <th>Contact</th>
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
                            <td>{{ $req->service?->name ?? 'Consultation' }}</td>
                            <td>
                                {{ $req->appointment_date->format('D j M Y') }}<br>
                                <small>{{ $req->appointment_time instanceof \DateTimeInterface ? $req->appointment_time->format('g:i A') : $req->appointment_time }}</small>
                            </td>
                            <td>
                                <small>{{ $req->patient_data['email'] ?? '-' }}</small><br>
                                <small>{{ $req->patient_data['phone'] ?? '-' }}</small>
                            </td>
                            <td>
                                <form action="{{ route('staff.clinic-booking-requests.accept', $req) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-check me-1"></i>Accept
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
    </div>

    <div class="alert alert-info mt-3">
        <i class="fas fa-info-circle me-2"></i>
        <strong>How it works:</strong> When a patient books into your clinic, the request appears here. The first doctor to click "Accept" gets the patient. Once accepted, the booking is removed from other doctors' view and the patient receives a confirmation email.
    </div>
@endsection
