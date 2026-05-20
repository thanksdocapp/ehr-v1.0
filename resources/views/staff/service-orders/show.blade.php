@extends('layouts.doctor')

@section('title', 'Service Order ' . $serviceOrder->order_number)
@section('page-title', 'Service Order')
@section('page-subtitle', $serviceOrder->order_number)

@section('content')
<div class="fade-in-up">
    @if(session('success'))<div class="alert alert-success">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="alert alert-danger">{{ session('error') }}</div>@endif

    <div class="mb-3">
        <a href="{{ route('staff.service-orders.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="doctor-card mb-4">
                <div class="doctor-card-header"><h5 class="mb-0">Order details</h5></div>
                <div class="doctor-card-body">
                    <p><strong>Status:</strong> {{ str_replace('_', ' ', $serviceOrder->status) }}</p>
                    <p><strong>Service:</strong> {{ $serviceOrder->service?->name }}</p>
                    <p><strong>Fee:</strong> £{{ number_format((float) $serviceOrder->fee, 2) }}</p>
                    @if($serviceOrder->notes)
                    <p><strong>Patient notes:</strong><br>{{ $serviceOrder->notes }}</p>
                    @endif
                </div>
            </div>

            <div class="doctor-card">
                <div class="doctor-card-header"><h5 class="mb-0">Patient</h5></div>
                <div class="doctor-card-body">
                    @if($serviceOrder->patient)
                    <p><strong>Name:</strong> {{ $serviceOrder->patient->full_name }}</p>
                    <p><strong>Email:</strong> {{ $serviceOrder->patient->email }}</p>
                    <p><strong>Phone:</strong> {{ $serviceOrder->patient->phone }}</p>
                    <a href="{{ route('staff.patients.show', $serviceOrder->patient) }}" class="btn btn-sm btn-primary">Open patient</a>
                    @else
                    <p class="text-muted mb-0">No patient linked.</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            @if(in_array($serviceOrder->status, [\App\Models\ServiceOrder::STATUS_PAID, \App\Models\ServiceOrder::STATUS_CONTACTED]))
            <div class="doctor-card">
                <div class="doctor-card-body d-grid gap-2">
                    @if($serviceOrder->status === \App\Models\ServiceOrder::STATUS_PAID)
                    <form method="POST" action="{{ route('staff.service-orders.contacted', $serviceOrder) }}">@csrf
                        <button type="submit" class="btn btn-outline-primary w-100">Mark contacted</button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('staff.service-orders.completed', $serviceOrder) }}">@csrf
                        <button type="submit" class="btn btn-success w-100">Mark completed</button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
