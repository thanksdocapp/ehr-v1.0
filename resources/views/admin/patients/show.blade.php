@extends('admin.layouts.app')

@section('title', 'Patient Details')

@php
    use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="container-fluid">
    <!-- Patient Alert Bar -->
    @include('components.patient-alert-bar', ['patient' => $patient])
    
    <div class="row">
        <div class="col-12">
            <div class="doctor-card">
                <div class="doctor-card-header d-flex justify-content-between align-items-center">
                    <h5 class="doctor-card-title mb-0">Patient Details</h5>
                    <div class="d-flex gap-2">
                        <span class="badge {{ $patient->is_active ? 'bg-success' : 'bg-danger' }}">
                            {{ $patient->is_active ? 'Active' : 'Inactive' }}
                        </span>
                        <a href="{{ route('admin.patients.edit', $patient) }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    </div>
                </div>
                
                <div class="doctor-card-body">
                    <div class="row">
                        <div class="col-md-8">
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

                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="text-primary border-bottom pb-2">Personal Information</h4>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Patient ID:</td>
                                            <td>{{ $patient->patient_id }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Full Name:</td>
                                            <td>{{ $patient->first_name }} {{ $patient->last_name }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Date of Birth:</td>
                                            <td>
                                                @if($patient->date_of_birth)
                                                    {{ formatDate($patient->date_of_birth) }}
                                                    <small class="text-muted">({{ \Carbon\Carbon::parse($patient->date_of_birth)->age }} years old)</small>
                                                @else
                                                    <span class="text-muted">Not provided</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Gender:</td>
                                            <td>{{ $patient->gender ? ucfirst($patient->gender) : 'Not specified' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Blood Group:</td>
                                            <td>{{ $patient->blood_group ?: 'Not provided' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Registration Date:</td>
                                            <td>{{ formatDateTime($patient->created_at) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Last Updated:</td>
                                            <td>{{ formatDateTime($patient->updated_at) }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Status:</td>
                                            <td>
                                                <span class="badge {{ $patient->is_active ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $patient->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="text-primary border-bottom pb-2">Assignment Information</h4>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Assigned Doctor:</td>
                                            <td>
                                                @if($patient->assignedDoctor)
                                                    <div>
                                                        <i class="fas fa-user-md me-1 text-primary"></i>
                                                        <strong>Dr. {{ $patient->assignedDoctor->first_name }} {{ $patient->assignedDoctor->last_name }}</strong>
                                                        @if($patient->assignedDoctor->employee_id)
                                                            <small class="text-muted">({{ $patient->assignedDoctor->employee_id }})</small>
                                                        @endif
                                                        @if($patient->assignedDoctor->specialization)
                                                            <div class="text-muted mt-1">
                                                                <small>Specialisation: {{ $patient->assignedDoctor->specialization }}</small>
                                                            </div>
                                                        @endif
                                                        @php
                                                            $doctor = $patient->assignedDoctor;
                                                            $departments = [];
                                                            
                                                            if ($doctor && !$doctor->relationLoaded('departments')) {
                                                                $doctor->load('departments');
                                                            }
                                                            
                                                            $doctorDepartments = $doctor->departments ?? collect();
                                                            if ($doctorDepartments && method_exists($doctorDepartments, 'count') && $doctorDepartments->count() > 0) {
                                                                foreach ($doctorDepartments as $dept) {
                                                                    if ($dept && isset($dept->name)) {
                                                                        $departments[] = $dept->name;
                                                                    }
                                                                }
                                                            }
                                                            
                                                            if (empty($departments) && $doctor && $doctor->department) {
                                                                if (!$doctor->relationLoaded('department')) {
                                                                    $doctor->load('department');
                                                                }
                                                                if ($doctor->department && isset($doctor->department->name)) {
                                                                    $departments[] = $doctor->department->name;
                                                                }
                                                            }
                                                        @endphp
                                                        @if(!empty($departments))
                                                            <div class="text-muted mt-1">
                                                                <small><i class="fas fa-building me-1"></i>Doctor's Departments: {{ implode(', ', $departments) }}</small>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">Not Assigned</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Assigned Clinic(s):</td>
                                            <td>
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
                                                                    <span class="badge bg-primary ms-1">Primary</span>
                                                                @endif
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @else
                                                    <span class="text-muted">Not Assigned</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Created By:</td>
                                            <td>
                                                @if($patient->createdByDoctor)
                                                    <div>
                                                        <i class="fas fa-user-md me-1 text-secondary"></i>
                                                        <strong>Dr. {{ $patient->createdByDoctor->first_name }} {{ $patient->createdByDoctor->last_name }}</strong>
                                                        @if($patient->createdByDoctor->employee_id)
                                                            <small class="text-muted">({{ $patient->createdByDoctor->employee_id }})</small>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-muted">System</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="text-primary border-bottom pb-2">Contact Information</h4>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Email:</td>
                                            <td>
                                                @if($patient->email)
                                                    <a href="mailto:{{ $patient->email }}">{{ $patient->email }}</a>
                                                @else
                                                    <span class="text-muted">Not provided</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Phone:</td>
                                            <td>
                                                @if($patient->phone)
                                                    <a href="tel:{{ $patient->phone }}">{{ $patient->phone }}</a>
                                                @else
                                                    <span class="text-muted">Not provided</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Address:</td>
                                            <td>{{ $patient->address ?: 'Not provided' }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">City:</td>
                                            <td>{{ $patient->city ?: 'Not provided' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">State:</td>
                                            <td>{{ $patient->state ?: 'Not provided' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Postal Code:</td>
                                            <td>{{ $patient->postal_code ?: 'Not provided' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Country:</td>
                                            <td>{{ $patient->country ?: 'Not provided' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="text-primary border-bottom pb-2">Emergency Contact</h4>
                                </div>
                                <div class="col-12">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Emergency Contact Name:</td>
                                            <td>{{ $patient->emergency_contact ?: 'Not provided' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Emergency Contact Phone:</td>
                                            <td>
                                                @if($patient->emergency_phone)
                                                    <a href="tel:{{ $patient->emergency_phone }}">{{ $patient->emergency_phone }}</a>
                                                @else
                                                    <span class="text-muted">Not provided</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="text-primary border-bottom pb-2">Insurance Information</h4>
                                </div>
                                <div class="col-12">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Insurance Provider:</td>
                                            <td>{{ $patient->insurance_provider ?: 'Not provided' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Insurance Number:</td>
                                            <td>{{ $patient->insurance_number ?: 'Not provided' }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="text-primary border-bottom pb-2">Medical Information</h4>
                                </div>
                                <div class="col-12">
                                    <div class="mb-3">
                                        <label class="fw-bold">Allergies:</label>
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
                                        <label class="fw-bold">Medical Conditions:</label>
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
                                    
                                    <div class="mb-3">
                                        <label class="fw-bold">Medical History/Notes:</label>
                                        <div class="mt-2">
                                            @if($patient->notes)
                                                <div class="border rounded p-3 bg-light">
                                                    {!! nl2br(e($patient->notes)) !!}
                                                </div>
                                            @else
                                                <span class="text-muted">No additional notes recorded</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Uploaded Documents Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="text-primary border-bottom pb-2">Uploaded Documents</h4>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="fw-bold">Patient ID Document:</label>
                                        <div class="mt-2">
                                            @if($patient->patient_id_document_path)
                                                @php
                                                    $documentPath = $patient->patient_id_document_path;
                                                    // Documents are stored in private storage, so we need to use a route to access them
                                                    // For now, we'll show the filename and create a download link
                                                    $filename = basename($documentPath);
                                                @endphp
                                                <a href="{{ route('admin.patients.download-document', ['patient' => $patient->id, 'type' => 'patient_id']) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   target="_blank">
                                                    <i class="fas fa-eye me-1"></i>View Document
                                                </a>
                                                <small class="text-muted d-block mt-1">{{ $filename }}</small>
                                            @else
                                                <span class="text-muted">No document uploaded</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="fw-bold">Guardian ID Document:</label>
                                        <div class="mt-2">
                                            @if($patient->guardian_id_document_path)
                                                @php
                                                    $documentPath = $patient->guardian_id_document_path;
                                                    // Documents are stored in private storage, so we need to use a route to access them
                                                    $filename = basename($documentPath);
                                                @endphp
                                                <a href="{{ route('admin.patients.download-document', ['patient' => $patient->id, 'type' => 'guardian_id']) }}" 
                                                   class="btn btn-sm btn-outline-primary" 
                                                   target="_blank">
                                                    <i class="fas fa-eye me-1"></i>View Document
                                                </a>
                                                <small class="text-muted d-block mt-1">{{ $filename }}</small>
                                            @else
                                                <span class="text-muted">No document uploaded</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- GP Information Section -->
                            @if($patient->consent_share_with_gp || $patient->gp_name || $patient->gp_email)
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="text-primary border-bottom pb-2">GP (General Practitioner) Information</h4>
                                </div>
                                <div class="col-12">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td class="fw-bold">Consent to Share with GP:</td>
                                            <td>
                                                @if($patient->consent_share_with_gp)
                                                    <span class="badge bg-success">Yes</span>
                                                @else
                                                    <span class="badge bg-secondary">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($patient->consent_share_with_gp)
                                        <tr>
                                            <td class="fw-bold">GP Name:</td>
                                            <td>{{ $patient->gp_name ?: 'Not provided' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">GP Email:</td>
                                            <td>
                                                @if($patient->gp_email)
                                                    <a href="mailto:{{ $patient->gp_email }}">{{ $patient->gp_email }}</a>
                                                @else
                                                    <span class="text-muted">Not provided</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">GP Phone:</td>
                                            <td>
                                                @if($patient->gp_phone)
                                                    <a href="tel:{{ $patient->gp_phone }}">{{ $patient->gp_phone }}</a>
                                                @else
                                                    <span class="text-muted">Not provided</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">GP Address:</td>
                                            <td>{{ $patient->gp_address ?: 'Not provided' }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                            @endif

                            <!-- Appointments Section -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <h4 class="text-primary border-bottom pb-2">Recent Appointments</h4>
                                </div>
                                <div class="col-12">
                                    @if($patient->appointments && $patient->appointments->count() > 0)
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Date</th>
                                                        <th>Time</th>
                                                        <th>Doctor</th>
                                                        <th>Status</th>
                                                        <th>Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($patient->appointments->take(5) as $appointment)
                                                        <tr>
                                                            <td>{{ formatDate($appointment->appointment_date) }}</td>
                                                            <td>{{ $appointment->appointment_time->format('g:i A') }}</td>
                                                            <td>
                                                                @if($appointment->doctor)
                                                                    Dr. {{ $appointment->doctor->first_name }} {{ $appointment->doctor->last_name }}
                                                                @else
                                                                    <span class="text-muted">Not assigned</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                <span class="badge bg-{{ $appointment->status == 'completed' ? 'success' : ($appointment->status == 'cancelled' ? 'danger' : 'warning') }}">
                                                                    {{ ucfirst($appointment->status) }}
                                                                </span>
                                                            </td>
                                                            <td>
                                                                <a href="{{ contextRoute('appointments.show', $appointment->id) }}" class="btn btn-sm btn-outline-info">
                                                                    <i class="fas fa-eye"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        @if($patient->appointments->count() > 5)
                                            <div class="text-center">
                                                <a href="{{ contextRoute('appointments.index', ['patient_id' => $patient->id]) }}" class="btn btn-outline-primary">
                                                    View All Appointments ({{ $patient->appointments->count() }})
                                                </a>
                                            </div>
                                        @endif
                                    @else
                                        <div class="text-center text-muted py-4">
                                            <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                            <p>No appointments found for this patient</p>
                                            <a href="{{ contextRoute('appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-doctor-primary">
                                                <i class="fas fa-plus"></i> Schedule Appointment
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="doctor-card mb-3">
                                <div class="doctor-card-header">
                                    <h6 class="doctor-card-title mb-0">Quick Actions</h6>
                                </div>
                                <div class="doctor-card-body">
                                    <div class="d-grid gap-2">
                                        <a href="{{ route('admin.patients.edit', $patient) }}" class="btn btn-warning">
                                            <i class="fas fa-edit"></i> Edit Patient
                                        </a>
                                        
                                        <a href="{{ contextRoute('appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-primary">
                                            <i class="fas fa-calendar-plus"></i> Schedule Appointment
                                        </a>
                                        
                                        @can('viewAny', [\App\Models\PatientAlert::class, $patient])
                                        <a href="{{ route('admin.patients.alerts.index', $patient) }}" class="btn btn-danger">
                                            <i class="fas fa-exclamation-triangle"></i> View Alerts
                                            @php
                                                $activeAlertsCount = $patient->activeAlerts()->count();
                                            @endphp
                                            @if($activeAlertsCount > 0)
                                                <span class="badge bg-light text-dark ms-2">{{ $activeAlertsCount }}</span>
                                            @endif
                                        </a>
                                        @endcan
                                        
                                        @can('viewAny', [\App\Models\PatientDocument::class, $patient])
                                        <a href="{{ route('admin.patients.documents.index', $patient) }}" class="btn btn-primary">
                                            <i class="fas fa-file-alt"></i> Letters & Forms
                                        </a>
                                        @endcan
                                        
                                        <button type="button" class="btn btn-info" data-bs-toggle="modal" data-bs-target="#contactModal">
                                            <i class="fas fa-phone"></i> Contact Patient
                                        </button>
                                        
                                        <hr>
                                        
                                        <button type="button" class="btn btn-outline-success" onclick="printPatientDetails()">
                                            <i class="fas fa-print"></i> Print Details
                                        </button>
                                        
                                        <button type="button" class="btn btn-outline-info" onclick="exportPatientData()">
                                            <i class="fas fa-download"></i> Export Data
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="doctor-card mb-3">
                                <div class="doctor-card-header">
                                    <h6 class="doctor-card-title mb-0">Patient Statistics</h6>
                                </div>
                                <div class="doctor-card-body">
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h4 class="text-primary">{{ $patient->appointments ? $patient->appointments->count() : 0 }}</h4>
                                                <small class="text-muted">Total Appointments</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-success">{{ $patient->appointments ? $patient->appointments->where('status', 'completed')->count() : 0 }}</h4>
                                            <small class="text-muted">Completed</small>
                                        </div>
                                    </div>
                                    <hr>
                                    <div class="row text-center">
                                        <div class="col-6">
                                            <div class="border-end">
                                                <h4 class="text-warning">{{ $patient->appointments ? $patient->appointments->where('status', 'scheduled')->count() : 0 }}</h4>
                                                <small class="text-muted">Scheduled</small>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <h4 class="text-danger">{{ $patient->appointments ? $patient->appointments->where('status', 'cancelled')->count() : 0 }}</h4>
                                            <small class="text-muted">Cancelled</small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="doctor-card">
                                <div class="doctor-card-header">
                                    <h6 class="doctor-card-title mb-0">Recent Activity</h6>
                                </div>
                                <div class="doctor-card-body">
                                    <div class="timeline">
                                        <div class="timeline-item">
                                            <div class="timeline-marker bg-success"></div>
                                            <div class="timeline-content">
                                                <small class="text-muted">{{ $patient->created_at->diffForHumans() }}</small>
                                                <p class="mb-0">Patient registered</p>
                                            </div>
                                        </div>
                                        
                                        @if($patient->updated_at != $patient->created_at)
                                            <div class="timeline-item">
                                                <div class="timeline-marker bg-info"></div>
                                                <div class="timeline-content">
                                                    <small class="text-muted">{{ $patient->updated_at->diffForHumans() }}</small>
                                                    <p class="mb-0">Profile updated</p>
                                                </div>
                                            </div>
                                        @endif
                                        
                                        @if($patient->appointments && $patient->appointments->count() > 0)
                                            @foreach($patient->appointments->take(3) as $appointment)
                                                <div class="timeline-item">
                                                    <div class="timeline-marker bg-{{ $appointment->status == 'completed' ? 'success' : ($appointment->status == 'cancelled' ? 'danger' : 'warning') }}"></div>
                                                    <div class="timeline-content">
                                                        <small class="text-muted">{{ $appointment->created_at->diffForHumans() }}</small>
                                                        <p class="mb-0">Appointment {{ $appointment->status }}</p>
                                                    </div>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="doctor-card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ contextRoute('patients.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Patients
                        </a>
                        <div>
                            <a href="{{ route('admin.patients.edit', $patient) }}" class="btn btn-warning">
                                <i class="fas fa-edit"></i> Edit Patient
                            </a>
                            <button type="button" class="btn btn-danger" onclick="deletePatient({{ $patient->id }})">
                                <i class="fas fa-trash"></i> Delete Patient
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">Contact {{ $patient->first_name }} {{ $patient->last_name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-12">
                        <h6>Contact Information</h6>
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Email:</strong></td>
                                <td>
                                    @if($patient->email)
                                        <a href="mailto:{{ $patient->email }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-envelope"></i> {{ $patient->email }}
                                        </a>
                                    @else
                                        <span class="text-muted">Not available</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Phone:</strong></td>
                                <td>
                                    @if($patient->phone)
                                        <a href="tel:{{ $patient->phone }}" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-phone"></i> {{ $patient->phone }}
                                        </a>
                                    @else
                                        <span class="text-muted">Not available</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
                
                @if($patient->emergency_contact_name || $patient->emergency_contact_phone)
                    <div class="row">
                        <div class="col-12">
                            <h6>Emergency Contact</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Name:</strong></td>
                                    <td>{{ $patient->emergency_contact_name ?: 'Not provided' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Phone:</strong></td>
                                    <td>
                                        @if($patient->emergency_contact_phone)
                                            <a href="tel:{{ $patient->emergency_contact_phone }}" class="btn btn-sm btn-outline-danger">
                                                <i class="fas fa-phone"></i> {{ $patient->emergency_contact_phone }}
                                            </a>
                                        @else
                                            <span class="text-muted">Not available</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 20px;
}

.timeline-item {
    position: relative;
    padding-bottom: 20px;
}

.timeline-item:last-child {
    padding-bottom: 0;
}

.timeline-marker {
    position: absolute;
    left: -25px;
    top: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
}

.timeline-item::before {
    content: '';
    position: absolute;
    left: -20px;
    top: 10px;
    bottom: -10px;
    width: 1px;
    background-color: #dee2e6;
}

.timeline-item:last-child::before {
    display: none;
}

.timeline-content {
    margin-left: 10px;
}

@media print {
    .btn, .modal, .card-footer {
        display: none !important;
    }
    
    .card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>
@endpush

@push('scripts')
<script>
function printPatientDetails() {
    window.print();
}

function exportPatientData() {
    // This would typically make an AJAX request to export patient data
    alert('Export functionality would be implemented here');
}

// Delete confirmation - using comprehensive confirmation dialog
function deletePatient(patientId) {
    console.log('Delete patient called with ID:', patientId);
    
    // Prevent any default behavior if event exists
    if (window.event) {
        window.event.preventDefault();
        window.event.stopPropagation();
    }
    
    // Handle both sync and async confirm dialogs
    function handleConfirmation(confirmResult) {
        console.log('User confirmation result:', confirmResult);
        
        if (confirmResult === true) {
            console.log('User confirmed deletion, proceeding...');
            
            // Add a small delay to ensure the dialog is properly closed
            setTimeout(() => {
                console.log('Creating form for deletion...');
                
                // Create a form to submit the DELETE request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/patients/${patientId}`;
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
    const confirmDelete = confirm('⚠️ WARNING: Are you sure you want to permanently delete this patient?\n\nThis action cannot be undone and will remove all patient data including:\n- Personal information\n- Medical history\n- Appointment history\n- Insurance information\n- Emergency contact details\n\nClick OK to confirm deletion or Cancel to abort.');
    
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
</script>
@endpush
