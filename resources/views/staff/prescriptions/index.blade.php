@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Prescriptions')
@section('page-title', 'Prescriptions')
@section('page-subtitle', 'Manage all prescriptions and medications')

@section('content')
<div class="fade-in-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0 text-gray-900">Prescriptions</h1>
                    <p class="text-muted mb-0">
                        @if(auth()->user()->role === 'doctor')
                            Create and manage prescriptions - Full access
                        @elseif(auth()->user()->role === 'pharmacist')
                            Dispense and manage prescription status
                        @else
                            View prescriptions you're involved with
                        @endif
                    </p>
                </div>
                
                @if(in_array(auth()->user()->role, ['doctor', 'pharmacist']))
                    <div class="btn-group">
                        <a href="{{ route('staff.prescriptions.create') }}" class="btn btn-doctor-primary">
                            <i class="fas fa-plus me-2"></i>New Prescription
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card h-100" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-primary" style="font-size: 1.75rem; font-weight: 600;">{{ $prescriptions->total() }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Total Prescriptions</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-prescription-bottle-alt text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card h-100" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-warning" style="font-size: 1.75rem; font-weight: 600;">
                            {{ $prescriptions->filter(function($prescription) { return $prescription->status === 'pending'; })->count() }}
                        </div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Pending</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning), #d97706); width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card stat-card h-100" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-success" style="font-size: 1.75rem; font-weight: 600;">
                            {{ $prescriptions->filter(function($prescription) { return $prescription->status === 'dispensed'; })->count() }}
                        </div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Dispensed</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--success), #16a34a); width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        @if(auth()->user()->role === 'doctor')
        <div class="col-xl-3 col-md-6">
            <div class="card stat-card h-100" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-info" style="font-size: 1.75rem; font-weight: 600;">
                            {{ $prescriptions->filter(function($prescription) { return $prescription->doctor_id == auth()->id(); })->count() }}
                        </div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">My Prescriptions</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--info), #0891b2); width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-user-md text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-filter me-2"></i>Filter Prescriptions
            </h6>
        </div>
        <div class="doctor-card-body">
            <form method="GET" action="{{ route('staff.prescriptions.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label for="patient_search" class="form-label">Patient Name</label>
                    <input type="text" name="patient_search" id="patient_search" class="form-control" 
                           placeholder="Search by name..." value="{{ request('patient_search') }}">
                </div>
                <div class="col-md-2">
                    <label for="status" class="form-label">Status</label>
                    <select name="status" id="status" class="form-control">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="dispensed" {{ request('status') === 'dispensed' ? 'selected' : '' }}>Dispensed</option>
                        <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label for="date_from" class="form-label">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" 
                           value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label for="date_to" class="form-label">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" 
                           value="{{ request('date_to') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-doctor-primary">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                        <a href="{{ route('staff.prescriptions.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Prescriptions Table -->
    <div class="card">
        <div class="card-header">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list me-2"></i>Prescriptions
                <small class="text-muted">({{ $prescriptions->total() }} total)</small>
            </h6>
        </div>
        <div class="doctor-card-body">
            @if($prescriptions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="prescriptionsTable">
                        <thead class="table-light">
                            <tr>
                                <th>Prescription #</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Medications</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($prescriptions as $prescription)
                            <tr>
                                <td>
                                    <div class="fw-bold text-primary">{{ $prescription->prescription_number }}</div>
                                    @if($prescription->follow_up_date)
                                        <small class="text-muted">Follow-up: {{ $prescription->follow_up_date->format('M d, Y') }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($prescription->patient)
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-3">
                                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    {{ strtoupper(substr($prescription->patient->first_name, 0, 1)) }}
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $prescription->patient->first_name }} {{ $prescription->patient->last_name }}</div>
                                                <small class="text-muted">{{ $prescription->patient->phone ?? 'No phone' }}</small>
                                            </div>
                                        </div>
                                    @else
                                        <div class="text-muted">
                                            <i class="fas fa-user-slash me-1"></i>Patient record deleted
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($prescription->doctor)
                                        <div class="fw-bold">{{ $prescription->doctor->name }}</div>
                                        <small class="text-muted">{{ $prescription->doctor->specialization ?? 'General' }}</small>
                                    @else
                                        <span class="text-muted">Pharmacist Issued</span>
                                    @endif
                                </td>
                                <td>
                                    @if($prescription->medications && is_array($prescription->medications))
                                        @php $medications = $prescription->medications; @endphp
                                        <div class="fw-bold">{{ count($medications) }} medication(s)</div>
                                        @if(count($medications) > 0)
                                            <small class="text-muted">{{ $medications[0]['name'] ?? 'N/A' }}{{ count($medications) > 1 ? ' +' . (count($medications) - 1) . ' more' : '' }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">No medications</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $prescription->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $prescription->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'approved' => 'info',
                                            'dispensed' => 'success',
                                            'cancelled' => 'danger'
                                        ];
                                        $color = $statusColors[$prescription->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">{{ ucfirst($prescription->status) }}</span>
                                    @if($prescription->dispensed_at)
                                        <div><small class="text-muted">{{ $prescription->dispensed_at->format('M d, Y') }}</small></div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('staff.prescriptions.show', $prescription) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        {{-- Edit button with proper permissions --}}
                                        @php
                                            $userDoctorId = null;
                                            if (auth()->user()->role === 'doctor') {
                                                $doctor = \DB::table('doctors')->where('user_id', auth()->id())->first();
                                                $userDoctorId = $doctor ? $doctor->id : null;
                                            }
                                        @endphp
                                        
                                        @if(
                                            auth()->user()->role === 'admin' ||
                                            (auth()->user()->role === 'doctor' && $prescription->doctor_id === $userDoctorId && !in_array($prescription->status, ['dispensed', 'cancelled'])) ||
                                            (auth()->user()->role === 'pharmacist' && in_array($prescription->status, ['pending', 'approved']))
                                        )
                                            <a href="{{ route('staff.prescriptions.edit', $prescription) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit Prescription">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        
                                        {{-- Approve prescription (doctors only) --}}
                                        @if(auth()->user()->role === 'doctor' && $prescription->status === 'pending' && $prescription->doctor_id === $userDoctorId)
                                            <button class="btn btn-sm btn-outline-info" 
                                                    data-bs-toggle="modal" data-bs-target="#approvePrescriptionModal"
                                                    data-prescription-id="{{ $prescription->id }}"
                                                    data-patient-name="{{ $prescription->patient ? $prescription->patient->first_name . ' ' . $prescription->patient->last_name : 'Unknown Patient' }}"
                                                    title="Approve Prescription">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        @endif
                                        
                                        {{-- Dispense prescription (pharmacists only) --}}
                                        @if(auth()->user()->role === 'pharmacist' && $prescription->status === 'approved')
                                            <button class="btn btn-sm btn-outline-success" 
                                                    data-bs-toggle="modal" data-bs-target="#dispensePrescriptionModal"
                                                    data-prescription-id="{{ $prescription->id }}"
                                                    data-patient-name="{{ $prescription->patient ? $prescription->patient->first_name . ' ' . $prescription->patient->last_name : 'Unknown Patient' }}"
                                                    data-doctor-name="{{ $prescription->doctor->name ?? 'Unknown' }}"
                                                    title="Mark as Dispensed">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        
                                        {{-- Print prescription --}}
                                        <button class="btn btn-sm btn-outline-info" 
                                                onclick="printPrescription({{ $prescription->id }})" 
                                                title="Print Prescription">
                                            <i class="fas fa-print"></i>
                                        </button>
                                        
                                        {{-- Cancel prescription --}}
                                        @if(
                                            !in_array($prescription->status, ['dispensed', 'cancelled']) &&
                                            (
                                                auth()->user()->role === 'admin' ||
                                                (auth()->user()->role === 'doctor' && $prescription->doctor_id === $userDoctorId) ||
                                                auth()->user()->role === 'pharmacist'
                                            )
                                        )
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    data-bs-toggle="modal" data-bs-target="#cancelPrescriptionModal"
                                                    data-prescription-id="{{ $prescription->id }}"
                                                    data-patient-name="{{ $prescription->patient ? $prescription->patient->first_name . ' ' . $prescription->patient->last_name : 'Unknown Patient' }}"
                                                    title="Cancel Prescription">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $prescriptions->firstItem() }} to {{ $prescriptions->lastItem() }} 
                        of {{ $prescriptions->total() }} results
                    </div>
                    <div>
                        {{ $prescriptions->appends(request()->query())->links() }}
                    </div>
                </div>
            @else
                <div class="text-center py-5">
                    <div class="mb-3">
                        <i class="fas fa-prescription-bottle-alt fa-3x text-muted"></i>
                    </div>
                    <h5 class="text-muted">No Prescriptions Found</h5>
                    <p class="text-muted mb-4">
                        @if(in_array(auth()->user()->role, ['doctor', 'pharmacist']))
                            Start by creating your first prescription.
                        @else
                            No prescriptions available to view at this time.
                        @endif
                    </p>
                    
                    @if(in_array(auth()->user()->role, ['doctor', 'pharmacist']))
                        <a href="{{ route('staff.prescriptions.create') }}" class="btn btn-doctor-primary">
                            <i class="fas fa-plus me-2"></i>Create First Prescription
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Status Update Modal -->
@if(auth()->user()->role === 'pharmacist')
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Update Prescription Status</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="statusForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="status_select" class="form-label">New Status</label>
                        <select id="status_select" class="form-control" required>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="dispensed">Dispensed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="pharmacist_notes" class="form-label">Notes (Optional)</label>
                        <textarea id="pharmacist_notes" class="form-control" rows="3" 
                                  placeholder="Add any notes about this status change..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-doctor-primary">Update Status</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<!-- Approve Prescription Modal -->
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
                        <li>Patient: <strong><span id="approve-patient-name"></span></strong></li>
                        <li>Prescription ID: <strong><span id="approve-prescription-id"></span></strong></li>
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
                <button type="button" class="btn btn-success" id="confirmApprove" style="position: relative; z-index: 1051;">
                    <i class="fas fa-check me-1"></i>Yes, Approve Prescription
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Dispense Prescription Modal -->
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
                        <li>Patient: <strong><span id="dispense-patient-name"></span></strong></li>
                        <li>Doctor: <strong><span id="dispense-doctor-name"></span></strong></li>
                        <li>Prescription ID: <strong><span id="dispense-prescription-id"></span></strong></li>
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
                <button type="button" class="btn btn-success" id="confirmDispense" style="position: relative; z-index: 1051;">
                    <i class="fas fa-check me-1"></i>Yes, Mark as Dispensed
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Cancel Prescription Modal -->
<div class="modal fade" id="cancelPrescriptionModal" tabindex="-1" aria-labelledby="cancelPrescriptionModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="cancelPrescriptionModalLabel">
                    <i class="fas fa-times-circle me-2"></i>Cancel Prescription
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <div class="mb-3">
                        <i class="fas fa-ban text-danger" style="font-size: 4rem;"></i>
                    </div>
                    <h4 class="text-dark mb-3">Confirm Prescription Cancellation</h4>
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Warning:</strong> This action cannot be undone. The prescription will be permanently cancelled and cannot be dispensed.
                </div>
                
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Prescription Details:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Patient: <strong><span id="cancel-patient-name"></span></strong></li>
                        <li>Prescription ID: <strong><span id="cancel-prescription-id"></span></strong></li>
                    </ul>
                </div>
                
                <p class="text-muted mb-0">
                    <i class="fas fa-question-circle text-warning me-2"></i>
                    Are you sure you want to cancel this prescription? This action cannot be reversed.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-arrow-left me-1"></i>Keep Prescription
                </button>
                <button type="button" class="btn btn-danger" id="confirmCancel">
                    <i class="fas fa-times me-1"></i>Yes, Cancel Prescription
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Initialize DataTable
    $('#prescriptionsTable').DataTable({
        "paging": false,
        "info": false,
        "searching": false,
        "ordering": true,
        "order": [[ 4, "desc" ]],
        "columnDefs": [
            { "orderable": false, "targets": [6] }
        ]
    });
    
    // Modal event handlers for prescription actions
    let currentPrescriptionId = null;
    
    // Approve prescription modal
    $('#approvePrescriptionModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        currentPrescriptionId = button.data('prescription-id');
        const patientName = button.data('patient-name');
        
        // Update modal content
        $('#approve-patient-name').text(patientName);
        $('#approve-prescription-id').text(currentPrescriptionId);
    });
    
    // Dispense prescription modal
    $('#dispensePrescriptionModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        currentPrescriptionId = button.data('prescription-id');
        const patientName = button.data('patient-name');
        const doctorName = button.data('doctor-name');
        
        // Update modal content
        $('#dispense-patient-name').text(patientName);
        $('#dispense-doctor-name').text(doctorName);
        $('#dispense-prescription-id').text(currentPrescriptionId);
    });
    
    // Cancel prescription modal
    $('#cancelPrescriptionModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        currentPrescriptionId = button.data('prescription-id');
        const patientName = button.data('patient-name');
        
        // Update modal content
        $('#cancel-patient-name').text(patientName);
        $('#cancel-prescription-id').text(currentPrescriptionId);
    });
    
    // Confirm approve prescription
    $('#confirmApprove').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (currentPrescriptionId) {
            updatePrescriptionStatus(currentPrescriptionId, 'approved');
        }
        return false;
    });
    
    // Confirm dispense prescription
    $('#confirmDispense').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();
        if (currentPrescriptionId) {
            updatePrescriptionStatus(currentPrescriptionId, 'dispensed');
        }
        return false;
    });
    
    // Confirm cancel prescription
    $('#confirmCancel').on('click', function() {
        if (currentPrescriptionId) {
            updatePrescriptionStatus(currentPrescriptionId, 'cancelled');
        }
    });
    
    // Modal animations
    $('.modal').on('show.bs.modal', function() {
        $(this).find('.modal-content').addClass('animate__animated animate__fadeInDown animate__faster');
    });
    
    $('.modal').on('hide.bs.modal', function() {
        $(this).find('.modal-content').removeClass('animate__animated animate__fadeInDown animate__faster');
    });
});

// Print prescription
function printPrescription(prescriptionId) {
    window.open(`/staff/prescriptions/${prescriptionId}/print`, '_blank');
}

// Update prescription status via AJAX
function updatePrescriptionStatus(prescriptionId, status) {
    // Show loading state
    const loadingText = {
        'approved': 'Approving...',
        'dispensed': 'Marking as Dispensed...',
        'cancelled': 'Cancelling...'
    };
    
    const originalButtonText = {
        'approved': $('#confirmApprove').html(),
        'dispensed': $('#confirmDispense').html(),
        'cancelled': $('#confirmCancel').html()
    };
    
    const buttonSelector = {
        'approved': '#confirmApprove',
        'dispensed': '#confirmDispense', 
        'cancelled': '#confirmCancel'
    };
    
    $(buttonSelector[status]).prop('disabled', true).html(
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
                    'dispensed': 'Prescription marked as dispensed successfully!',
                    'cancelled': 'Prescription cancelled successfully!'
                };
                
                // Hide modal first
                $('.modal').modal('hide');
                
                // Show success toast or alert
                showSuccessMessage(successMessages[status]);
                
                // Reload page after short delay
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showErrorMessage('Error updating status: ' + (response.message || 'Unknown error'));
                // Reset button
                $(buttonSelector[status]).prop('disabled', false).html(originalButtonText[status]);
            }
        },
        error: function(xhr) {
            let errorMessage = 'Error updating prescription status. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showErrorMessage(errorMessage);
            // Reset button
            $(buttonSelector[status]).prop('disabled', false).html(originalButtonText[status]);
        }
    });
}

// Success message function
function showSuccessMessage(message) {
    // Create a Bootstrap alert
    const alertHtml = `
        <div class="alert alert-success alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;"
             role="alert">
            <i class="fas fa-check-circle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('body').append(alertHtml);
    
    // Auto-dismiss after 30 seconds
    setTimeout(() => {
        $('.alert-success').fadeOut(() => {
            $('.alert-success').remove();
        });
    }, 30000);
}

// Error message function
function showErrorMessage(message) {
    // Create a Bootstrap alert
    const alertHtml = `
        <div class="alert alert-danger alert-dismissible fade show position-fixed" 
             style="top: 20px; right: 20px; z-index: 9999; min-width: 300px;"
             role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    $('body').append(alertHtml);
    
    // Auto-dismiss after 30 seconds
    setTimeout(() => {
        $('.alert-danger').fadeOut(() => {
            $('.alert-danger').remove();
        });
    }, 30000);
}

// Legacy function for pharmacist status updates (keeping for compatibility)
let currentPrescriptionId = null;

function updateStatus(prescriptionId, status = null) {
    currentPrescriptionId = prescriptionId;
    
    // For pharmacist modal-based updates only
    @if(auth()->user()->role === 'pharmacist')
    if (status) {
        $('#status_select').val(status);
    }
    $('#statusModal').modal('show');
    @endif
}

@if(auth()->user()->role === 'pharmacist')
$('#statusForm').on('submit', function(e) {
    e.preventDefault();
    
    const status = $('#status_select').val();
    const notes = $('#pharmacist_notes').val();
    
    $.ajax({
        url: `/staff/prescriptions/${currentPrescriptionId}/status`,
        method: 'PATCH',
        data: {
            status: status,
            notes: notes,
            _token: '{{ csrf_token() }}'
        },
        success: function(response) {
            if (response.success) {
                $('#statusModal').modal('hide');
                showSuccessMessage('Prescription status updated successfully!');
                setTimeout(() => {
                    location.reload();
                }, 1500);
            } else {
                showErrorMessage('Error updating status: ' + (response.message || 'Unknown error'));
            }
        },
        error: function(xhr) {
            let errorMessage = 'Error updating prescription status. Please try again.';
            if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            showErrorMessage(errorMessage);
        }
    });
});
@endif
</script>
@endpush
