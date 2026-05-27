@extends('admin.layouts.app')

@section('title', 'Clinic booking requests')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Clinic booking requests</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">Clinic booking requests</h1>
            <p class="text-muted mb-0">
                Patients who booked into a <strong>clinic</strong> and are waiting for a doctor to be assigned.
                <span class="fw-semibold">{{ $pendingCount }}</span> pending{{ $pendingCount === 1 ? '' : 's' }} in total.
                Doctors can also accept from <strong>Staff → Clinic Requests</strong>.
            </p>
        </div>
        <a href="{{ route('admin.appointments.index', ['status' => 'pending']) }}" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-calendar-check me-1"></i>Pending appointments
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="get" class="row g-2 align-items-end mb-3">
        <div class="col-md-4">
            <label class="form-label small mb-0">Clinic</label>
            <select name="department_id" class="form-select form-select-sm">
                <option value="">All clinics</option>
                @foreach($departments as $dept)
                    <option value="{{ $dept->id }}" {{ (string) request('department_id') === (string) $dept->id ? 'selected' : '' }}>
                        {{ $dept->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm btn-primary">Apply</button>
            @if(request()->filled('department_id'))
                <a href="{{ route('admin.clinic-booking-requests.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
            @endif
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($requests->isEmpty())
                <div class="text-center py-5 px-3">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted mb-0">No pending clinic booking requests.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Request</th>
                                <th>Clinic</th>
                                <th>Patient</th>
                                <th>Reason</th>
                                <th>Service</th>
                                <th>Slot</th>
                                <th>Contact</th>
                                <th style="min-width: 260px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                                @php
                                    $pd = $req->patient_data ?? [];
                                    $bookingReason = $pd['notes'] ?? $req->notes ?? '';
                                    $deptDoctors = $doctorsByDept[(int) $req->department_id] ?? collect();
                                    $eligible = $deptDoctors->filter(function ($d) use ($req) {
                                        return $req->service && $req->service->isAvailableForDoctor($d->id);
                                    })->values();
                                @endphp
                                <tr>
                                    <td><strong>{{ $req->request_number }}</strong></td>
                                    <td>{{ $req->department?->name ?? '—' }}</td>
                                    <td>{{ trim(($pd['first_name'] ?? '').' '.($pd['last_name'] ?? '')) ?: '—' }}</td>
                                    <td>
                                        @if($bookingReason !== '' && $bookingReason !== null)
                                            <span class="small" title="{{ e($bookingReason) }}">{{ \Illuminate\Support\Str::limit($bookingReason, 48) }}</span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $req->service?->name ?? '—' }}</td>
                                    <td>
                                        <span class="small">{{ $req->appointment_date->format('D j M Y') }}</span><br>
                                        <span class="small text-muted">{{ $req->appointment_time instanceof \DateTimeInterface ? $req->appointment_time->format('g:i A') : $req->appointment_time }}</span>
                                    </td>
                                    <td class="small">
                                        {{ $pd['email'] ?? '—' }}<br>
                                        {{ $pd['phone'] ?? '—' }}
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            @if($eligible->isEmpty())
                                                <span class="text-danger small">No active doctors in this clinic offer this service.</span>
                                            @else
                                                <form method="post" action="{{ route('admin.clinic-booking-requests.accept', $req) }}" class="d-flex flex-column gap-1">
                                                    @csrf
                                                    <select name="doctor_id" class="form-select form-select-sm" required>
                                                        <option value="">Choose doctor…</option>
                                                        @foreach($eligible as $d)
                                                            <option value="{{ $d->id }}">{{ $d->user->name ?? ($d->first_name.' '.$d->last_name) }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="fas fa-check me-1"></i>Accept for doctor
                                                    </button>
                                                </form>
                                            @endif
                                            <form method="post"
                                                  action="{{ route('admin.clinic-booking-requests.cancel', $req) }}"
                                                  class="d-flex flex-column gap-1 border-top pt-2"
                                                  onsubmit="return confirm('Cancel booking request {{ $req->request_number }}? The time slot will be released. Payment refunds are not automatic — process separately if needed.');">
                                                @csrf
                                                @if(request()->filled('department_id'))
                                                    <input type="hidden" name="department_id" value="{{ request('department_id') }}">
                                                @endif
                                                <label class="form-label small mb-0 text-muted">Cancel (optional note to patient record)</label>
                                                <textarea name="cancellation_reason" class="form-control form-control-sm" rows="2" maxlength="1000" placeholder="Reason for cancellation (internal)"></textarea>
                                                <button type="submit" class="btn btn-outline-danger btn-sm">
                                                    <i class="fas fa-times me-1"></i>Cancel request
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer border-top-0 py-3">
                    {{ $requests->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h2 class="h5 mb-0">Accepted clinic bookings</h2>
                <p class="text-muted small mb-0 mt-1">
                    Recent accepts (up to 5 below). <span class="fw-semibold">{{ $acceptedTotalCount }}</span> total{{ request()->filled('department_id') ? ' for this clinic filter' : '' }}.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('admin.clinic-booking-requests.accepted', request()->only('department_id')) }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-list me-1"></i>View all accepted
                </a>
                <a href="{{ route('admin.clinic-booking-requests.accepted.export.csv', request()->only('department_id')) }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-file-csv me-1"></i>Export CSV
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            @if($acceptedPreview->isEmpty())
                <div class="text-center py-4 px-3">
                    <p class="text-muted mb-0">No accepted bookings{{ request()->filled('department_id') ? ' for this clinic' : '' }} yet.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Request</th>
                                <th>Booking capture</th>
                                <th>Patient</th>
                                <th>Assigned doctor</th>
                                <th>Service</th>
                                <th>Slot</th>
                                <th>Accepted by</th>
                                <th>Accepted</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($acceptedPreview as $req)
                                @include('admin.clinic-booking-requests.partials.accepted-row', ['req' => $req])
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($acceptedTotalCount > $acceptedPreview->count())
                    <div class="card-footer border-top-0 py-2 text-center bg-light">
                        <a href="{{ route('admin.clinic-booking-requests.accepted', request()->only('department_id')) }}" class="small">
                            View all {{ $acceptedTotalCount }} accepted bookings…
                        </a>
                    </div>
                @endif
            @endif
        </div>
    </div>

    <div class="alert alert-info mt-3 mb-0">
        <i class="fas fa-info-circle me-2"></i>
        Accepting creates the patient (if new), adds them to the clinic, and places the visit on the selected doctor’s diary. Confirmation emails are sent like a normal doctor acceptance.
        Clinics with <strong>one active doctor</strong> are auto-assigned to that doctor and appear under <strong>Accepted clinic bookings</strong> below (not in the pending list).
        Cancelling removes a <strong>pending</strong> request from this list and frees the slot; contact the patient separately if a refund is required.
    </div>
</div>
@endsection
