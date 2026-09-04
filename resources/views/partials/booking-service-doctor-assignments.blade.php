@php
    $assignableDoctors = $assignableDoctors ?? collect();
    $assignedDoctorIds = array_map('intval', $assignedDoctorIds ?? []);
    $lockedDoctorIds = array_map('intval', $lockedDoctorIds ?? []);
    $doctorAssignments = $doctorAssignments ?? collect();
    $showPerDoctorSettings = (bool) ($showPerDoctorSettings ?? false);
    $assignmentMode = $assignmentMode ?? 'admin';
    $bookingService = $bookingService ?? null;
    $assignmentGroups = app(\App\Services\BookingServiceDoctorAssignmentService::class)
        ->groupDoctorsByDepartment($assignableDoctors);
    $defaultConsultationType = old(
        'default_consultation_type',
        $bookingService?->default_consultation_type ?? 'in_person'
    );
@endphp

<div class="card border mb-4">
    <div class="card-header bg-light">
        <h6 class="mb-1 fw-bold">
            <i class="fas fa-user-md me-2"></i>Assign to doctors
        </h6>
        <p class="small text-muted mb-0">
            @if($showPerDoctorSettings)
                Choose which doctors offer this service. Only assigned doctors will show this service on public and clinic booking.
            @else
                Select doctors now; they will use the service defaults above. You can customize each doctor after saving.
            @endif
        </p>
    </div>
    <div class="card-body">
        @error('assigned_doctor_ids')
            <div class="alert alert-danger py-2">{{ $message }}</div>
        @enderror

        @if(empty($assignmentGroups))
            <p class="text-muted mb-0">No active doctors are available to assign.</p>
        @else
            @foreach($assignmentGroups as $group)
                <div class="mb-3">
                    <div class="fw-semibold text-primary mb-2">{{ $group['name'] }}</div>
                    <div class="row g-2">
                        @foreach($group['doctors'] as $doctor)
                            @php
                                $doctorId = (int) $doctor->id;
                                $isLocked = in_array($doctorId, $lockedDoctorIds, true);
                                $isChecked = $isLocked
                                    || in_array($doctorId, $assignedDoctorIds, true)
                                    || in_array((string) $doctorId, old('assigned_doctor_ids', []), true);
                                $assignment = $doctorAssignments->get($doctorId);
                                $consultationType = old(
                                    "doctor_assignments.{$doctorId}.consultation_type",
                                    $assignment?->consultation_type ?? $defaultConsultationType
                                );
                                $customPrice = old(
                                    "doctor_assignments.{$doctorId}.custom_price",
                                    $assignment?->custom_price ?? $bookingService?->default_price
                                );
                                $customDuration = old(
                                    "doctor_assignments.{$doctorId}.custom_duration_minutes",
                                    $assignment?->custom_duration_minutes ?? $bookingService?->default_duration_minutes
                                );
                                $isActiveForDoctor = old(
                                    "doctor_assignments.{$doctorId}.is_active",
                                    $assignment?->is_active ?? true
                                );
                            @endphp
                            <div class="col-12">
                                <div class="border rounded p-3 h-100 doctor-assignment-row" data-doctor-id="{{ $doctorId }}">
                                    <div class="form-check">
                                        <input type="checkbox"
                                               class="form-check-input doctor-assignment-checkbox"
                                               id="assigned_doctor_{{ $doctorId }}"
                                               name="assigned_doctor_ids[]"
                                               value="{{ $doctorId }}"
                                               @checked($isChecked)
                                               @disabled($isLocked)>
                                        @if($isLocked)
                                            <input type="hidden" name="assigned_doctor_ids[]" value="{{ $doctorId }}">
                                        @endif
                                        <label class="form-check-label fw-semibold" for="assigned_doctor_{{ $doctorId }}">
                                            {{ formatDoctorName($doctor->full_name) }}
                                            @if($doctor->specialization)
                                                <span class="text-muted fw-normal">— {{ $doctor->specialization }}</span>
                                            @endif
                                            @if($isLocked)
                                                <span class="badge bg-secondary ms-1">Always assigned</span>
                                            @endif
                                        </label>
                                    </div>

                                    @if($showPerDoctorSettings && ! $isLocked)
                                        <div class="doctor-assignment-settings mt-3 ps-4 border-start @if(!$isChecked) d-none @endif">
                                            <div class="row g-2">
                                                <div class="col-md-4">
                                                    <label class="form-label small mb-1">Consultation type</label>
                                                    <select name="doctor_assignments[{{ $doctorId }}][consultation_type]"
                                                            class="form-select form-select-sm">
                                                        <option value="in_person" @selected($consultationType === 'in_person')>In person</option>
                                                        <option value="online" @selected($consultationType === 'online')>Online</option>
                                                        <option value="telephone" @selected($consultationType === 'telephone')>Telephone</option>
                                                    </select>
                                                </div>
                                                @if($assignmentMode === 'admin')
                                                    <div class="col-md-3">
                                                        <label class="form-label small mb-1">Price override</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text">£</span>
                                                            <input type="number"
                                                                   step="0.01"
                                                                   min="0"
                                                                   class="form-control"
                                                                   name="doctor_assignments[{{ $doctorId }}][custom_price]"
                                                                   value="{{ $customPrice }}">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small mb-1">Duration override</label>
                                                        <div class="input-group input-group-sm">
                                                            <input type="number"
                                                                   min="5"
                                                                   max="480"
                                                                   class="form-control"
                                                                   name="doctor_assignments[{{ $doctorId }}][custom_duration_minutes]"
                                                                   value="{{ $customDuration }}">
                                                            <span class="input-group-text">min</span>
                                                        </div>
                                                    </div>
                                                @endif
                                                <div class="col-md-2 d-flex align-items-end">
                                                    <div class="form-check form-switch mb-2">
                                                        <input type="hidden" name="doctor_assignments[{{ $doctorId }}][is_active]" value="0">
                                                        <input class="form-check-input"
                                                               type="checkbox"
                                                               name="doctor_assignments[{{ $doctorId }}][is_active]"
                                                               value="1"
                                                               @checked($isActiveForDoctor)>
                                                        <label class="form-check-label small">Active</label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>

@if($showPerDoctorSettings)
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.doctor-assignment-checkbox').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            const row = checkbox.closest('.doctor-assignment-row');
            const settings = row ? row.querySelector('.doctor-assignment-settings') : null;
            if (!settings || checkbox.disabled) {
                return;
            }
            settings.classList.toggle('d-none', !checkbox.checked);
        });
    });
});
</script>
@endif
