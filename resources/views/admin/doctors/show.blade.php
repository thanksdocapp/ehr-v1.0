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
                <div class="mt-3 mt-md-0 d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.doctors.booking-discount-codes.index', $doctor) }}" class="btn btn-light btn-lg" style="border-radius: 12px; font-weight: 600;">
                        <i class="fas fa-ticket-alt me-2"></i>Booking codes
                    </a>
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

                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="text-primary mb-0">Weekly Availability</h5>
                                    <a href="{{ route('admin.doctors.edit', $doctor->id) }}#availability" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit me-1"></i>Edit Availability
                                    </a>
                                </div>
                                @if(!empty($weeklyAvailabilityDays))
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>Day</th>
                                                    <th>Available</th>
                                                    <th>Time windows</th>
                                                    <th>Breaks</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $days = ['monday' => 'Monday', 'tuesday' => 'Tuesday', 'wednesday' => 'Wednesday', 'thursday' => 'Thursday', 'friday' => 'Friday', 'saturday' => 'Saturday', 'sunday' => 'Sunday'];
                                                @endphp
                                                @foreach($days as $day => $dayName)
                                                    @php
                                                        $dayAvailability = $weeklyAvailabilityDays[$day] ?? ['available' => false, 'sessions' => [], 'breaks' => []];
                                                        $isAvailable = $dayAvailability['available'] ?? false;
                                                        $sessions = $dayAvailability['sessions'] ?? [];
                                                        $breaks = $dayAvailability['breaks'] ?? [];
                                                    @endphp
                                                    <tr>
                                                        <td class="fw-bold">{{ $dayName }}</td>
                                                        <td>
                                                            @if($isAvailable)
                                                                <span class="badge bg-success">Yes</span>
                                                            @else
                                                                <span class="badge bg-secondary">No</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($isAvailable && !empty($sessions))
                                                                @foreach($sessions as $session)
                                                                    <span class="badge bg-light text-dark border me-1 mb-1">
                                                                        {{ $session['start'] ?? '—' }} - {{ $session['end'] ?? '—' }}
                                                                    </span>
                                                                @endforeach
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($isAvailable && !empty($breaks) && is_array($breaks))
                                                                @foreach($breaks as $break)
                                                                    <span class="badge bg-info me-1">
                                                                        {{ $break['start'] ?? '' }} - {{ $break['end'] ?? '' }}
                                                                    </span>
                                                                @endforeach
                                                            @else
                                                                <span class="text-muted">-</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <p class="small text-muted mb-0">
                                        Mirrors the doctor’s weekly schedule, including multiple time windows per day.
                                    </p>
                                @else
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle me-2"></i>
                                        No availability schedule set. <a href="{{ route('admin.doctors.edit', $doctor->id) }}#availability">Set availability now</a>.
                                    </div>
                                @endif
                            </div>

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

                            @if(isset($doctorServices) && $doctorServices->count() > 0)
                                <div class="mb-4">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <h5 class="text-primary mb-0">
                                            <i class="fas fa-briefcase-medical me-2"></i>Doctor Services ({{ $doctorServices->count() }})
                                        </h5>
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('admin.doctors.booking-discount-codes.index', $doctor) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-ticket-alt me-1"></i>Voucher codes
                                            </a>
                                            @if($doctor->user_id)
                                                <a href="{{ route('admin.booking-services.create', ['doctor_id' => $doctor->id]) }}" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-plus me-1"></i>Add booking service
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-hover">
                                            <thead>
                                                <tr>
                                                    <th>Service Name</th>
                                                    <th>Duration</th>
                                                    <th>Price</th>
                                                    <th>Status</th>
                                                    <th class="text-end">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($doctorServices as $service)
                                                    <tr>
                                                        <td>
                                                            <div class="fw-semibold">{{ $service['name'] }}</div>
                                                            @if($service['description'])
                                                                <small class="text-muted">{{ Str::limit($service['description'], 50) }}</small>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($service['has_override'] && $service['custom_duration_minutes'])
                                                                <span class="badge bg-info text-dark">{{ $service['custom_duration_minutes'] }} min</span>
                                                            @else
                                                                <span class="badge bg-light text-dark">{{ $service['default_duration_minutes'] ?? 60 }} min</span>
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if($service['has_override'] && $service['custom_price'] !== null)
                                                                <strong>£{{ number_format($service['custom_price'], 2) }}</strong>
                                                            @elseif($service['default_price'])
                                                                <strong>£{{ number_format($service['default_price'], 2) }}</strong>
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
                                                        <td class="text-end text-nowrap">
                                                            <a href="{{ route('admin.booking-services.edit', $service['id']) }}" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @elseif(isset($doctorServices))
                                <div class="mb-4">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <h5 class="text-primary mb-0">
                                            <i class="fas fa-briefcase-medical me-2"></i>Doctor Services
                                        </h5>
                                        <a href="{{ route('admin.doctors.booking-discount-codes.index', $doctor) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-ticket-alt me-1"></i>Voucher codes
                                        </a>
                                    </div>
                                    <p class="text-muted mb-3">No bookable services yet. Services must be tied to this doctor&apos;s user account to appear here and on public booking.</p>
                                    @if($doctor->user_id)
                                        <div class="d-flex flex-wrap gap-2">
                                            <a href="{{ route('admin.booking-services.create', ['doctor_id' => $doctor->id]) }}" class="btn btn-primary">
                                                <i class="fas fa-plus me-2"></i>Create booking service
                                            </a>
                                            <a href="{{ route('admin.booking-services.index') }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-list me-2"></i>All booking services
                                            </a>
                                        </div>
                                    @else
                                        <p class="text-warning small mb-0"><i class="fas fa-exclamation-triangle me-1"></i>Link a staff user to this doctor first, then add services.</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <div class="col-md-4">
                            <div class="modern-card">
                                <div class="modern-card-header">
                                    <h6 class="modern-card-title mb-0"><i class="fas fa-user-md me-2"></i>Doctor Information</h6>
                                </div>
                                <div class="modern-card-body">
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
                                <div class="modern-card mt-3">
                                    <div class="modern-card-header">
                                        <h6 class="modern-card-title mb-0"><i class="fas fa-calendar-day me-2"></i>Today's Appointments</h6>
                                    </div>
                                    <div class="modern-card-body">
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

                            <div class="modern-card mt-3">
                                <div class="modern-card-header">
                                    <h6 class="modern-card-title mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h6>
                                </div>
                                <div class="modern-card-body">
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-{{ $doctor->is_active ? 'warning' : 'success' }} btn-sm toggle-status" 
                                                data-url="{{ route('admin.doctors.toggle-status', $doctor) }}">
                                            <i class="fas fa-toggle-{{ $doctor->is_active ? 'on' : 'off' }}"></i>
                                            {{ $doctor->is_active ? 'Deactivate' : 'Activate' }}
                                        </button>
                                        
                                        <a href="{{ contextRoute('doctors.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-plus"></i> Add New Doctor
                                        </a>
                                        
                                        @if($doctor->department)
                                            <a href="{{ contextRoute('departments.show', $doctor->department->id) }}" class="btn btn-info btn-sm">
                                                <i class="fas fa-building"></i> View Department
                                            </a>
                                        @else
                                            <span class="btn btn-outline-secondary btn-sm disabled">
                                                <i class="fas fa-building"></i> No department assigned
                                            </span>
                                        @endif

                                        <a href="{{ contextRoute('doctors.edit', $doctor->id) }}#availability" class="btn btn-secondary btn-sm">
                                            <i class="fas fa-calendar-alt"></i> Edit availability
                                        </a>

                                        <a href="{{ route('admin.doctors.booking-discount-codes.index', $doctor) }}" class="btn btn-outline-primary btn-sm">
                                            <i class="fas fa-ticket-alt"></i> Booking discount codes
                                        </a>
                                    </div>
                                </div>
                            </div>

                            @if(isset($recentTestimonials) && $recentTestimonials->count() > 0)
                                <div class="modern-card mt-3">
                                    <div class="modern-card-header">
                                        <h6 class="modern-card-title mb-0"><i class="fas fa-star me-2"></i>Recent Reviews</h6>
                                    </div>
                                    <div class="modern-card-body">
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
                        <div class="d-flex flex-wrap gap-2 justify-content-end">
                            <a href="{{ route('admin.doctors.booking-discount-codes.index', $doctor) }}" class="btn btn-outline-primary">
                                <i class="fas fa-ticket-alt"></i> Booking codes
                            </a>
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
(function() {
    function showError(message) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Error', text: message });
        } else {
            alert(message);
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.toggle-status').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                var url = btn.getAttribute('data-url');
                if (!url) return;
                var meta = document.querySelector('meta[name="csrf-token"]');
                var token = meta ? meta.getAttribute('content') : '';
                btn.disabled = true;
                var body = new URLSearchParams();
                body.append('_token', token);
                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': token
                    },
                    credentials: 'same-origin',
                    body: body
                })
                .then(function(response) {
                    return response.text().then(function(text) {
                        var data = {};
                        try { data = text ? JSON.parse(text) : {}; } catch (err) { /* non-JSON */ }
                        return { ok: response.ok, data: data };
                    });
                })
                .then(function(result) {
                    if (result.ok && result.data && result.data.success) {
                        location.reload();
                        return;
                    }
                    var msg = (result.data && result.data.message) ? result.data.message : 'Could not update doctor status.';
                    showError(msg);
                })
                .catch(function() {
                    showError('Could not update doctor status. Please try again.');
                })
                .finally(function() {
                    btn.disabled = false;
                });
            });
        });
    });

    window.deleteDoctor = function(doctorId) {
        if (window.event) {
            window.event.preventDefault();
            window.event.stopPropagation();
        }
        var confirmDelete = confirm('WARNING: Are you sure you want to permanently delete this doctor?\n\nThis action cannot be undone and will remove all doctor data including:\n- Personal information\n- Professional credentials\n- Appointment history\n- Patient relationships\n\nClick OK to confirm deletion or Cancel to abort.');
        if (!confirmDelete) return false;
        setTimeout(function() {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '/admin/doctors/' + doctorId;
            form.style.display = 'none';
            var csrfTokenValue = document.querySelector('meta[name="csrf-token"]') && document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            var csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = csrfTokenValue || '';
            form.appendChild(csrfInput);
            var methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);
            document.body.appendChild(form);
            form.submit();
        }, 100);
        return false;
    };
})();
</script>
@endpush
