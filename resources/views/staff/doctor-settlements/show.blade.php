@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Settlement #'.$doctorSettlement->id)
@section('page-title', 'Settlement request')
@section('page-subtitle', formatDateUk($doctorSettlement->period_start).' — '.formatDateUk($doctorSettlement->period_end))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('staff.doctor-settlements.index') }}">Settlement requests</a></li>
    <li class="breadcrumb-item active">#{{ $doctorSettlement->id }}</li>
@endsection

@section('content')
<div class="fade-in">
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <span class="badge bg-{{ match($doctorSettlement->status) {
                        'draft' => 'secondary',
                        'submitted' => 'info',
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'paid' => 'primary',
                        default => 'secondary',
                    } }}">{{ ucfirst($doctorSettlement->status) }}</span>
                    <span class="text-muted ms-2">{{ ucfirst($doctorSettlement->period_type) }}</span>
                </div>
                @if($doctorSettlement->isDraft())
                <div class="d-flex flex-wrap gap-2">
                    <form method="post" action="{{ route('staff.doctor-settlements.recalculate', $doctorSettlement) }}" class="mb-0" onsubmit="return confirm('Replace line items and total using completed payments in this period?');">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">Recalculate from payments</button>
                    </form>
                    <form method="post" action="{{ route('staff.doctor-settlements.submit', $doctorSettlement) }}" onsubmit="return confirm('Submit this settlement to administration for review?');">
                        @csrf
                        <button type="submit" class="btn btn-primary">Submit for review</button>
                    </form>
                </div>
                @endif
            </div>
            <p class="mb-1"><strong>Total:</strong> {{ CurrencyHelper::format((float) $doctorSettlement->total_amount) }}</p>
            @if($doctorSettlement->submitted_at)
                <p class="text-muted small mb-0">Submitted {{ formatDateTimeUkAmPm($doctorSettlement->submitted_at) }}</p>
            @endif
            @if($doctorSettlement->notes)
                <div class="mt-3">
                    <strong>Notes</strong>
                    <div class="border rounded p-2 bg-light small mt-1" style="white-space: pre-wrap;">{{ $doctorSettlement->notes }}</div>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>Line items (from paid billings)</span>
        </div>
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
                        @forelse($doctorSettlement->lines as $line)
                        <tr>
                            <td>{{ $line->description }}</td>
                            <td class="text-end">{{ CurrencyHelper::format((float) $line->amount) }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="2" class="text-center text-muted py-3">No paid billings in this period.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3">
        <a href="{{ route('staff.doctor-settlements.index') }}" class="btn btn-outline-secondary">Back to list</a>
    </div>
</div>
@endsection
