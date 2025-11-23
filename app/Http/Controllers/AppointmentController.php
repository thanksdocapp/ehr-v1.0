<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Appointment;
use App\Models\Department;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\SiteSetting;
use App\Models\ThemeSetting;
use App\Models\FrontendTemplate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Services\NotificationService;

class AppointmentController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }
    /**
     * Show the appointment booking form
     */
    public function create()
    {
        $data = [
            'site_settings' => SiteSetting::getSettings(),
            'theme_settings' => ThemeSetting::getActive(),
            'frontend_template' => FrontendTemplate::getActive(),
            'departments' => Department::active()->ordered()->get(),
            'doctors' => Doctor::active()->with('department')->ordered()->get(),
        ];

        return view('appointments.book', $data);
    }

    /**
     * Get doctors by department (AJAX)
     */
    public function getDoctorsByDepartment($departmentId)
    {
        $doctors = Doctor::active()
            ->where('department_id', $departmentId)
            ->ordered()
            ->get(['id', 'first_name', 'last_name', 'title', 'specialization', 'photo', 'consultation_fee', 'experience_years']);

        return response()->json($doctors->map(function ($doctor) {
            return [
                'id' => $doctor->id,
                'name' => $doctor->name, // Uses accessor for frontend compatibility
                'full_name' => $doctor->full_name,
                'specialization' => $doctor->specialization,
                'image' => $doctor->image_url, // Uses accessor for frontend compatibility
                'photo' => $doctor->photo_url, // Backward compatibility
                'consultation_fee' => $doctor->consultation_fee,
                'experience_years' => $doctor->experience_years,
                'experience_text' => $doctor->experience_text,
            ];
        }));
    }

    /**
     * Get available time slots for a doctor on a specific date (AJAX)
     */
    public function getAvailableSlots(Request $request, $doctorId)
    {
        $date = $request->get('date');
        
        if (!$date) {
            return response()->json(['error' => 'Date is required'], 400);
        }

        // Get doctor's availability (you can extend this based on your needs)
        $doctor = Doctor::find($doctorId);
        if (!$doctor) {
            return response()->json(['error' => 'Doctor not found'], 404);
        }

        // Get existing appointments for the doctor on this date
        $existingAppointments = Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['pending', 'confirmed'])
            ->pluck('appointment_time')
            ->toArray();

        // Generate available time slots (9 AM to 5 PM, 30-minute intervals)
        $timeSlots = [];
        $startTime = Carbon::parse($date . ' 09:00:00');
        $endTime = Carbon::parse($date . ' 17:00:00');

        while ($startTime < $endTime) {
            $timeString = $startTime->format('H:i:s');
            
            // Skip if slot is already booked
            if (!in_array($timeString, $existingAppointments)) {
                $timeSlots[] = $startTime->format('g:i A');
            }
            
            $startTime->addMinutes(30);
        }

        return response()->json($timeSlots);
    }

    /**
     * Store the appointment
     */
    public function store(Request $request)
    {
        // Validate the simplified form data from frontend
        $validator = Validator::make($request->all(), [
            'department_id' => 'required|exists:departments,id',
            'doctor_id' => 'required|exists:doctors,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|string',
            'patient_name' => 'required|string|max:255',
            'patient_email' => 'required|email|max:255',
            'patient_phone' => 'required|string|max:20',
            'reason' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
                'message' => 'Please fill in all required fields correctly.'
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Check if appointment slot is still available
            $conflictingAppointment = $this->checkAppointmentConflict(
                $request->doctor_id,
                $request->appointment_date,
                $request->appointment_time
            );

            if ($conflictingAppointment) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'This time slot is no longer available. Please choose another time.'
                ], 422);
            }

            // Handle patient registration (new or returning)
            $patientData = $this->handlePatientRegistration($request);
            
            if (!$patientData['success']) {
                DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => $patientData['message']
                ], 422);
            }

            $patient = $patientData['patient'];

            // Generate unique appointment number
            $appointmentNumber = $this->generateAppointmentNumber();
            
            // Convert appointment time to 24-hour format (HH:MM:SS)
            $appointmentTime = $this->convertTo24HourFormat($request->appointment_time);

            // Ensure patient has department and assigned_doctor set (update if missing)
            // This handles cases where patient was created before appointment or self-registered
            $patientUpdateData = [];
            if (!$patient->department_id && $request->department_id) {
                $patientUpdateData['department_id'] = $request->department_id;
            }
            if (!$patient->assigned_doctor_id && $request->doctor_id) {
                $patientUpdateData['assigned_doctor_id'] = $request->doctor_id;
            }
            // If department still not set but doctor is, get it from doctor
            if (!isset($patientUpdateData['department_id']) && !$patient->department_id && $request->doctor_id) {
                $doctor = Doctor::find($request->doctor_id);
                if ($doctor && $doctor->department_id) {
                    $patientUpdateData['department_id'] = $doctor->department_id;
                }
            }
            if (!empty($patientUpdateData)) {
                $patient->update($patientUpdateData);
                Log::info('Patient department/doctor updated during appointment booking', [
                    'patient_id' => $patient->id,
                    'updates' => $patientUpdateData
                ]);
            }

            // Create appointment record
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $request->doctor_id,
                'department_id' => $request->department_id,
                'appointment_date' => $request->appointment_date,
                'appointment_time' => $appointmentTime,
                'symptoms' => $request->reason,
                'status' => 'pending',
                'is_emergency' => false,
                'is_online' => $request->boolean('is_online', false), // Default to false for in-person appointments
                'appointment_number' => $appointmentNumber,
                'notes' => 'Appointment booked online',
            ]);

            // Load relationships for response
            $appointment->load(['patient', 'doctor', 'department']);

            // Send confirmation email
            $this->sendConfirmationEmail($appointment);
            
            // Send appointment notification
            try {
                $this->notificationService->sendAppointmentNotification($appointment, 'created');
            } catch (\Exception $e) {
                Log::error('Failed to send appointment notification', [
                    'appointment_id' => $appointment->id,
                    'error' => $e->getMessage()
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully!',
                'appointment' => [
                    'id' => $appointment->id,
                    'number' => $appointment->appointment_number,
                    'date' => Carbon::parse($appointment->appointment_date)->format('M d, Y'),
                    'time' => Carbon::parse($appointment->appointment_time)->format('g:i A'),
                    'doctor' => $appointment->doctor->full_name,
                    'department' => $appointment->department->name,
                    'patient' => $appointment->patient->full_name,
                    'patient_type' => $patientData['patient_type'],
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            Log::error('Appointment booking failed', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'request_data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // In development, show the actual error
            $errorMessage = config('app.debug') 
                ? 'Error: ' . $e->getMessage() . ' (Line: ' . $e->getLine() . ')'
                : 'An error occurred while booking your appointment. Please try again.';
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'debug' => config('app.debug') ? [
                    'error' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'line' => $e->getLine()
                ] : null
            ], 500);
        }
    }

    /**
     * Show appointment confirmation
     */
    public function confirmation($appointmentNumber)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'department'])
            ->where('appointment_number', $appointmentNumber)
            ->firstOrFail();

        $data = [
            'site_settings' => SiteSetting::getSettings(),
            'theme_settings' => ThemeSetting::getActive(),
            'frontend_template' => FrontendTemplate::getActive(),
            'appointment' => $appointment,
        ];

        return view('appointments.confirmation', $data);
    }

    /**
     * Check for appointment conflicts
     */
    private function checkAppointmentConflict($doctorId, $date, $time)
    {
        return Appointment::where('doctor_id', $doctorId)
            ->whereDate('appointment_date', $date)
            ->where('appointment_time', $time)
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();
    }

    /**
     * Handle patient registration (new or returning)
     */
    private function handlePatientRegistration(Request $request)
    {
        try {
            // Parse patient name into first and last name
            $nameParts = explode(' ', trim($request->patient_name), 2);
            $firstName = $nameParts[0];
            $lastName = isset($nameParts[1]) ? $nameParts[1] : '';

            // Get doctor and department from request (for new patients)
            $doctorId = $request->doctor_id ?? null;
            $departmentId = $request->department_id ?? null;

            // Check if patient already exists by email
            $existingPatient = Patient::where('email', $request->patient_email)->first();
            
            if ($existingPatient) {
                // Returning patient - update their information and department/doctor if not set
                $this->updateReturningPatient($existingPatient, $firstName, $lastName, $request->patient_phone, $doctorId, $departmentId);
                
                return [
                    'success' => true,
                    'patient' => $existingPatient,
                    'patient_type' => 'returning'
                ];
            } else {
                // New patient - create new record with doctor and department
                $newPatient = $this->createNewPatient($firstName, $lastName, $request->patient_email, $request->patient_phone, $doctorId, $departmentId);
                
                return [
                    'success' => true,
                    'patient' => $newPatient,
                    'patient_type' => 'new'
                ];
            }
        } catch (\Exception $e) {
            Log::error('Patient registration failed', [
                'error' => $e->getMessage(),
                'email' => $request->patient_email,
                'name' => $request->patient_name
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to register patient information. Please try again.'
            ];
        }
    }

    /**
     * Update returning patient information
     * Also update department and assigned_doctor if not set and appointment provides them
     */
    private function updateReturningPatient($patient, $firstName, $lastName, $phone, $doctorId = null, $departmentId = null)
    {
        $updateData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'phone' => $phone,
            'updated_at' => now(),
        ];
        
        // Update department if not set and provided
        if (!$patient->department_id && $departmentId) {
            $updateData['department_id'] = $departmentId;
        } elseif (!$patient->department_id && $doctorId) {
            // If department not provided but doctor is, get department from doctor
            $doctor = Doctor::find($doctorId);
            if ($doctor && $doctor->department_id) {
                $updateData['department_id'] = $doctor->department_id;
            }
        }
        
        // Update assigned_doctor if not set and provided
        if (!$patient->assigned_doctor_id && $doctorId) {
            $updateData['assigned_doctor_id'] = $doctorId;
        }
        
        $patient->update($updateData);
        
        Log::info('Returning patient updated', [
            'patient_id' => $patient->id,
            'email' => $patient->email,
            'name' => $firstName . ' ' . $lastName,
            'department_id' => $updateData['department_id'] ?? $patient->department_id,
            'assigned_doctor_id' => $updateData['assigned_doctor_id'] ?? $patient->assigned_doctor_id
        ]);
    }

    /**
     * Create new patient record
     * Note: This is called from public booking, so we don't have a creating doctor.
     * Department and assigned_doctor_id will be set when the appointment is created.
     */
    private function createNewPatient($firstName, $lastName, $email, $phone, $doctorId = null, $departmentId = null)
    {
        $patientData = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $email,
            'phone' => $phone,
            'date_of_birth' => '1990-01-01', // Default date - can be updated later
            'gender' => 'other', // Default value
            'address' => null,
            'emergency_contact' => $phone, // Default to same phone
            'patient_id' => $this->generatePatientId(),
            'is_active' => true,
        ];
        
        // Set department and assigned_doctor if provided (from appointment booking)
        if ($departmentId) {
            $patientData['department_id'] = $departmentId;
        }
        if ($doctorId) {
            $patientData['assigned_doctor_id'] = $doctorId;
            // If department not set, get it from doctor
            if (!$departmentId) {
                $doctor = Doctor::find($doctorId);
                if ($doctor && $doctor->department_id) {
                    $patientData['department_id'] = $doctor->department_id;
                }
            }
        }
        
        $patient = Patient::create($patientData);
        
        Log::info('New patient created', [
            'patient_id' => $patient->id,
            'email' => $patient->email,
            'name' => $firstName . ' ' . $lastName,
            'department_id' => $patient->department_id ?? null,
            'assigned_doctor_id' => $patient->assigned_doctor_id ?? null
        ]);
        
        return $patient;
    }

    /**
     * Generate unique patient ID
     */
    private function generatePatientId()
    {
        do {
            $patientId = 'PAT' . str_pad(mt_rand(1, 999999), 6, '0', STR_PAD_LEFT);
        } while (Patient::where('patient_id', $patientId)->exists());
        
        return $patientId;
    }

    /**
     * Generate unique appointment number
     */
    private function generateAppointmentNumber()
    {
        do {
            $appointmentNumber = 'APT' . date('Ymd') . str_pad(mt_rand(1, 9999), 4, '0', STR_PAD_LEFT);
        } while (Appointment::where('appointment_number', $appointmentNumber)->exists());
        
        return $appointmentNumber;
    }
    
    /**
     * Convert time from 12-hour format (e.g., "10:00 AM") to 24-hour format (e.g., "10:00:00")
     */
    private function convertTo24HourFormat($time)
    {
        try {
            // If already in 24-hour format (HH:MM or HH:MM:SS), return as is
            if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $time)) {
                // Add seconds if not present
                return strlen($time) === 5 ? $time . ':00' : $time;
            }
            
            // Convert from 12-hour format to 24-hour format
            $parsed = Carbon::parse($time);
            return $parsed->format('H:i:s');
        } catch (\Exception $e) {
            Log::error('Failed to convert time format', [
                'time' => $time,
                'error' => $e->getMessage()
            ]);
            
            // Fallback: try to extract time components manually
            return $this->manualTimeConversion($time);
        }
    }
    
    /**
     * Manual time conversion as fallback
     */
    private function manualTimeConversion($time)
    {
        // Remove spaces and convert to uppercase
        $time = strtoupper(str_replace(' ', '', $time));
        
        // Extract hours, minutes, and AM/PM
        preg_match('/(\d{1,2}):(\d{2})(AM|PM)?/', $time, $matches);
        
        if (empty($matches)) {
            // Default to noon if parsing fails
            return '12:00:00';
        }
        
        $hours = (int)$matches[1];
        $minutes = $matches[2];
        $period = $matches[3] ?? '';
        
        // Convert to 24-hour format
        if ($period === 'PM' && $hours !== 12) {
            $hours += 12;
        } elseif ($period === 'AM' && $hours === 12) {
            $hours = 0;
        }
        
        return sprintf('%02d:%s:00', $hours, $minutes);
    }

    /**
     * Send confirmation email to patient
     */
    private function sendConfirmationEmail($appointment)
    {
        try {
            // For now, just log the email details
            // In a real application, you would use Laravel's Mail facade
            Log::info('Appointment confirmation email', [
                'appointment_id' => $appointment->id,
                'appointment_number' => $appointment->appointment_number,
                'patient_email' => $appointment->patient->email,
                'patient_name' => $appointment->patient->full_name,
                'doctor_name' => $appointment->doctor->full_name,
                'department' => $appointment->department->name,
                'date' => $appointment->appointment_date,
                'time' => $appointment->appointment_time,
            ]);
            
            // TODO: Implement actual email sending
            // Mail::to($appointment->patient->email)->send(new AppointmentConfirmationMail($appointment));
            
        } catch (\Exception $e) {
            Log::error('Failed to send confirmation email', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Update appointment status
     */
    public function updateStatus(Request $request, $appointmentId)
    {
        $status = $request->get('status');
        $validStatuses = ['pending', 'confirmed', 'completed', 'cancelled'];

        if (!in_array($status, $validStatuses)) {
            return response()->json(['error' => 'Invalid status'], 400);
        }

        $appointment = Appointment::with(['patient', 'doctor', 'department'])->find($appointmentId);

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        $oldStatus = $appointment->status;
        $appointment->status = $status;
        $appointment->save();
        
        // Send notification for status change
        if ($oldStatus !== $status) {
            try {
                $this->notificationService->sendAppointmentNotification($appointment, $status);
            } catch (\Exception $e) {
                Log::error('Failed to send appointment status notification', [
                    'appointment_id' => $appointment->id,
                    'old_status' => $oldStatus,
                    'new_status' => $status,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return response()->json(['success' => true, 'status' => $appointment->status], 200);
    }

    /**
     * Reschedule appointment
     */
    public function reschedule(Request $request, $appointmentId)
    {
        $newDate = $request->get('date');
        $newTime = $request->get('time');

        $validator = Validator::make($request->all(), [
            'date' => 'required|date|after:today',
            'time' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $appointment = Appointment::with(['patient', 'doctor', 'department'])->find($appointmentId);

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        $conflictingAppointment = $this->checkAppointmentConflict($appointment->doctor_id, $newDate, $newTime);

        if ($conflictingAppointment) {
            return response()->json(['success' => false, 'message' => 'This time slot is no longer available.'], 422);
        }

        $appointment->appointment_date = $newDate;
        $appointment->appointment_time = $newTime;
        $appointment->status = 'rescheduled';
        $appointment->save();
        
        // Send reschedule notification
        try {
            $this->notificationService->sendAppointmentNotification($appointment, 'updated');
        } catch (\Exception $e) {
            Log::error('Failed to send appointment reschedule notification', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'appointment' => $appointment->only(['appointment_date', 'appointment_time', 'status'])], 200);
    }

    /**
     * Cancel appointment
     */
    public function cancel(Request $request, $appointmentId)
    {
        $appointment = Appointment::with(['patient', 'doctor', 'department'])->find($appointmentId);

        if (!$appointment) {
            return response()->json(['error' => 'Appointment not found'], 404);
        }

        if ($appointment->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Cannot cancel a completed appointment.'], 422);
        }

        $appointment->status = 'cancelled';
        $appointment->save();
        
        // Send cancellation notification
        try {
            $this->notificationService->sendAppointmentNotification($appointment, 'cancelled');
        } catch (\Exception $e) {
            Log::error('Failed to send appointment cancellation notification', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage()
            ]);
        }

        return response()->json(['success' => true, 'status' => 'cancelled'], 200);
    }

    /**
     * Show patient appointments dashboard
     */
    public function dashboard($patientId)
    {
        $patient = Patient::where('patient_id', $patientId)->firstOrFail();
        
        $data = [
            'site_settings' => SiteSetting::getSettings(),
            'theme_settings' => ThemeSetting::getActive(),
            'frontend_template' => FrontendTemplate::getActive(),
            'patient' => $patient,
        ];

        return view('appointments.dashboard', $data);
    }

    /**
     * Get patient appointments (AJAX)
     */
    public function getPatientAppointments(Request $request)
    {
        $patientId = $request->get('patient_id');
        
        if (!$patientId) {
            return response()->json(['error' => 'Patient ID is required'], 400);
        }

        $patient = Patient::where('patient_id', $patientId)->first();
        
        if (!$patient) {
            return response()->json(['error' => 'Patient not found'], 404);
        }

        $appointments = Appointment::with(['doctor', 'department'])
            ->where('patient_id', $patient->id)
            ->whereIn('status', ['pending', 'confirmed', 'rescheduled'])
            ->orderBy('appointment_date', 'asc')
            ->orderBy('appointment_time', 'asc')
            ->get();

        return response()->json($appointments->map(function($appointment) {
            $statusColors = [
                'pending' => 'warning',
                'confirmed' => 'success', 
                'rescheduled' => 'info',
                'completed' => 'primary',
                'cancelled' => 'danger'
            ];

            return [
                'id' => $appointment->id,
                'appointment_number' => $appointment->appointment_number,
                'date' => Carbon::parse($appointment->appointment_date)->format('M d, Y'),
                'time' => Carbon::parse($appointment->appointment_time)->format('g:i A'),
                'status' => ucfirst($appointment->status),
                'status_color' => $statusColors[$appointment->status] ?? 'secondary',
                'doctor' => [
                    'id' => $appointment->doctor->id,
                    'full_name' => $appointment->doctor->full_name,
                    'specialization' => $appointment->doctor->specialization
                ],
                'department' => [
                    'id' => $appointment->department->id,
                    'name' => $appointment->department->name
                ],
                'symptoms' => $appointment->symptoms,
                'can_cancel' => in_array($appointment->status, ['pending', 'confirmed']),
                'can_reschedule' => in_array($appointment->status, ['pending', 'confirmed'])
            ];
        }));
    }

    /**
     * Get patient statistics for admin dashboard
     */
    public function getPatientStats()
    {
        $stats = [
            'total_patients' => Patient::count(),
            'new_patients_today' => Patient::whereDate('created_at', today())->count(),
            'new_patients_this_week' => Patient::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'new_patients_this_month' => Patient::whereMonth('created_at', now()->month)->count(),
            'active_patients' => Patient::where('is_active', true)->count(),
            'patients_with_appointments' => Patient::has('appointments')->count(),
        ];
        
        return response()->json($stats);
    }

    /**
     * Search patients by email or name
     */
    public function searchPatients(Request $request)
    {
        // Support both 'q' and 'query' parameters for backward compatibility
        $query = $request->get('q') ?: $request->get('query');
        
        if (empty($query)) {
            return response()->json([]);
        }
        
        $user = Auth::user();
        $patientQuery = Patient::where(function($q) use ($query) {
                $q->where('email', 'LIKE', '%' . $query . '%')
                  ->orWhere('first_name', 'LIKE', '%' . $query . '%')
                  ->orWhere('last_name', 'LIKE', '%' . $query . '%')
                  ->orWhere('phone', 'LIKE', '%' . $query . '%')
                  ->orWhere('patient_id', 'LIKE', '%' . $query . '%');
            });
        
        // Filter by department for doctors
        if ($user && $user->role === 'doctor') {
            $doctor = \App\Models\Doctor::where('user_id', $user->id)->first();
            if ($doctor) {
                // If doctor has a department, filter by it
                if ($doctor->department_id) {
                    $patientQuery->byDepartment($doctor->department_id);
                }
                // If doctor doesn't have a department but has additional departments
                elseif (method_exists($doctor, 'additionalDepartments') && $doctor->additionalDepartments) {
                    $departmentIds = [$doctor->department_id];
                    if (is_array($doctor->additionalDepartments)) {
                        $departmentIds = array_merge($departmentIds, $doctor->additionalDepartments);
                    }
                    $patientQuery->byDepartments(array_filter($departmentIds));
                }
                // If no department filter, show all patients (doctor can see all)
            }
        }
        
        $patients = $patientQuery->with(['appointments' => function($query) {
                $query->latest()->take(5);
            }])
            ->limit(20)
            ->get();
            
        // Return array format that works for both quick search and other uses
        $patientsArray = $patients->map(function($patient) {
            return [
                'id' => $patient->id,
                'patient_id' => $patient->patient_id ?? null,
                'first_name' => $patient->first_name,
                'last_name' => $patient->last_name,
                'name' => $patient->full_name,
                'email' => $patient->email,
                'phone' => $patient->phone,
                'appointments_count' => $patient->appointments->count(),
                'last_appointment' => $patient->appointments->first() ? $patient->appointments->first()->appointment_date : null,
                'registration_date' => $patient->created_at->format('M d, Y'),
            ];
        })->toArray();
        
        // Return flat array for quick search (when 'q' parameter is used)
        // Return object with 'patients' key for other uses (when 'query' parameter is used)
        if ($request->has('q')) {
            return response()->json($patientsArray);
        }
        
        return response()->json(['patients' => $patientsArray]);
    }
}
