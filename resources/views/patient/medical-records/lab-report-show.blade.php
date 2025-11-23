@extends('patient.layouts.app')

@section('title', 'Lab Report Details')
@section('page-title', 'Lab Report Details')

@section('content')
    <!-- Lab Report Header -->
    <div class="card mb-4">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">
                        <i class="fas fa-flask me-2"></i>
                        {{ $labReport->test_name }}
                    </h5>
                    <div class="d-flex align-items-center">
                        @php
                            $statusClass = match($labReport->status) {
                                'completed' => 'bg-success',
                                'pending' => 'bg-warning',
                                'in_progress' => 'bg-info',
                                'cancelled' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                        @endphp
                        <span class="badge {{ $statusClass }} me-2">{{ ucfirst(str_replace('_', ' ', $labReport->status)) }}</span>
                        <span class="badge bg-info me-2">{{ ucfirst(str_replace('_', ' ', $labReport->test_type)) }}</span>
                        @if($labReport->priority !== 'normal')
                            @php
                                $priorityClass = match($labReport->priority) {
                                    'urgent' => 'bg-warning',
                                    'stat' => 'bg-danger',
                                    default => 'bg-secondary'
                                };
                            @endphp
                            <span class="badge {{ $priorityClass }}">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                {{ ucfirst($labReport->priority) }} Priority
                            </span>
                        @endif
                    </div>
                </div>
                <div class="btn-group">
                    <a href="{{ route('patient.lab-reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Back to Lab Reports
                    </a>
                    @if($labReport->file_path && $labReport->status === 'completed')
                        <a href="{{ route('patient.lab-reports.download', $labReport) }}" class="btn btn-outline-success">
                            <i class="fas fa-download me-1"></i>
                            Download Report
                        </a>
                    @endif
                    <button type="button" class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>
                        Print Report
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Alert -->
    @if($labReport->status === 'pending')
        <div class="alert alert-warning">
            <i class="fas fa-clock me-2"></i>
            <strong>Test Pending:</strong> Your laboratory test has been scheduled and will be processed soon. Results will be available once the test is completed.
        </div>
    @elseif($labReport->status === 'in_progress')
        <div class="alert alert-info">
            <i class="fas fa-spinner me-2"></i>
            <strong>Processing:</strong> Your test is currently being processed in our laboratory. Results will be available soon.
        </div>
    @elseif($labReport->status === 'completed')
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i>
            <strong>Test Completed:</strong> Your laboratory test results are now available below.
        </div>
    @endif

    <div class="row">
        <!-- Lab Report Details -->
        <div class="col-lg-8 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        Test Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">Test Date & Time</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar text-primary me-2"></i>
                                <div>
                                    <strong>{{ $labReport->test_date->format('l, M d, Y') }}</strong><br>
                                    <small class="text-muted">Report #{{ $labReport->report_number }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">Doctor</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-md text-success me-2"></i>
                                <div>
                                    <strong>Dr. {{ $labReport->doctor->full_name }}</strong><br>
                                    <span class="text-muted">{{ $labReport->doctor->specialization }}</span>
                                </div>
                            </div>
                        </div>
                        @if($labReport->lab_technician)
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">Lab Technician</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-cog text-info me-2"></i>
                                <div>
                                    <strong>{{ $labReport->lab_technician }}</strong>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($labReport->appointment)
                        <div class="col-md-6 mb-4">
                            <h6 class="text-muted mb-2">Related Appointment</h6>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-calendar-check text-info me-2"></i>
                                <div>
                                    <strong>#{{ $labReport->appointment->appointment_number ?? 'N/A' }}</strong><br>
                                    <span class="text-muted">{{ $labReport->appointment->appointment_date->format('M d, Y') }}</span>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Test Results & Analysis -->
            @if($labReport->status === 'completed' && ($labReport->results || $labReport->interpretation || $labReport->notes))
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Test Results & Analysis
                    </h5>
                </div>
                <div class="card-body">
                    @if($labReport->results)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">Test Results</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-clipboard-list text-success me-2"></i>
                                {{ $labReport->results }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($labReport->reference_range)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">Reference Range</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-ruler text-info me-2"></i>
                                {{ $labReport->reference_range }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($labReport->interpretation)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">Medical Interpretation</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-stethoscope text-primary me-2"></i>
                                {{ $labReport->interpretation }}
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($labReport->notes)
                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-muted mb-2">Additional Notes</h6>
                            <div class="bg-light p-3 rounded">
                                <i class="fas fa-sticky-note text-warning me-2"></i>
                                {{ $labReport->notes }}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @else
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-hourglass-half me-2"></i>
                        Test Results
                    </h5>
                </div>
                <div class="card-body">
                    <div class="text-center py-5">
                        <i class="fas fa-flask fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">Results Not Yet Available</h5>
                        <p class="text-muted">Your test results will appear here once the laboratory has completed processing your sample.</p>
                        @if($labReport->priority === 'stat')
                            <div class="alert alert-danger">
                                <strong>STAT Priority:</strong> Immediate processing - results expected soon
                            </div>
                        @elseif($labReport->priority === 'urgent')
                            <div class="alert alert-warning">
                                <strong>Urgent Priority:</strong> Processing within 2-4 hours
                            </div>
                        @else
                            <div class="alert alert-info">
                                <strong>Normal Priority:</strong> Standard 24-48 hour processing time
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>

        <div class="col-lg-4">
            <!-- File Attachment -->
            @if($labReport->file_path && $labReport->status === 'completed')
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="card-title mb-0"><i class="fas fa-file me-2"></i>Report File</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="file-icon mb-3">
                            @php
                                $extension = pathinfo($labReport->file_path, PATHINFO_EXTENSION);
                            @endphp
                            @if(in_array(strtolower($extension), ['pdf']))
                                <i class="fas fa-file-pdf text-danger" style="font-size: 3rem;"></i>
                            @elseif(in_array(strtolower($extension), ['doc', 'docx']))
                                <i class="fas fa-file-word text-primary" style="font-size: 3rem;"></i>
                            @elseif(in_array(strtolower($extension), ['jpg', 'jpeg', 'png']))
                                <i class="fas fa-file-image text-success" style="font-size: 3rem;"></i>
                            @else
                                <i class="fas fa-file text-secondary" style="font-size: 3rem;"></i>
                            @endif
                        </div>
                        <p class="mb-3"><strong>{{ basename($labReport->file_path) }}</strong></p>
                        <a href="{{ route('patient.lab-reports.download', $labReport) }}" class="btn btn-success">
                            <i class="fas fa-download me-1"></i>Download File
                        </a>
                    </div>
                </div>
            @endif

            <!-- Report Timeline -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="fas fa-clock me-2"></i>Report Timeline</h6>
                </div>
                <div class="card-body">
                    <div class="timeline-item mb-3">
                        <strong>Test Ordered:</strong> {{ $labReport->created_at->format('M j, Y g:i A') }}<br>
                        <small class="text-muted">{{ $labReport->created_at->diffForHumans() }}</small>
                    </div>
                    <div class="timeline-item mb-3">
                        <strong>Test Date:</strong> {{ $labReport->test_date->format('M j, Y') }}<br>
                        <small class="text-muted">{{ $labReport->test_date->diffForHumans() }}</small>
                    </div>
                    @if($labReport->updated_at != $labReport->created_at)
                        <div class="timeline-item">
                            <strong>Last Updated:</strong> {{ $labReport->updated_at->format('M j, Y g:i A') }}<br>
                            <small class="text-muted">{{ $labReport->updated_at->diffForHumans() }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Test Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Understanding Your Results</h6>
                </div>
                <div class="card-body">
                    <ul class="mb-0" style="font-size: 0.9rem;">
                        <li class="mb-2">Reference ranges show normal values for healthy individuals</li>
                        <li class="mb-2">Values outside the reference range may require medical attention</li>
                        <li class="mb-2">Always discuss your results with your doctor</li>
                        <li class="mb-2">Some factors like age, gender, and health conditions can affect results</li>
                        <li>Contact your doctor if you have questions about your results</li>
                    </ul>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title mb-0"><i class="fas fa-phone me-2"></i>Need Help?</h6>
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Your Doctor:</strong><br>
                    {{ $labReport->doctor->full_name }}<br>
                    <a href="mailto:{{ $labReport->doctor->email }}" class="text-primary">{{ $labReport->doctor->email }}</a></p>
                    
                    <p class="mb-0"><strong>Hospital Contact:</strong><br>
                    For questions about your lab results, please contact your doctor or our medical records department.</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
