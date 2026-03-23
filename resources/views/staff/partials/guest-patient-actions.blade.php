{{-- Guest-only: complete profile is the path to clear guest status (saved on update when info is complete) --}}
@php
    $primaryEmphasis = $primaryEmphasis ?? false;
    $patientEditUrl = $patientEditUrl ?? route('staff.patients.edit', $patient->id);
@endphp
<div class="d-flex gap-2 flex-wrap align-items-center">
    <a href="{{ $patientEditUrl }}" class="btn {{ $primaryEmphasis ? 'btn-danger' : 'btn-success' }} btn-sm" title="Enter required details and save. When the profile is complete, guest restrictions are removed automatically.">
        <i class="fas fa-user-edit me-1"></i> Complete patient profile
    </a>
</div>
<p class="small text-muted mb-0 mt-2">
    UK core demographics (identity, valid email, DOB, gender, phone, address) must be complete to clear guest status. Next-of-kin details are recommended and shown separately until added — they do not block conversion.
</p>
