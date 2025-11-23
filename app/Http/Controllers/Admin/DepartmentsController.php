<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DepartmentsController extends Controller
{
    public function index()
    {
        $departments = Department::withCount(['doctors', 'appointments'])
            ->ordered()
            ->paginate(15);

        return view('admin.departments.index', compact('departments'));
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
        $department->load(['doctors' => function($query) {
            $query->active()->ordered();
        }]);

        $todayAppointments = $department->appointments()
            ->with(['patient', 'doctor'])
            ->today()
            ->ordered()
            ->get();

        return view('admin.departments.show', compact('department', 'todayAppointments'));
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
