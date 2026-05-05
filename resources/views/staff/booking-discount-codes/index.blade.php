@extends('layouts.doctor')

@section('title', 'Booking discount codes')
@section('page-title', 'Booking discount codes')
@section('page-subtitle', 'Codes patients can apply on your public booking link before payment')

@section('content')
<div class="fade-in-up">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <p class="text-muted mb-0 small">You set <strong>maximum uses</strong> per code when creating or editing (leave blank for unlimited). Codes apply only on your <strong>doctor</strong> booking link—not on clinic/department booking. Usage is counted after payment completes, or immediately if the booking is free after the discount.</p>
        <div class="d-flex gap-2">
            <a href="{{ route('staff.booking-discount-codes.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>New code
            </a>
            <a href="{{ route('staff.doctor-services.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Services
            </a>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Discount</th>
                            <th>Service</th>
                            <th>Uses</th>
                            <th>Valid</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($codes as $c)
                        <tr>
                            <td><code class="text-dark">{{ $c->code }}</code></td>
                            <td>
                                @if($c->discount_type === 'percent')
                                    {{ rtrim(rtrim(number_format((float) $c->discount_value, 2), '0'), '.') }}%
                                @else
                                    £{{ number_format((float) $c->discount_value, 2) }}
                                @endif
                            </td>
                            <td>
                                @php $scopeNames = $c->restrictedServiceNamesForDisplay(); @endphp
                                @if($scopeNames->isEmpty())
                                    <span class="text-muted">All your services</span>
                                @else
                                    {{ $scopeNames->join(', ') }}
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
                                    @if($c->valid_from){{ $c->valid_from->format('d M Y') }}@else…@endif
                                    –
                                    @if($c->valid_until){{ $c->valid_until->format('d M Y') }}@else…@endif
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
                                <a href="{{ route('staff.booking-discount-codes.edit', $c) }}" class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('staff.booking-discount-codes.destroy', $c) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this code?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">No discount codes yet. Create one for promotions or staff/family bookings.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
