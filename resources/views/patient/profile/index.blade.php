@extends('patient.layouts.app')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
    <div class="row">
        <div class="col-lg-4 mb-4">
            <!-- Profile Card -->
            <div class="card">
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="{{ $patient->photo_url }}" alt="Profile Picture" 
                             class="rounded-circle" style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                    <h4 class="mb-1">{{ $patient->full_name }}</h4>
                    <p class="text-muted mb-3">Patient ID: {{ $patient->patient_id }}</p>
                    
                    <div class="row text-center mb-3">
                        <div class="col-4">
                            <div class="border-end">
                                <h5 class="mb-1 text-primary">{{ $patient->total_appointments }}</h5>
                                <small class="text-muted">Appointments</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border-end">
                                <h5 class="mb-1 text-success">{{ $patient->age ?? 'N/A' }}</h5>
                                <small class="text-muted">Years Old</small>
                            </div>
                        </div>
                        <div class="col-4">
                            <h5 class="mb-1 text-info">{{ $patient->blood_group ?? 'N/A' }}</h5>
                            <small class="text-muted">Blood Group</small>
                        </div>
                    </div>

                    <a href="{{ route('patient.profile.edit') }}" class="btn btn-primary w-100">
                        <i class="fas fa-edit me-2"></i>
                        Edit Profile
                    </a>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>
                        Quick Stats
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span><i class="fas fa-calendar-check text-primary me-2"></i>Total Appointments</span>
                        <span class="badge bg-primary">{{ $patient->appointments()->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span><i class="fas fa-clock text-warning me-2"></i>Upcoming</span>
                        <span class="badge bg-warning">{{ $patient->upcoming_appointments->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span><i class="fas fa-check-circle text-success me-2"></i>Completed</span>
                        <span class="badge bg-success">{{ $patient->appointments()->where('status', 'completed')->count() }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <span><i class="fas fa-times-circle text-danger me-2"></i>Cancelled</span>
                        <span class="badge bg-danger">{{ $patient->appointments()->where('status', 'cancelled')->count() }}</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>
                            Personal Information
                        </h5>
                        <a href="{{ route('patient.profile.edit') }}" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>
                            Edit
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">First Name</label>
                            <p class="fw-medium">{{ $patient->first_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Last Name</label>
                            <p class="fw-medium">{{ $patient->last_name }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Email</label>
                            <p class="fw-medium">{{ $patient->email }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Phone</label>
                            <p class="fw-medium">{{ $patient->phone }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Date of Birth</label>
                            <p class="fw-medium">{{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Gender</label>
                            <p class="fw-medium">{{ ucfirst($patient->gender) }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Blood Group</label>
                            <p class="fw-medium">{{ $patient->blood_group ?? 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Account Status</label>
                            <p class="fw-medium">
                                <span class="badge bg-{{ $patient->is_active ? 'success' : 'danger' }}">
                                    {{ $patient->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-address-book me-2"></i>
                        Contact Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-muted">Address</label>
                            <p class="fw-medium">{{ $patient->address ?: 'Not provided' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted">City</label>
                            <p class="fw-medium">{{ $patient->city ?: 'Not provided' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted">State</label>
                            <p class="fw-medium">{{ $patient->state ?: 'Not provided' }}</p>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label text-muted">Postal Code</label>
                            <p class="fw-medium">{{ $patient->postal_code ?: 'Not provided' }}</p>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label text-muted">Country</label>
                            <p class="fw-medium">{{ $patient->country ?: 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Emergency Contact -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-phone-alt me-2"></i>
                        Emergency Contact
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Emergency Contact Name</label>
                            <p class="fw-medium">{{ $patient->emergency_contact ?: 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Emergency Phone</label>
                            <p class="fw-medium">{{ $patient->emergency_phone ?: 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Insurance Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-shield-alt me-2"></i>
                        Insurance Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Insurance Provider</label>
                            <p class="fw-medium">{{ $patient->insurance_provider ?: 'Not provided' }}</p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Insurance Number</label>
                            <p class="fw-medium">{{ $patient->insurance_number ?: 'Not provided' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-heartbeat me-2"></i>
                        Medical Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Known Allergies</label>
                            @if($patient->allergies && count($patient->allergies) > 0)
                                <div>
                                    @foreach($patient->allergies as $allergy)
                                        <span class="badge bg-warning text-dark me-1">{{ $allergy }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="fw-medium">No known allergies</p>
                            @endif
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label text-muted">Medical Conditions</label>
                            @if($patient->medical_conditions && count($patient->medical_conditions) > 0)
                                <div>
                                    @foreach($patient->medical_conditions as $condition)
                                        <span class="badge bg-info me-1">{{ $condition }}</span>
                                    @endforeach
                                </div>
                            @else
                                <p class="fw-medium">No known medical conditions</p>
                            @endif
                        </div>
                        @if($patient->notes)
                            <div class="col-md-12">
                                <label class="form-label text-muted">Additional Notes</label>
                                <p class="fw-medium">{{ $patient->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
