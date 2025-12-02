@extends('layouts.staff')

@section('title', 'My Services')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1 fw-bold">My Services</h2>
                    <p class="text-muted mb-0">Manage your service pricing and availability</p>
                </div>
                <div>
                    <a href="{{ route('staff.doctor-services.create') }}" class="btn btn-primary me-2">
                        <i class="fas fa-plus me-2"></i>Add Service
                    </a>
                    <a href="{{ route('staff.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
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

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">Available Services</h5>
                    <small class="text-muted">Customize pricing and duration for each service, or use global defaults</small>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 25%;">Service Name</th>
                                    <th style="width: 15%;">Duration</th>
                                    <th style="width: 15%;">Price</th>
                                    <th style="width: 15%;">Status</th>
                                    <th style="width: 15%;">Override</th>
                                    <th style="width: 15%;" class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($services as $service)
                                    <tr>
                                        <td>
                                            <div>
                                                <div class="fw-semibold">{{ $service['name'] }}</div>
                                                @if($service['description'])
                                                <small class="text-muted">{{ Str::limit($service['description'], 50) }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @if($service['has_override'] && $service['custom_duration_minutes'])
                                                <span class="badge bg-info text-dark">{{ $service['custom_duration_minutes'] }} min</span>
                                                <small class="text-muted d-block">(Custom)</small>
                                            @else
                                                <span class="badge bg-light text-dark">{{ $service['default_duration_minutes'] ?? 60 }} min</span>
                                                <small class="text-muted d-block">(Default)</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service['has_override'] && $service['custom_price'] !== null)
                                                <strong>£{{ number_format($service['custom_price'], 2) }}</strong>
                                                <small class="text-muted d-block">(Custom)</small>
                                            @elseif($service['default_price'])
                                                <strong>£{{ number_format($service['default_price'], 2) }}</strong>
                                                <small class="text-muted d-block">(Default)</small>
                                            @else
                                                <span class="text-muted">On request</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service['is_active_for_doctor'])
                                            <span class="badge bg-success">Active</span>
                                            @else
                                            <span class="badge bg-secondary">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($service['has_override'])
                                            <span class="badge bg-primary">Yes</span>
                                            @else
                                            <span class="badge bg-light text-dark">No</span>
                                            @endif
                                        </td>
                                        <td class="text-end">
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('staff.doctor-services.edit', $service['id']) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   title="Edit Service">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('staff.doctor-services.toggle-status', $service['id']) }}" 
                                                      method="POST" 
                                                      class="d-inline">
                                                    @csrf
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-{{ $service['is_active_for_doctor'] ? 'warning' : 'success' }}"
                                                            title="{{ $service['is_active_for_doctor'] ? 'Deactivate' : 'Activate' }}">
                                                        <i class="fas fa-{{ $service['is_active_for_doctor'] ? 'eye-slash' : 'eye' }}"></i>
                                                    </button>
                                                </form>
                                                @if($service['has_override'])
                                                <form action="{{ route('staff.doctor-services.destroy', $service['id']) }}" 
                                                      method="POST" 
                                                      class="d-inline"
                                                      onsubmit="return confirm('Remove custom settings and use global defaults?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="btn btn-sm btn-outline-danger"
                                                            title="Remove Override">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No services available.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

