@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Bill Details')
@section('page-title', 'Bill Details')
@section('page-subtitle', 'View complete bill information')

@push('styles')
@include('admin.shared.modern-ui')
@endpush

@section('content')
<div class="fade-in-up">
    <!-- Page Header -->
    <div class="modern-page-header">
        <div class="modern-page-header-content">
            <h1 class="modern-page-title">
                <i class="fas fa-file-invoice-dollar me-2"></i>Bill Details
            </h1>
            <p class="modern-page-subtitle">
                Detailed view of the bill for {{ $billing->patient->full_name }}
            </p>
        </div>
    </div>

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Billing Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Billing information</h5>
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
                    <div class="mb-4">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-file-invoice me-1"></i>Bill & amounts</p>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Bill number</label>
                                <div class="fw-bold text-primary">{{ $billing->bill_number }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Total amount</label>
                                <div class="fw-bold">{{ CurrencyHelper::format($billing->total_amount) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="mb-4 pt-3 border-top">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-calendar me-1"></i>Dates</p>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Billing date</label>
                                <div class="fw-bold">{{ formatDateUkLong($billing->billing_date) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Due date</label>
                                <div class="fw-bold">{{ $billing->due_date ? formatDateUkLong($billing->due_date) : 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                    @if($billing->notes)
                    <div class="pt-3 border-top">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-sticky-note me-1"></i>Notes</p>
                        <label class="form-label text-muted small mb-0">Notes</label>
                        <div class="fw-bold">{{ $billing->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Patient Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-user me-2 text-primary"></i>Patient information</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="mb-4">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-id-card me-1"></i>Identity</p>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Full name</label>
                                <div class="fw-bold">{{ $billing->patient->full_name }}</div>
                            </div>
                            @if($billing->patient->patient_id)
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Patient ID</label>
                                <div class="fw-bold">{{ $billing->patient->patient_id }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    <div class="pt-3 border-top">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-address-book me-1"></i>Contact</p>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Email</label>
                                <div class="fw-bold">
                                    @if($billing->patient->email)
                                        <a href="mailto:{{ $billing->patient->email }}" class="text-decoration-none">{{ $billing->patient->email }}</a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Phone</label>
                                <div class="fw-bold">
                                    @if($billing->patient->phone)
                                        <a href="tel:{{ $billing->patient->phone }}" class="text-decoration-none">{{ $billing->patient->phone }}</a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doctor Information -->
            @if($billing->doctor)
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-user-md me-2 text-primary"></i>Doctor information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-user-tie me-1"></i>Clinician</p>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Name</label>
                                <div class="fw-bold">{{ formatDoctorName($billing->doctor->name ?? $billing->doctor->full_name) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small mb-0">Specialisation</label>
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
                    <h6 class="doctor-card-title mb-0">
                        <i class="fas fa-cogs me-2 text-primary"></i>Quick actions
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

                        @if(in_array($billing->status, ['pending', 'partially_paid', 'overdue']))
                            <a href="{{ route('staff.billing.edit', $billing->id) }}" class="btn btn-outline-warning">
                                <i class="fas fa-edit me-1"></i>Edit Bill
                            </a>
                        @endif

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
                    <h6 class="doctor-card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Status information
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
                            <div class="fw-bold">{{ formatDateTimeUkAmPm($billing->paid_at) }}</div>
                        </div>
                    @endif

                    <div class="mb-3">
                        <label class="form-label text-muted">Created</label>
                        <div class="fw-bold">{{ formatDateTimeUkAmPm($billing->created_at) }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label text-muted">Last Updated</label>
                        <div class="fw-bold">{{ formatDateTimeUkAmPm($billing->updated_at) }}</div>
                    </div>
                </div>
            </div>

            <!-- Guidelines -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h6 class="doctor-card-title mb-0">
                        <i class="fas fa-lightbulb me-2 text-primary"></i>Billing guidelines
                    </h6>
                </div>
                <div class="doctor-card-body">
                    <ul class="mb-0" style="color: #4a5568; font-size: 0.875rem;">
                        <li class="mb-2"><strong style="color: #1a202c;">Review:</strong> Check bill details carefully before processing payment</li>
                        <li class="mb-2"><strong style="color: #1a202c;">Contact:</strong> Reach out to patients for overdue payments</li>
                        <li class="mb-2"><strong style="color: #1a202c;">Verify:</strong> Confirm insurance coverage if applicable</li>
                        <li><strong style="color: #1a202c;">Records:</strong> Keep records of all payment transactions</li>
                    </ul>
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

