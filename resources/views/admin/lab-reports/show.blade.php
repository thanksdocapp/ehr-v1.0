@extends('admin.layouts.app')

@section('title', 'Lab Report Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('lab-reports.index') }}">Lab Reports</a></li>
    <li class="breadcrumb-item active">Lab Report Details</li>
@endsection

@push('styles')
<style>
.details-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
}

.details-section-header {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
}

.details-section-body {
    padding: 2rem;
}

.detail-item {
    margin-bottom: 1.5rem;
}

.detail-label {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.detail-value {
    color: #2c3e50;
    font-size: 0.95rem;
    line-height: 1.6;
}

.status-badge {
    display: inline-block;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.status-in_progress {
    background: #cce5ff;
    color: #004085;
    border: 1px solid #74b9ff;
}

.status-completed {
    background: #d4edda;
    color: #155724;
    border: 1px solid #00b894;
}

.status-cancelled {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #fd79a8;
}

.priority-badge {
    display: inline-block;
    padding: 0.4rem 0.8rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.priority-normal {
    background: #e8f5e8;
    color: #2d5016;
}

.priority-urgent {
    background: #fff3cd;
    color: #856404;
}

.priority-stat {
    background: #f8d7da;
    color: #721c24;
}

.action-buttons {
    display: flex;
    gap: 1rem;
    flex-wrap: wrap;
}

.btn {
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
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

.btn-outline-secondary {
    border: 2px solid #6c757d;
    color: #6c757d;
    background: transparent;
}

.btn-outline-secondary:hover {
    background: #6c757d;
    color: white;
}

.btn-info {
    background: linear-gradient(135deg, #36b9cc 0%, #1cc88a 100%);
    border: none;
    color: white;
}

.btn-success {
    background: linear-gradient(135deg, #00b894 0%, #1cc88a 100%);
    border: none;
    color: white;
}

.info-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.info-card h6 {
    color: #5a5c69;
    margin-bottom: 1rem;
}

.file-preview {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1rem;
    text-align: center;
}

.file-icon {
    font-size: 3rem;
    color: #1cc88a;
    margin-bottom: 1rem;
}

.related-info {
    background: #e8f4f8;
    border-left: 4px solid #36b9cc;
    padding: 1rem;
    margin: 1rem 0;
    border-radius: 0 8px 8px 0;
}

.text-muted {
    color: #6c757d !important;
}

.text-primary {
    color: #1cc88a !important;
}

.empty-state {
    text-align: center;
    color: #6c757d;
    font-style: italic;
    padding: 2rem;
    background: #f8f9fc;
    border-radius: 8px;
    border: 1px dashed #dee2e6;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-flask me-2 text-primary"></i>Lab Report Details</h1>
                <p class="page-subtitle text-muted">Detailed view of laboratory test report</p>
            </div>
            <div class="action-buttons">
                <a href="{{ contextRoute('lab-reports.edit', $labReport) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-2"></i>Edit Report
                </a>
                @if($labReport->file_path)
                    <a href="{{ contextRoute('lab-reports.download', $labReport) }}" class="btn btn-info">
                        <i class="fas fa-download me-2"></i>Download File
                    </a>
                @endif
                <a href="{{ contextRoute('lab-reports.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <!-- Report Information -->
            <div class="details-section">
                <div class="details-section-header">
                    <h4 class="mb-0"><i class="fas fa-file-medical me-2"></i>Report Information</h4>
                    <small class="opacity-75">Basic laboratory report details and identification</small>
                </div>
                <div class="details-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-hashtag me-1"></i>Report Number
                                </div>
                                <div class="detail-value">
                                    <strong class="text-primary">{{ $labReport->report_number }}</strong>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-flask me-1"></i>Test Name
                                </div>
                                <div class="detail-value">{{ $labReport->test_name }}</div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-list-alt me-1"></i>Test Type
                                </div>
                                <div class="detail-value">
                                    <span class="badge bg-light text-dark">{{ ucfirst(str_replace('_', ' ', $labReport->test_type)) }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-calendar me-1"></i>Test Date
                                </div>
                                <div class="detail-value">{{ formatDate($labReport->test_date) }}</div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-info-circle me-1"></i>Status
                                </div>
                                <div class="detail-value">
                                    <span class="status-badge status-{{ $labReport->status }}">
                                        {{ ucfirst(str_replace('_', ' ', $labReport->status)) }}
                                    </span>
                                </div>
                            </div>

                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Priority
                                </div>
                                <div class="detail-value">
                                    <span class="priority-badge priority-{{ $labReport->priority }}">
                                        {{ ucfirst($labReport->priority) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient & Doctor Information -->
            <div class="details-section">
                <div class="details-section-header">
                    <h4 class="mb-0"><i class="fas fa-users me-2"></i>Patient & Doctor Information</h4>
                    <small class="opacity-75">Details about the patient and attending physician</small>
                </div>
                <div class="details-section-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-user-injured me-1"></i>Patient
                                </div>
                                <div class="detail-value">
                                    <strong>{{ $labReport->patient->full_name }}</strong><br>
                                    <small class="text-muted">ID: {{ $labReport->patient->patient_id }}</small><br>
                                    <small class="text-muted">{{ $labReport->patient->email }}</small><br>
                                    <small class="text-muted">{{ $labReport->patient->phone }}</small>
                                </div>
                            </div>

                            @if($labReport->lab_technician)
                                <div class="detail-item">
                                    <div class="detail-label">
                                        <i class="fas fa-user-cog me-1"></i>Lab Technician
                                    </div>
                                    <div class="detail-value">{{ $labReport->lab_technician }}</div>
                                </div>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <div class="detail-item">
                                <div class="detail-label">
                                    <i class="fas fa-user-md me-1"></i>Doctor
                                </div>
                                <div class="detail-value">
                                    <strong>{{ $labReport->doctor->full_name }}</strong><br>
                                    <small class="text-muted">{{ $labReport->doctor->specialization }}</small><br>
                                    <small class="text-muted">{{ $labReport->doctor->email }}</small><br>
                                    <small class="text-muted">{{ $labReport->doctor->phone }}</small>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($labReport->appointment)
                        <div class="related-info">
                            <strong><i class="fas fa-calendar-check me-2"></i>Related Appointment</strong><br>
                            <small>Appointment #{{ $labReport->appointment->id }} - {{ $labReport->appointment->appointment_date->format('M j, Y g:i A') }}</small>
                        </div>
                    @endif

                    @if($labReport->medicalRecord)
                        <div class="related-info">
                            <strong><i class="fas fa-file-medical me-2"></i>Related Medical Record</strong><br>
                            <small>Record #{{ $labReport->medicalRecord->id }} - {{ $labReport->medicalRecord->created_at->format('M j, Y') }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Test Results & Analysis -->
            <div class="details-section">
                <div class="details-section-header">
                    <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i>Test Results & Analysis</h4>
                    <small class="opacity-75">Detailed test results and medical interpretation</small>
                </div>
                <div class="details-section-body">
                    <div class="detail-item">
                        <div class="detail-label">
                            <i class="fas fa-clipboard-list me-1"></i>Test Results
                        </div>
                        <div class="detail-value">
                            @if($labReport->results)
                                <div style="background: #f8f9fc; padding: 1rem; border-radius: 8px; border-left: 4px solid #1cc88a;">
                                    {!! nl2br(e($labReport->results)) !!}
                                </div>
                            @else
                                <div class="empty-state">
                                    <i class="fas fa-clipboard me-2"></i>No test results available
                                </div>
                            @endif
                        </div>
                    </div>

                    @if($labReport->reference_range)
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-ruler me-1"></i>Reference Range
                            </div>
                            <div class="detail-value">
                                <div style="background: #e8f4f8; padding: 1rem; border-radius: 8px; border-left: 4px solid #36b9cc;">
                                    {!! nl2br(e($labReport->reference_range)) !!}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($labReport->interpretation)
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-stethoscope me-1"></i>Medical Interpretation
                            </div>
                            <div class="detail-value">
                                <div style="background: #fff3e0; padding: 1rem; border-radius: 8px; border-left: 4px solid #ff9800;">
                                    {!! nl2br(e($labReport->interpretation)) !!}
                                </div>
                            </div>
                        </div>
                    @endif

                    @if($labReport->notes)
                        <div class="detail-item">
                            <div class="detail-label">
                                <i class="fas fa-sticky-note me-1"></i>Additional Notes
                            </div>
                            <div class="detail-value">
                                <div style="background: #f3e5f5; padding: 1rem; border-radius: 8px; border-left: 4px solid #9c27b0;">
                                    {!! nl2br(e($labReport->notes)) !!}
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- File Attachment -->
            @if($labReport->file_path)
                <div class="info-card">
                    <h6><i class="fas fa-file me-2"></i>Report File</h6>
                    <div class="file-preview">
                        <div class="file-icon">
                            @php
                                $extension = pathinfo($labReport->file_path, PATHINFO_EXTENSION);
                            @endphp
                            @if(in_array(strtolower($extension), ['pdf']))
                                <i class="fas fa-file-pdf text-danger"></i>
                            @elseif(in_array(strtolower($extension), ['doc', 'docx']))
                                <i class="fas fa-file-word text-primary"></i>
                            @elseif(in_array(strtolower($extension), ['jpg', 'jpeg', 'png']))
                                <i class="fas fa-file-image text-success"></i>
                            @else
                                <i class="fas fa-file text-secondary"></i>
                            @endif
                        </div>
                        <p class="mb-2"><strong>{{ basename($labReport->file_path) }}</strong></p>
                        <a href="{{ contextRoute('lab-reports.download', $labReport) }}" class="btn btn-success btn-sm">
                            <i class="fas fa-download me-1"></i>Download File
                        </a>
                    </div>
                </div>
            @endif

            <!-- Report Timeline -->
            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Report Timeline</h6>
                <div class="timeline">
                    <div class="timeline-item">
                        <strong>Created:</strong> {{ $labReport->created_at->format('M j, Y g:i A') }}<br>
                        <small class="text-muted">{{ $labReport->created_at->diffForHumans() }}</small>
                    </div>
                    @if($labReport->updated_at != $labReport->created_at)
                        <div class="timeline-item mt-2">
                            <strong>Last Updated:</strong> {{ $labReport->updated_at->format('M j, Y g:i A') }}<br>
                            <small class="text-muted">{{ $labReport->updated_at->diffForHumans() }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Processing Information -->
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Processing Guidelines</h6>
                <ul class="mb-0">
                    @if($labReport->priority === 'stat')
                        <li class="text-danger"><strong>STAT Priority:</strong> Immediate processing required</li>
                    @elseif($labReport->priority === 'urgent')
                        <li class="text-warning"><strong>Urgent Priority:</strong> Process within 2-4 hours</li>
                    @else
                        <li class="text-success"><strong>Normal Priority:</strong> Standard 24-48 hour turnaround</li>
                    @endif
                    
                    @if($labReport->status === 'pending')
                        <li class="text-info">Report is awaiting processing</li>
                    @elseif($labReport->status === 'in_progress')
                        <li class="text-primary">Report is currently being processed</li>
                    @elseif($labReport->status === 'completed')
                        <li class="text-success">Report has been completed and is ready</li>
                    @elseif($labReport->status === 'cancelled')
                        <li class="text-danger">Report has been cancelled</li>
                    @endif
                </ul>
            </div>

            <!-- Quick Actions -->
            <div class="info-card">
                <h6><i class="fas fa-tools me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('lab-reports.edit', $labReport) }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-edit me-1"></i>Edit Report
                    </a>
                    @if($labReport->file_path)
                        <a href="{{ contextRoute('lab-reports.download', $labReport) }}" class="btn btn-outline-info btn-sm">
                            <i class="fas fa-download me-1"></i>Download File
                        </a>
                    @endif
                    <a href="{{ contextRoute('patients.show', $labReport->patient) }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-user-injured me-1"></i>View Patient
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
