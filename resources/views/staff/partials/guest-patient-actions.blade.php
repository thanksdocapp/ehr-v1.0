{{-- Guest-only: instant = clear flag only; edit = full form; quick convert = minimal questions --}}
@php
    $primaryEmphasis = $primaryEmphasis ?? false;
    $patientEditUrl = $patientEditUrl ?? route('staff.patients.edit', $patient->id);
    $patientConvertUrl = $patientConvertUrl ?? route('staff.patients.convert-guest', $patient);
    $patientInstantConvertUrl = $patientInstantConvertUrl ?? route('staff.patients.convert-guest-instant.post', $patient);
@endphp
<div class="d-flex gap-2 flex-wrap align-items-center">
    <form action="{{ $patientInstantConvertUrl }}" method="post" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-success btn-sm" title="Clears guest restrictions immediately. Does not change name, DOB, or other fields.">
            <i class="fas fa-user-check me-1"></i> Remove guest status
        </button>
    </form>
    <a href="{{ $patientEditUrl }}" class="btn {{ $primaryEmphasis ? 'btn-danger' : 'btn-warning' }} btn-sm">
        <i class="fas fa-user-edit me-1"></i> Complete patient profile
    </a>
    <a href="{{ $patientConvertUrl }}" class="btn btn-outline-secondary btn-sm">
        <i class="fas fa-bolt me-1"></i> Quick convert
    </a>
</div>
<p class="small text-muted mb-0 mt-2">
    <strong>Remove guest status</strong> drops guest restrictions in one step (no extra questions). Patient data stays as-is — use <strong>Complete patient profile</strong> when you still need to fix missing fields.
    <strong>Quick convert</strong> collects date of birth, gender, and address and then clears the guest flag.
</p>
