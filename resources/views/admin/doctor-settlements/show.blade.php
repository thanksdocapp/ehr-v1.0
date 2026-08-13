@extends('admin.layouts.app')
@php
    use App\Helpers\CurrencyHelper;
    use App\Models\DoctorSettlement;
@endphp

@section('title', 'Settlement #'.$doctorSettlement->id)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.doctor-settlements.index') }}">Doctor settlements</a></li>
    <li class="breadcrumb-item active">#{{ $doctorSettlement->id }}</li>
@endsection

@section('content')
@php
    $recalculateConfirm = $doctorSettlement->status === DoctorSettlement::STATUS_SUBMITTED
        ? 'This request is already submitted. Recalculate anyway?'
        : 'Recalculate this settlement from payments?';
@endphp
<div class="container-fluid">
    @if(in_array($doctorSettlement->status, [DoctorSettlement::STATUS_DRAFT, DoctorSettlement::STATUS_SUBMITTED], true))
    <div class="alert alert-light border mb-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <span class="small mb-0">Rebuild line items and total from completed payments for this doctor and period (overwrites existing lines).</span>
        <form method="post" action="{{ route('admin.doctor-settlements.recalculate', $doctorSettlement) }}" class="mb-0"
              onsubmit="return confirm(@json($recalculateConfirm));">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-primary">Recalculate from payments</button>
        </form>
    </div>
    @endif
    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                        <h2 class="h4 mb-0">Settlement #{{ $doctorSettlement->id }}</h2>
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('admin.doctor-settlements.export-csv', $doctorSettlement) }}" class="btn btn-sm btn-success">
                                <i class="fas fa-file-csv me-1"></i>Export CSV
                            </a>
                            <a href="{{ route('admin.doctor-settlements.export-pdf', $doctorSettlement) }}" class="btn btn-sm btn-danger">
                                <i class="fas fa-file-pdf me-1"></i>Export PDF
                            </a>
                        </div>
                    </div>
                    <p class="mb-1"><strong>Doctor:</strong> {{ $doctorSettlement->doctor->user->name ?? '—' }}</p>
                    <p class="mb-1"><strong>Period:</strong> {{ formatDateUk($doctorSettlement->period_start) }} — {{ formatDateUk($doctorSettlement->period_end) }} ({{ $doctorSettlement->period_type }})</p>
                    <p class="mb-1"><strong>Total:</strong> {{ CurrencyHelper::format((float) $doctorSettlement->total_amount) }}</p>
                    <p class="mb-1"><strong>Status:</strong> {{ $doctorSettlement->status }}</p>
                    @if($doctorSettlement->submitted_at)
                        <p class="text-muted small">Submitted {{ formatDateTimeUkAmPm($doctorSettlement->submitted_at) }}</p>
                    @endif
                    @if($doctorSettlement->reviewed_at)
                        <p class="text-muted small">Last reviewed {{ formatDateTimeUkAmPm($doctorSettlement->reviewed_at) }}
                            @if($doctorSettlement->reviewedByUser)
                                by {{ $doctorSettlement->reviewedByUser->name }}
                            @endif
                        </p>
                    @endif
                    @if($doctorSettlement->notes)
                        <div class="mt-3">
                            <strong>Notes</strong>
                            <div class="border rounded p-2 bg-light small mt-1" style="white-space: pre-wrap;">{{ $doctorSettlement->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header">Line items</div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($doctorSettlement->lines as $line)
                                <tr>
                                    <td>{{ $line->description }}</td>
                                    <td class="text-end">{{ CurrencyHelper::format((float) $line->amount) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            @if(in_array($doctorSettlement->status, [DoctorSettlement::STATUS_SUBMITTED, DoctorSettlement::STATUS_APPROVED], true))
            <div class="card shadow">
                <div class="card-header">Update status</div>
                <div class="card-body">
                    <form method="post" action="{{ route('admin.doctor-settlements.update-status', $doctorSettlement) }}">
                        @csrf
                        @method('PATCH')
                        <div class="mb-3">
                            <label class="form-label">New status</label>
                            <select name="status" class="form-select" required>
                                @if($doctorSettlement->status === DoctorSettlement::STATUS_SUBMITTED)
                                    <option value="approved">Approved</option>
                                    <option value="rejected">Rejected</option>
                                    <option value="paid">Paid</option>
                                @else
                                    <option value="paid">Paid</option>
                                    <option value="rejected">Rejected</option>
                                @endif
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Admin note (optional)</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Save</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
