@extends('admin.layouts.app')

@section('title', 'Prescription Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('prescriptions.index') }}">Prescriptions</a></li>
    <li class="breadcrumb-item active">Prescription #{{ $prescription->id }}</li>
@endsection

@push('styles')
<style>
.prescription-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
    overflow: hidden;
}

.prescription-section-header {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
    padding: 1.5rem 2rem;
}

.prescription-section-body {
    padding: 2rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #5a5c69;
    min-width: 150px;
}

.info-value {
    color: #858796;
    flex: 1;
}

.badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-pending {
    background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
    color: white;
}

.badge-approved {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
}

.badge-dispensed {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    color: white;
}

.badge-cancelled {
    background: linear-gradient(135deg, #e74a3b 0%, #c0392b 100%);
    color: white;
}

.text-area-content {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1rem;
    color: #5a5c69;
    line-height: 1.6;
    white-space: pre-wrap;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    border: none;
    color: white;
    box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
    color: white;
}

.btn-secondary {
    background: #858796;
    border: none;
    color: white;
}

.btn-secondary:hover {
    background: #5a5c69;
    color: white;
}

.btn-danger {
    background: #e74a3b;
    border: none;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
    color: white;
}

@media (max-width: 768px) {
    .action-buttons {
        justify-content: center;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-prescription-bottle-alt me-2 text-primary"></i>Prescription Details</h1>
        <p class="page-subtitle text-muted">View comprehensive prescription information</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Prescription Information -->
            <div class="prescription-section">
                <div class="prescription-section-header">
                    <h4 class="mb-0"><i class="fas fa-prescription-bottle me-2"></i>Prescription Details</h4>
                    <small class="opacity-75">Basic prescription information</small>
                </div>
                <div class="prescription-section-body">
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-hashtag me-1"></i>Prescription ID:</div>
                        <div class="info-value">#{{ $prescription->id }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-user me-1"></i>Patient:</div>
                        <div class="info-value">
                            <strong>{{ $prescription->patient->full_name }}</strong>
                            <small class="text-muted">({{ $prescription->patient->patient_number }})</small>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-user-md me-1"></i>Doctor:</div>
                        <div class="info-value">
                            <strong>Dr. {{ $prescription->doctor->full_name }}</strong>
                            <small class="text-muted">({{ $prescription->doctor->specialization }})</small>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-file-alt me-1"></i>Prescription Type:</div>
                        <div class="info-value">
                            <span class="badge badge-{{ $prescription->prescription_type }}">
                                {{ ucfirst(str_replace('_', ' ', $prescription->prescription_type)) }}
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-shield-alt me-1"></i>Status:</div>
                        <div class="info-value">
                            <span class="badge badge-{{ $prescription->status }}">
                                {{ ucfirst($prescription->status) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medications -->
            <div class="prescription-section">
                <div class="prescription-section-header">
                    <h4 class="mb-0"><i class="fas fa-pills me-2"></i>Medications</h4>
                    <small class="opacity-75">List of prescribed medications</small>
                </div>
                <div class="prescription-section-body">
                    @php
                        $medications = $prescription->medications;
                        // Handle case where medications might be stored as JSON string
                        if (is_string($medications)) {
                            $medications = json_decode($medications, true) ?? [];
                        }
                        // Ensure it's an array
                        if (!is_array($medications)) {
                            $medications = [];
                        }
                    @endphp
                    
                    @if(count($medications) > 0)
                        @foreach($medications as $medication)
                        <div class="mb-4">
                            <h6><i class="fas fa-tablets me-1"></i>{{ $medication['name'] ?? 'Unknown Medication' }}</h6>
                            <div class="text-area-content">
                                Dosage: {{ $medication['dosage'] ?? 'Not specified' }}, 
                                Frequency: {{ $medication['frequency'] ?? 'Not specified' }}, 
                                Duration: {{ $medication['duration'] ?? 'Not specified' }}
                            </div>
                            @if(!empty($medication['instructions']))
                            <div class="text-area-content mt-2">Instructions: {{ $medication['instructions'] }}</div>
                            @endif
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

            <!-- Action Buttons -->
            <div class="prescription-section">
                <div class="prescription-section-body text-center">
                    <div class="action-buttons">
                        <a href="{{ contextRoute('prescriptions.edit', $prescription->id) }}" class="btn btn-doctor-primary">
                            <i class="fas fa-edit me-2"></i>Edit Prescription
                        </a>
                        <a href="{{ contextRoute('prescriptions.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Prescriptions
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Information -->
        <div class="col-lg-4">
            <div class="quick-info-card">
                <h6><i class="fas fa-clock me-2"></i>Prescription Timeline</h6>
                <div class="timeline-item">
                    <i class="fas fa-plus-circle timeline-icon"></i>
                    Created: {{ formatDateTime($prescription->created_at) }}
                </div>
                <div class="timeline-item">
                    <i class="fas fa-edit timeline-icon"></i>
                    Last Updated: {{ formatDateTime($prescription->updated_at) }}
                </div>
            </div>
        
            <div class="quick-info-card">
                <h6><i class="fas fa-user-circle me-2"></i>Patient Information</h6>
                <div class="timeline-item">
                    <i class="fas fa-id-card timeline-icon"></i>
                    Patient ID: {{ $prescription->patient->patient_number }}
                </div>
                <div class="timeline-item">
                    <i class="fas fa-birthday-cake timeline-icon"></i>
                    Age: {{ \Carbon\Carbon::parse($prescription->patient->date_of_birth)->age }} years
                </div>
                <div class="timeline-item">
                    <i class="fas fa-venus-mars timeline-icon"></i>
                    Gender: {{ ucfirst($prescription->patient->gender) }}
                </div>
                <div class="timeline-item">
                    <i class="fas fa-phone timeline-icon"></i>
                    Phone: {{ $prescription->patient->phone }}
                </div>
            </div>
        
            <div class="quick-info-card">
                <h6><i class="fas fa-user-md me-2"></i>Doctor Information</h6>
                <div class="timeline-item">
                    <i class="fas fa-graduation-cap timeline-icon"></i>
                    Specialisation: {{ $prescription->doctor->specialization }}
                </div>
                <div class="timeline-item">
                    <i class="fas fa-phone timeline-icon"></i>
                    Phone: {{ $prescription->doctor->phone }}
                </div>
                <div class="timeline-item">
                    <i class="fas fa-envelope timeline-icon"></i>
                    Email: {{ $prescription->doctor->email }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" style="z-index: 1055;">
        <div class="modal-content" style="position: relative; z-index: 1056;">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this prescription?</p>
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
            </div>
            <div class="modal-footer" style="position: relative; z-index: 1050;">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn" style="position: relative; z-index: 1051;">Delete Prescription</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function printRecord() {
    window.print();
}

// Handle delete confirmation
$(document).ready(function() {
    $('#confirmDeleteBtn').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        // Get the prescription ID from the URL or data attribute
        const prescriptionId = {{ $prescription->id }};
        const form = $('<form>', {
            'method': 'POST',
            'action': '{{ contextRoute("prescriptions.destroy", $prescription->id) }}'
        });
        form.append($('<input>', {
            'type': 'hidden',
            'name': '_token',
            'value': '{{ csrf_token() }}'
        }));
        form.append($('<input>', {
            'type': 'hidden',
            'name': '_method',
            'value': 'DELETE'
        }));
        $('body').append(form);
        form.submit();
        return false;
    });
});
</script>
@endpush

