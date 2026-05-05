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

    <form method="get" class="row g-2 mb-3">
        <div class="col-auto">
            <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                <option value="">All statuses</option>
                @foreach(['draft','submitted','approved','rejected','paid'] as $st)
                    <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="card shadow">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Doctor</th>
                            <th>Period</th>
                            <th>Type</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($settlements as $s)
                        <tr>
                            <td>#{{ $s->id }}</td>
                            <td>{{ $s->doctor?->user?->name ?? 'Doctor #'.$s->doctor_id }}</td>
                            <td>{{ formatDateUk($s->period_start) }} — {{ formatDateUk($s->period_end) }}</td>
                            <td>{{ ucfirst($s->period_type) }}</td>
                            <td class="text-end">{{ CurrencyHelper::format((float) $s->total_amount) }}</td>
                            <td>
                                <span class="badge bg-secondary">{{ $s->status }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.doctor-settlements.show', $s) }}" class="btn btn-sm btn-primary">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">No settlement requests.</td>
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
