@extends('admin.layouts.app')

@section('title', 'Clinics Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Clinics</li>
@endsection

@push('styles')
<style>
    .modern-stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 16px;
        padding: 1.5rem;
        color: white;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        position: relative;
        overflow: hidden;
    }

    .modern-stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        transition: all 0.5s ease;
    }

    .modern-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .modern-stat-card:hover::before {
        top: -30%;
        right: -30%;
    }

    .modern-stat-card.primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .modern-stat-card.success {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .modern-stat-card.info {
        background: linear-gradient(135deg, #3494E6 0%, #EC6EAD 100%);
    }

    .modern-stat-card.warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-icon-modern {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        backdrop-filter: blur(10px);
    }

    .modern-clinic-list-item {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
        display: flex;
        align-items: center;
        gap: 1.5rem;
    }

    .modern-clinic-list-item:hover {
        transform: translateX(5px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        border-color: #667eea;
    }

    .clinic-list-image {
        width: 120px;
        height: 120px;
        border-radius: 12px;
        object-fit: cover;
        flex-shrink: 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 2.5rem;
    }

    .clinic-list-content {
        flex: 1;
        min-width: 0;
    }

    .clinic-list-stats {
        display: flex;
        gap: 2rem;
        margin-top: 1rem;
    }

    .clinic-list-stat-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .clinic-list-stat-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1rem;
    }

    .clinic-list-stat-icon.patients {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .clinic-list-stat-icon.appointments {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        color: white;
    }

    .clinic-list-stat-icon.doctors {
        background: linear-gradient(135deg, #3494E6 0%, #EC6EAD 100%);
        color: white;
    }

    .clinic-list-stat-info {
        display: flex;
        flex-direction: column;
    }

    .clinic-list-stat-number {
        font-size: 1.25rem;
        font-weight: 700;
        color: #2d3748;
    }

    .clinic-list-stat-label {
        font-size: 0.75rem;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .clinic-list-actions {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .modern-action-btn {
        border: none;
        border-radius: 10px;
        padding: 10px 15px;
        transition: all 0.3s ease;
        font-weight: 500;
    }

    .modern-action-btn:hover {
        transform: scale(1.05);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    }

    .filter-card-modern {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        border: 1px solid #e9ecef;
    }

    .search-input-wrapper {
        position: relative;
    }

    .search-input-wrapper .search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        z-index: 1;
    }

    .search-input-wrapper input {
        padding-left: 45px;
        padding-right: 45px;
    }

    .search-clear-btn {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6c757d;
        cursor: pointer;
        padding: 5px 10px;
        z-index: 1;
        display: none;
    }

    .search-input-wrapper.has-value .search-clear-btn {
        display: block;
    }

    .search-clear-btn:hover {
        color: #dc3545;
    }

    .filter-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        font-size: 0.875rem;
        margin: 0.25rem;
    }

    .filter-chip .remove {
        cursor: pointer;
        opacity: 0.8;
        transition: opacity 0.2s;
    }

    .filter-chip .remove:hover {
        opacity: 1;
    }

    .advanced-search-toggle {
        cursor: pointer;
        color: #667eea;
        text-decoration: none;
        font-size: 0.875rem;
        transition: color 0.2s;
    }

    .advanced-search-toggle:hover {
        color: #764ba2;
    }

    .advanced-search-panel {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s ease-out;
    }

    .advanced-search-panel.show {
        max-height: 500px;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
    }

    .page-header-modern {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 16px;
        padding: 2rem;
        color: white;
        margin-bottom: 2rem;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
    }

    .btn-modern-primary {
        background: white;
        color: #667eea;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        font-weight: 600;
        transition: all 0.3s ease;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .btn-modern-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        color: #667eea;
    }

    .empty-state-modern {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
    }

    .empty-state-icon {
        width: 120px;
        height: 120px;
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 2rem;
        font-size: 3rem;
        color: #667eea;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .fade-in-up {
        animation: fadeInUp 0.5s ease-out;
    }

    .stagger-1 { animation-delay: 0.1s; }
    .stagger-2 { animation-delay: 0.2s; }
    .stagger-3 { animation-delay: 0.3s; }
    .stagger-4 { animation-delay: 0.4s; }
</style>
@endpush

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="page-header-modern fade-in-up">
        <div class="d-flex justify-content-between align-items-center flex-wrap">
            <div>
                <h2 class="mb-2 fw-bold">
                    <i class="fas fa-hospital-alt me-2"></i>Clinics Management
                </h2>
                <p class="mb-0 opacity-90">Manage and monitor all clinics, departments, and their services</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ contextRoute('departments.create') }}" class="btn-modern-primary">
                    <i class="fas fa-plus me-2"></i>Add New Clinic
                </a>
            </div>
        </div>
    </div>

    <!-- Modern Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="modern-stat-card primary fade-in-up stagger-1">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="mb-1 opacity-75" style="font-size: 0.875rem;">Total Clinics</div>
                        <div class="h3 mb-0 fw-bold">{{ $departments->total() ?? 0 }}</div>
                    </div>
                    <div class="stat-icon-modern">
                        <i class="fas fa-building"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="modern-stat-card success fade-in-up stagger-2">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="mb-1 opacity-75" style="font-size: 0.875rem;">Active Clinics</div>
                        <div class="h3 mb-0 fw-bold">{{ $departments->where('is_active', true)->count() ?? 0 }}</div>
                    </div>
                    <div class="stat-icon-modern">
                        <i class="fas fa-check-circle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="modern-stat-card info fade-in-up stagger-3">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="mb-1 opacity-75" style="font-size: 0.875rem;">Total Doctors</div>
                        <div class="h3 mb-0 fw-bold">{{ $departments->sum('doctors_count') ?? 0 }}</div>
                    </div>
                    <div class="stat-icon-modern">
                        <i class="fas fa-user-md"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6">
            <div class="modern-stat-card warning fade-in-up stagger-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="mb-1 opacity-75" style="font-size: 0.875rem;">Total Appointments</div>
                        <div class="h3 mb-0 fw-bold">{{ $departments->sum('appointments_count') ?? 0 }}</div>
                    </div>
                    <div class="stat-icon-modern">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Filters Card -->
    <div class="filter-card-modern mb-4 fade-in-up">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center">
                <i class="fas fa-filter me-2 text-primary"></i>
                <h5 class="mb-0 fw-bold">Search & Filter</h5>
            </div>
            <a href="#" class="advanced-search-toggle" id="toggleAdvancedSearch">
                <i class="fas fa-sliders-h me-1"></i>Advanced Search
            </a>
        </div>

        <!-- Active Filters -->
        @if(request()->hasAny(['search', 'status', 'emergency', 'location', 'sort']))
        <div class="mb-3">
            <small class="text-muted d-block mb-2">Active Filters:</small>
            <div class="d-flex flex-wrap align-items-center">
                @if(request('search'))
                    <span class="filter-chip">
                        Search: "{{ request('search') }}"
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="remove text-white">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif
                @if(request('status'))
                    <span class="filter-chip">
                        Status: {{ ucfirst(request('status')) }}
                        <a href="{{ request()->fullUrlWithQuery(['status' => null]) }}" class="remove text-white">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif
                @if(request('emergency'))
                    <span class="filter-chip">
                        Emergency: {{ request('emergency') == 'yes' ? 'Yes' : 'No' }}
                        <a href="{{ request()->fullUrlWithQuery(['emergency' => null]) }}" class="remove text-white">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif
                @if(request('location'))
                    <span class="filter-chip">
                        Location: {{ request('location') }}
                        <a href="{{ request()->fullUrlWithQuery(['location' => null]) }}" class="remove text-white">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif
                @if(request('sort') && request('sort') != 'name')
                    <span class="filter-chip">
                        Sort: {{ ucfirst(request('sort')) }}
                        <a href="{{ request()->fullUrlWithQuery(['sort' => 'name']) }}" class="remove text-white">
                            <i class="fas fa-times"></i>
                        </a>
                    </span>
                @endif
                <a href="{{ contextRoute('departments.index') }}" class="btn btn-sm btn-outline-danger ms-2">
                    <i class="fas fa-times me-1"></i>Clear All
                </a>
            </div>
        </div>
        @endif

        <form method="GET" action="{{ contextRoute('departments.index') }}" id="searchForm">
            <div class="row g-3">
                <!-- Main Search -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Search Clinics</label>
                    <div class="search-input-wrapper {{ request('search') ? 'has-value' : '' }}">
                        <i class="fas fa-search search-icon"></i>
                        <input type="text" 
                               name="search" 
                               id="searchInput"
                               class="form-control" 
                               placeholder="Search by name, description, location, head, phone, email..." 
                               value="{{ request('search') }}"
                               autocomplete="off">
                        @if(request('search'))
                            <button type="button" class="search-clear-btn" onclick="clearSearch()">
                                <i class="fas fa-times"></i>
                            </button>
                        @endif
                    </div>
                </div>

                <!-- Status Filter -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <!-- Sort -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Sort By</label>
                    <select name="sort" class="form-select">
                        <option value="name" {{ request('sort') == 'name' ? 'selected' : '' }}>Name (A-Z)</option>
                        <option value="doctors" {{ request('sort') == 'doctors' ? 'selected' : '' }}>Most Doctors</option>
                        <option value="appointments" {{ request('sort') == 'appointments' ? 'selected' : '' }}>Most Appointments</option>
                        <option value="recent" {{ request('sort') == 'recent' ? 'selected' : '' }}>Recently Added</option>
                    </select>
                </div>
            </div>

            <!-- Advanced Search Panel -->
            <div class="advanced-search-panel" id="advancedSearchPanel">
                <div class="row g-3 mt-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Emergency Department</label>
                        <select name="emergency" class="form-select">
                            <option value="">All Types</option>
                            <option value="yes" {{ request('emergency') == 'yes' ? 'selected' : '' }}>Emergency Only</option>
                            <option value="no" {{ request('emergency') == 'no' ? 'selected' : '' }}>Regular Only</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold">Location</label>
                        <select name="location" class="form-select">
                            <option value="">All Locations</option>
                            @foreach($locations ?? [] as $location)
                                <option value="{{ $location }}" {{ request('location') == $location ? 'selected' : '' }}>
                                    {{ $location }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row g-3 mt-2">
                <div class="col-md-12">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary" style="border-radius: 10px;">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                        <a href="{{ contextRoute('departments.index') }}" class="btn btn-outline-secondary" style="border-radius: 10px;">
                            <i class="fas fa-redo me-1"></i>Reset
                        </a>
                        @if(request()->hasAny(['search', 'status', 'emergency', 'location', 'sort']))
                            <div class="ms-auto d-flex align-items-center">
                                <small class="text-muted me-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    {{ $departments->total() }} result(s) found
                                </small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Modern Clinics List -->
    <div class="fade-in-up">
        @if($departments->count() > 0)
            @foreach($departments as $index => $department)
            <div class="modern-clinic-list-item fade-in-up" style="animation-delay: {{ ($index % 6) * 0.1 }}s !important;">
                <!-- Clinic Image -->
                <div class="clinic-list-image">
                    @if($department->image)
                        <img src="{{ Storage::disk('public')->url('uploads/departments/' . $department->image) }}" 
                             alt="{{ $department->name }}"
                             style="width: 100%; height: 100%; object-fit: cover; border-radius: 12px;">
                    @else
                        <i class="fas fa-hospital-alt"></i>
                    @endif
                </div>
                
                <!-- Clinic Content -->
                <div class="clinic-list-content">
                    <div class="d-flex align-items-start justify-content-between mb-2">
                        <div>
                            <h5 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                                {{ $department->name }}
                                @if($department->is_emergency)
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Emergency
                                    </span>
                                @endif
                                <span class="badge {{ $department->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $department->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </h5>
                            @if($department->description)
                                <p class="text-muted mb-0 small">
                                    {{ Str::limit($department->description, 150) }}
                                </p>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Clinic Info -->
                    <div class="d-flex gap-4 mb-2 flex-wrap">
                        @if($department->head_of_department)
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-tie me-2 text-primary"></i>
                                <small class="text-muted">
                                    <strong>Head:</strong> {{ $department->head_of_department }}
                                </small>
                            </div>
                        @endif
                        
                        @if($department->location)
                            <div class="d-flex align-items-center">
                                <i class="fas fa-map-marker-alt me-2 text-danger"></i>
                                <small class="text-muted">
                                    <strong>Location:</strong> {{ Str::limit($department->location, 40) }}
                                </small>
                            </div>
                        @endif
                    </div>
                    
                    <!-- Modern Clinic Stats -->
                    <div class="clinic-list-stats">
                        <div class="clinic-list-stat-item">
                            <div class="clinic-list-stat-icon patients">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="clinic-list-stat-info">
                                <div class="clinic-list-stat-number">{{ $department->patients_count ?? 0 }}</div>
                                <div class="clinic-list-stat-label">Patients</div>
                            </div>
                        </div>
                        <div class="clinic-list-stat-item">
                            <div class="clinic-list-stat-icon appointments">
                                <i class="fas fa-calendar-check"></i>
                            </div>
                            <div class="clinic-list-stat-info">
                                <div class="clinic-list-stat-number">{{ $department->appointments_count ?? 0 }}</div>
                                <div class="clinic-list-stat-label">Appointments</div>
                            </div>
                        </div>
                        <div class="clinic-list-stat-item">
                            <div class="clinic-list-stat-icon doctors">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div class="clinic-list-stat-info">
                                <div class="clinic-list-stat-number">{{ $department->doctors_count ?? 0 }}</div>
                                <div class="clinic-list-stat-label">Doctors</div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="clinic-list-actions">
                    <a href="{{ contextRoute('departments.show', $department->id) }}" 
                       class="btn btn-primary modern-action-btn" 
                       title="View Details">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ contextRoute('departments.edit', $department->id) }}" 
                       class="btn btn-outline-secondary modern-action-btn" 
                       title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <button class="btn btn-outline-info modern-action-btn" 
                            onclick="viewDoctors({{ $department->id }})" 
                            title="View Doctors">
                        <i class="fas fa-user-md"></i>
                    </button>
                    <button class="btn btn-outline-{{ $department->is_active ? 'warning' : 'success' }} modern-action-btn"
                            onclick="toggleStatus({{ $department->id }})"
                            title="{{ $department->is_active ? 'Deactivate' : 'Activate' }}">
                        <i class="fas fa-{{ $department->is_active ? 'pause' : 'play' }}"></i>
                    </button>
                    <button class="btn btn-outline-danger modern-action-btn"
                            onclick="deleteDepartment({{ $department->id }})"
                            title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>
            </div>
            @endforeach
        @else
            <div class="empty-state-modern fade-in-up">
                <div class="empty-state-icon">
                    <i class="fas fa-building"></i>
                </div>
                <h4 class="fw-bold mb-2">No Clinics Found</h4>
                <p class="text-muted mb-4">No clinics match your current filters. Try adjusting your search criteria.</p>
                <a href="{{ contextRoute('departments.create') }}" class="btn btn-primary btn-lg" style="border-radius: 12px; padding: 12px 32px;">
                    <i class="fas fa-plus me-2"></i>Create First Clinic
                </a>
            </div>
        @endif
    </div>

    <!-- Pagination -->
    @if($departments->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $departments->links() }}
    </div>
    @endif

    <!-- Quick Actions Modal -->
    <div class="modal fade" id="quickActionsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Quick Actions</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="d-grid gap-2">
                        <button class="btn btn-doctor-primary" onclick="exportDepartments()">
                            <i class="fas fa-download me-2"></i>Export All Clinics
                        </button>
                        <button class="btn btn-outline-success" onclick="activateAll()">
                            <i class="fas fa-check me-2"></i>Activate All Clinics
                        </button>
                        <button class="btn btn-outline-warning" onclick="deactivateAll()">
                            <i class="fas fa-pause me-2"></i>Deactivate All Clinics
                        </button>
                        <button class="btn btn-outline-info" onclick="generateReport()">
                            <i class="fas fa-chart-bar me-2"></i>Generate Clinic Report
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modern Floating Action Button -->
    <div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1050;">
        <button class="btn btn-primary rounded-circle shadow-lg" 
                data-bs-toggle="modal" 
                data-bs-target="#quickActionsModal"
                style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; transition: all 0.3s ease;"
                onmouseover="this.style.transform='scale(1.1)'"
                onmouseout="this.style.transform='scale(1)'">
            <i class="fas fa-cog fa-lg"></i>
        </button>
    </div>
</div>


<!-- Application Footer -->
@if(shouldShowPoweredBy())
<div class="text-center mt-5 py-4" style="border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px;">
    <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
        <i class="fas fa-building" style="color: #e94560;"></i>
        <span>Clinics Management - <strong>{{ getAppName() }} v{{ getAppVersion() }}</strong></span>
    </div>
    <div class="mt-2" style="font-size: 12px; opacity: 0.8;">
        {{ getCopyrightText() }}
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Advanced Search Toggle
        $('#toggleAdvancedSearch').on('click', function(e) {
            e.preventDefault();
            const panel = $('#advancedSearchPanel');
            const icon = $(this).find('i');
            
            panel.toggleClass('show');
            
            if (panel.hasClass('show')) {
                icon.removeClass('fa-sliders-h').addClass('fa-chevron-up');
            } else {
                icon.removeClass('fa-chevron-up').addClass('fa-sliders-h');
            }
        });

        // Show advanced panel if any advanced filter is active
        @if(request()->hasAny(['emergency', 'location']))
            $('#advancedSearchPanel').addClass('show');
            $('#toggleAdvancedSearch i').removeClass('fa-sliders-h').addClass('fa-chevron-up');
        @endif

        // Search input clear button visibility
        const searchInput = $('#searchInput');
        const searchWrapper = $('.search-input-wrapper');
        
        function toggleClearButton() {
            if (searchInput.val().length > 0) {
                searchWrapper.addClass('has-value');
            } else {
                searchWrapper.removeClass('has-value');
            }
        }

        searchInput.on('input', toggleClearButton);
        toggleClearButton();

        // Clear search function
        window.clearSearch = function() {
            searchInput.val('');
            searchWrapper.removeClass('has-value');
            $('#searchForm').submit();
        };

        // Auto-submit on Enter key
        searchInput.on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                $('#searchForm').submit();
            }
        });

        // Debounced search (optional - for real-time search)
        let searchTimeout;
        searchInput.on('input', function() {
            clearTimeout(searchTimeout);
            // Uncomment below for auto-search on typing (with 500ms delay)
            // searchTimeout = setTimeout(function() {
            //     if (searchInput.val().length >= 3 || searchInput.val().length === 0) {
            //         $('#searchForm').submit();
            //     }
            // }, 500);
        });

        // Filter chip removal
        $('.filter-chip .remove').on('click', function(e) {
            e.preventDefault();
            const url = $(this).attr('href');
            if (url) {
                window.location.href = url;
            }
        });
    });

    // Clinic actions
    function viewDoctors(departmentId) {
        window.location.href = `/admin/doctors?department_id=${departmentId}`;
    }

    function toggleStatus(departmentId) {
        // Show a confirmation dialog
        const confirmChange = confirm('Are you sure you want to change this clinic\'s status?\n\nThis action will affect the clinic\'s visibility in the system and patient booking availability.');
        
        // Check if confirmation is a Promise
        if (confirmChange && typeof confirmChange.then === 'function') {
            confirmChange.then(result => {
                if (result) sendStatusChangeRequest(departmentId);
            }).catch(() => {});
        } else if (confirmChange) {
            sendStatusChangeRequest(departmentId);
        }
    }

    function sendStatusChangeRequest(departmentId) {
        // Make the request to toggle the status
        fetch(`/admin/departments/${departmentId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error changing clinic status');
            }
        })
        .catch(error => {
            console.error('Error toggling department status:', error);
            alert('An error occurred while changing the clinic\'s status. Please try again.');
        });
    }

    function deleteDepartment(departmentId) {
        console.log('Delete department called with ID:', departmentId);
        
        // Prevent any default behavior if event exists
        if (window.event) {
            window.event.preventDefault();
            window.event.stopPropagation();
        }
        
        // Handle both sync and async confirm dialogs
        function handleConfirmation(confirmResult) {
            console.log('User confirmation result:', confirmResult);
            
            if (confirmResult === true) {
                console.log('User confirmed deletion, proceeding...');
                
                // Add a small delay to ensure the dialog is properly closed
                setTimeout(() => {
                    console.log('Creating form for deletion...');
                    
                    // Create a form to submit the DELETE request
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `/admin/departments/${departmentId}`;
                    form.style.display = 'none';
                    
                    // Add CSRF token - try multiple methods
                    const csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    
                    // Try to get CSRF token from meta tag or Laravel's global
                    let csrfTokenValue = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    if (!csrfTokenValue && typeof Laravel !== 'undefined') {
                        csrfTokenValue = Laravel.csrfToken;
                    }
                    if (!csrfTokenValue && typeof window.Laravel !== 'undefined') {
                        csrfTokenValue = window.Laravel.csrfToken;
                    }
                    
                    csrfToken.value = csrfTokenValue;
                    form.appendChild(csrfToken);
                    
                    console.log('CSRF token:', csrfTokenValue);
                    
                    // Add DELETE method
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);
                    
                    console.log('Form action:', form.action);
                    console.log('Form method:', form.method);
                    console.log('Form children:', form.children);
                    
                    // Add form to document and submit
                    document.body.appendChild(form);
                    console.log('Form added to document, submitting...');
                    form.submit();
                }, 100);
            } else {
                console.log('User cancelled deletion');
            }
        }
        
        // Use a more explicit confirmation dialog
        const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this clinic?\n\nThis action cannot be undone and will remove all clinic data including:\n- Clinic information\n- Associated doctors\n- Appointment history\n- Patient relationships\n\nClick OK to confirm deletion or Cancel to abort.');
        
        // Handle both Promise and boolean returns
        if (confirmDelete && typeof confirmDelete.then === 'function') {
            // If it's a Promise, wait for it to resolve
            confirmDelete.then(handleConfirmation).catch(() => handleConfirmation(false));
        } else {
            // If it's a boolean, handle it directly
            handleConfirmation(confirmDelete);
        }
        
        return false;
    }

    // Quick actions
    function exportDepartments() {
        alert('Export functionality will be implemented');
    }

    function activateAll() {
        if (confirm('Are you sure you want to activate all clinics?')) {
            alert('Bulk activate functionality will be implemented');
        }
    }

    function deactivateAll() {
        if (confirm('Are you sure you want to deactivate all clinics?')) {
            alert('Bulk deactivate functionality will be implemented');
        }
    }

    function generateReport() {
        alert('Generate report functionality will be implemented');
    }

    // Auto-refresh data every 30 seconds
    setInterval(function() {
        // Update department stats without full page reload
        // fetch('/admin/departments/stats').then(response => response.json()).then(data => {
        //     // Update stats display
        // });
    }, 30000);
</script>
@endpush

