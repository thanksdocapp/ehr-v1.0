@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Prescription Details')
@section('page-title', 'Prescription Details')
@section('page-subtitle', 'Prescription #' . $prescription->id . ' - Created ' . formatDate($prescription->created_at))

@section('content')
<div class="fade-in-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">Prescription Details</h1>
                    <p class="text-muted mb-0">
                        Prescription #{{ $prescription->id }} - Created {{ formatDate($prescription->created_at) }}
                    </p>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.prescriptions.index') }}">Prescriptions</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Prescription Status -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-info-circle me-2" style="color: #1a202c;"></i>Prescription Status</h5>
                        <span class="badge bg-{{ $prescription->status === 'dispensed' ? 'success' : ($prescription->status === 'approved' ? 'warning' : 'secondary') }} fs-6">
                            {{ ucfirst($prescription->status) }}
                        </span>
                    </div>
                </div>
                <div class="doctor-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <small class="text-muted">Prescription Type:</small>
                            <div class="fw-bold mb-2">{{ ucfirst(str_replace('_', ' ', $prescription->prescription_type)) }}</div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">Prescription Date:</small>
                            <div class="fw-bold mb-2">{{ $prescription->prescription_date ? formatDate($prescription->prescription_date) : 'Not specified' }}</div>
                        </div>
                        @if($prescription->follow_up_date)
                        <div class="col-md-6">
                            <small class="text-muted">Follow-up Date:</small>
                            <div class="fw-bold mb-2">{{ formatDate($prescription->follow_up_date) }}</div>
                        </div>
                        @endif
                        <div class="col-md-6">
                            <small class="text-muted">Refills Allowed:</small>
                            <div class="fw-bold mb-2">{{ $prescription->refills_allowed ?? 0 }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Alert Bar -->
            @if($prescription->patient)
                @include('components.patient-alert-bar', ['patient' => $prescription->patient])
            @endif

            <!-- Patient Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header py-3">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-user me-2" style="color: #1a202c;"></i>Patient Information</h5>
                </div>
                <div class="doctor-card-body">
                    @if($prescription->patient)
                        <div class="row">
                            <div class="col-md-6">
                                <small class="text-muted">Name:</small>
                                <div class="fw-bold mb-2">{{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Age/Gender:</small>
                                <div class="fw-bold mb-2">
                                    @if($prescription->patient->date_of_birth)
                                        {{ \Carbon\Carbon::parse($prescription->patient->date_of_birth)->age }} years / {{ ucfirst($prescription->patient->gender) }}
                                    @else
                                        {{ ucfirst($prescription->patient->gender) }}
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Phone:</small>
                                <div class="fw-bold mb-2">{{ $prescription->patient->phone ?? 'Not provided' }}</div>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted">Blood Group:</small>
                                <div class="fw-bold mb-2">{{ $prescription->patient->blood_group ?? 'Unknown' }}</div>
                            </div>
                            @if($prescription->patient->allergies)
                            <div class="col-12">
                                <small class="text-muted">Allergies:</small>
                                <div class="fw-bold text-danger">{{ $prescription->patient->allergies }}</div>
                            </div>
                            @endif
                        </div>
                    @else
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Patient Record Deleted:</strong> The patient associated with this prescription has been removed from the system.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Doctor Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header py-3">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-user-md me-2" style="color: #1a202c;"></i>Prescribing Doctor</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar-lg me-3">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                {{ strtoupper(substr($prescription->doctor->user->name, 0, 1)) }}
                            </div>
                        </div>
                        <div>
                            <div class="fw-bold">Dr. {{ $prescription->doctor->user->name }}</div>
                            <div class="text-muted">{{ $prescription->doctor->specialization ?? 'GP' }}</div>
                            <small class="text-muted">{{ $prescription->doctor->user->email }}</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medications -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header py-3">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-pills me-2" style="color: #1a202c;"></i>Prescribed Medications</h5>
                </div>
                <div class="doctor-card-body">
                    @php
                        $medications = $prescription->medications;
                        // Handle both array and string cases
                        if (is_string($medications)) {
                            $medications = json_decode($medications, true) ?? [];
                        }
                        $medications = $medications ?? [];
                    @endphp
                    @if(!empty($medications))
                        @foreach($medications as $index => $medication)
                            <div class="card border-secondary mb-3">
                                <div class="doctor-card-header">
                                    <h6 class="mb-0">Medication {{ $index + 1 }}</h6>
                                </div>
                                <div class="doctor-card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="text-muted">Medication Name:</small>
                                            <div class="fw-bold mb-2">{{ $medication['name'] ?? 'Not specified' }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Strength/Dosage:</small>
                                            <div class="fw-bold mb-2">{{ $medication['dosage'] ?? 'Not specified' }}</div>
                                        </div>
                                        @if(isset($medication['form']) && $medication['form'])
                                        <div class="col-md-6">
                                            <small class="text-muted">Form:</small>
                                            <div class="fw-bold mb-2">{{ ucfirst($medication['form']) }}</div>
                                        </div>
                                        @endif
                                        <div class="col-md-6">
                                            <small class="text-muted">Frequency:</small>
                                            <div class="fw-bold mb-2">{{ ucfirst(str_replace('_', ' ', $medication['frequency'] ?? 'Not specified')) }}</div>
                                        </div>
                                        <div class="col-md-6">
                                            <small class="text-muted">Duration:</small>
                                            <div class="fw-bold mb-2">{{ $medication['duration'] ?? 'Not specified' }}</div>
                                        </div>
                                        @if(isset($medication['instructions']) && $medication['instructions'])
                                        <div class="col-12">
                                            <small class="text-muted">Instructions:</small>
                                            <div class="fw-bold">{{ $medication['instructions'] }}</div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-pills fa-3x mb-3"></i>
                            <p>No medications specified for this prescription.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Additional Information -->
            @if($prescription->diagnosis || $prescription->notes)
            <div class="doctor-card mb-4">
                <div class="doctor-card-header py-3">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-clipboard-list me-2" style="color: #1a202c;"></i>Additional Information</h5>
                </div>
                <div class="doctor-card-body">
                    @if($prescription->diagnosis)
                        <div class="mb-3">
                            <small class="text-muted">Diagnosis:</small>
                            <div class="fw-bold">{{ $prescription->diagnosis }}</div>
                        </div>
                    @endif
                    @if($prescription->notes)
                        <div class="mb-0">
                            <small class="text-muted">Doctor's Notes:</small>
                            <div class="fw-bold">{{ $prescription->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Medical Record Link -->
            @if($prescription->medicalRecord)
            <div class="doctor-card mb-4">
                <div class="doctor-card-header py-3">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-file-medical me-2" style="color: #1a202c;"></i>Related Medical Record</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="fw-bold">{{ formatDate($prescription->medicalRecord->created_at) }}</div>
                            <div class="text-muted">
                                {{ $prescription->medicalRecord->presenting_complaint ?? $prescription->medicalRecord->chief_complaint ?? ($prescription->medicalRecord->assessment ?? 'No diagnosis specified') }}
                            </div>
                        </div>
                        <a href="{{ route('staff.medical-records.show', $prescription->medicalRecord->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>View Record
                        </a>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header py-3">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-cogs me-2" style="color: #1a202c;"></i>Actions</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="d-grid gap-2">
                        @if(auth()->user()->role === 'doctor' || (auth()->user()->role === 'pharmacist' && in_array($prescription->status, ['pending', 'approved'])))
                            <a href="{{ route('staff.prescriptions.edit', $prescription->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i>Edit Prescription
                            </a>
                        @endif
                        
                        @if(auth()->user()->role === 'pharmacist' && $prescription->status === 'approved')
                            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#dispensePrescriptionModal">
                                <i class="fas fa-check me-1"></i>Mark as Dispensed
                            </button>
                        @endif

                        @if(auth()->user()->role === 'doctor' && $prescription->status === 'pending')
                            <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#approvePrescriptionModal">
                                <i class="fas fa-check me-1"></i>Approve Prescription
                            </button>
                        @endif

                        <button type="button" class="btn btn-info" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Print Prescription
                        </button>
                        
                        <a href="{{ route('staff.prescriptions.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Prescription Timeline -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header py-3">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-history me-2" style="color: #1a202c;"></i>Timeline</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Prescription Created</h6>
                                <p class="timeline-text text-muted">{{ formatDateTime($prescription->created_at) }}</p>
                                <small class="text-muted">by {{ $prescription->createdBy->name ?? 'Unknown' }}</small>
                            </div>
                        </div>

                        @if($prescription->status === 'approved' || $prescription->status === 'dispensed')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-warning"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Prescription Approved</h6>
                                <p class="timeline-text text-muted">{{ formatDateTime($prescription->updated_at) }}</p>
                                <small class="text-muted">by {{ $prescription->updatedBy->name ?? 'Doctor' }}</small>
                            </div>
                        </div>
                        @endif

                        @if($prescription->status === 'dispensed')
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success"></div>
                            <div class="timeline-content">
                                <h6 class="timeline-title">Prescription Dispensed</h6>
                                <p class="timeline-text text-muted">{{ formatDateTime($prescription->updated_at) }}</p>
                                <small class="text-muted">by {{ $prescription->updatedBy->name ?? 'Pharmacist' }}</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="card border-info">
                <div class="doctor-card-body">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="fas fa-info-circle text-info fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-info">Prescription Info</h6>
                            <ul class="mb-0 text-muted small">
                                <li class="mb-1"><strong>Total Medications:</strong> {{ count($medications) }}</li>
                                <li class="mb-1"><strong>Created:</strong> {{ $prescription->created_at->diffForHumans() }}</li>
                                @if($prescription->updated_at != $prescription->created_at)
                                <li class="mb-1"><strong>Last Updated:</strong> {{ $prescription->updated_at->diffForHumans() }}</li>
                                @endif
                                @if($prescription->follow_up_date)
                                <li><strong>Follow-up:</strong> {{ \Carbon\Carbon::parse($prescription->follow_up_date)->diffForHumans() }}</li>
                                @endif
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding-left: 30px;
}

.timeline-item {
    position: relative;
    margin-bottom: 20px;
}

.timeline-item:last-child {
    margin-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -35px;
    top: 0;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    border: 2px solid #fff;
    box-shadow: 0 0 0 2px #dee2e6;
}

.timeline::before {
    content: '';
    position: absolute;
    left: -30px;
    top: 6px;
    bottom: 0;
    width: 2px;
    background: #dee2e6;
}

.timeline-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}

.timeline-title {
    margin-bottom: 5px;
    font-size: 14px;
    font-weight: 600;
}

.timeline-text {
    margin-bottom: 5px;
    font-size: 13px;
}

@media print {
    .doctor-card-header, .btn, nav, .timeline, .card:last-child {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    
    .doctor-card-body {
        padding: 10px !important;
    }
}
</style>

<!-- Approve Prescription Modal -->
@if(auth()->user()->role === 'doctor' && $prescription->status === 'pending')
<div class="modal fade" id="approvePrescriptionModal" tabindex="-1" aria-labelledby="approvePrescriptionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="z-index: 1055;">
        <div class="modal-content" style="position: relative; z-index: 1056;">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approvePrescriptionModalLabel">
                    <i class="fas fa-check-circle me-2"></i>Approve Prescription
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="fas fa-prescription-bottle-alt text-success" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-dark mb-3">Confirm Prescription Approval</h4>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Prescription Details:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Patient: <strong>{{ $prescription->patient ? $prescription->patient->first_name . ' ' . $prescription->patient->last_name : 'Unknown Patient' }}</strong></li>
                        <li>Total Medications: <strong>{{ count($medications) }}</strong></li>
                        <li>Created: <strong>{{ formatDateTime($prescription->created_at) }}</strong></li>
                    </ul>
                </div>
                
                <p class="text-muted mb-0">
                    <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                    Are you sure you want to approve this prescription? Once approved, it will be available for dispensing by the pharmacy.
                </p>
            </div>
            <div class="modal-footer" style="position: relative; z-index: 1050;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirmApproveBtn" style="position: relative; z-index: 1051;">
                    <i class="fas fa-check me-1"></i>Yes, Approve Prescription
                </button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Dispense Prescription Modal -->
@if(auth()->user()->role === 'pharmacist' && $prescription->status === 'approved')
<div class="modal fade" id="dispensePrescriptionModal" tabindex="-1" aria-labelledby="dispensePrescriptionModalLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="z-index: 1055;">
        <div class="modal-content" style="position: relative; z-index: 1056;">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="dispensePrescriptionModalLabel">
                    <i class="fas fa-pills me-2"></i>Mark as Dispensed
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="fas fa-hand-holding-medical text-primary" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-dark mb-3">Confirm Medication Dispensing</h4>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Important:</strong> Please ensure you have:
                    <ul class="mb-0 mt-2">
                        <li>Verified patient identity</li>
                        <li>Prepared all prescribed medications</li>
                        <li>Provided proper instructions to the patient</li>
                        <li>Completed all necessary documentation</li>
                    </ul>
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Prescription Summary:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Patient: <strong>{{ $prescription->patient ? $prescription->patient->first_name . ' ' . $prescription->patient->last_name : 'Unknown Patient' }}</strong></li>
                        <li>Doctor: <strong>Dr. {{ $prescription->doctor->user->name }}</strong></li>
                        <li>Total Medications: <strong>{{ count($medications) }}</strong></li>
                    </ul>
                </div>
                
                <p class="text-muted mb-0">
                    <i class="fas fa-check-circle text-success me-2"></i>
                    Mark this prescription as dispensed once all medications have been provided to the patient.
                </p>
            </div>
            <div class="modal-footer" style="position: relative; z-index: 1050;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancel
                </button>
                <button type="button" class="btn btn-success" id="confirmDispenseBtn" style="position: relative; z-index: 1051;">
                    <i class="fas fa-check me-1"></i>Yes, Mark as Dispensed
                </button>
            </div>
        </div>
    </div>
</div>
@endif

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    let currentPrescriptionId = {{ $prescription->id }};
    
    // Confirm Approve Prescription
    $('#confirmApproveBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        updatePrescriptionStatus(currentPrescriptionId, 'approved');
        return false;
    });
    
    // Confirm Dispense Prescription  
    $('#confirmDispenseBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        updatePrescriptionStatus(currentPrescriptionId, 'dispensed');
        return false;
    });
    
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 30000);
    
    // Add animation effects to modals
    $('#approvePrescriptionModal, #dispensePrescriptionModal').on('shown.bs.modal', function () {
        $(this).find('.modal-content').addClass('animate__animated animate__fadeInDown');
    });
    
    $('#approvePrescriptionModal, #dispensePrescriptionModal').on('hidden.bs.modal', function () {
        $(this).find('.modal-content').removeClass('animate__animated animate__fadeInDown');
    });
});

// Update prescription status via AJAX
function updatePrescriptionStatus(prescriptionId, status) {
    // Show loading state
    const loadingText = {
        'approved': 'Approving...',
        'dispensed': 'Marking as Dispensed...'
    };
    
    const buttonSelector = status === 'approved' ? '#confirmApproveBtn' : '#confirmDispenseBtn';
    const originalButtonText = $(buttonSelector).html();
    
    $(buttonSelector).prop('disabled', true).html(
        `<i class="fas fa-spinner fa-spin me-1"></i>${loadingText[status]}`
    );
    
    $.ajax({
        url: `/staff/prescriptions/${prescriptionId}/status`,
        method: 'PATCH',
        data: {
            status: status,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                // Show success message with animation
                const successMessages = {
                    'approved': 'Prescription approved successfully!',
                    'dispensed': 'Prescription marked as dispensed successfully!'
                };
                
                // Hide modal first
                $('.modal').modal('hide');
                
                // Show success toast
                showSuccessMessage(successMessages[status]);
                
                // Reload page after short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showErrorMessage('Error updating status: ' + (response.message || 'Unknown error'));
                // Reset button
                $(buttonSelector).prop('disabled', false).html(originalButtonText);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Error updating prescription status. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showErrorMessage(errorMessage);
            // Reset button
            $(buttonSelector).prop('disabled', false).html(originalButtonText);
        }
    });
}

// Success message function
function showSuccessMessage(message) {
    const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;"
             role="alert">
            <i class="fas fa-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('body').append(alertHtml);
    
    // Auto-dismiss after 3 seconds
    setTimeout(() => {
        $('.alert-success').fadeOut(() => {
            $('.alert-success').remove();
        });
    }, 3000);
}

// Error message function
function showErrorMessage(message) {
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;"
             role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('body').append(alertHtml);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        $('.alert-danger').fadeOut(() => {
            $('.alert-danger').remove();
        });
    }, 5000);
}
</script>
@endpush
