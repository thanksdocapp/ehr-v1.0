@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'My Generated Documents')
@section('page-title', 'My Generated Documents')

@section('content')
<div class="fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">
                        <i class="fas fa-file-pdf me-2 text-danger"></i>My Generated Documents
                    </h1>
                    <p class="text-muted mb-0">Documents you have generated from templates</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('staff.templates.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-file-alt me-2"></i>Templates
                    </a>
                    <a href="{{ route('staff.generated-documents.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-2"></i>Generate New
                    </a>
                </div>
            </div>
        </div>
    </div>

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

    <!-- Filters -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-body">
            <form method="GET" action="{{ route('staff.generated-documents.index') }}">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label">Search</label>
                        <input type="text" name="search" class="form-control"
                               placeholder="Search by title..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="final" {{ request('status') == 'final' ? 'selected' : '' }}>Final</option>
                            <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-doctor-primary w-100">
                            <i class="fas fa-search me-1"></i>Filter
                        </button>
                    </div>
                    @if(request()->anyFilled(['search', 'status']))
                    <div class="col-md-2">
                        <a href="{{ route('staff.generated-documents.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Documents Table -->
    <div class="doctor-card">
        <div class="doctor-card-body">
            @if($documents->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Document</th>
                                <th>Patient</th>
                                <th>Template</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($documents as $document)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $document->title }}</div>
                                    <small class="text-muted">{{ $document->file_name }}</small>
                                </td>
                                <td>
                                    @if($document->patient)
                                        <a href="{{ route('staff.patients.show', $document->patient) }}">
                                            {{ $document->patient->full_name }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    @if($document->template)
                                        <span class="badge bg-{{ $document->template->type === 'letter' ? 'primary' : 'info' }}">
                                            {{ $document->template->name }}
                                        </span>
                                    @else
                                        <span class="text-muted">Unknown</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $document->status === 'sent' ? 'success' : ($document->status === 'final' ? 'primary' : 'secondary') }}">
                                        {{ ucfirst($document->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ $document->created_at->format('M d, Y') }}</div>
                                    <small class="text-muted">{{ $document->created_at->format('H:i') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('staff.generated-documents.show', $document) }}"
                                           class="btn btn-sm btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('staff.generated-documents.download', $document) }}"
                                           class="btn btn-sm btn-outline-success" title="Download">
                                            <i class="fas fa-download"></i>
                                        </a>
                                        @if($document->status === 'final')
                                        <a href="{{ route('staff.generated-documents.send-form', $document) }}"
                                           class="btn btn-sm btn-outline-info" title="Send">
                                            <i class="fas fa-envelope"></i>
                                        </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                    <div class="text-muted">
                        Showing {{ $documents->firstItem() }} to {{ $documents->lastItem() }}
                        of {{ $documents->total() }} documents
                    </div>
                    {{ $documents->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-file-pdf fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No documents generated yet</h5>
                    <p class="text-muted mb-4">Start by selecting a template and generating a document.</p>
                    <a href="{{ route('staff.generated-documents.create') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-2"></i>Generate First Document
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
