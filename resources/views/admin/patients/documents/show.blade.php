@extends('admin.layouts.app')

@section('title', $document->title . ' - ' . $patient->full_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.index') }}">Patients</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.show', $patient) }}">{{ $patient->full_name }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.documents.index', $patient) }}">Documents</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($document->title, 30) }}</li>
@endsection

@push('styles')
<style>
    .document-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 20px;
        padding: 2rem;
        color: white;
        margin-bottom: 1.5rem;
    }
    .document-header.form-type {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }
    .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.5rem 1rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.875rem;
    }
    .status-badge.draft { background: rgba(255,193,7,0.2); color: #856404; }
    .status-badge.final { background: rgba(40,167,69,0.2); color: #155724; }
    .status-badge.void { background: rgba(220,53,69,0.2); color: #721c24; }
    .action-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }
    .action-card .card-header {
        background: #f8f9fa;
        border-bottom: 1px solid #eee;
        padding: 1rem 1.25rem;
        font-weight: 600;
    }
    .action-btn {
        display: flex;
        align-items: center;
        padding: 0.875rem 1rem;
        border-radius: 12px;
        text-decoration: none;
        color: #333;
        transition: all 0.2s ease;
        margin-bottom: 0.5rem;
        background: #f8f9fa;
    }
    .action-btn:hover {
        background: #e9ecef;
        transform: translateX(4px);
        color: #333;
    }
    .action-btn i {
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        margin-right: 0.75rem;
        font-size: 0.875rem;
    }
    .action-btn.edit i { background: rgba(255,193,7,0.15); color: #d39e00; }
    .action-btn.finalise i { background: rgba(40,167,69,0.15); color: #28a745; }
    .action-btn.download i { background: rgba(23,162,184,0.15); color: #17a2b8; }
    .action-btn.send i { background: rgba(0,123,255,0.15); color: #007bff; }
    .action-btn.signature i { background: rgba(111,66,193,0.15); color: #6f42c1; }
    .action-btn.void i { background: rgba(220,53,69,0.15); color: #dc3545; }
    .document-content-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }
    .document-preview {
        background: white;
        border: 1px solid #e9ecef;
        border-radius: 12px;
        padding: 2rem;
        min-height: 400px;
        max-height: 600px;
        overflow-y: auto;
    }
    .document-preview.letter {
        font-family: 'Times New Roman', Georgia, serif;
        line-height: 1.8;
    }
    .info-row {
        display: flex;
        align-items: center;
        padding: 0.75rem 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .info-row:last-child {
        border-bottom: none;
    }
    .info-row .label {
        width: 140px;
        color: #666;
        font-size: 0.875rem;
    }
    .info-row .value {
        flex: 1;
        font-weight: 500;
    }
    .delivery-timeline {
        position: relative;
        padding-left: 2rem;
    }
    .delivery-timeline::before {
        content: '';
        position: absolute;
        left: 8px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e9ecef;
    }
    .delivery-item {
        position: relative;
        padding-bottom: 1.5rem;
    }
    .delivery-item::before {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 4px;
        width: 12px;
        height: 12px;
        border-radius: 50%;
        background: #007bff;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #007bff;
    }
    .delivery-item.sent::before { background: #28a745; box-shadow: 0 0 0 2px #28a745; }
    .delivery-item.failed::before { background: #dc3545; box-shadow: 0 0 0 2px #dc3545; }
    .delivery-item.pending::before { background: #ffc107; box-shadow: 0 0 0 2px #ffc107; }
    .signature-box {
        border: 2px dashed #dee2e6;
        border-radius: 12px;
        padding: 2rem;
        text-align: center;
        background: #f8f9fa;
    }
    .signature-box.signed {
        border-color: #28a745;
        background: rgba(40,167,69,0.05);
    }
    .signature-canvas {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        background: white;
        cursor: crosshair;
    }
    .tab-content-modern {
        padding: 1.5rem;
    }
    .nav-tabs-modern {
        border-bottom: 2px solid #e9ecef;
    }
    .nav-tabs-modern .nav-link {
        border: none;
        color: #666;
        padding: 1rem 1.5rem;
        font-weight: 500;
        position: relative;
    }
    .nav-tabs-modern .nav-link.active {
        color: #007bff;
        background: transparent;
    }
    .nav-tabs-modern .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: #007bff;
    }
</style>
@endpush

@section('content')
<div class="fade-in">
    <!-- Document Header -->
    <div class="document-header {{ $document->type === 'form' ? 'form-type' : '' }}">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <div class="d-flex align-items-center mb-2">
                    <span class="badge bg-white text-dark me-2">
                        <i class="fas fa-{{ $document->type === 'letter' ? 'envelope-open-text' : 'clipboard-list' }} me-1"></i>
                        {{ ucfirst($document->type) }}
                    </span>
                    @php
                        $statusLabels = ['draft' => 'Draft', 'final' => 'Finalized', 'void' => 'Voided'];
                        $statusIcons = ['draft' => 'edit', 'final' => 'check-circle', 'void' => 'ban'];
                    @endphp
                    <span class="status-badge {{ $document->status }}">
                        <i class="fas fa-{{ $statusIcons[$document->status] ?? 'circle' }} me-1"></i>
                        {{ $statusLabels[$document->status] ?? ucfirst($document->status) }}
                    </span>
                </div>
                <h2 class="mb-1">{{ $document->title }}</h2>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-user me-1"></i>{{ $patient->full_name }}
                    @if($patient->patient_id)
                        <span class="mx-2">|</span>
                        <i class="fas fa-id-card me-1"></i>{{ $patient->patient_id }}
                    @endif
                </p>
            </div>
            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                <a href="{{ route('admin.patients.documents.index', $patient) }}" class="btn btn-light">
                    <i class="fas fa-arrow-left me-2"></i>Back to Documents
                </a>
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

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Document Content Card -->
            <div class="card document-content-card mb-4">
                <div class="card-body p-0">
                    <!-- Tabs -->
                    <ul class="nav nav-tabs-modern" id="documentTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="preview-tab" data-bs-toggle="tab" data-bs-target="#preview" type="button" role="tab">
                                <i class="fas fa-eye me-2"></i>Preview
                            </button>
                        </li>
                        @if($document->type === 'form' && $document->form_data)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="data-tab" data-bs-toggle="tab" data-bs-target="#data" type="button" role="tab">
                                <i class="fas fa-database me-2"></i>Form Data
                            </button>
                        </li>
                        @endif
                        @if($document->deliveries && $document->deliveries->count() > 0)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="deliveries-tab" data-bs-toggle="tab" data-bs-target="#deliveries" type="button" role="tab">
                                <i class="fas fa-paper-plane me-2"></i>Deliveries
                                <span class="badge bg-primary ms-1">{{ $document->deliveries->count() }}</span>
                            </button>
                        </li>
                        @endif
                    </ul>

                    <div class="tab-content tab-content-modern" id="documentTabsContent">
                        <!-- Preview Tab -->
                        <div class="tab-pane fade show active" id="preview" role="tabpanel">
                            @if($document->type === 'letter')
                                @if($document->content)
                                    <div class="document-preview letter">
                                        {!! $document->content !!}
                                    </div>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        Document content is not available.
                                    </div>
                                @endif
                            @else
                                @if($document->form_data && is_array($document->form_data))
                                    <div class="document-preview">
                                        <div class="table-responsive">
                                            <table class="table table-bordered">
                                                <tbody>
                                                    @foreach($document->form_data as $field => $value)
                                                    <tr>
                                                        <th class="bg-light" style="width: 200px;">{{ ucwords(str_replace('_', ' ', $field)) }}</th>
                                                        <td>
                                                            @if(is_bool($value))
                                                                <i class="fas fa-{{ $value ? 'check-circle text-success' : 'times-circle text-danger' }}"></i>
                                                                {{ $value ? 'Yes' : 'No' }}
                                                            @elseif(is_array($value))
                                                                {{ implode(', ', $value) }}
                                                            @else
                                                                {{ $value ?: '-' }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info mb-0">
                                        <i class="fas fa-info-circle me-2"></i>
                                        Form data is not available yet.
                                    </div>
                                @endif
                            @endif
                        </div>

                        <!-- Form Data Tab -->
                        @if($document->type === 'form' && $document->form_data)
                        <div class="tab-pane fade" id="data" role="tabpanel">
                            <pre class="bg-light p-3 rounded" style="max-height: 500px; overflow: auto;"><code>{{ json_encode($document->form_data, JSON_PRETTY_PRINT) }}</code></pre>
                        </div>
                        @endif

                        <!-- Deliveries Tab -->
                        @if($document->deliveries && $document->deliveries->count() > 0)
                        <div class="tab-pane fade" id="deliveries" role="tabpanel">
                            <div class="delivery-timeline">
                                @foreach($document->deliveries->sortByDesc('created_at') as $delivery)
                                <div class="delivery-item {{ $delivery->status }}">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <div class="fw-bold">{{ $delivery->recipient_name }}</div>
                                            <div class="text-muted small">{{ $delivery->recipient_email }}</div>
                                            <div class="mt-2">
                                                <span class="badge bg-{{ $delivery->status === 'sent' ? 'success' : ($delivery->status === 'failed' ? 'danger' : 'warning') }}">
                                                    {{ ucfirst($delivery->status) }}
                                                </span>
                                                <span class="badge bg-secondary">{{ ucfirst($delivery->channel) }}</span>
                                                <span class="badge bg-info">{{ ucfirst($delivery->recipient_type) }}</span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="small text-muted">
                                                @if($delivery->sent_at)
                                                    {{ $delivery->sent_at->format('M d, Y') }}<br>
                                                    {{ $delivery->sent_at->format('h:i A') }}
                                                @else
                                                    {{ $delivery->created_at->format('M d, Y') }}<br>
                                                    {{ $delivery->created_at->format('h:i A') }}
                                                @endif
                                            </div>
                                            @if($delivery->sender)
                                                <div class="small text-muted">by {{ $delivery->sender->name }}</div>
                                            @endif
                                            @if($delivery->opened_at)
                                                <div class="small text-success mt-1">
                                                    <i class="fas fa-eye me-1"></i>Opened {{ $delivery->opened_at->diffForHumans() }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Patient Signature Section -->
            <div class="card action-card mb-4">
                <div class="card-header">
                    <i class="fas fa-signature me-2"></i>Patient Signature
                </div>
                <div class="card-body">
                    @if($document->signed_by_patient)
                        <div class="signature-box signed">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h5 class="mb-2">Document Signed</h5>
                            <p class="text-muted mb-0">
                                Signed by {{ $patient->full_name }} on {{ $document->signed_at ? $document->signed_at->format('F d, Y \a\t h:i A') : 'Unknown date' }}
                            </p>
                        </div>
                    @else
                        @if($document->isFinal())
                        <div class="signature-box">
                            <i class="fas fa-pen-fancy fa-3x text-muted mb-3"></i>
                            <h5 class="mb-2">Awaiting Patient Signature</h5>
                            <p class="text-muted mb-3">
                                This document requires the patient's signature. You can request the signature via email or collect it in person.
                            </p>
                            <div class="d-flex gap-2 justify-content-center flex-wrap">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestSignatureModal">
                                    <i class="fas fa-envelope me-2"></i>Request via Email
                                </button>
                                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#collectSignatureModal">
                                    <i class="fas fa-pen me-2"></i>Collect Now
                                </button>
                            </div>
                        </div>
                        @else
                        <div class="alert alert-info mb-0">
                            <i class="fas fa-info-circle me-2"></i>
                            Signatures can only be collected on finalized documents.
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card action-card mb-4">
                <div class="card-header">
                    <i class="fas fa-bolt me-2"></i>Quick Actions
                </div>
                <div class="card-body">
                    @can('update', $document)
                    @if($document->isDraft())
                        <a href="{{ route('admin.patients.documents.edit', [$patient, $document]) }}" class="action-btn edit">
                            <i class="fas fa-edit"></i>
                            <div>
                                <div class="fw-semibold">Edit Document</div>
                                <small class="text-muted">Modify content or form data</small>
                            </div>
                        </a>
                    @endif
                    @endcan

                    @can('finalise', $document)
                    @if($document->isDraft())
                        <form action="{{ route('admin.patients.documents.finalise', [$patient, $document]) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="action-btn finalise w-100 border-0" onclick="return confirm('Finalise this document? This action cannot be undone.')">
                                <i class="fas fa-check"></i>
                                <div>
                                    <div class="fw-semibold">Finalise Document</div>
                                    <small class="text-muted">Generate PDF and lock</small>
                                </div>
                            </button>
                        </form>
                    @endif
                    @endcan

                    @can('download', $document)
                    @if($document->isFinal() && $document->pdf_path)
                        <a href="{{ route('admin.patients.documents.download', [$patient, $document]) }}" class="action-btn download">
                            <i class="fas fa-download"></i>
                            <div>
                                <div class="fw-semibold">Download PDF</div>
                                <small class="text-muted">Get the document file</small>
                            </div>
                        </a>
                    @endif
                    @endcan

                    @can('send', $document)
                    @if($document->isFinal())
                        <a href="#" class="action-btn send" data-bs-toggle="modal" data-bs-target="#sendDocumentModal">
                            <i class="fas fa-paper-plane"></i>
                            <div>
                                <div class="fw-semibold">Send Document</div>
                                <small class="text-muted">Email to patient or third party</small>
                            </div>
                        </a>
                    @endif
                    @endcan

                    @can('void', $document)
                    @if(!$document->isVoid())
                        <form action="{{ route('admin.patients.documents.void', [$patient, $document]) }}" method="POST">
                            @csrf
                            <button type="submit" class="action-btn void w-100 border-0" onclick="return confirm('Void this document? This action cannot be undone.')">
                                <i class="fas fa-ban"></i>
                                <div>
                                    <div class="fw-semibold">Void Document</div>
                                    <small class="text-muted">Cancel this document</small>
                                </div>
                            </button>
                        </form>
                    @endif
                    @endcan
                </div>
            </div>

            <!-- Document Details -->
            <div class="card action-card mb-4">
                <div class="card-header">
                    <i class="fas fa-info-circle me-2"></i>Document Details
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="label">Document ID</span>
                        <span class="value text-primary">#{{ str_pad($document->id, 6, '0', STR_PAD_LEFT) }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Type</span>
                        <span class="value">
                            <span class="badge bg-{{ $document->type === 'letter' ? 'primary' : 'success' }}">
                                {{ ucfirst($document->type) }}
                            </span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">Status</span>
                        <span class="value">
                            @php
                                $statusColors = ['draft' => 'warning', 'final' => 'success', 'void' => 'danger'];
                            @endphp
                            <span class="badge bg-{{ $statusColors[$document->status] ?? 'secondary' }}">
                                {{ ucfirst($document->status) }}
                            </span>
                        </span>
                    </div>
                    @if($document->template)
                    <div class="info-row">
                        <span class="label">Template</span>
                        <span class="value">{{ $document->template->name }}</span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="label">Created</span>
                        <span class="value">{{ $document->created_at->format('M d, Y h:i A') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Created By</span>
                        <span class="value">{{ $document->creator->name ?? 'Unknown' }}</span>
                    </div>
                    @if($document->pdf_path)
                    <div class="info-row">
                        <span class="label">PDF</span>
                        <span class="value">
                            <span class="badge bg-success"><i class="fas fa-file-pdf me-1"></i>Available</span>
                        </span>
                    </div>
                    @endif
                    <div class="info-row">
                        <span class="label">Signature</span>
                        <span class="value">
                            @if($document->signed_by_patient)
                                <span class="badge bg-success"><i class="fas fa-check me-1"></i>Signed</span>
                            @else
                                <span class="badge bg-secondary"><i class="fas fa-clock me-1"></i>Pending</span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            <!-- Patient Info -->
            <div class="card action-card">
                <div class="card-header">
                    <i class="fas fa-user me-2"></i>Patient Information
                </div>
                <div class="card-body">
                    <div class="info-row">
                        <span class="label">Name</span>
                        <span class="value">{{ $patient->full_name }}</span>
                    </div>
                    @if($patient->patient_id)
                    <div class="info-row">
                        <span class="label">Patient ID</span>
                        <span class="value">{{ $patient->patient_id }}</span>
                    </div>
                    @endif
                    @if($patient->email)
                    <div class="info-row">
                        <span class="label">Email</span>
                        <span class="value">{{ $patient->email }}</span>
                    </div>
                    @endif
                    @if($patient->phone)
                    <div class="info-row">
                        <span class="label">Phone</span>
                        <span class="value">{{ $patient->phone }}</span>
                    </div>
                    @endif
                    <div class="mt-3">
                        <a href="{{ route('admin.patients.show', $patient) }}" class="btn btn-sm btn-outline-primary w-100">
                            <i class="fas fa-user me-2"></i>View Patient Profile
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send Document Modal -->
@can('send', $document)
@if($document->isFinal())
<div class="modal fade" id="sendDocumentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="fas fa-paper-plane me-2 text-primary"></i>Send Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.patients.documents.deliveries.store', [$patient, $document]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Recipient Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="recipient_type" name="recipient_type" required>
                                <option value="patient">Patient</option>
                                <option value="third_party">Third Party (GP, Specialist, etc.)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Delivery Channel <span class="text-danger">*</span></label>
                            <select class="form-select" name="channel" required>
                                <option value="email">Email</option>
                                <option value="portal">Patient Portal</option>
                                <option value="print">Print (Manual)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Recipient Name</label>
                            <input type="text" class="form-control" id="recipient_name" name="recipient_name"
                                   value="{{ $patient->full_name }}" placeholder="Recipient name">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Recipient Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="recipient_email" name="recipient_email"
                                   value="{{ $patient->email }}" required placeholder="email@example.com">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Recipient Phone</label>
                            <input type="text" class="form-control" id="recipient_phone" name="recipient_phone"
                                   value="{{ $patient->phone }}" placeholder="Phone number">
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Document
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endcan

<!-- Request Signature Modal -->
@if($document->isFinal() && !$document->signed_by_patient)
<div class="modal fade" id="requestSignatureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="fas fa-envelope me-2 text-primary"></i>Request Signature via Email
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.patients.documents.request-signature', [$patient, $document]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">
                        An email will be sent to the patient with a secure link to sign this document electronically.
                    </p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Patient Email</label>
                        <input type="email" class="form-control" name="email" value="{{ $patient->email }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Custom Message (Optional)</label>
                        <textarea class="form-control" name="message" rows="3" placeholder="Add a personal message to the email..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane me-2"></i>Send Request
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Collect Signature Modal -->
<div class="modal fade" id="collectSignatureModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 16px;">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title">
                    <i class="fas fa-pen me-2 text-primary"></i>Collect Patient Signature
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.patients.documents.sign', [$patient, $document]) }}" method="POST" id="signatureForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-4">
                        Have the patient sign in the box below using their finger or a stylus.
                    </p>
                    <div class="text-center mb-3">
                        <canvas id="signatureCanvas" class="signature-canvas" width="500" height="200"></canvas>
                    </div>
                    <input type="hidden" name="signature" id="signatureData">
                    <div class="d-flex justify-content-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="clearSignature">
                            <i class="fas fa-eraser me-1"></i>Clear
                        </button>
                    </div>
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="signatureConsent" required>
                        <label class="form-check-label" for="signatureConsent">
                            I, {{ $patient->full_name }}, confirm that I have read and agree to the contents of this document.
                        </label>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Confirm Signature
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);

    // Recipient type change handler
    $('#recipient_type').on('change', function() {
        if ($(this).val() === 'patient') {
            $('#recipient_name').val('{{ $patient->full_name }}');
            $('#recipient_email').val('{{ $patient->email }}');
            $('#recipient_phone').val('{{ $patient->phone }}');
        } else {
            $('#recipient_name').val('');
            $('#recipient_email').val('');
            $('#recipient_phone').val('');
        }
    });

    // Signature Canvas
    const canvas = document.getElementById('signatureCanvas');
    if (canvas) {
        const ctx = canvas.getContext('2d');
        let isDrawing = false;
        let lastX = 0;
        let lastY = 0;

        ctx.strokeStyle = '#000';
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.lineJoin = 'round';

        function getPosition(e) {
            const rect = canvas.getBoundingClientRect();
            const x = (e.clientX || (e.touches && e.touches[0].clientX)) - rect.left;
            const y = (e.clientY || (e.touches && e.touches[0].clientY)) - rect.top;
            return { x, y };
        }

        function startDrawing(e) {
            isDrawing = true;
            const pos = getPosition(e);
            lastX = pos.x;
            lastY = pos.y;
        }

        function draw(e) {
            if (!isDrawing) return;
            e.preventDefault();
            const pos = getPosition(e);
            ctx.beginPath();
            ctx.moveTo(lastX, lastY);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            lastX = pos.x;
            lastY = pos.y;
        }

        function stopDrawing() {
            isDrawing = false;
        }

        // Mouse events
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseout', stopDrawing);

        // Touch events
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);

        // Clear signature
        $('#clearSignature').on('click', function() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        });

        // Save signature on form submit
        $('#signatureForm').on('submit', function(e) {
            const dataURL = canvas.toDataURL('image/png');
            $('#signatureData').val(dataURL);

            // Check if canvas is empty
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const isEmpty = !imageData.data.some((channel, index) => {
                return index % 4 !== 3 ? channel !== 0 : channel !== 0 && channel !== 255;
            });

            if (isEmpty) {
                e.preventDefault();
                alert('Please provide a signature before submitting.');
                return false;
            }
        });
    }
});
</script>
@endpush
