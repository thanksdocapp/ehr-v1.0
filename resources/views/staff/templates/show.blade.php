@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', $template->name)
@section('page-title', $template->name)

@section('content')
<div class="fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <a href="{{ route('staff.templates.index') }}" class="text-muted text-decoration-none">
                        <i class="fas fa-arrow-left me-2"></i>Back to Templates
                    </a>
                    <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">{{ $template->name }}</h1>
                    <span class="badge bg-{{ $template->type === 'letter' ? 'primary' : 'info' }}">
                        {{ ucfirst($template->type) }}
                    </span>
                    @if($template->is_system)
                        <span class="badge bg-secondary ms-1">System Template</span>
                    @endif
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('staff.generated-documents.create', ['template_id' => $template->id]) }}"
                       class="btn btn-success">
                        <i class="fas fa-file-pdf me-2"></i>Generate Document
                    </a>
                    @can('update', $template)
                    <a href="{{ route('staff.templates.edit', $template) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                    @endcan
                    <form action="{{ route('staff.templates.duplicate', $template) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="fas fa-copy me-2"></i>Duplicate
                        </button>
                    </form>
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

    <div class="row">
        <div class="col-lg-8">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-file-alt me-2"></i>Template Preview
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <div class="border rounded p-4 bg-white" style="min-height: 400px;">
                        {!! $template->content !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Template Info
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td class="text-muted">Type</td>
                            <td class="text-end">{{ ucfirst($template->type) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created By</td>
                            <td class="text-end">{{ $template->creator->name ?? 'System' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created</td>
                            <td class="text-end">{{ $template->created_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Last Updated</td>
                            <td class="text-end">{{ $template->updated_at->format('M d, Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status</td>
                            <td class="text-end">
                                <span class="badge bg-{{ $template->is_active ? 'success' : 'secondary' }}">
                                    {{ $template->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-code me-2"></i>Available Placeholders
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <p class="text-muted small mb-3">These placeholders will be replaced with actual data when generating a document:</p>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach(\App\Models\Template::DEFAULT_PLACEHOLDERS as $placeholder => $description)
                            <code class="bg-light px-2 py-1 rounded small" title="{{ $description }}">{{ $placeholder }}</code>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
