@extends('admin.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Billing Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Billing Management</li>
@endsection

@push('styles')
<style>
.stats-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 1.5rem;
    color: white;
    margin-bottom: 1.5rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.stats-card:hover {
    transform: translateY(-5px);
}

.stats-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stats-label {
    font-size: 0.9rem;
    opacity: 0.9;
}

.table-actions .btn {
    margin: 0 2px;
    padding: 0.375rem 0.75rem;
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
}

.bill-type-badge {
    font-size: 0.7rem;
    padding: 0.2rem 0.6rem;
    border-radius: 12px;
}

.filter-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 10px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

/* Fix modal backdrop and z-index issues */
#syncDetailsModal {
    z-index: 1060 !important;
}

#syncDetailsModal .modal-backdrop {
    z-index: 1055 !important;
    pointer-events: none !important; /* Don't block clicks - allow clicks through backdrop */
}

#syncDetailsModal.modal {
    z-index: 1065 !important;
}

#syncDetailsModal .modal-dialog {
    z-index: 1070 !important;
    position: relative;
}

/* Ensure modal content is clickable */
#syncDetailsModal .modal-content {
    position: relative;
    z-index: 1075 !important;
    pointer-events: auto;
}

/* Fix any potential overlay issues */
.modal-open .modal {
    pointer-events: none;
}

.modal-open .modal-dialog {
    pointer-events: auto;
}

</style>
@endpush

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="modern-page-title">Billing Management</h1>
                    <p class="modern-page-subtitle">Manage and track all billing records and payments</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ contextRoute('billing.create') }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-plus me-2"></i>New Invoice
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-primary" style="font-size: 1.75rem; font-weight: 600;">{{ $bills->total() ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Total Bills</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark)); width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-file-invoice-dollar text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-success" style="font-size: 1.75rem; font-weight: 600;">{{ $bills->where('status', 'paid')->count() ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Paid Bills</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--success), #16a34a); width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check-circle text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-warning" style="font-size: 1.75rem; font-weight: 600;">{{ $bills->where('status', 'pending')->count() ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Pending Bills</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--warning), #d97706); width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-clock text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-danger" style="font-size: 1.75rem; font-weight: 600;">{{ $bills->where('status', 'overdue')->count() ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Overdue Bills</div>
                    </div>
                    <div class="stat-icon" style="background: linear-gradient(135deg, var(--danger), #dc2626); width: 48px; height: 48px; font-size: 1.25rem; border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-exclamation-triangle text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="doctor-card-body">
            <form method="GET" action="{{ contextRoute('billing.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Patient name, bill ID..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-control">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="partially_paid" {{ request('status') == 'partially_paid' ? 'selected' : '' }}>Partially Paid</option>
                        <option value="overdue" {{ request('status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select name="type" class="form-control">
                        <option value="">All Types</option>
                        <option value="consultation" {{ request('type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                        <option value="procedure" {{ request('type') == 'procedure' ? 'selected' : '' }}>Procedure</option>
                        <option value="medication" {{ request('type') == 'medication' ? 'selected' : '' }}>Medication</option>
                        <option value="lab_test" {{ request('type') == 'lab_test' ? 'selected' : '' }}>Lab Test</option>
                        <option value="other" {{ request('type') == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="text" name="date_from" id="date_from" class="form-control" 
                           value="{{ request('date_from') ? formatDate(request('date_from')) : '' }}"
                           placeholder="dd-mm-yyyy" 
                           pattern="\d{2}-\d{2}-\d{4}" 
                           maxlength="10">
                    <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="text" name="date_to" id="date_to" class="form-control" 
                           value="{{ request('date_to') ? formatDate(request('date_to')) : '' }}"
                           placeholder="dd-mm-yyyy" 
                           pattern="\d{2}-\d{2}-\d{4}" 
                           maxlength="10">
                    <small class="form-text text-muted" style="font-size: 0.75rem;">Format: dd-mm-yyyy</small>
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Billing Table -->
    <div class="doctor-card">
        <div class="doctor-card-header d-flex justify-content-between align-items-center">
            <h5 class="doctor-card-title mb-0">Bills List</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="exportBills()">
                    <i class="fas fa-download me-1"></i>Export
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="refreshTable()">
                    <i class="fas fa-sync me-1"></i>Refresh
                </button>
            </div>
        </div>
        <div class="doctor-card-body p-0">
            @if($bills->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Bill ID</th>
                                <th>Patient</th>
                                <th>Doctor</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bills as $bill)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input bill-checkbox" 
                                           value="{{ $bill->id }}">
                                </td>
                                <td>
                                    <div class="fw-bold text-primary">#{{ $bill->bill_number }}</div>
                                    <small class="text-muted">{{ formatDate($bill->created_at) }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($bill->patient)
                                            <div class="avatar-placeholder bg-info text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                {{ strtoupper(substr($bill->patient->first_name, 0, 1)) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold">{{ $bill->patient->full_name }}</div>
                                                <small class="text-muted">{{ $bill->patient->patient_id }}</small>
                                            </div>
                                        @else
                                            <div class="avatar-placeholder bg-danger text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                <i class="fas fa-exclamation-triangle"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-danger">Patient Deleted</div>
                                                <small class="text-muted">Patient record no longer exists</small>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    @if($bill->doctor)
                                        <div class="fw-bold">{{ $bill->doctor->full_name }}</div>
                                        <small class="text-muted">{{ $bill->doctor->specialization ?? 'General' }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary text-white">{{ ucfirst(str_replace('_', ' ', $bill->type)) }}</span>
                                    @if($bill->appointment_id)
                                    <br><small class="text-muted">Appointment: {{ $bill->appointment->appointment_number ?? 'N/A' }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-bold">{{ CurrencyHelper::format($bill->total_amount) }}</div>
                                    @if($bill->paid_amount > 0)
                                        <small class="text-success">Paid: {{ CurrencyHelper::format($bill->paid_amount) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'warning',
                                            'paid' => 'success',
                                            'partially_paid' => 'info',
                                            'overdue' => 'danger',
                                            'cancelled' => 'secondary'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$bill->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $bill->status)) }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ formatDate($bill->billing_date) }}</div>
                                    @if($bill->due_date)
                                        <small class="text-muted">Due: {{ formatDate($bill->due_date) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ contextRoute('billing.show', $bill) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ contextRoute('billing.edit', $bill) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Edit Bill">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @if($bill->status !== 'paid')
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    title="Process Payment" onclick="processPayment({{ $bill->id }})">
                                                <i class="fas fa-credit-card"></i>
                                            </button>
                                        @endif
                                        @if($bill->invoice)
                                            <button type="button" class="btn btn-sm btn-outline-info" 
                                                    title="View Sync Details" onclick="showSyncDetails({{ $bill->id }})">
                                                <i class="fas fa-sync"></i>
                                            </button>
                                        @else
                                            <button type="button" class="btn btn-sm btn-outline-secondary" 
                                                    title="Sync with Patient Portal" onclick="manualSync({{ $bill->id }})">
                                                <i class="fas fa-sync-alt"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                title="Delete Bill" onclick="deleteBill({{ $bill->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="doctor-card-footer">
                    {{ $bills->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No bills found</h5>
                    <p class="text-muted">No billing records match your current filters.</p>
                    <a href="{{ contextRoute('billing.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-2"></i>Create First Bill
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ISOLATED SYNC MODAL - COMPLETELY BYPASSES LAYOUT CONFLICTS -->
<div id="isolatedSyncModal" style="display: none;">
    <!-- Full screen overlay -->
    <div id="isolatedModalOverlay" style="
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 99999999;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    ">
        <!-- Modal container -->
        <div id="isolatedModalContainer" style="
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 50px rgba(0, 0, 0, 0.3);
            max-width: 800px;
            width: 90%;
            max-height: 80vh;
            overflow: auto;
            cursor: default;
            animation: modalSlideIn 0.3s ease-out;
        " onclick="event.stopPropagation();">
            
            <!-- Modal Header -->
            <div style="
                background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
                color: white;
                padding: 20px 25px;
                border-radius: 15px 15px 0 0;
                display: flex;
                justify-content: space-between;
                align-items: center;
            ">
                <h5 style="margin: 0; font-weight: 600;">
                    <i class="fas fa-sync me-2"></i>Patient Portal Sync Details
                </h5>
                <button onclick="closeIsolatedModal()" style="
                    background: none;
                    border: none;
                    color: white;
                    font-size: 24px;
                    cursor: pointer;
                    padding: 0;
                    width: 30px;
                    height: 30px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                    transition: background 0.3s ease;
                " onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='none'">
                    &times;
                </button>
            </div>
            
            <!-- Modal Body -->
            <div id="isolatedModalBody" style="
                padding: 25px;
                min-height: 200px;
            ">
                <div class="text-center py-3">
                    <i class="fas fa-spinner fa-spin"></i> Loading sync details...
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div style="
                background: #f8f9fa;
                padding: 20px 25px;
                border-radius: 0 0 15px 15px;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
                border-top: 1px solid #dee2e6;
            ">
                <button onclick="closeIsolatedModal()" style="
                    background: #6c757d;
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-weight: 500;
                    transition: background 0.3s ease;
                " onmouseover="this.style.background='#5a6268'" onmouseout="this.style.background='#6c757d'">
                    Close
                </button>
                <button onclick="isolatedForceResync()" style="
                    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
                    color: white;
                    border: none;
                    padding: 10px 20px;
                    border-radius: 8px;
                    cursor: pointer;
                    font-weight: 500;
                    transition: transform 0.2s ease;
                " onmouseover="this.style.transform='translateY(-1px)'" onmouseout="this.style.transform='translateY(0)'">
                    <i class="fas fa-sync-alt me-1"></i>Force Re-sync
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes modalSlideIn {
    from {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

@keyframes modalSlideOut {
    from {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
    to {
        opacity: 0;
        transform: translateY(-50px) scale(0.9);
    }
}

#isolatedSyncModal.closing #isolatedModalContainer {
    animation: modalSlideOut 0.2s ease-in;
}
</style>

@push('scripts')
<script>
// Date input handling is done globally in admin layout, but we keep this for form-specific functionality
$(document).ready(function() {
    // Additional date validation or formatting specific to billing if needed
});

function exportBills() {
    // Implementation for exporting bills
    console.log('Export bills functionality');
}

function refreshTable() {
    window.location.reload();
}

function processPayment(billId) {
    console.log('Process payment called with billId:', billId);
    
    // Redirect to dedicated payment processing page
    window.location.href = '{{ url('admin/billing') }}/' + billId + '/payment';
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

// ISOLATED MODAL SYSTEM - COMPLETELY BYPASSES ALL LAYOUT CONFLICTS
let currentBillingId = null;

// Show isolated modal
function showIsolatedModal() {
    const modal = document.getElementById('isolatedSyncModal');
    modal.style.display = 'block';
    document.body.style.overflow = 'hidden'; // Prevent background scroll
}

// Close isolated modal
function closeIsolatedModal() {
    const modal = document.getElementById('isolatedSyncModal');
    modal.classList.add('closing');
    
    setTimeout(() => {
        modal.style.display = 'none';
        modal.classList.remove('closing');
        document.body.style.overflow = ''; // Restore scroll
        currentBillingId = null;
    }, 200);
}

// Close modal when clicking overlay
document.addEventListener('click', function(e) {
    if (e.target.id === 'isolatedModalOverlay') {
        closeIsolatedModal();
    }
});

// Close modal with Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape' && document.getElementById('isolatedSyncModal').style.display === 'block') {
        closeIsolatedModal();
    }
});

// Show sync details - ISOLATED VERSION
function showSyncDetails(billingId) {
    currentBillingId = billingId;
    
    // Show loading state
    document.getElementById('isolatedModalBody').innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary mb-3" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="text-muted">Loading sync details...</p>
        </div>
    `;
    
    showIsolatedModal();
    
    fetch(`/admin/billing/${billingId}/sync-details`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('isolatedModalBody').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <h6 style="color: #007bff; font-weight: 600; margin-bottom: 15px;">
                            <i class="fas fa-file-invoice me-2"></i>Billing Information
                        </h6>
                        <div style="background: #f8f9fa; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                            <p style="margin: 8px 0;"><strong>Bill Number:</strong> <span style="color: #007bff;">${data.bill_number}</span></p>
                            <p style="margin: 8px 0;"><strong>Patient:</strong> ${data.patient_name}</p>
                            <p style="margin: 8px 0;"><strong>Total Amount:</strong> <span style="color: #28a745; font-weight: 600;">£${data.total_amount}</span></p>
                            <p style="margin: 8px 0;"><strong>Status:</strong> 
                                <span style="background: #007bff; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: 500;">${data.status}</span>
                            </p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 style="color: #28a745; font-weight: 600; margin-bottom: 15px;">
                            <i class="fas fa-sync me-2"></i>Patient Portal Sync
                        </h6>
                        <div style="background: #f0f8f0; padding: 15px; border-radius: 8px; margin-bottom: 15px;">
                            <p style="margin: 8px 0;"><strong>Invoice Number:</strong> <span style="color: #28a745;">${data.invoice_number}</span></p>
                            <p style="margin: 8px 0;"><strong>Invoice Status:</strong> 
                                <span style="background: #28a745; color: white; padding: 4px 12px; border-radius: 15px; font-size: 12px; font-weight: 500;">${data.invoice_status}</span>
                            </p>
                            <p style="margin: 8px 0;"><strong>Payments Made:</strong> <span style="color: #28a745; font-weight: 600;">£${data.payments_made}</span></p>
                            <p style="margin: 8px 0;"><strong>Last Payment:</strong> ${data.last_payment_date || 'N/A'}</p>
                        </div>
                    </div>
                </div>
                <hr style="margin: 20px 0;">
                <div style="text-align: center; background: #e8f5e8; padding: 15px; border-radius: 8px;">
                    <i class="fas fa-check-circle" style="color: #28a745; font-size: 24px; margin-right: 10px;"></i>
                    <span style="color: #155724; font-weight: 600;">Sync Status: Synchronized</span>
                    <p style="margin: 10px 0 0 0; color: #6c757d; font-size: 14px;">Billing record is successfully synced with patient portal</p>
                </div>
            `;
        })
        .catch(error => {
            console.error('Error fetching sync details:', error);
            document.getElementById('isolatedModalBody').innerHTML = `
                <div style="text-align: center; padding: 40px; color: #dc3545;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 48px; margin-bottom: 20px;"></i>
                    <h5>Failed to Load Sync Details</h5>
                    <p style="color: #6c757d;">There was an error loading the synchronization details. Please try again.</p>
                    <button onclick="showSyncDetails(${billingId})" style="
                        background: #007bff;
                        color: white;
                        border: none;
                        padding: 10px 20px;
                        border-radius: 8px;
                        cursor: pointer;
                        margin-top: 15px;
                    ">Try Again</button>
                </div>
            `;
        });
}

// Force resync from isolated modal
function isolatedForceResync() {
    if (currentBillingId) {
        closeIsolatedModal();
        setTimeout(() => manualSync(currentBillingId), 300);
    }
}

function manualSync(billingId) {
    if (confirm('Manually sync this bill with the patient portal?')) {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        fetch(`/admin/billing/${billingId}/manual-sync`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Billing record synchronized successfully!');
                refreshTable();
            } else {
                alert('Sync failed: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error during manual sync:', error);
            alert('An error occurred during sync');
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
}

function forceResync() {
    const billingId = document.querySelector('.modal-title').getAttribute('data-billing-id');
    if (billingId) {
        closeSyncModal();
        setTimeout(() => manualSync(billingId), 300);
    }
}

// Select all checkbox functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.bill-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>
@endpush
@endsection
