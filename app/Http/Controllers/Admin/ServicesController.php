<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ServicesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Service::with('department');
        
        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }
        
        // Department filter
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }
        
        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $services = $query->orderBy('name')->paginate(20);
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        
        return view('admin.services.index', compact('services', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('admin.services.create', compact('departments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'price' => 'nullable|numeric|min:0',
            'duration' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);
        
        try {
            DB::beginTransaction();
            
            $serviceData = [
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'short_description' => Str::limit($request->description, 150),
                'description' => $request->description,
                'department_id' => $request->department_id,
                'price' => $request->price,
                'duration' => $request->duration,
                'is_active' => $request->boolean('is_active', true)
            ];
            
            // Handle image upload
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('services', 'public');
                $serviceData['image'] = $imagePath;
            }
            
            $service = Service::create($serviceData);
            
            DB::commit();
            
            return redirect()->route('admin.services.index')
                           ->with('success', 'Service created successfully.');
                           
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating service: ' . $e->getMessage());
            
            return back()->withInput()
                        ->with('error', 'Error creating service. Please try again.');
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Service $service)
    {
        $service->load('department');
        return view('admin.services.show', compact('service'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Service $service)
    {
        $service->load('department');
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('admin.services.edit', compact('service', 'departments'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Service $service)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'department_id' => 'required|exists:departments,id',
            'price' => 'nullable|numeric|min:0',
            'duration' => 'nullable|integer|min:1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_active' => 'boolean'
        ]);
        
        try {
            DB::beginTransaction();
            
            $serviceData = [
                'name' => $request->name,
                'slug' => Str::slug($request->name),
                'short_description' => Str::limit($request->description, 150),
                'description' => $request->description,
                'department_id' => $request->department_id,
                'price' => $request->price,
                'duration' => $request->duration,
                'is_active' => $request->boolean('is_active', true)
            ];
            
            // Handle image upload
            if ($request->hasFile('image')) {
                // Delete old image if exists
                if ($service->image) {
                    Storage::disk('public')->delete($service->image);
                }
                
                $imagePath = $request->file('image')->store('services', 'public');
                $serviceData['image'] = $imagePath;
            }
            
            $service->update($serviceData);
            
            DB::commit();
            
            return redirect()->route('admin.services.index')
                           ->with('success', 'Service updated successfully.');
                           
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating service: ' . $e->getMessage());
            
            return back()->withInput()
                        ->with('error', 'Error updating service. Please try again.');
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Service $service)
    {
        try {
            // Delete associated image if exists
            if ($service->image) {
                Storage::disk('public')->delete($service->image);
            }
            
            $service->delete();
            
            return redirect()->route('admin.services.index')
                           ->with('success', 'Service deleted successfully.');
                           
        } catch (\Exception $e) {
            Log::error('Error deleting service: ' . $e->getMessage());
            
            return back()->with('error', 'Error deleting service. Please try again.');
        }
    }

    /**
     * Toggle service status
     */
    public function toggleStatus(Service $service)
    {
        try {
            $service->update(['is_active' => !$service->is_active]);
            
            $status = $service->is_active ? 'activated' : 'deactivated';
            
            return response()->json([
                'success' => true,
                'message' => "Service {$status} successfully.",
                'status' => $service->is_active
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error toggling service status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating service status.'
            ], 500);
        }
    }
}
