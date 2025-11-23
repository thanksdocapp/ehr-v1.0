@extends('admin.layouts.app')

@section('title', $document->title . ' - ' . $patient->full_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.index') }}">Patients</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.show', $patient) }}">{{ $patient->full_name }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.patients.documents.index', $patient) }}">Letters & Forms</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($document->title, 30) }}</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-file-alt me-2 text-primary"></i>{{ $document->title }}
            </h1>
            <p class="text-muted mb-0">Patient: <strong>{{ $patient->full_name }}</strong> ({{ $patient->patient_id ?? 'N/A' }})</p>
        </div>
        <div class="btn-group">
            @can('update', $document)
            @if($document->isDraft())
                <a href="{{ route('admin.patients.documents.edit', [$patient, $document]) }}" class="btn btn-warning">
                    <i class="fas fa-edit me-2"></i>Edit
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
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check me-2"></i>Finalise
                    </button>
                </form>
            @endif
            @endcan
            @can('download', $document)
            @if($document->isFinal() && $document->pdf_path)
                <a href="{{ route('admin.patients.documents.download', [$patient, $document]) }}" class="btn btn-info">
                    <i class="fas fa-download me-2"></i>Download PDF
                </a>
            @endif
            @endcan
            @can('send', $document)
            @if($document->isFinal())
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#sendDocumentModal">
                    <i class="fas fa-paper-plane me-2"></i>Send
                </button>
            @endif
            @endcan
            <a href="{{ route('admin.patients.documents.index', $patient) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
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
            <!-- Document Details -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-file-alt me-2"></i>Document Details
                        </h5>
                        <div>
                            @php
                                $statusColors = ['draft' => 'warning', 'final' => 'success', 'void' => 'danger'];
                                $statusColor = $statusColors[$document->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }} fs-6">{{ ucfirst($document->status) }}</span>
                            <span class="badge bg-{{ $document->type === 'letter' ? 'primary' : 'info' }} fs-6">
                                {{ ucfirst($document->type) }}
                            </span>
                        </div>
                    </div>
                </div>
                <div class="doctor-card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Document ID</label>
                            <div class="fw-bold text-primary">#{{ str_pad($document->id, 6, '0', STR_PAD_LEFT) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Created</label>
                            <div class="fw-bold">{{ $document->created_at->format('M d, Y') }}</div>
                            <small class="text-muted">{{ $document->created_at->format('h:i A') }}</small>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Created By</label>
                            <div class="fw-bold">
                                @if($document->creator)
                                    {{ $document->creator->name }}
                                    <small class="text-muted">({{ ucfirst($document->creator->role) }})</small>
                                @else
                                    <span class="text-muted">Unknown</span>
                                @endif
                            </div>
                        </div>
                        @if($document->template)
                        <div class="col-md-6">
                            <label class="form-label text-muted">Template</label>
                            <div class="fw-bold">{{ $document->template->name }}</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Document Content -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-{{ $document->type === 'letter' ? 'file-alt' : 'list' }} me-2"></i>
                        {{ $document->type === 'letter' ? 'Letter Content' : 'Form Data' }}
                    </h5>
                </div>
                <div class="doctor-card-body">
                    @if($document->type === 'letter')
                        @if($document->content)
                            <div class="document-content" style="line-height: 1.8; font-family: 'Times New Roman', serif;">
                                {!! $document->content !!}
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Document content is not available.
                            </div>
                        @endif
                    @else
                        @if($document->form_data && is_array($document->form_data))
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Field</th>
                                            <th>Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($document->form_data as $field => $value)
                                        <tr>
                                            <td class="fw-bold">{{ ucwords(str_replace('_', ' ', $field)) }}</td>
                                            <td>{{ is_bool($value) ? ($value ? 'Yes' : 'No') : $value }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Form data is not available yet.
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            <!-- Deliveries History -->
            @if($document->deliveries && $document->deliveries->count() > 0)
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-paper-plane me-2"></i>Delivery History
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Sent To</th>
                                    <th>Channel</th>
                                    <th>Status</th>
                                    <th>Sent At</th>
                                    <th>Sent By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($document->deliveries as $delivery)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $delivery->recipient_name }}</div>
                                        <small class="text-muted">{{ $delivery->recipient_email }}</small>
                                    </td>
                                    <td><span class="badge bg-info">{{ ucfirst($delivery->channel) }}</span></td>
                                    <td>
                                        @php
                                            $deliveryStatusColors = ['pending' => 'warning', 'sent' => 'success', 'failed' => 'danger'];
                                            $deliveryStatusColor = $deliveryStatusColors[$delivery->status] ?? 'secondary';
                                        @endphp
                                        <span class="badge bg-{{ $deliveryStatusColor }}">{{ ucfirst($delivery->status) }}</span>
                                    </td>
                                    <td>
                                        @if($delivery->sent_at)
                                            <div class="fw-bold">{{ $delivery->sent_at->format('M d, Y') }}</div>
                                            <small class="text-muted">{{ $delivery->sent_at->format('h:i A') }}</small>
                                        @else
                                            <span class="text-muted">Not sent</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $delivery->sender ? $delivery->sender->name : 'Unknown' }}
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="d-grid gap-2">
                        @can('update', $document)
                        @if($document->isDraft())
                            <a href="{{ route('admin.patients.documents.edit', [$patient, $document]) }}" class="btn btn-warning w-100">
                                <i class="fas fa-edit me-2"></i>Edit Document
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
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check me-2"></i>Finalise Document
                                </button>
                            </form>
                        @endif
                        @endcan
                        
                        @can('download', $document)
                        @if($document->isFinal() && $document->pdf_path)
                            <a href="{{ route('admin.patients.documents.download', [$patient, $document]) }}" class="btn btn-info w-100">
                                <i class="fas fa-download me-2"></i>Download PDF
                            </a>
                        @endif
                        @endcan
                        
                        @can('send', $document)
                        @if($document->isFinal())
                            <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#sendDocumentModal">
                                <i class="fas fa-paper-plane me-2"></i>Send Document
                            </button>
                        @endif
                        @endcan
                        
                        <a href="{{ route('admin.patients.documents.index', $patient) }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-list me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Document Information -->
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-info-circle me-2"></i>Document Information</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="mb-3">
                        <label class="form-label text-muted">Title</label>
                        <div class="fw-bold">{{ $document->title }}</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Type</label>
                        <div>
                            <span class="badge bg-{{ $document->type === 'letter' ? 'primary' : 'info' }}">
                                {{ ucfirst($document->type) }}
                            </span>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted">Status</label>
                        <div>
                            @php
                                $statusColors = ['draft' => 'warning', 'final' => 'success', 'void' => 'danger'];
                                $statusColor = $statusColors[$document->status] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $statusColor }}">{{ ucfirst($document->status) }}</span>
                        </div>
                    </div>
                    @if($document->template)
                    <div class="mb-3">
                        <label class="form-label text-muted">Template</label>
                        <div class="fw-bold">{{ $document->template->name }}</div>
                    </div>
                    @endif
                    @if($document->pdf_path)
                    <div class="mb-3">
                        <label class="form-label text-muted">PDF</label>
                        <div>
                            <span class="badge bg-success">
                                <i class="fas fa-file-pdf me-1"></i>Available
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Send Document Modal -->
@can('send', $document)
@if($document->isFinal())
<div class="modal fade" id="sendDocumentModal" tabindex="-1" aria-labelledby="sendDocumentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="sendDocumentModalLabel">
                    <i class="fas fa-paper-plane me-2"></i>Send Document
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.patients.documents.deliveries.store', [$patient, $document]) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="recipient_type" class="form-label">Recipient Type <span class="text-danger">*</span></label>
                        <select class="form-control" id="recipient_type" name="recipient_type" required>
                            <option value="patient">Patient</option>
                            <option value="third_party">Third Party</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="recipient_name" class="form-label">Recipient Name</label>
                        <input type="text" class="form-control" id="recipient_name" name="recipient_name" 
                               value="{{ $patient->full_name }}" placeholder="Recipient name">
                    </div>
                    
                    <div class="mb-3">
                        <label for="recipient_email" class="form-label">Recipient Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="recipient_email" name="recipient_email" 
                               value="{{ $patient->email }}" required placeholder="recipient@example.com">
                    </div>
                    
                    <div class="mb-3">
                        <label for="recipient_phone" class="form-label">Recipient Phone</label>
                        <input type="text" class="form-control" id="recipient_phone" name="recipient_phone" 
                               value="{{ $patient->phone }}" placeholder="Phone number">
                    </div>
                    
                    <div class="mb-3">
                        <label for="channel" class="form-label">Delivery Channel <span class="text-danger">*</span></label>
                        <select class="form-control" id="channel" name="channel" required>
                            <option value="email" selected>Email</option>
                            <option value="portal">Patient Portal</option>
                            <option value="print">Print (Manual)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
    
    $('#recipient_type').on('change', function() {
        const type = $(this).val();
        if (type === 'patient') {
            $('#recipient_name').val('{{ $patient->full_name }}');
            $('#recipient_email').val('{{ $patient->email }}');
            $('#recipient_phone').val('{{ $patient->phone }}');
        } else {
            $('#recipient_name').val('');
            $('#recipient_email').val('');
            $('#recipient_phone').val('');
        }
    });
});
</script>
@endpush

