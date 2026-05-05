@extends('admin.layouts.app')

@section('title', 'Clinic booking discount codes')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <div>
            <h4 class="mb-1 fw-bold">Clinic booking codes — {{ $department->name }}</h4>
            <p class="text-muted small mb-0">Used on the <strong>public clinic booking</strong> flow before payment. For doctor booking links, use each doctor’s page → <strong>Booking codes</strong> (admin) or Staff → Booking discount codes.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.departments.clinic-booking-discount-codes.create', $department) }}" class="btn btn-primary btn-sm">
                <i class="fas fa-plus me-1"></i>New code
            </a>
            <a href="{{ route('admin.departments.show', $department) }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i>Clinic
            </a>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Service scope</th>
                            <th>Uses</th>
                            <th>Valid</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($codes as $c)
                        <tr>
                            <td><code>{{ $c->code }}</code></td>
                            <td>
                                @if($c->discount_type === 'percent')
                                    {{ rtrim(rtrim(number_format((float) $c->discount_value, 2), '0'), '.') }}%
                                @else
                                    £{{ number_format((float) $c->discount_value, 2) }}
                                @endif
                            </td>
                            <td>
                                @if($c->booking_service_id)
                                    {{ $c->bookingService?->name ?? '—' }}
                                @else
                                    <span class="text-muted">All services</span>
                                @endif
                            </td>
                            <td>
                                @if($c->max_uses !== null)
                                    {{ $c->uses_count }} / {{ $c->max_uses }}
                                @else
                                    {{ $c->uses_count }} / ∞
                                @endif
                            </td>
                            <td class="small text-muted">
                                @if($c->valid_from || $c->valid_until)
                                    @if($c->valid_from){{ $c->valid_from->format('d M Y') }}@else…@endif – @if($c->valid_until){{ $c->valid_until->format('d M Y') }}@else…@endif
                                @else
                                    No date limit
                                @endif
                            </td>
                            <td>
                                @if($c->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Off</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <a href="{{ route('admin.departments.clinic-booking-discount-codes.edit', [$department, $c]) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('admin.departments.clinic-booking-discount-codes.destroy', [$department, $c]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this code?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No codes yet. Add one for promotions or partner referrals.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
