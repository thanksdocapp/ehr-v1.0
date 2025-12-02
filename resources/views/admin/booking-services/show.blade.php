@extends('admin.layouts.app')

@section('title', 'View Booking Service')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h4 class="mb-0 fw-bold">Service Details</h4>
                    <div>
                        <a href="{{ route('admin.booking-services.assign-doctor', $bookingService) }}" class="btn btn-success me-2">
                            <i class="fas fa-user-plus me-2"></i>Assign to Doctor
                        </a>
                        <a href="{{ route('admin.booking-services.edit', $bookingService) }}" class="btn btn-primary me-2">
                            <i class="fas fa-edit me-2"></i>Edit
                        </a>
                        <a href="{{ route('admin.booking-services.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="200">Service Name:</th>
                                    <td><strong>{{ $bookingService->name }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Description:</th>
                                    <td>{{ $bookingService->description ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Duration:</th>
                                    <td><span class="badge bg-light text-dark">{{ $bookingService->default_duration_minutes }} minutes</span></td>
                                </tr>
                                <tr>
                                    <th>Default Price:</th>
                                    <td>
                                        @if($bookingService->default_price)
                                        <strong>£{{ number_format($bookingService->default_price, 2) }}</strong>
                                        @else
                                        <span class="text-muted">Price on request</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Tags:</th>
                                    <td>
                                        @if($bookingService->tags && count($bookingService->tags) > 0)
                                            @foreach($bookingService->tags as $tag)
                                            <span class="badge bg-secondary me-1">{{ $tag }}</span>
                                            @endforeach
                                        @else
                                        <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        @if($bookingService->is_active)
                                        <span class="badge bg-success">Active</span>
                                        @else
                                        <span class="badge bg-secondary">Inactive</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created By:</th>
                                    <td>{{ $bookingService->creator->name ?? 'System' }}</td>
                                </tr>
                                <tr>
                                    <th>Created At:</th>
                                    <td>{{ $bookingService->created_at->format('F d, Y \a\t g:i A') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Doctors Using This Service -->
            @if($doctorsUsingService->count() > 0)
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Doctors Using This Service</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Doctor</th>
                                    <th>Custom Price</th>
                                    <th>Custom Duration</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($doctorsUsingService as $doctor)
                                <tr>
                                    <td>{{ $doctor->full_name }}</td>
                                    <td>
                                        @php
                                            $doctorPrice = $doctor->servicePrices->firstWhere('service_id', $bookingService->id);
                                        @endphp
                                        @if($doctorPrice && $doctorPrice->custom_price)
                                        <strong>£{{ number_format($doctorPrice->custom_price, 2) }}</strong>
                                        @else
                                        <span class="text-muted">Uses default</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($doctorPrice && $doctorPrice->custom_duration_minutes)
                                        <span class="badge bg-light text-dark">{{ $doctorPrice->custom_duration_minutes }} min</span>
                                        @else
                                        <span class="text-muted">Uses default</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($doctorPrice && $doctorPrice->is_active)
                                        <span class="badge bg-success">Active</span>
                                        @else
                                        <span class="badge bg-secondary">Inactive</span>
                                        @endif
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
    </div>
</div>
@endsection

