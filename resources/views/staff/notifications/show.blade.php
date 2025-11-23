@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Notification Details')
@section('page-title', 'Notification Details')
@section('page-subtitle', 'View notification information')
@section('page-title', 'Notification Details')
@section('page-subtitle', $notification->title)

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="doctor-card-header">
                    <h5 class="doctor-doctor-card-title mb-0">
                        <i class="{{ $notification->type_icon }} me-2"></i>{{ $notification->title }}
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <x-notifications.card :notification="$notification" />
                </div>
                <div class="card-footer text-end">
                    <a href="{{ route('staff.notifications.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to Notifications
                    </a>
                    @if($notification->action_url)
                        <a href="{{ $notification->action_url }}" class="btn btn-doctor-primary">
                            <i class="fas fa-external-link-alt me-1"></i>Take Action
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
