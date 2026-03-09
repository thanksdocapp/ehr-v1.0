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

    @if(session('booking_link'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-start">
            <i class="fas fa-check-circle me-2 mt-1"></i>
            <div class="flex-grow-1">
                <div>{{ session('success', 'Booking link generated.') }}</div>
                <div class="mt-3 p-3 bg-white rounded border">
                    <div class="mb-2">
                        <strong class="d-block mb-2">
                            <i class="fas fa-link me-2"></i>Booking Link:
                        </strong>
                        <div class="input-group">
                            <input type="text" class="form-control font-monospace" id="bookingLinkInput" value="{{ session('booking_link') }}" readonly style="font-size: 0.875rem;">
                            <button class="btn btn-outline-secondary" type="button" onclick="copyBookingLink(event)" title="Copy to clipboard">
                                <i class="fas fa-copy me-1"></i>Copy
                            </button>
                            <a href="{{ session('booking_link') }}" target="_blank" class="btn btn-primary" title="Open booking link">
                                <i class="fas fa-external-link-alt me-1"></i>Open
                            </a>
                        </div>
                    </div>
                    @if(session('service_name'))
                    <div class="mt-2 pt-2 border-top">
                        <small class="text-muted">
                            <strong>Service:</strong> {{ session('service_name') }}
                        </small>
                    </div>
                    @endif
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-briefcase-medical me-2"></i>My Services
                    </h5>
                    <small class="text-muted">Create and manage your private services. Drag rows to change the order shown on your booking page.</small>
                </div>
                <div class="doctor-card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 36px;" class="text-center" title="Drag to reorder"><i class="fas fa-grip-vertical text-muted"></i></th>
                                    <th style="width: 25%;">Service Name</th>
                                    <th style="width: 15%;">Duration</th>
                                    <th style="width: 15%;">Price</th>
                                    <th style="width: 15%;">Status</th>
                                    <th style="width: 15%;">Override</th>
                                    <th style="width: 20%;" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="doctor-services-sortable">
                                @forelse($services as $service)
                                    <tr class="doctor-service-row" data-id="{{ $service['id'] }}">
                                        <td class="text-center align-middle" style="cursor: grab;">
                                            <i class="fas fa-grip-vertical text-muted drag-handle" style="opacity: 0.6;"></i>
                                        </td>
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
                                                @if($service['is_active_for_doctor'])
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-success" 
                                                        title="Get Booking Link"
                                                        onclick="showBookingLinkModal({{ $service['id'] }}, '{{ addslashes($service['name']) }}')">
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
                                        <td colspan="7" class="text-center py-5">
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

<!-- Booking Link Modal -->
<div class="modal fade" id="bookingLinkModal" tabindex="-1" aria-labelledby="bookingLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="bookingLinkModalLabel">
                    <i class="fas fa-link me-2"></i>Generate Booking Link
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bookingLinkForm" method="POST" action="{{ route('staff.doctor-services.generate-booking-link') }}">
                @csrf
                <input type="hidden" name="service_id" id="modal_service_id">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Service</label>
                        <input type="text" class="form-control" id="modal_service_name" readonly>
                    </div>
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Note:</strong> A booking link will be generated that can be used on websites. 
                        Patients will be taken directly to the booking page with this service and your profile pre-selected.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-link me-2"></i>Generate Booking Link
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function showBookingLinkModal(serviceId, serviceName) {
    document.getElementById('modal_service_id').value = serviceId;
    document.getElementById('modal_service_name').value = serviceName;
    
    const modal = new bootstrap.Modal(document.getElementById('bookingLinkModal'));
    modal.show();
}

function copyBookingLink(event) {
    event.preventDefault();
    const input = document.getElementById('bookingLinkInput');
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var tbody = document.getElementById('doctor-services-sortable');
    if (!tbody || typeof Sortable === 'undefined') return;
    new Sortable(tbody, {
        handle: '.drag-handle',
        animation: 150,
        onEnd: function() {
            var rows = tbody.querySelectorAll('tr.doctor-service-row');
            var order = Array.from(rows).map(function(row) { return parseInt(row.getAttribute('data-id'), 10); });
            fetch('{{ route("staff.doctor-services.reorder") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ order: order })
            }).then(function(r) { return r.json(); }).then(function(data) {
                if (data.success) {
                    var toast = document.createElement('div');
                    toast.className = 'alert alert-success alert-dismissible fade show position-fixed';
                    toast.style.cssText = 'top: 1rem; right: 1rem; z-index: 9999; min-width: 200px;';
                    toast.innerHTML = '<span>' + (data.message || 'Order saved.') + '</span><button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
                    document.body.appendChild(toast);
                    setTimeout(function() { toast.remove(); }, 3000);
                }
            }).catch(function() {});
        }
    });
});
</script>
@endpush

