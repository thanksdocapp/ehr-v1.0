<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Book Appointment - {{ $site_settings['hospital_name'] ?? getAppName() }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background-color: #f5f7fa;
            color: #1a202c;
            line-height: 1.6;
        }
        
        .booking-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem 1rem;
        }
        
        .booking-header {
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .booking-header h1 {
            font-size: 2rem;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .booking-header p {
            font-size: 1rem;
            color: #718096;
        }
        
        .progress-steps {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 3rem;
            gap: 1rem;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 0 0 auto;
        }
        
        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #e2e8f0;
            color: #718096;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
            transition: all 0.2s;
        }
        
        .step.active .step-circle {
            background-color: #2563eb;
            color: #ffffff;
        }
        
        .step.completed .step-circle {
            background-color: #10b981;
            color: #ffffff;
        }
        
        .step-label {
            font-size: 0.75rem;
            color: #718096;
            font-weight: 500;
        }
        
        .step.active .step-label {
            color: #2563eb;
            font-weight: 600;
        }
        
        .step-line {
            width: 60px;
            height: 2px;
            background-color: #e2e8f0;
            margin: 0 0.5rem;
            margin-top: -25px;
        }
        
        .step-line.completed {
            background-color: #10b981;
        }
        
        .info-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .info-card h3 {
            font-size: 1.25rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .info-card p {
            color: #718096;
            margin: 0;
        }
        
        .services-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .service-card {
            background: #ffffff;
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 1.5rem;
            cursor: pointer;
            transition: all 0.2s;
            position: relative;
        }
        
        .service-card:hover {
            border-color: #2563eb;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.1);
        }
        
        .service-card.selected {
            border-color: #2563eb;
            background-color: #eff6ff;
        }
        
        .service-radio {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        
        .service-card-header {
            margin-bottom: 1rem;
        }
        
        .service-card-header h4 {
            font-size: 1.125rem;
            font-weight: 600;
            color: #1a202c;
            margin-bottom: 0.5rem;
        }
        
        .service-card-header p {
            font-size: 0.875rem;
            color: #718096;
            margin: 0;
        }
        
        .service-card-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #e2e8f0;
        }
        
        .service-duration {
            font-size: 0.875rem;
            color: #718096;
        }
        
        .service-price {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a202c;
        }
        
        .service-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.75rem;
        }
        
        .service-tag {
            font-size: 0.75rem;
            padding: 0.25rem 0.75rem;
            background-color: #f7fafc;
            color: #4a5568;
            border-radius: 6px;
            font-weight: 500;
        }
        
        .btn-primary {
            background-color: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
            font-weight: 600;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .btn-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
        }
        
        .btn-primary:disabled {
            background-color: #cbd5e1;
            border-color: #cbd5e1;
            cursor: not-allowed;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #718096;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #cbd5e1;
        }
        
        @media (max-width: 768px) {
            .booking-header h1 {
                font-size: 1.5rem;
            }
            
            .services-grid {
                grid-template-columns: 1fr;
            }
            
            .step-line {
                width: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <!-- Header -->
        <div class="booking-header">
            <h1>Book Your Appointment</h1>
            <p>Select a service to continue</p>
        </div>
        
        <!-- Progress Steps -->
        <div class="progress-steps">
            <div class="step active">
                <div class="step-circle">1</div>
                <div class="step-label">Service</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-circle">2</div>
                <div class="step-label">Date & Time</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-circle">3</div>
                <div class="step-label">Your Details</div>
            </div>
            <div class="step-line"></div>
            <div class="step">
                <div class="step-circle">4</div>
                <div class="step-label">Confirm</div>
            </div>
        </div>
        
        <!-- Clinic/Doctor Info -->
        @if(isset($department))
        <div class="info-card">
            <h3>{{ $department->name }}</h3>
            @if($department->description)
            <p>{{ $department->description }}</p>
            @endif
        </div>
        @elseif(isset($doctor))
        <div class="info-card">
            <h3>Dr. {{ $doctor->full_name }}</h3>
            <p>{{ $doctor->specialization }}</p>
        </div>
        @endif
        
        <!-- Services Selection -->
        <form id="service-form" method="POST" action="{{ route('public.booking.select-datetime') }}">
            @csrf
            @if(isset($doctor))
            <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
            @endif
            @if(isset($department))
            <input type="hidden" name="department_id" value="{{ $department->id }}">
            @endif
            
            @if($services->isEmpty())
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <p>No services available at this time.</p>
            </div>
            @else
            <div class="services-grid">
                @foreach($services as $service)
                <label class="service-card">
                    <input type="radio" name="service_id" value="{{ $service->id }}" class="service-radio" required>
                    <div class="service-card-header">
                        <h4>{{ $service->name }}</h4>
                        @if($service->description)
                        <p>{{ Str::limit($service->description, 100) }}</p>
                        @endif
                    </div>
                    <div class="service-card-footer">
                        <span class="service-duration">{{ $service->default_duration_minutes ?? 60 }} min</span>
                        <span class="service-price">
                            @if($service->default_price)
                            £{{ number_format($service->default_price, 2) }}
                            @else
                            On request
                            @endif
                        </span>
                    </div>
                    @if($service->tags && is_array($service->tags) && count($service->tags) > 0)
                    <div class="service-tags">
                        @foreach(array_slice($service->tags, 0, 3) as $tag)
                        <span class="service-tag">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                </label>
                @endforeach
            </div>
            @endif
            
            <div class="text-center">
                <button type="submit" class="btn btn-primary btn-lg" id="continue-btn" disabled>
                    Continue <i class="fas fa-arrow-right ms-2"></i>
                </button>
            </div>
        </form>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const serviceCards = document.querySelectorAll('.service-card');
            const continueBtn = document.getElementById('continue-btn');
            const form = document.getElementById('service-form');
            
            serviceCards.forEach(card => {
                card.addEventListener('click', function() {
                    // Remove selected class from all cards
                    serviceCards.forEach(c => c.classList.remove('selected'));
                    // Add selected class to clicked card
                    this.classList.add('selected');
                    // Check the radio button
                    const radio = this.querySelector('.service-radio');
                    if (radio) {
                        radio.checked = true;
                        continueBtn.disabled = false;
                    }
                });
            });
            
            form.addEventListener('submit', function(e) {
                const selectedService = document.querySelector('input[name="service_id"]:checked');
                if (!selectedService) {
                    e.preventDefault();
                    alert('Please select a service to continue.');
                    return false;
                }
            });
        });
    </script>
</body>
</html>
