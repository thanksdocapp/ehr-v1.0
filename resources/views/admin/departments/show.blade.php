@php
use Illuminate\Support\Facades\Storage;
@endphp

@extends('admin.layouts.app')

@section('title', 'Department Details')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">{{ $department->name }}</h1>
            <p class="mb-0 text-muted">Department Overview & Statistics</p>
        </div>
        <div>
            <a href="{{ contextRoute('departments.edit', $department->id) }}" class="btn btn-warning">
                <i class="fas fa-edit"></i> Edit Department
            </a>
            <a href="{{ contextRoute('departments.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Clinics
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Patients Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                Total Patients
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $patientStats['total'] }}</div>
                            <div class="text-xs text-muted mt-1">
                                <span class="text-success">{{ $patientStats['active'] }} active</span> | 
                                <span>{{ $patientStats['this_month'] }} this month</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointments Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Appointments
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $appointmentStats['total'] }}</div>
                            <div class="text-xs text-muted mt-1">
                                <span class="text-info">{{ $appointmentStats['today'] }} today</span> | 
                                <span class="text-warning">{{ $appointmentStats['upcoming'] }} upcoming</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Doctors Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                Doctors
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $doctorStats['total'] }}</div>
                            <div class="text-xs text-muted mt-1">
                                <span class="text-success">{{ $doctorStats['active'] }} active</span> | 
                                <span>{{ $doctorStats['available'] }} available</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-md fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Medical Records Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                Medical Records
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $medicalRecordStats['total'] }}</div>
                            <div class="text-xs text-muted mt-1">
                                <span>{{ $medicalRecordStats['this_month'] }} this month</span>
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-medical fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Department Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">Department Information</h6>
                    @if($department->is_emergency)
                        <span class="badge bg-danger">Emergency Department</span>
                    @endif
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            @if($department->image)
                                <img src="{{ Storage::disk('public')->url('uploads/departments/' . $department->image) }}" 
                                     alt="{{ $department->name }}" 
                                     class="img-fluid rounded mb-3" 
                                     style="max-height: 250px; width: 100%; object-fit: cover;">
                            @endif
                        </div>
                        <div class="col-md-6">
                            <dl class="row mb-0">
                                <dt class="col-sm-5">Status:</dt>
                                <dd class="col-sm-7">
                                    <span class="badge {{ $department->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $department->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </dd>

                                <dt class="col-sm-5">Head of Department:</dt>
                                <dd class="col-sm-7">{{ $department->head_of_department ?: 'Not specified' }}</dd>

                                <dt class="col-sm-5">Location:</dt>
                                <dd class="col-sm-7">{{ $department->location ?: 'Not specified' }}</dd>

                                <dt class="col-sm-5">Phone:</dt>
                                <dd class="col-sm-7">
                                    @if($department->phone)
                                        <a href="tel:{{ $department->phone }}">{{ $department->phone }}</a>
                                    @else
                                        Not specified
                                    @endif
                                </dd>

                                <dt class="col-sm-5">Email:</dt>
                                <dd class="col-sm-7">
                                    @if($department->email)
                                        <a href="mailto:{{ $department->email }}">{{ $department->email }}</a>
                                    @else
                                        Not specified
                                    @endif
                                </dd>

                                @if($department->operating_hours)
                                <dt class="col-sm-5">Operating Hours:</dt>
                                <dd class="col-sm-7">{{ $department->operating_hours }}</dd>
                                @endif
                            </dl>
                        </div>
                    </div>

                    @if($department->description)
                    <div class="mt-3">
                        <h6 class="text-primary">Description</h6>
                        <p class="text-muted">{{ $department->description }}</p>
                    </div>
                    @endif

                    @if($department->services && count($department->services) > 0)
                    <div class="mt-3">
                        <h6 class="text-primary">Services Offered</h6>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($department->services as $service)
                                <span class="badge bg-secondary">{{ $service }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Detailed Statistics -->
            <div class="row mb-4">
                <!-- Appointments Statistics -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Appointment Statistics</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="border-right">
                                        <div class="h4 mb-0 text-success">{{ $appointmentStats['completed'] }}</div>
                                        <small class="text-muted">Completed</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h4 mb-0 text-warning">{{ $appointmentStats['pending'] }}</div>
                                    <small class="text-muted">Pending</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 mb-0 text-danger">{{ $appointmentStats['cancelled'] }}</div>
                                    <small class="text-muted">Cancelled</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 mb-0 text-info">{{ $appointmentStats['this_month'] }}</div>
                                    <small class="text-muted">This Month</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Prescriptions & Lab Reports -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">Prescriptions & Lab Reports</h6>
                        </div>
                        <div class="card-body">
                            <div class="row text-center">
                                <div class="col-6 mb-3">
                                    <div class="h4 mb-0 text-primary">{{ $prescriptionStats['total'] }}</div>
                                    <small class="text-muted">Total Prescriptions</small>
                                    <div class="mt-2">
                                        <small class="text-warning">{{ $prescriptionStats['pending'] }} pending</small>
                                    </div>
                                </div>
                                <div class="col-6 mb-3">
                                    <div class="h4 mb-0 text-info">{{ $labReportStats['total'] }}</div>
                                    <small class="text-muted">Total Lab Reports</small>
                                    <div class="mt-2">
                                        <small class="text-warning">{{ $labReportStats['pending'] }} pending</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="h4 mb-0 text-success">{{ $prescriptionStats['this_month'] }}</div>
                                    <small class="text-muted">Prescriptions This Month</small>
                                </div>
                                <div class="col-6">
                                    <div class="h4 mb-0 text-success">{{ $labReportStats['this_month'] }}</div>
                                    <small class="text-muted">Lab Reports This Month</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Today's Appointments -->
            @if($todayAppointments->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-day"></i> Today's Appointments ({{ $todayAppointments->count() }})
                    </h6>
                    <a href="{{ route('admin.appointments.index') }}?department_id={{ $department->id }}&date={{ today()->format('Y-m-d') }}" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Type</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todayAppointments as $appointment)
                                    <tr>
                                        <td>
                                            <strong>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</strong>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.patients.show', $appointment->patient_id) }}" class="text-decoration-none">
                                                {{ $appointment->patient->name ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ $appointment->doctor->full_name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($appointment->type ?? 'Regular') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $appointment->status == 'confirmed' ? 'success' : ($appointment->status == 'pending' ? 'warning' : ($appointment->status == 'completed' ? 'info' : 'danger')) }}">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Upcoming Appointments -->
            @if($upcomingAppointments->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-calendar-week"></i> Upcoming Appointments (Next 7 Days)
                    </h6>
                    <a href="{{ route('admin.appointments.index') }}?department_id={{ $department->id }}" class="btn btn-sm btn-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($upcomingAppointments as $appointment)
                                    <tr>
                                        <td>{{ formatDate($appointment->appointment_date) }}</td>
                                        <td>{{ \Carbon\Carbon::parse($appointment->appointment_time)->format('H:i') }}</td>
                                        <td>
                                            <a href="{{ route('admin.patients.show', $appointment->patient_id) }}" class="text-decoration-none">
                                                {{ $appointment->patient->name ?? 'N/A' }}
                                            </a>
                                        </td>
                                        <td>{{ $appointment->doctor->full_name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-{{ $appointment->status == 'confirmed' ? 'success' : ($appointment->status == 'pending' ? 'warning' : ($appointment->status == 'completed' ? 'info' : 'danger')) }}">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.appointments.show', $appointment->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Recent Patients -->
            @if($recentPatients->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-plus"></i> Recent Patients
                    </h6>
                    <a href="{{ route('admin.patients.index') }}?department_id={{ $department->id }}" class="btn btn-sm btn-primary">
                        View All Patients
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-hover">
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Registered</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentPatients as $patient)
                                    <tr>
                                        <td><strong>{{ $patient->patient_id }}</strong></td>
                                        <td>{{ $patient->name }}</td>
                                        <td>{{ $patient->email ?? 'N/A' }}</td>
                                        <td>{{ $patient->phone ?? 'N/A' }}</td>
                                        <td>{{ formatDate($patient->created_at) }}</td>
                                        <td>
                                            <span class="badge {{ $patient->is_active ? 'bg-success' : 'bg-secondary' }}">
                                                {{ $patient->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.patients.show', $patient->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
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

        <!-- Right Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Quick Actions</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('admin.appointments.create') }}?department_id={{ $department->id }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> New Appointment
                        </a>
                        <a href="{{ route('admin.patients.create') }}?department_id={{ $department->id }}" class="btn btn-success btn-sm">
                            <i class="fas fa-user-plus"></i> New Patient
                        </a>
                        <a href="{{ route('admin.doctors.index') }}?department_id={{ $department->id }}" class="btn btn-info btn-sm">
                            <i class="fas fa-user-md"></i> View Doctors
                        </a>
                        <a href="{{ route('admin.appointments.index') }}?department_id={{ $department->id }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-calendar"></i> View Appointments
                        </a>
                        <button class="btn btn-{{ $department->is_active ? 'warning' : 'success' }} btn-sm toggle-status" 
                                data-url="{{ contextRoute('departments.toggle-status', $department->id) }}">
                            <i class="fas fa-toggle-{{ $department->is_active ? 'on' : 'off' }}"></i>
                            {{ $department->is_active ? 'Deactivate' : 'Activate' }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Top Doctors -->
            @if($topDoctors->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-star"></i> Top Doctors by Appointments
                    </h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($topDoctors as $doctor)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <strong>{{ $doctor->full_name }}</strong>
                                    @if($doctor->specialization)
                                        <br><small class="text-muted">{{ $doctor->specialization }}</small>
                                    @endif
                                </div>
                                <span class="badge bg-primary rounded-pill">{{ $doctor->appointments_count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            <!-- Department Doctors List -->
            @if($department->doctors->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-md"></i> Department Doctors ({{ $department->doctors->count() }})
                    </h6>
                    <a href="{{ route('admin.doctors.index') }}?department_id={{ $department->id }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($department->doctors->take(5) as $doctor)
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <a href="{{ route('admin.doctors.show', $doctor->id) }}" class="text-decoration-none">
                                        <strong>{{ $doctor->full_name }}</strong>
                                    </a>
                                    @if($doctor->specialization)
                                        <br><small class="text-muted">{{ $doctor->specialization }}</small>
                                    @endif
                                </div>
                                <span class="badge {{ $doctor->is_active ? 'bg-success' : 'bg-secondary' }}">
                                    {{ $doctor->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        @endforeach
                        @if($department->doctors->count() > 5)
                            <div class="list-group-item text-center px-0">
                                <small class="text-muted">+ {{ $department->doctors->count() - 5 }} more doctors</small>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Additional Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Additional Information</h6>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-6">Created:</dt>
                        <dd class="col-sm-6">{{ formatDate($department->created_at) }}</dd>

                        <dt class="col-sm-6">Last Updated:</dt>
                        <dd class="col-sm-6">{{ formatDate($department->updated_at) }}</dd>

                        @if($department->working_hours)
                        <dt class="col-sm-6">Working Hours:</dt>
                        <dd class="col-sm-6">{{ $department->working_hours }}</dd>
                        @endif
                    </dl>
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

@push('styles')
<style>
.border-left-primary {
    border-left: 0.25rem solid #4e73df !important;
}

.border-left-success {
    border-left: 0.25rem solid #1cc88a !important;
}

.border-left-info {
    border-left: 0.25rem solid #36b9cc !important;
}

.border-left-warning {
    border-left: 0.25rem solid #f6c23e !important;
}

.border-left-danger {
    border-left: 0.25rem solid #e74a3b !important;
}

.card {
    transition: transform 0.2s;
}

.card:hover {
    transform: translateY(-2px);
}
</style>
@endpush

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
