@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Bill Details')
@section('page-title', 'Bill Details')
@section('page-subtitle', 'View complete bill information')

@section('content')
<div class="fade-in-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">Bill Details</h1>
                    <p class="text-muted mb-0">
                        Detailed view of the bill for {{ $billing->patient->full_name }}
                    </p>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.billing.index') }}">Billing</a></li>
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
            <!-- Billing Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-doctor-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="doctor-doctor-card-title mb-0"><i class="fas fa-file-invoice-dollar me-2"></i>Billing Information</h5>
                        <div class="d-flex gap-2">
                            @php
                                $statusColors = [
                                    'paid' => 'success',
                                    'pending' => 'warning',
                                    'overdue' => 'danger',
                                    'cancelled' => 'secondary'
                                ];
                                $statusColor = $statusColors[$billing->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }} fs-6">{{ ucfirst($billing->status) }}</span>
                        </div>
                    </div>
                </div>
                <div class="doctor-card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-hashtag me-1"></i>Bill Number</label>
                            <div class="fw-bold text-primary">{{ $billing->bill_number }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-pound-sign me-1"></i>Total Amount</label>
                            <div class="fw-bold">{{ CurrencyHelper::format($billing->total_amount) }}</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-calendar me-1"></i>Billing Date</label>
                            <div class="fw-bold">{{ $billing->billing_date->format('F j, Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-calendar-alt me-1"></i>Due Date</label>
                            <div class="fw-bold">{{ $billing->due_date ? $billing->due_date->format('F j, Y') : 'N/A' }}</div>
                        </div>
                    </div>

                    @if($billing->notes)
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="form-label text-muted"><i class="fas fa-sticky-note me-1"></i>Notes</label>
                                <div class="fw-bold">{{ $billing->notes }}</div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Patient Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-doctor-card-header">
                    <h5 class="doctor-doctor-card-title mb-0"><i class="fas fa-user me-2"></i>Patient Information</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-id-card me-1"></i>Full Name</label>
                            <div class="fw-bold">{{ $billing->patient->full_name }}</div>
                        </div>
                        @if($billing->patient->patient_id)
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-barcode me-1"></i>Patient ID</label>
                                <div class="fw-bold">{{ $billing->patient->patient_id }}</div>
                            </div>
                        @endif
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-envelope me-1"></i>Email</label>
                            <div class="fw-bold">
                                @if($billing->patient->email)
                                    <a href="mailto:{{ $billing->patient->email }}" class="text-decoration-none">
                                        {{ $billing->patient->email }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-phone me-1"></i>Phone</label>
                            <div class="fw-bold">
                                @if($billing->patient->phone)
                                    <a href="tel:{{ $billing->patient->phone }}" class="text-decoration-none">
                                        {{ $billing->patient->phone }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doctor Information -->
            @if($billing->doctor)
                <div class="doctor-card mb-4">
                    <div class="doctor-doctor-card-header">
                        <h5 class="doctor-doctor-card-title mb-0"><i class="fas fa-user-md me-2"></i>Doctor Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-user-tie me-1"></i>Doctor Name</label>
                                <div class="fw-bold">Dr. {{ $billing->doctor->name ?? $billing->doctor->full_name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-stethoscope me-1"></i>Specialisation</label>
                                <div class="fw-bold">{{ $billing->doctor->specialization ?? 'GP' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-cogs me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="doctor-card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('staff.billing.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to Billing
                        </a>

                        @if($billing->patient && $billing->patient->email)
                        <button type="button" class="btn btn-success" onclick="sendToPatient({{ $billing->id }})" id="sendToPatientBtn">
                            <i class="fas fa-envelope me-1"></i>Send to Patient
                        </button>
                        @endif

                        {{-- Staff cannot edit or update billing status - these actions are restricted to admin --}}
                        {{-- @if($billing->status === 'pending')
                            <a href="{{ route('staff.billing.edit', $billing->id) }}" class="btn btn-outline-warning">
                                <i class="fas fa-edit me-1"></i>Edit Bill
                            </a>
                            <button class="btn btn-success" onclick="updateStatus({{ $billing->id }}, 'paid')">
                                <i class="fas fa-check me-1"></i>Mark as Paid
                            </button>
                        @endif

                        @if(in_array($billing->status, ['pending', 'overdue']))
                            <button class="btn btn-outline-danger" onclick="updateStatus({{ $billing->id }}, 'cancelled')">
                                <i class="fas fa-times me-1"></i>Cancel Bill
                            </button>
                        @endif --}}

                        <div class="dropdown-divider"></div>
                        <button class="btn btn-outline-info" onclick="window.print()">
                            <i class="fas fa-print me-1"></i>Print Bill
                        </button>
                    </div>
                </div>
            </div>

            <!-- Status Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Status Information
                    </h6>
                </div>
                <div class="doctor-card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Current Status</label>
                        <div>
                            <span class="badge bg-{{ $statusColor }} fs-6">{{ ucfirst($billing->status) }}</span>
                        </div>
                    </div>

                    @if($billing->paid_at)
                        <div class="mb-3">
                            <label class="form-label text-muted">Paid At</label>
                            <div class="fw-bold">{{ $billing->paid_at->format('M j, Y h:i A') }}</div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label text-muted">Created</label>
                        <div class="fw-bold">{{ $billing->created_at->format('M j, Y h:i A') }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Last Updated</label>
                        <div class="fw-bold">{{ $billing->updated_at->format('M j, Y h:i A') }}</div>
                    </div>
                </div>
            </div>

            <!-- Guidelines -->
            <div class="card border-info">
                <div class="doctor-card-body">
                    <div class="d-flex">
                        <div class="me-3">
                            <i class="fas fa-lightbulb text-info fa-2x"></i>
                        </div>
                        <div>
                            <h6 class="text-info">Billing Guidelines</h6>
                            <ul class="mb-0 text-muted small">
                                <li class="mb-1">Review bill details carefully before processing payment</li>
                                <li class="mb-1">Contact patients for overdue payments</li>
                                <li class="mb-1">Verify insurance coverage if applicable</li>
                                <li>Keep records of all payment transactions</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Status Update Modal - Removed for staff as they cannot update billing status --}}
{{-- Only admins can edit and update billing status --}}
@endsection

@push('scripts')
<script>
function sendToPatient(billId) {
    if (!confirm('Send billing notification email to patient with payment link?')) {
        return;
    }
    
    const btn = document.getElementById('sendToPatientBtn');
    const originalHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Sending...';
    
    fetch(`{{ url('/staff/billing') }}/${billId}/send-to-patient`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✓ Billing notification sent to patient successfully!');
        } else {
            alert('✗ Failed to send notification: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('✗ An error occurred while sending the notification.');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = originalHtml;
    });
}

// Status update functions removed - staff cannot update billing status
// Only admins have permission to edit and update billing status

// Auto-dismiss alerts after 5 seconds
setTimeout(function() {
    $('.alert').fadeOut();
}, 30000);
</script>
@endpush

