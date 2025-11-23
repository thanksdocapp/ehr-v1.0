@extends('admin.layouts.app')

@section('title', 'Notification Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.notifications.index') }}">Notifications</a></li>
    <li class="breadcrumb-item active">{{ $notification->title }}</li>
@endsection

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Notification Details</h5>
                </div>
                <div class="card-body">
                    <x-notifications.card :notification="$notification" />
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('admin.notifications.index') }}" class="btn btn-secondary">Back to Notifications</a>
                </div>
            </div>
        </div>
    </div>
@endsection

