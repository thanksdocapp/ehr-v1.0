<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ClinicDataExportRequest;
use App\Models\Department;
use App\Models\Doctor;
use App\Services\ClinicDataExportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ClinicDataExportController extends Controller
{
    public function __construct(
        private readonly ClinicDataExportService $exportService
    ) {
    }

    public function index(Request $request): View
    {
        $this->ensureAdmin();

        $user = Auth::guard('admin')->user();
        $lockedDepartmentId = $user?->department_id ? (int) $user->department_id : null;

        $departmentsQuery = Department::query()->where('is_active', true)->orderBy('name');
        if ($lockedDepartmentId) {
            $departmentsQuery->where('id', $lockedDepartmentId);
        }
        $departments = $departmentsQuery->get();

        $selectedDepartmentId = (int) ($request->input('department_id', $lockedDepartmentId ?: ($departments->first()?->id ?? 0)));

        $doctorsQuery = Doctor::query()->orderBy('first_name');
        if ($selectedDepartmentId) {
            $doctorsQuery->where(function ($q) use ($selectedDepartmentId) {
                $q->where('department_id', $selectedDepartmentId)
                    ->orWhereHas('departments', function ($deptQuery) use ($selectedDepartmentId) {
                        $deptQuery->where('departments.id', $selectedDepartmentId);
                    });
            });
        }
        $doctors = $doctorsQuery->get();

        $preview = null;
        if ($selectedDepartmentId > 0) {
            try {
                $filters = $this->exportService->normalizeFilters(array_merge(
                    $request->only([
                        'department_id',
                        'reg_from',
                        'reg_to',
                        'status',
                        'record_date_from',
                        'record_date_to',
                        'record_type',
                        'doctor_id',
                        'include_private',
                        'include_attachments',
                    ]),
                    ['department_id' => $selectedDepartmentId]
                ));
                $this->exportService->assertDepartmentAccess($user, $filters['department_id']);
                $preview = $this->exportService->counts($filters);
            } catch (AccessDeniedHttpException) {
                $preview = null;
            }
        }

        return view('admin.clinic-export.index', [
            'departments' => $departments,
            'doctors' => $doctors,
            'lockedDepartmentId' => $lockedDepartmentId,
            'selectedDepartmentId' => $selectedDepartmentId,
            'preview' => $preview,
            'filters' => $request->only([
                'reg_from',
                'reg_to',
                'status',
                'record_date_from',
                'record_date_to',
                'record_type',
                'doctor_id',
                'include_private',
                'include_attachments',
            ]),
        ]);
    }

    public function preview(ClinicDataExportRequest $request): JsonResponse
    {
        $this->ensureAdmin();

        $user = Auth::guard('admin')->user();
        $filters = $this->exportService->normalizeFilters($request->validated());
        $this->exportService->assertDepartmentAccess($user, $filters['department_id']);

        return response()->json($this->exportService->counts($filters));
    }

    public function download(ClinicDataExportRequest $request)
    {
        $this->ensureAdmin();

        $user = Auth::guard('admin')->user();
        $filters = $this->exportService->normalizeFilters($request->validated());
        $this->exportService->assertDepartmentAccess($user, $filters['department_id']);

        $result = $this->exportService->buildZip($filters, $user);

        return response()->download($result['path'], $result['filename'], [
            'Content-Type' => 'application/zip',
        ])->deleteFileAfterSend(true);
    }

    private function ensureAdmin(): void
    {
        $user = Auth::guard('admin')->user();

        if (! $user || (! ($user->is_admin ?? false) && $user->role !== 'admin')) {
            abort(403, 'Clinic data export is available to administrators only.');
        }
    }
}
