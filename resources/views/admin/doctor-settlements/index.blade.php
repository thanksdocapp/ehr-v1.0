@extends('admin.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Doctor settlement requests')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Doctor settlement requests</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">Doctor settlement requests</h1>
    </div>
    <p class="text-muted small mb-3">
        The total is stored when a settlement is created. If older rows show £0.00 after a fix, open the request and use <strong>Recalculate from payments</strong> (draft or submitted) to rebuild lines and the total.
    </p>

    <form method="get" class="row g-2 mb-3 align-items-center">
        <div class="col-auto">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach(['draft','submitted','approved','rejected','paid'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="doctor_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All doctors</option>
                @foreach($doctors as $doctor)
                    <option value="{{ $doctor->id }}" {{ (string) request('doctor_id') === (string) $doctor->id ? 'selected' : '' }}>
                        {{ $doctor->user?->name ?? trim(($doctor->first_name ?? '').' '.($doctor->last_name ?? '')) ?: 'Doctor #'.$doctor->id }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-auto">
            <select name="department_id" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All clinics</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" {{ (string) request('department_id') === (string) $department->id ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </div>
        @if(request()->hasAny(['status', 'doctor_id', 'department_id']))
        <div class="col-auto">
            <a href="{{ route('admin.doctor-settlements.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        </div>
        @endif
    </form>

    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Doctor</th>
                            <th>Clinic</th>
                            <th>Period</th>
                            <th>Type</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th>Date submitted</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settlements as $s)
                        <tr>
                            <td>#{{ $s->id }}</td>
                            <td>{{ $s->doctor?->user?->name ?? 'Doctor #'.$s->doctor_id }}</td>
                            <td>{{ $s->doctor ? $s->doctor->clinicsDisplayLabel() : '—' }}</td>
                            <td>{{ formatDateUk($s->period_start) }} — {{ formatDateUk($s->period_end) }}</td>
                            <td>{{ ucfirst($s->period_type) }}</td>
                            <td class="text-end">{{ CurrencyHelper::format((float) $s->total_amount) }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $s->status }}</span>
                            </td>
                            <td>{{ $s->submitted_at ? formatDateTimeUkAmPm($s->submitted_at) : '—' }}</td>
                            <td class="text-end">
                                <a href="{{ route('admin.doctor-settlements.show', $s) }}" class="btn btn-sm btn-primary">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-4">No settlement requests.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($settlements->hasPages())
        <div class="card-footer">{{ $settlements->links() }}</div>
        @endif
    </div>
</div>
@endsection
