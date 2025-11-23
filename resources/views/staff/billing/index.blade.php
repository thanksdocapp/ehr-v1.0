@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Billing Overview')
@section('page-title', 'Billing Overview')
@section('page-subtitle', 'Track and manage bills efficiently')

@section('breadcrumb')
    <li class="breadcrumb-item">
        <a href="{{ contextRoute('dashboard') }}">Dashboard</a>
    </li>
    <li class="breadcrumb-item active">Billing</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Billing Overview</h1>
            <p class="text-muted mb-0">Track and manage bills efficiently</p>
        </div>
        <div>
            <a href="{{ contextRoute('billing.create') }}" class="btn btn-doctor-primary">
                <i class="fas fa-receipt me-2"></i>New Bill
            </a>
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

    <!-- Quick Search Bar -->
    <div class="doctor-card mb-3">
        <div class="doctor-card-body">
            <div class="d-flex gap-2 align-items-end">
                <div class="flex-grow-1">
                    <label class="form-label fw-semibold">Quick Search</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" 
                               id="quickSearch" 
                               name="search" 
                               class="form-control form-control-lg" 
                               placeholder="Search by bill #, patient name, description, payment reference..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-doctor-primary" onclick="toggleFilters()">
                        <i class="fas fa-filter me-1"></i>Filters
                        @php
                            $activeFiltersCount = count(array_filter(request()->except(['page', 'search'])));
                        @endphp
                        @if($activeFiltersCount > 0)
                            <span class="badge bg-primary ms-1">{{ $activeFiltersCount }}</span>
                        @endif
                    </button>
                </div>
                <div>
                    <a href="{{ contextRoute('billing.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-1"></i>New Bill
                    </a>
                </div>
                <div>
                    @if(request()->hasAny(['search', 'status', 'type', 'patient_name', 'doctor_id', 'department_id', 'billing_date_from', 'billing_date_to']))
                        <a href="{{ contextRoute('billing.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-1"></i>Clear All
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Active Filters Chips -->
    @php
        $activeFilters = [];
        if(request('status')) $activeFilters[] = ['key' => 'status', 'label' => 'Status: ' . ucfirst(str_replace('_', ' ', request('status')))];
        if(request('type')) $activeFilters[] = ['key' => 'type', 'label' => 'Type: ' . ucfirst(str_replace('_', ' ', request('type')))];
        if(request('patient_name')) $activeFilters[] = ['key' => 'patient_name', 'label' => 'Patient: ' . request('patient_name')];
        if(request('doctor_id')) {
            $doc = collect($doctors)->firstWhere('id', request('doctor_id'));
            if($doc) $activeFilters[] = ['key' => 'doctor_id', 'label' => 'Doctor: ' . $doc['name']];
        }
        if(request('department_id')) {
            $dept = $departments->firstWhere('id', request('department_id'));
            if($dept) $activeFilters[] = ['key' => 'department_id', 'label' => 'Department: ' . $dept->name];
        }
        if(request('billing_date_from')) $activeFilters[] = ['key' => 'billing_date_from', 'label' => 'Billing From: ' . request('billing_date_from')];
        if(request('billing_date_to')) $activeFilters[] = ['key' => 'billing_date_to', 'label' => 'Billing To: ' . request('billing_date_to')];
        if(request('date_range')) $activeFilters[] = ['key' => 'date_range', 'label' => 'Range: ' . ucfirst(str_replace('_', ' ', request('date_range')))];
        if(request('overdue')) $activeFilters[] = ['key' => 'overdue', 'label' => 'Overdue: Yes'];
        if(request('payment_method')) $activeFilters[] = ['key' => 'payment_method', 'label' => 'Payment: ' . ucfirst(request('payment_method'))];
    @endphp
    @if(count($activeFilters) > 0)
        <div class="mb-3" id="activeFilters">
            <div class="d-flex flex-wrap gap-2 align-items-center">
                <span class="text-muted small">Active filters:</span>
                @foreach($activeFilters as $filter)
                    <span class="badge bg-primary d-flex align-items-center gap-1">
                        {{ $filter['label'] }}
                        <button type="button" class="btn-close btn-close-white" style="font-size: 0.65rem;" onclick="removeFilter('{{ $filter['key'] }}')"></button>
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Filter Sidebar (Collapsible) -->
    <div class="doctor-card mb-4" id="filterPanel" style="display: {{ request()->hasAny(['status', 'type', 'patient_name', 'doctor_id', 'department_id', 'billing_date_from', 'billing_date_to', 'date_range']) ? 'block' : 'none' }};">
        <div class="doctor-card-header">
            <h6 class="doctor-card-title mb-0">
                <i class="fas fa-filter me-2"></i>Advanced Filters
            </h6>
        </div>
        <div class="doctor-card-body">
            <form method="GET" action="{{ contextRoute('billing.index') }}" id="filterForm">
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif
                
                <div class="row g-3">
                    <!-- Patient & Doctor Section -->
                    <div class="col-12">
                        <h6 class="text-primary border-bottom pb-2 mb-3">
                            <i class="fas fa-user-md me-2"></i>Patient & Doctor
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Patient Name</label>
                        <input type="text" name="patient_name" class="form-control" value="{{ request('patient_name') }}" placeholder="Search patient...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Doctor</label>
                        <select name="doctor_id" class="form-control">
                            <option value="">All Doctors</option>
                            @foreach($doctors ?? [] as $doctor)
                                <option value="{{ $doctor['id'] }}" {{ request('doctor_id') == $doctor['id'] ? 'selected' : '' }}>
                                    {{ $doctor['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Department</label>
                        <select name="department_id" class="form-control">
                            <option value="">All Departments</option>
                            @foreach($departments ?? [] as $dept)
                                <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status & Type Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-success border-bottom pb-2 mb-3">
                            <i class="fas fa-tag me-2"></i>Status & Type
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Statuses</option>
                            @foreach($statuses ?? [] as $status)
                                <option value="{{ $status }}" {{ request('status') == $status ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $status)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Billing Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            @foreach($billingTypes ?? [] as $type)
                                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $type)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Date & Time Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-info border-bottom pb-2 mb-3">
                            <i class="fas fa-calendar-alt me-2"></i>Date & Time
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Quick Date Range</label>
                        <select name="date_range" class="form-control">
                            <option value="">Select Range</option>
                            <option value="today" {{ request('date_range') == 'today' ? 'selected' : '' }}>Today</option>
                            <option value="yesterday" {{ request('date_range') == 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                            <option value="this_week" {{ request('date_range') == 'this_week' ? 'selected' : '' }}>This Week</option>
                            <option value="last_week" {{ request('date_range') == 'last_week' ? 'selected' : '' }}>Last Week</option>
                            <option value="this_month" {{ request('date_range') == 'this_month' ? 'selected' : '' }}>This Month</option>
                            <option value="last_month" {{ request('date_range') == 'last_month' ? 'selected' : '' }}>Last Month</option>
                            <option value="this_year" {{ request('date_range') == 'this_year' ? 'selected' : '' }}>This Year</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Billing Date From</label>
                        <input type="date" name="billing_date_from" class="form-control" value="{{ request('billing_date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Billing Date To</label>
                        <input type="date" name="billing_date_to" class="form-control" value="{{ request('billing_date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Specific Billing Date</label>
                        <input type="date" name="billing_date" class="form-control" value="{{ request('billing_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Date From</label>
                        <input type="date" name="due_date_from" class="form-control" value="{{ request('due_date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Date To</label>
                        <input type="date" name="due_date_to" class="form-control" value="{{ request('due_date_to') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Created From</label>
                        <input type="date" name="created_from" class="form-control" value="{{ request('created_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Created To</label>
                        <input type="date" name="created_to" class="form-control" value="{{ request('created_to') }}">
                    </div>

                    <!-- Amount Filters Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-warning border-bottom pb-2 mb-3">
                            <i class="fas fa-dollar-sign me-2"></i>Amount Filters
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Total Amount Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="total_amount_min" class="form-control" placeholder="Min" value="{{ request('total_amount_min') }}" min="0" step="0.01">
                            </div>
                            <div class="col-6">
                                <input type="number" name="total_amount_max" class="form-control" placeholder="Max" value="{{ request('total_amount_max') }}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Balance Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="balance_min" class="form-control" placeholder="Min" value="{{ request('balance_min') }}" min="0" step="0.01">
                            </div>
                            <div class="col-6">
                                <input type="number" name="balance_max" class="form-control" placeholder="Max" value="{{ request('balance_max') }}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Paid Amount Range</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="number" name="paid_amount_min" class="form-control" placeholder="Min" value="{{ request('paid_amount_min') }}" min="0" step="0.01">
                            </div>
                            <div class="col-6">
                                <input type="number" name="paid_amount_max" class="form-control" placeholder="Max" value="{{ request('paid_amount_max') }}" min="0" step="0.01">
                            </div>
                        </div>
                    </div>

                    <!-- Payment Filters Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-danger border-bottom pb-2 mb-3">
                            <i class="fas fa-credit-card me-2"></i>Payment Filters
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Payment Method</label>
                        <select name="payment_method" class="form-control">
                            <option value="">All Methods</option>
                            @foreach($paymentMethods ?? [] as $method)
                                <option value="{{ $method }}" {{ request('payment_method') == $method ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $method)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has Payment</label>
                        <select name="has_payment" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('has_payment') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('has_payment') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has Payment Reference</label>
                        <select name="has_payment_reference" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('has_payment_reference') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('has_payment_reference') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>

                    <!-- Additional Filters Section -->
                    <div class="col-12 mt-3">
                        <h6 class="text-secondary border-bottom pb-2 mb-3">
                            <i class="fas fa-filter me-2"></i>Additional Filters
                        </h6>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Overdue</label>
                        <select name="overdue" class="form-control">
                            <option value="">All</option>
                            <option value="1" {{ request('overdue') ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Due Soon (Next 7 Days)</label>
                        <select name="due_soon" class="form-control">
                            <option value="">All</option>
                            <option value="1" {{ request('due_soon') ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Has Appointment</label>
                        <select name="has_appointment" class="form-control">
                            <option value="">All</option>
                            <option value="yes" {{ request('has_appointment') == 'yes' ? 'selected' : '' }}>Yes</option>
                            <option value="no" {{ request('has_appointment') == 'no' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Description</label>
                        <input type="text" name="description" class="form-control" value="{{ request('description') }}" placeholder="Search description...">
                    </div>

                    <!-- Form Actions -->
                    <div class="col-12 mt-3">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-search me-1"></i>Apply Filters
                            </button>
                            <a href="{{ contextRoute('billing.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Clear All
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Billing Table -->
    <div class="doctor-card">
        <div class="doctor-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Bills List</h5>
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
                                <th>Bill ID</th>
                                <th>Patient</th>
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
                                    <div class="fw-bold text-primary">#{{ $bill->bill_number }}</div>
                                    <small class="text-muted">{{ $bill->created_at->format('M d, Y') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder bg-info text-white rounded-circle me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            {{ strtoupper(substr($bill->patient->first_name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $bill->patient->full_name }}</div>
                                            <small class="text-muted">{{ $bill->patient->patient_id }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ CurrencyHelper::format($bill->total_amount) }}</div>
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
                                    <div>{{ $bill->billing_date->format('M d, Y') }}</div>
                                    @if($bill->due_date)
                                        <small class="text-muted">Due: {{ $bill->due_date->format('M d, Y') }}</small>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ contextRoute('billing.show', $bill) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        {{-- Note: Staff cannot edit or process payments for bills per route configuration --}}
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="card-footer d-flex justify-content-between align-items-center">
                    <div class="text-muted">
                        Showing {{ $bills->firstItem() }} to {{ $bills->lastItem() }} 
                        of {{ $bills->total() }} results
                    </div>
                    <div>
                        {{ $bills->appends(request()->query())->links() }}
                    </div>
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

@push('scripts')
<script>
// Debounced Quick Search
let searchTimeout;
$(document).ready(function() {
    $('#quickSearch').on('input', function() {
        clearTimeout(searchTimeout);
        const searchValue = $(this).val();
        
        searchTimeout = setTimeout(function() {
            const url = new URL(window.location.href);
            if (searchValue) {
                url.searchParams.set('search', searchValue);
            } else {
                url.searchParams.delete('search');
            }
            url.searchParams.delete('page'); // Reset to first page
            window.location.href = url.toString();
        }, 400); // 400ms debounce
    });

    // Toggle filter panel
    window.toggleFilters = function() {
        const panel = document.getElementById('filterPanel');
        if (panel.style.display === 'none' || !panel.style.display) {
            panel.style.display = 'block';
        } else {
            panel.style.display = 'none';
        }
    };

    // Remove individual filter
    window.removeFilter = function(filterKey) {
        const url = new URL(window.location.href);
        url.searchParams.delete(filterKey);
        url.searchParams.delete('page'); // Reset to first page
        window.location.href = url.toString();
    };
});

function exportBills() {
    // Implementation for exporting bills
    console.log('Export bills functionality');
}

function refreshTable() {
    window.location.reload();
}
</script>
@endpush
@endsection
