<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Select Date & Time - {{ $site_settings['hospital_name'] ?? getAppName() }}</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background-color: #f5f7fa; color: #1a202c; line-height: 1.6; }
        .booking-container { max-width: 900px; margin: 0 auto; padding: 2rem 1rem; }
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
        .summary-card h4 { font-size: 1.125rem; font-weight: 600; color: #1a202c; margin-bottom: 0.5rem; }
        .summary-card p { color: #718096; margin: 0; font-size: 0.875rem; }
        .form-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 2rem; margin-bottom: 2rem; }
        .form-label { font-weight: 600; color: #1a202c; margin-bottom: 0.75rem; font-size: 0.875rem; }
        .form-control { border: 1px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; font-size: 1rem; transition: all 0.2s; }
        .form-control:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1); outline: none; }
        .time-slots-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 0.75rem; margin-top: 1rem; }
        .time-slot-btn { background: #ffffff; border: 2px solid #e2e8f0; border-radius: 8px; padding: 0.75rem 1rem; text-align: center; cursor: pointer; transition: all 0.2s; font-weight: 500; color: #1a202c; }
        .time-slot-btn:hover { border-color: #2563eb; background-color: #eff6ff; }
        .time-slot-btn.selected { border-color: #2563eb; background-color: #2563eb; color: #ffffff; }
        .time-slot-btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .loading-spinner { text-align: center; padding: 2rem; color: #718096; }
        .empty-message { text-align: center; padding: 2rem; color: #718096; background: #f7fafc; border-radius: 8px; }
        .btn-primary { background-color: #2563eb; border-color: #2563eb; color: #ffffff; font-weight: 600; padding: 0.75rem 2rem; border-radius: 8px; transition: all 0.2s; }
        .btn-primary:hover { background-color: #1d4ed8; border-color: #1d4ed8; }
        .btn-primary:disabled { background-color: #cbd5e1; border-color: #cbd5e1; cursor: not-allowed; }
        .btn-outline-secondary { border-color: #e2e8f0; color: #4a5568; font-weight: 600; padding: 0.75rem 2rem; border-radius: 8px; }
        @media (max-width: 768px) { .booking-header h1 { font-size: 1.5rem; } .time-slots-grid { grid-template-columns: repeat(auto-fill, minmax(100px, 1fr)); } .step-line { width: 30px; } }
    </style>
</head>
<body>
    <div class="booking-container">
        <div class="booking-header">
            <h1>Select Date & Time</h1>
            <p>Choose your preferred appointment slot</p>
        </div>
        
        <div class="progress-steps">
            <div class="step completed">
                <div class="step-circle"><i class="fas fa-check"></i></div>
                <div class="step-label">Service</div>
            </div>
            <div class="step-line completed"></div>
            <div class="step active">
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
        
        <div class="summary-card">
            <h4>{{ $service->name }}</h4>
            <p>{{ $service->default_duration_minutes ?? 60 }} minutes • 
            @if($service->default_price)
            £{{ number_format($service->default_price, 2) }}
            @else
            Price on request
            @endif
            </p>
        </div>
        
        <form id="datetime-form" method="POST" action="{{ route('public.booking.patient-details') }}">
            @csrf
            <input type="hidden" name="doctor_id" value="{{ $doctor->id }}">
            <input type="hidden" name="service_id" value="{{ $service->id }}">
            @if(isset($department_id))
            <input type="hidden" name="department_id" value="{{ $department_id }}">
            @endif
            
            <div class="form-card">
                <label class="form-label">Select Date</label>
                <input type="date" name="appointment_date" id="appointment-date" class="form-control" min="{{ date('Y-m-d') }}" required>
                <small class="text-muted d-block mt-2">Select a date to see available time slots</small>
                
                <div id="time-slots-container" style="display: none; margin-top: 2rem;">
                    <label class="form-label">Available Time Slots</label>
                    <div id="time-slots-grid" class="time-slots-grid"></div>
                    <div id="no-slots-message" class="empty-message" style="display: none;">
                        <i class="fas fa-info-circle me-2"></i>No available slots on this date. Please select another date.
                    </div>
                </div>
                
                <div id="loading-slots" class="loading-spinner" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <p class="mt-2">Loading available slots...</p>
                </div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="{{ isset($department) ? route('public.booking.clinic', $department->slug) : route('public.booking.doctor', $doctor->slug) }}" class="btn btn-outline-secondary btn-lg">
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
            const dateInput = document.getElementById('appointment-date');
            const timeSlotsContainer = document.getElementById('time-slots-container');
            const timeSlotsGrid = document.getElementById('time-slots-grid');
            const noSlotsMessage = document.getElementById('no-slots-message');
            const loadingSlots = document.getElementById('loading-slots');
            const continueBtn = document.getElementById('continue-btn');
            const form = document.getElementById('datetime-form');
            let selectedTime = null;
            
            dateInput.addEventListener('change', function() {
                const selectedDate = this.value;
                if (!selectedDate) {
                    timeSlotsContainer.style.display = 'none';
                    continueBtn.disabled = true;
                    return;
                }
                
                loadingSlots.style.display = 'block';
                timeSlotsContainer.style.display = 'none';
                continueBtn.disabled = true;
                selectedTime = null;
                
                fetch(`{{ route('public.api.available-slots', $doctor->id) }}?date=${selectedDate}&service_id={{ $service->id }}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    loadingSlots.style.display = 'none';
                    timeSlotsContainer.style.display = 'block';
                    timeSlotsGrid.innerHTML = '';
                    
                    if (data.slots && data.slots.length > 0) {
                        noSlotsMessage.style.display = 'none';
                        data.slots.forEach(slot => {
                            const slotBtn = document.createElement('button');
                            slotBtn.type = 'button';
                            slotBtn.className = 'time-slot-btn';
                            slotBtn.textContent = slot.display || slot.start;
                            slotBtn.dataset.time = slot.start;
                            slotBtn.addEventListener('click', function() {
                                document.querySelectorAll('.time-slot-btn').forEach(btn => {
                                    btn.classList.remove('selected');
                                });
                                this.classList.add('selected');
                                selectedTime = this.dataset.time;
                                continueBtn.disabled = false;
                            });
                            timeSlotsGrid.appendChild(slotBtn);
                        });
                    } else {
                        noSlotsMessage.style.display = 'block';
                    }
                })
                .catch(error => {
                    console.error('Error loading slots:', error);
                    loadingSlots.style.display = 'none';
                    alert('Failed to load available time slots. Please try again.');
                });
            });
            
            form.addEventListener('submit', function(e) {
                if (!selectedTime) {
                    e.preventDefault();
                    alert('Please select a time slot.');
                    return false;
                }
                
                const timeInput = document.createElement('input');
                timeInput.type = 'hidden';
                timeInput.name = 'appointment_time';
                timeInput.value = selectedTime;
                form.appendChild(timeInput);
            });
        });
    </script>
</body>
</html>
