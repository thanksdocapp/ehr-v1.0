<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Department;
use App\Models\Patient;
use App\Models\User;
use App\Models\PasswordResetToken;
use App\Services\HospitalEmailNotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class DoctorsController extends Controller
{
    public function index(Request $request)
    {
        $query = Doctor::with(['departments', 'department']) // Load both relationships for compatibility
            ->withCount(['appointments']);
        
        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('specialization', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        // Apply department filter (support both old and new relationships)
        if ($request->filled('department_id')) {
            $query->byDepartment($request->department_id);
        }
        
        // Apply status filter
        if ($request->filled('status')) {
            $isActive = $request->status === 'active';
            $query->where('is_active', $isActive);
        }
        
        // Experience filter removed - experience_years field no longer used
        
        $doctors = $query->ordered()->paginate(15);
        $departments = Department::active()->ordered()->get();

        return view('admin.doctors.index', compact('doctors', 'departments'));
    }

    public function create()
    {
        $departments = Department::active()->ordered()->get();
        return view('admin.doctors.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'specialization' => 'required|string',
            'department_id' => 'required|exists:departments,id', // Primary department for backward compatibility
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'languages' => 'nullable|array',
            'specialties' => 'nullable|array',
            'email' => 'nullable|email|unique:doctors,email',
            'phone' => 'nullable|string',
            'availability' => 'nullable|array',
            'is_available_online' => 'boolean',
            'is_featured' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->except('photo');
        // Set title to "Dr." by default
        $data['title'] = $request->title ?? 'Dr.';
        $data['slug'] = Str::slug($data['title'] . ' ' . $request->first_name . ' ' . $request->last_name);
        $data['status'] = 'active'; // Set default status
        $data['is_active'] = $request->has('is_active') ? $request->is_active : true; // Set default is_active

        // Handle photo upload
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = time() . '_' . Str::slug($data['slug']) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/doctors', $filename, 'public');
            $data['photo'] = $filename;
        }

        // Handle multiple departments
        $departmentIds = $request->input('department_ids', []);
        $primaryDepartmentId = $request->input('department_id');
        
        // Ensure primary department is in the list
        if (!in_array($primaryDepartmentId, $departmentIds)) {
            $departmentIds[] = $primaryDepartmentId;
        }
        
        $doctor = Doctor::create($data);
        
        // Sync departments to pivot table
        if (!empty($departmentIds)) {
            $syncData = [];
            foreach ($departmentIds as $deptId) {
                $syncData[$deptId] = [
                    'is_primary' => ($deptId == $primaryDepartmentId),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $doctor->departments()->sync($syncData);
        }
        
        // Sync with user model if user_id is provided and user exists
        if (isset($data['user_id']) && $data['user_id']) {
            $user = \App\Models\User::find($data['user_id']);
            if ($user) {
                $userUpdateData = [];
                
                // Sync specialization from doctor to user
                if (isset($data['specialization'])) {
                    $userUpdateData['specialization'] = $data['specialization'];
                }
                
                // Sync email if provided
                if (isset($data['email']) && $data['email'] !== $user->email) {
                    $userUpdateData['email'] = $data['email'];
                }
                
                // Sync phone if provided
                if (isset($data['phone']) && $data['phone'] && $data['phone'] !== $user->phone) {
                    $userUpdateData['phone'] = $data['phone'];
                }
                
                // Sync department if provided
                if (isset($data['department_id']) && $data['department_id'] !== $user->department_id) {
                    $userUpdateData['department_id'] = $data['department_id'];
                }
                
                // Sync name (first_name + last_name to user.name)
                $fullName = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
                if ($fullName && $fullName !== $user->name) {
                    $userUpdateData['name'] = $fullName;
                }
                
                // Update user if there are changes
                if (!empty($userUpdateData)) {
                    $user->update($userUpdateData);
                }
            }
        }

        return redirect()
            ->route('admin.doctors.index')
            ->with('success', 'Doctor created successfully!');
    }

    public function show(Doctor $doctor)
    {
        $doctor->load(['departments', 'department']); // Load both for compatibility
        
        $upcomingAppointments = $doctor->appointments()
            ->with('patient')
            ->where('appointment_date', '>=', today())
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->take(10)
            ->get();

        $todayAppointments = $doctor->appointments()
            ->with('patient')
            ->whereDate('appointment_date', today())
            ->orderBy('appointment_time')
            ->get();

        // Note: Testimonials relationship might not exist, so we'll skip it for now
        $recentTestimonials = collect();

        return view('admin.doctors.show', compact(
            'doctor', 
            'upcomingAppointments', 
            'todayAppointments', 
            'recentTestimonials'
        ));
    }

    public function edit(Doctor $doctor)
    {
        $doctor->load(['departments', 'department']); // Load both for compatibility
        $departments = Department::active()->ordered()->get();
        return view('admin.doctors.edit', compact('doctor', 'departments'));
    }

    public function update(Request $request, Doctor $doctor, HospitalEmailNotificationService $emailService)
    {
        
        $request->validate([
            'title' => 'nullable|string',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'specialization' => 'required|string',
            'department_id' => 'required|exists:departments,id', // Primary department for backward compatibility
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'languages' => 'nullable|array',
            'specialties' => 'nullable|array',
            'email' => 'nullable|email|unique:doctors,email,' . $doctor->id,
            'phone' => 'nullable|string',
            'availability' => 'nullable|array',
            'is_available_online' => 'boolean',
            'is_featured' => 'boolean',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->except('photo');
        // Set title to "Dr." by default if not provided
        $data['title'] = $request->title ?? 'Dr.';
        $data['slug'] = Str::slug($data['title'] . ' ' . $request->first_name . ' ' . $request->last_name);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo
            if ($doctor->photo) {
                Storage::disk('public')->delete('uploads/doctors/' . $doctor->photo);
            }

            $file = $request->file('photo');
            $filename = time() . '_' . Str::slug($data['slug']) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/doctors', $filename, 'public');
            $data['photo'] = $filename;
        }

        // Store original availability for comparison
        $oldAvailability = $doctor->availability;
        $oldPhone = $doctor->phone;
        $oldDepartmentId = $doctor->department_id;
        
        // Handle multiple departments
        $departmentIds = $request->input('department_ids', []);
        $primaryDepartmentId = $request->input('department_id');
        
        // Ensure primary department is in the list
        if (!in_array($primaryDepartmentId, $departmentIds)) {
            $departmentIds[] = $primaryDepartmentId;
        }
        
        $doctor->update($data);
        
        // Sync departments to pivot table
        if (!empty($departmentIds)) {
            $syncData = [];
            foreach ($departmentIds as $deptId) {
                $syncData[$deptId] = [
                    'is_primary' => ($deptId == $primaryDepartmentId),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $doctor->departments()->sync($syncData);
        }
        
        // Check if critical information changed and notify patients
        $this->handleDoctorUpdateNotifications($doctor, $oldAvailability, $oldPhone, $oldDepartmentId, $emailService);
        
        // Sync with user model if user exists
        if ($doctor->user_id && $doctor->user) {
            $userUpdateData = [];
            
            // Sync specialization from doctor to user
            if (isset($data['specialization'])) {
                $userUpdateData['specialization'] = $data['specialization'];
            }
            
            // Sync email if changed
            if (isset($data['email']) && $doctor->email !== $doctor->user->email) {
                $userUpdateData['email'] = $data['email'];
            }
            
            // Sync phone if changed
            if (isset($data['phone']) && $doctor->phone !== $doctor->user->phone) {
                $userUpdateData['phone'] = $data['phone'];
            }
            
            // Sync department if changed
            if (isset($data['department_id']) && $doctor->department_id !== $doctor->user->department_id) {
                $userUpdateData['department_id'] = $data['department_id'];
            }
            
            // Sync name if changed (first_name + last_name to user.name)
            if (isset($data['first_name']) || isset($data['last_name'])) {
                $fullName = trim(($data['first_name'] ?? $doctor->first_name) . ' ' . ($data['last_name'] ?? $doctor->last_name));
                if ($fullName && $fullName !== $doctor->user->name) {
                    $userUpdateData['name'] = $fullName;
                }
            }
            
            // Update user if there are changes
            if (!empty($userUpdateData)) {
                $doctor->user->update($userUpdateData);
            }
        }

        return redirect()
            ->route('admin.doctors.index')
            ->with('success', 'Doctor updated successfully! Notifications have been sent for any schedule changes.');
    }

    public function destroy(Doctor $doctor)
    {
        // Check if doctor has upcoming appointments
        $upcomingAppointments = $doctor->appointments()
            ->where('appointment_date', '>=', today())
            ->where('status', '!=', 'cancelled')
            ->count();

        if ($upcomingAppointments > 0) {
            return redirect()
                ->route('admin.doctors.index')
                ->with('error', 'Cannot delete doctor with upcoming appointments!');
        }

        // Delete photo if exists
        if ($doctor->photo) {
            Storage::disk('public')->delete('uploads/doctors/' . $doctor->photo);
        }

        $doctor->delete();

        return redirect()
            ->route('admin.doctors.index')
            ->with('success', 'Doctor deleted successfully!');
    }

    public function toggleStatus(Doctor $doctor, HospitalEmailNotificationService $emailService)
    {
        $wasActive = $doctor->is_active;
        $doctor->update(['is_active' => !$doctor->is_active]);
        
        // Notify patients if doctor becomes unavailable
        if ($wasActive && !$doctor->is_active) {
            $this->handleDoctorUnavailableNotifications($doctor, $emailService);
        }

        return response()->json([
            'success' => true,
            'message' => 'Doctor status updated successfully!',
            'is_active' => $doctor->is_active
        ]);
    }

    public function toggleFeatured(Doctor $doctor)
    {
        $doctor->update(['is_featured' => !$doctor->is_featured]);

        return response()->json([
            'success' => true,
            'message' => 'Doctor featured status updated successfully!',
            'is_featured' => $doctor->is_featured
        ]);
    }

    public function updateAvailability(Request $request, Doctor $doctor, HospitalEmailNotificationService $emailService)
    {
        $request->validate([
            'availability' => 'required|array',
            'availability.*.available' => 'required|boolean',
            'availability.*.times' => 'nullable|array'
        ]);
        
        $oldAvailability = $doctor->availability;
        $doctor->update(['availability' => $request->availability]);
        
        // Send notifications to patients about availability changes
        $this->handleAvailabilityChangeNotifications($doctor, $oldAvailability, $emailService);

        return response()->json([
            'success' => true,
            'message' => 'Doctor availability updated successfully! Notifications have been sent to affected patients.'
        ]);
    }

    public function schedule(Doctor $doctor)
    {
        $appointments = $doctor->appointments()
            ->with('patient')
            ->whereBetween('appointment_date', [
                now()->startOfWeek(),
                now()->endOfWeek()
            ])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        return view('admin.doctors.schedule', compact('doctor', 'appointments'));
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'doctor_ids' => 'required|array',
            'doctor_ids.*' => 'exists:doctors,id'
        ]);

        $doctorIds = $request->doctor_ids;
        $deletedCount = 0;
        $errors = [];

        foreach ($doctorIds as $doctorId) {
            $doctor = Doctor::find($doctorId);
            
            if (!$doctor) {
                $errors[] = "Doctor with ID {$doctorId} not found.";
                continue;
            }

            // Check if doctor has upcoming appointments
            $upcomingAppointments = $doctor->appointments()
                ->where('appointment_date', '>=', today())
                ->where('status', '!=', 'cancelled')
                ->count();

            if ($upcomingAppointments > 0) {
                $errors[] = "Cannot delete Dr. {$doctor->first_name} {$doctor->last_name} - has {$upcomingAppointments} upcoming appointments.";
                continue;
            }

            // Delete photo if exists
            if ($doctor->photo) {
                Storage::disk('public')->delete('uploads/doctors/' . $doctor->photo);
            }

            $doctor->delete();
            $deletedCount++;
        }

        if ($deletedCount > 0) {
            $message = "Successfully deleted {$deletedCount} doctor(s).";
            if (!empty($errors)) {
                $message .= " However, some doctors could not be deleted: " . implode(', ', $errors);
            }
            return redirect()->route('admin.doctors.index')->with('success', $message);
        } else {
            return redirect()->route('admin.doctors.index')->with('error', 'No doctors were deleted. ' . implode(' ', $errors));
        }
    }

    /**
     * Handle notifications for doctor profile updates
     */
    private function handleDoctorUpdateNotifications(
        Doctor $doctor,
        $oldAvailability,
        $oldPhone,
        $oldDepartmentId,
        HospitalEmailNotificationService $emailService
    ) {
        try {
            // Check if availability changed
            if ($oldAvailability != $doctor->availability) {
                $this->handleAvailabilityChangeNotifications($doctor, $oldAvailability, $emailService);
            }
            
            // Room number removed - no longer tracking room number changes
            
            // Check if phone changed
            if ($oldPhone != $doctor->phone) {
                $patientsWithAppointments = $this->getPatientsWithUpcomingAppointments($doctor);
                foreach ($patientsWithAppointments as $patient) {
                    $emailService->sendDoctorContactUpdateNotification(
                        $patient->email,
                        $patient->first_name,
                        $doctor->first_name . ' ' . $doctor->last_name,
                        $doctor->phone
                    );
                }
            }
            
            // Check if department changed
            if ($oldDepartmentId != $doctor->department_id) {
                $patientsWithAppointments = $this->getPatientsWithUpcomingAppointments($doctor);
                foreach ($patientsWithAppointments as $patient) {
                    $emailService->sendDoctorDepartmentChangeNotification(
                        $patient->email,
                        $patient->first_name,
                        $doctor->first_name . ' ' . $doctor->last_name,
                        $doctor->department->name
                    );
                }
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to send doctor update notifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle notifications when doctor becomes unavailable
     */
    private function handleDoctorUnavailableNotifications(
        Doctor $doctor,
        HospitalEmailNotificationService $emailService
    ) {
        try {
            // Get patients with upcoming appointments
            $patientsWithAppointments = $this->getPatientsWithUpcomingAppointments($doctor);
            
            foreach ($patientsWithAppointments as $patient) {
                $emailService->sendDoctorUnavailableNotification(
                    $patient->email,
                    $patient->first_name,
                    $doctor->first_name . ' ' . $doctor->last_name
                );
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to send doctor unavailable notifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle notifications for availability changes
     */
    private function handleAvailabilityChangeNotifications(
        Doctor $doctor,
        $oldAvailability,
        HospitalEmailNotificationService $emailService
    ) {
        try {
            // Get patients with upcoming appointments
            $patientsWithAppointments = $this->getPatientsWithUpcomingAppointments($doctor);
            
            foreach ($patientsWithAppointments as $patient) {
                $emailService->sendDoctorScheduleUpdateNotification(
                    $patient->email,
                    $patient->first_name,
                    $doctor->first_name . ' ' . $doctor->last_name,
                    $doctor->availability
                );
            }
            
        } catch (\Exception $e) {
            \Log::error('Failed to send availability change notifications: ' . $e->getMessage());
        }
    }
    
    /**
     * Get patients with upcoming appointments for a doctor
     */
    private function getPatientsWithUpcomingAppointments(Doctor $doctor)
    {
        return Patient::whereHas('appointments', function ($query) use ($doctor) {
            $query->where('doctor_id', $doctor->id)
                  ->where('appointment_date', '>=', today())
                  ->where('status', '!=', 'cancelled');
        })->get();
    }

    /**
     * Export doctors to CSV
     */
    public function exportCsv(Request $request)
    {
        try {
            $query = Doctor::with(['departments', 'department', 'user']);
            
            // Apply same filters as index method
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('specialization', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('phone', 'like', "%{$search}%");
                });
            }
            
            if ($request->filled('department_id')) {
                $query->byDepartment($request->department_id);
            }
            
            if ($request->filled('status')) {
                $isActive = $request->status === 'active';
                $query->where('is_active', $isActive);
            }
            
            if ($request->filled('experience')) {
                $experience = $request->experience;
                switch ($experience) {
                    case '0-5':
                        $query->where('experience_years', '<=', 5);
                        break;
                    case '6-10':
                        $query->whereBetween('experience_years', [6, 10]);
                        break;
                    case '11-20':
                        $query->whereBetween('experience_years', [11, 20]);
                        break;
                    case '20+':
                        $query->where('experience_years', '>', 20);
                        break;
                }
            }

            $doctors = $query->ordered()->get();

            $filename = 'doctors_' . now()->format('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
                'Expires' => '0',
            ];

            $callback = function() use ($doctors) {
                $file = fopen('php://output', 'w');
                
                // Add BOM for UTF-8
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // CSV Headers
                fputcsv($file, [
                    'Title',
                    'First Name',
                    'Last Name',
                    'Full Name',
                    'Email',
                    'Phone',
                    'Employee ID',
                    'Specialization',
                    'Specialties',
                    'Languages',
                    'Primary Clinic',
                    'Additional Clinics',
                    'Online Available',
                    'Is Featured',
                    'Status',
                    'Registration Date',
                    'Last Updated'
                ]);

                // CSV Data
                foreach ($doctors as $doctor) {
                    $primaryDept = $doctor->primaryDepartment();
                    $primaryDeptName = $primaryDept ? $primaryDept->name : ($doctor->department ? $doctor->department->name : '');
                    $additionalDepts = $doctor->departments->filter(function($dept) use ($primaryDept) {
                        return $primaryDept ? $dept->id !== $primaryDept->id : true;
                    })->pluck('name')->join(', ');
                    $specialties = is_array($doctor->specialties) ? implode(', ', $doctor->specialties) : ($doctor->specialties ?? '');
                    $languages = is_array($doctor->languages) ? implode(', ', $doctor->languages) : ($doctor->languages ?? '');
                    $employeeId = $doctor->user ? ($doctor->user->employee_id ?? '') : '';
                    
                    fputcsv($file, [
                        $doctor->title ?? 'Dr.',
                        $doctor->first_name,
                        $doctor->last_name,
                        $doctor->full_name,
                        $doctor->email ?? '',
                        $doctor->phone ?? '',
                        $employeeId,
                        $doctor->specialization,
                        $specialties,
                        $languages,
                        $primaryDeptName,
                        $additionalDepts,
                        $doctor->is_available_online ? 'Yes' : 'No',
                        $doctor->is_featured ? 'Yes' : 'No',
                        $doctor->is_active ? 'Active' : 'Inactive',
                        $doctor->created_at->format('Y-m-d H:i:s'),
                        $doctor->updated_at->format('Y-m-d H:i:s')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            \Log::error('Doctors CSV Export Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to export doctors: ' . $e->getMessage());
        }
    }

    /**
     * Show CSV import form
     */
    public function showImport()
    {
        $departments = Department::active()->ordered()->get();
        return view('admin.doctors.import', compact('departments'));
    }

    /**
     * Import doctors from CSV
     */
    public function importCsv(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
            'import_mode' => 'required|in:insert,update,upsert,skip',
            'create_user_account' => 'nullable|boolean',
            'skip_errors' => 'nullable|boolean'
        ]);

        try {
            $file = $request->file('csv_file');
            $importMode = $request->import_mode;
            $createUserAccount = $request->boolean('create_user_account');
            $skipErrors = $request->boolean('skip_errors');
            
            $handle = fopen($file->getRealPath(), 'r');
            
            if ($handle === false) {
                throw new \Exception('Could not open CSV file');
            }

            // Skip BOM if present
            $bom = fread($handle, 3);
            if ($bom !== chr(0xEF).chr(0xBB).chr(0xBF)) {
                rewind($handle);
            }

            // Read headers
            $headers = fgetcsv($handle);
            if ($headers === false) {
                throw new \Exception('CSV file is empty or invalid');
            }

            // Normalize headers (trim and lowercase)
            $headers = array_map(function($header) {
                return strtolower(trim($header));
            }, $headers);

            // Map headers to database columns
            $headerMap = [
                'title' => 'title',
                'first name' => 'first_name',
                'last name' => 'last_name',
                'email' => 'email',
                'phone' => 'phone',
                'employee id' => 'employee_id',
                'specialization' => 'specialization',
                'specialties' => 'specialties',
                'languages' => 'languages',
                'primary clinic' => 'primary_department',
                'additional clinics' => 'additional_departments',
                'online available' => 'is_available_online',
                'is featured' => 'is_featured',
                'status' => 'status',
            ];

            $stats = [
                'total' => 0,
                'created' => 0,
                'updated' => 0,
                'skipped' => 0,
                'errors' => []
            ];

            DB::beginTransaction();

            $rowNumber = 1;
            while (($row = fgetcsv($handle)) !== false) {
                $rowNumber++;
                $stats['total']++;

                try {
                    $data = [];
                    foreach ($headers as $index => $header) {
                        $columnName = $headerMap[$header] ?? null;
                        if ($columnName && isset($row[$index])) {
                            $data[$columnName] = trim($row[$index]);
                        }
                    }

                    // Validate required fields
                    if (empty($data['first_name']) || empty($data['last_name']) || empty($data['email'])) {
                        throw new \Exception("Row {$rowNumber}: Missing required fields (first_name, last_name, email)");
                    }

                    if (empty($data['specialization'])) {
                        throw new \Exception("Row {$rowNumber}: Missing required field (specialization)");
                    }

                    // Handle department assignment
                    $primaryDeptId = null;
                    $additionalDeptIds = [];
                    $departmentErrors = [];
                    
                    if (!empty($data['primary_department'])) {
                        $primaryDept = Department::where('name', trim($data['primary_department']))->first();
                        if ($primaryDept) {
                            $primaryDeptId = $primaryDept->id;
                            $data['department_id'] = $primaryDeptId; // For backward compatibility
                        } else {
                            $departmentErrors[] = "Primary Clinic '{$data['primary_department']}' not found";
                        }
                        unset($data['primary_department']);
                    }
                    
                    if (!empty($data['additional_departments'])) {
                        $deptNames = array_filter(array_map('trim', explode(',', $data['additional_departments'])));
                        foreach ($deptNames as $deptName) {
                            $deptName = trim($deptName);
                            if (empty($deptName)) continue;
                            $dept = Department::where('name', $deptName)->first();
                            if ($dept) {
                                $additionalDeptIds[] = $dept->id;
                            } else {
                                $departmentErrors[] = "Additional Clinic '{$deptName}' not found";
                            }
                        }
                        unset($data['additional_departments']);
                    }
                    
                    // If primary department was specified but not found, throw error (unless skip_errors is enabled)
                    if (empty($primaryDeptId) && !empty($departmentErrors) && !$skipErrors) {
                        throw new \Exception("Row {$rowNumber}: " . implode(', ', $departmentErrors));
                    }
                    
                    // Log department errors even if skip_errors is enabled
                    if (!empty($departmentErrors) && $skipErrors) {
                        foreach ($departmentErrors as $deptError) {
                            $stats['errors'][] = "Row {$rowNumber}: {$deptError}";
                        }
                    }

                    // Handle specialties (ensure it's an array for JSON storage)
                    if (!empty($data['specialties'])) {
                        if (is_string($data['specialties'])) {
                            $specialties = array_filter(array_map('trim', explode(',', $data['specialties'])));
                            $data['specialties'] = !empty($specialties) ? $specialties : null;
                        } elseif (is_array($data['specialties'])) {
                            $data['specialties'] = array_filter(array_map('trim', $data['specialties']));
                            $data['specialties'] = !empty($data['specialties']) ? $data['specialties'] : null;
                        } else {
                            $data['specialties'] = null;
                        }
                    } else {
                        $data['specialties'] = null;
                    }

                    // Handle languages (ensure it's an array for JSON storage)
                    if (!empty($data['languages'])) {
                        if (is_string($data['languages'])) {
                            $languages = array_filter(array_map('trim', explode(',', $data['languages'])));
                            $data['languages'] = !empty($languages) ? $languages : null;
                        } elseif (is_array($data['languages'])) {
                            $data['languages'] = array_filter(array_map('trim', $data['languages']));
                            $data['languages'] = !empty($data['languages']) ? $data['languages'] : null;
                        } else {
                            $data['languages'] = null;
                        }
                    } else {
                        $data['languages'] = null;
                    }

                    // Handle boolean fields
                    $data['is_available_online'] = isset($data['is_available_online']) 
                        ? (strtolower($data['is_available_online']) === 'yes' || $data['is_available_online'] === '1')
                        : false;
                    $data['is_featured'] = isset($data['is_featured']) 
                        ? (strtolower($data['is_featured']) === 'yes' || $data['is_featured'] === '1')
                        : false;

                    // Handle status
                    $data['is_active'] = true;
                    if (isset($data['status'])) {
                        $data['is_active'] = strtolower($data['status']) === 'active';
                        unset($data['status']);
                    }

                    // Set title to Dr. by default
                    $data['title'] = $data['title'] ?? 'Dr.';

                    // Find existing doctor
                    $existingDoctor = Doctor::where('email', $data['email'])->first();

                    // Handle user account creation
                    $user = null;
                    if ($createUserAccount || $existingDoctor) {
                        $user = User::where('email', $data['email'])->first();
                        
                        if (!$user && $createUserAccount) {
                            // Create user account
                            $user = User::create([
                                'name' => trim($data['first_name'] . ' ' . $data['last_name']),
                                'first_name' => $data['first_name'],
                                'last_name' => $data['last_name'],
                                'email' => $data['email'],
                                'phone' => $data['phone'] ?? null,
                                'password' => Hash::make('password123'), // Default password
                                'role' => 'doctor',
                                'is_active' => $data['is_active'],
                                'employee_id' => $data['employee_id'] ?? null,
                            ]);
                        } elseif ($user) {
                            $user->update([
                                'name' => trim($data['first_name'] . ' ' . $data['last_name']),
                                'first_name' => $data['first_name'],
                                'last_name' => $data['last_name'],
                                'phone' => $data['phone'] ?? $user->phone,
                                'is_active' => $data['is_active'],
                            ]);
                        }
                    }

                    // Clean data array - remove fields that shouldn't be saved directly
                    $dataToSave = array_intersect_key($data, array_flip([
                        'title', 'first_name', 'last_name', 'specialization', 'department_id',
                        'email', 'phone', 'employee_id', 'specialties', 'languages',
                        'is_available_online', 'is_featured', 'is_active', 'user_id'
                    ]));

                    // Handle import modes
                    if ($existingDoctor) {
                        if ($importMode === 'insert' || $importMode === 'skip') {
                            $stats['skipped']++;
                            continue;
                        }
                        
                        if ($importMode === 'update' || $importMode === 'upsert') {
                            // Update existing
                            if ($user) {
                                $dataToSave['user_id'] = $user->id;
                            }
                            $existingDoctor->update($dataToSave);
                            $doctor = $existingDoctor;
                            $stats['updated']++;
                        }
                    } else {
                        if ($importMode === 'update') {
                            $stats['skipped']++;
                            continue;
                        }
                        
                        // Generate slug
                        $dataToSave['slug'] = Str::slug($dataToSave['title'] . ' ' . $dataToSave['first_name'] . ' ' . $dataToSave['last_name'] . ' ' . time());
                        
                        // Set default status
                        $dataToSave['status'] = 'active';
                        
                        // Create new
                        if ($user) {
                            $dataToSave['user_id'] = $user->id;
                        }
                        $doctor = Doctor::create($dataToSave);
                        $stats['created']++;
                    }

                    // Sync departments (ensure at least primary department is set)
                    $deptIds = [];
                    if ($primaryDeptId) {
                        $deptIds[] = $primaryDeptId;
                    }
                    // Add additional departments, avoiding duplicates
                    foreach ($additionalDeptIds as $deptId) {
                        if (!in_array($deptId, $deptIds)) {
                            $deptIds[] = $deptId;
                        }
                    }
                    
                    // Sync departments with primary flag
                    if (!empty($deptIds)) {
                        $syncData = [];
                        foreach ($deptIds as $deptId) {
                            $syncData[$deptId] = [
                                'is_primary' => ($primaryDeptId && $deptId == $primaryDeptId),
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }
                        $doctor->departments()->sync($syncData);
                        
                        // Also update department_id for backward compatibility
                        if ($primaryDeptId) {
                            $doctor->department_id = $primaryDeptId;
                            $doctor->save();
                        }
                    } elseif ($primaryDeptId) {
                        // If only primary department is set but sync wasn't called above
                        $doctor->departments()->sync([$primaryDeptId => [
                            'is_primary' => true,
                            'created_at' => now(),
                            'updated_at' => now()
                        ]]);
                        $doctor->department_id = $primaryDeptId;
                        $doctor->save();
                    }

                } catch (\Exception $e) {
                    $errorMsg = "Row {$rowNumber}: " . $e->getMessage();
                    $stats['errors'][] = $errorMsg;
                    
                    if (!$skipErrors) {
                        DB::rollBack();
                        fclose($handle);
                        return redirect()->back()
                            ->with('error', $errorMsg)
                            ->withInput();
                    }
                }
            }

            fclose($handle);
            DB::commit();

            $message = "Import completed! Created: {$stats['created']}, Updated: {$stats['updated']}, Skipped: {$stats['skipped']}";
            if (!empty($stats['errors'])) {
                $message .= ". Errors: " . count($stats['errors']);
            }

            return redirect()->route('admin.doctors.index')
                ->with('success', $message)
                ->with('import_stats', $stats);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Doctors CSV Import Error: ' . $e->getMessage(), [
                'exception' => $e,
                'request' => $request->all()
            ]);
            
            return redirect()->back()
                ->with('error', 'Failed to import doctors: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Reset doctor's password (via user account)
     */
    public function resetPassword(Request $request, Doctor $doctor)
    {
        if (!$doctor->user_id || !$doctor->user) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor does not have a user account linked.'
            ], 400);
        }

        $user = $doctor->user;

        // Validate request
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'notify_via' => 'required|in:email,sms,both',
            'force_change' => 'boolean',
        ]);

        try {
            DB::beginTransaction();

            // Create password reset token using the PasswordResetToken model
            $resetToken = PasswordResetToken::createForUser(
                $user->id,
                auth()->id(),
                $validated['reason'],
                24 // Valid for 24 hours
            );
            
            $token = $resetToken->token;

            // Mark user for password change on next login if required
            if ($validated['force_change'] ?? true) {
                $user->update([
                    'password_change_required' => true
                ]);
            }

            // Invalidate all active sessions for this user if required
            if ($resetToken->invalidate_sessions) {
                try {
                    // Check if sessions table exists
                    if (Schema::hasTable('sessions')) {
                        DB::table('sessions')
                            ->where('user_id', $user->id)
                            ->delete();
                    }
                } catch (\Exception $e) {
                    // Log but don't fail if session invalidation fails
                    \Log::warning('Could not invalidate sessions: ' . $e->getMessage());
                }
            }

            // Log the action
            if (class_exists(\App\Models\UserActivity::class)) {
                try {
                    \App\Models\UserActivity::create([
                        'user_id' => auth()->id(),
                        'action' => 'password_reset_admin',
                        'model_type' => 'User',
                        'model_id' => $user->id,
                        'description' => "Admin reset password for doctor: {$doctor->full_name} (User ID: {$user->id}). Reason: {$validated['reason']}",
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'severity' => 'high',
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Could not log user activity: ' . $e->getMessage());
                }
            }

            // Generate reset link
            $resetLink = url('/password/reset/' . $token . '?email=' . urlencode($user->email));

            // Send notification via email
            if (in_array($validated['notify_via'], ['email', 'both'])) {
                try {
                    $emailService = app(\App\Services\EmailNotificationService::class);
                    $emailLog = $emailService->sendTemplateEmail(
                        'password_reset',
                        [$user->email => $user->name],
                        [
                            'name' => $user->name,
                            'reset_link' => $resetLink,
                            'hospital_name' => config('app.name'),
                        ],
                        [
                            'email_type' => 'password_reset',
                            'event' => 'password.reset.requested',
                            'metadata' => [
                                'doctor_id' => $doctor->id,
                                'user_id' => $user->id,
                                'reset_token_id' => $resetToken->id ?? null,
                            ]
                        ]
                    );
                    
                    if ($emailLog) {
                        \Log::info('Password reset email logged', [
                            'email_log_id' => $emailLog->id,
                            'doctor_id' => $doctor->id,
                            'user_id' => $user->id,
                            'email' => $user->email
                        ]);
                    } else {
                        \Log::warning('Password reset email sent but not logged', [
                            'doctor_id' => $doctor->id,
                            'user_id' => $user->id,
                            'email' => $user->email
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send password reset email: ' . $e->getMessage(), [
                        'doctor_id' => $doctor->id,
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'trace' => $e->getTraceAsString()
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Password reset link has been sent to the doctor.',
                'reset_link' => config('app.debug') ? $resetLink : null,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Failed to reset doctor password: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to reset password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Resend login credentials to doctor (via user account)
     */
    public function resendCredentials(Request $request, Doctor $doctor)
    {
        if (!$doctor->user_id || !$doctor->user) {
            return response()->json([
                'success' => false,
                'message' => 'Doctor does not have a user account linked.'
            ], 400);
        }

        $user = $doctor->user;

        // Validate
        $validated = $request->validate([
            'notify_via' => 'required|in:email,sms,both',
        ]);

        // Check account status
        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot resend credentials for inactive account.'
            ], 400);
        }

        try {
            // Create password reset token for initial setup using the PasswordResetToken model
            $resetToken = PasswordResetToken::createForUser(
                $user->id,
                auth()->id(),
                'Credentials resend requested by admin',
                72 // Valid for 72 hours for initial setup
            );
            
            $token = $resetToken->token;

            $resetLink = url('/password/reset/' . $token . '?email=' . urlencode($user->email));
            $portalLink = url('/login');

            // Log the action
            if (class_exists(\App\Models\UserActivity::class)) {
                try {
                    \App\Models\UserActivity::create([
                        'user_id' => auth()->id(),
                        'action' => 'resend_credentials',
                        'model_type' => 'User',
                        'model_id' => $user->id,
                        'description' => "Admin resent credentials to doctor: {$doctor->full_name} (User ID: {$user->id})",
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'severity' => 'medium',
                    ]);
                } catch (\Exception $e) {
                    \Log::warning('Could not log user activity: ' . $e->getMessage());
                }
            }

            // Send credentials notification via email
            if (in_array($validated['notify_via'], ['email', 'both'])) {
                try {
                    // Configure SMTP settings from database
                    $settings = \App\Models\SiteSetting::getSettings();
                    if (isset($settings['smtp_host']) && $settings['smtp_host']) {
                        \Illuminate\Support\Facades\Config::set('mail.default', 'smtp');
                        \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.host', $settings['smtp_host']);
                        \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.port', $settings['smtp_port'] ?? 587);
                        \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.username', $settings['smtp_username'] ?? '');
                        \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.password', $settings['smtp_password'] ?? '');
                        $encryption = $settings['smtp_encryption'] ?? 'tls';
                        \Illuminate\Support\Facades\Config::set('mail.mailers.smtp.encryption', $encryption === 'none' ? null : $encryption);
                        if (isset($settings['from_email']) && $settings['from_email']) {
                            \Illuminate\Support\Facades\Config::set('mail.from.address', $settings['from_email']);
                            \Illuminate\Support\Facades\Config::set('mail.from.name', $settings['from_name'] ?? $settings['hospital_name'] ?? config('app.name'));
                        }
                    }
                    
                    // Force synchronous sending
                    $originalQueueConnection = config('queue.default');
                    \Illuminate\Support\Facades\Config::set('queue.default', 'sync');
                    
                    try {
                        // Send email using Mail facade directly
                        \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($user, $doctor, $resetLink, $portalLink) {
                        $emailBody = "
                            <p>Hello {$user->name},</p>
                            <p>Your login credentials for {$doctor->full_name} have been reset.</p>
                            <p><strong>Email:</strong> {$user->email}</p>
                            <p>To set your password, please click the link below:</p>
                            <p><a href=\"{$resetLink}\" style=\"background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block;\">Set Password</a></p>
                            <p>Or copy and paste this URL into your browser: {$resetLink}</p>
                            <p>This link will expire in 72 hours.</p>
                            <p>Login portal: <a href=\"{$portalLink}\">{$portalLink}</a></p>
                            <p>Best regards,<br>" . config('app.name') . " Team</p>
                        ";

                        $message->to($user->email, $user->name)
                                ->subject('Your Login Credentials - ' . config('app.name'))
                                ->html($emailBody);
                        });
                    } catch (\Symfony\Component\Mailer\Exception\TransportExceptionInterface $e) {
                        \Log::error('SMTP connection error when sending credentials email: ' . $e->getMessage());
                        throw new \Exception('SMTP connection failed. Please check SMTP settings in Admin > Settings > Email Configuration.');
                    } catch (\Exception $e) {
                        \Log::error('Failed to send credentials email: ' . $e->getMessage());
                        throw $e;
                    } finally {
                        \Illuminate\Support\Facades\Config::set('queue.default', $originalQueueConnection);
                    }
                } catch (\Exception $e) {
                    \Log::error('Failed to send credentials email: ' . $e->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Login credentials have been sent to the doctor.',
                'reset_link' => config('app.debug') ? $resetLink : null,
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to resend doctor credentials: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend credentials: ' . $e->getMessage()
            ], 500);
        }
    }
}
