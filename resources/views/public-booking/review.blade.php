<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Review & Confirm - {{ $site_settings['hospital_name'] ?? getAppName() }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f5f7fa; color: #1a202c; line-height: 1.6; }
        .booking-container { max-width: 700px; margin: 0 auto; padding: 2rem 1rem; }
        .booking-header { text-align: center; margin-bottom: 3rem; }
        .booking-header h1 { font-size: 2rem; font-weight: 700; color: #1a202c; margin-bottom: 0.5rem; }
        .booking-header p { font-size: 1rem; color: #718096; }
        .progress-steps { display: flex; justify-content: center; align-items: center; margin-bottom: 3rem; gap: 1rem; }
        .step { display: flex; flex-direction: column; align-items: center; flex: 0 0 auto; }
        .step-circle { width: 40px; height: 40px; border-radius: 50%; background-color: #e2e8f0; color: #718096; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.875rem; margin-bottom: 0.5rem; transition: all 0.2s; }
        .step.active .step-circle { background-color: #2563eb; color: #ffffff; }
        .step.completed .step-circle { background-color: #10b981; color: #ffffff; }
        .step-label { font-size: 0.75rem; color: #718096; font-weight: 500; }
        .step.active .step-label { color: #2563eb; font-weight: 600; }
        .step-line { width: 60px; height: 2px; background-color: #e2e8f0; margin: 0 0.5rem; margin-top: -25px; }
        .step-line.completed { background-color: #10b981; }
        .review-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2rem; margin-bottom: 1.5rem; }
        .review-card-header { border-bottom: 1px solid #e2e8f0; padding-bottom: 1rem; margin-bottom: 1.5rem; }
        .review-card-header h3 { font-size: 1.25rem; font-weight: 600; color: #1a202c; margin: 0; }
        .review-row { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #f7fafc; }
        .review-row:last-child { border-bottom: none; }
        .review-label { font-size: 0.875rem; color: #718096; }
        .review-value { font-weight: 600; color: #1a202c; text-align: right; }
        .review-price { font-size: 1.5rem; font-weight: 700; color: #2563eb; }
        .btn-success { background-color: #10b981; border-color: #10b981; color: #ffffff; font-weight: 600; padding: 0.75rem 2rem; border-radius: 8px; transition: all 0.2s; }
        .btn-success:hover { background-color: #059669; border-color: #059669; }
        .btn-success:disabled { opacity: 0.6; cursor: not-allowed; }
        .btn-outline-secondary { border-color: #e2e8f0; color: #4a5568; font-weight: 600; padding: 0.75rem 2rem; border-radius: 8px; }
        @media (max-width: 768px) { .booking-header h1 { font-size: 1.5rem; } .step-line { width: 30px; } }
    </style>
</head>
<body>
    <div class="booking-container">
        <div class="booking-header">
            <h1>Review & Confirm</h1>
            <p>Please review your appointment details before confirming</p>
        </div>
        
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        
        @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif
        
        <div class="progress-steps">
            <div class="step completed">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <div class="step-label">Service</div>
            </div>
            <div class="step-line completed"></div>
            <div class="step completed">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <div class="step-label">Date & Time</div>
            </div>
            <div class="step-line completed"></div>
            <div class="step completed">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <div class="step-label">Your Details</div>
            </div>
            <div class="step-line completed"></div>
            <div class="step active">
                <div class="step-circle">4</div>
                <div class="step-label">Confirm</div>
            </div>
        </div>
        
        <form id="confirm-form" method="POST" action="{{ route('public.booking.confirm') }}">
            @csrf
            <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
            <input type="hidden" name="service_id" value="{{ $service->id }}">
            <input type="hidden" name="appointment_date" value="{{ $appointment_date }}">
            <input type="hidden" name="appointment_time" value="{{ $appointment_time }}">
            <input type="hidden" name="first_name" value="{{ $patient_data['first_name'] }}">
            <input type="hidden" name="last_name" value="{{ $patient_data['last_name'] }}">
            <input type="hidden" name="email" value="{{ $patient_data['email'] }}">
            <input type="hidden" name="phone" value="{{ $patient_data['phone'] }}">
            <input type="hidden" name="date_of_birth" value="{{ $patient_data['date_of_birth'] ?? '' }}">
            <input type="hidden" name="gender" value="{{ $patient_data['gender'] ?? '' }}">
            <input type="hidden" name="consultation_type" value="{{ $patient_data['consultation_type'] ?? 'in_person' }}">
            @if(isset($patient_data['department_id']))
            <input type="hidden" name="department_id" value="{{ $patient_data['department_id'] }}">
            @endif
            @if(isset($patient_data['notes']))
            <input type="hidden" name="notes" value="{{ $patient_data['notes'] }}">
            @endif
            @if(isset($patient_data['consent_share_with_gp']) && $patient_data['consent_share_with_gp'])
            <input type="hidden" name="consent_share_with_gp" value="1">
            @if(isset($patient_data['gp_name']))
            <input type="hidden" name="gp_name" value="{{ $patient_data['gp_name'] }}">
            @endif
            @if(isset($patient_data['gp_email']))
            <input type="hidden" name="gp_email" value="{{ $patient_data['gp_email'] }}">
            @endif
            @if(isset($patient_data['gp_phone']))
            <input type="hidden" name="gp_phone" value="{{ $patient_data['gp_phone'] }}">
            @endif
            @if(isset($patient_data['gp_address']))
            <input type="hidden" name="gp_address" value="{{ $patient_data['gp_address'] }}">
            @endif
            @endif
            
            <div class="review-card">
                <div class="review-card-header">
                    <h3><i class="fas fa-calendar-check me-2"></i>Appointment Summary</h3>
                </div>
                <div class="review-row">
                    <span class="review-label">Service</span>
                    <span class="review-value">{{ $service->name }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Duration</span>
                    <span class="review-value">{{ $service->default_duration_minutes ?? 60 }} minutes</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Doctor</span>
                    <span class="review-value">{{ $doctor->full_name }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Consultation Type</span>
                    <span class="review-value">
                        @if(isset($patient_data['consultation_type']) && $patient_data['consultation_type'] === 'online')
                            <i class="fas fa-video me-1"></i>Online Consultation
                        @else
                            <i class="fas fa-hospital me-1"></i>In-Person Consultation
                        @endif
                    </span>
                </div>
                <div class="review-row">
                    <span class="review-label">Date</span>
                    <span class="review-value">{{ \Carbon\Carbon::parse($appointment_date)->format('l, F j, Y') }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Time</span>
                    <span class="review-value">{{ \Carbon\Carbon::parse($appointment_time)->format('g:i A') }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Price</span>
                    <span class="review-price">
                        @if($price)
                        £{{ number_format($price, 2) }}
                        @else
                        On request
                        @endif
                    </span>
                </div>
            </div>
            
            <div class="review-card">
                <div class="review-card-header">
                    <h3><i class="fas fa-user me-2"></i>Your Information</h3>
                </div>
                <div class="review-row">
                    <span class="review-label">Name</span>
                    <span class="review-value">{{ $patient_data['first_name'] }} {{ $patient_data['last_name'] }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Email</span>
                    <span class="review-value">{{ $patient_data['email'] }}</span>
                </div>
                <div class="review-row">
                    <span class="review-label">Phone</span>
                    <span class="review-value">{{ $patient_data['phone'] }}</span>
                </div>
                @if(isset($patient_data['date_of_birth']))
                <div class="review-row">
                    <span class="review-label">Date of Birth</span>
                    <span class="review-value">{{ \Carbon\Carbon::parse($patient_data['date_of_birth'])->format('M d, Y') }}</span>
                </div>
                @endif
                @if(isset($patient_data['gender']))
                <div class="review-row">
                    <span class="review-label">Gender</span>
                    <span class="review-value">{{ ucfirst($patient_data['gender']) }}</span>
                </div>
                @endif
                @if(isset($patient_data['notes']) && $patient_data['notes'])
                <div class="review-row">
                    <span class="review-label">Notes</span>
                    <span class="review-value">{{ $patient_data['notes'] }}</span>
                </div>
                @endif
                @if(isset($patient_data['consent_share_with_gp']) && $patient_data['consent_share_with_gp'])
                <div class="review-row">
                    <span class="review-label">GP Consent</span>
                    <span class="review-value"><i class="fas fa-check-circle text-success me-1"></i>Yes</span>
                </div>
                @if(isset($patient_data['gp_name']))
                <div class="review-row">
                    <span class="review-label">GP Name</span>
                    <span class="review-value">{{ $patient_data['gp_name'] }}</span>
                </div>
                @endif
                @if(isset($patient_data['gp_email']))
                <div class="review-row">
                    <span class="review-label">GP Email</span>
                    <span class="review-value">{{ $patient_data['gp_email'] }}</span>
                </div>
                @endif
                @endif
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="button" onclick="window.history.back()" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </button>
                <button type="submit" class="btn btn-success btn-lg" id="confirm-btn">
                    <i class="fas fa-check me-2"></i>Confirm Appointment
                </button>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('confirm-form');
            const confirmBtn = document.getElementById('confirm-btn');
            
            if (!form || !confirmBtn) {
                console.error('Form or confirm button not found');
                return;
            }
            
            let isSubmitting = false;
            
            // Handle form submission
            form.addEventListener('submit', function(e) {
                // Prevent double submission
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                
                isSubmitting = true;
                
                // Disable button and show loading state
                if (confirmBtn && !confirmBtn.disabled) {
                    confirmBtn.disabled = true;
                    confirmBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...';
                }
                
                // Allow form to submit normally
                return true;
            });
            
            // Handle button click - just ensure form submits
            confirmBtn.addEventListener('click', function(e) {
                // Log for debugging
                console.log('Confirm button clicked');
                
                // If form is already submitting, prevent click
                if (isSubmitting) {
                    e.preventDefault();
                    return false;
                }
                
                // Trigger form submission
                if (form) {
                    // Validate CSRF token is present
                    const csrfToken = form.querySelector('input[name="_token"]');
                    if (!csrfToken || !csrfToken.value) {
                        console.error('CSRF token missing');
                        e.preventDefault();
                        alert('Security token missing. Please refresh the page and try again.');
                        return false;
                    }
                    
                    // Let the form submit normally
                    return true;
                }
            });
        });
    </script>
</body>
</html>
