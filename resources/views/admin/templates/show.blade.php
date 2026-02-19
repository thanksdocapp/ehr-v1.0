@extends('admin.layouts.app')

@section('title', $template->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item active">{{ $template->name }}</li>
@endsection

@section('content')
<div class="container-fluid">
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
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <span class="badge {{ $template->type_badge_class }} me-2">
                            <i class="fas {{ $template->type_icon }} me-1"></i>
                            {{ ucfirst($template->type) }}
                        </span>
                        <h5 class="card-title mb-0">{{ $template->name }}</h5>
                    </div>
                    <div class="d-flex gap-2">
                        @can('update', $template)
                            <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-edit me-1"></i>Edit
                            </a>
                        @endcan
                        <a href="{{ route('admin.generated-documents.create', ['template_id' => $template->id]) }}"
                           class="btn btn-success btn-sm">
                            <i class="fas fa-file-pdf me-1"></i>Generate Document
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($template->description)
                        <p class="text-muted mb-4">{{ $template->description }}</p>
                    @endif

                    <h6 class="text-muted mb-3">Template Preview</h6>
                    <div class="border rounded p-4 bg-white" style="min-height: 300px;">
                        {!! $template->content !!}
                    </div>
                </div>
            </div>

            <!-- Used Placeholders -->
            @php
                $usedPlaceholders = $template->extractUsedPlaceholders();
            @endphp
            @if(count($usedPlaceholders) > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-code me-2 text-primary"></i>Placeholders Used
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($usedPlaceholders as $placeholder)
                                <span class="badge bg-light text-dark border">
                                    <code>{{ $placeholder }}</code>
                                </span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status & Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Template Info
                    </h6>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Status</td>
                            <td class="text-end">
                                <span class="badge {{ $template->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Type</td>
                            <td class="text-end">
                                <span class="badge {{ $template->type_badge_class }}">
                                    {{ ucfirst($template->type) }}
                                </span>
                            </td>
                        </tr>
                        @if($template->is_system)
                            <tr>
                                <td class="text-muted">Visibility</td>
                                <td class="text-end">
                                    <span class="badge bg-info">System Template</span>
                                </td>
                            </tr>
                        @endif
                        <tr>
                            <td class="text-muted">Usage Count</td>
                            <td class="text-end">{{ $usageCount }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created</td>
                            <td class="text-end">{{ formatDateUk($template->created_at) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created By</td>
                            <td class="text-end">{{ $template->creator->name ?? 'Unknown' }}</td>
                        </tr>
                        @if($template->updated_by)
                            <tr>
                                <td class="text-muted">Last Updated</td>
                                <td class="text-end">{{ formatDateUk($template->updated_at) }}</td>
                            </tr>
                        @endif
                    </table>
                </div>
            </div>

            <!-- Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="card-title mb-0">
                        <i class="fas fa-bolt me-2 text-primary"></i>Actions
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.generated-documents.create', ['template_id' => $template->id]) }}"
                           class="btn btn-success">
                            <i class="fas fa-file-pdf me-2"></i>Generate Document
                        </a>

                        @can('update', $template)
                            <a href="{{ route('admin.templates.edit', $template) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>Edit Template
                            </a>
                        @endcan

                        @can('duplicate', $template)
                            <form action="{{ route('admin.templates.duplicate', $template) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="fas fa-copy me-2"></i>Duplicate
                                </button>
                            </form>
                        @endcan

                        @can('toggleActive', $template)
                            <form action="{{ route('admin.templates.toggle-active', $template) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-outline-secondary w-100">
                                    @if($template->is_active)
                                        <i class="fas fa-toggle-off me-2"></i>Deactivate
                                    @else
                                        <i class="fas fa-toggle-on me-2"></i>Activate
                                    @endif
                                </button>
                            </form>
                        @endcan

                        @can('delete', $template)
                            <form action="{{ route('admin.templates.destroy', $template) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this template? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-trash me-2"></i>Delete Template
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>

            <!-- Recent Documents -->
            @if($template->generatedDocuments->count() > 0)
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="card-title mb-0">
                            <i class="fas fa-file-alt me-2 text-primary"></i>Recent Documents
                        </h6>
                        <a href="{{ route('admin.generated-documents.index', ['template_id' => $template->id]) }}"
                           class="btn btn-sm btn-link">View All</a>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach($template->generatedDocuments()->latest()->limit(5)->get() as $doc)
                            <a href="{{ route('admin.generated-documents.show', $doc) }}"
                               class="list-group-item list-group-item-action">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold">{{ $doc->patient->full_name ?? 'Unknown Patient' }}</div>
                                        <small class="text-muted">{{ formatDateUk($doc->created_at) }}</small>
                                    </div>
                                    <span class="badge {{ $doc->status_badge_class }}">{{ $doc->status_label }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
