@extends('patient.layouts.app')

@section('title', 'Notification Details')
@section('page-title', 'Notification Details')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="{{ $notification->type_icon }} me-2 text-{{ $notification->type_color }}"></i>
                        {{ $notification->title }}
                    </h5>
                    @if(!$notification->is_read)
                        <span class="badge bg-primary">New</span>
                    @endif
                </div>
                <div class="card-body">
                    <x-notifications.card :notification="$notification" />
                    
                    <!-- Additional Details for Patients -->
                    @if($notification->data)
                        <div class="mt-4">
                            <h6>Additional Information:</h6>
                            <div class="bg-light p-3 rounded">
                                @foreach($notification->data as $key => $value)
                                    <div class="row mb-2">
                                        <div class="col-sm-4"><strong>{{ ucwords(str_replace('_', ' ', $key)) }}:</strong></div>
                                        <div class="col-sm-8">{{ is_array($value) ? implode(', ', $value) : $value }}</div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    
                    <!-- Related Information -->
                    @if($notification->related_appointment_id)
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-calendar-check me-1"></i>
                                Related to your appointment
                            </small>
                        </div>
                    @elseif($notification->related_patient_id)
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-user me-1"></i>
                                Related to your medical records
                            </small>
                        </div>
                    @endif
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                Received {{ $notification->created_at->diffForHumans() }}
                            </small>
                            @if($notification->is_read)
                                <small class="text-muted ms-3">
                                    <i class="fas fa-check me-1"></i>
                                    Read {{ $notification->read_at->diffForHumans() }}
                                </small>
                            @endif
                        </div>
                        <div>
                            <a href="{{ route('patient.notifications.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Back to Notifications
                            </a>
                            @if($notification->action_url)
                                <a href="{{ $notification->action_url }}" class="btn btn-primary">
                                    <i class="fas fa-external-link-alt me-1"></i>Take Action
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
