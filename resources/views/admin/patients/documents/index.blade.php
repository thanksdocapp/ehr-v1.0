@extends('admin.layouts.app')

@section('title', 'Letters & Forms - ' . $patient->full_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.index') }}">Patients</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.show', $patient) }}">{{ $patient->full_name }}</a></li>
    <li class="breadcrumb-item active">Letters & Forms</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-file-alt me-2 text-primary"></i>Letters & Forms
            </h1>
            <p class="text-muted mb-0">Patient: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_id ?? 'N/A' }})</p>
        </div>
        @can('create', [\App\Models\PatientDocument::class, $patient])
        <div class="d-flex gap-2">
            <a href="{{ route('admin.patients.documents.create', $patient) }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create Document
            </a>
            <a href="{{ route('admin.patients.show', $patient) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Patient
            </a>
        </div>
        @endcan
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

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="doctor-card">
                <div class="doctor-card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="h4 mb-1 text-primary">{{ $documents->total() }}</div>
                            <div class="text-muted small">Total Documents</div>
                        </div>
                        <div style="width: 48px; height: 48px; background: rgba(13, 110, 253, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-file-alt text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="doctor-card">
                <div class="doctor-card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="h4 mb-1 text-warning">{{ $documents->where('status', 'draft')->count() }}</div>
                            <div class="text-muted small">Draft</div>
                        </div>
                        <div style="width: 48px; height: 48px; background: rgba(255, 193, 7, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-edit text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="doctor-card">
                <div class="doctor-card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="h4 mb-1 text-success">{{ $documents->where('status', 'final')->count() }}</div>
                            <div class="text-muted small">Final</div>
                        </div>
                        <div style="width: 48px; height: 48px; background: rgba(25, 135, 84, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="doctor-card">
                <div class="doctor-card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="h4 mb-1 text-info">{{ $documents->sum(function($doc) { return $doc->deliveries ? $doc->deliveries->where('status', 'sent')->count() : 0; }) }}</div>
                            <div class="text-muted small">Sent</div>
                        </div>
                        <div style="width: 48px; height: 48px; background: rgba(13, 202, 240, 0.1); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-envelope text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0"><i class="fas fa-filter me-2"></i>Filters</h5>
        </div>
        <div class="doctor-card-body">
            <form method="GET" action="{{ route('admin.patients.documents.index', $patient) }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Quick Search</label>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               placeholder="Search documents..." 
                               value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-control">
                            <option value="">All Types</option>
                            <option value="letter" {{ request('type') == 'letter' ? 'selected' : '' }}>Letter</option>
                            <option value="form" {{ request('type') == 'form' ? 'selected' : '' }}>Form</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>Final</option>
                            <option value="void" {{ request('status') == 'void' ? 'selected' : '' }}>Void</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Template</label>
                        <select name="template_id" class="form-control">
                            <option value="">All Templates</option>
                            @foreach(\App\Models\DocumentTemplate::active()->orderBy('name')->get() as $template)
                                <option value="{{ $template->id }}" {{ request('template_id') == $template->id ? 'selected' : '' }}>
                                    {{ $template->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                    </div>
                </div>
                @if(request()->anyFilled(['search', 'type', 'status', 'template_id']))
                <div class="mt-3">
                    <a href="{{ route('admin.patients.documents.index', $patient) }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-times me-1"></i>Clear Filters
                    </a>
                </div>
                @endif
            </form>
        </div>
    </div>

    <!-- Bulk Operations Toolbar -->
    @if($documents->count() > 0)
    <div class="doctor-card mb-3" id="bulkActionsToolbar" style="display: none;">
        <div class="doctor-card-body">
            <form action="{{ route('admin.patients.documents.bulk-action', $patient) }}" method="POST" id="bulkActionForm" onsubmit="return confirmBulkAction()">
                @csrf
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <strong id="selectedCount">0</strong> document(s) selected
                    </div>
                    <div class="btn-group">
                        <select class="form-select form-select-sm" id="bulkAction" name="action" required>
                            <option value="">Select Action</option>
                            <option value="finalise">Finalise</option>
                            <option value="void">Void</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-check me-1"></i>Apply
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSelection">
                            <i class="fas fa-times me-1"></i>Clear
                        </button>
                    </div>
                </div>
                <input type="hidden" name="document_ids" id="bulkDocumentIds">
            </form>
        </div>
    </div>
    @endif

    @if(session('bulk_errors'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Bulk Operation Results:</strong>
            <ul class="mb-0 mt-2">
                @foreach(session('bulk_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Documents Table -->
    <div class="doctor-card">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0">
                <i class="fas fa-list me-2"></i>Documents ({{ $documents->total() }})
            </h5>
        </div>
        <div class="doctor-card-body">
            @if($documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover" id="documentsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="50">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Template</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $document)
                            <tr>
                                <td>
                                    <input type="checkbox" 
                                           class="form-check-input document-checkbox" 
                                           value="{{ $document->id }}"
                                           data-title="{{ $document->title }}">
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $document->title }}</div>
                                    @if($document->template)
                                        <small class="text-muted">{{ $document->template->name }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $document->type === 'letter' ? 'primary' : 'info' }}">
                                        <i class="fas fa-{{ $document->type === 'letter' ? 'file-alt' : 'list' }} me-1"></i>
                                        {{ ucfirst($document->type) }}
                                    </span>
                                </td>
                                <td>
                                    @if($document->template)
                                        <span class="text-muted">{{ $document->template->name }}</span>
                                    @else
                                        <span class="text-muted">No template</span>
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'warning',
                                            'final' => 'success',
                                            'void' => 'danger'
                                        ];
                                        $statusColor = $statusColors[$document->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}">
                                        {{ ucfirst($document->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold">{{ $document->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $document->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($document->creator)
                                        <div class="fw-bold">{{ $document->creator->name }}</div>
                                        <small class="text-muted">{{ $document->creator->role }}</small>
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('admin.patients.documents.show', [$patient, $document]) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('update', $document)
                                        @if($document->isDraft())
                                            <a href="{{ route('admin.patients.documents.edit', [$patient, $document]) }}" 
                                               class="btn btn-sm btn-outline-secondary" 
                                               title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endif
                                        @endcan
                                        @can('finalise', $document)
                                        @if($document->isDraft())
                                            <form action="{{ route('admin.patients.documents.finalise', [$patient, $document]) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to finalise this document? This action cannot be undone.');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Finalise">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @endcan
                                        @can('download', $document)
                                        @if($document->isFinal() && $document->pdf_path)
                                            <a href="{{ route('admin.patients.documents.download', [$patient, $document]) }}" 
                                               class="btn btn-sm btn-outline-info" 
                                               title="Download PDF">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @endif
                                        @endcan
                                        @can('void', $document)
                                        @if(!$document->isVoid())
                                            <form action="{{ route('admin.patients.documents.void', [$patient, $document]) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to void this document?');">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Void">
                                                    <i class="fas fa-ban"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                
                @push('scripts')
                <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
                <script>
                $(document).ready(function() {
                    const selectAll = $('#selectAll');
                    const documentCheckboxes = $('.document-checkbox');
                    const bulkActionsToolbar = $('#bulkActionsToolbar');
                    const selectedCount = $('#selectedCount');
                    const bulkDocumentIds = $('#bulkDocumentIds');
                    
                    // Select all checkbox
                    selectAll.on('change', function() {
                        documentCheckboxes.prop('checked', $(this).is(':checked'));
                        updateBulkActions();
                    });
                    
                    // Individual checkbox
                    documentCheckboxes.on('change', function() {
                        updateBulkActions();
                        selectAll.prop('checked', documentCheckboxes.length === $('.document-checkbox:checked').length);
                    });
                    
                    // Update bulk actions toolbar
                    function updateBulkActions() {
                        const checked = $('.document-checkbox:checked');
                        const count = checked.length;
                        
                        if (count > 0) {
                            bulkActionsToolbar.show();
                            selectedCount.text(count);
                            
                            const ids = checked.map(function() {
                                return $(this).val();
                            }).get();
                            bulkDocumentIds.val(JSON.stringify(ids));
                        } else {
                            bulkActionsToolbar.hide();
                            bulkDocumentIds.val('');
                        }
                    }
                    
                    // Clear selection
                    $('#clearSelection').on('click', function() {
                        documentCheckboxes.prop('checked', false);
                        selectAll.prop('checked', false);
                        updateBulkActions();
                    });
                    
                    // Confirm bulk action
                    window.confirmBulkAction = function() {
                        const action = $('#bulkAction').val();
                        if (!action) {
                            alert('Please select an action.');
                            return false;
                        }
                        
                        const count = $('.document-checkbox:checked').length;
                        let message = `Are you sure you want to ${action} ${count} document(s)?`;
                        
                        if (action === 'delete') {
                            message += '\n\nThis action cannot be undone!';
                        }
                        
                        return confirm(message);
                    };
                });
                </script>
                @endpush

                <!-- Pagination -->
                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $documents->firstItem() }} to {{ $documents->lastItem() }} 
                        of {{ $documents->total() }} documents
                    </div>
                    {{ $documents->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No documents found</h5>
                    <p class="text-muted mb-4">There are no documents for this patient yet.</p>
                    @can('create', [\App\Models\PatientDocument::class, $patient])
                        <a href="{{ route('admin.patients.documents.create', $patient) }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create First Document
                        </a>
                    @endcan
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
    
    // Debounce search
    let searchTimeout;
    $('input[name="search"]').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function() {
            $('#filterForm').submit();
        }, 500);
    });
});
</script>
@endpush

