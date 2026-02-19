@extends('layouts.doctor')

@section('title', 'Form Submissions')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-check me-2 text-success"></i>Form Submissions
            </h1>
            <p class="text-muted mb-0">View submitted patient forms</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <form action="{{ route('staff.form-requests.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search patient or form..."
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="opened" {{ request('status') == 'opened' ? 'selected' : '' }}>Opened</option>
                        <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search me-1"></i>Filter
                    </button>
                </div>
                @if(request()->hasAny(['search', 'status']))
                    <div class="col-md-2">
                        <a href="{{ route('staff.form-requests.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-times me-1"></i>Clear
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <div class="card-body p-0">
            @if($formRequests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Form</th>
                                <th>Patient</th>
                                <th>Sent To</th>
                                <th>Status</th>
                                <th>Sent At</th>
                                <th>Completed At</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($formRequests as $formRequest)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $formRequest->template->name ?? ($formRequest->patientDocument->title ?? 'Form') }}</div>
                                    </td>
                                    <td>
                                        @if($formRequest->patient)
                                            {{ $formRequest->patient->full_name ?? $formRequest->patient->first_name . ' ' . $formRequest->patient->last_name }}
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $formRequest->recipient_email }}</small>
                                    </td>
                                    <td>
                                        <span class="badge {{ $formRequest->status_badge_class }}">
                                            {{ $formRequest->status_label }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $formRequest->sent_at ? formatDateTimeUk($formRequest->sent_at) : '-' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($formRequest->completed_at)
                                            <small class="text-success">
                                                {{ formatDateTimeUk($formRequest->completed_at) }}
                                            </small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('staff.form-requests.show', $formRequest) }}"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(!$formRequest->isCompleted())
                                            <form action="{{ route('staff.form-requests.resend', $formRequest) }}"
                                                  method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                                        onclick="return confirm('Resend this form request?')">
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white">
                    {{ $formRequests->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No form submissions found.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
