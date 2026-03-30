<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicBookingRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Services\ClinicBookingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClinicBookingRequestsController extends Controller
{
    public function __construct(protected ClinicBookingService $clinicBookingService) {}

    /**
     * All clinic booking requests awaiting a doctor assignment (practice-wide).
     */
    public function index(Request $request): View
    {
        $query = ClinicBookingRequest::query()
            ->with(['department', 'service'])
            ->pendingAcceptance()
            ->orderBy('appointment_date')
            ->orderBy('appointment_time');

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->integer('department_id'));
        }

        $requests = $query->paginate(25)->withQueryString();

        $departmentIds = $requests->getCollection()->pluck('department_id')->unique()->filter();
        $doctorsByDept = [];
        foreach ($departmentIds as $deptId) {
            $doctorsByDept[(int) $deptId] = Doctor::byDepartment((int) $deptId)
                ->active()
                ->with('user')
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        $departments = Department::query()->active()->orderBy('name')->get();
        $pendingCount = ClinicBookingRequest::query()->pendingAcceptance()->count();

        return view('admin.clinic-booking-requests.index', compact(
            'requests',
            'doctorsByDept',
            'departments',
            'pendingCount'
        ));
    }

    /**
     * Assign the booking to a doctor in the clinic (same outcome as doctor self-accept from staff portal).
     */
    public function accept(Request $request, ClinicBookingRequest $clinicBookingRequest): RedirectResponse
    {
        if (! $clinicBookingRequest->isPending()) {
            return redirect()->back()->with('error', 'This booking request is no longer pending.');
        }

        $request->validate([
            'doctor_id' => ['required', 'exists:doctors,id'],
        ]);

        $doctor = Doctor::query()
            ->with('departments')
            ->where('id', $request->integer('doctor_id'))
            ->active()
            ->firstOrFail();

        $departmentIds = $doctor->departments->isNotEmpty()
            ? $doctor->departments->pluck('id')->all()
            : ($doctor->department_id ? [$doctor->department_id] : []);

        if (! in_array((int) $clinicBookingRequest->department_id, array_map('intval', $departmentIds), true)) {
            return redirect()->back()->with('error', 'The selected doctor is not assigned to this clinic.');
        }

        $service = $clinicBookingRequest->service;
        if ($service && ! $service->isAvailableForDoctor($doctor->id)) {
            return redirect()->back()->with('error', 'The selected doctor does not offer this booking service.');
        }

        try {
            $appointment = $this->clinicBookingService->acceptRequest($clinicBookingRequest, $doctor);
            $doctorLabel = $doctor->user->name ?? trim($doctor->first_name.' '.$doctor->last_name);

            return redirect()->route('admin.appointments.show', $appointment)
                ->with('success', 'Booking accepted. Appointment created for '.$doctorLabel.'.');
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            \Log::error('Admin clinic booking accept failed', ['error' => $e->getMessage()]);

            return redirect()->back()->with('error', 'Failed to accept booking. Please try again.');
        }
    }
}
