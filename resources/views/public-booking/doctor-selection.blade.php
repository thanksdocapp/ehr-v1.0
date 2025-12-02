<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Select Doctor - {{ $site_settings['hospital_name'] ?? getAppName() }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f5f7fa; color: #1a202c; line-height: 1.6; }
        .booking-container { max-width: 1200px; margin: 0 auto; padding: 2rem 1rem; }
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
        .info-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; margin-bottom: 2rem; }
        .info-card h3 { font-size: 1.25rem; font-weight: 600; color: #1a202c; margin-bottom: 0.5rem; }
        .info-card p { color: #718096; margin: 0; }
        .doctors-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .doctor-card { background: #ffffff; border: 2px solid #e2e8f0; border-radius: 12px; padding: 1.5rem; cursor: pointer; transition: all 0.2s; }
        .doctor-card:hover { border-color: #2563eb; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1); }
        .doctor-card.selected { border-color: #2563eb; background-color: #eff6ff; }
        .doctor-radio { position: absolute; opacity: 0; pointer-events: none; }
        .doctor-info { display: flex; align-items: center; gap: 1rem; }
        .doctor-avatar { width: 60px; height: 60px; border-radius: 50%; background-color: #e2e8f0; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #718096; }
        .doctor-details h4 { font-size: 1.125rem; font-weight: 600; color: #1a202c; margin-bottom: 0.25rem; }
        .doctor-details p { font-size: 0.875rem; color: #718096; margin: 0; }
        .btn-primary { background-color: #2563eb; border-color: #2563eb; color: #ffffff; font-weight: 600; padding: 0.75rem 2rem; border-radius: 8px; transition: all 0.2s; }
        .btn-primary:hover { background-color: #1d4ed8; border-color: #1d4ed8; }
        .btn-primary:disabled { background-color: #cbd5e1; border-color: #cbd5e1; cursor: not-allowed; }
        @media (max-width: 768px) { .booking-header h1 { font-size: 1.5rem; } .doctors-grid { grid-template-columns: 1fr; } .step-line { width: 30px; } }
    </style>
</head>
<body>
    <div class="booking-container">
        <div class="booking-header">
            <h1>Select a Doctor</h1>
            <p>Choose your preferred doctor</p>
        </div>
        
        <div class="progress-steps">
            <div class="step completed">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <div class="step-label">Service</div>
            </div>
            <div class="step-line completed"></div>
            <div class="step active">
                <div class="step-circle">2</div>
                <div class="step-label">Doctor</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-circle">3</div>
                <div class="step-label">Date & Time</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-circle">4</div>
                <div class="step-label">Your Details</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-circle">5</div>
                <div class="step-label">Confirm</div>
            </div>
        </div>
        
        <div class="info-card">
            <h3>{{ $department->name }}</h3>
            <p>Service: {{ $service->name }}</p>
        </div>
        
        <form id="doctor-form" method="POST" action="{{ route('public.booking.select-datetime') }}">
            @csrf
            <input type="hidden" name="service_id" value="{{ $service->id }}">
            <input type="hidden" name="department_id" value="{{ $department->id }}">
            
            @if($doctors->isEmpty())
            <div class="text-center py-5">
                <i class="fas fa-user-times fa-3x text-muted mb-3"></i>
                <p class="text-muted">No doctors available for this service.</p>
            </div>
            @else
            <div class="doctors-grid">
                @foreach($doctors as $doctor)
                <label class="doctor-card">
                    <input type="radio" name="doctor_id" value="{{ $doctor->id }}" class="doctor-radio" required>
                    <div class="doctor-info">
                        <div class="doctor-avatar">
                            <i class="fas fa-user-md"></i>
                        </div>
                        <div class="doctor-details">
                            <h4>{{ $doctor->full_name }}</h4>
                            <p>{{ $doctor->specialization }}</p>
                        </div>
                    </div>
                </label>
                @endforeach
            </div>
            @endif
            
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('public.booking.clinic', $department->slug) }}" class="btn btn-outline-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Back
                </a>
                <button type="submit" class="btn btn-primary btn-lg" id="continue-btn" disabled>
                    Continue <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const doctorCards = document.querySelectorAll('.doctor-card');
            const continueBtn = document.getElementById('continue-btn');
            
            doctorCards.forEach(card => {
                card.addEventListener('click', function() {
                    doctorCards.forEach(c => c.classList.remove('selected'));
                    this.classList.add('selected');
                    const radio = this.querySelector('.doctor-radio');
                    if (radio) {
                        radio.checked = true;
                        continueBtn.disabled = false;
                    }
                });
            });
        });
    </script>
</body>
</html>

