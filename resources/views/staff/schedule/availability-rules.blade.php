@extends('layouts.doctor')

@section('title', 'Availability by Consultation Type')
@section('page-title', 'Availability by Consultation Type')
@section('page-subtitle', 'Control when patients can book in-person, online, or telephone consultations')

@php
    $modalityLabels = [
        'in_person' => 'In-person',
        'online' => 'Online (video)',
        'telephone' => 'Telephone',
        'all' => 'All types',
    ];
    $modalityBadges = [
        'in_person' => 'bg-primary',
        'online' => 'bg-info',
        'telephone' => 'bg-success',
        'all' => 'bg-secondary',
    ];
@endphp

@section('content')
<div class="fade-in-up">
    {{-- success/error flashes are rendered globally by layouts.doctor; only validation errors are page-local --}}
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="doctor-card mb-4">
                <div class="doctor-card-header d-flex justify-content-between align-items-center">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-clock me-2"></i>Your availability windows
                    </h5>
                    <a href="{{ route('staff.schedule.index') }}" class="btn btn-sm btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>Back to schedule
                    </a>
                </div>
                <div class="doctor-card-body">
                    <div class="alert alert-light border mb-4">
                        <i class="fas fa-lightbulb text-warning me-2"></i>
                        <strong>How it works:</strong> Each window says when a given consultation type is possible.
                        A service’s type (in-person, online or telephone) decides which windows a patient sees.
                        A window set to <strong>All types</strong> serves every consultation type. Booking any type fills
                        that physical time for all types, so you can never be double-booked.
                    </div>

                    @if ($needsReviewCount > 0)
                        <div class="alert alert-warning">
                            <i class="fas fa-triangle-exclamation me-2"></i>
                            <strong>{{ $needsReviewCount }}</strong> window(s) were imported from your old availability
                            and marked <strong>All types</strong>. Review each one and set the correct consultation type.
                        </div>
                    @endif

                    @php $hasAnyRule = $rulesByDay->flatten()->isNotEmpty(); @endphp

                    @if (!$hasAnyRule)
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-calendar-plus fa-2x mb-2"></i>
                            <p class="mb-0">No availability windows yet. Add one using the form.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Day</th>
                                        <th>Time window</th>
                                        <th>Consultation type</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($days as $day)
                                        @foreach ($rulesByDay->get($day, []) as $rule)
                                            <tr>
                                                <td class="text-capitalize">{{ $day }}</td>
                                                <td>{{ \Illuminate\Support\Str::of($rule->start_time)->substr(0, 5) }}
                                                    – {{ \Illuminate\Support\Str::of($rule->end_time)->substr(0, 5) }}</td>
                                                <td>
                                                    <span class="badge {{ $modalityBadges[$rule->modality] ?? 'bg-secondary' }}">
                                                        {{ $modalityLabels[$rule->modality] ?? $rule->modality }}
                                                    </span>
                                                    @if ($rule->needs_review)
                                                        <span class="badge bg-warning text-dark ms-1">Review</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($rule->is_active)
                                                        <span class="badge bg-success-subtle text-success">Active</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary">Inactive</span>
                                                    @endif
                                                </td>
                                                <td class="text-end">
                                                    <button type="button" class="btn btn-sm btn-outline-primary edit-rule-btn"
                                                        data-id="{{ $rule->id }}"
                                                        data-day="{{ $rule->day_of_week }}"
                                                        data-start="{{ \Illuminate\Support\Str::of($rule->start_time)->substr(0, 5) }}"
                                                        data-end="{{ \Illuminate\Support\Str::of($rule->end_time)->substr(0, 5) }}"
                                                        data-modality="{{ $rule->modality }}"
                                                        data-active="{{ $rule->is_active ? 1 : 0 }}">
                                                        <i class="fas fa-pen"></i>
                                                    </button>
                                                    <form action="{{ route('staff.schedule.availability-rules.destroy', $rule->id) }}"
                                                        method="POST" class="d-inline"
                                                        onsubmit="return confirm('Remove this availability window?');">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Add availability window
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <form action="{{ route('staff.schedule.availability-rules.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Day of week</label>
                            <select name="day_of_week" class="form-select" required>
                                @foreach ($days as $day)
                                    <option value="{{ $day }}" class="text-capitalize">{{ ucfirst($day) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label">Start</label>
                                <input type="time" name="start_time" class="form-control" value="09:00" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label">End</label>
                                <input type="time" name="end_time" class="form-control" value="17:00" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Consultation type</label>
                            <select name="modality" class="form-select" required>
                                @foreach ($modalities as $modality)
                                    <option value="{{ $modality }}">{{ $modalityLabels[$modality] ?? $modality }}</option>
                                @endforeach
                            </select>
                            <small class="text-muted">“All types” serves in-person, online and telephone.</small>
                        </div>
                        <div class="form-check mb-3">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" id="addActive" checked>
                            <label class="form-check-label" for="addActive">Active (bookable)</label>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus me-1"></i>Add window
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit modal -->
<div class="modal fade" id="editRuleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" id="editRuleForm">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Edit availability window</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Day of week</label>
                        <select name="day_of_week" class="form-select" id="editDay" required>
                            @foreach ($days as $day)
                                <option value="{{ $day }}">{{ ucfirst($day) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-6 mb-3">
                            <label class="form-label">Start</label>
                            <input type="time" name="start_time" class="form-control" id="editStart" required>
                        </div>
                        <div class="col-6 mb-3">
                            <label class="form-label">End</label>
                            <input type="time" name="end_time" class="form-control" id="editEnd" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Consultation type</label>
                        <select name="modality" class="form-select" id="editModality" required>
                            @foreach ($modalities as $modality)
                                <option value="{{ $modality }}">{{ $modalityLabels[$modality] ?? $modality }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-check mb-1">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="editActive">
                        <label class="form-check-label" for="editActive">Active (bookable)</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const updateUrlTemplate = "{{ route('staff.schedule.availability-rules.update', '__ID__') }}";
        const form = document.getElementById('editRuleForm');
        const modalEl = document.getElementById('editRuleModal');
        const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

        document.querySelectorAll('.edit-rule-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                form.action = updateUrlTemplate.replace('__ID__', btn.dataset.id);
                document.getElementById('editDay').value = btn.dataset.day;
                document.getElementById('editStart').value = btn.dataset.start;
                document.getElementById('editEnd').value = btn.dataset.end;
                document.getElementById('editModality').value = btn.dataset.modality;
                document.getElementById('editActive').checked = btn.dataset.active === '1';
                if (modal) modal.show();
            });
        });
    })();
</script>
@endpush
