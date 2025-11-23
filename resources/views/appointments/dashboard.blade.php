@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="mb-0"><i class="fas fa-calendar-check"></i> My Appointments Dashboard</h4>
                            @if(isset($patient))
                                <small>Patient ID: {{ $patient->patient_id }} | {{ $patient->full_name }}</small>
                            @endif
                        </div>
                        <div class="col-md-4 text-right">
                            <button class="btn btn-light btn-sm" id="refresh-appointments">
                                <i class="fas fa-sync"></i> Refresh
                            </button>
                            <a href="{{ route('appointments.create') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> New Appointment
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="appointment-list">
                        <!-- Appointment list will be dynamically loaded here -->
                    </div>
                </div>
            </div>

            <!-- Loading Spinner -->
            <div class="text-center mt-4 d-none" id="loading-spinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
                <p class="mt-2">Loading your appointments...</p>
            </div>

            <!-- No Appointments Message -->
            <div class="text-center mt-4 d-none" id="no-appointments-message">
                <div class="alert alert-info">
                    <h5><i class="fas fa-info-circle"></i> No Upcoming Appointments</h5>
                    <p class="mb-0">You have no upcoming appointments.</p>
                    <a href="{{ route('appointments.create') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus"></i> Book Your First Appointment
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reschedule Modal -->
<div class="modal fade" id="rescheduleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reschedule Appointment</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="reschedule-form">
                    <div class="form-group">
                        <label for="reschedule-date">New Date:</label>
                        <input type="date" id="reschedule-date" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label for="reschedule-time">New Time:</label>
                        <select id="reschedule-time" class="form-control" required>
                            <option value="">Select a time slot</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirm-reschedule">Reschedule</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const appointmentList = document.getElementById('appointment-list');
        const loadingSpinner = document.getElementById('loading-spinner');
        const noAppointmentsMessage = document.getElementById('no-appointments-message');
        const refreshButton = document.getElementById('refresh-appointments');
        const patientId = @if(isset($patient)) '{{ $patient->patient_id }}' @else null @endif;
        
        let currentAppointmentId = null;

        function showLoading() {
            loadingSpinner.classList.remove('d-none');
            noAppointmentsMessage.classList.add('d-none');
            appointmentList.innerHTML = '';
        }

        function hideLoading() {
            loadingSpinner.classList.add('d-none');
        }

        function loadAppointments() {
            if (!patientId) {
                console.error('Patient ID not found');
                return;
            }

            showLoading();

            fetch(`/api/appointments?patient_id=${patientId}`)
                .then(response => response.json())
                .then(appointments => {
                    hideLoading();

                    if (appointments.length === 0) {
                        noAppointmentsMessage.classList.remove('d-none');
                    } else {
                        renderAppointments(appointments);
                    }
                })
                .catch(error => {
                    console.error('Error loading appointments:', error);
                    hideLoading();
                    showAlert('Failed to load appointments. Please try again later.', 'danger');
                });
        }

        function renderAppointments(appointments) {
            appointmentList.innerHTML = '';
            
            appointments.forEach(appointment => {
                const appointmentCard = document.createElement('div');
                appointmentCard.className = 'card mb-3 border-left-' + appointment.status_color;
                appointmentCard.innerHTML = `
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <h5 class="card-title">
                                    <i class="fas fa-calendar-alt"></i> Appointment #${appointment.appointment_number}
                                </h5>
                                <div class="row">
                                    <div class="col-sm-6">
                                        <p class="card-text">
                                            <strong><i class="fas fa-clock"></i> Date & Time:</strong><br>
                                            ${appointment.date} at ${appointment.time}
                                        </p>
                                    </div>
                                    <div class="col-sm-6">
                                        <p class="card-text">
                                            <strong><i class="fas fa-user-md"></i> Doctor:</strong><br>
                                            ${appointment.doctor.full_name}<br>
                                            <small class="text-muted">${appointment.doctor.specialization}</small>
                                        </p>
                                    </div>
                                </div>
                                <p class="card-text">
                                    <strong><i class="fas fa-hospital"></i> Department:</strong> ${appointment.department.name}
                                </p>
                                ${appointment.symptoms ? `<p class="card-text"><strong><i class="fas fa-notes-medical"></i> Reason:</strong> ${appointment.symptoms}</p>` : ''}
                            </div>
                            <div class="col-md-4 text-right">
                                <span class="badge badge-${appointment.status_color} badge-lg mb-2 d-block">
                                    ${appointment.status}
                                </span>
                                <div class="btn-group-vertical" role="group">
                                    ${appointment.can_reschedule ? `<button class="btn btn-outline-warning btn-sm reschedule-button" data-id="${appointment.id}" data-doctor="${appointment.doctor.id}"><i class="fas fa-edit"></i> Reschedule</button>` : ''}
                                    ${appointment.can_cancel ? `<button class="btn btn-outline-danger btn-sm cancel-button" data-id="${appointment.id}"><i class="fas fa-times"></i> Cancel</button>` : ''}
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                appointmentList.appendChild(appointmentCard);
            });

            // Add event listeners for reschedule and cancel buttons
            attachEventListeners();
        }

        function attachEventListeners() {
            // Reschedule buttons
            document.querySelectorAll('.reschedule-button').forEach(button => {
                button.addEventListener('click', function() {
                    currentAppointmentId = this.dataset.id;
                    const doctorId = this.dataset.doctor;
                    openRescheduleModal(doctorId);
                });
            });

            // Cancel buttons
            document.querySelectorAll('.cancel-button').forEach(button => {
                button.addEventListener('click', function() {
                    const appointmentId = this.dataset.id;
                    cancelAppointment(appointmentId);
                });
            });
        }

        function openRescheduleModal(doctorId) {
            // Set minimum date to tomorrow
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('reschedule-date').min = tomorrow.toISOString().split('T')[0];
            
            // Clear previous selections
            document.getElementById('reschedule-date').value = '';
            document.getElementById('reschedule-time').innerHTML = '<option value="">Select a time slot</option>';
            
            $('#rescheduleModal').modal('show');
        }

        function loadTimeSlots(doctorId, date) {
            const timeSelect = document.getElementById('reschedule-time');
            timeSelect.innerHTML = '<option value="">Loading...</option>';
            
            fetch(`/appointments/slots/${doctorId}?date=${date}`)
                .then(response => response.json())
                .then(slots => {
                    timeSelect.innerHTML = '<option value="">Select a time slot</option>';
                    slots.forEach(slot => {
                        timeSelect.innerHTML += `<option value="${slot}">${slot}</option>`;
                    });
                })
                .catch(error => {
                    console.error('Error loading time slots:', error);
                    timeSelect.innerHTML = '<option value="">Error loading slots</option>';
                });
        }

        function cancelAppointment(appointmentId) {
            if (!confirm('Are you sure you want to cancel this appointment?')) {
                return;
            }

            fetch(`/api/appointments/${appointmentId}/cancel`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showAlert('Appointment cancelled successfully!', 'success');
                    loadAppointments();
                } else {
                    showAlert(data.message || 'Error cancelling appointment', 'danger');
                }
            })
            .catch(error => {
                console.error('Error cancelling appointment:', error);
                showAlert('Error cancelling appointment. Please try again.', 'danger');
            });
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
                ${message}
                <button type="button" class="close" data-dismiss="alert">
                    <span>&times;</span>
                </button>
            `;
            
            appointmentList.insertBefore(alertDiv, appointmentList.firstChild);
            
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Event listeners
        refreshButton.addEventListener('click', loadAppointments);
        
        // Date change event for reschedule modal
        document.getElementById('reschedule-date').addEventListener('change', function() {
            const date = this.value;
            if (date && currentAppointmentId) {
                // Get doctor ID from the current appointment (you might need to store this differently)
                const doctorId = document.querySelector(`[data-id="${currentAppointmentId}"]`).dataset.doctor;
                loadTimeSlots(doctorId, date);
            }
        });
        
        // Confirm reschedule
        document.getElementById('confirm-reschedule').addEventListener('click', function() {
            const date = document.getElementById('reschedule-date').value;
            const time = document.getElementById('reschedule-time').value;
            
            if (!date || !time) {
                alert('Please select both date and time.');
                return;
            }
            
            fetch(`/api/appointments/${currentAppointmentId}/reschedule`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ date, time })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    $('#rescheduleModal').modal('hide');
                    showAlert('Appointment rescheduled successfully!', 'success');
                    loadAppointments();
                } else {
                    showAlert(data.message || 'Error rescheduling appointment', 'danger');
                }
            })
            .catch(error => {
                console.error('Error rescheduling appointment:', error);
                showAlert('Error rescheduling appointment. Please try again.', 'danger');
            });
        });

        // Initial load
        loadAppointments();
    });
</script>
@endsection

