<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\BookingService;
use App\Models\Doctor;
use App\Models\DoctorServicePrice;
use App\Models\Billing;
use App\Models\Invoice;
use App\Models\Patient;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

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

        // Get only services created by this doctor (private by default), ordered by sort_order
        $globalServices = BookingService::where('created_by', $user->id)
            ->ordered()
            ->get();

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
            'minimum_age' => 'nullable|integer|min:0|max:130',
            'maximum_age' => 'nullable|integer|min:0|max:130',
            'consultation_type' => 'required|in:in_person,online,telephone',
            'tags_input' => 'nullable|string',
            'is_active' => 'boolean',
        ]);
        if ($request->filled('minimum_age') && $request->filled('maximum_age')
            && (int) $request->maximum_age < (int) $request->minimum_age) {
            return back()->withErrors(['maximum_age' => 'Maximum age must be greater than or equal to minimum age.'])->withInput();
        }

        try {
            // Parse tags from comma-separated string
            $tags = [];
            if ($request->filled('tags_input')) {
                $tags = array_filter(array_map('trim', explode(',', $request->tags_input)));
            }

            // Create global service (sort_order = next for this doctor)
            $nextSortOrder = (int) BookingService::where('created_by', $user->id)->max('sort_order') + 1;
            $service = BookingService::create([
                'name' => $request->name,
                'description' => $request->description,
                'default_duration_minutes' => $request->default_duration_minutes,
                'default_price' => $request->default_price,
                'tags' => $tags,
                'created_by' => $user->id,
                'sort_order' => $nextSortOrder,
                'is_active' => $request->boolean('is_active', true),
            ]);

            // Automatically create override for this doctor
            DoctorServicePrice::create([
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'custom_price' => $request->default_price,
                'custom_duration_minutes' => $request->default_duration_minutes,
                'consultation_type' => $request->consultation_type ?? 'in_person',
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

        // Ensure doctor can only edit their own services
        if ($bookingService->created_by !== $user->id) {
            abort(403, 'You can only edit services you created.');
        }

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

        // Ensure doctor can only update their own services
        if ($bookingService->created_by !== $user->id) {
            abort(403, 'You can only update services you created.');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'default_price' => 'required|numeric|min:0',
            'description' => 'nullable|string|max:1000',
            'custom_duration_minutes' => 'required|integer|min:5|max:480',
            'minimum_age' => 'nullable|integer|min:0|max:130',
            'maximum_age' => 'nullable|integer|min:0|max:130',
            'consultation_type' => 'required|in:in_person,online,telephone',
            'is_active' => 'boolean',
        ]);
        if ($request->filled('minimum_age') && $request->filled('maximum_age')
            && (int) $request->maximum_age < (int) $request->minimum_age) {
            return back()->withErrors(['maximum_age' => 'Maximum age must be greater than or equal to minimum age.'])->withInput();
        }

        try {
            $newDefaultPrice = $request->default_price;
            $duration = (int) $request->custom_duration_minutes;

            // Update service (doctor owns this service - can edit name, default price and duration)
            $bookingService->update([
                'name' => $request->name,
                'default_price' => $newDefaultPrice,
                'default_duration_minutes' => $duration,
                'description' => $request->description,
                'minimum_age' => $request->filled('minimum_age') ? (int) $request->minimum_age : null,
                'maximum_age' => $request->filled('maximum_age') ? (int) $request->maximum_age : null,
            ]);

            // Update or create doctor service override (duration used by SlotAvailabilityService for scheduling)
            DoctorServicePrice::updateOrCreate(
                [
                    'doctor_id' => $doctor->id,
                    'service_id' => $bookingService->id,
                ],
                [
                    'custom_price' => $newDefaultPrice,
                    'custom_duration_minutes' => $duration,
                    'consultation_type' => $request->consultation_type ?? 'in_person',
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

        // Ensure doctor can only toggle their own services
        if ($bookingService->created_by !== $user->id) {
            abort(403, 'You can only toggle services you created.');
        }

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

        // Ensure doctor can only delete their own services
        if ($bookingService->created_by !== $user->id) {
            abort(403, 'You can only delete services you created.');
        }

        try {
            DoctorServicePrice::where('doctor_id', $doctor->id)
                ->where('service_id', $bookingService->id)
                ->delete();

            return back()->with('success', 'Custom settings removed. Service now uses default values.');
        } catch (\Exception $e) {
            Log::error('Error removing doctor service override: ' . $e->getMessage(), [
                'doctor_id' => $doctor->id,
                'service_id' => $bookingService->id
            ]);
            return back()->with('error', 'Failed to remove custom settings: ' . $e->getMessage());
        }
    }

    /**
     * Delete a service completely.
     */
    public function deleteService(BookingService $bookingService)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->firstOrFail();

        // Ensure doctor can only delete their own services
        if ($bookingService->created_by !== $user->id) {
            abort(403, 'You can only delete services you created.');
        }

        try {
            // Delete the override first
            DoctorServicePrice::where('doctor_id', $doctor->id)
                ->where('service_id', $bookingService->id)
                ->delete();

            // Delete the service itself
            $bookingService->delete();

            return back()->with('success', 'Service deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting doctor service: ' . $e->getMessage(), [
                'doctor_id' => $doctor->id,
                'service_id' => $bookingService->id
            ]);
            return back()->with('error', 'Failed to delete service: ' . $e->getMessage());
        }
    }

    /**
     * Generate a booking link for a service.
     */
    public function generateBookingLink(Request $request)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->with('department')->firstOrFail();

        $request->validate([
            'service_id' => 'required|exists:booking_services,id',
        ]);

        try {
            $service = BookingService::findOrFail($request->service_id);

            // Verify service is available for this doctor
            if (!$service->isAvailableForDoctor($doctor->id)) {
                return back()->with('error', 'Service is not available for your profile.');
            }

            // Get clinic/department slug
            $department = $doctor->primaryDepartment();
            if (!$department || !$department->slug) {
                return back()->with('error', 'Doctor must be associated with a clinic/department that has a slug.');
            }

            // Generate booking link with pre-selected service and doctor
            // Format: /book/service/{serviceId}/{doctorId}
            $bookingLink = route('public.booking.service', [
                'serviceId' => $service->id,
                'doctorId' => $doctor->id
            ]);

            Log::info('Service booking link generated', [
                'service_id' => $service->id,
                'doctor_id' => $doctor->id,
                'department_id' => $department->id,
                'booking_link' => $bookingLink,
            ]);

            // Return to doctor services page with booking link
            return redirect()->route('staff.doctor-services.index')
                ->with('success', 'Booking link generated successfully for ' . $service->name . '. Ready to use on websites!')
                ->with('booking_link', $bookingLink)
                ->with('service_name', $service->name);

        } catch (\Exception $e) {
            Log::error('Error generating service booking link: ' . $e->getMessage(), [
                'doctor_id' => $doctor->id,
                'service_id' => $request->service_id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to generate booking link: ' . $e->getMessage());
        }
    }

    /**
     * Reorder services for the authenticated doctor.
     */
    public function reorder(Request $request)
    {
        $user = Auth::user();
        $request->validate([
            'order' => 'required|array',
            'order.*' => 'required|integer|exists:booking_services,id',
        ]);

        $ids = $request->order;
        $services = BookingService::where('created_by', $user->id)->whereIn('id', $ids)->get();
        if ($services->count() !== count($ids)) {
            return response()->json(['error' => 'Invalid service list.'], 403);
        }

        foreach ($ids as $position => $id) {
            BookingService::where('id', $id)->where('created_by', $user->id)->update(['sort_order' => $position]);
        }

        return response()->json(['success' => true, 'message' => 'Order saved.']);
    }

}

