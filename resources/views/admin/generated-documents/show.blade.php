@extends('admin.layouts.app')

@section('title', $generatedDocument->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.generated-documents.index') }}">Generated Documents</a></li>
    <li class="breadcrumb-item active">{{ Str::limit($generatedDocument->title, 30) }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Document Header -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="card-title mb-1">{{ $generatedDocument->title }}</h5>
                        <span class="badge {{ $generatedDocument->status_badge_class }}">
                            {{ $generatedDocument->status_label }}
                        </span>
                        @if($generatedDocument->template)
                            <span class="badge {{ $generatedDocument->template->type_badge_class }} ms-1">
                                {{ ucfirst($generatedDocument->template->type) }}
                            </span>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        @can('download', $generatedDocument)
                            <a href="{{ route('admin.generated-documents.download', $generatedDocument) }}"
                               class="btn btn-success btn-sm">
                                <i class="fas fa-download me-1"></i>Download PDF
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <!-- Document Preview -->
                    <h6 class="text-muted mb-3">Document Content</h6>
                    <div class="border rounded p-4 bg-white" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                        {!! $generatedDocument->rendered_content !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Patient Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-user me-2 text-primary"></i>Patient
                    </h6>
                </div>
                <div class="card-body">
                    @if($generatedDocument->patient)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 50px; height: 50px;">
                                {{ strtoupper(substr($generatedDocument->patient->first_name ?? 'P', 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="mb-0">
                                    {{ $generatedDocument->patient->full_name ?? $generatedDocument->patient->first_name . ' ' . $generatedDocument->patient->last_name }}
                                </h6>
                                <small class="text-muted">
                                    {{ $generatedDocument->patient->patient_id ?? 'ID: ' . $generatedDocument->patient->id }}
                                </small>
                            </div>
                        </div>
                        <a href="{{ route('admin.patients.show', $generatedDocument->patient) }}"
                           class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-external-link-alt me-1"></i>View Patient Profile
                        </a>
                    @else
                        <p class="text-muted mb-0">Patient information not available</p>
                    @endif
                </div>
            </div>

            <!-- Document Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Document Info
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Status</td>
                            <td class="text-end">
                                <span class="badge {{ $generatedDocument->status_badge_class }}">
                                    {{ $generatedDocument->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Template</td>
                            <td class="text-end">
                                @if($generatedDocument->template)
                                    <a href="{{ route('admin.templates.show', $generatedDocument->template) }}">
                                        {{ $generatedDocument->template->name }}
                                    </a>
                                @else
                                    <span class="text-muted">Unknown</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Generated By</td>
                            <td class="text-end">{{ $generatedDocument->generator->name ?? 'Unknown' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created</td>
                            <td class="text-end">{{ formatDateTimeUk($generatedDocument->created_at) }}</td>
                        </tr>
                        @if($generatedDocument->sent_at)
                            <tr>
                                <td class="text-muted">Sent</td>
                                <td class="text-end">{{ formatDateTimeUk($generatedDocument->sent_at) }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted">Sent To</td>
                                <td class="text-end">{{ $generatedDocument->sent_to }}</td>
                            </tr>
                        @endif
                    </table>

                    @if($generatedDocument->notes)
                        <hr>
                        <h6 class="text-muted">Notes</h6>
                        <p class="small mb-0">{{ $generatedDocument->notes }}</p>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt me-2 text-primary"></i>Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @can('download', $generatedDocument)
                            <a href="{{ route('admin.generated-documents.download', $generatedDocument) }}"
                               class="btn btn-success">
                                <i class="fas fa-download me-2"></i>Download PDF
                            </a>
                        @endcan

                        @can('send', $generatedDocument)
                            <a href="{{ route('admin.generated-documents.send-form', $generatedDocument) }}"
                               class="btn btn-primary">
                                <i class="fas fa-envelope me-2"></i>Send via Email
                            </a>
                        @endcan

                        @can('finalize', $generatedDocument)
                            <form action="{{ route('admin.generated-documents.finalize', $generatedDocument) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-check me-2"></i>Finalize Document
                                </button>
                            </form>
                        @endcan

                        @can('update', $generatedDocument)
                            <form action="{{ route('admin.generated-documents.regenerate', $generatedDocument) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-secondary w-100"
                                        onclick="return confirm('This will regenerate the PDF with fresh data. Continue?')">
                                    <i class="fas fa-sync me-2"></i>Regenerate PDF
                                </button>
                            </form>
                        @endcan

                        @can('void', $generatedDocument)
                            <form action="{{ route('admin.generated-documents.void', $generatedDocument) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-warning w-100"
                                        onclick="return confirm('Are you sure you want to void this document?')">
                                    <i class="fas fa-ban me-2"></i>Void Document
                                </button>
                            </form>
                        @endcan

                        @can('delete', $generatedDocument)
                            <form action="{{ route('admin.generated-documents.destroy', $generatedDocument) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100"
                                        onclick="return confirm('Are you sure you want to permanently delete this document?')">
                                    <i class="fas fa-trash me-2"></i>Delete Document
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
