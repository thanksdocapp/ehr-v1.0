@extends('admin.layouts.app')

@section('title', 'Clinic Data Export')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.advanced-reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Clinic Data Export</li>
@endsection

@push('styles')
@include('admin.shared.modern-ui')
@endpush

@section('content')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">
                <i class="fas fa-file-archive mr-2"></i>Clinic Data Export
            </h1>
            <p class="text-muted mb-0">
                Download a ZIP archive with patient demographics, medical record notes, and optional file attachments for one clinic.
                Administrators only.
            </p>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title mb-0"><i class="fas fa-sliders-h"></i>Export options</h5>
                </div>
                <div class="modern-card-body">
                    <form id="clinic-export-form" method="POST" action="{{ route('admin.clinic-export.download') }}">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="department_id" class="modern-form-label">Clinic <span class="text-danger">*</span></label>
                                <select class="modern-form-select @error('department_id') is-invalid @enderror"
                                        id="department_id" name="department_id" required
                                        @if($lockedDepartmentId) disabled @endif>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}"
                                            @selected(old('department_id', $selectedDepartmentId) == $department->id)>
                                            {{ $department->name }}@unless($department->is_active) (Inactive)@endunless
                                        </option>
                                    @endforeach
                                </select>
                                @if($lockedDepartmentId)
                                    <input type="hidden" name="department_id" value="{{ $lockedDepartmentId }}">
                                    <small class="text-muted">Your account is scoped to this clinic.</small>
                                @endif
                                @error('department_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="modern-form-label">Patient status</label>
                                <select class="modern-form-select" id="status" name="status">
                                    <option value="">All patients</option>
                                    <option value="active" @selected(old('status', $filters['status'] ?? '') === 'active')>Active only</option>
                                    <option value="inactive" @selected(old('status', $filters['status'] ?? '') === 'inactive')>Inactive only</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="reg_from" class="modern-form-label">Patient registered from</label>
                                <input type="date" class="modern-form-control" id="reg_from" name="reg_from"
                                       value="{{ old('reg_from', $filters['reg_from'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="reg_to" class="modern-form-label">Patient registered to</label>
                                <input type="date" class="modern-form-control" id="reg_to" name="reg_to"
                                       value="{{ old('reg_to', $filters['reg_to'] ?? '') }}">
                            </div>

                            <div class="col-12"><hr class="my-1"></div>
                            <div class="col-12"><h6 class="text-muted mb-0">Medical records filters</h6></div>

                            <div class="col-md-6">
                                <label for="record_date_from" class="modern-form-label">Record date from</label>
                                <input type="date" class="modern-form-control" id="record_date_from" name="record_date_from"
                                       value="{{ old('record_date_from', $filters['record_date_from'] ?? '') }}">
                            </div>
                            <div class="col-md-6">
                                <label for="record_date_to" class="modern-form-label">Record date to</label>
                                <input type="date" class="modern-form-control" id="record_date_to" name="record_date_to"
                                       value="{{ old('record_date_to', $filters['record_date_to'] ?? '') }}">
                            </div>

                            <div class="col-md-6">
                                <label for="record_type" class="modern-form-label">Record type</label>
                                <select class="modern-form-select" id="record_type" name="record_type">
                                    <option value="">All types</option>
                                    <option value="consultation" @selected(old('record_type', $filters['record_type'] ?? '') === 'consultation')>Consultation</option>
                                    <option value="followup" @selected(old('record_type', $filters['record_type'] ?? '') === 'followup')>Follow-up</option>
                                    <option value="administration_update" @selected(old('record_type', $filters['record_type'] ?? '') === 'administration_update')>Administrative update</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label for="doctor_id" class="modern-form-label">Doctor</label>
                                <select class="modern-form-select" id="doctor_id" name="doctor_id">
                                    <option value="">All doctors</option>
                                    @foreach($doctors as $doctor)
                                        <option value="{{ $doctor->id }}"
                                            @selected(old('doctor_id', $filters['doctor_id'] ?? '') == $doctor->id)>
                                            {{ formatDoctorName($doctor->full_name) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="include_private"
                                           name="include_private" @checked(old('include_private', $filters['include_private'] ?? false))>
                                    <label class="form-check-label" for="include_private">
                                        Include private notes (excluded by default)
                                    </label>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="include_attachments"
                                           name="include_attachments" @checked(old('include_attachments', $filters['include_attachments'] ?? false))>
                                    <label class="form-check-label" for="include_attachments">
                                        Include medical record attachments (files under <code>attachments/</code>)
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex flex-wrap gap-2 mt-4">
                            <button type="button" class="btn-modern btn-modern-outline" id="preview-export-btn">
                                <i class="fas fa-search"></i>Preview counts
                            </button>
                            <button type="submit" class="btn-modern btn-modern-primary">
                                <i class="fas fa-download"></i>Download ZIP
                            </button>
                            <a href="{{ route('admin.clinic-export.index') }}" class="btn-modern btn-modern-outline">
                                <i class="fas fa-rotate-right"></i>Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="modern-card mb-4">
                <div class="modern-card-header">
                    <h5 class="modern-card-title mb-0"><i class="fas fa-chart-bar"></i>Preview</h5>
                </div>
                <div class="modern-card-body">
                    <div id="export-preview-stats">
                        @if($preview)
                            <p class="mb-2"><strong>{{ number_format($preview['patient_count']) }}</strong> patients</p>
                            <p class="mb-2"><strong>{{ number_format($preview['record_count']) }}</strong> medical records</p>
                            @if(!empty($preview['attachments_included']))
                                <p class="mb-0"><strong>{{ number_format($preview['attachment_count']) }}</strong> attachments</p>
                            @else
                                <p class="text-muted mb-0 small">Enable attachments to include files in the ZIP.</p>
                            @endif
                        @else
                            <p class="text-muted mb-0">Select a clinic and click Preview counts to see how many rows will be exported.</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="modern-card">
                <div class="modern-card-header">
                    <h5 class="modern-card-title mb-0"><i class="fas fa-box-open"></i>ZIP contents</h5>
                </div>
                <div class="modern-card-body">
                    <ul class="mb-0 ps-3">
                        <li><code>export_manifest.txt</code> — export summary and filters</li>
                        <li><code>patients.csv</code> — patient demographics</li>
                        <li><code>medical_records.csv</code> — clinical notes (import-compatible columns)</li>
                        <li><code>attachments.csv</code> + <code>attachments/</code> — optional file metadata and binaries</li>
                    </ul>
                    <p class="text-muted small mt-3 mb-0">
                        Expired attachments are skipped. Missing files are listed in <code>attachments.csv</code> with status <code>missing</code>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('clinic-export-form');
    const previewBtn = document.getElementById('preview-export-btn');
    const previewBox = document.getElementById('export-preview-stats');
    const previewUrl = @json(route('admin.clinic-export.preview'));

    previewBtn.addEventListener('click', function () {
        const formData = new FormData(form);
        previewBtn.disabled = true;
        previewBox.innerHTML = '<p class="text-muted mb-0">Calculating…</p>';

        fetch(previewUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': formData.get('_token'),
                'Accept': 'application/json',
            },
            body: formData,
        })
            .then(function (response) {
                if (!response.ok) {
                    return response.json().then(function (data) {
                        throw new Error(data.message || 'Preview failed.');
                    });
                }
                return response.json();
            })
            .then(function (data) {
                var html =
                    '<p class="mb-2"><strong>' + Number(data.patient_count).toLocaleString() + '</strong> patients</p>' +
                    '<p class="mb-2"><strong>' + Number(data.record_count).toLocaleString() + '</strong> medical records</p>';

                if (data.attachments_included) {
                    html += '<p class="mb-0"><strong>' + Number(data.attachment_count).toLocaleString() + '</strong> attachments</p>';
                } else {
                    html += '<p class="text-muted mb-0 small">Enable attachments to include files in the ZIP.</p>';
                }

                previewBox.innerHTML = html;
            })
            .catch(function (error) {
                previewBox.innerHTML = '<p class="text-danger mb-0">' + error.message + '</p>';
            })
            .finally(function () {
                previewBtn.disabled = false;
            });
    });
});
</script>
@endpush
