<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\ClinicBookingRequest;
use App\Models\Doctor;
use App\Services\ClinicBookingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class ClinicBookingRequestsController extends Controller
{
    public function __construct(protected ClinicBookingService $clinicBookingService)
    {
    }

    /**
     * List pending clinic booking requests for the doctor's department(s).
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->with('departments')->first();

        if (!$doctor) {
            return redirect()->route('staff.dashboard')->with('error', 'Doctor profile not found.');
        }

        $departmentIds = $doctor->departments->isNotEmpty()
            ? $doctor->departments->pluck('id')->toArray()
            : ($doctor->department_id ? [$doctor->department_id] : []);

        if (empty($departmentIds)) {
            $requests = collect();
        } else {
            $requests = ClinicBookingRequest::with(['department', 'service'])
                ->pendingAcceptance()
                ->whereIn('department_id', $departmentIds)
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->get()
                ->filter(fn (ClinicBookingRequest $req) => $this->clinicBookingService->canDoctorAcceptClinicRequest($doctor, $req))
                ->values();
        }

        return view('staff.clinic-booking-requests.index', [
            'requests' => $requests,
            'doctor' => $doctor,
        ]);
    }

    /**
     * Accept a clinic booking request.
     */
    public function accept(ClinicBookingRequest $clinicBookingRequest)
    {
        $user = Auth::user();
        $doctor = Doctor::where('user_id', $user->id)->with('departments')->first();

        if (!$doctor) {
            return redirect()->back()->with('error', 'Doctor profile not found.');
        }

        $departmentIds = $doctor->departments->isNotEmpty()
            ? $doctor->departments->pluck('id')->toArray()
            : ($doctor->department_id ? [$doctor->department_id] : []);

        if (!in_array($clinicBookingRequest->department_id, $departmentIds)) {
            return redirect()->back()->with('error', 'You cannot accept bookings from this clinic.');
        }

        if (! $this->clinicBookingService->canDoctorAcceptClinicRequest($doctor, $clinicBookingRequest)) {
            return redirect()->back()->with('error', 'You are not available at this date and time.');
        }

        try {
            $appointment = $this->clinicBookingService->acceptRequest($clinicBookingRequest, $doctor, $user->id);
            return redirect()->route('staff.appointments.show', $appointment)
                ->with('success', 'Booking accepted! The patient has been added to your schedule.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (ValidationException $e) {
            return redirect()->back()->with('error', collect($e->errors())->flatten()->first() ?: 'You are not available at this date and time.');
        } catch (\Exception $e) {
            \Log::error('Clinic booking accept failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Failed to accept booking. Please try again.');
        }
    }
}
