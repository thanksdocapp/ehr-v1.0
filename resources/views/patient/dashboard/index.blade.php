@extends('patient.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('content')
    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-primary-gradient">
                    <i class="fas fa-calendar-check"></i>
                </div>
                <div class="stat-number text-primary">{{ $stats['appointments']['total'] }}</div>
                <div class="stat-label">Total Appointments</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-success-gradient">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number text-success">{{ $stats['appointments']['upcoming'] }}</div>
                <div class="stat-label">Upcoming Appointments</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-info-gradient">
                    <i class="fas fa-file-medical"></i>
                </div>
                <div class="stat-number text-info">{{ $stats['medical']['records'] }}</div>
                <div class="stat-label">Medical Records</div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="stat-card">
                <div class="stat-icon bg-warning-gradient">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
                <div class="stat-number text-warning">{{ CurrencyHelper::format($stats['billing']['outstanding_amount']) }}</div>
                <div class="stat-label">Outstanding Balance</div>
            </div>
        </div>
    </div>

    <!-- Next Appointment Alert -->
    @if($nextAppointment)
        <div class="alert alert-info border-0 shadow-sm mb-4">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h5 class="alert-heading mb-2">
                        <i class="fas fa-calendar-alt me-2"></i>
                        Your Next Appointment
                    </h5>
                    <p class="mb-1">
                        <strong>{{ $nextAppointment->doctor->full_name }}</strong> 
                        - {{ $nextAppointment->department->name }}
                    </p>
                    <p class="mb-0">
                        <i class="fas fa-clock me-1"></i>
                        {{ $nextAppointment->appointment_date->format('M d, Y') }} at 
                        {{ \Carbon\Carbon::parse($nextAppointment->appointment_time)->format('g:i A') }}
                    </p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="{{ route('patient.appointments.show', $nextAppointment) }}" class="btn btn-outline-info">
                        View Details
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('patient.appointments.create') }}" class="btn btn-primary w-100 py-3">
                                <i class="fas fa-plus-circle me-2"></i>
                                Book Appointment
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('patient.profile.edit') }}" class="btn btn-outline-secondary w-100 py-3">
                                <i class="fas fa-user-edit me-2"></i>
                                Update Profile
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('patient.medical-records.index') }}" class="btn btn-outline-info w-100 py-3">
                                <i class="fas fa-file-medical me-2"></i>
                                Medical Records
                            </a>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-3">
                            <a href="{{ route('patient.billing.index') }}" class="btn btn-outline-warning w-100 py-3">
                                <i class="fas fa-credit-card me-2"></i>
                                Pay Bills
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Upcoming Appointments -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-calendar-check me-2"></i>
                            Upcoming Appointments
                        </h5>
                        <a href="{{ route('patient.appointments.index') }}" class="btn btn-sm btn-outline-light">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($upcomingAppointments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($upcomingAppointments as $appointment)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $appointment->doctor->full_name }}</h6>
                                            <p class="mb-1 text-muted">{{ $appointment->department->name }}</p>
                                            <small class="text-muted">
                                                <i class="fas fa-calendar me-1"></i>
                                                {{ $appointment->appointment_date->format('M d, Y') }}
                                                <i class="fas fa-clock ms-2 me-1"></i>
                                                {{ \Carbon\Carbon::parse($appointment->appointment_time)->format('g:i A') }}
                                            </small>
                                        </div>
                                        <span class="badge bg-{{ $appointment->status === 'confirmed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($appointment->status) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No upcoming appointments</p>
                            <a href="{{ route('patient.appointments.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus me-1"></i>
                                Book New Appointment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Medical Records -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-medical me-2"></i>
                            Recent Medical Records
                        </h5>
                        <a href="{{ route('patient.medical-records.index') }}" class="btn btn-sm btn-outline-light">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($recentMedicalRecords) && $recentMedicalRecords->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($recentMedicalRecords as $record)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $record->diagnosis ?? 'General Consultation' }}</h6>
                                            <p class="mb-1 text-muted">Dr. {{ $record->doctor->full_name }}</p>
                                            <small class="text-muted">
                                                {{ $record->created_at->format('M d, Y') }}
                                            </small>
                                        </div>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst($record->record_type) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-medical fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No medical records yet</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Active Prescriptions -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-pills me-2"></i>
                            Active Prescriptions
                        </h5>
                        <a href="{{ route('patient.medical-records.index') }}" class="btn btn-sm btn-outline-light">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($activePrescriptions) && $activePrescriptions->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($activePrescriptions as $prescription)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">{{ $prescription->medication_name ?? 'Prescription' }}</h6>
                                            <p class="mb-1 text-muted">Prescribed by Dr. {{ $prescription->doctor->full_name }}</p>
                                            <small class="text-muted">
                                                {{ $prescription->created_at->format('M d, Y') }}
                                            </small>
                                        </div>
                                        <span class="badge bg-success">
                                            Active
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-pills fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No active prescriptions</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Pending Invoices -->
        <div class="col-lg-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-file-invoice-dollar me-2"></i>
                            Pending Invoices
                        </h5>
                        <a href="{{ route('patient.billing.index') }}" class="btn btn-sm btn-outline-light">
                            View All
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(isset($pendingInvoices) && $pendingInvoices->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($pendingInvoices as $invoice)
                                <div class="list-group-item border-0 px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1">Invoice #{{ $invoice->invoice_number }}</h6>
                                            <p class="mb-1 text-muted">{{ CurrencyHelper::format($invoice->total_amount) }}</p>
                                            <small class="text-muted">
                                                Due: {{ $invoice->due_date->format('M d, Y') }}
                                            </small>
                                        </div>
                                        <span class="badge bg-{{ $invoice->due_date->isPast() ? 'danger' : 'warning' }}">
                                            {{ $invoice->due_date->isPast() ? 'Overdue' : 'Pending' }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <p class="text-muted">All invoices are paid!</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Departments Access -->
    @if($departments->count() > 0)
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-hospital me-2"></i>
                            Book Appointment by Department
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            @foreach($departments->take(6) as $department)
                                <div class="col-lg-4 col-md-6 mb-3">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-body text-center">
                                            <i class="fas fa-{{ $department->icon ?? 'hospital-alt' }} fa-2x text-primary mb-3"></i>
                                            <h6 class="card-title">{{ $department->name }}</h6>
                                            <p class="card-text text-muted small">
                                                {{ $department->doctors_count }} Doctor{{ $department->doctors_count > 1 ? 's' : '' }} Available
                                            </p>
                                            <a href="{{ route('patient.appointments.create', ['department' => $department->id]) }}" 
                                               class="btn btn-outline-primary btn-sm">
                                                Book Now
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($departments->count() > 6)
                            <div class="text-center mt-3">
                                <a href="{{ route('patient.appointments.create') }}" class="btn btn-primary">
                                    View All Departments
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    // Refresh dashboard stats every 5 minutes
    setInterval(function() {
        // You can implement AJAX refresh here if needed
    }, 300000);
</script>
@endpush
