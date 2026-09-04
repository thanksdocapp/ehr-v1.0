<?php

namespace App\Services;

use App\Models\BookingService;
use App\Models\Doctor;
use App\Models\DoctorServicePrice;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class BookingServiceDoctorAssignmentService
{
    /**
     * Active doctors available for admin assignment (all clinics).
     *
     * @return Collection<int, Doctor>
     */
    public function doctorsForAdminAssignment(): Collection
    {
        return Doctor::query()
            ->active()
            ->with(['departments', 'department'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get();
    }

    /**
     * Colleagues in the same clinic(s) as the given doctor (includes self).
     *
     * @return Collection<int, Doctor>
     */
    public function doctorsForStaffAssignment(Doctor $doctor): Collection
    {
        $departmentIds = $this->departmentIdsForDoctor($doctor);

        if ($departmentIds === []) {
            return collect([$doctor]);
        }

        return Doctor::query()
            ->byDepartments($departmentIds)
            ->active()
            ->with(['departments', 'department'])
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get()
            ->unique('id')
            ->values();
    }

    /**
     * @return list<int>
     */
    public function assignedDoctorIds(BookingService $service): array
    {
        return DoctorServicePrice::query()
            ->where('service_id', $service->id)
            ->pluck('doctor_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }

    /**
     * @return Collection<int, DoctorServicePrice>
     */
    public function assignmentRowsForService(BookingService $service): Collection
    {
        return DoctorServicePrice::query()
            ->where('service_id', $service->id)
            ->with('doctor')
            ->get()
            ->keyBy('doctor_id');
    }

    /**
     * @param  Collection<int, Doctor>  $doctors
     * @return array<int, array{name: string, doctors: array<int, Doctor>}>
     */
    public function groupDoctorsByDepartment(Collection $doctors): array
    {
        $groups = [];

        foreach ($doctors as $doctor) {
            $departments = $doctor->departments->isNotEmpty()
                ? $doctor->departments
                : ($doctor->department ? collect([$doctor->department]) : collect());

            if ($departments->isEmpty()) {
                $groups[0]['name'] = 'No clinic assigned';
                $groups[0]['doctors'][(int) $doctor->id] = $doctor;

                continue;
            }

            foreach ($departments as $department) {
                $groups[(int) $department->id]['name'] = $department->name;
                $groups[(int) $department->id]['doctors'][(int) $doctor->id] = $doctor;
            }
        }

        uasort($groups, fn ($a, $b) => strcasecmp($a['name'], $b['name']));

        return $groups;
    }

    /**
     * Sync doctor_service_prices rows for a booking service.
     *
     * @param  list<int>  $selectedDoctorIds
     * @param  list<int>  $requiredDoctorIds  Always kept assigned (e.g. service creator).
     * @param  array<int|string, array<string, mixed>>  $perDoctorSettings  Keyed by doctor id.
     */
    public function syncAssignments(
        BookingService $service,
        array $selectedDoctorIds,
        array $requiredDoctorIds = [],
        array $perDoctorSettings = [],
        ?string $defaultConsultationType = null
    ): void {
        $selectedDoctorIds = $this->normalizeDoctorIds($selectedDoctorIds);
        $requiredDoctorIds = $this->normalizeDoctorIds($requiredDoctorIds);
        $targetDoctorIds = array_values(array_unique(array_merge($selectedDoctorIds, $requiredDoctorIds)));

        $consultationDefault = $defaultConsultationType
            ?? $service->default_consultation_type
            ?? 'in_person';

        foreach ($targetDoctorIds as $doctorId) {
            $settings = $perDoctorSettings[$doctorId] ?? $perDoctorSettings[(string) $doctorId] ?? [];

            $customPrice = array_key_exists('custom_price', $settings)
                ? $settings['custom_price']
                : $service->default_price;

            $customDuration = array_key_exists('custom_duration_minutes', $settings)
                ? $settings['custom_duration_minutes']
                : null;

            if ($customDuration === '') {
                $customDuration = null;
            }

            DoctorServicePrice::updateOrCreate(
                [
                    'doctor_id' => $doctorId,
                    'service_id' => $service->id,
                ],
                [
                    'custom_price' => $customPrice,
                    'custom_duration_minutes' => $customDuration,
                    'consultation_type' => $settings['consultation_type'] ?? $consultationDefault,
                    'is_active' => array_key_exists('is_active', $settings)
                        ? (bool) $settings['is_active']
                        : true,
                ]
            );
        }

        $removeQuery = DoctorServicePrice::query()->where('service_id', $service->id);

        if ($targetDoctorIds !== []) {
            $removeQuery->whereNotIn('doctor_id', $targetDoctorIds);
        }

        $removeQuery->delete();
    }

    /**
     * @throws ValidationException
     */
    public function syncFromAdminRequest(Request $request, BookingService $service): void
    {
        $selectedDoctorIds = $this->normalizeDoctorIds($request->input('assigned_doctor_ids', []));
        $requiredDoctorIds = [];

        if ($request->filled('created_for_doctor_id')) {
            $requiredDoctorIds[] = (int) $request->input('created_for_doctor_id');
        }

        $perDoctorSettings = $this->parsePerDoctorSettingsFromRequest($request);
        $allowedDoctorIds = $this->doctorsForAdminAssignment()->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertDoctorIdsAllowed($selectedDoctorIds, $allowedDoctorIds);
        $this->assertDoctorIdsAllowed($requiredDoctorIds, $allowedDoctorIds);

        $this->syncAssignments(
            $service,
            $selectedDoctorIds,
            $requiredDoctorIds,
            $perDoctorSettings,
            $request->input('default_consultation_type', $service->default_consultation_type)
        );
    }

    /**
     * @throws ValidationException
     */
    public function syncFromStaffRequest(Request $request, BookingService $service, Doctor $actor): void
    {
        $selectedDoctorIds = $this->normalizeDoctorIds($request->input('assigned_doctor_ids', []));
        $allowedDoctorIds = $this->doctorsForStaffAssignment($actor)->pluck('id')->map(fn ($id) => (int) $id)->all();

        $this->assertDoctorIdsAllowed($selectedDoctorIds, $allowedDoctorIds);

        $perDoctorSettings = $this->parsePerDoctorSettingsFromRequest($request);

        $perDoctorSettings[(int) $actor->id] = array_merge(
            $perDoctorSettings[(int) $actor->id] ?? [],
            [
                'custom_price' => $request->input('default_price'),
                'custom_duration_minutes' => $request->boolean('is_non_consultation')
                    ? null
                    : (int) $request->input('custom_duration_minutes'),
                'consultation_type' => $request->boolean('is_non_consultation')
                    ? 'in_person'
                    : ($request->input('consultation_type') ?? 'in_person'),
                'is_active' => $request->boolean('is_active'),
            ]
        );

        $this->syncAssignments(
            $service,
            $selectedDoctorIds,
            [(int) $actor->id],
            $perDoctorSettings,
            $request->input('consultation_type', $service->default_consultation_type)
        );
    }

    /**
     * @return array<int|string, array<string, mixed>>
     */
    public function parsePerDoctorSettingsFromRequest(Request $request): array
    {
        $raw = $request->input('doctor_assignments', []);

        if (! is_array($raw)) {
            return [];
        }

        $normalized = [];

        foreach ($raw as $doctorId => $settings) {
            if (! is_array($settings)) {
                continue;
            }

            $id = (int) $doctorId;
            if ($id <= 0) {
                continue;
            }

            $entry = [];

            if (array_key_exists('consultation_type', $settings) && $settings['consultation_type'] !== '') {
                $entry['consultation_type'] = (string) $settings['consultation_type'];
            }

            if (array_key_exists('custom_price', $settings)) {
                $entry['custom_price'] = $settings['custom_price'] === '' || $settings['custom_price'] === null
                    ? null
                    : $settings['custom_price'];
            }

            if (array_key_exists('custom_duration_minutes', $settings)) {
                $entry['custom_duration_minutes'] = $settings['custom_duration_minutes'] === '' || $settings['custom_duration_minutes'] === null
                    ? null
                    : (int) $settings['custom_duration_minutes'];
            }

            if (array_key_exists('is_active', $settings)) {
                $entry['is_active'] = (bool) $settings['is_active'];
            }

            $normalized[$id] = $entry;
        }

        return $normalized;
    }

    /**
     * @param  list<int>  $doctorIds
     * @return list<int>
     */
    private function normalizeDoctorIds(array $doctorIds): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $doctorIds), fn ($id) => $id > 0)));
    }

    /**
     * @param  list<int>  $doctorIds
     * @param  list<int>  $allowedDoctorIds
     *
     * @throws ValidationException
     */
    private function assertDoctorIdsAllowed(array $doctorIds, array $allowedDoctorIds): void
    {
        $invalid = array_diff($doctorIds, $allowedDoctorIds);

        if ($invalid !== []) {
            throw ValidationException::withMessages([
                'assigned_doctor_ids' => ['One or more selected doctors are not valid for this service.'],
            ]);
        }
    }

    /**
     * @return list<int>
     */
    private function departmentIdsForDoctor(Doctor $doctor): array
    {
        $doctor->loadMissing(['departments', 'department']);

        $ids = $doctor->departments->pluck('id')->map(fn ($id) => (int) $id)->all();

        if ($doctor->department_id) {
            $ids[] = (int) $doctor->department_id;
        }

        return array_values(array_unique(array_filter($ids)));
    }
}
