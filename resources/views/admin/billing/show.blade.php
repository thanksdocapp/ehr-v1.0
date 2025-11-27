@extends('admin.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Billing Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('billing.index') }}">Billing</a></li>
    <li class="breadcrumb-item active">Bill #{{ $billing->bill_number }}</li>
@endsection

@push('styles')
<style>
.record-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
    overflow: hidden;
}

.record-section-header {
    background: #f8f9fc;
    color: #2d3748;
    padding: 1.5rem 2rem;
    border-bottom: 2px solid #e2e8f0;
}

.record-section-header h4,
.record-section-header h5 {
    color: #1a202c;
    font-weight: 700;
}

.record-section-header i {
    color: #1a202c;
}

.record-section-header small {
    color: #4a5568;
}

.record-section-body {
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

.quick-info-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.quick-info-card h6 {
    color: #5a5c69;
    margin-bottom: 1rem;
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
        <h1><i class="fas fa-file-invoice-dollar me-2 text-primary"></i>Billing Details</h1>
        <p class="page-subtitle text-muted">Comprehensive billing record information</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Patient & Doctor Information -->
            <div class="record-section">
                <div class="record-section-header">
                    <h4 class="mb-0"><i class="fas fa-user-md me-2"></i>Patient & Doctor Information</h4>
                    <small class="opacity-75">Basic billing and personnel details</small>
                </div>
                <div class="record-section-body">
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-hashtag me-1"></i>Bill Number:</div>
                        <div class="info-value">#{{ $billing->bill_number }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-user me-1"></i>Patient:</div>
                        <div class="info-value">
                            <strong>{{ $billing->patient->full_name }}</strong>
                            <small class="text-muted">({{ $billing->patient->patient_id }})</small>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-stethoscope me-1"></i>Doctor:</div>
                        <div class="info-value">
                            <strong>{{ $billing->doctor ? 'Dr. ' . $billing->doctor->full_name : 'N/A' }}</strong>
                            @if($billing->doctor && $billing->doctor->specialization)
                                <small class="text-muted">({{ $billing->doctor->specialization }})</small>
                            @endif
                        </div>
                    </div>
                    @if($billing->appointment)
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-calendar-check me-1"></i>Appointment:</div>
                        <div class="info-value">
                            #{{ $billing->appointment->id }} - 
                            {{ formatDate($billing->appointment->appointment_date) }}
                        </div>
                    </div>
                    @endif
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-file-alt me-1"></i>Bill Type:</div>
                        <div class="info-value">{{ ucfirst(str_replace('_', ' ', $billing->type)) }}</div>
                    </div>
                </div>
            </div>

            <!-- Financial Information -->
            <div class="record-section">
                <div class="record-section-header">
                    <h4 class="mb-0"><i class="fas fa-pound-sign me-2"></i>Financial Information</h4>
                    <small class="opacity-75">Payment details and amount breakdown</small>
                </div>
                <div class="record-section-body">
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-receipt me-1"></i>Subtotal:</div>
                        <div class="info-value">{{ CurrencyHelper::format($billing->subtotal ?? $billing->total_amount) }}</div>
                    </div>
                    @if($billing->discount > 0)
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-percentage me-1"></i>Discount:</div>
                        <div class="info-value">{{ CurrencyHelper::format($billing->discount) }}</div>
                    </div>
                    @endif
                    @if($billing->tax > 0)
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-calculator me-1"></i>Tax:</div>
                        <div class="info-value">{{ CurrencyHelper::format($billing->tax) }}</div>
                    </div>
                    @endif
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-money-bill-wave me-1"></i>Total Amount:</div>
                        <div class="info-value"><strong>{{ CurrencyHelper::format($billing->total_amount) }}</strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-credit-card me-1"></i>Paid Amount:</div>
                        <div class="info-value">{{ CurrencyHelper::format($billing->paid_amount) }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-balance-scale me-1"></i>Balance:</div>
                        <div class="info-value"><strong>{{ CurrencyHelper::format($billing->balance) }}</strong></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-info-circle me-1"></i>Status:</div>
                        <div class="info-value">
                            @php
                                $statusColors = [
                                    'pending' => 'warning',
                                    'paid' => 'success',
                                    'partially_paid' => 'info',
                                    'overdue' => 'danger',
                                    'cancelled' => 'secondary'
                                ];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$billing->status] ?? 'secondary' }}">
                                <i class="fas fa-{{ $billing->status == 'paid' ? 'check' : ($billing->status == 'pending' ? 'clock' : 'exclamation') }} me-1"></i>
                                {{ ucfirst(str_replace('_', ' ', $billing->status)) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="record-section">
                <div class="record-section-header">
                    <h4 class="mb-0"><i class="fas fa-clipboard me-2"></i>Additional Information</h4>
                    <small class="opacity-75">Dates, descriptions, and notes</small>
                </div>
                <div class="record-section-body">
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-calendar-day me-1"></i>Billing Date:</div>
                        <div class="info-value">{{ formatDate($billing->billing_date) }}</div>
                    </div>
                    @if($billing->due_date)
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-calendar-times me-1"></i>Due Date:</div>
                        <div class="info-value">
                            {{ formatDate($billing->due_date) }}
                            @if($billing->due_date->isPast() && $billing->status !== 'paid')
                                <small class="text-danger">({{ $billing->due_date->diffForHumans() }})</small>
                            @elseif($billing->due_date->isFuture())
                                <small class="text-success">({{ $billing->due_date->diffForHumans() }})</small>
                            @endif
                        </div>
                    </div>
                    @endif
                    @if($billing->description)
                    <div class="mb-4">
                        <h6><i class="fas fa-file-text me-1"></i>Description</h6>
                        <div class="bg-light p-3 rounded">{{ $billing->description }}</div>
                    </div>
                    @endif
                    @if($billing->notes)
                    <div class="mb-4">
                        <h6><i class="fas fa-sticky-note me-1"></i>Notes</h6>
                        <div class="bg-light p-3 rounded">{{ $billing->notes }}</div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="quick-info-card">
                <h6>Quick Actions</h6>
                <div class="action-buttons">
                    <a href="{{ contextRoute('billing.edit', $billing) }}" class="btn btn-doctor-primary">
                        <i class="fas fa-edit"></i> Edit Bill
                    </a>
                    @if($billing->patient && $billing->patient->email)
                    <button type="button" class="btn btn-success" onclick="sendToPatient({{ $billing->id }})" id="sendToPatientBtn">
                        <i class="fas fa-envelope"></i> Send to Patient
                    </button>
                    @endif
                    <button type="button" class="btn btn-secondary" onclick="printBill()">
                        <i class="fas fa-print"></i> Print Bill
                    </button>
                    <button type="button" class="btn btn-danger" onclick="deleteBill({{ $billing->id }})">
                        <i class="fas fa-trash"></i> Delete Bill
                    </button>
                </div>
            </div>

            <div class="quick-info-card">
                <h6>Billing Statistics</h6>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border-end">
                            <h4 class="text-primary">{{ CurrencyHelper::getCurrencySymbol() }}{{ number_format($billing->total_amount, 0) }}</h4>
                            <small class="text-muted">Total Amount</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ CurrencyHelper::getCurrencySymbol() }}{{ number_format($billing->paid_amount, 0) }}</h4>
                        <small class="text-muted">Paid</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-12">
                        <h4 class="text-{{ $billing->balance > 0 ? 'danger' : 'success' }}">{{ CurrencyHelper::getCurrencySymbol() }}{{ number_format($billing->balance, 0) }}</h4>
                        <small class="text-muted">Outstanding Balance</small>
                    </div>
                </div>
            </div>

            <div class="quick-info-card">
                <h6>Recent Activity</h6>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-success"></div>
                        <div class="timeline-content">
                            <small class="text-muted">{{ $billing->created_at->diffForHumans() }}</small>
                            <p class="mb-0">Bill created</p>
                        </div>
                    </div>
                    
                    @if($billing->updated_at != $billing->created_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-info"></div>
                            <div class="timeline-content">
                                <small class="text-muted">{{ $billing->updated_at->diffForHumans() }}</small>
                                <p class="mb-0">Bill updated</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';
    
    fetch(`{{ url('/admin/billing') }}/${billId}/send-to-patient`, {
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

function printBill() {
    window.print();
}

function deleteBill(billId) {
    console.log('Delete bill called with ID:', billId);
    
    // Prevent any default behavior if event exists
    if (window.event) {
        window.event.preventDefault();
        window.event.stopPropagation();
    }
    
    // Handle both sync and async confirm dialogs
    function handleConfirmation(confirmResult) {
        console.log('User confirmation result:', confirmResult);
        
        if (confirmResult === true) {
            console.log('User confirmed deletion, proceeding...');
            
            // Add a small delay to ensure the dialog is properly closed
            setTimeout(() => {
                console.log('Creating form for deletion...');
                
                // Create a form to submit the DELETE request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/billing/${billId}`;
                form.style.display = 'none';
                
                // Add CSRF token - try multiple methods
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                
                // Try to get CSRF token from meta tag or Laravel's global
                let csrfTokenValue = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfTokenValue && typeof Laravel !== 'undefined') {
                    csrfTokenValue = Laravel.csrfToken;
                }
                if (!csrfTokenValue && typeof window.Laravel !== 'undefined') {
                    csrfTokenValue = window.Laravel.csrfToken;
                }
                
                csrfToken.value = csrfTokenValue;
                form.appendChild(csrfToken);
                
                console.log('CSRF token:', csrfTokenValue);
                
                // Add DELETE method
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);
                console.log('Form children:', form.children);
                
                // Add form to document and submit
                document.body.appendChild(form);
                console.log('Form added to document, submitting...');
                form.submit();
            }, 100);
        } else {
            console.log('User cancelled deletion');
        }
    }
    
    // Use a more explicit confirmation dialog
    const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this billing record?\n\nThis action cannot be undone and will remove:\n- All billing information\n- Payment history\n- Invoice details\n- Associated financial data\n\nClick OK to confirm deletion or Cancel to abort.');
    
    // Handle both Promise and boolean returns
    if (confirmDelete && typeof confirmDelete.then === 'function') {
        // If it's a Promise, wait for it to resolve
        confirmDelete.then(handleConfirmation).catch(() => handleConfirmation(false));
    } else {
        // If it's a boolean, handle it directly
        handleConfirmation(confirmDelete);
    }
    
    return false;
}
</script>
@endpush
