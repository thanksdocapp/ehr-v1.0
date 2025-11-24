@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('admin.layouts.app')

@section('title', 'Doctor Details')

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="modern-page-title">{{ $doctor->title }} {{ $doctor->first_name }} {{ $doctor->last_name }}</h1>
                    <p class="modern-page-subtitle">{{ $doctor->specialization }}</p>
                </div>
                <div class="mt-3 mt-md-0">
                    <a href="{{ contextRoute('doctors.edit', $doctor->id) }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-edit me-2"></i>Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-12">
            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title mb-0">
                        <i class="fas fa-user-md"></i>Doctor Details
                    </h5>
                </div>
                
                <div class="modern-card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-3">
                                    @if($doctor->photo)
                                        <img src="{{ Storage::disk('public')->url('uploads/doctors/' . $doctor->photo) }}" 
                                             alt="{{ $doctor->full_name }}" 
                                             class="img-fluid rounded mb-3" 
                                             style="max-height: 200px;">
                                    @else
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center mb-3" style="height: 200px;">
                                            <i class="fas fa-user-md text-muted fa-4x"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-9">
                                    <div class="mb-3">
                                        <h4 class="text-primary mb-2">
                                            {{ $doctor->title }} {{ $doctor->first_name }} {{ $doctor->last_name }}
                                            @if($doctor->is_featured)
                                                <span class="badge bg-warning text-dark ms-2">Featured</span>
                                            @endif
                                        </h4>
                                        <h6 class="text-muted">{{ $doctor->specialization }}</h6>
                                        <p class="text-muted mb-1">
                                            <i class="fas fa-building me-2"></i>
                                            <strong>Departments:</strong>
                                            @php
                                                $allDepartments = $doctor->departments->isNotEmpty() 
                                                    ? $doctor->departments 
                                                    : collect([$doctor->department])->filter();
                                            @endphp
                                            @if($allDepartments->isNotEmpty())
                                                @foreach($allDepartments as $dept)
                                                    <span class="badge bg-primary me-1">
                                                        {{ $dept->name }}
                                                        @if($dept->pivot && $dept->pivot->is_primary)
                                                            <span class="badge bg-warning text-dark">Primary</span>
                                                        @elseif(!$dept->pivot && $dept->id == $doctor->department_id)
                                                            <span class="badge bg-warning text-dark">Primary</span>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-danger">Not assigned</span>
                                            @endif
                                        </p>
                                    </div>
                                </div>
                            </div>

                            @if($doctor->languages && is_array($doctor->languages) && count($doctor->languages) > 0)
                                <div class="mb-4">
                                    <h5 class="text-primary">Languages</h5>
                                    <div class="row">
                                        @foreach($doctor->languages as $language)
                                            <div class="col-md-4 mb-2">
                                                <span class="badge bg-info">{{ $language }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($doctor->specialties && is_array($doctor->specialties) && count($doctor->specialties) > 0)
                                <div class="mb-4">
                                    <h5 class="text-primary">Specialties</h5>
                                    <div class="row">
                                        @foreach($doctor->specialties as $specialty)
                                            <div class="col-md-6 mb-2">
                                                <span class="badge bg-secondary">{{ $specialty }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($doctor->availability && is_array($doctor->availability) && count($doctor->availability) > 0)
                                <div class="mb-4">
                                    <h5 class="text-primary">Weekly Availability</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Day</th>
                                                    <th>Available</th>
                                                    <th>Time</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
                                                @endphp
                                                @foreach($days as $day => $dayName)
                                                    @php
                                                        $dayAvailability = $doctor->availability[$day] ?? [];
                                                    @endphp
                                                    <tr>
                                                        <td class="fw-bold">{{ $dayName }}</td>
                                                        <td>
                                                            @if(($dayAvailability['available'] ?? false))
                                                                <span class="badge bg-success">Yes</span>
                                                            @else
                                                                <span class="badge bg-secondary">No</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if(($dayAvailability['available'] ?? false))
                                                                {{ $dayAvailability['from'] ?? 'N/A' }} - {{ $dayAvailability['to'] ?? 'N/A' }}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            @if(isset($upcomingAppointments) && $upcomingAppointments->count() > 0)
                                <div class="mb-4">
                                    <h5 class="text-primary">Upcoming Appointments</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Date</th>
                                                    <th>Time</th>
                                                    <th>Patient</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($upcomingAppointments as $appointment)
                                                    <tr>
                                                        <td>{{ $appointment->appointment_date }}</td>
                                                        <td>{{ $appointment->appointment_time }}</td>
                                                        <td>{{ $appointment->patient->name ?? 'N/A' }}</td>
                                                        <td>
                                                            <span class="badge bg-{{ $appointment->status == 'confirmed' ? 'success' : ($appointment->status == 'pending' ? 'warning' : 'danger') }}">
                                                                {{ ucfirst($appointment->status) }}
                                                            </span>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-4">
                            <div class="doctor-card">
                                <div class="doctor-card-header">
                                    <h6 class="doctor-card-title mb-0">Doctor Information</h6>
                                </div>
                                <div class="doctor-card-body">
                                    <dl class="row">
                                        <dt class="col-sm-5">Status:</dt>
                                        <dd class="col-sm-7">
                                            <span class="badge {{ $doctor->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $doctor->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-5">Featured:</dt>
                                        <dd class="col-sm-7">
                                            <span class="badge {{ $doctor->is_featured ? 'bg-warning text-dark' : 'bg-secondary' }}">
                                                {{ $doctor->is_featured ? 'Yes' : 'No' }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-5">Departments:</dt>
                                        <dd class="col-sm-7">
                                            @php
                                                $allDepartments = $doctor->departments->isNotEmpty() 
                                                    ? $doctor->departments 
                                                    : collect([$doctor->department])->filter();
                                            @endphp
                                            @if($allDepartments->isNotEmpty())
                                                @foreach($allDepartments as $dept)
                                                    <span class="badge bg-primary me-1 mb-1">
                                                        {{ $dept->name }}
                                                        @if($dept->pivot && $dept->pivot->is_primary)
                                                            <span class="badge bg-warning text-dark ms-1">Primary</span>
                                                        @elseif(!$dept->pivot && $dept->id == $doctor->department_id)
                                                            <span class="badge bg-warning text-dark ms-1">Primary</span>
                                                        @endif
                                                    </span>
                                                @endforeach
                                            @else
                                                <span class="text-danger">Not assigned</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-5">Specialisation:</dt>
                                        <dd class="col-sm-7">{{ $doctor->specialization }}</dd>


                                        @if($doctor->email)
                                            <dt class="col-sm-5">Email:</dt>
                                            <dd class="col-sm-7">{{ $doctor->email }}</dd>
                                        @endif

                                        @if($doctor->phone)
                                            <dt class="col-sm-5">Phone:</dt>
                                            <dd class="col-sm-7">{{ $doctor->phone }}</dd>
                                        @endif

                                        <dt class="col-sm-5">Online:</dt>
                                        <dd class="col-sm-7">
                                            <span class="badge {{ $doctor->is_available_online ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $doctor->is_available_online ? 'Available' : 'Not Available' }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-5">Created:</dt>
                                        <dd class="col-sm-7">{{ formatDate($doctor->created_at) }}</dd>

                                        <dt class="col-sm-5">Updated:</dt>
                                        <dd class="col-sm-7">{{ formatDate($doctor->updated_at) }}</dd>
                                    </dl>
                                </div>
                            </div>

                            @if(isset($todayAppointments) && $todayAppointments->count() > 0)
                                <div class="doctor-card mt-3">
                                    <div class="doctor-card-header">
                                        <h6 class="doctor-card-title mb-0">Today's Appointments</h6>
                                    </div>
                                    <div class="doctor-card-body">
                                        @foreach($todayAppointments as $appointment)
                                            <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                                <div>
                                                    <div class="fw-bold">{{ $appointment->appointment_time }}</div>
                                                    <small class="text-muted">{{ $appointment->patient->name ?? 'N/A' }}</small>
                                                </div>
                                                <span class="badge bg-{{ $appointment->status == 'confirmed' ? 'success' : ($appointment->status == 'pending' ? 'warning' : 'danger') }}">
                                                    {{ ucfirst($appointment->status) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <div class="doctor-card mt-3">
                                <div class="doctor-card-header">
                                    <h6 class="doctor-card-title mb-0">Quick Actions</h6>
                                </div>
                                <div class="doctor-card-body">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-{{ $doctor->is_active ? 'warning' : 'success' }} btn-sm toggle-status" 
                                                data-url="{{ contextRoute('doctors.toggle-status', $doctor->id) }}">
                                            <i class="fas fa-toggle-{{ $doctor->is_active ? 'on' : 'off' }}"></i>
                                            {{ $doctor->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        
                                        <a href="{{ contextRoute('doctors.create') }}" class="btn btn-doctor-primary btn-sm">
                                            <i class="fas fa-plus"></i> Add New Doctor
                                        </a>
                                        
                                        <a href="{{ contextRoute('departments.show', $doctor->department->id) }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-building"></i> View Department
                                        </a>

                                        @if(isset($doctor->schedule))
                                            <a href="{{ contextRoute('doctors.schedule', $doctor->id) }}" class="btn btn-secondary btn-sm">
                                                <i class="fas fa-calendar"></i> View Schedule
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            @if(isset($recentTestimonials) && $recentTestimonials->count() > 0)
                                <div class="doctor-card mt-3">
                                    <div class="doctor-card-header">
                                        <h6 class="doctor-card-title mb-0">Recent Reviews</h6>
                                    </div>
                                    <div class="doctor-card-body">
                                        @foreach($recentTestimonials as $testimonial)
                                            <div class="mb-3 pb-2 border-bottom">
                                                <div class="d-flex justify-content-between">
                                                    <small class="fw-bold">{{ $testimonial->patient_name }}</small>
                                                    <small class="text-muted">{{ $testimonial->created_at->format('M d') }}</small>
                                                </div>
                                                <p class="small text-muted mb-0">{{ Str::limit($testimonial->review, 100) }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="doctor-card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ contextRoute('doctors.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Doctors
                        </a>
                        <div>
                            <a href="{{ contextRoute('doctors.edit', $doctor->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Doctor
                            </a>
                            <button type="button" class="btn btn-danger" onclick="deleteDoctor({{ $doctor->id }})">
                                <i class="fas fa-trash"></i> Delete Doctor
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Toggle status
    $('.toggle-status').click(function() {
        let button = $(this);
        let url = button.data('url');
        
        $.ajax({
            url: url,
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    location.reload(); // Reload to show updated status
                } else {
                    toastr.error('Error updating doctor status');
                }
            },
            error: function() {
                toastr.error('Error updating doctor status');
            }
        });
    });

    // Delete confirmation - using comprehensive confirmation dialog
    window.deleteDoctor = function(doctorId) {
        console.log('Delete doctor called with ID:', doctorId);
        
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
                    form.action = `/admin/doctors/${doctorId}`;
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
        const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this doctor?\n\nThis action cannot be undone and will remove all doctor data including:\n- Personal information\n- Professional credentials\n- Appointment history\n- Patient relationships\n\nClick OK to confirm deletion or Cancel to abort.');
        
        // Handle both Promise and boolean returns
        if (confirmDelete && typeof confirmDelete.then === 'function') {
            // If it's a Promise, wait for it to resolve
            confirmDelete.then(handleConfirmation).catch(() => handleConfirmation(false));
        } else {
            // If it's a boolean, handle it directly
            handleConfirmation(confirmDelete);
        }
        
        return false;
    };
});
</script>
@endpush
