@extends('layouts.doctor')

@section('title', 'My Services')
@section('page-title', 'My Services')
@section('page-subtitle', 'Manage your service pricing and availability')

@section('content')
<div class="fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div></div>
        <div>
            <a href="{{ route('staff.doctor-services.create') }}" class="btn btn-primary me-2">
                <i class="fas fa-plus me-2"></i>Add Service
            </a>
            <a href="{{ route('staff.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-start">
            <i class="fas fa-check-circle me-2 mt-1"></i>
            <div class="flex-grow-1">
                <div>{{ session('success') }}</div>
                @if(session('payment_link'))
                <div class="mt-3 p-3 bg-white rounded border">
                    <div class="mb-2">
                        <strong class="d-block mb-2">
                            <i class="fas fa-link me-2"></i>Payment Link:
                        </strong>
                        <div class="input-group">
                            <input type="text" class="form-control font-monospace" id="paymentLinkInput" value="{{ session('payment_link') }}" readonly style="font-size: 0.875rem;">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyPaymentLink(event)" title="Copy to clipboard">
                                <i class="fas fa-copy me-1"></i>Copy
                            </button>
                            <a href="{{ session('payment_link') }}" target="_blank" class="btn btn-primary" title="Open payment link">
                                <i class="fas fa-external-link-alt me-1"></i>Open
                            </a>
                        </div>
                    </div>
                    @if(session('invoice_number'))
                    <div class="mt-2 pt-2 border-top">
                        <small class="text-muted">
                            <strong>Invoice:</strong> #{{ session('invoice_number') }}
                            @if(session('billing_number'))
                            | <strong>Bill:</strong> #{{ session('billing_number') }}
                            @endif
                            @if(session('service_name'))
                            | <strong>Service:</strong> {{ session('service_name') }}
                            @endif
                            @if(session('patient_name'))
                            | <strong>Patient:</strong> {{ session('patient_name') }}
                            @endif
                        </small>
                    </div>
                    @endif
                </div>
                @endif
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-briefcase-medical me-2"></i>My Services
                    </h5>
                    <small class="text-muted">Create and manage your private services. Only you can see and edit services you create.</small>
                </div>
                <div class="doctor-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%;">Service Name</th>
                                    <th style="width: 15%;">Duration</th>
                                    <th style="width: 15%;">Price</th>
                                    <th style="width: 15%;">Status</th>
                                    <th style="width: 15%;">Override</th>
                                    <th style="width: 20%;" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    <tr>
                                        <td>
                                            <div>
                                                <div class="fw-semibold">{{ $service['name'] }}</div>
                                                @if($service['description'])
                                                <small class="text-muted">{{ Str::limit($service['description'], 50) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($service['has_override'] && $service['custom_duration_minutes'])
                                                <span class="badge bg-info text-dark">{{ $service['custom_duration_minutes'] }} min</span>
                                                <small class="text-muted d-block">(Custom)</small>
                                            @else
                                                <span class="badge bg-light text-dark">{{ $service['default_duration_minutes'] ?? 60 }} min</span>
                                                <small class="text-muted d-block">(Default)</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service['has_override'] && $service['custom_price'] !== null)
                                                <strong>£{{ number_format($service['custom_price'], 2) }}</strong>
                                                <small class="text-muted d-block">(Custom)</small>
                                            @elseif($service['default_price'])
                                                <strong>£{{ number_format($service['default_price'], 2) }}</strong>
                                                <small class="text-muted d-block">(Default)</small>
                                            @else
                                                <span class="text-muted">On request</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service['is_active_for_doctor'])
                                            <span class="badge bg-success">Active</span>
                                            @else
                                            <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service['has_override'])
                                            <span class="badge bg-primary">Yes</span>
                                            @else
                                            <span class="badge bg-light text-dark">No</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="d-flex gap-1 justify-content-end">
                                                @if($service['is_active_for_doctor'] && ($service['custom_price'] ?? $service['default_price']))
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        title="Get Payment Link"
                                                        onclick="showPaymentLinkModal({{ $service['id'] }}, '{{ addslashes($service['name']) }}', {{ $service['custom_price'] ?? $service['default_price'] ?? 0 }})">
                                                    <i class="fas fa-link"></i>
                                                </button>
                                                @endif
                                                <a href="{{ route('staff.doctor-services.edit', $service['id']) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Edit Service">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('staff.doctor-services.toggle-status', $service['id']) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-{{ $service['is_active_for_doctor'] ? 'warning' : 'success' }}"
                                                            title="{{ $service['is_active_for_doctor'] ? 'Deactivate Service' : 'Activate Service' }}">
                                                        <i class="fas fa-{{ $service['is_active_for_doctor'] ? 'eye-slash' : 'eye' }}"></i>
                                                    </button>
                                                </form>
                                                @if($service['has_override'])
                                                <form action="{{ route('staff.doctor-services.destroy', $service['id']) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Remove custom price/duration settings and revert to default values?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-info"
                                                            title="Remove Custom Settings (Revert to Defaults)">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                                @endif
                                                <form action="{{ route('staff.doctor-services.delete-service', $service['id']) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this service? This action cannot be undone.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Delete Service">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No services available.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment Link Modal -->
<div class="modal fade" id="paymentLinkModal" tabindex="-1" aria-labelledby="paymentLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentLinkModalLabel">
                    <i class="fas fa-link me-2"></i>Generate Payment Link
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="paymentLinkForm" method="POST" action="{{ route('staff.doctor-services.generate-payment-link') }}">
                @csrf
                <input type="hidden" name="service_id" id="modal_service_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Service</label>
                        <input type="text" class="form-control" id="modal_service_name" readonly>
                        <small class="text-muted" id="modal_service_price"></small>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> A payment link will be generated that can be used on websites. 
                        Patient information will be collected when the payment is made.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-link me-2"></i>Generate Payment Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showPaymentLinkModal(serviceId, serviceName, price) {
    document.getElementById('modal_service_id').value = serviceId;
    document.getElementById('modal_service_name').value = serviceName;
    document.getElementById('modal_service_price').textContent = 'Price: £' + parseFloat(price).toFixed(2);
    
    const modal = new bootstrap.Modal(document.getElementById('paymentLinkModal'));
    modal.show();
}

function copyPaymentLink(event) {
    event.preventDefault();
    const input = document.getElementById('paymentLinkInput');
    if (input) {
        input.select();
        input.setSelectionRange(0, 99999); // For mobile devices
        
        try {
            // Try modern clipboard API first
            navigator.clipboard.writeText(input.value).then(() => {
                showCopyFeedback(event.target);
            }).catch(() => {
                // Fallback to execCommand
                document.execCommand('copy');
                showCopyFeedback(event.target);
            });
        } catch (e) {
            // Fallback to execCommand if clipboard API fails
            document.execCommand('copy');
            showCopyFeedback(event.target);
        }
    }
}

function showCopyFeedback(button) {
    const btn = button.closest('button');
    const originalHtml = btn.innerHTML;
    btn.innerHTML = '<i class="fas fa-check me-1"></i>Copied!';
    btn.classList.add('btn-success');
    btn.classList.remove('btn-outline-secondary');
    
    setTimeout(() => {
        btn.innerHTML = originalHtml;
        btn.classList.remove('btn-success');
        btn.classList.add('btn-outline-secondary');
    }, 2000);
}
</script>
@endsection

