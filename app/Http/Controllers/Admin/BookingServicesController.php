<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingService;
use App\Models\Doctor;
use App\Models\DoctorServicePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingServicesController extends Controller
{
    /**
     * Display a listing of booking services.
     */
    public function index(Request $request)
    {
        // Check if booking_services table exists
        if (!\Illuminate\Support\Facades\Schema::hasTable('booking_services')) {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Booking services table not found. Please run: php artisan migrate or php run_migrations.php');
        }

        $query = BookingService::with('creator');

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }

        $services = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.booking-services.index', compact('services'));
    }

    /**
     * Show the form for creating a new booking service.
     */
    public function create()
    {
        return view('admin.booking-services.create');
    }

    /**
     * Store a newly created booking service.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_duration_minutes' => 'required|integer|min:5|max:480',
            'default_price' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $service = BookingService::create([
            'name' => $request->name,
            'description' => $request->description,
            'default_duration_minutes' => $request->default_duration_minutes,
            'default_price' => $request->default_price,
            'tags' => $request->tags ?? [],
            'created_by' => Auth::id(),
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.booking-services.index')
            ->with('success', 'Booking service created successfully.');
    }

    /**
     * Display the specified booking service.
     */
    public function show(BookingService $bookingService)
    {
        $bookingService->load(['creator', 'doctorPrices.doctor']);
        
        // Get doctors using this service
        $doctorsUsingService = Doctor::whereHas('servicePrices', function ($query) use ($bookingService) {
            $query->where('service_id', $bookingService->id)->where('is_active', true);
        })->with('servicePrices', function ($query) use ($bookingService) {
            $query->where('service_id', $bookingService->id);
        })->get();

        return view('admin.booking-services.show', compact('bookingService', 'doctorsUsingService'));
    }

    /**
     * Show the form for editing the specified booking service.
     */
    public function edit(BookingService $bookingService)
    {
        return view('admin.booking-services.edit', compact('bookingService'));
    }

    /**
     * Update the specified booking service.
     */
    public function update(Request $request, BookingService $bookingService)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_duration_minutes' => 'required|integer|min:5|max:480',
            'default_price' => 'nullable|numeric|min:0',
            'tags' => 'nullable|array',
            'tags.*' => 'string|max:50',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $bookingService->update([
            'name' => $request->name,
            'description' => $request->description,
            'default_duration_minutes' => $request->default_duration_minutes,
            'default_price' => $request->default_price,
            'tags' => $request->tags ?? [],
            'is_active' => $request->has('is_active') ? true : false,
        ]);

        return redirect()->route('admin.booking-services.index')
            ->with('success', 'Booking service updated successfully.');
    }

    /**
     * Remove the specified booking service (soft delete by deactivating).
     */
    public function destroy(BookingService $bookingService)
    {
        // Check if service is being used
        $hasActiveAppointments = $bookingService->appointments()
            ->whereIn('status', ['pending', 'confirmed'])
            ->exists();

        if ($hasActiveAppointments) {
            return back()->with('error', 'Cannot delete service with active appointments. Please deactivate it instead.');
        }

        // Deactivate instead of deleting
        $bookingService->update(['is_active' => false]);

        return redirect()->route('admin.booking-services.index')
            ->with('success', 'Booking service deactivated successfully.');
    }

    /**
     * Toggle service active status.
     */
    public function toggleStatus(BookingService $bookingService)
    {
        $bookingService->update([
            'is_active' => !$bookingService->is_active
        ]);

        $status = $bookingService->is_active ? 'activated' : 'deactivated';

        return back()->with('success', "Booking service {$status} successfully.");
    }

    /**
     * Show form to assign service to a doctor.
     */
    public function assignDoctor(BookingService $bookingService)
    {
        $doctors = Doctor::where('is_active', true)
            ->with('user')
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();

        // Get doctors who already have this service
        $assignedDoctorIds = DoctorServicePrice::where('service_id', $bookingService->id)
            ->pluck('doctor_id')
            ->toArray();

        return view('admin.booking-services.assign-doctor', compact('bookingService', 'doctors', 'assignedDoctorIds'));
    }

    /**
     * Store service assignment to doctor.
     */
    public function storeDoctorAssignment(Request $request, BookingService $bookingService)
    {
        $validator = Validator::make($request->all(), [
            'doctor_id' => 'required|exists:doctors,id',
            'custom_price' => 'nullable|numeric|min:0',
            'custom_duration_minutes' => 'nullable|integer|min:5|max:480',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        try {
            DoctorServicePrice::updateOrCreate(
                [
                    'doctor_id' => $request->doctor_id,
                    'service_id' => $bookingService->id,
                ],
                [
                    'custom_price' => $request->custom_price ?? $bookingService->default_price,
                    'custom_duration_minutes' => $request->custom_duration_minutes ?? $bookingService->default_duration_minutes,
                    'is_active' => $request->boolean('is_active', true),
                ]
            );

            $doctor = Doctor::findOrFail($request->doctor_id);
            return redirect()->route('admin.booking-services.show', $bookingService)
                ->with('success', "Service assigned to Dr. {$doctor->full_name} successfully.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to assign service: ' . $e->getMessage())->withInput();
        }
    }
}

