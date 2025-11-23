@extends('admin.layouts.app')

@section('title', 'Email Templates')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Email Templates</li>
@endsection

@section('content')
<div class="page-title">
    <h1>Email Templates</h1>
    <p class="page-subtitle">Manage email templates for automated emails sent by the system.</p>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif


<div class="row">
    <div class="col-lg-12">
        <div class="admin-card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">Email Templates</h3>
                <a href="{{ contextRoute('email-templates.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Create New Template
                </a>
            </div>
            <div class="card-body">
                <!-- Role Filter -->
                @if(isset($availableRoles) && count($availableRoles) > 0)
                <div class="mb-3">
                    <form method="GET" action="{{ contextRoute('email-templates.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="role" class="form-label">Filter by Role:</label>
                            <select name="role" id="role" class="form-select" onchange="this.form.submit()">
                                <option value="">All Roles</option>
                                @foreach($availableRoles as $roleKey => $roleLabel)
                                    <option value="{{ $roleKey }}" {{ request('role') == $roleKey ? 'selected' : '' }}>
                                        {{ $roleLabel }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="category" class="form-label">Filter by Category:</label>
                            <select name="category" id="category" class="form-select" onchange="this.form.submit()">
                                <option value="">All Categories</option>
                                @foreach(['appointments', 'authentication', 'billing', 'contact', 'emergency', 'lab_reports', 'medical', 'patient_care', 'patient_notifications', 'prescriptions', 'security', 'staff_notifications', 'welcome'] as $cat)
                                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                                        {{ ucwords(str_replace('_', ' ', $cat)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <a href="{{ contextRoute('email-templates.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Clear Filters
                            </a>
                        </div>
                    </form>
                </div>
                @endif

                <!-- Show grouped view only when no filters are applied, otherwise show paginated table -->
                @if(!request()->has('role') && !request()->has('category') && isset($templatesByRole) && $templatesByRole->count() > 0)
                    @foreach($templatesByRole as $roleGroup => $roleTemplates)
                        <div class="mb-4">
                            <h5 class="mb-3">
                                @if($roleGroup === 'all')
                                    <i class="fas fa-users text-primary me-2"></i>All Roles
                                @else
                                    <i class="fas fa-user-tag text-info me-2"></i>
                                    @php
                                        $roleLabels = explode(', ', $roleGroup);
                                        $displayRoles = collect($roleLabels)->map(function($r) use ($availableRoles) {
                                            return isset($availableRoles[$r]) ? $availableRoles[$r] : ucfirst($r);
                                        })->implode(', ');
                                    @endphp
                                    {{ $displayRoles }}
                                @endif
                                <span class="badge bg-secondary">{{ $roleTemplates->count() }}</span>
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Subject</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Last Used</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($roleTemplates as $template)
                                        <tr>
                                            <td>
                                                <strong>{{ $template->formatted_name }}</strong>
                                                @if($template->description)
                                                    <br><small class="text-muted">{{ $template->description }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $template->subject }}</td>
                                            <td>
                                                <span class="badge bg-secondary">{{ ucfirst($template->category) }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $template->status_badge_class }}">
                                                    {{ ucfirst($template->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                @if($template->last_used_at)
                                                    {{ $template->last_used_at->diffForHumans() }}
                                                @else
                                                    <span class="text-muted">Never</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="{{ contextRoute('email-templates.show', $template) }}" 
                                                       class="btn btn-sm btn-outline-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ contextRoute('email-templates.edit', $template) }}" 
                                                       class="btn btn-sm btn-outline-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ contextRoute('email-templates.toggle-status', $template) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" 
                                                                class="btn btn-sm {{ $template->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                                title="{{ $template->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                            <i class="fas {{ $template->status === 'active' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                        </button>
                                                    </form>
                                                    <form action="{{ contextRoute('email-templates.duplicate', $template) }}" 
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-secondary" 
                                                                title="Duplicate">
                                                            <i class="fas fa-copy"></i>
                                                        </button>
                                                    </form>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="deleteTemplate({{ $template->id }}); return false;" 
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endforeach
                @elseif($templates->count() > 0)
                    <!-- Fallback to regular table if no grouping -->
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Category</th>
                                    <th>Target Roles</th>
                                    <th>Status</th>
                                    <th>Last Used</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templates as $template)
                                <tr>
                                    <td>
                                        <strong>{{ $template->formatted_name }}</strong>
                                        @if($template->description)
                                            <br><small class="text-muted">{{ $template->description }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $template->subject }}</td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($template->category) }}</span>
                                    </td>
                                    <td>
                                        @if(empty($template->target_roles))
                                            <span class="badge bg-primary">All Roles</span>
                                        @else
                                            @foreach($template->target_roles as $role)
                                                <span class="badge bg-info">{{ isset($availableRoles[$role]) ? $availableRoles[$role] : ucfirst($role) }}</span>
                                            @endforeach
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge {{ $template->status_badge_class }}">
                                            {{ ucfirst($template->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($template->last_used_at)
                                            {{ $template->last_used_at->diffForHumans() }}
                                        @else
                                            <span class="text-muted">Never</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ contextRoute('email-templates.show', $template) }}" 
                                               class="btn btn-sm btn-outline-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ contextRoute('email-templates.edit', $template) }}" 
                                               class="btn btn-sm btn-outline-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form action="{{ contextRoute('email-templates.toggle-status', $template) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" 
                                                        class="btn btn-sm {{ $template->status === 'active' ? 'btn-outline-warning' : 'btn-outline-success' }}" 
                                                        title="{{ $template->status === 'active' ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fas {{ $template->status === 'active' ? 'fa-toggle-on' : 'fa-toggle-off' }}"></i>
                                                </button>
                                            </form>
                                            <form action="{{ contextRoute('email-templates.duplicate', $template) }}" 
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary" 
                                                        title="Duplicate">
                                                    <i class="fas fa-copy"></i>
                                                </button>
                                            </form>
                                            <button class="btn btn-sm btn-outline-danger" 
                                                    onclick="deleteTemplate({{ $template->id }}); return false;" 
                                                    title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-between align-items-center mt-4 px-3">
                        <div class="text-muted">
                            Showing {{ $templates->firstItem() }} to {{ $templates->lastItem() }} of {{ $templates->total() }} results
                        </div>
                        <div>
                            {{ $templates->links() }}
                        </div>
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-envelope-open fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">No email templates found</h5>
                        <p class="text-muted">Create your first email template to get started</p>
                        <a href="{{ contextRoute('email-templates.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Create First Template
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Quick Stats -->
<div class="row mt-4">
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-primary">
                <i class="fas fa-envelope"></i>
            </div>
            <div class="stats-content">
                <h3>{{ $templates->total() }}</h3>
                <p>Total Templates</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-success">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stats-content">
                <h3>{{ $templates->where('status', 'active')->count() }}</h3>
                <p>Active Templates</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-warning">
                <i class="fas fa-edit"></i>
            </div>
            <div class="stats-content">
                <h3>{{ $templates->where('status', 'draft')->count() }}</h3>
                <p>Draft Templates</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="stats-card">
            <div class="stats-icon bg-info">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stats-content">
                <h3>{{ $templates->whereNotNull('last_used_at')->count() }}</h3>
                <p>Used Templates</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>  
    function deleteTemplate(templateId) {
        ModalSystem.confirm({
            title: 'Delete Email Template',
            message: 'Are you sure you want to delete this email template?',
            confirmText: 'Delete',
            confirmClass: 'btn-danger',
            icon: 'fas fa-exclamation-triangle',
            onConfirm: function() {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/email-templates/${templateId}`;
                
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                form.appendChild(csrfToken);
                
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);

                document.body.appendChild(form);
                form.submit();
            }
        });
    }
</script>
@endpush

@push('styles')
<style>
    .admin-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
    
    /* Hide pagination arrow SVG icons - multiple selectors for different Laravel versions */
    .pagination .page-link svg,
    .pagination svg,
    nav[aria-label="Pagination Navigation"] svg {
        display: none !important;
    }
    
    /* Hide aria-hidden elements that contain arrows */
    .pagination [aria-hidden="true"],
    .pagination .page-link span:first-child:not(:only-child) {
        display: none !important;
    }
    
    /* Ensure Previous/Next text shows properly */
    .pagination .page-link {
        font-size: 0.9rem;
    }
    
    /* Better pagination button styling */
    .pagination .page-link {
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        margin: 0 0.25rem;
    }
    
    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        border-radius: 0.375rem;
    }
    
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    
    .table th {
        font-weight: 600;
        color: #374151;
        border-bottom: 2px solid #dee2e6;
    }
    
    .table td {
        vertical-align: middle;
    }
    
    .stats-card {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .stats-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        color: white;
        font-size: 1.5rem;
    }
    
    .stats-content h3 {
        margin: 0;
        font-size: 2rem;
        font-weight: 700;
        color: #374151;
    }
    
    .stats-content p {
        margin: 0;
        color: #6b7280;
        font-weight: 500;
    }
    
    .badge {
        font-size: 0.75rem;
        padding: 0.375rem 0.75rem;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        font-weight: 600;
        transition: all 0.3s ease;
    }
    
    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-outline-info, .btn-outline-warning, .btn-outline-secondary, .btn-outline-danger {
        border-radius: 6px;
        border-width: 1px;
        transition: all 0.3s ease;
    }
    
    .btn-outline-info:hover, .btn-outline-warning:hover, 
    .btn-outline-secondary:hover, .btn-outline-danger:hover {
        transform: translateY(-1px);
    }
</style>
@endpush
