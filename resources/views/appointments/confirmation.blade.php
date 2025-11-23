@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white text-center">
                    <h3 class="mb-0">
                        <i class="fas fa-check-circle"></i> Appointment Confirmed
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <div class="alert alert-success">
                            <h4 class="alert-heading">Your appointment has been successfully booked!</h4>
                            <p class="mb-0">You will receive a confirmation email shortly with all the details.</p>
                        </div>
                    </div>

                    <!-- Appointment Details -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary mb-4">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="mb-0"><i class="fas fa-calendar-alt"></i> Appointment Details</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Appointment #:</strong></div>
                                        <div class="col-sm-7">{{ $appointment->appointment_number }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Date:</strong></div>
                                        <div class="col-sm-7">{{ \Carbon\Carbon::parse($appointment->appointment_date)->format('l, F j, Y') }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Time:</strong></div>
                                        <div class="col-sm-7">{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Status:</strong></div>
                                        <div class="col-sm-7">
                                            <span class="badge badge-warning">{{ ucfirst($appointment->status) }}</span>
                                        </div>
                                    </div>
                                    @if($appointment->symptoms)
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Reason:</strong></div>
                                        <div class="col-sm-7">{{ $appointment->symptoms }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Doctor Information -->
                            <div class="card border-info mb-4">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0"><i class="fas fa-user-md"></i> Doctor Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Doctor:</strong></div>
                                        <div class="col-sm-7">{{ $appointment->doctor->full_name }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Department:</strong></div>
                                        <div class="col-sm-7">{{ $appointment->department->name }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Specialization:</strong></div>
                                        <div class="col-sm-7">{{ $appointment->doctor->specialization }}</div>
                                    </div>
                                    @if($appointment->doctor->phone)
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Phone:</strong></div>
                                        <div class="col-sm-7">{{ $appointment->doctor->phone }}</div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Patient Information -->
                            <div class="card border-secondary mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h5 class="mb-0"><i class="fas fa-user"></i> Patient Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Name:</strong></div>
                                        <div class="col-sm-7">{{ $appointment->patient->full_name }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Email:</strong></div>
                                        <div class="col-sm-7">{{ $appointment->patient->email }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Phone:</strong></div>
                                        <div class="col-sm-7">{{ $appointment->patient->phone }}</div>
                                    </div>
                                    <div class="row mb-3">
                                        <div class="col-sm-5"><strong>Patient ID:</strong></div>
                                        <div class="col-sm-7">{{ $appointment->patient->patient_id }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Important Notes -->
                    <div class="alert alert-info">
                        <h5 class="alert-heading"><i class="fas fa-info-circle"></i> Important Notes</h5>
                        <ul class="mb-0">
                            <li>Please arrive 15 minutes before your appointment time</li>
                            <li>Bring a valid ID and insurance card (if applicable)</li>
                            <li>Your appointment is currently <strong>{{ ucfirst($appointment->status) }}</strong> and will be confirmed by our staff</li>
                            <li>You will receive a confirmation call/email within 24 hours</li>
                        </ul>
                    </div>

                    <!-- Action Buttons -->
                    <div class="text-center mt-4">
                        <a href="{{ route('appointments.dashboard', $appointment->patient->patient_id) }}" class="btn btn-primary btn-lg mr-3">
                            <i class="fas fa-tachometer-alt"></i> My Appointments
                        </a>
                        <a href="{{ route('homepage') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                    </div>

                    <!-- Contact Information -->
                    <div class="mt-4 text-center">
                        <p class="text-muted">
                            <strong>Questions?</strong> Call us at {{ $site_settings['phone'] ?? '(555) 123-4567' }} 
                            or email {{ $site_settings['email'] ?? 'info@hospital.com' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Auto-print option
    document.addEventListener('DOMContentLoaded', function() {
        // Add print button functionality
        const printButton = document.createElement('button');
        printButton.className = 'btn btn-outline-primary btn-sm';
        printButton.innerHTML = '<i class="fas fa-print"></i> Print';
        printButton.onclick = function() {
            window.print();
        };
        
        // Add to action buttons area
        const actionArea = document.querySelector('.text-center.mt-4');
        if (actionArea) {
            actionArea.appendChild(printButton);
        }
    });
</script>
@endsection
