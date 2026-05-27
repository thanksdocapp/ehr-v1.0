@extends('admin.layouts.app')

@section('title', 'Accepted clinic bookings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.clinic-booking-requests.index') }}">Clinic booking requests</a></li>
    <li class="breadcrumb-item active">Accepted</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-start mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">Accepted clinic bookings</h1>
            <p class="text-muted mb-0">Full history with filters. Use export for spreadsheets.</p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('admin.clinic-booking-requests.accepted.export.csv', request()->query()) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-file-csv me-1"></i>Export CSV
            </a>
            <a href="{{ route('admin.clinic-booking-requests.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Pending requests
            </a>
        </div>
    </div>

    <form method="get" class="card shadow-sm mb-4">
        <div class="card-body row g-2 align-items-end">
            <div class="col-md-3">
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
            <div class="col-md-3">
                <label class="form-label small mb-0">Accepted from</label>
                <input type="date" name="accepted_from" class="form-control form-control-sm" value="{{ request('accepted_from') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">Accepted to</label>
                <input type="date" name="accepted_to" class="form-control form-control-sm" value="{{ request('accepted_to') }}">
            </div>
            <div class="col-md-3 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-sm btn-primary">Apply</button>
                @if(request()->anyFilled(['department_id', 'accepted_from', 'accepted_to']))
                    <a href="{{ route('admin.clinic-booking-requests.accepted') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                @endif
            </div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            @if($acceptedRequests->isEmpty())
                <div class="text-center py-5 px-3">
                    <p class="text-muted mb-0">No accepted bookings match your filters.</p>
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
                                <th class="text-end">Payment amount</th>
                                <th>Slot</th>
                                <th>Accepted by</th>
                                <th>Accepted</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($acceptedRequests as $req)
                                @include('admin.clinic-booking-requests.partials.accepted-row', ['req' => $req])
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="card-footer border-top-0 py-3">
                    {{ $acceptedRequests->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
