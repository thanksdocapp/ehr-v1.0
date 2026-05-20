@extends('layouts.doctor')

@section('title', 'Service Orders')
@section('page-title', 'Service Orders')
@section('page-subtitle', 'Non-consultation bookings to follow up')

@section('content')
<div class="fade-in-up">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="doctor-card">
        <div class="doctor-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Patient</th>
                            <th>Service</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->order_number }}</td>
                            <td>{{ $order->patient?->full_name ?? '—' }}</td>
                            <td>{{ $order->service?->name ?? '—' }}</td>
                            <td><span class="badge bg-secondary">{{ str_replace('_', ' ', $order->status) }}</span></td>
                            <td>{{ $order->created_at?->format('d M Y H:i') }}</td>
                            <td><a href="{{ route('staff.service-orders.show', $order) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">No service orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    {{ $orders->links() }}
</div>
@endsection
