@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('admin.layouts.app')

@section('title', 'Department Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title mb-0">Department Details</h3>
                    <div>
                        <a href="{{ contextRoute('departments.edit', $department->id) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-4">
                                <h4 class="text-primary d-flex align-items-center">
                                    {{ $department->name }}
                                    @if($department->is_emergency)
                                        <span class="badge bg-danger ms-2">Emergency</span>
                                    @endif
                                </h4>
                                
                                @if($department->image)
                                    <img src="{{ Storage::disk('public')->url('uploads/departments/' . $department->image) }}" 
                                         alt="{{ $department->name }}" 
                                         class="img-fluid rounded mb-3" 
                                         style="max-height: 300px;">
                                @endif
                            </div>

                            <div class="mb-4">
                                <h5 class="text-primary">Description</h5>
                                <p class="text-muted">{{ $department->description }}</p>
                            </div>

                            @if($department->services && count($department->services) > 0)
                                <div class="mb-4">
                                    <h5 class="text-primary">Services</h5>
                                    <div class="row">
                                        @foreach($department->services as $service)
                                            <div class="col-md-6 mb-2">
                                                <span class="badge bg-secondary">{{ $service }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($department->operating_hours)
                                <div class="mb-4">
                                    <h5 class="text-primary">Operating Hours</h5>
                                    <p class="text-muted">{{ $department->operating_hours }}</p>
                                </div>
                            @endif

                            @if($todayAppointments->count() > 0)
                                <div class="mb-4">
                                    <h5 class="text-primary">Today's Appointments</h5>
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Time</th>
                                                    <th>Patient</th>
                                                    <th>Doctor</th>
                                                    <th>Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($todayAppointments as $appointment)
                                                    <tr>
                                                        <td>{{ $appointment->appointment_time }}</td>
                                                        <td>{{ $appointment->patient->name ?? 'N/A' }}</td>
                                                        <td>{{ $appointment->doctor->full_name ?? 'N/A' }}</td>
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
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Department Information</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-5">Status:</dt>
                                        <dd class="col-sm-7">
                                            <span class="badge {{ $department->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $department->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </dd>

                                        <dt class="col-sm-5">Type:</dt>
                                        <dd class="col-sm-7">
                                            @if($department->is_emergency)
                                                <span class="badge bg-danger">Emergency</span>
                                            @else
                                                <span class="badge bg-info">Regular</span>
                                            @endif
                                        </dd>

                                        <dt class="col-sm-5">Head:</dt>
                                        <dd class="col-sm-7">
                                            {{ $department->head_of_department ?: 'Not specified' }}
                                        </dd>

                                        <dt class="col-sm-5">Location:</dt>
                                        <dd class="col-sm-7">
                                            {{ $department->location ?: 'Not specified' }}
                                        </dd>

                                        <dt class="col-sm-5">Phone:</dt>
                                        <dd class="col-sm-7">
                                            {{ $department->phone ?: 'Not specified' }}
                                        </dd>

                                        <dt class="col-sm-5">Email:</dt>
                                        <dd class="col-sm-7">
                                            {{ $department->email ?: 'Not specified' }}
                                        </dd>

                                        <dt class="col-sm-5">Created:</dt>
                                        <dd class="col-sm-7">{{ formatDate($department->created_at) }}</dd>

                                        <dt class="col-sm-5">Updated:</dt>
                                        <dd class="col-sm-7">{{ formatDate($department->updated_at) }}</dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Statistics</h6>
                                </div>
                                <div class="card-body">
                                    <dl class="row">
                                        <dt class="col-sm-6">Doctors:</dt>
                                        <dd class="col-sm-6">{{ $department->doctors_count ?? 0 }}</dd>

                                        <dt class="col-sm-6">Appointments:</dt>
                                        <dd class="col-sm-6">{{ $department->appointments_count ?? 0 }}</dd>

                                        <dt class="col-sm-6">Services:</dt>
                                        <dd class="col-sm-6">{{ $department->services ? count($department->services) : 0 }}</dd>
                                    </dl>
                                </div>
                            </div>

                            <div class="card mt-3">
                                <div class="card-header">
                                    <h6 class="card-title mb-0">Quick Actions</h6>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <button class="btn btn-{{ $department->is_active ? 'warning' : 'success' }} btn-sm toggle-status" 
                                                data-url="{{ contextRoute('departments.toggle-status', $department->id) }}">
                                            <i class="fas fa-toggle-{{ $department->is_active ? 'on' : 'off' }}"></i>
                                            {{ $department->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        
                                        <a href="{{ contextRoute('departments.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus"></i> Add New Department
                                        </a>
                                        
                                        <a href="{{ contextRoute('doctors.index') }}?department_id={{ $department->id }}" class="btn btn-info btn-sm">
                                            <i class="fas fa-user-md"></i> View Doctors
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer">
                    <div class="d-flex justify-content-between">
                        <a href="{{ contextRoute('departments.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Clinics
                        </a>
                        <div>
                            <a href="{{ contextRoute('departments.edit', $department->id) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Department
                            </a>
                            <button type="button" class="btn btn-danger" onclick="deleteDepartment({{ $department->id }})">
                                <i class="fas fa-trash"></i> Delete Department
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
                    toastr.error('Error updating department status');
                }
            },
            error: function() {
                toastr.error('Error updating department status');
            }
        });
    });

    // Delete confirmation - using comprehensive confirmation dialog
    window.deleteDepartment = function(departmentId) {
        console.log('Delete department called with ID:', departmentId);
        
        // Prevent any default behavior if event exists
        if (window.event) {
            window.event.preventDefault();
            window.event.stopPropagation();
        }
        
        // Check if department has doctors assigned
        const doctorsCount = {{ $department->doctors_count ?? 0 }};
        console.log('Department has', doctorsCount, 'doctors assigned');
        
        // If department has doctors, show informative message instead of delete confirmation
        if (doctorsCount > 0) {
            alert('🚫 Cannot Delete Clinic\n\nThis clinic has ' + doctorsCount + ' doctor(s) assigned to it.\n\nTo delete this clinic, you must first:\n1. Reassign all doctors to other clinics\n2. Or remove the doctors from the system\n\nThen you can delete this clinic.\n\nClick "View Doctors" to see the assigned doctors.');
            return false;
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
        
        // Use a more explicit confirmation dialog (only shown if no doctors assigned)
        const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this department?\n\nThis action cannot be undone and will remove all department data including:\n- Department information\n- Services and appointments\n- Department statistics\n- Working hours and contact details\n\nClick OK to confirm deletion or Cancel to abort.');
        
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
