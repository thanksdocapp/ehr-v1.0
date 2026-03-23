{{-- Guest-only: one convert action (instant); edit for full demographics --}}
@php
    $primaryEmphasis = $primaryEmphasis ?? false;
    $patientEditUrl = $patientEditUrl ?? route('staff.patients.edit', $patient->id);
    $patientInstantConvertUrl = $patientInstantConvertUrl ?? route('staff.patients.convert-guest-instant.post', $patient);
@endphp
<div class="d-flex gap-2 flex-wrap align-items-center">
    <form action="{{ $patientInstantConvertUrl }}" method="post" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-success btn-sm" title="Clears guest restrictions in one step. Existing patient details are not changed — use Complete patient profile to add or fix fields.">
            <i class="fas fa-user-check me-1"></i> Convert guest patient
        </button>
    </form>
    <a href="{{ $patientEditUrl }}" class="btn {{ $primaryEmphasis ? 'btn-danger' : 'btn-warning' }} btn-sm">
        <i class="fas fa-user-edit me-1"></i> Complete patient profile
    </a>
</div>
<p class="small text-muted mb-0 mt-2">
    <strong>Convert guest patient</strong> removes guest restrictions immediately (no extra questions). Data already on the record stays as it is. Use <strong>Complete patient profile</strong> to enter or correct name, DOB, address, and other required fields.
</p>
