@extends('admin.layouts.app')

@section('title', 'View About Statistic')
@section('page-title', 'View About Statistic')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">View About Statistic</h1>
            <p class="text-muted">{{ $aboutStat->title }} - Details and Preview</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ contextRoute('about-stats.edit', $aboutStat) }}" class="btn btn-primary">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="{{ contextRoute('about-stats.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Details Column -->
        <div class="col-lg-8">
            <!-- Basic Information -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Basic Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Title</label>
                                <div class="fw-bold">{{ $aboutStat->title }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Subtitle</label>
                                <div class="fw-bold">{{ $aboutStat->subtitle ?: 'Not set' }}</div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Value</label>
                                <div class="fw-bold fs-4" style="color: {{ $aboutStat->color ?: '#0d6efd' }};">
                                    {{ $aboutStat->prefix }}{{ $aboutStat->value }}{{ $aboutStat->suffix }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Icon</label>
                                <div>
                                    @if($aboutStat->icon)
                                        <i class="{{ $aboutStat->icon }}" style="color: {{ $aboutStat->color ?: '#0d6efd' }};"></i>
                                        <span class="ms-2">{{ $aboutStat->icon }}</span>
                                    @else
                                        <span class="text-muted">No icon set</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Color</label>
                                <div class="d-flex align-items-center">
                                    <div class="color-preview me-2" style="width: 20px; height: 20px; background: {{ $aboutStat->color ?: '#0d6efd' }}; border-radius: 4px; border: 1px solid #ddd;"></div>
                                    <span>{{ $aboutStat->color ?: '#0d6efd' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($aboutStat->description)
                        <div class="mb-3">
                            <label class="form-label text-muted small">Description</label>
                            <div class="p-3 bg-light rounded">{{ $aboutStat->description }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Display Settings -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-cog me-2"></i>Display Settings
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Status</label>
                                <div>
                                    <span class="badge {{ $aboutStat->is_active ? 'bg-success' : 'bg-secondary' }} fs-6">
                                        <i class="fas fa-{{ $aboutStat->is_active ? 'check' : 'times' }} me-1"></i>
                                        {{ $aboutStat->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Sort Order</label>
                                <div class="fw-bold">{{ $aboutStat->sort_order }}</div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Display Priority</label>
                                <div class="fw-bold">
                                    @if($aboutStat->sort_order == 0)
                                        First
                                    @elseif($aboutStat->sort_order <= 2)
                                        High
                                    @elseif($aboutStat->sort_order <= 5)
                                        Medium
                                    @else
                                        Low
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-clock me-2"></i>Timestamps
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Created</label>
                                <div class="fw-bold">{{ formatDateTime($aboutStat->created_at) }}</div>
                                <small class="text-muted">{{ $aboutStat->created_at->diffForHumans() }}</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label text-muted small">Last Updated</label>
                                <div class="fw-bold">{{ formatDateTime($aboutStat->updated_at) }}</div>
                                <small class="text-muted">{{ $aboutStat->updated_at->diffForHumans() }}</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Preview Column -->
        <div class="col-lg-4">
            <!-- Live Preview -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-eye me-2"></i>Frontend Preview
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="stat-card p-4 rounded-3 shadow-sm" style="background: {{ $aboutStat->color ?: '#0d6efd' }}; color: white;">
                        @if($aboutStat->icon)
                            <div class="stat-icon mb-3">
                                <i class="{{ $aboutStat->icon }} fs-1"></i>
                            </div>
                        @endif
                        
                        <h3 class="fw-bold mb-2">
                            {{ $aboutStat->prefix }}{{ $aboutStat->value }}{{ $aboutStat->suffix }}
                        </h3>
                        
                        <p class="mb-0">{{ $aboutStat->title }}</p>
                        
                        @if($aboutStat->subtitle)
                            <small class="opacity-75">{{ $aboutStat->subtitle }}</small>
                        @endif
                    </div>
                    
                    @if($aboutStat->description)
                        <div class="mt-3 p-2 bg-light rounded text-muted small">
                            {{ $aboutStat->description }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ contextRoute('about-stats.edit', $aboutStat) }}" class="btn btn-primary">
                            <i class="fas fa-edit me-2"></i>Edit Statistic
                        </a>
                        
                        <button type="button" class="btn btn-outline-{{ $aboutStat->is_active ? 'warning' : 'success' }}" 
                                onclick="toggleStatus({{ $aboutStat->id }}, {{ $aboutStat->is_active ? 'true' : 'false' }})">
                            <i class="fas fa-{{ $aboutStat->is_active ? 'pause' : 'play' }} me-2"></i>
                            {{ $aboutStat->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                        
                        <button type="button" class="btn btn-outline-danger" 
                                onclick="deleteStatistic({{ $aboutStat->id }}, '{{ $aboutStat->title }}')">
                            <i class="fas fa-trash me-2"></i>Delete
                        </button>
                        
                        <hr class="my-2">
                        
                        <a href="{{ url('/about') }}" target="_blank" class="btn btn-outline-info">
                            <i class="fas fa-external-link-alt me-2"></i>View on Frontend
                        </a>
                    </div>
                </div>
            </div>

            <!-- Statistics -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-bar me-2"></i>Statistics
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $totalStats = \App\Models\AboutStat::count();
                        $activeStats = \App\Models\AboutStat::where('is_active', true)->count();
                        $inactiveStats = $totalStats - $activeStats;
                    @endphp
                    
                    <div class="row g-3 text-center">
                        <div class="col-6">
                            <div class="p-2 bg-primary bg-opacity-10 rounded">
                                <div class="fw-bold text-primary">{{ $totalStats }}</div>
                                <small class="text-muted">Total Stats</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-success bg-opacity-10 rounded">
                                <div class="fw-bold text-success">{{ $activeStats }}</div>
                                <small class="text-muted">Active</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr class="my-2">
                    
                    <div class="small text-center">
                        <div class="text-muted">Position in list: 
                            <span class="fw-bold">{{ $aboutStat->sort_order + 1 }}</span> of {{ $totalStats }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the statistic "<span id="deleteItemName"></span>"?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Status Toggle Confirmation Modal -->
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Status Change</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to <span id="statusAction"></span> this statistic?</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmStatusChange">Confirm</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Setup CSRF token for AJAX requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
});

function deleteStatistic(id, title) {
    $('#deleteItemName').text(title);
    $('#deleteForm').attr('action', '/admin/about-stats/' + id);
    $('#deleteModal').modal('show');
}

function toggleStatus(id, currentStatus) {
    const action = currentStatus ? 'deactivate' : 'activate';
    $('#statusAction').text(action);
    $('#confirmStatusChange').off('click').on('click', function() {
        $.post(`/admin/about-stats/${id}/toggle-status`)
            .done(function(response) {
                if (response.success) {
                    // Show success message and reload page
                    showNotification(response.message, 'success');
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                }
            })
            .fail(function() {
                showNotification('Error updating status. Please try again.', 'error');
            });
        
        $('#statusModal').modal('hide');
    });
    $('#statusModal').modal('show');
}

function showNotification(message, type = 'success') {
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const iconClass = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${iconClass} me-2"></i>${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
    
    $('.container-fluid').prepend(alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut();
    }, 30000);
}
</script>
@endpush
