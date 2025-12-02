@extends('admin.layouts.app')

@section('title', 'Booking Services Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <h4 class="mb-0 fw-bold">Booking Services</h4>
                    <a href="{{ route('admin.booking-services.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>Add New Service
                    </a>
                </div>
                
                <div class="card-body">
                    <!-- Search and Filter Form -->
                    <form method="GET" action="{{ route('admin.booking-services.index') }}" class="row g-3 mb-4">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control" 
                                   placeholder="Search services..." 
                                   value="{{ request('search') }}">
                        </div>
                        <div class="col-md-3">
                            <select name="status" class="form-select">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <button type="submit" class="btn btn-outline-primary me-2">Search</button>
                            <a href="{{ route('admin.booking-services.index') }}" class="btn btn-outline-secondary">Clear</a>
                        </div>
                    </form>

                    <!-- Services Table -->
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Service Name</th>
                                    <th>Duration</th>
                                    <th>Price</th>
                                    <th>Tags</th>
                                    <th>Status</th>
                                    <th>Created By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    <tr>
                                        <td>
                                            <div>
                                                <div class="fw-semibold">{{ $service->name }}</div>
                                                @if($service->description)
                                                <small class="text-muted">{{ Str::limit($service->description, 60) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">{{ $service->default_duration_minutes }} min</span>
                                        </td>
                                        <td>
                                            @if($service->default_price)
                                            <strong>£{{ number_format($service->default_price, 2) }}</strong>
                                            @else
                                            <span class="text-muted">On request</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->tags && count($service->tags) > 0)
                                                @foreach(array_slice($service->tags, 0, 2) as $tag)
                                                <span class="badge bg-secondary me-1">{{ $tag }}</span>
                                                @endforeach
                                                @if(count($service->tags) > 2)
                                                <span class="text-muted">+{{ count($service->tags) - 2 }}</span>
                                                @endif
                                            @else
                                            <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service->is_active)
                                            <span class="badge bg-success">Active</span>
                                            @else
                                            <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ $service->creator->name ?? 'System' }}
                                                <br>
                                                <span class="text-muted">{{ $service->created_at->format('M d, Y') }}</span>
                                            </small>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.booking-services.show', $service) }}" 
                                                   class="btn btn-sm btn-outline-info" 
                                                   title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.booking-services.edit', $service) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('admin.booking-services.toggle-status', $service) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-{{ $service->is_active ? 'warning' : 'success' }}"
                                                            title="{{ $service->is_active ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fas fa-{{ $service->is_active ? 'pause' : 'play' }}"></i>
                                                    </button>
                                                </form>
                                                <form action="{{ route('admin.booking-services.destroy', $service) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Are you sure you want to deactivate this service?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Delete">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No booking services found.</p>
                                            <a href="{{ route('admin.booking-services.create') }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>Create First Service
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($services->hasPages())
                    <div class="mt-4">
                        {{ $services->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

