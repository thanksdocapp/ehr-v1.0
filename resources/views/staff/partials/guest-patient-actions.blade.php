{{-- Guest patients only: primary action opens edit profile; after save, restrictions end once required fields are complete. --}}
@php
    $primaryEmphasis = $primaryEmphasis ?? false;
    $patientEditUrl = $patientEditUrl ?? route('staff.patients.edit', $patient->id);
@endphp
<div class="d-flex gap-2 flex-wrap align-items-center">
    <a href="{{ $patientEditUrl }}" class="btn {{ $primaryEmphasis ? 'btn-danger' : 'btn-success' }} btn-sm" title="Update required details and save. Restrictions lift when the record is complete.">
        <i class="fas fa-user-edit me-1"></i> Complete patient profile
    </a>
</div>
