@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Medical Record Details')
@section('page-title', 'Medical Record Details')
@section('page-subtitle', $medicalRecord->patient ? 'Complete medical record for ' . $medicalRecord->patient->first_name . ' ' . $medicalRecord->patient->last_name : 'Medical Record #' . $medicalRecord->id)

@section('content')
<div class="fade-in-up">

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

    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show" role="alert">
            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Patient Alert Bar -->
    @if($medicalRecord->patient)
        @include('components.patient-alert-bar', ['patient' => $medicalRecord->patient])
    @endif

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Record Overview -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-file-medical me-2"></i>Record Overview</h5>
                        <div class="d-flex gap-2">
                            @php
                                $typeColors = [
                                    'consultation' => 'primary',
                                    'follow_up' => 'info',
                                    'emergency' => 'danger',
                                    'routine_checkup' => 'success',
                                    'procedure' => 'warning'
                                ];
                                $typeColor = $typeColors[$medicalRecord->record_type] ?? 'secondary';
                            @endphp
                            <span class="badge bg-{{ $typeColor }} fs-6">{{ ucfirst(str_replace('_', ' ', $medicalRecord->record_type)) }}</span>
                            <span class="badge bg-light text-dark fs-6">{{ formatDate($medicalRecord->record_date ?? $medicalRecord->created_at) }}</span>
                        </div>
                    </div>
                </div>
                <div class="doctor-card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-hashtag me-1"></i>Record ID</label>
                            <div class="fw-bold text-primary">#{{ str_pad($medicalRecord->id, 4, '0', STR_PAD_LEFT) }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-calendar me-1"></i>Record Date</label>
                            <div class="fw-bold">{{ formatDate($medicalRecord->record_date ?? $medicalRecord->created_at) }}</div>
                        </div>
                    </div>
                    
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-clock me-1"></i>Created</label>
                            <div class="fw-bold">{{ formatDateTime($medicalRecord->created_at) }}</div>
                            <small class="text-muted">{{ $medicalRecord->created_at->diffForHumans() }}</small>
                        </div>
                        @if($medicalRecord->updated_at != $medicalRecord->created_at)
                        <div class="col-md-6">
                            <label class="form-label text-muted"><i class="fas fa-edit me-1"></i>Last Updated</label>
                            <div class="fw-bold">{{ formatDateTime($medicalRecord->updated_at) }}</div>
                            <small class="text-muted">{{ $medicalRecord->updated_at->diffForHumans() }}</small>
                        </div>
                        @endif
                    </div>

                    @if($medicalRecord->appointment)
                    <div class="row mb-3">
                        <div class="col-12">
                            <label class="form-label text-muted"><i class="fas fa-calendar-check me-1"></i>Related Appointment</label>
                            <div class="fw-bold">
                                <a href="{{ route('staff.appointments.show', $medicalRecord->appointment) }}" class="text-decoration-none">
                                    #{{ $medicalRecord->appointment->appointment_number }} - {{ formatDate($medicalRecord->appointment->appointment_date) }} at {{ $medicalRecord->appointment->appointment_time }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Patient Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-user me-2"></i>Patient Information</h5>
                </div>
                <div class="doctor-card-body">
                    @if($medicalRecord->patient)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-id-card me-1"></i>Full Name</label>
                                <div class="fw-bold">
                                    <a href="{{ route('staff.patients.show', $medicalRecord->patient) }}" class="text-decoration-none">
                                        {{ $medicalRecord->patient->first_name }} {{ $medicalRecord->patient->last_name }}
                                    </a>
                                </div>
                            </div>
                            @if($medicalRecord->patient->patient_id)
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-barcode me-1"></i>Patient ID</label>
                                <div class="fw-bold">{{ $medicalRecord->patient->patient_id }}</div>
                            </div>
                            @endif
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-envelope me-1"></i>Email</label>
                                <div class="fw-bold">
                                    @if($medicalRecord->patient->email)
                                        <a href="mailto:{{ $medicalRecord->patient->email }}" class="text-decoration-none">
                                            {{ $medicalRecord->patient->email }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-phone me-1"></i>Phone</label>
                                <div class="fw-bold">
                                    @if($medicalRecord->patient->phone)
                                        <a href="tel:{{ $medicalRecord->patient->phone }}" class="text-decoration-none">
                                            {{ $medicalRecord->patient->phone }}
                                        </a>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        @if($medicalRecord->patient->date_of_birth)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-birthday-cake me-1"></i>Date of Birth</label>
                                <div class="fw-bold">{{ formatDate($medicalRecord->patient->date_of_birth) }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-calculator me-1"></i>Age</label>
                                <div class="fw-bold">{{ \Carbon\Carbon::parse($medicalRecord->patient->date_of_birth)->age }} years</div>
                            </div>
                        </div>
                        @endif

                        @if($medicalRecord->patient->blood_group || $medicalRecord->patient->gender)
                        <div class="row mb-3">
                            @if($medicalRecord->patient->gender)
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-venus-mars me-1"></i>Gender</label>
                                <div class="fw-bold">
                                    <span class="badge bg-secondary">{{ ucfirst($medicalRecord->patient->gender) }}</span>
                                </div>
                            </div>
                            @endif
                            @if($medicalRecord->patient->blood_group)
                            <div class="col-md-6">
                                <label class="form-label text-muted"><i class="fas fa-tint me-1"></i>Blood Group</label>
                                <div class="fw-bold">
                                    <span class="badge bg-danger">{{ $medicalRecord->patient->blood_group }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endif
                    @else
                        <div class="alert alert-warning" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Patient Record Deleted:</strong> The patient associated with this medical record has been removed from the system.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Medical Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-notes-medical me-2"></i>Medical Information</h5>
                </div>
                <div class="doctor-card-body">
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

                    @if($medicalRecord->notes)
                    <div class="row mb-4">
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary"><i class="fas fa-sticky-note me-1"></i>Additional Notes</label>
                            <div class="border rounded p-3 bg-light">
                                {!! nl2br(e($medicalRecord->notes)) !!}
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- File Attachments -->
            @if($medicalRecord->attachments && $medicalRecord->attachments->count() > 0)
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-paperclip me-2"></i>Documents or Attachments</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Description</th>
                                    <th>File Name</th>
                                    <th>Type</th>
                                    <th>Uploaded By</th>
                                    <th>Date</th>
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
                                        @if($attachment->virus_scan_status === 'pending')
                                            <br><small class="text-warning"><i class="fas fa-clock me-1"></i>Scan pending</small>
                                        @elseif($attachment->virus_scan_status === 'clean')
                                            <br><small class="text-success"><i class="fas fa-shield-alt me-1"></i>Scanned clean</small>
                                        @elseif($attachment->virus_scan_status === 'infected')
                                            <br><small class="text-danger"><i class="fas fa-exclamation-triangle me-1"></i>Infected - Access restricted</small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-info">{{ ucfirst($attachment->file_category) }}</span>
                                    </td>
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
                                        @if($attachment->canAccess(auth()->user()) && $attachment->virus_scan_status !== 'infected')
                                            @if($attachment->isViewable())
                                                <a href="{{ route('staff.medical-record-attachments.view', $attachment) }}" 
                                                   target="_blank"
                                                   class="btn btn-sm btn-outline-info me-1" 
                                                   title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endif
                                        @else
                                            <span class="text-muted">Access restricted</span>
                                        @endif
                                        @if(auth()->user()->is_admin || $attachment->uploaded_by === auth()->id())
                                            <form action="{{ route('staff.medical-record-attachments.destroy', $attachment) }}" 
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
            @if($medicalRecord->vital_signs && !empty($medicalRecord->vital_signs))
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-heartbeat me-2"></i>Vital Signs</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="row">
                        @php 
                            // Handle vital_signs - it's already cast as array in the model
                            $vitals = $medicalRecord->vital_signs ?? [];
                        @endphp
                        
                        @if(isset($vitals['temperature']) && $vitals['temperature'])
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <i class="fas fa-thermometer-half text-danger fa-2x mb-2"></i>
                                <div class="fw-bold">{{ $vitals['temperature'] }}{{ !str_contains($vitals['temperature'], '°') ? '°C' : '' }}</div>
                                <small class="text-muted">Temperature</small>
                            </div>
                        </div>
                        @endif

                        @if(isset($vitals['blood_pressure']) && $vitals['blood_pressure'])
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <i class="fas fa-heart text-danger fa-2x mb-2"></i>
                                <div class="fw-bold">{{ $vitals['blood_pressure'] }}</div>
                                <small class="text-muted">Blood Pressure</small>
                            </div>
                        </div>
                        @endif

                        @if(isset($vitals['pulse']) && $vitals['pulse'])
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <i class="fas fa-heartbeat text-primary fa-2x mb-2"></i>
                                <div class="fw-bold">{{ $vitals['pulse'] }}{{ !str_contains($vitals['pulse'], 'bpm') ? ' bpm' : '' }}</div>
                                <small class="text-muted">Pulse Rate</small>
                            </div>
                        </div>
                        @endif

                        @if(isset($vitals['respiratory_rate']) && $vitals['respiratory_rate'])
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <i class="fas fa-lungs text-info fa-2x mb-2"></i>
                                <div class="fw-bold">{{ $vitals['respiratory_rate'] }}{{ !str_contains($vitals['respiratory_rate'], 'breaths') && !str_contains($vitals['respiratory_rate'], '/min') ? ' breaths/min' : '' }}</div>
                                <small class="text-muted">Respiratory Rate</small>
                            </div>
                        </div>
                        @endif

                        @if(isset($vitals['oxygen_saturation']) && $vitals['oxygen_saturation'])
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <i class="fas fa-wind text-success fa-2x mb-2"></i>
                                <div class="fw-bold">{{ $vitals['oxygen_saturation'] }}{{ !str_contains($vitals['oxygen_saturation'], '%') ? '%' : '' }}</div>
                                <small class="text-muted">Oxygen Saturation</small>
                            </div>
                        </div>
                        @endif

                        @if(isset($vitals['weight']) && $vitals['weight'])
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <i class="fas fa-weight text-secondary fa-2x mb-2"></i>
                                <div class="fw-bold">{{ $vitals['weight'] }}{{ !str_contains($vitals['weight'], 'kg') && !str_contains($vitals['weight'], 'lbs') ? ' kg' : '' }}</div>
                                <small class="text-muted">Weight</small>
                            </div>
                        </div>
                        @endif

                        @if(isset($vitals['height']) && $vitals['height'])
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <i class="fas fa-ruler-vertical text-secondary fa-2x mb-2"></i>
                                <div class="fw-bold">{{ $vitals['height'] }}{{ !str_contains($vitals['height'], 'cm') && !str_contains($vitals['height'], 'ft') && !str_contains($vitals['height'], 'in') ? ' cm' : '' }}</div>
                                <small class="text-muted">Height</small>
                            </div>
                        </div>
                        @endif

                        @if(isset($vitals['bmi']) && $vitals['bmi'])
                        <div class="col-md-4 mb-3">
                            <div class="text-center p-3 border rounded bg-light">
                                <i class="fas fa-calculator text-warning fa-2x mb-2"></i>
                                <div class="fw-bold">{{ number_format($vitals['bmi'], 1) }}</div>
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

            <!-- Action Buttons -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h6 class="doctor-doctor-card-title mb-0">
                        <i class="fas fa-cogs me-2"></i>Quick Actions
                    </h6>
                </div>
                <div class="doctor-doctor-card-body">
                    <div class="d-grid gap-2">
                        @if(auth()->user()->role === 'doctor')
                            <h6 class="text-uppercase fw-bold text-muted small mb-2">Workflow</h6>
                            
                            @if(!$medicalRecord->prescriptions || $medicalRecord->prescriptions->count() === 0)
                                <a href="{{ route('staff.prescriptions.create', ['medical_record_id' => $medicalRecord->id, 'patient_id' => $medicalRecord->patient_id]) }}" 
                                   class="btn btn-success w-100 mb-2">
                                    <i class="fas fa-prescription-bottle-alt me-2"></i>Write Prescription
                                </a>
                            @else
                                <a href="{{ route('staff.prescriptions.index', ['medical_record_id' => $medicalRecord->id]) }}" 
                                   class="btn btn-outline-success w-100 mb-2">
                                    <i class="fas fa-prescription-bottle me-2"></i>View Prescriptions ({{ $medicalRecord->prescriptions->count() }})
                                </a>
                            @endif
                            
                            <a href="{{ route('staff.lab-reports.create', ['patient_id' => $medicalRecord->patient_id, 'medical_record_id' => $medicalRecord->id]) }}" 
                               class="btn btn-outline-info w-100 mb-2">
                                <i class="fas fa-vial me-2"></i>Order Lab Test
                            </a>
                            
                            @if($medicalRecord->patient && in_array(auth()->user()->role, ['doctor', 'nurse']))
                                <a href="{{ route('staff.medical-records.create', ['patient_id' => $medicalRecord->patient_id, 'source_record_id' => $medicalRecord->id]) }}" 
                                   class="btn btn-doctor-primary w-100 mb-2">
                                    <i class="fas fa-copy me-2"></i>Copy Forward to New Record
                                </a>
                            @endif
                            
                            <div class="dropdown-divider my-2"></div>
                        @endif
                        
                        <h6 class="text-uppercase fw-bold text-muted small mb-2">Navigation</h6>
                        
                        @if($medicalRecord->patient)
                        <a href="{{ route('staff.patients.show', $medicalRecord->patient) }}" class="btn btn-outline-primary w-100 mb-2">
                            <i class="fas fa-user me-2"></i>View Patient Profile
                        </a>
                        @endif
                        
                        @if($medicalRecord->appointment)
                        <a href="{{ route('staff.appointments.show', $medicalRecord->appointment) }}" class="btn btn-outline-success w-100 mb-2">
                            <i class="fas fa-calendar-check me-2"></i>View Appointment
                        </a>
                        @endif
                        
                        <a href="{{ route('staff.medical-records.index') }}" class="btn btn-outline-secondary w-100 mb-2">
                            <i class="fas fa-arrow-left me-2"></i>Back to Records
                        </a>
                    </div>
                </div>
            </div>

            <!-- Doctor Information -->
            <div class="doctor-card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-user-md me-2"></i>Doctor Information
                    </h6>
                </div>
                <div class="doctor-card-body">
                    @if($medicalRecord->doctor)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-sm me-3">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                                    {{ strtoupper(substr($medicalRecord->doctor->name ?? 'Dr', 0, 1)) }}
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold">{{ formatDoctorName($medicalRecord->doctor->name ?? 'Unknown') }}</div>
                                <small class="text-muted">{{ $medicalRecord->doctor->specialization ?? 'GP' }}</small>
                            </div>
                        </div>
                    @else
                        <p class="text-muted mb-0">No doctor assigned to this record</p>
                    @endif
                </div>
            </div>

            <!-- Related Records -->
            @if($medicalRecord->prescriptions && $medicalRecord->prescriptions->count() > 0)
            <div class="doctor-card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-prescription-bottle-alt me-2"></i>Prescriptions ({{ $medicalRecord->prescriptions->count() }})
                    </h6>
                </div>
                <div class="doctor-card-body">
                    @foreach($medicalRecord->prescriptions->take(3) as $prescription)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="fw-bold">{{ $prescription->medication_name }}</div>
                        <small class="text-muted">{{ $prescription->dosage }} - {{ $prescription->frequency }}</small>
                    </div>
                    @endforeach
                    @if($medicalRecord->prescriptions->count() > 3)
                        <small class="text-muted">and {{ $medicalRecord->prescriptions->count() - 3 }} more...</small>
                    @endif
                </div>
            </div>
            @endif

            @if($medicalRecord->labReports && $medicalRecord->labReports->count() > 0)
            <div class="doctor-card mb-4">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-flask me-2"></i>Lab Reports ({{ $medicalRecord->labReports->count() }})
                    </h6>
                </div>
                <div class="doctor-card-body">
                    @foreach($medicalRecord->labReports->take(3) as $labReport)
                    <div class="border-bottom pb-2 mb-2">
                        <div class="fw-bold">{{ $labReport->test_name }}</div>
                        <small class="text-muted">{{ formatDate($labReport->created_at) }}</small>
                    </div>
                    @endforeach
                    @if($medicalRecord->labReports->count() > 3)
                        <small class="text-muted">and {{ $medicalRecord->labReports->count() - 3 }} more...</small>
                    @endif
                </div>
            </div>
            @endif

            <!-- Record Metadata -->
            <div class="card shadow-sm">
                <div class="card-header">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Record Information
                    </h6>
                </div>
                <div class="doctor-card-body">
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Created</div>
                            <div class="fw-bold">{{ $medicalRecord->created_at->format('M d') }}</div>
                            <small class="text-muted">{{ $medicalRecord->created_at->format('Y') }}</small>
                        </div>
                        <div class="col-6">
                            <div class="text-xs font-weight-bold text-uppercase mb-1">Updated</div>
                            <div class="fw-bold">{{ $medicalRecord->updated_at->format('M d') }}</div>
                            <small class="text-muted">{{ $medicalRecord->updated_at->format('Y') }}</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    /* Hide elements that shouldn't be printed */
    .breadcrumb, .alert, .btn, nav, .card-header, .no-print {
        display: none !important;
    }
    
    /* Remove shadows and borders for cleaner print */
    .card {
        border: none !important;
        box-shadow: none !important;
        margin-bottom: 20px !important;
    }
    
    .doctor-card-body {
        padding: 10px !important;
    }
    
    /* Ensure proper page breaks */
    .card {
        page-break-inside: avoid;
    }
    
    /* Make text darker for better printing */
    body, .text-muted {
        color: #000 !important;
    }
    
    /* Header styling for print */
    h1, h2, h3, h4, h5, h6 {
        color: #000 !important;
        margin-bottom: 10px !important;
    }
    
    /* Ensure badges print in grayscale */
    .badge {
        background: #ddd !important;
        color: #000 !important;
        border: 1px solid #999;
    }
    
    /* Background colors for print */
    .bg-light {
        background-color: #f8f9fa !important;
        border: 1px solid #ddd !important;
    }
}
</style>

@push('scripts')
<script>
$(document).ready(function() {
    // Check if print parameter is present in URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('print') === '1') {
        // Trigger print dialog after page loads
        setTimeout(function() {
            window.print();
            // Optionally redirect back to the medical records list after printing
            // window.onafterprint = function() {
            //     window.location.href = '/staff/medical-records';
            // };
        }, 500);
    }
});
</script>
@endpush

@endsection
