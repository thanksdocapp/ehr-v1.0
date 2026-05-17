<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ClinicBookingRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Services\ClinicBookingService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

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

        $acceptedPreviewQuery = $this->acceptedClinicRequestsQuery($request);
        $acceptedTotalCount = (clone $acceptedPreviewQuery)->count();
        $acceptedPreview = (clone $acceptedPreviewQuery)->limit(5)->get();

        return view('admin.clinic-booking-requests.index', compact(
            'requests',
            'doctorsByDept',
            'departments',
            'pendingCount',
            'acceptedPreview',
            'acceptedTotalCount'
        ));
    }

    /**
     * Full list of accepted clinic booking requests (filters + pagination).
     */
    public function accepted(Request $request): View
    {
        $departments = Department::query()->active()->orderBy('name')->get();

        $acceptedRequests = $this->acceptedClinicRequestsQuery($request)
            ->paginate(50)
            ->withQueryString();

        return view('admin.clinic-booking-requests.accepted', compact(
            'acceptedRequests',
            'departments'
        ));
    }

    /**
     * CSV export of accepted clinic booking requests (same filters as the accepted list).
     */
    public function exportAcceptedCsv(Request $request): StreamedResponse
    {
        $rows = $this->acceptedClinicRequestsQuery($request)->get();
        $hasAcceptedByColumn = Schema::hasColumn((new ClinicBookingRequest)->getTable(), 'accepted_by_user_id');

        $filename = 'clinic-booking-requests-accepted-'.now()->format('Y-m-d-His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
        ];

        $callback = function () use ($rows, $hasAcceptedByColumn) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($file, [
                'Request number',
                'Clinic',
                'Patient name',
                'Patient email',
                'Patient phone',
                'Assigned doctor',
                'Service',
                'Appointment date',
                'Appointment time',
                'Accepted by (name)',
                'Accepted by (email)',
                'Accepted at',
                'Appointment ID',
            ]);

            foreach ($rows as $req) {
                $pd = $req->patient_data ?? [];
                $patientName = trim(($pd['first_name'] ?? '').' '.($pd['last_name'] ?? ''));
                $doctorLabel = $req->doctor
                    ? ($req->doctor->user->name ?? trim($req->doctor->first_name.' '.$req->doctor->last_name))
                    : '';
                $acceptor = $hasAcceptedByColumn ? $req->acceptedByUser : null;
                $acceptorName = $acceptor ? (string) ($acceptor->name ?? '') : '';
                $acceptorEmail = $acceptor ? (string) ($acceptor->email ?? '') : '';
                $acceptedAt = $req->accepted_at ?? $req->updated_at;

                fputcsv($file, [
                    $req->request_number,
                    $req->department?->name ?? '',
                    $patientName,
                    $pd['email'] ?? '',
                    $pd['phone'] ?? '',
                    $doctorLabel,
                    $req->service?->name ?? '',
                    $req->appointment_date?->format('Y-m-d') ?? '',
                    $req->appointment_time instanceof \DateTimeInterface
                        ? $req->appointment_time->format('H:i')
                        : (string) $req->appointment_time,
                    $acceptor ? ($acceptorName !== '' ? $acceptorName : '—') : 'Legacy (not recorded)',
                    $acceptor ? $acceptorEmail : '',
                    $acceptedAt ? $acceptedAt->format('Y-m-d H:i:s') : '',
                    $req->appointment_id ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Accepted requests only; optional clinic + accepted date range.
     * When `accepted_at` / `accepted_by_user_id` migrations are not applied yet, falls back to `updated_at` only.
     */
    protected function acceptedClinicRequestsQuery(Request $request): Builder
    {
        $table = (new ClinicBookingRequest)->getTable();
        $hasAcceptedAt = Schema::hasColumn($table, 'accepted_at');
        $hasAcceptedBy = Schema::hasColumn($table, 'accepted_by_user_id');

        $with = ['department', 'service', 'doctor.user', 'appointment'];
        if ($hasAcceptedBy) {
            $with[] = 'acceptedByUser';
        }

        $q = ClinicBookingRequest::query()
            ->with($with)
            ->where('status', 'accepted');

        if ($hasAcceptedAt) {
            $q->orderByDesc('accepted_at')->orderByDesc('updated_at');
        } else {
            $q->orderByDesc('updated_at');
        }

        if ($request->filled('department_id')) {
            $q->where('department_id', $request->integer('department_id'));
        }

        if ($request->filled('accepted_from')) {
            $from = $request->date('accepted_from')->startOfDay();
            if ($hasAcceptedAt) {
                $q->whereRaw('COALESCE(accepted_at, updated_at) >= ?', [$from]);
            } else {
                $q->where('updated_at', '>=', $from);
            }
        }

        if ($request->filled('accepted_to')) {
            $to = $request->date('accepted_to')->endOfDay();
            if ($hasAcceptedAt) {
                $q->whereRaw('COALESCE(accepted_at, updated_at) <= ?', [$to]);
            } else {
                $q->where('updated_at', '<=', $to);
            }
        }

        return $q;
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
            $appointment = $this->clinicBookingService->acceptRequest($clinicBookingRequest, $doctor, Auth::id());
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

    /**
     * Cancel a pending clinic booking request (releases the slot; does not refund payment automatically).
     */
    public function cancel(Request $request, ClinicBookingRequest $clinicBookingRequest): RedirectResponse
    {
        if (! $clinicBookingRequest->isPending()) {
            return redirect()->back()->with('error', 'Only pending booking requests can be cancelled.');
        }

        $request->validate([
            'cancellation_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->clinicBookingService->cancelRequest(
                $clinicBookingRequest,
                $request->input('cancellation_reason'),
                Auth::id()
            );

            $redirectParams = array_filter([
                'department_id' => $request->input('department_id'),
            ]);

            return redirect()
                ->route('admin.clinic-booking-requests.index', $redirectParams)
                ->with('success', 'Booking request '.$clinicBookingRequest->request_number.' has been cancelled.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->with('error', 'This booking request is no longer pending.');
        } catch (\Exception $e) {
            \Log::error('Admin clinic booking cancel failed', [
                'clinic_booking_request_id' => $clinicBookingRequest->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->with('error', 'Failed to cancel booking request. Please try again.');
        }
    }
}
