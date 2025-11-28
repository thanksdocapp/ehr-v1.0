<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DepartmentsController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::withCount(['doctors', 'appointments']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('head_of_department', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Emergency filter
        if ($request->filled('emergency')) {
            $query->where('is_emergency', $request->emergency === 'yes');
        }

        // Location filter
        if ($request->filled('location')) {
            $query->where('location', 'like', "%{$request->location}%");
        }

        // Sort functionality
        $sortBy = $request->get('sort', 'name');
        switch ($sortBy) {
            case 'doctors':
                $query->orderBy('doctors_count', 'desc');
                break;
            case 'appointments':
                $query->orderBy('appointments_count', 'desc');
                break;
            case 'recent':
                $query->orderBy('created_at', 'desc');
                break;
            case 'name':
            default:
                $query->ordered();
                break;
        }

        $departments = $query->paginate(15)->withQueryString();

        // Manually calculate patient counts for each department
        foreach ($departments as $department) {
            $patientCount = \App\Models\Patient::where(function($q) use ($department) {
                $q->whereHas('departments', function($q2) use ($department) {
                    $q2->where('departments.id', $department->id);
                })->orWhere('department_id', $department->id);
            })->count();
            
            $department->patients_count = $patientCount;
        }

        // Get unique locations for filter dropdown
        $locations = Department::whereNotNull('location')
            ->distinct()
            ->pluck('location')
            ->filter()
            ->sort()
            ->values();

        // Calculate total unique doctors across ALL departments (not filtered)
        // Count unique doctors from the many-to-many relationship
        $totalDoctors = \App\Models\Doctor::whereHas('departments')->distinct()->count();
        // Also include doctors with legacy department_id who don't have pivot relationships
        $totalDoctorsLegacy = \App\Models\Doctor::whereNotNull('department_id')
            ->whereDoesntHave('departments')
            ->distinct()
            ->count();
        $totalDoctors = $totalDoctors + $totalDoctorsLegacy;

        // Calculate total appointments across ALL departments (not filtered)
        $totalAppointments = \App\Models\Appointment::count();

        return view('admin.departments.index', compact('departments', 'locations', 'totalDoctors', 'totalAppointments'));
    }

    public function create()
    {
        return view('admin.departments.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive',
            'head_of_department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'services' => 'nullable|array',
            'services.*' => 'nullable|string|max:255',
            'operating_hours' => 'nullable|string|max:255',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'location' => 'nullable|string',
            'working_hours' => 'nullable|string',
            'is_emergency' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->except(['image', 'status']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = ($request->status === 'active');
        
        // Filter out empty services
        if (isset($data['services']) && is_array($data['services'])) {
            $data['services'] = array_filter($data['services'], function($service) {
                return !empty(trim($service));
            });
            // Reset array keys to avoid sparse arrays
            $data['services'] = array_values($data['services']);
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/departments', $filename, 'public');
            $data['image'] = $filename;
        }

        Department::create($data);

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Department created successfully!');
    }

    public function show(Department $department)
    {
        // Load doctors
        $department->load(['doctors' => function($query) {
            $query->active()->ordered();
        }]);

        // Patient Statistics
        $patientStats = [
            'total' => \App\Models\Patient::where(function($q) use ($department) {
                $q->whereHas('departments', function($q2) use ($department) {
                    $q2->where('departments.id', $department->id);
                })->orWhere('department_id', $department->id);
            })->count(),
            'active' => \App\Models\Patient::where(function($q) use ($department) {
                $q->whereHas('departments', function($q2) use ($department) {
                    $q2->where('departments.id', $department->id);
                })->orWhere('department_id', $department->id);
            })->where('is_active', true)->count(),
            'this_month' => \App\Models\Patient::where(function($q) use ($department) {
                $q->whereHas('departments', function($q2) use ($department) {
                    $q2->where('departments.id', $department->id);
                })->orWhere('department_id', $department->id);
            })->whereMonth('created_at', now()->month)->count(),
        ];

        // Appointment Statistics
        $appointmentStats = [
            'total' => $department->appointments()->count(),
            'today' => $department->appointments()->today()->count(),
            'upcoming' => $department->appointments()->where('appointment_date', '>=', today())->where('status', '!=', 'cancelled')->count(),
            'completed' => $department->appointments()->where('status', 'completed')->count(),
            'pending' => $department->appointments()->where('status', 'pending')->count(),
            'cancelled' => $department->appointments()->where('status', 'cancelled')->count(),
            'this_month' => $department->appointments()->whereMonth('appointment_date', now()->month)->count(),
        ];

        // Doctor Statistics
        $doctorStats = [
            'total' => $department->doctors()->count(),
            'active' => $department->doctors()->where('is_active', true)->count(),
            'available' => $department->doctors()->where('is_active', true)->where('is_available_online', true)->count(),
        ];

        // Medical Records Statistics
        $medicalRecordStats = [
            'total' => \App\Models\MedicalRecord::byDepartment($department->id)->count(),
            'this_month' => \App\Models\MedicalRecord::byDepartment($department->id)
                ->whereMonth('record_date', now()->month)
                ->whereYear('record_date', now()->year)
                ->count(),
        ];

        // Prescription Statistics
        $prescriptionStats = [
            'total' => \App\Models\Prescription::whereHas('patient', function($q) use ($department) {
                $q->whereHas('departments', function($q2) use ($department) {
                    $q2->where('departments.id', $department->id);
                })->orWhere('department_id', $department->id);
            })->count(),
            'pending' => \App\Models\Prescription::whereHas('patient', function($q) use ($department) {
                $q->whereHas('departments', function($q2) use ($department) {
                    $q2->where('departments.id', $department->id);
                })->orWhere('department_id', $department->id);
            })->where('status', 'pending')->count(),
            'this_month' => \App\Models\Prescription::whereHas('patient', function($q) use ($department) {
                $q->whereHas('departments', function($q2) use ($department) {
                    $q2->where('departments.id', $department->id);
                })->orWhere('department_id', $department->id);
            })->whereMonth('prescription_date', now()->month)->count(),
        ];

        // Lab Report Statistics
        $labReportStats = [
            'total' => \App\Models\LabReport::whereHas('patient', function($q) use ($department) {
                $q->whereHas('departments', function($q2) use ($department) {
                    $q2->where('departments.id', $department->id);
                })->orWhere('department_id', $department->id);
            })->count(),
            'pending' => \App\Models\LabReport::whereHas('patient', function($q) use ($department) {
                $q->whereHas('departments', function($q2) use ($department) {
                    $q2->where('departments.id', $department->id);
                })->orWhere('department_id', $department->id);
            })->where('status', 'pending')->count(),
            'this_month' => \App\Models\LabReport::whereHas('patient', function($q) use ($department) {
                $q->whereHas('departments', function($q2) use ($department) {
                    $q2->where('departments.id', $department->id);
                })->orWhere('department_id', $department->id);
            })->whereMonth('test_date', now()->month)->count(),
        ];

        // Today's Appointments
        $todayAppointments = $department->appointments()
            ->with(['patient', 'doctor'])
            ->today()
            ->orderBy('appointment_time')
            ->get();

        // Upcoming Appointments (next 7 days)
        $upcomingAppointments = $department->appointments()
            ->with(['patient', 'doctor'])
            ->where('appointment_date', '>=', today())
            ->where('appointment_date', '<=', today()->addDays(7))
            ->where('status', '!=', 'cancelled')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(10)
            ->get();

        // Recent Patients (last 10)
        $recentPatients = \App\Models\Patient::where(function($q) use ($department) {
            $q->whereHas('departments', function($q2) use ($department) {
                $q2->where('departments.id', $department->id);
            })->orWhere('department_id', $department->id);
        })
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Top Doctors by Appointment Count
        $topDoctors = $department->doctors()
            ->withCount(['appointments' => function($q) use ($department) {
                $q->where('department_id', $department->id);
            }])
            ->orderBy('appointments_count', 'desc')
            ->limit(5)
            ->get();

        return view('admin.departments.show', compact(
            'department',
            'todayAppointments',
            'upcomingAppointments',
            'patientStats',
            'appointmentStats',
            'doctorStats',
            'medicalRecordStats',
            'prescriptionStats',
            'labReportStats',
            'recentPatients',
            'topDoctors'
        ));
    }

    public function edit(Department $department)
    {
        return view('admin.departments.edit', compact('department'));
    }

    public function update(Request $request, Department $department)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:active,inactive',
            'head_of_department' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'services' => 'nullable|string',
            'operating_hours' => 'nullable|string|max:255',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'location' => 'nullable|string',
            'working_hours' => 'nullable|string',
            'is_emergency' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $data = $request->except(['image', 'status']);
        $data['slug'] = Str::slug($request->name);
        $data['is_active'] = ($request->status === 'active');
        
        // Convert services from textarea to array if it's a string
        if (isset($data['services']) && is_string($data['services'])) {
            $data['services'] = array_filter(array_map('trim', explode("\n", $data['services'])));
            $data['services'] = array_values($data['services']); // Reset array keys
        }

        // Handle image upload
        if ($request->hasFile('image')) {
            // Delete old image
            if ($department->image) {
                Storage::disk('public')->delete('uploads/departments/' . $department->image);
            }

            $file = $request->file('image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $file->storeAs('uploads/departments', $filename, 'public');
            $data['image'] = $filename;
        }

        $department->update($data);

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Department updated successfully!');
    }

    public function destroy(Department $department)
    {
        // Check if department has doctors
        if ($department->doctors()->count() > 0) {
            return redirect()
                ->route('admin.departments.index')
                ->with('error', 'Cannot delete department with assigned doctors!');
        }

        // Delete image if exists
        if ($department->image) {
            Storage::disk('public')->delete('uploads/departments/' . $department->image);
        }

        $department->delete();

        return redirect()
            ->route('admin.departments.index')
            ->with('success', 'Department deleted successfully!');
    }

    public function toggleStatus(Department $department)
    {
        $department->update(['is_active' => !$department->is_active]);

        return response()->json([
            'success' => true,
            'message' => 'Department status updated successfully!',
            'is_active' => $department->is_active
        ]);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'departments' => 'required|array',
            'departments.*.id' => 'required|exists:departments,id',
            'departments.*.sort_order' => 'required|integer'
        ]);

        foreach ($request->departments as $departmentData) {
            Department::where('id', $departmentData['id'])
                ->update(['sort_order' => $departmentData['sort_order']]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Department order updated successfully!'
        ]);
    }
}
