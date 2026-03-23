@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Edit Patient')
@section('page-title', 'Edit Patient')
@section('page-subtitle', 'Update patient information')

@section('content')
<div class="fade-in-up">

    <form action="{{ route('staff.patients.update', $patient->id) }}" method="POST" id="patientEditForm" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Basic Information -->
            <div class="col-lg-8">
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-user-md me-2"></i>Personal Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label fw-semibold">First Name <span class="text-danger">*</span></label>
                                <input type="text" name="first_name" id="first_name" 
                                       class="form-control @error('first_name') is-invalid @enderror" 
                                       value="{{ old('first_name', $patient->first_name) }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label fw-semibold">Last Name <span class="text-danger">*</span></label>
                                <input type="text" name="last_name" id="last_name" 
                                       class="form-control @error('last_name') is-invalid @enderror" 
                                       value="{{ old('last_name', $patient->last_name) }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label fw-semibold">Date of Birth <span class="text-danger">*</span></label>
                                <input type="text" name="date_of_birth" id="date_of_birth" 
                                       class="form-control uk-date uk-date-dob @error('date_of_birth') is-invalid @enderror" 
                                       value="{{ old('date_of_birth') ? (old('date_of_birth') && preg_match('/^\d{4}-\d{2}-\d{2}$/', old('date_of_birth')) ? \Carbon\Carbon::parse(old('date_of_birth'))->format('d/m/Y') : old('date_of_birth')) : ($patient->date_of_birth ? $patient->date_of_birth->format('d/m/Y') : '') }}" 
                                       placeholder="dd/mm/yyyy"
                                       pattern="\d{2}/\d{2}/\d{4}"
                                       maxlength="10"
                                       required>
                                <small class="text-muted">Format: dd/mm/yyyy (e.g., 15/01/2025)</small>
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label fw-semibold">Gender <span class="text-danger">*</span></label>
                                <select name="gender" id="gender" class="form-select @error('gender') is-invalid @enderror" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $patient->gender) === 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $patient->gender) === 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $patient->gender) === 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="patient_id" class="form-label fw-semibold">Patient ID</label>
                                <input type="text" name="patient_id" id="patient_id" 
                                       class="form-control @error('patient_id') is-invalid @enderror" 
                                       value="{{ old('patient_id', $patient->patient_id) }}" readonly>
                                <small class="text-muted">Patient ID cannot be changed</small>
                                @error('patient_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Contact Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-address-book me-2"></i>Contact Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $patient->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-semibold">Phone Number <span class="text-danger">*</span></label>
                                <input type="tel" name="phone" id="phone" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $patient->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @php
                            if (session()->hasOldInput()) {
                                $editAddressLine1 = old('address', '');
                                $editAddressLine2 = old('address_line_2', '');
                            } else {
                                $pa = $patient->address ?? '';
                                $pa = is_string($pa) ? $pa : '';
                                $parts = preg_split("/\r\n|\n|\r/", $pa, 2);
                                $editAddressLine1 = $parts[0] ?? '';
                                $editAddressLine2 = isset($parts[1]) ? $parts[1] : '';
                            }
                        @endphp
                        <div class="mb-4 pt-3 border-top">
                            <p class="text-uppercase small fw-semibold text-muted mb-2"><i class="fas fa-map-marker-alt me-1"></i>Address</p>
                            <div class="row">
                                <div class="col-md-12 mb-3">
                                    <label for="ideal_postcodes_finder" class="form-label fw-semibold">Find address</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                                        <input type="text"
                                               id="ideal_postcodes_finder"
                                               class="form-control"
                                               autocomplete="off"
                                               placeholder="Start typing postcode or address…">
                                    </div>
                                    <small class="text-muted" id="ideal_postcodes_notice" style="display:none;"></small>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="address" class="form-label fw-semibold">Address line 1 <span class="text-danger">*</span></label>
                                    <input type="text" name="address" id="address"
                                           class="form-control @error('address') is-invalid @enderror"
                                           value="{{ $editAddressLine1 }}" placeholder="House number and street name" required>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="address_line_2" class="form-label fw-semibold">Address line 2 <small class="text-muted">(optional)</small></label>
                                    <input type="text" name="address_line_2" id="address_line_2"
                                           class="form-control @error('address_line_2') is-invalid @enderror"
                                           value="{{ $editAddressLine2 }}" placeholder="Apartment, suite, unit, etc.">
                                    @error('address_line_2')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="city" class="form-label fw-semibold">Town / city</label>
                                    <input type="text" name="city" id="city"
                                           class="form-control @error('city') is-invalid @enderror"
                                           value="{{ old('city', $patient->city) }}" placeholder="Enter town or city">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label fw-semibold">County</label>
                                    <input type="text" name="state" id="state"
                                           class="form-control @error('state') is-invalid @enderror"
                                           value="{{ old('state', $patient->state) }}" placeholder="Enter county">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label for="postal_code" class="form-label fw-semibold">Postcode</label>
                                    <input type="text" name="postal_code" id="postal_code"
                                           class="form-control @error('postal_code') is-invalid @enderror"
                                           value="{{ old('postal_code', $patient->postal_code) }}" placeholder="e.g. SW1A 1AA"
                                           style="text-transform: uppercase;">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Emergency Contact Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-user-shield me-2"></i>Emergency Contact</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_name" class="form-label fw-semibold">Emergency Contact Name</label>
                                <input type="text" name="emergency_contact_name" id="emergency_contact_name" 
                                       class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                       value="{{ old('emergency_contact_name', $patient->emergency_contact) }}" 
                                       placeholder="Enter emergency contact name">
                                @error('emergency_contact_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact_phone" class="form-label fw-semibold">Emergency Contact Phone</label>
                                <input type="tel" name="emergency_contact_phone" id="emergency_contact_phone" 
                                       class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                       value="{{ old('emergency_contact_phone', $patient->emergency_phone) }}" 
                                       placeholder="+000 123 456 789">
                                @error('emergency_contact_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                @php
                    $dobSe = $patient->date_of_birth ? \Carbon\Carbon::parse($patient->date_of_birth) : null;
                    $isUnder18Se = $dobSe !== null && $dobSe->age < 18;
                @endphp
                <div class="doctor-card mb-4" id="guardian_id_document_group" style="display: {{ $isUnder18Se ? 'block' : 'none' }};">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-user-shield me-2 text-primary"></i>Parent / guardian</h5>
                    </div>
                    <div class="doctor-card-body">
                        <p class="small text-muted mb-3">For patients under 18, guardian or parent name and phone are required. You can upload or replace the guardian ID document here at any time (optional).</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="guardian_name" class="form-label fw-semibold">Guardian / parent name @if($isUnder18Se)<span class="text-danger">*</span>@endif</label>
                                <input type="text" name="guardian_name" id="guardian_name"
                                       class="form-control @error('guardian_name') is-invalid @enderror"
                                       value="{{ old('guardian_name', $patient->guardian_name) }}"
                                       placeholder="Full name"
                                       autocomplete="name"
                                       @if($isUnder18Se) required @endif>
                                @error('guardian_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="guardian_phone" class="form-label fw-semibold">Guardian / parent phone @if($isUnder18Se)<span class="text-danger">*</span>@endif</label>
                                <input type="tel" name="guardian_phone" id="guardian_phone"
                                       class="form-control @error('guardian_phone') is-invalid @enderror"
                                       value="{{ old('guardian_phone', $patient->guardian_phone) }}"
                                       placeholder="Contact number"
                                       autocomplete="tel"
                                       @if($isUnder18Se) required @endif>
                                @error('guardian_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        @if($patient->guardian_id_document_path)
                        <div class="alert alert-success mb-3">
                            <i class="fas fa-check-circle me-2"></i>
                            <strong>Guardian ID document</strong> is on file. Upload a new file below to replace it.
                        </div>
                        @endif
                        <div class="mb-0">
                            <label for="guardian_id_document" class="form-label fw-semibold">Guardian ID document <small class="text-muted fw-normal">(optional)</small></label>
                            <input type="file" name="guardian_id_document" id="guardian_id_document"
                                   class="form-control @error('guardian_id_document') is-invalid @enderror"
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted d-block mt-1">PDF, JPG, or PNG, max 5MB.</small>
                            @error('guardian_id_document')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Clinic Assignment (for doctors and staff) -->
                @php
                    $user = auth()->user();
                    $selectedDeptIds = old('department_ids', $patient->getDepartmentIds());
                    if (!is_array($selectedDeptIds) && $selectedDeptIds) {
                        $selectedDeptIds = [$selectedDeptIds];
                    }
                    if (empty($selectedDeptIds) && old('department_id', $patient->department_id)) {
                        $selectedDeptIds = [old('department_id', $patient->department_id)];
                    }
                    $userDepartments = [];
                    if ($user->role === 'doctor') {
                        $doctor = \App\Models\Doctor::where('user_id', $user->id)->with('departments')->first();
                        if ($doctor) {
                            if ($doctor->departments->isNotEmpty()) {
                                $userDepartments = $doctor->departments->pluck('id')->toArray();
                            } elseif ($doctor->department_id) {
                                $userDepartments = [$doctor->department_id];
                            }
                        }
                    } else {
                        // For staff users, get their departments
                        $user->load('departments');
                        if ($user->departments->isNotEmpty()) {
                            $userDepartments = $user->departments->pluck('id')->toArray();
                        } elseif ($user->department_id) {
                            $userDepartments = [$user->department_id];
                        }
                    }
                @endphp
                @if($user->role === 'doctor' || $user->is_admin || $user->role === 'admin')
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-building me-2"></i>Clinic Assignment</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <label for="department_ids" class="form-label fw-semibold">Assign to Clinic(s)</label>
                            @if($departments->isEmpty())
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>No Clinics Available:</strong> You are not assigned to any clinic/department. Please contact an administrator to assign you to a clinic.
                                </div>
                            @else
                                <select class="form-select @error('department_ids') is-invalid @enderror" 
                                        id="department_ids" name="department_ids[]" multiple size="5">
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" 
                                                {{ ($selectedDeptIds && in_array($department->id, $selectedDeptIds)) ? 'selected' : '' }}>
                                            {{ $department->name }}
                                        </option>
                                    @endforeach
                                </select>
                            @endif
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Hold <kbd>Ctrl</kbd> (Windows/Linux) or <kbd>Cmd</kbd> (Mac) to select multiple clinics. First selected clinic will be set as primary.
                                @if(!$user->is_admin && $user->role !== 'admin' && !empty($userDepartments))
                                    <br><strong>Your assigned clinic(s):</strong> You belong to {{ count($userDepartments) }} clinic(s).
                                @elseif($user->is_admin || $user->role === 'admin')
                                    <br><strong>Admin access:</strong> You can assign patients to any clinic.
                                @endif
                            </small>
                            @error('department_ids')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @error('department_ids.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <!-- Hidden field for backward compatibility with single department_id -->
                        <input type="hidden" id="department_id" name="department_id" value="{{ old('department_id', $patient->department_id) }}">
                    </div>
                </div>
                @endif
                
                <!-- Medical Information -->
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h5 class="doctor-card-title mb-0"><i class="fas fa-stethoscope me-2"></i>Medical Information</h5>
                    </div>
                    <div class="doctor-card-body">
                        <div class="mb-3">
                            <label for="allergies" class="form-label fw-semibold">Allergies</label>
                            <div id="allergies-container">
                                @php
                                    $allergies = old('allergies', $patient->allergies ?? []);
                                @endphp
                                @if($allergies && count($allergies) > 0)
                                    @foreach($allergies as $allergy)
                                        <div class="input-group mb-2 allergy-item">
                                            <input type="text" class="form-control" name="allergies[]" 
                                                   value="{{ $allergy }}" placeholder="Enter allergy">
                                            <button type="button" class="btn btn-outline-danger remove-allergy">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 allergy-item">
                                        <input type="text" class="form-control" name="allergies[]" 
                                               placeholder="Enter allergy">
                                        <button type="button" class="btn btn-outline-danger remove-allergy">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </div>
                            <button type="button" class="btn btn-outline-primary btn-sm" id="add-allergy">
                                <i class="fas fa-plus"></i> Add Allergy
                            </button>
                        </div>

                        <div class="mb-3">
                            <label for="medical_history" class="form-label fw-semibold">Medical History</label>
                            <textarea name="medical_history" id="medical_history" rows="4" 
                                      class="form-control @error('medical_history') is-invalid @enderror" 
                                      placeholder="Enter patient's medical history">{{ old('medical_history', $patient->notes) }}</textarea>
                            @error('medical_history')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-0">
                            <input class="form-check-input" type="checkbox" 
                                   id="is_active" name="is_active" value="1" 
                                   {{ old('is_active', $patient->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                <strong>Active Patient</strong>
                            </label>
                            <small class="text-muted d-block">Check to activate patient record</small>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Actions -->
            <div class="col-lg-4">
                <div class="doctor-card mb-4">
                    <div class="doctor-card-header">
                        <h6 class="doctor-card-title mb-0 fw-semibold">Actions</h6>
                    </div>
                    <div class="doctor-card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-save me-2"></i>Update Patient
                            </button>
                            <a href="{{ route('staff.patients.show', $patient->id) }}" class="btn btn-info">
                                <i class="fas fa-eye me-2"></i>View Patient
                            </a>
                            <a href="{{ route('staff.patients.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Back to List
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Current Information -->
                <div class="card shadow-sm">
                    <div class="card-header bg-warning text-white py-3">
                        <h6 class="card-title mb-0">Current Information</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <small class="text-muted d-block">Patient ID</small>
                            <strong>#{{ $patient->id }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Email</small>
                            <strong>{{ $patient->email }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Phone</small>
                            <strong>{{ $patient->phone }}</strong>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Registered Date</small>
                            <strong>{{ formatDateUk($patient->created_at) }}</strong>
                        </div>
                        <div class="mb-0">
                            <small class="text-muted d-block">Last Updated</small>
                            <strong>{{ formatDateTimeUkAmPm($patient->updated_at) }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
<script>
$(document).ready(function() {
    // Wait for Flatpickr to load
    if (typeof flatpickr === 'undefined') {
        console.error('Flatpickr failed to load');
    }

    // Sync department_ids to department_id hidden field (for backward compatibility)
    // First selected department becomes the primary department_id
    $('#department_ids').on('change', function() {
        const selectedIds = $(this).val();
        if (selectedIds && selectedIds.length > 0) {
            // Set first selected department as primary (for backward compatibility)
            $('#department_id').val(selectedIds[0]);
        } else {
            $('#department_id').val('');
        }
    });
    
    // Initialize department_id from patient's departments if present
    const selectedIds = $('#department_ids').val();
    if (selectedIds && selectedIds.length > 0) {
        $('#department_id').val(selectedIds[0]);
    }
    
    // Form validation
    $('#patientEditForm').on('submit', function(e) {
        let isValid = true;
        
        // Check required fields
        $(this).find('[required]').each(function() {
            if (!$(this).val().trim()) {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields.');
            return false;
        }
        
        // Show loading state
        $(this).find('button[type="submit"]').html('<i class="fas fa-spinner fa-spin me-1"></i>Updating...').prop('disabled', true);
    });

    // Real-time validation
    $('input, select, textarea').on('blur', function() {
        if ($(this).prop('required') && !$(this).val().trim()) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Email validation
    $('#email').on('blur', function() {
        const email = $(this).val();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (email && !emailRegex.test(email)) {
            $(this).addClass('is-invalid');
            $(this).siblings('.invalid-feedback').text('Please enter a valid email address.');
        }
    });

    // Phone number formatting (simple)
    $('#phone, #emergency_contact_phone').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length >= 10) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        }
        $(this).val(value);
    });

    // Ideal Postcodes Address Finder (same behaviour as staff patient create)
    (function initIdealPostcodes() {
        const apiKey = @json(\App\Models\Setting::get('ideal_postcodes_api_key') ?: config('services.ideal_postcodes.api_key'));
        const input = document.getElementById('ideal_postcodes_finder');
        const notice = document.getElementById('ideal_postcodes_notice');
        if (!input || !notice) return;

        function hideNotice() {
            notice.style.display = 'none';
            notice.textContent = '';
        }

        function showNotice(msg) {
            notice.style.display = 'block';
            notice.innerHTML = '<i class="fas fa-info-circle me-1"></i>' + msg;
        }

        function getAF() {
            if (typeof AddressFinder !== 'undefined' && AddressFinder) return AddressFinder;
            if (window.IdealPostcodes && window.IdealPostcodes.AddressFinder) return window.IdealPostcodes.AddressFinder;
            return null;
        }

        function ensureAFScript() {
            if (getAF()) return;
            if (document.querySelector('script[data-ideal-postcodes-af="1"]')) return;

            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/@ideal-postcodes/address-finder-bundled@5/dist/address-finder.js';
            s.async = true;
            s.setAttribute('data-ideal-postcodes-af', '1');
            document.head.appendChild(s);
        }

        function waitForAF(timeoutMs) {
            return new Promise(function(resolve, reject) {
                const start = Date.now();
                ensureAFScript();

                (function tick() {
                    const AF = getAF();
                    if (AF && typeof AF.setup === 'function') return resolve(AF);
                    if (Date.now() - start > timeoutMs) return reject(new Error('Ideal Postcodes AddressFinder load timeout'));
                    setTimeout(tick, 50);
                })();
            });
        }

        if (!apiKey) {
            showNotice('Address lookup is unavailable (missing API key). Please enter address manually.');
            return;
        }
        hideNotice();
        waitForAF(8000)
            .then(function(AF) {
                AF.setup({
                    apiKey: apiKey,
                    inputField: input,
                    outputFields: {
                        line_1: '#address',
                        line_2: '#address_line_2',
                        post_town: '#city',
                        county: '#state',
                        postcode: '#postal_code',
                    },
                    onCheckFailed: function() {
                        showNotice('Address lookup is unavailable right now. Please enter address manually.');
                    },
                });
            })
            .catch(function(e) {
                console.error('Ideal Postcodes load/init failed:', e);
                showNotice('Address lookup failed to load. Please enter address manually.');
            });
    })();

    function staffEditParseDob(val) {
        if (!val || !String(val).trim()) return null;
        var s = String(val).trim();
        var m = s.match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (m) return new Date(parseInt(m[3], 10), parseInt(m[2], 10) - 1, parseInt(m[1], 10));
        var d = new Date(s);
        return isNaN(d.getTime()) ? null : d;
    }

    // Age calculation: under 18 shows guardian contact (name/phone required); ID file always optional on edit
    function calculateAgeAndToggleGuardian() {
        const guardianGroup = $('#guardian_id_document_group');
        const guardianInput = $('#guardian_id_document');
        const guardianName = $('#guardian_name');
        const guardianPhone = $('#guardian_phone');
        const birthDate = staffEditParseDob($('#date_of_birth').val());
        const today = new Date();

        if (!birthDate || isNaN(birthDate.getTime())) {
            guardianGroup.slideUp();
            guardianInput.prop('required', false);
            guardianName.prop('required', false);
            guardianPhone.prop('required', false);
            return;
        }

        let age = today.getFullYear() - birthDate.getFullYear();
        const mo = today.getMonth() - birthDate.getMonth();
        if (mo < 0 || (mo === 0 && today.getDate() < birthDate.getDate())) {
            age--;
        }

        if (age < 0) {
            alert('Birth date cannot be in the future.');
            $('#date_of_birth').val('');
            guardianGroup.slideUp();
            guardianInput.prop('required', false);
            guardianName.prop('required', false);
            guardianPhone.prop('required', false);
        } else if (age > 150) {
            alert('Please check the birth date. Age seems too high.');
        } else if (age < 18) {
            guardianGroup.slideDown();
            guardianName.prop('required', true);
            guardianPhone.prop('required', true);
            guardianInput.prop('required', false);
        } else {
            guardianGroup.slideUp();
            guardianInput.prop('required', false);
            guardianName.prop('required', false);
            guardianPhone.prop('required', false);
        }
    }
    
    // Date of Birth UK format (dd/mm/yyyy) with Flatpickr calendar picker
    (function initDateOfBirthPicker() {
        const dobInput = document.getElementById('date_of_birth');
        if (!dobInput) return;

        // Wait for Flatpickr to be available
        if (typeof flatpickr === 'undefined') {
            console.error('Flatpickr library not loaded');
            return;
        }

        // Initialize Flatpickr with UK format
        const dobPicker = flatpickr(dobInput, {
            dateFormat: "d/m/Y",
            altInput: false,
            altFormat: "d/m/Y",
            locale: {
                firstDayOfWeek: 1 // Monday
            },
            maxDate: "today",
            minDate: new Date(new Date().setFullYear(new Date().getFullYear() - 150)),
            allowInput: true, // Allow manual typing
            clickOpens: true,
            onChange: function(selectedDates, dateStr, instance) {
                // Ensure format is dd/mm/yyyy
                if (dateStr && dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
                    const date = new Date(dateStr);
                    const dd = String(date.getDate()).padStart(2, '0');
                    const mm = String(date.getMonth() + 1).padStart(2, '0');
                    const yyyy = date.getFullYear();
                    instance.input.value = dd + '/' + mm + '/' + yyyy;
                }
                // Trigger age calculation
                calculateAgeAndToggleGuardian();
            }
        });
        
        // Convert dd/mm/yyyy to yyyy-mm-dd before form submission
        const form = document.getElementById('patientEditForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const dobValue = dobInput.value.trim();
                
                if (dobValue) {
                    // Check if it's in dd/mm/yyyy format
                    if (dobValue.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                        const parts = dobValue.split('/');
                        // Convert to yyyy-mm-dd format
                        const convertedDate = parts[2] + '-' + parts[1] + '-' + parts[0];
                        dobInput.value = convertedDate;
                    }
                }
            });
        }
    })();

    $('#date_of_birth').on('change', calculateAgeAndToggleGuardian);
    
    // Calculate age on page load if date is already set
    if ($('#date_of_birth').val()) {
        calculateAgeAndToggleGuardian();
    }
    
    // GP Consent checkbox toggle - backup handler (primary should be inline)
    $('#consent_share_with_gp').on('change', function() {
        if (typeof handleGpConsentChange === 'function') {
            handleGpConsentChange(this);
        } else {
            const gpDetailsGroup = $('#gp_details_group');
            const gpFields = ['gp_name', 'gp_email', 'gp_phone', 'gp_address'];
            
            if ($(this).is(':checked')) {
                gpDetailsGroup.slideDown();
                gpFields.forEach(function(field) {
                    $('#' + field).prop('required', true);
                });
            } else {
                gpDetailsGroup.slideUp();
                gpFields.forEach(function(field) {
                    $('#' + field).prop('required', false);
                });
            }
        }
    });
    
    // Initialize GP details visibility based on consent checkbox state
    setTimeout(function() {
        if ($('#consent_share_with_gp').is(':checked')) {
            if (typeof handleGpConsentChange === 'function') {
                handleGpConsentChange(document.getElementById('consent_share_with_gp'));
            } else {
                $('#gp_details_group').show();
                ['gp_name', 'gp_email', 'gp_phone', 'gp_address'].forEach(function(field) {
                    $('#' + field).prop('required', true);
                });
            }
        }
    }, 200);
    
    // Add allergy functionality
    $('#add-allergy').click(function() {
        const allergyHtml = `
            <div class="input-group mb-2 allergy-item">
                <input type="text" class="form-control" name="allergies[]" placeholder="Enter allergy">
                <button type="button" class="btn btn-outline-danger remove-allergy">
                    <i class="fas fa-trash"></i>
                </button>
            </div>
        `;
        $('#allergies-container').append(allergyHtml);
    });

    // Remove allergy functionality
    $(document).on('click', '.remove-allergy', function() {
        if ($('.allergy-item').length > 1) {
            $(this).closest('.allergy-item').remove();
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.card {
    border: none;
    border-radius: 10px;
}

.card-header {
    border-radius: 10px 10px 0 0 !important;
    border-bottom: 1px solid rgba(0,0,0,0.125);
}

.form-control:focus,
.form-select:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
}

.btn {
    border-radius: 6px;
    font-weight: 500;
}

.breadcrumb-item a {
    color: #6c757d;
    text-decoration: none;
}

.breadcrumb-item a:hover {
    color: #495057;
}

.alert {
    border-radius: 8px;
    border: none;
}
</style>
@endpush
@endsection
