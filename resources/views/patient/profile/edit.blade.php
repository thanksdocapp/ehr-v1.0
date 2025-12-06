@extends('patient.layouts.app')

@section('title', 'Edit Profile')
@section('page-title', 'Edit Profile')

@section('content')
    <div class="row">
        <div class="col-lg-4 mb-4">
            <!-- Profile Photo -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-camera me-2"></i>
                        Profile Photo
                    </h5>
                </div>
                <div class="card-body text-center">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="{{ $patient->photo_url }}" alt="Profile Picture" 
                             id="profileImage" class="rounded-circle" 
                             style="width: 120px; height: 120px; object-fit: cover;">
                    </div>
                    @if($patient->photo)
                        <div class="mb-3">
                            <form action="{{ route('patient.profile.delete-photo') }}" method="POST" style="display: inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger btn-sm"
                                        onclick="return confirm('Are you sure you want to delete your profile photo?')">
                                    <i class="fas fa-trash me-1"></i>
                                    Remove Photo
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="col-lg-8">
            <!-- Profile Update Form -->
            <form method="POST" action="{{ route('patient.profile.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Photo Upload Section -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-camera me-2"></i>
                            Upload New Photo
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <input type="file" class="form-control @error('photo') is-invalid @enderror" 
                                   id="photo" name="photo" accept="image/*" onchange="previewImage(this)">
                            @error('photo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Maximum file size: 2MB. Supported formats: JPEG, PNG, JPG, GIF</div>
                        </div>
                    </div>
                </div>

                <!-- Personal Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-user me-2"></i>
                            Personal Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                       id="first_name" name="first_name" value="{{ old('first_name', $patient->first_name) }}" required>
                                @error('first_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                       id="last_name" name="last_name" value="{{ old('last_name', $patient->last_name) }}" required>
                                @error('last_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                       id="email" name="email" value="{{ old('email', $patient->email) }}" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                       id="phone" name="phone" value="{{ old('phone', $patient->phone) }}" required>
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="date_of_birth" class="form-label">Date of Birth <span class="text-danger">*</span></label>
                                <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                       id="date_of_birth" name="date_of_birth" 
                                       value="{{ old('date_of_birth', $patient->date_of_birth ? $patient->date_of_birth->format('Y-m-d') : '') }}" required>
                                @error('date_of_birth')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">Gender <span class="text-danger">*</span></label>
                                <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" {{ old('gender', $patient->gender) == 'male' ? 'selected' : '' }}>Male</option>
                                    <option value="female" {{ old('gender', $patient->gender) == 'female' ? 'selected' : '' }}>Female</option>
                                    <option value="other" {{ old('gender', $patient->gender) == 'other' ? 'selected' : '' }}>Other</option>
                                </select>
                                @error('gender')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="blood_group" class="form-label">Blood Group</label>
                                <select class="form-select @error('blood_group') is-invalid @enderror" id="blood_group" name="blood_group">
                                    <option value="">Select Blood Group</option>
                                    <option value="A+" {{ old('blood_group', $patient->blood_group) == 'A+' ? 'selected' : '' }}>A+</option>
                                    <option value="A-" {{ old('blood_group', $patient->blood_group) == 'A-' ? 'selected' : '' }}>A-</option>
                                    <option value="B+" {{ old('blood_group', $patient->blood_group) == 'B+' ? 'selected' : '' }}>B+</option>
                                    <option value="B-" {{ old('blood_group', $patient->blood_group) == 'B-' ? 'selected' : '' }}>B-</option>
                                    <option value="AB+" {{ old('blood_group', $patient->blood_group) == 'AB+' ? 'selected' : '' }}>AB+</option>
                                    <option value="AB-" {{ old('blood_group', $patient->blood_group) == 'AB-' ? 'selected' : '' }}>AB-</option>
                                    <option value="O+" {{ old('blood_group', $patient->blood_group) == 'O+' ? 'selected' : '' }}>O+</option>
                                    <option value="O-" {{ old('blood_group', $patient->blood_group) == 'O-' ? 'selected' : '' }}>O-</option>
                                </select>
                                @error('blood_group')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-address-book me-2"></i>
                            Contact Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="address" class="form-label">Address</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" 
                                          id="address" name="address" rows="3">{{ old('address', $patient->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="city" class="form-label">City</label>
                                <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                       id="city" name="city" value="{{ old('city', $patient->city) }}">
                                @error('city')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="state" class="form-label">State</label>
                                <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                       id="state" name="state" value="{{ old('state', $patient->state) }}">
                                @error('state')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="postal_code" class="form-label">Postal Code</label>
                                <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                       id="postal_code" name="postal_code" value="{{ old('postal_code', $patient->postal_code) }}">
                                @error('postal_code')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" class="form-control @error('country') is-invalid @enderror" 
                                       id="country" name="country" value="{{ old('country', $patient->country) }}">
                                @error('country')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Emergency Contact -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-phone-alt me-2"></i>
                            Emergency Contact
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="emergency_contact" class="form-label">Emergency Contact Name</label>
                                <input type="text" class="form-control @error('emergency_contact') is-invalid @enderror" 
                                       id="emergency_contact" name="emergency_contact" 
                                       value="{{ old('emergency_contact', $patient->emergency_contact) }}">
                                @error('emergency_contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="emergency_phone" class="form-label">Emergency Phone</label>
                                <input type="tel" class="form-control @error('emergency_phone') is-invalid @enderror" 
                                       id="emergency_phone" name="emergency_phone" 
                                       value="{{ old('emergency_phone', $patient->emergency_phone) }}">
                                @error('emergency_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Insurance Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-shield-alt me-2"></i>
                            Insurance Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="insurance_provider" class="form-label">Insurance Provider</label>
                                <input type="text" class="form-control @error('insurance_provider') is-invalid @enderror" 
                                       id="insurance_provider" name="insurance_provider" 
                                       value="{{ old('insurance_provider', $patient->insurance_provider) }}">
                                @error('insurance_provider')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="insurance_number" class="form-label">Insurance Number</label>
                                <input type="text" class="form-control @error('insurance_number') is-invalid @enderror" 
                                       id="insurance_number" name="insurance_number" 
                                       value="{{ old('insurance_number', $patient->insurance_number) }}">
                                @error('insurance_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medical Information -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-heartbeat me-2"></i>
                            Medical Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="allergies" class="form-label">Known Allergies</label>
                                <input type="text" class="form-control @error('allergies') is-invalid @enderror" 
                                       id="allergies" name="allergies" 
                                       value="{{ old('allergies', is_array($patient->allergies) ? implode(', ', $patient->allergies) : '') }}"
                                       placeholder="Separate multiple allergies with commas">
                                @error('allergies')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Example: Penicillin, Peanuts, Latex</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="medical_conditions" class="form-label">Medical Conditions</label>
                                <input type="text" class="form-control @error('medical_conditions') is-invalid @enderror" 
                                       id="medical_conditions" name="medical_conditions" 
                                       value="{{ old('medical_conditions', is_array($patient->medical_conditions) ? implode(', ', $patient->medical_conditions) : '') }}"
                                       placeholder="Separate multiple conditions with commas">
                                @error('medical_conditions')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Example: Diabetes, Hypertension, Asthma</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Notification Preferences -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-bell me-2"></i>
                            Notification Preferences
                        </h5>
                    </div>
                    <div class="card-body">
                        @php
                            $notifPrefs = $patient->notification_preferences ?? [];
                        @endphp
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle me-2"></i>
                            Choose how you'd like to receive notifications about appointments, lab results, and other updates.
                        </div>

                        <!-- Channel Preferences -->
                        <h6 class="mb-3">Notification Channels</h6>
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="notification_preferences[email_enabled]" value="0">
                                    <input class="form-check-input" type="checkbox" id="pref_email"
                                           name="notification_preferences[email_enabled]" value="1"
                                           {{ old('notification_preferences.email_enabled', $notifPrefs['email_enabled'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pref_email">
                                        <i class="fas fa-envelope me-1"></i> Email Notifications
                                    </label>
                                </div>
                                <small class="text-muted">Receive notifications via email</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="notification_preferences[sms_enabled]" value="0">
                                    <input class="form-check-input" type="checkbox" id="pref_sms"
                                           name="notification_preferences[sms_enabled]" value="1"
                                           {{ old('notification_preferences.sms_enabled', $notifPrefs['sms_enabled'] ?? false) ? 'checked' : '' }}
                                           {{ empty($patient->phone) ? 'disabled' : '' }}>
                                    <label class="form-check-label" for="pref_sms">
                                        <i class="fas fa-sms me-1"></i> SMS Notifications
                                    </label>
                                </div>
                                <small class="text-muted">{{ empty($patient->phone) ? 'Add phone number to enable' : 'Receive text messages' }}</small>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="notification_preferences[push_enabled]" value="0">
                                    <input class="form-check-input" type="checkbox" id="pref_push"
                                           name="notification_preferences[push_enabled]" value="1"
                                           {{ old('notification_preferences.push_enabled', $notifPrefs['push_enabled'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pref_push">
                                        <i class="fas fa-mobile-alt me-1"></i> Push Notifications
                                    </label>
                                </div>
                                <small class="text-muted">Browser and mobile push alerts</small>
                            </div>
                        </div>

                        <!-- Notification Types -->
                        <h6 class="mb-3">Notification Types</h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="notification_preferences[appointment_reminders]" value="0">
                                    <input class="form-check-input" type="checkbox" id="pref_appointments"
                                           name="notification_preferences[appointment_reminders]" value="1"
                                           {{ old('notification_preferences.appointment_reminders', $notifPrefs['appointment_reminders'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pref_appointments">
                                        <i class="fas fa-calendar-check me-1"></i> Appointment Reminders
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="notification_preferences[lab_results]" value="0">
                                    <input class="form-check-input" type="checkbox" id="pref_lab_results"
                                           name="notification_preferences[lab_results]" value="1"
                                           {{ old('notification_preferences.lab_results', $notifPrefs['lab_results'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pref_lab_results">
                                        <i class="fas fa-flask me-1"></i> Lab Results Ready
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="notification_preferences[prescription_updates]" value="0">
                                    <input class="form-check-input" type="checkbox" id="pref_prescriptions"
                                           name="notification_preferences[prescription_updates]" value="1"
                                           {{ old('notification_preferences.prescription_updates', $notifPrefs['prescription_updates'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pref_prescriptions">
                                        <i class="fas fa-pills me-1"></i> Prescription Updates
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="notification_preferences[billing_alerts]" value="0">
                                    <input class="form-check-input" type="checkbox" id="pref_billing"
                                           name="notification_preferences[billing_alerts]" value="1"
                                           {{ old('notification_preferences.billing_alerts', $notifPrefs['billing_alerts'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pref_billing">
                                        <i class="fas fa-file-invoice-dollar me-1"></i> Billing Alerts
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="notification_preferences[health_tips]" value="0">
                                    <input class="form-check-input" type="checkbox" id="pref_health_tips"
                                           name="notification_preferences[health_tips]" value="1"
                                           {{ old('notification_preferences.health_tips', $notifPrefs['health_tips'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pref_health_tips">
                                        <i class="fas fa-heartbeat me-1"></i> Health Tips & Updates
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="notification_preferences[promotional]" value="0">
                                    <input class="form-check-input" type="checkbox" id="pref_promotional"
                                           name="notification_preferences[promotional]" value="1"
                                           {{ old('notification_preferences.promotional', $notifPrefs['promotional'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pref_promotional">
                                        <i class="fas fa-bullhorn me-1"></i> Promotional Messages
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Quiet Hours -->
                        <h6 class="mb-3 mt-3">Quiet Hours (Do Not Disturb)</h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check form-switch">
                                    <input type="hidden" name="notification_preferences[quiet_hours_enabled]" value="0">
                                    <input class="form-check-input" type="checkbox" id="pref_quiet_hours"
                                           name="notification_preferences[quiet_hours_enabled]" value="1"
                                           {{ old('notification_preferences.quiet_hours_enabled', $notifPrefs['quiet_hours_enabled'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="pref_quiet_hours">Enable Quiet Hours</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="quiet_start" class="form-label">From</label>
                                <input type="time" class="form-control" id="quiet_start"
                                       name="notification_preferences[quiet_hours_start]"
                                       value="{{ old('notification_preferences.quiet_hours_start', $notifPrefs['quiet_hours_start'] ?? '22:00') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="quiet_end" class="form-label">To</label>
                                <input type="time" class="form-control" id="quiet_end"
                                       name="notification_preferences[quiet_hours_end]"
                                       value="{{ old('notification_preferences.quiet_hours_end', $notifPrefs['quiet_hours_end'] ?? '08:00') }}">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-lock me-2"></i>
                            Change Password
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Leave password fields empty if you don't want to change your password.
                        </div>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="current_password" class="form-label">Current Password</label>
                                <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                       id="current_password" name="current_password">
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">New Password</label>
                                <input type="password" class="form-control @error('new_password') is-invalid @enderror" 
                                       id="new_password" name="new_password">
                                @error('new_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Minimum 8 characters</div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="new_password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" class="form-control" 
                                       id="new_password_confirmation" name="new_password_confirmation">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('patient.profile') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Update Profile
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function previewImage(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                document.getElementById('profileImage').src = e.target.result;
            }
            
            reader.readAsDataURL(input.files[0]);
        }
    }
</script>
@endpush
