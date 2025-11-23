@include('partials.page-header', [
    'pageTitle' => 'Book Appointment - ' . ($site_settings['hospital_name'] ?? getAppName()),
    'pageDescription' => 'Schedule your appointment with our expert medical team at ' . ($site_settings['hospital_name'] ?? getAppName()),
    'heroTitle' => 'Book Your Appointment',
    'heroSubtitle' => 'Schedule your visit with our expert medical team',
    'showBreadcrumbs' => !request()->routeIs('homepage'),
    'currentPage' => 'Book Appointment'
])

<!-- CSRF Token for AJAX -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<script>
    // Set CSRF token for AJAX requests
    window.Laravel = {
        csrfToken: '{{ csrf_token() }}'
    };
</script>

    <!-- Appointment Booking Section -->
    <section class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="glass-card p-4">
                        <!-- Progress Bar -->
                        <div class="progress mb-4" style="height: 6px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: 25%" id="progress-bar"></div>
                        </div>
                        <!-- Step 1: Select Department -->
                    <div id="appointment-form-step-1" class="appointment-form-step">
                        <h4 class="text-primary">Step 1: Select a Department</h4>
                        <p class="text-muted mb-3">Choose the medical department you need.</p>
                        <div class="form-group">
                            <select id="department-select" class="form-control form-control-lg" required>
                                <option value="">Select a Department</option>
                                @foreach($departments as $department)
                                    <option value="{{ $department->id }}">{{ $department->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="text-center mt-4">
                            <button id="to-step-2" class="btn btn-primary btn-lg" disabled>Next <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- Step 2: Select Doctor -->
                    <div id="appointment-form-step-2" class="appointment-form-step d-none">
                        <h4 class="text-primary">Step 2: Select a Doctor</h4>
                        <p class="text-muted mb-3">Choose your preferred doctor from the selected department.</p>
                        <div class="form-group">
                            <select id="doctor-select" class="form-control form-control-lg" required disabled>
                                <option value="">Loading doctors...</option>
                            </select>
                        </div>
                        <div class="text-center mt-4">
                            <button id="back-to-step-1" class="btn btn-secondary btn-lg mr-2"><i class="fas fa-arrow-left"></i> Back</button>
                            <button id="to-step-3" class="btn btn-primary btn-lg" disabled>Next <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- Step 3: Select Date and Time -->
                    <div id="appointment-form-step-3" class="appointment-form-step d-none">
                        <h4 class="text-primary">Step 3: Select Date and Time</h4>
                        <p class="text-muted mb-3">Choose your preferred appointment date and time.</p>
                        <div class="form-group">
                            <label for="date-select">Select Date:</label>
                            <input type="date" id="date-select" class="form-control form-control-lg" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="timeslot-select">Select Time:</label>
                            <select id="timeslot-select" class="form-control form-control-lg" required disabled>
                                <option value="">Select a date first</option>
                            </select>
                        </div>
                        <div class="text-center mt-4">
                            <button id="back-to-step-2" class="btn btn-secondary btn-lg mr-2"><i class="fas fa-arrow-left"></i> Back</button>
                            <button id="to-step-4" class="btn btn-primary btn-lg" disabled>Next <i class="fas fa-arrow-right"></i></button>
                        </div>
                    </div>

                    <!-- Step 4: Patient Details -->
                    <div id="appointment-form-step-4" class="appointment-form-step d-none">
                        <h4 class="text-primary">Step 4: Enter Patient Details</h4>
                        <p class="text-muted mb-3">Provide your contact information for the appointment.</p>
                        <div class="form-group">
                            <label for="patient-name">Full Name:</label>
                            <input type="text" id="patient-name" placeholder="Enter your full name" class="form-control form-control-lg" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="patient-email">Email Address:</label>
                            <input type="email" id="patient-email" placeholder="Enter your email address" class="form-control form-control-lg" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="patient-phone">Phone Number:</label>
                            <input type="tel" id="patient-phone" placeholder="Enter your phone number" class="form-control form-control-lg" required>
                        </div>
                        <div class="form-group mt-3">
                            <label for="patient-reason">Reason for Appointment (Optional):</label>
                            <textarea id="patient-reason" placeholder="Brief description of your concern" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="text-center mt-4">
                            <button id="back-to-step-3" class="btn btn-secondary btn-lg mr-2"><i class="fas fa-arrow-left"></i> Back</button>
                            <button id="submit-appointment" class="btn btn-success btn-lg"><i class="fas fa-check"></i> Book Appointment</button>
                        </div>
                    </div>

                    <!-- Confirmation -->
                    <div id="appointment-confirmation" class="appointment-confirmation d-none text-center">
                        <div class="alert alert-success">
                            <h4 class="text-success"><i class="fas fa-check-circle"></i> Appointment Confirmed!</h4>
                            <p class="mb-0">Your appointment has been successfully booked. You will receive a confirmation email shortly.</p>
                        </div>
                        <div id="appointment-details" class="mt-4">
                            <!-- Appointment details will be populated here -->
                        </div>
                        <div class="mt-4">
                            <a href="{{ route('homepage') }}" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Back to Home
                            </a>
                        </div>
                    </div>

                    <!-- Loading Spinner -->
                    <div id="loading-spinner" class="text-center d-none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Loading...</span>
                        </div>
                        <p class="mt-2">Processing your request...</p>
                    </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('partials.footer')

    <!-- Back to Top Button -->
    <button id="backToTop" class="btn btn-primary rounded-circle position-fixed bottom-0 end-0 m-4 shadow" style="display: none;">
        <i class="fas fa-arrow-up"></i>
    </button>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

    <script>
        // Initialize AOS
        AOS.init({
            duration: 1000,
            once: true
        });

        // Back to Top Button
        window.addEventListener('scroll', function() {
            const backToTopBtn = document.getElementById('backToTop');
            if (window.scrollY > 300) {
                backToTopBtn.style.display = 'block';
            } else {
                backToTopBtn.style.display = 'none';
            }
        });

        document.getElementById('backToTop').addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const departmentSelect = document.getElementById('department-select');
        const doctorSelect = document.getElementById('doctor-select');
        const dateSelect = document.getElementById('date-select');
        const timeslotSelect = document.getElementById('timeslot-select');
        const progressBar = document.getElementById('progress-bar');
        const loadingSpinner = document.getElementById('loading-spinner');
        
        const steps = [
            document.getElementById('appointment-form-step-1'),
            document.getElementById('appointment-form-step-2'),
            document.getElementById('appointment-form-step-3'),
            document.getElementById('appointment-form-step-4')
        ];

        let currentStep = 0;
        let selectedData = {
            department: null,
            doctor: null,
            date: null,
            timeslot: null
        };

        // Progress bar update
        function updateProgressBar() {
            const progress = ((currentStep + 1) / 4) * 100;
            progressBar.style.width = progress + '%';
        }

        // Show/hide steps
        function showStep(stepIndex) {
            steps.forEach((step, index) => {
                if (index === stepIndex) {
                    step.classList.remove('d-none');
                } else {
                    step.classList.add('d-none');
                }
            });
            currentStep = stepIndex;
            updateProgressBar();
        }

        // Step 1: Department selection
        departmentSelect.addEventListener('change', function() {
            const departmentId = this.value;
            const nextButton = document.getElementById('to-step-2');
            
            if (departmentId) {
                selectedData.department = departmentId;
                nextButton.disabled = false;
                nextButton.classList.add('btn-primary');
                nextButton.classList.remove('btn-secondary');
            } else {
                nextButton.disabled = true;
                nextButton.classList.remove('btn-primary');
                nextButton.classList.add('btn-secondary');
            }
        });

        // Step 1 to 2: Load doctors
        document.getElementById('to-step-2').addEventListener('click', function() {
            showStep(1);
            loadDoctors(selectedData.department);
        });

        // Back to step 1
        document.getElementById('back-to-step-1').addEventListener('click', function() {
            showStep(0);
        });

        // Step 2: Doctor selection
        doctorSelect.addEventListener('change', function() {
            const doctorId = this.value;
            const nextButton = document.getElementById('to-step-3');
            
            if (doctorId) {
                selectedData.doctor = doctorId;
                nextButton.disabled = false;
                nextButton.classList.add('btn-primary');
                nextButton.classList.remove('btn-secondary');
            } else {
                nextButton.disabled = true;
                nextButton.classList.remove('btn-primary');
                nextButton.classList.add('btn-secondary');
            }
        });

        // Step 2 to 3
        document.getElementById('to-step-3').addEventListener('click', function() {
            showStep(2);
            setupDateSelection();
        });

        // Back to step 2
        document.getElementById('back-to-step-2').addEventListener('click', function() {
            showStep(1);
        });

        // Step 3: Date selection
        dateSelect.addEventListener('change', function() {
            const selectedDate = this.value;
            if (selectedDate) {
                selectedData.date = selectedDate;
                loadTimeSlots(selectedData.doctor, selectedDate);
            }
        });

        // Step 3: Time selection
        timeslotSelect.addEventListener('change', function() {
            const selectedTime = this.value;
            const nextButton = document.getElementById('to-step-4');
            
            if (selectedTime) {
                selectedData.timeslot = selectedTime;
                nextButton.disabled = false;
                nextButton.classList.add('btn-primary');
                nextButton.classList.remove('btn-secondary');
            } else {
                nextButton.disabled = true;
                nextButton.classList.remove('btn-primary');
                nextButton.classList.add('btn-secondary');
            }
        });

        // Step 3 to 4
        document.getElementById('to-step-4').addEventListener('click', function() {
            showStep(3);
        });

        // Back to step 3
        document.getElementById('back-to-step-3').addEventListener('click', function() {
            showStep(2);
        });

        // Submit appointment
        document.getElementById('submit-appointment').addEventListener('click', function() {
            const patientName = document.getElementById('patient-name').value;
            const patientEmail = document.getElementById('patient-email').value;
            const patientPhone = document.getElementById('patient-phone').value;
            const patientReason = document.getElementById('patient-reason').value;

            if (!patientName || !patientEmail || !patientPhone) {
                alert('Please fill in all required fields.');
                return;
            }

            submitAppointment({
                department_id: selectedData.department,
                doctor_id: selectedData.doctor,
                appointment_date: selectedData.date,
                appointment_time: selectedData.timeslot,
                patient_name: patientName,
                patient_email: patientEmail,
                patient_phone: patientPhone,
                reason: patientReason
            });
        });

        // AJAX Functions
        function loadDoctors(departmentId) {
            doctorSelect.innerHTML = '<option value="">Loading doctors...</option>';
            doctorSelect.disabled = true;

            fetch(`/appointments/doctors/${departmentId}`)
                .then(response => response.json())
                .then(data => {
                    doctorSelect.innerHTML = '<option value="">Select a Doctor</option>';
                    data.forEach(doctor => {
                        doctorSelect.innerHTML += `<option value="${doctor.id}">${doctor.name} - ${doctor.specialization}</option>`;
                    });
                    doctorSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading doctors:', error);
                    doctorSelect.innerHTML = '<option value="">Error loading doctors</option>';
                });
        }

        function setupDateSelection() {
            const today = new Date();
            const tomorrow = new Date(today);
            tomorrow.setDate(tomorrow.getDate() + 1);
            
            const maxDate = new Date(today);
            maxDate.setMonth(maxDate.getMonth() + 2);
            
            dateSelect.min = tomorrow.toISOString().split('T')[0];
            dateSelect.max = maxDate.toISOString().split('T')[0];
        }

        function loadTimeSlots(doctorId, date) {
            timeslotSelect.innerHTML = '<option value="">Loading time slots...</option>';
            timeslotSelect.disabled = true;

            fetch(`/appointments/slots/${doctorId}?date=${date}`)
                .then(response => response.json())
                .then(data => {
                    timeslotSelect.innerHTML = '<option value="">Select a Time Slot</option>';
                    data.forEach(slot => {
                        timeslotSelect.innerHTML += `<option value="${slot}">${slot}</option>`;
                    });
                    timeslotSelect.disabled = false;
                })
                .catch(error => {
                    console.error('Error loading time slots:', error);
                    timeslotSelect.innerHTML = '<option value="">Error loading time slots</option>';
                });
        }

        function submitAppointment(appointmentData) {
            // Show loading
            steps[3].classList.add('d-none');
            loadingSpinner.classList.remove('d-none');

            fetch('/appointments', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify(appointmentData)
            })
            .then(response => response.json())
            .then(data => {
                loadingSpinner.classList.add('d-none');
                if (data.success) {
                    showConfirmation(data.appointment);
                } else {
                    alert('Error booking appointment: ' + data.message);
                    showStep(3);
                }
            })
            .catch(error => {
                console.error('Error submitting appointment:', error);
                loadingSpinner.classList.add('d-none');
                alert('Error booking appointment. Please try again.');
                showStep(3);
            });
        }

        function showConfirmation(appointment) {
            document.getElementById('appointment-confirmation').classList.remove('d-none');
            document.getElementById('appointment-details').innerHTML = `
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Appointment Details</h5>
                        <p><strong>Department:</strong> ${appointment.department}</p>
                        <p><strong>Doctor:</strong> ${appointment.doctor}</p>
                        <p><strong>Date:</strong> ${appointment.date}</p>
                        <p><strong>Time:</strong> ${appointment.time}</p>
                        <p><strong>Patient:</strong> ${appointment.patient}</p>
                        <p><strong>Appointment ID:</strong> ${appointment.id}</p>
                    </div>
                </div>
            `;
            progressBar.style.width = '100%';
        }

        // Initialize
        updateProgressBar();
    });
    </script>
</body>
</html>
