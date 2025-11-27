@extends('admin.layouts.app')

@section('title', 'Payment Gateways')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Payment Gateways</li>
@endsection

@include('admin.shared.modern-ui')

@push('styles')
<style>
.table-actions .btn {
    margin: 0 2px;
    padding: 0.375rem 0.75rem;
}

.status-badge {
    font-size: 0.75rem;
    padding: 0.25rem 0.75rem;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="modern-page-header">
        <div class="modern-page-header-content">
            <h1 class="modern-page-title">
                <i class="fas fa-credit-card" style="color: #1a202c;"></i>
                Payment Gateways
            </h1>
            <p class="modern-page-subtitle">Manage and configure payment gateways for your hospital's billing system</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="stat-card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-primary" style="font-size: 1.75rem; font-weight: 600;">{{ $gateways->count() ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Total Gateways</div>
                    </div>
                    <div class="stat-icon" style="background: #f8f9fc; border: 1px solid #e2e8f0; width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-credit-card" style="color: #1a202c;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-success" style="font-size: 1.75rem; font-weight: 600;">{{ $gateways->where('is_active', true)->count() ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Active Gateways</div>
                    </div>
                    <div class="stat-icon" style="background: #f8f9fc; border: 1px solid #e2e8f0; width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-check-circle" style="color: #1a202c;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-warning" style="font-size: 1.75rem; font-weight: 600;">{{ $gateways->where('is_active', false)->count() ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Inactive Gateways</div>
                    </div>
                    <div class="stat-icon" style="background: #f8f9fc; border: 1px solid #e2e8f0; width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-times-circle" style="color: #1a202c;"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="stat-card" style="padding: 1rem;">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="stat-number text-info" style="font-size: 1.75rem; font-weight: 600;">{{ $gateways->where('is_default', true)->count() ?? 0 }}</div>
                        <div class="stat-label" style="font-size: 0.875rem; margin-top: 0.25rem;">Default Gateway</div>
                    </div>
                    <div class="stat-icon" style="background: #f8f9fc; border: 1px solid #e2e8f0; width: 48px; height: 48px; font-size: 1.25rem;">
                        <i class="fas fa-star" style="color: #1a202c;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Gateways Table -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Configured Payment Gateways</h5>
            <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary" onclick="refreshTable()">
                    <i class="fas fa-sync me-1"></i>Refresh
                </button>
                <a href="{{ contextRoute('payment-gateways.create') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-plus me-1"></i>Add New Gateway
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($gateways->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Gateway</th>
                                <th>Display Name</th>
                                <th>Provider</th>
                                <th>Status</th>
                                <th>Default</th>
                                <th>Test Mode</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($gateways as $gateway)
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input gateway-checkbox" 
                                           value="{{ $gateway->id }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-placeholder bg-primary text-white rounded me-3 d-flex align-items-center justify-content-center" 
                                             style="width: 40px; height: 40px;">
                                            <i class="fas fa-{{ $gateway->provider === 'stripe' ? 'cc-stripe' : ($gateway->provider === 'paypal' ? 'cc-paypal' : 'credit-card') }}"></i>
                                        </div>
                                        <div>
                                            <div class="fw-bold">{{ $gateway->display_name }}</div>
                                            <small class="text-muted">{{ ucfirst($gateway->provider) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $gateway->display_name }}</div>
                                    <small class="text-muted">{{ $gateway->description ?? 'No description' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ ucfirst($gateway->provider) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $gateway->is_active ? 'success' : 'secondary' }}">
                                        {{ $gateway->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    @if($gateway->is_default)
                                        <span class="badge bg-primary">
                                            <i class="fas fa-star me-1"></i>Default
                                        </span>
                                    @else
                                        <span class="badge bg-light text-dark">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $gateway->is_test_mode ? 'warning' : 'info' }}">
                                        {{ $gateway->is_test_mode ? 'Test' : 'Live' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ contextRoute('payment-gateways.show', $gateway) }}" 
                                           class="btn btn-sm btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ contextRoute('payment-gateways.edit', $gateway) }}" 
                                           class="btn btn-sm btn-outline-warning" title="Edit Gateway">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-outline-info" 
                                                title="Test Connection" onclick="testConnection({{ $gateway->id }})">
                                            <i class="fas fa-wifi"></i>
                                        </button>
                                        @if(!$gateway->is_default)
                                            <button type="button" class="btn btn-sm btn-outline-success" 
                                                    title="Set as Default" onclick="setDefault({{ $gateway->id }})">
                                                <i class="fas fa-star"></i>
                                            </button>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-outline-{{ $gateway->is_active ? 'secondary' : 'success' }}" 
                                                title="{{ $gateway->is_active ? 'Deactivate' : 'Activate' }}" onclick="toggleStatus({{ $gateway->id }})">
                                            <i class="fas fa-{{ $gateway->is_active ? 'pause' : 'play' }}"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-danger" 
                                                title="Delete Gateway" onclick="deleteGateway({{ $gateway->id }})">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No payment gateways configured</h5>
                    <p class="text-muted">Get started by adding your first payment gateway to accept online payments.</p>
                    <a href="{{ contextRoute('payment-gateways.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add First Gateway
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function refreshTable() {
    window.location.reload();
}

function testConnection(gatewayId) {
    const btn = event.target.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
    btn.disabled = true;
    
    fetch(`{{ url('admin/payment-gateways') }}/${gatewayId}/test-connection`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('✅ Connection test successful!');
        } else {
            alert('❌ Connection test failed: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error testing connection:', error);
        alert('An error occurred during connection test');
    })
    .finally(() => {
        btn.innerHTML = originalHtml;
        btn.disabled = false;
    });
}

function setDefault(gatewayId) {
    if (confirm('Set this gateway as the default payment method?')) {
        const btn = event.target.closest('button');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        fetch(`{{ url('admin/payment-gateways') }}/${gatewayId}/set-default`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Gateway set as default successfully!');
                refreshTable();
            } else {
                alert('Failed to set as default: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error setting default:', error);
            alert('An error occurred while setting default');
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
}

function toggleStatus(gatewayId) {
    const btn = event.target.closest('button');
    const isActive = btn.title.includes('Deactivate');
    const action = isActive ? 'deactivate' : 'activate';
    
    if (confirm(`Are you sure you want to ${action} this payment gateway?`)) {
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        btn.disabled = true;
        
        fetch(`{{ url('admin/payment-gateways') }}/${gatewayId}/toggle-status`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(`Gateway ${action}d successfully!`);
                refreshTable();
            } else {
                alert(`Failed to ${action}: ` + data.message);
            }
        })
        .catch(error => {
            console.error(`Error ${action}ing gateway:`, error);
            alert(`An error occurred while ${action}ing the gateway`);
        })
        .finally(() => {
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }
}

function deleteGateway(id) {
    if (confirm('⚠️ WARNING: Are you sure you want to permanently delete this payment gateway?\n\nThis action cannot be undone and will remove:\n- Gateway configuration\n- Payment history\n- Associated settings\n\nClick OK to confirm deletion or Cancel to abort.')) {
        document.getElementById(`delete-form-${id}`).submit();
    }
}

// Select all checkbox functionality
document.getElementById('selectAll').addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.gateway-checkbox');
    checkboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
});
</script>
@endpush

@foreach($gateways as $gateway)
    <form id="delete-form-{{ $gateway->id }}" action="{{ contextRoute('payment-gateways.destroy', $gateway) }}" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endforeach
@endsection
