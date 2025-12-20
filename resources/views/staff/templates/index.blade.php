@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Letters & Forms Templates')
@section('page-title', 'Letters & Forms Templates')

@section('content')
<div class="fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">
                        <i class="fas fa-file-medical me-2 text-primary"></i>Letters & Forms Templates
                    </h1>
                    <p class="text-muted mb-0">NHS/CQC compliant document templates</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('staff.generated-documents.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-file-pdf me-2"></i>My Documents
                    </a>
                    @can('create', \App\Models\Template::class)
                    <div class="btn-group">
                        <a href="{{ route('staff.templates.create', ['type' => 'letter']) }}" class="btn btn-doctor-primary">
                            <i class="fas fa-plus me-2"></i>Create Letter
                        </a>
                        <a href="{{ route('staff.templates.create', ['type' => 'form']) }}" class="btn btn-success">
                            <i class="fas fa-plus me-2"></i>Create Form
                        </a>
                    </div>
                    @endcan
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

    <!-- Filters -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-body">
            @php
                $baseQuery = request()->except(['page', 'type']);
                $allUrl = route('staff.templates.index', array_merge($baseQuery, ['type' => null]));
                $lettersUrl = route('staff.templates.index', array_merge($baseQuery, ['type' => 'letter']));
                $formsUrl = route('staff.templates.index', array_merge($baseQuery, ['type' => 'form']));
                $activeType = request('type');
            @endphp

            <div class="d-flex flex-wrap gap-2 mb-3">
                <a href="{{ $allUrl }}" class="btn btn-sm {{ empty($activeType) ? 'btn-doctor-primary' : 'btn-outline-secondary' }}">
                    <i class="fas fa-layer-group me-1"></i>All
                </a>
                <a href="{{ $lettersUrl }}" class="btn btn-sm {{ $activeType === 'letter' ? 'btn-doctor-primary' : 'btn-outline-secondary' }}">
                    <i class="fas fa-envelope me-1"></i>Letters
                </a>
                <a href="{{ $formsUrl }}" class="btn btn-sm {{ $activeType === 'form' ? 'btn-doctor-primary' : 'btn-outline-secondary' }}">
                    <i class="fas fa-clipboard-list me-1"></i>Forms
                </a>
            </div>

            <form method="GET" action="{{ route('staff.templates.index') }}" id="templateSearchForm">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label">Search Templates</label>
                        <input type="text" name="search" id="templateSearchInput" class="form-control"
                               placeholder="Search by name..." value="{{ request('search') }}"
                               autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Type</label>
                        <select name="type" id="templateTypeSelect" class="form-control">
                            <option value="">All Types</option>
                            <option value="letter" {{ request('type') == 'letter' ? 'selected' : '' }}>Letters</option>
                            <option value="form" {{ request('type') == 'form' ? 'selected' : '' }}>Forms</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-doctor-primary w-100" id="searchButton">
                            <i class="fas fa-search me-1"></i>Search
                        </button>
                    </div>
                    @if(request()->anyFilled(['search', 'type']))
                    <div class="col-md-2">
                        <a href="{{ route('staff.templates.index') }}" class="btn btn-outline-secondary w-100" id="clearButton">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Templates Grid -->
    <div class="row">
        @forelse($templates as $template)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="doctor-card h-100">
                <div class="doctor-card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-{{ $template->type === 'letter' ? 'primary' : 'info' }}">
                            <i class="fas fa-{{ $template->type === 'letter' ? 'envelope' : 'clipboard-list' }} me-1"></i>
                            {{ ucfirst($template->type) }}
                        </span>
                        @if($template->is_system)
                            <span class="badge bg-secondary">System</span>
                        @endif
                    </div>

                    <h5 class="card-title mb-2">{{ $template->name }}</h5>
                    <p class="text-muted small mb-3">
                        Created {{ $template->created_at->diffForHumans() }}
                        @if($template->creator)
                            by {{ $template->creator->name }}
                        @endif
                    </p>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="{{ route('staff.generated-documents.create', ['template_id' => $template->id]) }}"
                           class="btn btn-sm btn-success flex-fill">
                            <i class="fas fa-file-pdf me-1"></i>Generate
                        </a>
                        <a href="{{ route('staff.templates.show', $template) }}"
                           class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-eye"></i>
                        </a>
                        @can('update', $template)
                        <a href="{{ route('staff.templates.edit', $template) }}"
                           class="btn btn-sm btn-outline-secondary">
                            <i class="fas fa-edit"></i>
                        </a>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h5 class="text-muted">No templates found</h5>
                <p class="text-muted">No templates match your search criteria.</p>
            </div>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if($templates->hasPages())
    <div class="d-flex justify-content-center mt-4">
        {{ $templates->links() }}
    </div>
    @endif
</div>

@push('scripts')
<script>
(function() {
    'use strict';
    
    // Wait for DOM to be ready
    function initTemplateSearch() {
        // Get form elements
        const searchForm = document.getElementById('templateSearchForm');
        const searchInput = document.getElementById('templateSearchInput');
        const typeSelect = document.getElementById('templateTypeSelect');
        const searchButton = document.getElementById('searchButton');
        const clearButton = document.getElementById('clearButton');
        
        if (!searchForm) {
            return; // Exit if form doesn't exist
        }
        
        // Debounce function to delay search execution
        let searchTimeout = null;
        
        function debounce(func, wait) {
            return function executedFunction(...args) {
                const later = function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = null;
                    func(...args);
                };
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(later, wait);
            };
        }
        
        // Function to submit the search form
        function performSearch() {
            if (searchForm) {
                // Add loading state
                if (searchButton) {
                    const originalHtml = searchButton.innerHTML;
                    searchButton.disabled = true;
                    searchButton.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Searching...';
                    
                    // Submit the form
                    searchForm.submit();
                    
                    // Restore button after a delay (in case form doesn't submit)
                    setTimeout(function() {
                        searchButton.disabled = false;
                        searchButton.innerHTML = originalHtml;
                    }, 2000);
                } else {
                    searchForm.submit();
                }
            }
        }
        
        // Debounced search function (wait 500ms after user stops typing)
        const debouncedSearch = debounce(performSearch, 500);
        
        // Event listener for search input (real-time search as user types)
        if (searchInput) {
            // Track if user has interacted with the input
            let hasUserInteracted = false;
            
            searchInput.addEventListener('focus', function() {
                hasUserInteracted = true;
            });
            
            searchInput.addEventListener('input', function(e) {
                if (hasUserInteracted) {
                    // Trigger debounced search
                    debouncedSearch();
                }
            });
            
            // Also trigger search on Enter key
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    searchTimeout = null;
                    performSearch();
                }
            });
        }
        
        // Event listener for type select change
        if (typeSelect) {
            typeSelect.addEventListener('change', function() {
                clearTimeout(searchTimeout);
                searchTimeout = null;
                performSearch();
            });
        }
        
        // Event listener for clear button (if it exists)
        if (clearButton) {
            clearButton.addEventListener('click', function(e) {
                e.preventDefault();
                // Navigate to clean URL (no need to clear inputs since we're navigating)
                window.location.href = clearButton.href;
            });
        }
        
        // Handle form submission (manual button click)
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                // Clear any pending debounced search
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                    searchTimeout = null;
                }
                // Let the form submit normally
            });
        }
    }
    
    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initTemplateSearch);
    } else {
        // DOM is already ready
        initTemplateSearch();
    }
})();
</script>
@endpush
@endsection
