@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Lab Report Details')

@section('content')
<div class="fade-in-up">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">Lab Report Details</h1>
                    <p class="text-muted mb-0">
                        Report Number: <strong>{{ $labReport->report_number }}</strong>
                    </p>
                </div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a href="{{ route('staff.dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('staff.lab-reports.index') }}">Lab Reports</a></li>
                        <li class="breadcrumb-item active">Report Details</li>
                    </ol>
                </nav>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Patient & Test Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-doctor-card-header">
                    <h5 class="doctor-doctor-card-title mb-0">
                        <i class="fas fa-user me-2"></i>Patient & Test Information
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th width="40%">Patient:</th>
                                        <td>{{ $labReport->patient->first_name }} {{ $labReport->patient->last_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Patient ID:</th>
                                        <td>{{ $labReport->patient->patient_id ?? $labReport->patient->id }}</td>
                                    </tr>
                                    <tr>
                                        <th>Doctor:</th>
                                        <td>{{ $labReport->doctor ? $labReport->doctor->first_name . ' ' . $labReport->doctor->last_name : 'Not assigned' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Medical Record:</th>
                                        <td>{{ $labReport->medicalRecord ? '#' . $labReport->medicalRecord->id : 'Not linked' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tbody>
                                    <tr>
                                        <th width="40%">Test Name:</th>
                                        <td>{{ $labReport->test_name }}</td>
                                    </tr>
                                    <tr>
                                        <th>Test Type:</th>
                                        <td><span class="badge bg-info">{{ ucfirst($labReport->test_type) }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Category:</th>
                                        <td><span class="badge bg-secondary">{{ ucfirst($labReport->test_category) }}</span></td>
                                    </tr>
                                    <tr>
                                        <th>Specimen:</th>
                                        <td><span class="badge bg-warning text-dark">{{ ucfirst($labReport->specimen_type) }}</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Test Results -->
            <div class="doctor-card mb-4">
                <div class="doctor-doctor-card-header">
                    <h5 class="doctor-doctor-card-title mb-0">
                        <i class="fas fa-microscope me-2"></i>Test Results
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <strong>Collection Date:</strong><br>
                            <span class="text-muted">{{ $labReport->collection_date ? $labReport->collection_date->format('d M, Y') : 'Not specified' }}</span>
                        </div>
                        <div class="col-md-6">
                            <strong>Report Date:</strong><br>
                            <span class="text-muted">{{ $labReport->report_date ? $labReport->report_date->format('d M, Y') : 'Not specified' }}</span>
                        </div>
                    </div>
                    
                    @if($labReport->results)
                        <div class="mb-3">
                            <strong>Results:</strong>
                            <div class="border p-3 mt-2 bg-light">
                                {{ $labReport->results }}
                            </div>
                        </div>
                    @endif

                    @if($labReport->normal_range)
                        <div class="mb-3">
                            <strong>Normal Range:</strong>
                            <div class="border p-3 mt-2 bg-info bg-opacity-10">
                                {{ $labReport->normal_range }}
                            </div>
                        </div>
                    @endif

                    @if($labReport->interpretation)
                        <div class="mb-3">
                            <strong>Interpretation:</strong>
                            <div class="border p-3 mt-2 bg-warning bg-opacity-10">
                                {{ $labReport->interpretation }}
                            </div>
                        </div>
                    @endif

                    @if($labReport->technician_notes)
                        <div class="mb-3">
                            <strong>Technician Notes:</strong>
                            <div class="border p-3 mt-2 bg-secondary bg-opacity-10">
                                {{ $labReport->technician_notes }}
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Status & Actions -->
            <div class="doctor-card mb-4">
                <div class="doctor-doctor-card-header">
                    <h5 class="doctor-doctor-card-title mb-0">
                        <i class="fas fa-cog me-2"></i>Status & Actions
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <div class="mb-3">
                        <strong>Status:</strong><br>
                        @php
                            $statusColors = [
                                'pending' => 'warning',
                                'in_progress' => 'info',
                                'completed' => 'success',
                                'cancelled' => 'danger'
                            ];
                            $statusColor = $statusColors[$labReport->status] ?? 'secondary';
                        @endphp
                        <span class="badge bg-{{ $statusColor }} fs-6">{{ ucfirst(str_replace('_', ' ', $labReport->status)) }}</span>
                    </div>

                    <div class="mb-3">
                        <strong>Created:</strong><br>
                        <span class="text-muted">{{ $labReport->created_at->format('d M, Y H:i') }}</span>
                    </div>

                    @if($labReport->updated_at != $labReport->created_at)
                        <div class="mb-3">
                            <strong>Last Updated:</strong><br>
                            <span class="text-muted">{{ $labReport->updated_at->format('d M, Y H:i') }}</span>
                        </div>
                    @endif

                    <div class="d-grid gap-2">
                        @if($labReport->report_file)
                            <a href="{{ route('staff.lab-reports.download', $labReport) }}" class="btn btn-doctor-primary">
                                <i class="fas fa-download me-1"></i>Download Report
                            </a>
                        @endif
                        
                        @if(auth()->user()->role === 'technician')
                            <a href="{{ route('staff.lab-reports.edit', $labReport) }}" class="btn btn-warning">
                                <i class="fas fa-edit me-1"></i>Edit Report
                            </a>
                        @endif
                        
                        <a href="{{ route('staff.lab-reports.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Additional Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-doctor-card-header">
                    <h5 class="doctor-doctor-card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Additional Information
                    </h5>
                </div>
                <div class="doctor-card-body">
                    @if($labReport->technician)
                        <div class="mb-2">
                            <strong>Technician:</strong><br>
                            <span class="text-muted">{{ $labReport->technician->name }}</span>
                        </div>
                    @endif
                    
                    @if($labReport->createdBy)
                        <div class="mb-2">
                            <strong>Created By:</strong><br>
                            <span class="text-muted">{{ $labReport->createdBy->name }}</span>
                        </div>
                    @endif
                    
                    @if($labReport->updatedBy && $labReport->updatedBy->id !== $labReport->createdBy?->id)
                        <div class="mb-2">
                            <strong>Last Updated By:</strong><br>
                            <span class="text-muted">{{ $labReport->updatedBy->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

