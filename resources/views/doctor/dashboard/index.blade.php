@extends('layouts.doctor')

@section('title', 'Doctor Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Manage your practice efficiently')

@section('content')
<div class="fade-in-up">
    <!-- System Notices -->
    @if(isset($notices) && $notices->count() > 0)
        <div class="row g-4 mb-4">
            <div class="col-12">
                @foreach($notices as $notice)
                    <div class="alert alert-{{ $notice->type }} alert-dismissible fade show mb-3" role="alert">
                        <div class="d-flex align-items-start">
                            <i class="fas {{ $notice->type_icon }} fa-2x me-3 mt-1"></i>
                            <div class="flex-grow-1">
                                <h5 class="alert-heading mb-2">
                                    @if($notice->priority === 'urgent')
                                        <span class="badge bg-danger me-2">URGENT</span>
                                    @elseif($notice->priority === 'high')
                                        <span class="badge bg-warning me-2">HIGH PRIORITY</span>
                                    @endif
                                    {{ $notice->title }}
                                </h5>
                                <div class="mb-2">
                                    {!! nl2br(e($notice->message)) !!}
                                </div>
                                @if($notice->expires_at)
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>
                                        Expires: {{ formatDateTimeUk($notice->expires_at) }}
                                    </small>
                                @endif
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Pending appointments awaiting confirmation - remind doctors to confirm -->
    @if(isset($pendingUpcomingCount) && $pendingUpcomingCount > 0)
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert" style="border-left: 4px solid #d97706;">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle fa-2x me-3 text-warning"></i>
                <div class="flex-grow-1">
                    <strong>Pending appointments awaiting confirmation</strong>
                    <p class="mb-0 mt-1">You have {{ $pendingUpcomingCount }} {{ \Illuminate\Support\Str::plural('appointment', $pendingUpcomingCount) }} that {{ $pendingUpcomingCount === 1 ? 'needs' : 'need' }} confirmation. Please confirm {{ $pendingUpcomingCount === 1 ? 'it' : 'them' }} so patients know their appointment is secured.</p>
                </div>
                <a href="{{ route('staff.appointments.index', ['status' => 'pending']) }}" class="btn btn-warning ms-3">
                    <i class="fas fa-check-circle me-1"></i> Confirm appointments
                </a>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- Pending clinic booking requests - awaiting doctor acceptance -->
    @if(isset($pendingClinicRequestsCount) && $pendingClinicRequestsCount > 0)
        <div class="alert alert-info alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-inbox fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <strong>Clinic booking requests awaiting acceptance</strong>
                    <p class="mb-0 mt-1">{{ $pendingClinicRequestsCount }} {{ \Illuminate\Support\Str::plural('patient', $pendingClinicRequestsCount) }} {{ $pendingClinicRequestsCount === 1 ? 'has' : 'have' }} requested an appointment at your clinic. Accept to add {{ $pendingClinicRequestsCount === 1 ? 'them' : 'them' }} to your schedule.</p>
                </div>
                <a href="{{ route('staff.clinic-booking-requests.index') }}" class="btn btn-info ms-3">
                    <i class="fas fa-inbox me-1"></i> View & accept
                </a>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- Pending appointments that have passed - flag for action -->
    @if(isset($pendingPastCount) && $pendingPastCount > 0)
        <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                <div class="flex-grow-1">
                    <strong>Pending appointments need action</strong>
                    <p class="mb-0 mt-1">You have {{ $pendingPastCount }} {{ \Illuminate\Support\Str::plural('appointment', $pendingPastCount) }} that {{ $pendingPastCount === 1 ? 'is' : 'are' }} still pending and {{ $pendingPastCount === 1 ? 'has' : 'have' }} passed. Please confirm, complete, or cancel {{ $pendingPastCount === 1 ? 'it' : 'them' }}.</p>
                </div>
                <a href="{{ route('staff.appointments.index', ['status' => 'pending', 'overdue' => '1']) }}" class="btn btn-warning ms-3">
                    <i class="fas fa-tasks me-1"></i> View & take action
                </a>
                <button type="button" class="btn-close ms-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        </div>
    @endif

    <!-- Welcome Hero Section - Transparent and Clean -->
    <div class="doctor-card mb-4" style="background: transparent; border: 1px solid rgba(0, 0, 0, 0.1); box-shadow: none;">
        <div class="doctor-card-body p-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h2 class="mb-2 fw-bold" style="font-size: 1.75rem; color: #212529;">Welcome back, {{ formatDoctorName(Auth::user()->name) }}!</h2>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                        @php
                            $ratingAvg = isset($doctorRating['avg']) && $doctorRating['avg'] !== null ? (float) $doctorRating['avg'] : null;
                            $ratingCount = (int) ($doctorRating['count'] ?? 0);
                        @endphp
                        @if($ratingCount > 0 && $ratingAvg !== null)
                            <span class="badge bg-warning text-dark" style="border-radius: 999px; padding: 8px 12px;">
                                <i class="fas fa-star me-1"></i>{{ number_format($ratingAvg, 1) }}/5
                            </span>
                            <span class="text-muted small">
                                {{ number_format($ratingCount) }} {{ \Illuminate\Support\Str::plural('rating', $ratingCount) }}
                            </span>
                        @else
                            <span class="text-muted small">
                                <i class="far fa-star me-1"></i>No patient feedback yet
                            </span>
                        @endif
                    </div>
                    <p class="mb-0" style="font-size: 1rem; color: #6c757d;">
                        <i class="fas fa-calendar-day me-2"></i>{{ \Carbon\Carbon::now()->format('l, F j, Y') }}
                        <span class="ms-3"><i class="fas fa-clock me-2"></i><span id="hero-current-time">{{ \Carbon\Carbon::now()->format('h:i A') }}</span></span>
                    </p>
                </div>
                <div class="mt-3 mt-md-0 d-flex flex-column align-items-stretch align-items-md-end gap-2">
                    @php
                        $bookingLink = null;
                        if (isset($doctor) && $doctor) {
                            // Get the doctor's primary department
                            $department = $doctor->primaryDepartment();
                            
                            if ($department) {
                                // Use department slug for the booking link
                                if ($department->slug) {
                                    $bookingLink = route('public.booking.clinic', ['slug' => $department->slug]);
                                } else {
                                    // Generate slug from department name if it doesn't exist
                                    $department->slug = \Illuminate\Support\Str::slug($department->name);
                                    $department->save();
                                    $bookingLink = route('public.booking.clinic', ['slug' => $department->slug]);
                                }
                            } else {
                                // Fallback: if no department, use doctor slug (for backward compatibility)
                                if ($doctor->slug) {
                                    $bookingLink = route('public.booking.doctor', ['slug' => $doctor->slug]);
                                } else {
                                    // Generate slug if it doesn't exist
                                    $doctor->slug = \Illuminate\Support\Str::slug($doctor->first_name . ' ' . $doctor->last_name);
                                    $doctor->save();
                                    $bookingLink = route('public.booking.doctor', ['slug' => $doctor->slug]);
                                }
                            }
                        }
                    @endphp
                    <div class="d-flex flex-wrap gap-2 align-items-center justify-content-md-end">
                    @if($bookingLink)
                    <button type="button" onclick="copyBookingLink('{{ $bookingLink }}')" class="btn btn-success btn-sm" style="border-radius: 8px; font-weight: 500;" title="Copy your public booking link" id="copy-booking-link-btn">
                        <i class="fas fa-link me-1"></i>Copy Booking Link
                    </button>
                    @else
                    <button type="button" class="btn btn-secondary btn-sm" style="border-radius: 8px; font-weight: 500;" disabled title="Doctor profile incomplete - no department assigned">
                        <i class="fas fa-link me-1"></i>Booking Link Unavailable
                    </button>
                    @endif
                    <a href="{{ route('staff.appointments.create') }}" class="btn btn-doctor-primary btn-sm" style="border-radius: 8px; font-weight: 500;">
                        <i class="fas fa-plus me-1"></i>New Appointment
                    </a>
                    <a href="{{ route('staff.patients.create') }}" class="btn btn-outline-primary btn-sm" style="border-radius: 8px; font-weight: 500;">
                        <i class="fas fa-user-plus me-1"></i>New Patient
                    </a>
                    </div>
                    @if($bookingLink)
                    <small class="d-block text-muted text-md-end text-break" id="booking-link-display" style="font-size: 0.7rem; max-width: 22rem;">{{ $bookingLink }}</small>
                    @else
                    <small class="d-block text-muted text-md-end" style="font-size: 0.7rem;">No department assigned to doctor</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Grid - Enhanced Design -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-enhanced" onclick="window.location.href='{{ route('staff.appointments.index') }}?date={{ now()->format('Y-m-d') }}'">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper" style="background: #000000;">
                        <i class="fas fa-calendar-day"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ $stats['today_appointments'] ?? 0 }}</div>
                        <div class="stat-label">Today's Appointments</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-enhanced" onclick="window.location.href='{{ route('staff.appointments.index') }}?status=pending'">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper" style="background: #000000;">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ $stats['pending_appointments'] ?? 0 }}</div>
                        <div class="stat-label">Pending Consultations</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-enhanced" onclick="window.location.href='{{ route('staff.patients.index') }}'">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper" style="background: #000000;">
                        <i class="fas fa-user-injured"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ $stats['total_patients'] ?? 0 }}</div>
                        <div class="stat-label">Total Patients</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-lg-6 col-md-6">
            <div class="stat-card-enhanced" onclick="window.location.href='{{ route('staff.appointments.index') }}'">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper" style="background: #000000;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ $stats['total_appointments'] ?? 0 }}</div>
                        <div class="stat-label">Total Appointments</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quincy Prescription Delivery Status -->
    @if(isset($quincyDeliveryStatus) && ($quincyDeliveryStatus['available'] ?? false))
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-prescription-bottle-alt me-2 text-primary"></i>
                        Quincy prescription delivery status
                    </h5>
                    @if(($quincyDeliveryStatus['stats']['total'] ?? 0) > 0)
                        @php
                            $successRate = $quincyDeliveryStatus['stats']['success_rate'] ?? 0;
                            $hasFailures = $quincyDeliveryStatus['has_failures'] ?? false;
                        @endphp
                        @if($successRate >= 90)
                            <span class="badge bg-success ms-2">
                                <i class="fas fa-check-circle me-1"></i>Excellent
                            </span>
                        @elseif($successRate >= 70)
                            <span class="badge bg-warning ms-2">
                                <i class="fas fa-exclamation-triangle me-1"></i>Good
                            </span>
                        @else
                            <span class="badge bg-danger ms-2">
                                <i class="fas fa-times-circle me-1"></i>Needs Attention
                            </span>
                        @endif
                    @endif
                </div>
                <div class="doctor-card-body">
                    @if(($quincyDeliveryStatus['stats']['total'] ?? 0) > 0)
                        <!-- Statistics Row -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3" style="background: #f8f9fa; border-radius: 8px;">
                                    <div class="fw-bold text-primary" style="font-size: 1.75rem;">{{ $quincyDeliveryStatus['stats']['total'] ?? 0 }}</div>
                                    <small class="text-muted d-block">Total Sent</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3" style="background: #d4edda; border-radius: 8px;">
                                    <div class="fw-bold text-success" style="font-size: 1.75rem;">{{ $quincyDeliveryStatus['stats']['successful'] ?? 0 }}</div>
                                    <small class="text-muted d-block">Successful</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3" style="background: #f8d7da; border-radius: 8px;">
                                    <div class="fw-bold text-danger" style="font-size: 1.75rem;">{{ $quincyDeliveryStatus['stats']['failed'] ?? 0 }}</div>
                                    <small class="text-muted d-block">Failed</small>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-center p-3" style="background: #fff3cd; border-radius: 8px;">
                                    <div class="fw-bold text-warning" style="font-size: 1.75rem;">{{ $quincyDeliveryStatus['stats']['pending'] ?? 0 }}</div>
                                    <small class="text-muted d-block">Pending</small>
                                </div>
                            </div>
                        </div>

                        <!-- Success Rate -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-bold">Success Rate</span>
                                <span class="fw-bold {{ ($quincyDeliveryStatus['stats']['success_rate'] ?? 0) >= 90 ? 'text-success' : (($quincyDeliveryStatus['stats']['success_rate'] ?? 0) >= 70 ? 'text-warning' : 'text-danger') }}">
                                    {{ $quincyDeliveryStatus['stats']['success_rate'] ?? 0 }}%
                                </span>
                            </div>
                            <div class="progress" style="height: 8px; border-radius: 4px;">
                                <div class="progress-bar {{ ($quincyDeliveryStatus['stats']['success_rate'] ?? 0) >= 90 ? 'bg-success' : (($quincyDeliveryStatus['stats']['success_rate'] ?? 0) >= 70 ? 'bg-warning' : 'bg-danger') }}" 
                                     role="progressbar" 
                                     style="width: {{ $quincyDeliveryStatus['stats']['success_rate'] ?? 0 }}%"
                                     aria-valuenow="{{ $quincyDeliveryStatus['stats']['success_rate'] ?? 0 }}" 
                                     aria-valuemin="0" 
                                     aria-valuemax="100">
                                </div>
                            </div>
                        </div>

                        <!-- Recent Failed Deliveries -->
                        @if(($quincyDeliveryStatus['has_failures'] ?? false) && count($quincyDeliveryStatus['recent_failed'] ?? []) > 0)
                        <div class="alert alert-warning mb-0">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <strong><i class="fas fa-exclamation-triangle me-2"></i>Recent Failed Deliveries:</strong>
                                <a href="{{ route('staff.prescriptions.index') }}" class="btn btn-sm btn-outline-warning">
                                    View All Prescriptions
                                </a>
                            </div>
                            <ul class="mb-0 mt-2">
                                @foreach($quincyDeliveryStatus['recent_failed'] as $failed)
                                <li class="mb-2">
                                    <strong>{{ $failed['patient_name'] }}</strong> - 
                                    Order #<code>{{ $failed['order_number'] }}</code>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle me-1"></i>{{ $failed['rejection_reason'] }}
                                    </small>
                                    <br>
                                    <small class="text-muted">
                                        <i class="fas fa-clock me-1"></i>{{ $failed['created_at'] }}
                                    </small>
                                    @if($failed['prescription_id'])
                                    <br>
                                    <a href="{{ route('staff.prescriptions.show', $failed['prescription_id']) }}" class="btn btn-xs btn-outline-primary btn-sm mt-1">
                                        View Prescription
                                    </a>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @elseif(($quincyDeliveryStatus['stats']['successful'] ?? 0) > 0)
                        <div class="alert alert-success mb-0">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>All clear!</strong> All recent prescription deliveries to Quincy were successful.
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-prescription-bottle-alt fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                            <p class="text-muted mb-0">No prescriptions have been sent to Quincy yet.</p>
                            <small class="text-muted">Prescriptions sent to Quincy will appear here once submitted.</small>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Upcoming Video Consultations -->
    <div class="row g-3 mb-4">
        <div class="col-12">
            <div class="doctor-card" style="border-left: 4px solid #6C63FF;">
                <div class="doctor-card-header" style="background: linear-gradient(135deg, rgba(108, 99, 255, 0.05) 0%, rgba(108, 99, 255, 0.02) 100%);">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-video me-2 text-primary"></i>
                            Upcoming video consultations
                        </h5>
                        <a href="{{ route('staff.appointments.index') }}?is_online=1&status=pending,confirmed" class="btn btn-sm btn-outline-primary">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="doctor-card-body">
                    @if(isset($upcomingVideoConsultations) && $upcomingVideoConsultations->count() > 0)
                    <div class="row g-3">
                        @foreach($upcomingVideoConsultations as $videoAppt)
                        <div class="col-lg-6">
                            <div class="video-consultation-card p-3 rounded" style="background: #f8f9fa; border: 1px solid #e9ecef;">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="doctor-user-avatar" style="width: 50px; height: 50px; background: linear-gradient(135deg, #6C63FF 0%, #5a52e0 100%);">
                                            <i class="fas fa-video" style="font-size: 1.25rem;"></i>
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <h6 class="mb-1 fw-bold">{{ $videoAppt->patient->first_name }} {{ $videoAppt->patient->last_name }}</h6>
                                                <div class="text-muted small">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ $videoAppt->appointment_date->format('D, M j, Y') }}
                                                    <span class="mx-1">•</span>
                                                    <i class="fas fa-clock me-1"></i>
                                                    {{ \Carbon\Carbon::parse($videoAppt->appointment_time)->format('h:i A') }}
                                                </div>
                                            </div>
                                            @if($videoAppt->appointment_date->isToday())
                                                <span class="badge bg-success">Today</span>
                                            @elseif($videoAppt->appointment_date->isTomorrow())
                                                <span class="badge bg-info">Tomorrow</span>
                                            @else
                                                <span class="badge bg-secondary">{{ $videoAppt->appointment_date->diffForHumans() }}</span>
                                            @endif
                                        </div>
                                        @if($videoAppt->service)
                                        <div class="mb-2">
                                            <span class="badge bg-light text-dark">
                                                <i class="fas fa-stethoscope me-1"></i>{{ $videoAppt->service->name }}
                                            </span>
                                        </div>
                                        @endif
                                        <div class="d-flex gap-2 mt-2">
                                            @if($videoAppt->canJoinMeeting())
                                                @if($videoAppt->whereby_host_url)
                                                    <a href="{{ $videoAppt->whereby_host_url }}" target="_blank" class="btn btn-sm btn-success">
                                                        <i class="fas fa-video me-1"></i>Start Meeting
                                                    </a>
                                                @elseif($videoAppt->meeting_link)
                                                    <a href="{{ $videoAppt->meeting_link }}" target="_blank" class="btn btn-sm btn-success">
                                                        <i class="fas fa-video me-1"></i>Join Meeting
                                                    </a>
                                                @endif
                                            @else
                                                <button class="btn btn-sm btn-outline-secondary" disabled title="Meeting available 15 minutes before appointment">
                                                    <i class="fas fa-clock me-1"></i>Not Yet Available
                                                </button>
                                            @endif
                                            <a href="{{ route('staff.appointments.show', $videoAppt->id) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye me-1"></i>Details
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @else
                    <div class="text-center py-4">
                        <i class="fas fa-video fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                        <p class="text-muted mb-0">No upcoming video consultations.</p>
                        <small class="text-muted">Online appointments will appear here when scheduled.</small>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .stat-card-enhanced {
            background: #ffffff;
            border: 1px solid rgba(0, 0, 0, 0.08);
            border-radius: 16px;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
            position: relative;
            overflow: hidden;
        }

        .stat-card-enhanced::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, transparent, rgba(102, 126, 234, 0.3), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .stat-card-enhanced:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            border-color: rgba(102, 126, 234, 0.2);
        }

        .stat-card-enhanced:hover::before {
            opacity: 1;
        }

        .stat-card-content {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .stat-icon-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
        }

        .stat-icon-wrapper i {
            color: #ffffff;
            font-size: 1.5rem;
        }

        .stat-card-enhanced:hover .stat-icon-wrapper {
            transform: scale(1.1) rotate(5deg);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
        }

        .stat-info {
            flex: 1;
            min-width: 0;
        }

        .stat-number {
            font-size: 1.75rem;
            font-weight: 700;
            color: #212529;
            line-height: 1.2;
            margin-bottom: 0.25rem;
            letter-spacing: -0.5px;
        }

        .stat-label {
            font-size: 0.875rem;
            color: #6c757d;
            line-height: 1.4;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .stat-card-enhanced {
                padding: 1rem;
            }

            .stat-icon-wrapper {
                width: 48px;
                height: 48px;
            }

            .stat-icon-wrapper i {
                font-size: 1.25rem;
            }

            .stat-number {
                font-size: 1.5rem;
            }

            .stat-label {
                font-size: 0.8rem;
            }
        }
    </style>

    <!-- Quick Actions - Streamlined -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0">
                <i class="fas fa-bolt me-2 text-primary"></i>
                Quick actions
            </h5>
        </div>
        <div class="doctor-card-body">
            <div class="row g-3">
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.patients.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon" style="background: transparent; color: #000;">
                            <i class="fas fa-user-plus"></i>
                        </div>
                        <div class="doctor-quick-action-title">New Patient</div>
                        <div class="doctor-quick-action-subtitle">Register</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.appointments.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon" style="background: transparent; color: #000;">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="doctor-quick-action-title">Schedule</div>
                        <div class="doctor-quick-action-subtitle">Appointment</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.medical-records.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon" style="background: transparent; color: #000;">
                            <i class="fas fa-file-medical"></i>
                        </div>
                        <div class="doctor-quick-action-title">Medical Record</div>
                        <div class="doctor-quick-action-subtitle">Create</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.prescriptions.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon" style="background: transparent; color: #000;">
                            <i class="fas fa-prescription-bottle-alt"></i>
                        </div>
                        <div class="doctor-quick-action-title">Prescription</div>
                        <div class="doctor-quick-action-subtitle">Write</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.doctor-services.index') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon" style="background: transparent; color: #000;">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div class="doctor-quick-action-title">Services</div>
                        <div class="doctor-quick-action-subtitle">Manage</div>
                    </a>
                </div>
                
                <div class="col-lg-2 col-md-4 col-sm-6">
                    <a href="{{ route('staff.lab-reports.create') }}" class="doctor-quick-action">
                        <div class="doctor-quick-action-icon" style="background: transparent; color: #000;">
                            <i class="fas fa-vial"></i>
                        </div>
                        <div class="doctor-quick-action-title">Lab Order</div>
                        <div class="doctor-quick-action-subtitle">Request</div>
                    </a>
                </div>
                
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="row g-4 mb-4">
        <!-- Calendar Widget - Left Side -->
        <div class="col-xl-8 col-lg-7">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-calendar-alt me-2 text-primary"></i>
                            Appointments calendar
                        </h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('staff.appointments.calendar') }}" class="btn btn-sm btn-doctor-primary" title="View Full Calendar">
                                <i class="fas fa-external-link-alt"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="doctor-card-body p-0">
                    <div id="dashboard-calendar" style="height: 450px; min-height: 450px; width: 100%; background: #f8f9fa; border-radius: 0 0 8px 8px;">
                        <div class="d-flex align-items-center justify-content-center h-100">
                            <div class="text-center">
                                <div class="spinner-border text-primary mb-3" role="status">
                                    <span class="visually-hidden">Loading calendar...</span>
                                </div>
                                <p class="text-muted mb-0">Loading calendar...</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Today's Schedule - Right Side (Show by default if appointments exist) -->
        <div class="col-xl-4 col-lg-5">
            <div class="doctor-card" id="today-schedule-card-sidebar">
                <div class="doctor-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h6 class="doctor-card-title mb-0">
                            <i class="fas fa-clock me-2 text-primary"></i>
                            Today's schedule
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted small" id="current-time">{{ now()->format('H:i A') }}</span>
                        </div>
                    </div>
                </div>
                <div class="doctor-card-body" style="max-height: 450px; overflow-y: auto;">
                    @if(isset($todayAppointments) && $todayAppointments->count() > 0)
                        <div class="list-group list-group-flush">
                            @foreach($todayAppointments->take(10) as $appointment)
                            <div class="list-group-item border-0 px-0 py-3 border-bottom">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <div class="doctor-user-avatar" style="width: 40px; height: 40px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                            {{ strtoupper(substr($appointment->patient->first_name ?? 'N', 0, 1)) }}
                                        </div>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <div>
                                                <div class="fw-semibold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</div>
                                                <small class="text-muted">
                                                    <i class="fas fa-clock me-1"></i>{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : 'TBD' }}
                                                </small>
                                            </div>
                                            <span class="badge 
                                                @if($appointment->status === 'confirmed') bg-success
                                                @elseif($appointment->status === 'pending') bg-warning
                                                @elseif($appointment->status === 'cancelled') bg-danger
                                                @else bg-secondary
                                                @endif
                                            " style="font-size: 0.7rem;">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </div>
                                        <div class="d-flex gap-2 mt-2">
                                            <a href="{{ route('staff.appointments.show', $appointment->id) }}" class="btn btn-sm btn-outline-primary" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                            @if($appointment->is_online && ($appointment->whereby_host_url || $appointment->meeting_link) && $appointment->canJoinMeeting())
                                                <a href="{{ $appointment->whereby_host_url ?? $appointment->meeting_link }}" target="_blank" class="btn btn-sm btn-success" style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                                    <i class="fas fa-video"></i> Start
                                                </a>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                        @if($todayAppointments->count() > 10)
                        <div class="text-center mt-3">
                            <a href="{{ route('staff.appointments.index') }}?date={{ now()->format('Y-m-d') }}" class="btn btn-sm btn-doctor-primary">
                                View All Today's Appointments <i class="fas fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-calendar-check fa-3x text-muted mb-3" style="opacity: 0.3;"></i>
                            <h6 class="text-muted mb-2">No appointments today</h6>
                            <p class="text-muted small mb-3">You have a free schedule</p>
                            <a href="{{ route('staff.appointments.create') }}" class="btn btn-sm btn-doctor-primary">
                                <i class="fas fa-plus me-1"></i>Schedule Appointment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Appointments & Patients Row -->
    <div class="row g-4">
        <!-- Recent Appointments -->
        <div class="col-xl-8 col-lg-7">
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="doctor-card-title mb-0">
                            <i class="fas fa-history me-2 text-primary"></i>
                            Recent appointments
                        </h5>
                        <a href="{{ route('staff.appointments.index') }}" class="btn btn-sm btn-doctor-primary">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="doctor-card-body">
                    @if(isset($recentAppointments) && $recentAppointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Patient</th>
                                        <th>Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAppointments->take(8) as $appointment)
                                    <tr>
                                        <td>
                                            <div class="fw-semibold">{{ $appointment->appointment_date->format('M d') }}</div>
                                            <small class="text-muted">{{ $appointment->appointment_date->format('Y') }}</small>
                                        </td>
                                        <td>
                                            <strong>{{ $appointment->appointment_time ? \Carbon\Carbon::parse($appointment->appointment_time)->format('h:i A') : 'TBD' }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="doctor-user-avatar me-2" style="width: 36px; height: 36px; font-size: 0.8rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                                    {{ strtoupper(substr($appointment->patient->first_name, 0, 1)) }}
                                                </div>
                                                <div>
                                                    <div class="fw-semibold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</div>
                                                    <small class="text-muted">#{{ $appointment->appointment_number }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark">
                                                {{ ucfirst(str_replace('_', ' ', $appointment->type ?? 'consultation')) }}
                                            </span>
                                            @if($appointment->is_online)
                                                <span class="badge bg-info ms-1">
                                                    <i class="fas fa-video"></i>
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge 
                                                @if($appointment->status === 'confirmed') bg-success
                                                @elseif($appointment->status === 'pending') bg-warning
                                                @elseif($appointment->status === 'cancelled') bg-danger
                                                @else bg-secondary
                                                @endif
                                            ">
                                                {{ ucfirst($appointment->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('staff.appointments.show', $appointment->id) }}" class="btn btn-outline-primary btn-sm" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if($appointment->is_online && ($appointment->whereby_host_url || $appointment->meeting_link) && $appointment->canJoinMeeting())
                                                    <a href="{{ $appointment->whereby_host_url ?? $appointment->meeting_link }}" target="_blank" class="btn btn-success btn-sm" title="Start Meeting as Host">
                                                        <i class="fas fa-video"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-4x text-muted mb-3" style="opacity: 0.3;"></i>
                            <h6 class="text-muted mb-2">No recent appointments</h6>
                            <p class="text-muted mb-4">Start by booking your first appointment</p>
                            <a href="{{ route('staff.appointments.create') }}" class="btn btn-doctor-primary">
                                <i class="fas fa-plus me-2"></i>Schedule New Appointment
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Widgets -->
        <div class="col-xl-4 col-lg-5">
            <!-- Recent Patients -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <div class="d-flex align-items-center justify-content-between">
                            <h6 class="doctor-card-title mb-0">
                                <i class="fas fa-users me-2 text-primary"></i>
                                Recent patients
                            </h6>
                        <a href="{{ route('staff.patients.index') }}" class="btn btn-sm btn-link text-primary p-0">
                            View All <i class="fas fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
                <div class="doctor-card-body">
                    @if(isset($recentAppointments) && $recentAppointments->count() > 0)
                        @foreach($recentAppointments->take(6) as $appointment)
                        <div class="d-flex align-items-center mb-3 pb-3 border-bottom">
                            <div class="doctor-user-avatar me-3" style="width: 45px; height: 45px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);">
                                {{ strtoupper(substr($appointment->patient->first_name, 0, 1)) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-semibold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</div>
                                <small class="text-muted">
                                    <i class="fas fa-calendar me-1"></i>{{ formatDateUk($appointment->appointment_date) }}
                                </small>
                            </div>
                            <a href="{{ route('staff.patients.show', $appointment->patient_id) }}" class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-eye"></i>
                            </a>
                        </div>
                        @endforeach
                    @else
                        <div class="text-center py-3">
                            <i class="fas fa-user-slash fa-2x text-muted mb-2" style="opacity: 0.3;"></i>
                            <p class="text-muted mb-0 small">No recent patients</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Quick Insights -->
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h6 class="doctor-card-title mb-0">
                        <i class="fas fa-chart-line me-2 text-primary"></i>
                        Quick insights
                    </h6>
                </div>
                <div class="doctor-card-body">
                    <div class="row g-3">
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%); border: 1px solid rgba(13, 110, 253, 0.2);">
                                <div class="fs-3 fw-bold text-primary mb-1">{{ $stats['total_patients'] ?? 0 }}</div>
                                <small class="text-muted d-block">Total Patients</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(25, 135, 84, 0.05) 100%); border: 1px solid rgba(25, 135, 84, 0.2);">
                                <div class="fs-3 fw-bold text-success mb-1">{{ $stats['total_appointments'] ?? 0 }}</div>
                                <small class="text-muted d-block">Total Appointments</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%); border: 1px solid rgba(255, 193, 7, 0.2);">
                                <div class="fs-3 fw-bold text-warning mb-1">{{ $stats['pending_appointments'] ?? 0 }}</div>
                                <small class="text-muted d-block">Pending</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-center p-3 rounded" style="background: linear-gradient(135deg, rgba(13, 202, 240, 0.1) 0%, rgba(13, 202, 240, 0.05) 100%); border: 1px solid rgba(13, 202, 240, 0.2);">
                                <div class="fs-3 fw-bold text-info mb-1">{{ $stats['today_appointments'] ?? 0 }}</div>
                                <small class="text-muted d-block">Today</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<!-- FullCalendar CSS -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.css" rel="stylesheet" />
<style>
    /* FullCalendar specific styles for dashboard widget */
    #dashboard-calendar {
        font-size: 0.9rem;
    }
    
    #dashboard-calendar .fc-header-toolbar {
        margin-bottom: 1rem;
        padding: 0.5rem;
    }
    
    #dashboard-calendar .fc-button {
        padding: 0.35rem 0.75rem;
        font-size: 0.85rem;
    }
    
    #dashboard-calendar .fc-event {
        font-size: 0.75rem;
        padding: 1px 3px;
    }
    
    /* Enhanced Stat Cards */
    .doctor-stat-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(0, 0, 0, 0.05);
    }
    
    .doctor-stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
    }
    
    /* Quick Action Enhancements */
    .doctor-quick-action {
        transition: all 0.3s ease;
        text-decoration: none;
        color: inherit;
    }
    
    .doctor-quick-action:hover {
        transform: translateY(-3px);
    }
    
    .doctor-quick-action-icon {
        transition: all 0.3s ease;
        color: white;
    }
    
    .doctor-quick-action:hover .doctor-quick-action-icon {
        transform: scale(1.1);
    }
    
    /* Table Enhancements */
    .table tbody tr {
        transition: all 0.2s ease;
    }
    
    .table tbody tr:hover {
        background-color: #f8f9fa;
        transform: translateX(3px);
    }
    
    /* List Group Enhancements */
    .list-group-item {
        transition: all 0.2s ease;
    }
    
    .list-group-item:hover {
        background-color: #f8f9fa;
    }
</style>
@endpush

@push('scripts')
<!-- FullCalendar JavaScript -->
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js"></script>
<script>
// Copy booking link function - Make it globally accessible
window.copyBookingLink = function(link) {
    if (!link || link === 'null' || link === 'undefined') {
        console.error('No link provided to copyBookingLink');
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'error',
                title: 'Link Unavailable',
                text: 'Booking link is not available. Please ensure your doctor profile is complete.',
            });
        } else {
            alert('Booking link is not available. Please ensure your doctor profile is complete.');
        }
        return false;
    }

    console.log('Attempting to copy link:', link);

    // Try modern clipboard API first
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(link).then(function() {
            console.log('Link copied successfully via Clipboard API');
            // Show success message
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Link Copied!',
                    text: 'Your booking link has been copied to clipboard.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert('Booking link copied to clipboard!');
            }
        }).catch(function(err) {
            console.error('Clipboard API failed, trying fallback:', err);
            // Fallback for older browsers or when clipboard API fails
            window.fallbackCopy(link);
        });
    } else {
        console.log('Clipboard API not available, using fallback');
        // Fallback for browsers without clipboard API
        window.fallbackCopy(link);
    }
};

// Fallback copy function (also make it globally accessible)
window.fallbackCopy = function(link) {
    const textArea = document.createElement('textarea');
    textArea.value = link;
    textArea.style.position = 'fixed';
    textArea.style.top = '0';
    textArea.style.left = '0';
    textArea.style.width = '2em';
    textArea.style.height = '2em';
    textArea.style.padding = '0';
    textArea.style.border = 'none';
    textArea.style.outline = 'none';
    textArea.style.boxShadow = 'none';
    textArea.style.background = 'transparent';
    document.body.appendChild(textArea);
    textArea.focus();
    textArea.select();
    
    try {
        const successful = document.execCommand('copy');
        if (successful) {
            console.log('Link copied successfully via fallback method');
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Link Copied!',
                    text: 'Your booking link has been copied to clipboard.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                alert('Booking link copied to clipboard!');
            }
        } else {
            throw new Error('execCommand returned false');
        }
    } catch (err) {
        console.error('Failed to copy: ', err);
        // Show the link in a prompt so user can copy manually
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'info',
                title: 'Copy Link Manually',
                html: '<p>Please copy this link:</p><input type="text" class="form-control mt-2" value="' + link + '" readonly onclick="this.select(); document.execCommand(\'copy\');" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">',
                confirmButtonText: 'OK',
                width: '600px'
            });
        } else {
            prompt('Copy this link:', link);
        }
    }
    document.body.removeChild(textArea);
};

document.addEventListener('DOMContentLoaded', function() {
    // Add fade-in animation to cards
    const cards = document.querySelectorAll('.doctor-card, .doctor-stat-card');
    cards.forEach((card, index) => {
        card.style.animationDelay = `${index * 0.1}s`;
        card.classList.add('fade-in-up');
    });
    
    // Dashboard Calendar Widget - Initialize with delay to ensure DOM is ready
    setTimeout(function() {
        const dashboardCalendarEl = document.getElementById('dashboard-calendar');
        if (dashboardCalendarEl) {
            console.log('Initializing dashboard calendar...');
            console.log('Calendar element found:', dashboardCalendarEl);
            
            // Check if FullCalendar is loaded
            if (typeof FullCalendar === 'undefined') {
                console.error('FullCalendar library not loaded!');
                dashboardCalendarEl.innerHTML = '<div class="alert alert-warning p-3"><i class="fas fa-exclamation-triangle me-2"></i>Calendar library failed to load. Please refresh the page.</div>';
                return;
            }
            
            console.log('FullCalendar library loaded, creating calendar instance...');
            
            // Clear loading message
            dashboardCalendarEl.innerHTML = '';

            const dashboardCalendar = new FullCalendar.Calendar(dashboardCalendarEl, {
                initialView: 'dayGridMonth',
                height: 'auto',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                events: function(fetchInfo, successCallback, failureCallback) {
                    const params = new URLSearchParams({
                        start: fetchInfo.start.toISOString().split('T')[0],
                        end: fetchInfo.end.toISOString().split('T')[0]
                    });
                    
                    fetch(`{{ route('staff.api.appointments.calendar-data') }}?${params}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        const events = data.map(appointment => ({
                            id: appointment.id,
                            title: appointment.title,
                            start: appointment.start,
                            end: appointment.end,
                            backgroundColor: appointment.backgroundColor,
                            borderColor: appointment.borderColor,
                            textColor: appointment.textColor || '#fff',
                            url: `{{ route('staff.appointments.show', '') }}/${appointment.id}`
                        }));
                        successCallback(events);
                    })
                    .catch(error => {
                        console.error('Error loading calendar data:', error);
                        if (failureCallback) failureCallback(error);
                    });
                },
                eventClick: function(arg) {
                    arg.jsEvent.preventDefault();
                    window.location.href = arg.event.url;
                }
            });
            
            dashboardCalendar.render();
            console.log('Dashboard calendar rendered successfully');
        } else {
            console.error('Dashboard calendar element not found!');
        }
    }, 500); // Wait 500ms to ensure DOM is ready
    
    // Update current time every minute
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit',
            hour12: true 
        });
        const timeElement = document.getElementById('current-time');
        const heroTimeElement = document.getElementById('hero-current-time');
        if (timeElement) {
            timeElement.textContent = timeString;
        }
        if (heroTimeElement) {
            heroTimeElement.textContent = timeString;
        }
    }
    
    updateTime();
    setInterval(updateTime, 60000);
});
</script>
@endpush

