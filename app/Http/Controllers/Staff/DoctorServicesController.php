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

        // Get only services created by this doctor (private by default)
        $globalServices = BookingService::where('created_by', $user->id)
            ->orderBy('name')
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
     * Generate a payment link for a service.
     */
    public function generatePaymentLink(Request $request)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->with('department')->firstOrFail();

        $request->validate([
            'service_id' => 'required|exists:booking_services,id',
        ]);

        try {
            $service = BookingService::findOrFail($request->service_id);

            // Get the price for this doctor
            $servicePrice = $service->getPriceForDoctor($doctor->id);
            
            if (!$servicePrice || $servicePrice <= 0) {
                return back()->with('error', 'Service does not have a valid price set.');
            }

            // Get clinic/department slug
            $department = $doctor->department;
            if (!$department || !$department->slug) {
                return back()->with('error', 'Doctor must be associated with a clinic/department that has a slug.');
            }
            $clinicSlug = $department->slug;

            // Generate service slug
            $serviceSlug = Str::slug($service->name);

            // Create a placeholder guest patient for this payment link
            // The actual payer information will be collected when they use the link
            $guestEmail = 'guest.' . time() . '.' . $service->id . '@payment-link.temp';
            $patient = Patient::create([
                'first_name' => 'Guest',
                'last_name' => 'Patient',
                'email' => $guestEmail,
                'phone' => null,
                'patient_id' => Patient::generatePatientId(),
                'is_guest' => true,
                'is_active' => true,
                'department_id' => $department->id,
                'assigned_doctor_id' => $doctor->id,
                'created_by_doctor_id' => $doctor->id,
            ]);

            // Create billing record
            // Set due_date far in the future (100 years) to effectively make it non-expiring
            $billing = Billing::create([
                'bill_number' => Billing::generateBillNumber(),
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'billing_date' => now(),
                'due_date' => now()->addYears(100), // Far future date for no expiration
                'type' => 'procedure', // Service payment
                'description' => 'Service Payment: ' . $service->name,
                'subtotal' => $servicePrice,
                'discount' => 0,
                'tax' => 0,
                'total_amount' => $servicePrice,
                'paid_amount' => 0,
                'balance' => $servicePrice,
                'status' => 'pending',
                'notes' => 'Direct payment link for service: ' . $service->name . ' (Clinic: ' . $department->name . ')',
                'created_by' => $user->id,
            ]);

            // Create invoice linked to billing
            // Set due_date far in the future (100 years) to effectively make it non-expiring
            $invoice = Invoice::create([
                'billing_id' => $billing->id,
                'patient_id' => $patient->id,
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'invoice_date' => now(),
                'due_date' => now()->addYears(100), // Far future date for no expiration
                'subtotal' => $servicePrice,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $servicePrice,
                'status' => 'pending',
                'description' => 'Service Payment: ' . $service->name,
                'payment_token' => Str::random(32),
                'payment_token_expires_at' => null, // No expiration for token
            ]);

            // Generate payment link with clinic/service structure: /{clinic-slug}/{service-slug}/pay/{token}
            $paymentLink = route('public.service.payment', [
                'clinic' => $clinicSlug,
                'service' => $serviceSlug,
                'token' => $invoice->payment_token
            ]);

            Log::info('Service payment link generated', [
                'service_id' => $service->id,
                'doctor_id' => $doctor->id,
                'department_id' => $department->id,
                'clinic_slug' => $clinicSlug,
                'service_slug' => $serviceSlug,
                'patient_id' => $patient->id,
                'billing_id' => $billing->id,
                'invoice_id' => $invoice->id,
                'invoice_token' => $invoice->payment_token,
                'payment_link' => $paymentLink,
            ]);

            // Return to doctor services page with payment link
            return redirect()->route('staff.doctor-services.index')
                ->with('success', 'Payment link generated successfully for ' . $service->name . '. Ready to use on websites!')
                ->with('payment_link', $paymentLink)
                ->with('invoice_number', $invoice->invoice_number)
                ->with('billing_number', $billing->bill_number)
                ->with('service_name', $service->name);

        } catch (\Exception $e) {
            Log::error('Error generating service payment link: ' . $e->getMessage(), [
                'doctor_id' => $doctor->id,
                'service_id' => $request->service_id,
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);
            return back()->with('error', 'Failed to generate payment link: ' . $e->getMessage());
        }
    }

}

