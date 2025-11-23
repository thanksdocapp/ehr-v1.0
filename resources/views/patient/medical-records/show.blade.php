@extends('patient.layouts.app')

@section('title', 'Medical Record Details')
@section('page-title', 'Medical Record Details')

@section('content')
    <!-- Medical Record Header -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">
                        <i class="fas fa-file-medical me-2"></i>
                        Medical Record #{{ $record->id }}
                    </h5>
                    <div class="d-flex align-items-center">
                        @php
                            $statusClass = match($record->record_type) {
                                'consultation' => 'bg-primary',
                                'diagnosis' => 'bg-success',
                                'prescription' => 'bg-warning',
                                'lab_result' => 'bg-info',
                                'follow_up' => 'bg-secondary',
                                'discharge' => 'bg-dark',
                                default => 'bg-secondary'
                            };
                        @endphp
                        <span class="badge {{ $statusClass }} me-2">{{ ucfirst(str_replace('_', ' ', $record->record_type)) }}</span>
                        @if($record->is_private)
                            <span class="badge bg-danger">
                                <i class="fas fa-lock me-1"></i>
                                Private Record
                            </span>
                        @endif
                    </div>
                </div>
                <div class="btn-group">
                    <a href="{{ route('patient.medical-records.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Records
                    </a>
                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>
                        Print Record
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Medical Record Details -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Record Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">Date & Time</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                <div>
                                    <strong>{{ $record->created_at->format('l, M d, Y') }}</strong><br>
                                    <span class="text-muted">{{ $record->created_at->format('g:i A') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">Doctor</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-md text-success me-2"></i>
                                <div>
                                    <strong>Dr. {{ $record->doctor->full_name }}</strong><br>
                                    <span class="text-muted">{{ $record->doctor->specialization }}</span>
                                </div>
                            </div>
                        </div>
                        @if($record->appointment)
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">Related Appointment</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-check text-info me-2"></i>
                                <div>
                                    <strong>#{{ $record->appointment->appointment_number }}</strong><br>
                                    <span class="text-muted">{{ $record->appointment->appointment_date->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($record->follow_up_date)
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">Follow-up Date</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-plus text-warning me-2"></i>
                                <div>
                                    <strong>{{ \Carbon\Carbon::parse($record->follow_up_date)->format('M d, Y') }}</strong><br>
                                    <span class="text-muted">
                                        @if(\Carbon\Carbon::parse($record->follow_up_date)->isFuture())
                                            {{ \Carbon\Carbon::parse($record->follow_up_date)->diffForHumans() }}
                                        @else
                                            {{ \Carbon\Carbon::parse($record->follow_up_date)->diffForHumans() }}
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            @if($record->presenting_complaint || $record->chief_complaint || $record->history_of_presenting_complaint || $record->present_illness || $record->past_medical_history || $record->drug_history || $record->allergies || $record->social_history || $record->family_history || $record->ideas_concerns_expectations || $record->plan || $record->diagnosis || $record->symptoms || $record->treatment || $record->notes)
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-notes-medical me-2"></i>
                        Medical Information
                    </h5>
                </div>
                <div class="card-body">
                    @if($record->presenting_complaint || $record->chief_complaint)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">PC* (Presenting Complaint)</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-comment-medical text-primary me-2"></i>
                                {!! nl2br(e($record->presenting_complaint ?? $record->chief_complaint)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($record->history_of_presenting_complaint || $record->present_illness)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">HPC* (History of Presenting Complaint)</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-history text-primary me-2"></i>
                                {!! nl2br(e($record->history_of_presenting_complaint ?? $record->present_illness)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($record->past_medical_history)
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">PMH* (Past Medical History)</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-file-medical text-info me-2"></i>
                                {!! nl2br(e($record->past_medical_history)) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">DH* (Drug History)</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-pills text-info me-2"></i>
                                {!! nl2br(e($record->drug_history ?? 'N/A')) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($record->allergies)
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">Allergies* (Known Allergies)</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                                {!! nl2br(e($record->allergies)) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">SH (Social History)</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-users text-secondary me-2"></i>
                                {!! nl2br(e($record->social_history ?? 'N/A')) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($record->family_history || $record->ideas_concerns_expectations)
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">FH (Family History)</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-sitemap text-secondary me-2"></i>
                                {!! nl2br(e($record->family_history ?? 'N/A')) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-2">ICE* (Ideas, Concerns, Expectations)</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-lightbulb text-warning me-2"></i>
                                {!! nl2br(e($record->ideas_concerns_expectations ?? 'N/A')) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($record->plan)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">Plan* (Management Plan - investigations, treatment, follow-up)</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-clipboard-list text-success me-2"></i>
                                {!! nl2br(e($record->plan)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($record->diagnosis)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">Diagnosis</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-diagnoses text-success me-2"></i>
                                {{ $record->diagnosis }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($record->symptoms)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">Symptoms</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-thermometer text-warning me-2"></i>
                                {{ $record->symptoms }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($record->treatment)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">Treatment</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-pills text-primary me-2"></i>
                                {{ $record->treatment }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($record->notes)
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">Additional Notes</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-sticky-note text-info me-2"></i>
                                {{ $record->notes }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Vital Signs -->
            @if($record->vital_signs && array_filter($record->vital_signs))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-heartbeat me-2"></i>
                        Vital Signs
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @if(!empty($record->vital_signs['blood_pressure']))
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-thermometer text-danger mb-2" style="font-size: 1.5rem;"></i>
                                <h6 class="mb-1">{{ $record->vital_signs['blood_pressure'] }}</h6>
                                <small class="text-muted">Blood Pressure</small>
                            </div>
                        </div>
                        @endif

                        @if(!empty($record->vital_signs['temperature']))
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-temperature-low text-info mb-2" style="font-size: 1.5rem;"></i>
                                <h6 class="mb-1">{{ $record->vital_signs['temperature'] }}</h6>
                                <small class="text-muted">Temperature</small>
                            </div>
                        </div>
                        @endif

                        @if(!empty($record->vital_signs['pulse']))
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-heartbeat text-success mb-2" style="font-size: 1.5rem;"></i>
                                <h6 class="mb-1">{{ $record->vital_signs['pulse'] }}</h6>
                                <small class="text-muted">Pulse Rate</small>
                            </div>
                        </div>
                        @endif

                        @if(!empty($record->vital_signs['respiratory_rate']))
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-lungs text-primary mb-2" style="font-size: 1.5rem;"></i>
                                <h6 class="mb-1">{{ $record->vital_signs['respiratory_rate'] }}</h6>
                                <small class="text-muted">Respiratory Rate</small>
                            </div>
                        </div>
                        @endif

                        @if(!empty($record->vital_signs['oxygen_saturation']))
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-percentage text-warning mb-2" style="font-size: 1.5rem;"></i>
                                <h6 class="mb-1">{{ $record->vital_signs['oxygen_saturation'] }}</h6>
                                <small class="text-muted">Oxygen Saturation</small>
                            </div>
                        </div>
                        @endif

                        @if(!empty($record->vital_signs['weight']))
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-weight text-secondary mb-2" style="font-size: 1.5rem;"></i>
                                <h6 class="mb-1">{{ $record->vital_signs['weight'] }}</h6>
                                <small class="text-muted">Weight</small>
                            </div>
                        </div>
                        @endif

                        @if(!empty($record->vital_signs['height']))
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-ruler-vertical text-dark mb-2" style="font-size: 1.5rem;"></i>
                                <h6 class="mb-1">{{ $record->vital_signs['height'] }}</h6>
                                <small class="text-muted">Height</small>
                            </div>
                        </div>
                        @endif

                        @if(!empty($record->vital_signs['bmi']))
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 bg-light rounded">
                                <i class="fas fa-calculator text-info mb-2" style="font-size: 1.5rem;"></i>
                                <h6 class="mb-1">{{ number_format($record->vital_signs['bmi'], 1) }}</h6>
                                <small class="text-muted">BMI</small>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>
                        Quick Actions
                    </h5>
                </div>
                <div class="card-body">
                    <button type="button" class="btn btn-primary w-100 mb-2" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>
                        Print Record
                    </button>
                    
                    @if($record->follow_up_date && \Carbon\Carbon::parse($record->follow_up_date)->isFuture())
                        <div class="alert alert-info mb-2">
                            <i class="fas fa-info-circle me-1"></i>
                            <small>You have a follow-up appointment scheduled for {{ \Carbon\Carbon::parse($record->follow_up_date)->format('M d, Y') }}.</small>
                        </div>
                    @endif

                    <a href="{{ route('patient.appointments.create') }}" class="btn btn-outline-primary w-100 mb-2">
                        <i class="fas fa-plus me-1"></i>
                        Book New Appointment
                    </a>
                    
                    <a href="{{ route('patient.medical-records.index') }}" class="btn btn-outline-secondary w-100">
                        <i class="fas fa-list me-1"></i>
                        View All Records
                    </a>
                </div>
            </div>

            <!-- Doctor Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-user-md me-2"></i>
                        Doctor Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center mb-3">
                        @if($record->doctor->photo)
                            <img src="{{ asset('storage/uploads/doctors/' . $record->doctor->photo) }}" 
                                 alt="{{ $record->doctor->full_name }}" 
                                 class="rounded-circle" 
                                 style="width: 80px; height: 80px; object-fit: cover;">
                        @else
                            <div class="bg-primary rounded-circle d-inline-flex align-items-center justify-content-center text-white" 
                                 style="width: 80px; height: 80px;">
                                <i class="fas fa-user-md fa-2x"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="text-center">
                        <h6 class="mb-1">Dr. {{ $record->doctor->full_name }}</h6>
                        <p class="text-muted mb-2">{{ $record->doctor->specialization }}</p>
                        @if($record->doctor->experience_years)
                            <small class="text-muted">
                                <i class="fas fa-clock me-1"></i>
                                {{ $record->doctor->experience_years }} years experience
                            </small>
                        @endif
                    </div>

                    @if($record->doctor->bio)
                        <hr>
                        <p class="small text-muted mb-0">{{ Str::limit($record->doctor->bio, 100) }}</p>
                    @endif
                </div>
            </div>

            <!-- Record Timeline -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-history me-2"></i>
                        Record Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        <div class="timeline-item">
                            <div class="timeline-marker bg-primary"></div>
                            <div class="timeline-content">
                                <h6 class="mb-1">Record Created</h6>
                                <p class="text-muted mb-0">{{ $record->created_at->format('M d, Y g:i A') }}</p>
                            </div>
                        </div>
                        
                        @if($record->updated_at != $record->created_at)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-info"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Record Updated</h6>
                                    <p class="text-muted mb-0">{{ $record->updated_at->format('M d, Y g:i A') }}</p>
                                </div>
                            </div>
                        @endif

                        @if($record->follow_up_date)
                            <div class="timeline-item">
                                <div class="timeline-marker bg-warning"></div>
                                <div class="timeline-content">
                                    <h6 class="mb-1">Follow-up Scheduled</h6>
                                    <p class="text-muted mb-0">{{ \Carbon\Carbon::parse($record->follow_up_date)->format('M d, Y') }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
<style>
    .timeline {
        position: relative;
        padding-left: 30px;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        height: 100%;
        width: 2px;
        background: #dee2e6;
    }

    .timeline-item {
        position: relative;
        margin-bottom: 30px;
    }

    .timeline-item:last-child {
        margin-bottom: 0;
    }

    .timeline-marker {
        position: absolute;
        left: -23px;
        top: 5px;
        width: 16px;
        height: 16px;
        border-radius: 50%;
        border: 3px solid #fff;
        box-shadow: 0 0 0 3px #dee2e6;
    }

    .timeline-content {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
        border-left: 3px solid #007bff;
    }

    @media print {
        .btn, .card-header .btn-group, .timeline, .card:last-child {
            display: none !important;
        }
        
        .card {
            border: 1px solid #dee2e6 !important;
            box-shadow: none !important;
            margin-bottom: 1rem !important;
        }

        .bg-light {
            border: 1px solid #dee2e6 !important;
        }
    }
</style>
@endsection
