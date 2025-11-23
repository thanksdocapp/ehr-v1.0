@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Patient Details')
@section('page-title', 'Patient Details')
@section('page-subtitle', 'Complete patient information and medical history')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

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

    <!-- Patient Alert Bar -->
    @include('components.patient-alert-bar', ['patient' => $patient])

    <div class="row">
        <!-- Main Content -->
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-user me-2"></i>Personal Information</h5>
                </div>
                <div class="doctor-card-body">
                    <!-- Patient Photo -->
                    @if($patient->photo)
                    <div class="row mb-4">
                        <div class="col-12 text-center">
                            <div class="mb-3">
                                <img src="{{ $patient->photo_url }}" alt="Patient Photo" 
                                     class="img-thumbnail rounded-circle" 
                                     style="width: 150px; height: 150px; object-fit: cover;">
                                <div class="mt-2">
                                    <small class="text-muted">Patient Photo</small>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Patient ID</label>
                                <div class="form-control-plaintext">{{ $patient->patient_id ?? 'Not assigned' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Full Name</label>
                                <div class="form-control-plaintext">{{ $patient->full_name }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Date of Birth</label>
                                <div class="form-control-plaintext">
                                    {{ $patient->date_of_birth ? $patient->date_of_birth->format('M d, Y') : 'Not provided' }}
                                    @if($patient->date_of_birth)
                                        <small class="text-muted">({{ $patient->age }} years old)</small>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Gender</label>
                                <div class="form-control-plaintext">
                                    <span class="badge bg-secondary">{{ ucfirst($patient->gender ?? 'Not specified') }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Email Address</label>
                                <div class="form-control-plaintext">
                                    @if($patient->email)
                                        <a href="mailto:{{ $patient->email }}">{{ $patient->email }}</a>
                                    @else
                                        Not provided
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Phone Number</label>
                                <div class="form-control-plaintext">
                                    @if($patient->phone)
                                        <a href="tel:{{ $patient->phone }}">{{ $patient->phone }}</a>
                                    @else
                                        Not provided
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Blood Group</label>
                                <div class="form-control-plaintext">
                                    @if($patient->blood_group)
                                        <span class="badge bg-danger">{{ $patient->blood_group }}</span>
                                    @else
                                        Not specified
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Status</label>
                                <div class="form-control-plaintext">
                                    @if($patient->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Assigned Clinic(s)</label>
                                <div class="form-control-plaintext">
                                    @php
                                        $patientDepartments = [];
                                        
                                        // Get departments from many-to-many relationship (primary method)
                                        if ($patient->relationLoaded('departments') || $patient->departments()->exists()) {
                                            if (!$patient->relationLoaded('departments')) {
                                                $patient->load('departments');
                                            }
                                            foreach ($patient->departments as $dept) {
                                                $isPrimary = $dept->pivot->is_primary ?? false;
                                                $patientDepartments[] = [
                                                    'name' => $dept->name,
                                                    'is_primary' => $isPrimary
                                                ];
                                            }
                                        }
                                        
                                        // Fallback to legacy department_id if no pivot records exist
                                        if (empty($patientDepartments) && $patient->department_id && $patient->department) {
                                            if (!$patient->relationLoaded('department')) {
                                                $patient->load('department');
                                            }
                                            if ($patient->department) {
                                                $patientDepartments[] = [
                                                    'name' => $patient->department->name,
                                                    'is_primary' => true
                                                ];
                                            }
                                        }
                                    @endphp
                                    
                                    @if(!empty($patientDepartments))
                                        <div>
                                            @foreach($patientDepartments as $dept)
                                                <div class="mb-1">
                                                    <i class="fas fa-building me-1 text-primary"></i>
                                                    <strong>{{ $dept['name'] }}</strong>
                                                    @if($dept['is_primary'] && count($patientDepartments) > 1)
                                                        <span class="badge bg-primary ms-1" style="font-size: 0.65rem;">Primary</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <span class="text-muted">Not Assigned</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($patient->address || $patient->city || $patient->state || $patient->country || $patient->postal_code)
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Address</label>
                                <div class="form-control-plaintext">{{ $patient->full_address ?: $patient->address }}</div>
                            </div>
                        </div>
                    </div>
                    @endif
                    
                    @if($patient->city || $patient->state || $patient->country || $patient->postal_code)
                    <div class="row">
                        <div class="col-md-6">
                            @if($patient->city)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">City</label>
                                <div class="form-control-plaintext">{{ $patient->city }}</div>
                            </div>
                            @endif
                            @if($patient->state)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">State</label>
                                <div class="form-control-plaintext">{{ $patient->state }}</div>
                            </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if($patient->country)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Country</label>
                                <div class="form-control-plaintext">{{ $patient->country }}</div>
                            </div>
                            @endif
                            @if($patient->postal_code)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Postal Code</label>
                                <div class="form-control-plaintext">{{ $patient->postal_code }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Insurance Information -->
            @if($patient->insurance_provider || $patient->insurance_number)
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-shield-alt me-2"></i>Insurance Information</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Insurance Provider</label>
                                <div class="form-control-plaintext">{{ $patient->insurance_provider ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Insurance Number</label>
                                <div class="form-control-plaintext">{{ $patient->insurance_number ?? 'Not provided' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <!-- Medical Information -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-heartbeat me-2"></i>Medical Information</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Allergies</label>
                        <div class="mt-2">
                            @if($patient->allergies && count($patient->allergies) > 0)
                                @foreach($patient->allergies as $allergy)
                                    <span class="badge bg-warning text-dark me-1 mb-1">{{ $allergy }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">No allergies recorded</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Medical Conditions</label>
                        <div class="mt-2">
                            @if($patient->medical_conditions && count($patient->medical_conditions) > 0)
                                @foreach($patient->medical_conditions as $condition)
                                    <span class="badge bg-info text-dark me-1 mb-1">{{ $condition }}</span>
                                @endforeach
                            @else
                                <span class="text-muted">No medical conditions recorded</span>
                            @endif
                        </div>
                    </div>
                    
                    @if($patient->notes)
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Additional Notes</label>
                        <div class="border rounded p-3 bg-light mt-2">
                            {!! nl2br(e($patient->notes)) !!}
                        </div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Uploaded Documents -->
            @if($patient->patient_id_document_path || $patient->guardian_id_document_path)
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-file-upload me-2"></i>Uploaded Documents</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="row">
                        @if($patient->patient_id_document_path)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Patient ID Document</label>
                                <div class="mt-2">
                                    @php
                                        $documentPath = $patient->patient_id_document_path;
                                        $filename = basename($documentPath);
                                    @endphp
                                    <a href="{{ route('staff.patients.download-document', ['patient' => $patient->id, 'type' => 'patient_id']) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       target="_blank">
                                        <i class="fas fa-file-pdf me-1"></i>View/Download Document
                                    </a>
                                    <small class="text-muted d-block mt-1">{{ $filename }}</small>
                                </div>
                            </div>
                        </div>
                        @endif
                        
                        @if($patient->guardian_id_document_path)
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Guardian ID Document</label>
                                <div class="mt-2">
                                    @php
                                        $documentPath = $patient->guardian_id_document_path;
                                        $filename = basename($documentPath);
                                    @endphp
                                    <a href="{{ route('staff.patients.download-document', ['patient' => $patient->id, 'type' => 'guardian_id']) }}" 
                                       class="btn btn-sm btn-outline-primary" 
                                       target="_blank">
                                        <i class="fas fa-file-pdf me-1"></i>View/Download Document
                                    </a>
                                    <small class="text-muted d-block mt-1">{{ $filename }}</small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- GP Information -->
            @if($patient->consent_share_with_gp || $patient->gp_name || $patient->gp_email)
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-user-md me-2"></i>GP (General Practitioner) Information</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Consent to Share with GP</label>
                                <div class="form-control-plaintext">
                                    @if($patient->consent_share_with_gp)
                                        <span class="badge bg-success">Yes</span>
                                    @else
                                        <span class="badge bg-secondary">No</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    @if($patient->consent_share_with_gp)
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">GP Name</label>
                                <div class="form-control-plaintext">{{ $patient->gp_name ?? 'Not provided' }}</div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">GP Email</label>
                                <div class="form-control-plaintext">
                                    @if($patient->gp_email)
                                        <a href="mailto:{{ $patient->gp_email }}">{{ $patient->gp_email }}</a>
                                    @else
                                        <span class="text-muted">Not provided</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">GP Phone</label>
                                <div class="form-control-plaintext">
                                    @if($patient->gp_phone)
                                        <a href="tel:{{ $patient->gp_phone }}">{{ $patient->gp_phone }}</a>
                                    @else
                                        <span class="text-muted">Not provided</span>
                                    @endif
                                </div>
                            </div>
                            @if($patient->gp_address)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">GP Address</label>
                                <div class="form-control-plaintext">{{ $patient->gp_address }}</div>
                            </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            <!-- Emergency Contact -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-user-shield me-2"></i>Emergency Contact</h5>
                </div>
                <div class="doctor-card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Contact Name</label>
                                <div class="form-control-plaintext">{{ $patient->emergency_contact ?? 'Not provided' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Contact Phone</label>
                                <div class="form-control-plaintext">
                                    @if($patient->emergency_phone)
                                        <a href="tel:{{ $patient->emergency_phone }}">{{ $patient->emergency_phone }}</a>
                                    @else
                                        Not provided
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Medical Records -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-file-medical me-2"></i>Medical Records
                        @if($patient->medicalRecords && $patient->medicalRecords->count() > 0)
                            <span class="badge bg-light text-dark ms-2">{{ $patient->medicalRecords->count() }}</span>
                        @endif
                    </h5>
                </div>
                    <div class="doctor-card-body">
                    @if($patient->medicalRecords && $patient->medicalRecords->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Record Date</th>
                                        <th>Doctor</th>
                                        <th>Presenting Complaint</th>
                                        <th>Type</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patient->medicalRecords as $record)
                                    <tr>
                                        <td>
                                            <div class="fw-bold">{{ formatDate($record->record_date ?? $record->created_at) }}</div>
                                            <small class="text-muted">{{ ($record->record_date ?? $record->created_at)->format('h:i A') }}</small>
                                        </td>
                                        <td>
                                            @if($record->doctor)
                                                <div class="fw-bold">{{ formatDoctorName($record->doctor->name ?? 'Unknown') }}</div>
                                                <small class="text-muted">{{ $record->doctor->specialization ?? 'GP' }}</small>
                                            @else
                                                <span class="text-muted">Not assigned</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 250px;" title="{{ $record->presenting_complaint ?? $record->chief_complaint ?? 'N/A' }}">
                                                {{ Str::limit($record->presenting_complaint ?? $record->chief_complaint ?? 'N/A', 50) }}
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'consultation' => 'primary',
                                                    'follow_up' => 'info',
                                                    'emergency' => 'danger',
                                                    'routine_checkup' => 'success',
                                                    'procedure' => 'warning'
                                                ];
                                                $typeColor = $typeColors[$record->record_type] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $typeColor }}">
                                                {{ ucfirst(str_replace('_', ' ', $record->record_type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('staff.medical-records.show', $record) }}" 
                                               class="btn btn-sm btn-outline-primary" 
                                               title="View Record">
                                                <i class="fas fa-eye me-1"></i>View Record
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($patient->medicalRecords->count() > 10)
                            <div class="text-center mt-3">
                                <a href="{{ route('staff.medical-records.index', ['patient_search' => $patient->first_name . ' ' . $patient->last_name]) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-list me-1"></i>View All Medical Records
                                </a>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-file-medical fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No medical records found for this patient.</p>
                            @if(in_array(auth()->user()->role, ['doctor', 'nurse']))
                                <a href="{{ route('staff.medical-records.create', ['patient_id' => $patient->id]) }}" class="btn btn-doctor-primary mt-3">
                                    <i class="fas fa-plus me-1"></i>Create First Medical Record
                                </a>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Appointments -->
            @if($patient->appointments && $patient->appointments->count() > 0)
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-calendar-check me-2"></i>Recent Appointments</h5>
                </div>
                    <div class="doctor-card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th>Type</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($patient->appointments->take(5) as $appointment)
                                <tr>
                                    <td>{{ $appointment->appointment_date ? formatDate($appointment->appointment_date) : 'N/A' }}</td>
                                    <td>{{ $appointment->appointment_time ?? 'N/A' }}</td>
                                    <td>
                                        @if($appointment->doctor)
                                            <div class="fw-bold">{{ formatDoctorName($appointment->doctor->name ?? 'Unknown') }}</div>
                                            <small class="text-muted">{{ $appointment->doctor->specialization ?? 'GP' }}</small>
                                        @else
                                            <span class="text-muted">Not assigned</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $appointment->status === 'completed' ? 'success' : ($appointment->status === 'cancelled' ? 'danger' : 'warning') }}">
                                            {{ ucfirst($appointment->status ?? 'pending') }}
                                        </span>
                                    </td>
                                    <td>{{ ucfirst($appointment->appointment_type ?? 'consultation') }}</td>
                                    <td>
                                        <a href="{{ route('staff.appointments.show', $appointment) }}" 
                                           class="btn btn-sm btn-outline-primary" 
                                           title="View Appointment">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($patient->appointments->count() > 5)
                        <div class="text-center mt-3">
                            <small class="text-muted">Showing 5 most recent appointments</small>
                        </div>
                    @endif
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Quick Actions -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-bolt me-2"></i>Quick Actions</h5>
                </div>
                <div class="doctor-card-body">
                    @if(auth()->user()->role === 'doctor')
                        <h6 class="text-uppercase fw-bold text-muted small mb-2">Workflow</h6>
                        <div class="d-grid gap-2 mb-3">
                            <a href="{{ route('staff.appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-doctor-primary w-100">
                                <i class="fas fa-calendar-plus me-2"></i>Schedule Appointment
                            </a>
                            
                            @if(in_array(auth()->user()->role, ['doctor', 'nurse']))
                                <a href="{{ route('staff.medical-records.create', ['patient_id' => $patient->id]) }}" class="btn btn-success w-100">
                                    <i class="fas fa-file-medical me-2"></i>New Medical Record
                                </a>
                            @endif
                            
                            @if(auth()->user()->role === 'doctor')
                                <a href="{{ route('staff.prescriptions.create', ['patient_id' => $patient->id]) }}" class="btn btn-info w-100">
                                    <i class="fas fa-prescription-bottle-alt me-2"></i>Write Prescription
                                </a>
                                
                                <a href="{{ route('staff.lab-reports.create', ['patient_id' => $patient->id]) }}" class="btn btn-outline-info w-100">
                                    <i class="fas fa-vial me-2"></i>Order Lab Test
                                </a>
                            @endif
                        </div>
                        
                        <div class="dropdown-divider my-2"></div>
                    @endif
                    
                    @can('viewAny', [\App\Models\PatientAlert::class, $patient])
                    <h6 class="text-uppercase fw-bold text-muted small mb-2">Alerts</h6>
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('staff.patients.alerts.index', $patient) }}" class="btn btn-outline-danger w-100">
                            <i class="fas fa-exclamation-triangle me-2"></i>View Alerts
                            @php
                                $activeAlertsCount = $patient->activeAlerts()->count();
                            @endphp
                            @if($activeAlertsCount > 0)
                                <span class="badge bg-danger ms-2">{{ $activeAlertsCount }}</span>
                            @endif
                        </a>
                        @can('create', [\App\Models\PatientAlert::class, $patient])
                        <a href="{{ route('staff.patients.alerts.create', $patient) }}" class="btn btn-outline-warning w-100">
                            <i class="fas fa-plus me-2"></i>Add Alert
                        </a>
                        @endcan
                    </div>
                    <div class="dropdown-divider my-2"></div>
                    @endcan
                    
                    @can('viewAny', [\App\Models\PatientDocument::class, $patient])
                    <h6 class="text-uppercase fw-bold text-muted small mb-2">Letters & Forms</h6>
                    <div class="d-grid gap-2 mb-3">
                        <a href="{{ route('staff.patients.documents.index', $patient) }}" class="btn btn-outline-primary w-100">
                            <i class="fas fa-file-alt me-2"></i>View Documents
                        </a>
                        @can('create', [\App\Models\PatientDocument::class, $patient])
                        <a href="{{ route('staff.patients.documents.create', $patient) }}" class="btn btn-outline-info w-100">
                            <i class="fas fa-plus me-2"></i>Create Document
                        </a>
                        @endcan
                    </div>
                    <div class="dropdown-divider my-2"></div>
                    @endcan
                    
                    <h6 class="text-uppercase fw-bold text-muted small mb-2">Management</h6>
                    <div class="d-grid gap-2">
                        <a href="{{ route('staff.patients.edit', $patient) }}" class="btn btn-warning w-100">
                            <i class="fas fa-edit me-2"></i>Edit Patient
                        </a>
                        <a href="{{ route('staff.patients.index') }}" class="btn btn-outline-secondary w-100">
                            <i class="fas fa-arrow-left me-2"></i>Back to List
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Stats -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-chart-bar me-2"></i>Quick Stats</h5>
                </div>
                    <div class="doctor-card-body">
                    <div class="row text-center">
                        <div class="col-6 mb-3">
                            <div class="h4 mb-1 text-primary">{{ $patient->medicalRecords ? $patient->medicalRecords->count() : 0 }}</div>
                            <small class="text-muted">Medical Records</small>
                        </div>
                        <div class="col-6 mb-3">
                            <div class="h4 mb-1 text-success">{{ $patient->appointments ? $patient->appointments->count() : 0 }}</div>
                            <small class="text-muted">Total Appointments</small>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="h4 mb-1 text-warning">{{ $patient->appointments ? $patient->appointments->where('status', 'pending')->count() : 0 }}</div>
                            <small class="text-muted">Pending</small>
                        </div>
                        <div class="col-6">
                            <div class="h4 mb-1 text-success">{{ $patient->appointments ? $patient->appointments->where('status', 'completed')->count() : 0 }}</div>
                            <small class="text-muted">Completed</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Information -->
            <div class="card shadow-sm">
                <div class="card-header bg-light py-3">
                    <h6 class="card-title mb-0 fw-semibold">Patient Information</h6>
                </div>
                    <div class="doctor-card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Registered</small>
                        <strong>{{ $patient->created_at ? $patient->created_at->format('M d, Y') : 'Unknown' }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Last Updated</small>
                        <strong>{{ $patient->updated_at ? $patient->updated_at->diffForHumans() : 'Never' }}</strong>
                    </div>
                    @if($patient->notes)
                    <div class="mb-0">
                        <small class="text-muted d-block">Notes</small>
                        <p class="small mb-0">{{ $patient->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 30000);
});
</script>
@endpush
