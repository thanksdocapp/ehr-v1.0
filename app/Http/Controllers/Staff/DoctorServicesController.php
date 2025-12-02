<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\BookingService;
use App\Models\Doctor;
use App\Models\DoctorServicePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class DoctorServicesController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of services for the authenticated doctor.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        // Check if booking_services table exists
        if (!\Illuminate\Support\Facades\Schema::hasTable('booking_services')) {
            return redirect()->route('staff.dashboard')
                ->with('error', 'Booking services table not found. Please run migrations: php artisan migrate or php run_migrations.php');
        }

        // Check if doctor_service_prices table exists
        if (!\Illuminate\Support\Facades\Schema::hasTable('doctor_service_prices')) {
            return redirect()->route('staff.dashboard')
                ->with('error', 'Doctor service prices table not found. Please create the doctor_service_prices table. See create_booking_tables.sql');
        }

        // Get all global services
        $globalServices = BookingService::active()->orderBy('name')->get();

        // Get doctor's service overrides
        $doctorServicePrices = DoctorServicePrice::where('doctor_id', $doctor->id)
            ->with('service')
            ->get()
            ->keyBy('service_id');

        // Combine data for display
        $services = $globalServices->map(function ($service) use ($doctorServicePrices, $doctor) {
            $override = $doctorServicePrices->get($service->id);
            return [
                'id' => $service->id,
                'name' => $service->name,
                'description' => $service->description,
                'default_price' => $service->default_price,
                'default_duration_minutes' => $service->default_duration_minutes,
                'is_active_globally' => $service->is_active,
                'has_override' => $override !== null,
                'custom_price' => $override ? $override->custom_price : null,
                'custom_duration_minutes' => $override ? $override->custom_duration_minutes : null,
                'is_active_for_doctor' => $override ? $override->is_active : $service->is_active,
            ];
        });

        return view('staff.doctor-services.index', compact('services', 'doctor'));
    }

    /**
     * Show the form for creating a new service.
     */
    public function create()
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();
        
        return view('staff.doctor-services.create', compact('doctor'));
    }

    /**
     * Store a newly created service.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_duration_minutes' => 'required|integer|min:5|max:480',
            'default_price' => 'nullable|numeric|min:0',
            'tags_input' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        try {
            // Parse tags from comma-separated string
            $tags = [];
            if ($request->filled('tags_input')) {
                $tags = array_filter(array_map('trim', explode(',', $request->tags_input)));
            }

            // Create global service
            $service = BookingService::create([
                'name' => $request->name,
                'description' => $request->description,
                'default_duration_minutes' => $request->default_duration_minutes,
                'default_price' => $request->default_price,
                'tags' => $tags,
                'created_by' => $user->id,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Automatically create override for this doctor
            DoctorServicePrice::create([
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'custom_price' => $request->default_price,
                'custom_duration_minutes' => $request->default_duration_minutes,
                'is_active' => $request->boolean('is_active', true),
            ]);

            return redirect()->route('staff.doctor-services.index')
                ->with('success', 'Service created successfully.');
        } catch (\Exception $e) {
            Log::error('Error creating doctor service: ' . $e->getMessage(), [
                'doctor_id' => $doctor->id,
                'request' => $request->all()
            ]);
            return back()->with('error', 'Failed to create service: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Show the form for creating/editing a service override.
     */
    public function edit(BookingService $bookingService)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        // Get or create override
        $override = DoctorServicePrice::firstOrNew([
            'doctor_id' => $doctor->id,
            'service_id' => $bookingService->id,
        ]);

        return view('staff.doctor-services.edit', compact('bookingService', 'doctor', 'override'));
    }

    /**
     * Update or create a service override for the doctor.
     */
    public function update(Request $request, BookingService $bookingService)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        $request->validate([
            'custom_price' => 'nullable|numeric|min:0',
            'custom_duration_minutes' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);

        try {
            DoctorServicePrice::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'service_id' => $bookingService->id,
                ],
                [
                    'custom_price' => $request->custom_price,
                    'custom_duration_minutes' => $request->custom_duration_minutes,
                    'is_active' => $request->boolean('is_active', true),
                ]
            );

            return redirect()->route('staff.doctor-services.index')
                ->with('success', 'Service settings updated successfully.');
        } catch (\Exception $e) {
            Log::error('Error updating doctor service override: ' . $e->getMessage(), [
                'doctor_id' => $doctor->id,
                'service_id' => $bookingService->id,
                'request' => $request->all()
            ]);
            return back()->with('error', 'Failed to update service settings: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Toggle the active status of a service for the doctor.
     */
    public function toggleStatus(BookingService $bookingService)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        try {
            $override = DoctorServicePrice::firstOrNew([
                'doctor_id' => $doctor->id,
                'service_id' => $bookingService->id,
            ]);

            // If override doesn't exist, create it with default values
            if (!$override->exists) {
                $override->custom_price = $bookingService->default_price;
                $override->custom_duration_minutes = $bookingService->default_duration_minutes;
            }

            $override->is_active = !$override->is_active;
            $override->save();

            $status = $override->is_active ? 'activated' : 'deactivated';
            return back()->with('success', "Service {$status} successfully.");
        } catch (\Exception $e) {
            Log::error('Error toggling doctor service status: ' . $e->getMessage(), [
                'doctor_id' => $doctor->id,
                'service_id' => $bookingService->id
            ]);
            return back()->with('error', 'Failed to update service status: ' . $e->getMessage());
        }
    }

    /**
     * Remove a service override (revert to global settings).
     */
    public function destroy(BookingService $bookingService)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        try {
            DoctorServicePrice::where('doctor_id', $doctor->id)
                ->where('service_id', $bookingService->id)
                ->delete();

            return back()->with('success', 'Service override removed. Using global settings.');
        } catch (\Exception $e) {
            Log::error('Error removing doctor service override: ' . $e->getMessage(), [
                'doctor_id' => $doctor->id,
                'service_id' => $bookingService->id
            ]);
            return back()->with('error', 'Failed to remove service override: ' . $e->getMessage());
        }
    }
}

