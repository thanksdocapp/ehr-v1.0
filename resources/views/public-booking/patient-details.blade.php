<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Your Details - {{ $site_settings['hospital_name'] ?? getAppName() }}</title>
    
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
        .summary-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 0.75rem; }
        .summary-row:last-child { margin-bottom: 0; }
        .summary-label { font-size: 0.875rem; color: #718096; }
        .summary-value { font-weight: 600; color: #1a202c; }
        .form-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2rem; margin-bottom: 2rem; }
        .form-label { font-weight: 600; color: #1a202c; margin-bottom: 0.75rem; font-size: 0.875rem; }
        .form-label .text-danger { color: #dc2626; }
        .form-control { border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; font-size: 1rem; transition: all 0.2s; }
        .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); outline: none; }
        .form-control.is-invalid { border-color: #dc2626; }
        .invalid-feedback { color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem; }
        .form-check { margin-top: 1.5rem; }
        .form-check-input { margin-top: 0.25rem; }
        .form-check-label { font-size: 0.875rem; color: #4a5568; }
        .btn-primary { background-color: #2563eb; border-color: #2563eb; color: #ffffff; font-weight: 600; padding: 0.75rem 2rem; border-radius: 8px; transition: all 0.2s; }
        .btn-primary:hover { background-color: #1d4ed8; border-color: #1d4ed8; }
        .btn-outline-secondary { border-color: #e2e8f0; color: #4a5568; font-weight: 600; padding: 0.75rem 2rem; border-radius: 8px; }
        @media (max-width: 768px) { .booking-header h1 { font-size: 1.5rem; } .step-line { width: 30px; } }
    </style>
</head>
<body>
    <div class="booking-container">
        <div class="booking-header">
            <h1>Your Details</h1>
            <p>Please provide your contact information</p>
        </div>
        
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
            <div class="step active">
                <div class="step-circle">3</div>
                <div class="step-label">Your Details</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-circle">4</div>
                <div class="step-label">Confirm</div>
            </div>
        </div>
        
        <div class="summary-card">
            <div class="summary-row">
                <span class="summary-label">Service</span>
                <span class="summary-value">{{ $service->name }}</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Date & Time</span>
                <span class="summary-value">
                    {{ \Carbon\Carbon::parse($appointment_date)->format('l, F j, Y') }} at {{ \Carbon\Carbon::parse($appointment_time)->format('g:i A') }}
                </span>
            </div>
        </div>
        
        <form id="patient-details-form" method="POST" action="{{ route('public.booking.review') }}">
            @csrf
            <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
            <input type="hidden" name="service_id" value="{{ $service->id }}">
            <input type="hidden" name="appointment_date" value="{{ $appointment_date }}">
            <input type="hidden" name="appointment_time" value="{{ $appointment_time }}">
            @if(isset($department_id))
            <input type="hidden" name="department_id" value="{{ $department_id }}">
            @endif
            
            <div class="form-card">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="first_name" class="form-label">First Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('first_name') is-invalid @enderror" id="first_name" name="first_name" required value="{{ old('first_name') }}">
                        @error('first_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="col-md-6 mb-3">
                        <label for="last_name" class="form-label">Last Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('last_name') is-invalid @enderror" id="last_name" name="last_name" required value="{{ old('last_name') }}">
                        @error('last_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" required value="{{ old('email') }}">
                    @error('email')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone Number <span class="text-danger">*</span></label>
                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" id="phone" name="phone" required value="{{ old('phone') }}">
                    @error('phone')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Consultation Type <span class="text-danger">*</span></label>
                    <div class="d-flex gap-3">
                        <div class="form-check form-check-inline" style="flex: 1;">
                            <input class="form-check-input @error('consultation_type') is-invalid @enderror" type="radio" id="consultation_in_person" name="consultation_type" value="in_person" {{ old('consultation_type', 'in_person') === 'in_person' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="consultation_in_person" style="cursor: pointer; width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 8px; text-align: center; transition: all 0.2s;">
                                <i class="fas fa-hospital me-2"></i>In-Person Consultation
                            </label>
                        </div>
                        <div class="form-check form-check-inline" style="flex: 1;">
                            <input class="form-check-input @error('consultation_type') is-invalid @enderror" type="radio" id="consultation_online" name="consultation_type" value="online" {{ old('consultation_type') === 'online' ? 'checked' : '' }} required>
                            <label class="form-check-label" for="consultation_online" style="cursor: pointer; width: 100%; padding: 0.75rem; border: 2px solid #e2e8f0; border-radius: 8px; text-align: center; transition: all 0.2s;">
                                <i class="fas fa-video me-2"></i>Online Consultation
                            </label>
                        </div>
                    </div>
                    <style>
                        .form-check-input:checked + .form-check-label {
                            border-color: #2563eb !important;
                            background-color: #eff6ff;
                            color: #2563eb;
                            font-weight: 600;
                        }
                        .form-check-input:focus + .form-check-label {
                            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
                        }
                    </style>
                    @error('consultation_type')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Additional Notes (Optional)</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Any additional information you'd like to share...">{{ old('notes') }}</textarea>
                </div>
                
                <div class="form-check">
                    <input class="form-check-input @error('consent') is-invalid @enderror" type="checkbox" id="consent" name="consent" value="1" required>
                    <label class="form-check-label" for="consent">
                        I consent to the processing of my personal data for appointment purposes <span class="text-danger">*</span>
                    </label>
                    @error('consent')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <button type="button" onclick="window.history.back()" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </button>
                <button type="submit" class="btn btn-primary btn-lg">
                    Continue <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
