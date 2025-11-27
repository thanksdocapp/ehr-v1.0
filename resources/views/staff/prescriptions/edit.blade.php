@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Edit Prescription')

@section('content')

<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">Edit Prescription</h1>
                    <p class="text-muted mb-0">Update prescription information</p>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.prescriptions.index') }}">Prescriptions</a></li>
                        <li class="breadcrumb-item active">Edit Prescription</li>
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

    <form action="{{ route('staff.prescriptions.update', $prescription->id) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Prescription Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header py-3">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-file-medical me-2" style="color: #1a202c;"></i>Prescription Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="patient_id" class="form-label fw-semibold">Patient <span class="text-danger">*</span></label>
                                <select name="patient_id" id="patient_id" class="form-select" required>
                                    <option value="">Select Patient</option>
                                    @foreach($patients as $patient)
                                        <option value="{{ $patient->id }}" {{ $prescription->patient_id == $patient->id ? 'selected' : '' }}>{{ $patient->first_name }} {{ $patient->last_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="doctor_id" class="form-label fw-semibold">Doctor</label>
                                <input type="text" class="form-control" readonly value="Dr. {{ $prescription->doctor->first_name }} {{ $prescription->doctor->last_name }}">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="prescribed_date" class="form-label fw-semibold">Prescribed Date <span class="text-danger">*</span></label>
                                <input type="date" name="prescribed_date" class="form-control" value="{{ $prescription->prescribed_date->format('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status" class="form-label fw-semibold">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select" required>
                                    <option value="pending" {{ $prescription->status == 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="dispensed" {{ $prescription->status == 'dispensed' ? 'selected' : '' }}>Dispensed</option>
                                    <option value="cancelled" {{ $prescription->status == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="notes" class="form-label fw-semibold">Notes</label>
                            <textarea name="notes" id="notes" rows="3" class="form-control">{{ $prescription->notes }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Medication Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header py-3">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-pills me-2" style="color: #1a202c;"></i>Medications</h5>
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
                        @foreach($medications as $index => $medication)
                            <div class="medication-item border p-3 mb-3">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Medication Name <span class="text-danger">*</span></label>
                                        <input type="text" name="medications[{{ $index }}][name]" class="form-control" value="{{ $medication['name'] ?? '' }}" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label fw-semibold">Dosage <span class="text-danger">*</span></label>
                                        <input type="text" name="medications[{{ $index }}][dosage]" class="form-control" value="{{ $medication['dosage'] ?? '' }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Frequency <span class="text-danger">*</span></label>
                                        <select name="medications[{{ $index }}][frequency]" class="form-select" required>
                                            <option value="Once daily" {{ ($medication['frequency'] ?? '') == 'Once daily' ? 'selected' : '' }}>Once daily</option>
                                            <option value="Twice daily" {{ ($medication['frequency'] ?? '') == 'Twice daily' ? 'selected' : '' }}>Twice daily</option>
                                            <option value="Three times daily" {{ ($medication['frequency'] ?? '') == 'Three times daily' ? 'selected' : '' }}>Three times daily</option>
                                            <option value="Four times daily" {{ ($medication['frequency'] ?? '') == 'Four times daily' ? 'selected' : '' }}>Four times daily</option>
                                            <option value="Every 4 hours" {{ ($medication['frequency'] ?? '') == 'Every 4 hours' ? 'selected' : '' }}>Every 4 hours</option>
                                            <option value="Every 6 hours" {{ ($medication['frequency'] ?? '') == 'Every 6 hours' ? 'selected' : '' }}>Every 6 hours</option>
                                            <option value="Every 8 hours" {{ ($medication['frequency'] ?? '') == 'Every 8 hours' ? 'selected' : '' }}>Every 8 hours</option>
                                            <option value="As needed" {{ ($medication['frequency'] ?? '') == 'As needed' ? 'selected' : '' }}>As needed</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-semibold">Duration <span class="text-danger">*</span></label>
                                        <input type="text" name="medications[{{ $index }}][duration]" class="form-control" value="{{ $medication['duration'] ?? '' }}" required>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Quantity</label>
                                        <input type="number" name="medications[{{ $index }}][quantity]" class="form-control" value="{{ $medication['quantity'] ?? '' }}">
                                    </div>
                                </div>
                                <div class="mb-0">
                                    <label class="form-label">Instructions</label>
                                    <textarea name="medications[{{ $index }}][instructions]" class="form-control" rows="2">{{ $medication['instructions'] ?? '' }}</textarea>
                                </div>
                            </div>
                        @endforeach
                        <div id="medicationsContainer"></div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header bg-light py-3">
                        <h6 class="doctor-card-title mb-0 fw-semibold">Actions</h6>
                    </div>
                    <div class="doctor-card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-save me-2"></i>Update Prescription
                            </button>
                            <a href="{{ route('staff.prescriptions.show', $prescription->id) }}" class="btn btn-info">
                                <i class="fas fa-eye me-2"></i>View Prescription
                            </a>
                            <a href="{{ route('staff.prescriptions.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Current Information -->
                <div class="card shadow-sm">
                    <div class="doctor-card-header py-3">
                        <h6 class="doctor-card-title mb-0">Current Information</h6>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Prescription ID</small>
                            <strong>#{{ $prescription->id }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Created Date</small>
                            <strong>{{ $prescription->created_at->format('M d, Y') }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Last Updated</small>
                            <strong>{{ $prescription->updated_at->format('M d, Y g:i A') }}</strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block">Total Medications</small>
                            <strong>{{ count($medications) }} items</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Medication Template (Hidden) -->
<template id="medicationTemplate">
    <div class="medication-item border p-3 mb-3">
        <div class="row">
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Medication Name <span class="text-danger">*</span></label>
                <input type="text" name="medications[INDEX][name]" class="form-control" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label fw-semibold">Dosage <span class="text-danger">*</span></label>
                <input type="text" name="medications[INDEX][dosage]" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-semibold">Frequency <span class="text-danger">*</span></label>
                <select name="medications[INDEX][frequency]" class="form-select" required>
                    <option value="Once daily">Once daily</option>
                    <option value="Twice daily">Twice daily</option>
                    <option value="Three times daily">Three times daily</option>
                    <option value="Four times daily">Four times daily</option>
                    <option value="Every 4 hours">Every 4 hours</option>
                    <option value="Every 6 hours">Every 6 hours</option>
                    <option value="Every 8 hours">Every 8 hours</option>
                    <option value="As needed">As needed</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label fw-semibold">Duration <span class="text-danger">*</span></label>
                <input type="text" name="medications[INDEX][duration]" class="form-control" required>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="medications[INDEX][quantity]" class="form-control">
            </div>
        </div>
        <div class="mb-0">
            <label class="form-label">Instructions</label>
            <textarea name="medications[INDEX][instructions]" class="form-control" rows="2"></textarea>
        </div>
    </div>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let medicationIndex = {{ count($medications) }};
    const medicationsContainer = document.getElementById('medicationsContainer');
    const medicationTemplate = document.getElementById('medicationTemplate');

    // Add medication functionality
    document.querySelector('.btn-info').addEventListener('click', function(e) {
        e.preventDefault();
        const template = medicationTemplate.content.cloneNode(true);
        const newItemHTML = template.innerHTML.replace(/INDEX/g, medicationIndex);
        medicationsContainer.insertAdjacentHTML('beforeend', newItemHTML);
        medicationIndex++;
    });

    // Remove medication functionality
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-close')) {
            e.target.closest('.medication-item').remove();
        }
    });
});
</script>
@endpush
@endsection

