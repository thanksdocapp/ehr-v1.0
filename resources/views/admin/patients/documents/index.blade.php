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
    <!-- Page Header with Gradient -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body py-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-white">
                    <h1 class="h3 mb-1 fw-bold">
                        <i class="fas fa-file-alt me-2"></i>Letters & Forms
                    </h1>
                    <p class="mb-0 opacity-75">
                        Patient: <strong>{{ $patient->full_name }}</strong>
                        <span class="badge bg-white bg-opacity-25 ms-2">{{ $patient->patient_id ?? 'N/A' }}</span>
                    </p>
                </div>
                <div class="d-flex gap-2">
                    @can('create', [\App\Models\PatientDocument::class, $patient])
                    <a href="{{ route('admin.patients.documents.create', $patient) }}" class="btn btn-light">
                        <i class="fas fa-plus me-2"></i>Create Document
                    </a>
                    @endcan
                    <a href="{{ route('admin.patients.show', $patient) }}" class="btn btn-outline-light">
                        <i class="fas fa-arrow-left me-2"></i>Back to Patient
                    </a>
                </div>
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

    <!-- Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="h3 mb-1 fw-bold text-primary">{{ $documents->total() }}</div>
                            <div class="text-muted small">Total Documents</div>
                        </div>
                        <div class="rounded-circle p-3" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <i class="fas fa-file-alt fa-lg text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="h3 mb-1 fw-bold text-warning">{{ $documents->where('status', 'draft')->count() }}</div>
                            <div class="text-muted small">Draft</div>
                        </div>
                        <div class="rounded-circle p-3" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <i class="fas fa-edit fa-lg text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="h3 mb-1 fw-bold text-success">{{ $documents->where('status', 'final')->count() }}</div>
                            <div class="text-muted small">Final</div>
                        </div>
                        <div class="rounded-circle p-3" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                            <i class="fas fa-check-circle fa-lg text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="h3 mb-1 fw-bold text-info">{{ $documents->sum(function($doc) { return $doc->deliveries ? $doc->deliveries->where('status', 'sent')->count() : 0; }) }}</div>
                            <div class="text-muted small">Sent</div>
                        </div>
                        <div class="rounded-circle p-3" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                            <i class="fas fa-paper-plane fa-lg text-white"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-light border-0 py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-filter me-2 text-primary"></i>Filters
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('admin.patients.documents.index', $patient) }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Quick Search</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text"
                                   name="search"
                                   class="form-control border-start-0"
                                   placeholder="Search documents..."
                                   value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="letter" {{ request('type') == 'letter' ? 'selected' : '' }}>Letter</option>
                            <option value="form" {{ request('type') == 'form' ? 'selected' : '' }}>Form</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">Status</label>
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>Final</option>
                            <option value="void" {{ request('status') == 'void' ? 'selected' : '' }}>Void</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Template</label>
                        <select name="template_id" class="form-select">
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
    <div class="card border-0 shadow-sm mb-3 border-start border-4 border-primary" id="bulkActionsToolbar" style="display: none;">
        <div class="card-body py-3">
            <form action="{{ route('admin.patients.documents.bulk-action', $patient) }}" method="POST" id="bulkActionForm" onsubmit="return confirmBulkAction()">
                @csrf
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <div class="fw-semibold">
                        <i class="fas fa-check-square me-2 text-primary"></i>
                        <span id="selectedCount">0</span> document(s) selected
                    </div>
                    <div class="vr d-none d-md-block"></div>
                    <div class="d-flex gap-2 flex-wrap">
                        <select class="form-select form-select-sm" id="bulkAction" name="action" style="width: auto;" required>
                            <option value="">Select Action</option>
                            <option value="finalise">Finalise</option>
                            <option value="void">Void</option>
                            <option value="delete">Delete</option>
                        </select>
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-check me-1"></i>Apply
                        </button>
                        <button type="button" class="btn btn-sm btn-success" id="bulkSendBtn">
                            <i class="fas fa-paper-plane me-1"></i>Bulk Send
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

    <!-- Documents Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light border-0 py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-list me-2 text-primary"></i>Documents ({{ $documents->total() }})
            </h5>
        </div>
        <div class="card-body p-0">
            @if($documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" id="documentsTable">
                        <thead class="table-light">
                            <tr>
                                <th width="50" class="ps-3">
                                    <input type="checkbox" id="selectAll" class="form-check-input">
                                </th>
                                <th>Document</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Signature</th>
                                <th>Created</th>
                                <th class="text-end pe-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $document)
                            <tr>
                                <td class="ps-3">
                                    <input type="checkbox"
                                           class="form-check-input document-checkbox"
                                           value="{{ $document->id }}"
                                           data-title="{{ $document->title }}"
                                           data-status="{{ $document->status }}"
                                           data-has-pdf="{{ $document->pdf_path ? '1' : '0' }}">
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle p-2 me-3" style="background: {{ $document->type === 'letter' ? 'rgba(102, 126, 234, 0.1)' : 'rgba(17, 153, 142, 0.1)' }};">
                                            <i class="fas fa-{{ $document->type === 'letter' ? 'envelope' : 'clipboard-list' }} {{ $document->type === 'letter' ? 'text-primary' : 'text-success' }}"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('admin.patients.documents.show', [$patient, $document]) }}" class="fw-bold text-decoration-none text-dark">
                                                {{ Str::limit($document->title, 35) }}
                                            </a>
                                            @if($document->template)
                                                <br><small class="text-muted">{{ $document->template->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $document->type === 'letter' ? 'primary' : 'success' }} bg-opacity-75">
                                        {{ ucfirst($document->type) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'draft' => 'warning',
                                            'final' => 'success',
                                            'void' => 'secondary'
                                        ];
                                        $statusIcons = [
                                            'draft' => 'edit',
                                            'final' => 'check-circle',
                                            'void' => 'ban'
                                        ];
                                    @endphp
                                    <span class="badge bg-{{ $statusColors[$document->status] ?? 'secondary' }}">
                                        <i class="fas fa-{{ $statusIcons[$document->status] ?? 'file' }} me-1"></i>
                                        {{ ucfirst($document->status) }}
                                    </span>
                                </td>
                                <td>
                                    @if($document->signed_by_patient)
                                        <span class="badge bg-success">
                                            <i class="fas fa-signature me-1"></i>Signed
                                        </span>
                                        @if($document->signed_at)
                                            <br><small class="text-muted">{{ $document->signed_at->format('M d, Y') }}</small>
                                        @endif
                                    @elseif($document->isFinal())
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-clock me-1"></i>Awaiting
                                        </span>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium small">{{ $document->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $document->created_at->format('h:i A') }}</small>
                                    @if($document->creator)
                                        <br><small class="text-muted">by {{ $document->creator->name }}</small>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <a href="{{ route('admin.patients.documents.show', [$patient, $document]) }}"
                                           class="btn btn-outline-primary"
                                           title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @can('update', $document)
                                        @if($document->isDraft())
                                            <a href="{{ route('admin.patients.documents.edit', [$patient, $document]) }}"
                                               class="btn btn-outline-secondary"
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
                                                  onsubmit="return confirm('Are you sure you want to finalise this document?');">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-success" title="Finalise">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        @endcan
                                        @can('download', $document)
                                        @if($document->isFinal() && $document->pdf_path)
                                            <a href="{{ route('admin.patients.documents.download', [$patient, $document]) }}"
                                               class="btn btn-outline-info"
                                               title="Download PDF">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @endif
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer bg-light border-0 d-flex justify-content-between align-items-center">
                    <div class="text-muted small">
                        Showing {{ $documents->firstItem() }} to {{ $documents->lastItem() }}
                        of {{ $documents->total() }} documents
                    </div>
                    {{ $documents->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <div class="rounded-circle mx-auto mb-4 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-file-alt fa-2x text-white"></i>
                    </div>
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

<!-- Bulk Send Modal -->
<div class="modal fade" id="bulkSendModal" tabindex="-1" aria-labelledby="bulkSendModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h5 class="modal-title text-white" id="bulkSendModalLabel">
                    <i class="fas fa-paper-plane me-2"></i>Bulk Send Documents
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="bulkSendForm" method="POST">
                @csrf
                <div class="modal-body">
                    <div id="bulkSendError" class="alert alert-danger" style="display: none;"></div>

                    <!-- Selected Documents -->
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3">
                            <i class="fas fa-file-alt me-2 text-primary"></i>Selected Documents (<span id="bulkSendDocCount">0</span>)
                        </h6>
                        <div id="selectedDocumentsList" class="border rounded p-3" style="max-height: 150px; overflow-y: auto;">
                            <!-- Documents will be listed here -->
                        </div>
                        <small class="text-muted">Only finalized documents with PDFs can be sent.</small>
                    </div>

                    <!-- Recipient Selection -->
                    <div class="mb-4">
                        <h6 class="fw-semibold mb-3">
                            <i class="fas fa-user me-2 text-primary"></i>Recipient
                        </h6>
                        <div class="row g-3">
                            <div class="col-12">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="recipient_type" id="recipientPatient" value="patient" checked>
                                    <label class="form-check-label" for="recipientPatient">
                                        Send to Patient
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="recipient_type" id="recipientThirdParty" value="third_party">
                                    <label class="form-check-label" for="recipientThirdParty">
                                        Send to Third Party
                                    </label>
                                </div>
                            </div>

                            <!-- Patient Info -->
                            <div id="patientRecipientInfo" class="col-12">
                                <div class="alert alert-info mb-0">
                                    <i class="fas fa-user me-2"></i>
                                    <strong>{{ $patient->full_name }}</strong><br>
                                    <small>{{ $patient->email ?? 'No email on file' }}</small>
                                </div>
                            </div>

                            <!-- Third Party Fields -->
                            <div id="thirdPartyFields" class="col-12" style="display: none;">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Recipient Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" name="recipient_name" placeholder="Full name">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Recipient Email <span class="text-danger">*</span></label>
                                        <input type="email" class="form-control" name="recipient_email_custom" placeholder="email@example.com">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Email Options -->
                    <div class="mb-3">
                        <h6 class="fw-semibold mb-3">
                            <i class="fas fa-envelope me-2 text-primary"></i>Email Options
                        </h6>
                        <div class="mb-3">
                            <label class="form-label">Custom Message (optional)</label>
                            <textarea class="form-control" name="custom_message" rows="3" placeholder="Add a personal message to include with the documents..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 bg-light">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="sendBulkBtn">
                        <i class="fas fa-paper-plane me-2"></i>Send Documents
                    </button>
                </div>
                <input type="hidden" name="document_ids" id="bulkSendDocumentIds">
                <input type="hidden" name="recipient_email" id="bulkSendRecipientEmail" value="{{ $patient->email }}">
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
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
            bulkActionsToolbar.slideDown();
            selectedCount.text(count);

            const ids = checked.map(function() {
                return $(this).val();
            }).get();
            bulkDocumentIds.val(JSON.stringify(ids));
        } else {
            bulkActionsToolbar.slideUp();
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

    // Bulk Send Modal
    $('#bulkSendBtn').on('click', function() {
        const checked = $('.document-checkbox:checked');
        const sendableDocuments = [];
        const nonSendable = [];

        checked.each(function() {
            const $this = $(this);
            const status = $this.data('status');
            const hasPdf = $this.data('has-pdf') === '1' || $this.data('has-pdf') === 1;
            const title = $this.data('title');
            const id = $this.val();

            if (status === 'final' && hasPdf) {
                sendableDocuments.push({ id: id, title: title });
            } else {
                nonSendable.push(title);
            }
        });

        if (sendableDocuments.length === 0) {
            alert('No sendable documents selected. Only finalized documents with PDFs can be sent.');
            return;
        }

        // Update modal
        $('#bulkSendDocCount').text(sendableDocuments.length);

        let docListHtml = '';
        sendableDocuments.forEach(doc => {
            docListHtml += `<div class="d-flex align-items-center py-1 border-bottom">
                <i class="fas fa-file-pdf text-danger me-2"></i>
                <span>${doc.title}</span>
            </div>`;
        });

        if (nonSendable.length > 0) {
            docListHtml += `<div class="mt-2 text-warning small">
                <i class="fas fa-exclamation-triangle me-1"></i>
                ${nonSendable.length} document(s) skipped (not finalized or no PDF)
            </div>`;
        }

        $('#selectedDocumentsList').html(docListHtml);
        $('#bulkSendDocumentIds').val(JSON.stringify(sendableDocuments.map(d => d.id)));

        // Show modal
        const modal = new bootstrap.Modal(document.getElementById('bulkSendModal'));
        modal.show();
    });

    // Toggle recipient fields
    $('input[name="recipient_type"]').on('change', function() {
        if ($(this).val() === 'patient') {
            $('#patientRecipientInfo').show();
            $('#thirdPartyFields').hide();
            $('#bulkSendRecipientEmail').val('{{ $patient->email }}');
        } else {
            $('#patientRecipientInfo').hide();
            $('#thirdPartyFields').show();
        }
    });

    // Handle bulk send form submission
    $('#bulkSendForm').on('submit', function(e) {
        e.preventDefault();

        const $form = $(this);
        const $submitBtn = $('#sendBulkBtn');
        const $error = $('#bulkSendError');

        // Validate
        const recipientType = $('input[name="recipient_type"]:checked').val();
        let recipientEmail = '';

        if (recipientType === 'patient') {
            recipientEmail = '{{ $patient->email }}';
            if (!recipientEmail) {
                $error.text('Patient does not have an email address on file.').show();
                return;
            }
        } else {
            recipientEmail = $('input[name="recipient_email_custom"]').val();
            const recipientName = $('input[name="recipient_name"]').val();

            if (!recipientEmail || !recipientName) {
                $error.text('Please enter recipient name and email.').show();
                return;
            }
        }

        $error.hide();
        $submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Sending...');

        // Get document IDs
        const documentIds = JSON.parse($('#bulkSendDocumentIds').val() || '[]');

        // Send each document
        let sentCount = 0;
        let errorCount = 0;
        const totalDocs = documentIds.length;

        documentIds.forEach((docId, index) => {
            $.ajax({
                url: `/admin/patients/{{ $patient->id }}/documents/${docId}/deliveries`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    recipient_type: recipientType,
                    recipient_name: recipientType === 'patient' ? '{{ $patient->full_name }}' : $('input[name="recipient_name"]').val(),
                    recipient_email: recipientEmail,
                    channel: 'email'
                },
                success: function() {
                    sentCount++;
                    checkComplete();
                },
                error: function() {
                    errorCount++;
                    checkComplete();
                }
            });
        });

        function checkComplete() {
            if (sentCount + errorCount === totalDocs) {
                $submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane me-2"></i>Send Documents');

                if (errorCount === 0) {
                    alert(`Successfully sent ${sentCount} document(s)!`);
                    location.reload();
                } else {
                    alert(`Sent ${sentCount} document(s). ${errorCount} failed.`);
                    location.reload();
                }
            }
        }
    });

    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert:not(.alert-info)').fadeOut();
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
