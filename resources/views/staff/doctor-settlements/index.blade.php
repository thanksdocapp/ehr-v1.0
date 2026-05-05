@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')
@php
    use App\Helpers\CurrencyHelper;
@endphp

@section('title', 'Settlement requests')
@section('page-title', 'Settlement requests')
@section('page-subtitle', 'Weekly or monthly summaries of collections attributed to you')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Settlement requests</li>
@endsection

@section('content')
<div class="fade-in">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="h3 mb-1">Settlement requests</h1>
            <p class="text-muted mb-0">Request review of your attributed collections for a calendar week or month</p>
        </div>
        <a href="{{ route('staff.doctor-settlements.create') }}" class="btn btn-doctor-primary">
            <i class="fas fa-plus me-2"></i>New draft
        </a>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
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
                            <td>{{ formatDateUk($s->period_start) }} — {{ formatDateUk($s->period_end) }}</td>
                            <td>{{ ucfirst($s->period_type) }}</td>
                            <td class="text-end">{{ CurrencyHelper::format((float) $s->total_amount) }}</td>
                            <td>
                                <span class="badge bg-{{ match($s->status) {
                                    'draft' => 'secondary',
                                    'submitted' => 'info',
                                    'approved' => 'success',
                                    'rejected' => 'danger',
                                    'paid' => 'primary',
                                    default => 'secondary',
                                } }}">{{ ucfirst($s->status) }}</span>
                            </td>
                            <td class="text-end">
                                <a href="{{ route('staff.doctor-settlements.show', $s) }}" class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">No settlement requests yet.</td>
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
