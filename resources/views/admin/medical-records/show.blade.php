@extends('admin.layouts.app')

@section('title', 'Medical Record Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.medical-records.index') }}">Medical Records</a></li>
    <li class="breadcrumb-item active">Record #{{ $medicalRecord->id }}</li>
@endsection

@push('styles')
<style>
.record-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
    overflow: hidden;
}

.record-section-header {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
    padding: 1.5rem 2rem;
}

.record-section-body {
    padding: 2rem;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0.75rem 0;
    border-bottom: 1px solid #f1f3f4;
}

.info-row:last-child {
    border-bottom: none;
}

.info-label {
    font-weight: 600;
    color: #5a5c69;
    min-width: 150px;
}

.info-value {
    color: #858796;
    flex: 1;
}

.badge {
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}

.badge-consultation {
    background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
    color: white;
}

.badge-diagnosis {
    background: linear-gradient(135deg, #1cc88a 0%, #17a673 100%);
    color: white;
}

.badge-prescription {
    background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%);
    color: white;
}

.badge-lab-result {
    background: linear-gradient(135deg, #e74a3b 0%, #c0392b 100%);
    color: white;
}

.badge-follow-up {
    background: linear-gradient(135deg, #36b9cc 0%, #258391 100%);
    color: white;
}

.badge-discharge {
    background: linear-gradient(135deg, #858796 0%, #5a5c69 100%);
    color: white;
}

.badge-private {
    background: linear-gradient(135deg, #e74a3b 0%, #fd79a8 100%);
    color: white;
}

.badge-public {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
}

.vital-signs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 1.5rem;
}

.vital-sign-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
}

.vital-sign-icon {
    font-size: 2rem;
    color: #1cc88a;
    margin-bottom: 0.5rem;
}

.vital-sign-value {
    font-size: 1.25rem;
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.25rem;
}

.vital-sign-label {
    color: #858796;
    font-size: 0.85rem;
}

.text-area-content {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1rem;
    color: #5a5c69;
    line-height: 1.6;
    white-space: pre-wrap;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.75rem 1.5rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
}

.btn-primary {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    border: none;
    color: white;
    box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
    color: white;
}

.btn-secondary {
    background: #858796;
    border: none;
    color: white;
}

.btn-secondary:hover {
    background: #5a5c69;
    color: white;
}

.btn-danger {
    background: #e74a3b;
    border: none;
    color: white;
}

.btn-danger:hover {
    background: #c0392b;
    color: white;
}

.quick-info-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.quick-info-card h6 {
    color: #5a5c69;
    margin-bottom: 1rem;
}

.timeline-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
    color: #858796;
    font-size: 0.9rem;
}

.timeline-icon {
    color: #1cc88a;
    margin-right: 0.5rem;
    width: 16px;
}

@media (max-width: 768px) {
    .vital-signs-grid {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        justify-content: center;
    }
    
    .info-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 0.5rem;
    }
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-file-medical-alt me-2 text-primary"></i>Medical Record Details</h1>
        <p class="page-subtitle text-muted">View comprehensive medical record information</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Patient & Doctor Information -->
            <div class="record-section">
                <div class="record-section-header">
                    <h4 class="mb-0"><i class="fas fa-user-md me-2"></i>Patient & Doctor Information</h4>
                    <small class="opacity-75">Basic record and personnel details</small>
                </div>
                <div class="record-section-body">
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-hashtag me-1"></i>Record ID:</div>
                        <div class="info-value">#{{ $medicalRecord->id }}</div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-user me-1"></i>Patient:</div>
                        <div class="info-value">
                            @if($medicalRecord->patient)
                                <strong>{{ $medicalRecord->patient->full_name }}</strong>
                                <small class="text-muted">({{ $medicalRecord->patient->patient_id }})</small>
                            @else
                                <span class="text-muted">Patient record deleted</span>
                            @endif
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-stethoscope me-1"></i>Doctor:</div>
                        <div class="info-value">
                            @if($medicalRecord->doctor)
                                <strong>{{ formatDoctorName($medicalRecord->doctor->full_name) }}</strong>
                                <small class="text-muted">({{ $medicalRecord->doctor->specialization }})</small>
                            @else
                                <span class="text-muted">Doctor record deleted</span>
                            @endif
                        </div>
                    </div>
                    @if($medicalRecord->appointment)
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-calendar-check me-1"></i>Appointment:</div>
                        <div class="info-value">
                            #{{ $medicalRecord->appointment->appointment_number }} - 
                            {{ formatDate($medicalRecord->appointment->appointment_date) }}
                        </div>
                    </div>
                    @endif
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-file-alt me-1"></i>Record Type:</div>
                        <div class="info-value">
                            <span class="badge badge-{{ $medicalRecord->record_type }}">
                                {{ ucfirst(str_replace('_', ' ', $medicalRecord->record_type)) }}
                            </span>
                        </div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-shield-alt me-1"></i>Privacy:</div>
                        <div class="info-value">
                            <span class="badge badge-{{ $medicalRecord->is_private ? 'private' : 'public' }}">
                                <i class="fas fa-{{ $medicalRecord->is_private ? 'lock' : 'unlock' }} me-1"></i>
                                {{ $medicalRecord->is_private ? 'Private' : 'Public' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Information -->
            <div class="record-section">
                <div class="record-section-header">
                    <h4 class="mb-0"><i class="fas fa-notes-medical me-2"></i>Medical Information</h4>
                    <small class="opacity-75">Patient medical history and clinical information</small>
                </div>
                <div class="record-section-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary"><i class="fas fa-comment-medical me-1"></i>PC* <small class="text-muted">(Presenting Complaint)</small></label>
                            <div class="border rounded p-3 bg-light">
                                {!! nl2br(e($medicalRecord->presenting_complaint ?? $medicalRecord->chief_complaint ?? 'N/A')) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary"><i class="fas fa-history me-1"></i>HPC* <small class="text-muted">(History of Presenting Complaint)</small></label>
                            <div class="border rounded p-3 bg-light">
                                {!! nl2br(e($medicalRecord->history_of_presenting_complaint ?? $medicalRecord->present_illness ?? 'N/A')) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary"><i class="fas fa-file-medical me-1"></i>PMH* <small class="text-muted">(Past Medical History)</small></label>
                            <div class="border rounded p-3 bg-light">
                                {!! nl2br(e($medicalRecord->past_medical_history ?? 'N/A')) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-primary"><i class="fas fa-pills me-1"></i>DH* <small class="text-muted">(Drug History)</small></label>
                            <div class="border rounded p-3 bg-light">
                                {!! nl2br(e($medicalRecord->drug_history ?? 'N/A')) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Allergies* <small class="text-muted">(Known Allergies)</small></label>
                            <div class="border rounded p-3 bg-light">
                                {!! nl2br(e($medicalRecord->allergies ?? 'N/A')) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-info"><i class="fas fa-users me-1"></i>SH <small class="text-muted">(Social History)</small></label>
                            <div class="border rounded p-3 bg-light">
                                {!! nl2br(e($medicalRecord->social_history ?? 'N/A')) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-info"><i class="fas fa-sitemap me-1"></i>FH <small class="text-muted">(Family History)</small></label>
                            <div class="border rounded p-3 bg-light">
                                {!! nl2br(e($medicalRecord->family_history ?? 'N/A')) !!}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-warning"><i class="fas fa-lightbulb me-1"></i>ICE* <small class="text-muted">(Ideas, Concerns, Expectations)</small></label>
                            <div class="border rounded p-3 bg-light">
                                {!! nl2br(e($medicalRecord->ideas_concerns_expectations ?? 'N/A')) !!}
                            </div>
                        </div>
                    </div>

                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-success"><i class="fas fa-clipboard-list me-1"></i>Plan* <small class="text-muted">(Management Plan - investigations, treatment, follow-up)</small></label>
                            <div class="border rounded p-3 bg-light">
                                {!! nl2br(e($medicalRecord->plan ?? 'N/A')) !!}
                            </div>
                        </div>
                    </div>

                    @if($medicalRecord->diagnosis)
                    <div class="mb-4">
                        <h6><i class="fas fa-diagnoses me-1"></i>Diagnosis</h6>
                        <div class="text-area-content">{{ $medicalRecord->diagnosis }}</div>
                    </div>
                    @endif

                    @if($medicalRecord->symptoms)
                    <div class="mb-4">
                        <h6><i class="fas fa-symptoms me-1"></i>Symptoms</h6>
                        <div class="text-area-content">{{ $medicalRecord->symptoms }}</div>
                    </div>
                    @endif

                    @if($medicalRecord->treatment)
                    <div class="mb-4">
                        <h6><i class="fas fa-pills me-1"></i>Treatment</h6>
                        <div class="text-area-content">{{ $medicalRecord->treatment }}</div>
                    </div>
                    @endif

                    @if($medicalRecord->notes)
                    <div class="mb-4">
                        <h6><i class="fas fa-sticky-note me-1"></i>Additional Notes</h6>
                        <div class="text-area-content">{{ $medicalRecord->notes }}</div>
                    </div>
                    @endif

                    @if($medicalRecord->follow_up_date)
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-calendar-plus me-1"></i>Follow-up Date:</div>
                        <div class="info-value">
                            <strong>{{ formatDate($medicalRecord->follow_up_date) }}</strong>
                            @if(\Carbon\Carbon::parse($medicalRecord->follow_up_date)->isFuture())
                                <small class="text-success">({{ \Carbon\Carbon::parse($medicalRecord->follow_up_date)->diffForHumans() }})</small>
                            @else
                                <small class="text-warning">({{ \Carbon\Carbon::parse($medicalRecord->follow_up_date)->diffForHumans() }})</small>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- File Attachments -->
            @if($medicalRecord->attachments && $medicalRecord->attachments->count() > 0)
            <div class="record-section">
                <div class="record-section-header" style="background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);">
                    <h4 class="mb-0"><i class="fas fa-paperclip me-2"></i>Documents or Attachments</h4>
                    <small class="opacity-75">Files attached to this medical record</small>
                </div>
                <div class="record-section-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th>File Name</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($medicalRecord->attachments as $attachment)
                                <tr>
                                    <td>
                                        <span class="text-muted">{{ $attachment->description ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <i class="fas fa-{{ $attachment->file_icon }} me-2 text-primary"></i>
                                        <strong>{{ $attachment->file_name }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($attachment->file_category) }}</span>
                                    </td>
                                    <td>{{ $attachment->file_size_human }}</td>
                                    <td>
                                        {{ $attachment->uploader->name ?? 'Unknown' }}
                                        @if($attachment->uploader)
                                            <br><small class="text-muted">{{ ucfirst($attachment->uploader->role ?? 'Staff') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ formatDate($attachment->created_at) }}</small><br>
                                        <small class="text-muted">{{ $attachment->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        @if($attachment->virus_scan_status === 'pending')
                                            <span class="badge bg-warning"><i class="fas fa-clock me-1"></i>Scan pending</span>
                                        @elseif($attachment->virus_scan_status === 'clean')
                                            <span class="badge bg-success"><i class="fas fa-shield-alt me-1"></i>Scanned clean</span>
                                        @elseif($attachment->virus_scan_status === 'infected')
                                            <span class="badge bg-danger"><i class="fas fa-exclamation-triangle me-1"></i>Infected</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($attachment->virus_scan_status) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($attachment->canAccess(auth()->user()) && $attachment->virus_scan_status !== 'infected')
                                            @if($attachment->isViewable())
                                                <a href="{{ route('admin.medical-record-attachments.view', $attachment) }}" 
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-info me-1" 
                                                   title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                            <a href="{{ route('admin.medical-record-attachments.download', $attachment) }}" 
                                               class="btn btn-sm btn-outline-primary me-1" 
                                               title="Download">
                                                <i class="fas fa-download"></i>
                                            </a>
                                        @else
                                            <span class="text-muted">Access restricted</span>
                                        @endif
                                        @if(auth()->user()->is_admin || $attachment->uploaded_by === auth()->id())
                                            <form action="{{ route('admin.medical-record-attachments.destroy', $attachment) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this file?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif

            <!-- Vital Signs -->
            @if($medicalRecord->vital_signs && array_filter($medicalRecord->vital_signs))
            <div class="record-section">
                <div class="record-section-header">
                    <h4 class="mb-0"><i class="fas fa-heartbeat me-2"></i>Vital Signs</h4>
                    <small class="opacity-75">Patient vital signs and measurements</small>
                </div>
                <div class="record-section-body">
                    <div class="vital-signs-grid">
                        @if(!empty($medicalRecord->vital_signs['blood_pressure']))
                        <div class="vital-sign-card">
                            <div class="vital-sign-icon"><i class="fas fa-thermometer"></i></div>
                            <div class="vital-sign-value">{{ $medicalRecord->vital_signs['blood_pressure'] }}</div>
                            <div class="vital-sign-label">Blood Pressure</div>
                        </div>
                        @endif

                        @if(!empty($medicalRecord->vital_signs['temperature']))
                        <div class="vital-sign-card">
                            <div class="vital-sign-icon"><i class="fas fa-temperature-low"></i></div>
                            <div class="vital-sign-value">{{ $medicalRecord->vital_signs['temperature'] }}</div>
                            <div class="vital-sign-label">Temperature</div>
                        </div>
                        @endif

                        @if(!empty($medicalRecord->vital_signs['pulse']))
                        <div class="vital-sign-card">
                            <div class="vital-sign-icon"><i class="fas fa-heartbeat"></i></div>
                            <div class="vital-sign-value">{{ $medicalRecord->vital_signs['pulse'] }}</div>
                            <div class="vital-sign-label">Pulse Rate</div>
                        </div>
                        @endif

                        @if(!empty($medicalRecord->vital_signs['respiratory_rate']))
                        <div class="vital-sign-card">
                            <div class="vital-sign-icon"><i class="fas fa-lungs"></i></div>
                            <div class="vital-sign-value">{{ $medicalRecord->vital_signs['respiratory_rate'] }}</div>
                            <div class="vital-sign-label">Respiratory Rate</div>
                        </div>
                        @endif

                        @if(!empty($medicalRecord->vital_signs['oxygen_saturation']))
                        <div class="vital-sign-card">
                            <div class="vital-sign-icon"><i class="fas fa-percentage"></i></div>
                            <div class="vital-sign-value">{{ $medicalRecord->vital_signs['oxygen_saturation'] }}</div>
                            <div class="vital-sign-label">Oxygen Saturation</div>
                        </div>
                        @endif

                        @if(!empty($medicalRecord->vital_signs['weight']))
                        <div class="vital-sign-card">
                            <div class="vital-sign-icon"><i class="fas fa-weight"></i></div>
                            <div class="vital-sign-value">{{ $medicalRecord->vital_signs['weight'] }}</div>
                            <div class="vital-sign-label">Weight</div>
                        </div>
                        @endif

                        @if(!empty($medicalRecord->vital_signs['height']))
                        <div class="vital-sign-card">
                            <div class="vital-sign-icon"><i class="fas fa-ruler-vertical"></i></div>
                            <div class="vital-sign-value">{{ $medicalRecord->vital_signs['height'] }}</div>
                            <div class="vital-sign-label">Height</div>
                        </div>
                        @endif

                        @if(!empty($medicalRecord->vital_signs['bmi']))
                        <div class="vital-sign-card">
                            <div class="vital-sign-icon"><i class="fas fa-calculator"></i></div>
                            <div class="vital-sign-value">{{ number_format($medicalRecord->vital_signs['bmi'], 1) }}</div>
                            <div class="vital-sign-label">BMI</div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Action Buttons -->
            <div class="record-section">
                <div class="record-section-body text-center">
                    <div class="action-buttons">
                        <a href="{{ contextRoute('medical-records.edit', $medicalRecord->id) }}" class="btn btn-doctor-primary">
                            <i class="fas fa-edit me-2"></i>Edit Record
                        </a>
                        <a href="{{ contextRoute('medical-records.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Records
                        </a>
                        <button type="button" class="btn btn-danger" onclick="return deleteRecord({{ $medicalRecord->id }})">
                            <i class="fas fa-trash me-2"></i>Delete Record
                        </button>
                    </div>
                </div>
            </div>

            <!-- Audit Trail -->
            @if(isset($auditActivities) && $auditActivities->count() > 0)
            <div class="record-section">
                <div class="record-section-header">
                    <h4 class="mb-0"><i class="fas fa-history me-2"></i>Audit Trail</h4>
                    <small class="opacity-75">Record activity and verification history</small>
                </div>
                <div class="record-section-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Staff/Doctor</th>
                                    <th>Event</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($auditActivities as $activity)
                                @php
                                $iconMap = ['create' => 'plus', 'update' => 'edit', 'delete' => 'trash', 'view' => 'eye', 'login' => 'sign-in-alt', 'logout' => 'sign-out-alt'];
                                $icon = $iconMap[$activity->action] ?? 'circle';
                                @endphp
                                <tr style="cursor: pointer;" onclick="window.location.href='{{ route('admin.advanced-reports.audit-trail.show', $activity->id) }}'">
                                    <td>
                                        <small class="text-muted">{{ $activity->created_at->format('d-m-y') }}</small><br>
                                        <small class="text-muted">{{ $activity->created_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <strong>{{ $activity->user->name ?? 'System' }}</strong>
                                        @if($activity->user)
                                            <br><small class="text-muted">{{ ucfirst($activity->user->role ?? 'Staff') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $activity->severity_badge }}">
                                            @if($activity->action === 'pre_consultation_verified')
                                                <i class="fas fa-clipboard-check me-1"></i>Pre-consultation Verified
                                            @else
                                                <i class="fas fa-{{ $icon }} me-1"></i>{{ ucfirst(str_replace('_', ' ', $activity->action)) }}
                                            @endif
                                        </span>
                                    </td>
                                    <td>
                                        @if($activity->action === 'pre_consultation_verified')
                                            <strong class="text-success">Pre-consultation verification confirmed</strong><br>
                                            <small class="text-muted">{{ $activity->description }}</small>
                                        @else
                                            {{ $activity->description }}
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar Information -->
        <div class="col-lg-4">
            <div class="quick-info-card">
                <h6><i class="fas fa-clock me-2"></i>Record Timeline</h6>
                <div class="timeline-item">
                    <i class="fas fa-plus-circle timeline-icon"></i>
                    Created: {{ formatDateTime($medicalRecord->created_at) }}
                </div>
                <div class="timeline-item">
                    <i class="fas fa-edit timeline-icon"></i>
                    Last Updated: {{ formatDateTime($medicalRecord->updated_at) }}
                </div>
                @if($medicalRecord->follow_up_date)
                <div class="timeline-item">
                    <i class="fas fa-calendar-plus timeline-icon"></i>
                    Follow-up: {{ formatDate($medicalRecord->follow_up_date) }}
                </div>
                @endif
            </div>

            <div class="quick-info-card">
                <h6><i class="fas fa-user-circle me-2"></i>Patient Information</h6>
                @if($medicalRecord->patient)
                    <div class="timeline-item">
                        <i class="fas fa-id-card timeline-icon"></i>
                        Patient ID: {{ $medicalRecord->patient->patient_id }}
                    </div>
                    <div class="timeline-item">
                        <i class="fas fa-birthday-cake timeline-icon"></i>
                        Age: {{ \Carbon\Carbon::parse($medicalRecord->patient->date_of_birth)->age }} years
                    </div>
                    <div class="timeline-item">
                        <i class="fas fa-venus-mars timeline-icon"></i>
                        Gender: {{ ucfirst($medicalRecord->patient->gender) }}
                    </div>
                    <div class="timeline-item">
                        <i class="fas fa-phone timeline-icon"></i>
                        Phone: {{ $medicalRecord->patient->phone }}
                    </div>
                @else
                    <div class="timeline-item">
                        <i class="fas fa-exclamation-circle timeline-icon"></i>
                        <span class="text-muted">Patient record has been deleted</span>
                    </div>
                @endif
            </div>

            <div class="quick-info-card">
                <h6><i class="fas fa-user-md me-2"></i>Doctor Information</h6>
                @if($medicalRecord->doctor)
                    <div class="timeline-item">
                        <i class="fas fa-graduation-cap timeline-icon"></i>
                        Specialisation: {{ $medicalRecord->doctor->specialization }}
                    </div>
                    <div class="timeline-item">
                        <i class="fas fa-phone timeline-icon"></i>
                        Phone: {{ $medicalRecord->doctor->phone }}
                    </div>
                    <div class="timeline-item">
                        <i class="fas fa-envelope timeline-icon"></i>
                        Email: {{ $medicalRecord->doctor->email }}
                    </div>
                @else
                    <div class="timeline-item">
                        <i class="fas fa-exclamation-circle timeline-icon"></i>
                        <span class="text-muted">Doctor record has been deleted</span>
                    </div>
                @endif
            </div>

            <div class="quick-info-card">
                <h6><i class="fas fa-shield-alt me-2"></i>Security & Privacy</h6>
                <div class="timeline-item">
                    <i class="fas fa-lock timeline-icon"></i>
                    GDPR Compliant: Yes
                </div>
                <div class="timeline-item">
                    <i class="fas fa-key timeline-icon"></i>
                    Encrypted: Yes
                </div>
                <div class="timeline-item">
                    <i class="fas fa-eye timeline-icon"></i>
                    Access Level: {{ $medicalRecord->is_private ? 'Restricted' : 'Standard' }}
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this medical record?</p>
                <p class="text-danger"><strong>Warning:</strong> This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Record</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteRecord(recordId) {
    console.log('Delete function called with recordId:', recordId);
    
    const handleConfirmation = (confirmed) => {
        if (confirmed) {
            console.log('User confirmed deletion, proceeding...');
            
            // Small delay to ensure UI update
            setTimeout(() => {
                // Create a form to submit the DELETE request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/medical-records/${recordId}`;
                form.style.display = 'none';
                
                // Add CSRF token - try multiple methods
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                
                // Try to get CSRF token from meta tag or Laravel's global
                let csrfTokenValue = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                if (!csrfTokenValue && typeof Laravel !== 'undefined') {
                    csrfTokenValue = Laravel.csrfToken;
                }
                if (!csrfTokenValue && typeof window.Laravel !== 'undefined') {
                    csrfTokenValue = window.Laravel.csrfToken;
                }
                
                csrfToken.value = csrfTokenValue;
                form.appendChild(csrfToken);
                
                console.log('CSRF token:', csrfTokenValue);
                
                // Add DELETE method
                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';
                form.appendChild(methodInput);
                
                console.log('Form action:', form.action);
                console.log('Form method:', form.method);
                console.log('Form children:', form.children);
                
                // Add form to document and submit
                document.body.appendChild(form);
                console.log('Form added to document, submitting...');
                form.submit();
            }, 100);
        } else {
            console.log('User cancelled deletion');
        }
    }
    
    // Use a more explicit confirmation dialog
    const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this medical record?\n\nThis action cannot be undone and will remove:\n- Patient medical information\n- Diagnosis and treatment data\n- Vital signs and notes\n- All associated record data\n\nClick OK to confirm deletion or Cancel to abort.');
    
    // Handle both Promise and boolean returns
    if (confirmDelete && typeof confirmDelete.then === 'function') {
        // If it's a Promise, wait for it to resolve
        confirmDelete.then(handleConfirmation).catch(() => handleConfirmation(false));
    } else {
        // If it's a boolean, handle it directly
        handleConfirmation(confirmDelete);
    }
    
    return false;
}

// Print functionality
function printRecord() {
    window.print();
}

// Add print styles
const printStyles = `
@media print {
    .action-buttons, .quick-info-card, .record-section-header {
        display: none !important;
    }
    .record-section {
        box-shadow: none !important;
        border: 1px solid #ddd !important;
        margin-bottom: 1rem !important;
    }
    .page-title {
        border-bottom: 2px solid #000;
        padding-bottom: 1rem;
        margin-bottom: 2rem;
    }
}
`;

const styleSheet = document.createElement('style');
styleSheet.textContent = printStyles;
document.head.appendChild(styleSheet);
</script>
@endpush
