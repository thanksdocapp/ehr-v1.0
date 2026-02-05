@extends('admin.layouts.app')

@section('title', 'Consultations Report')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.advanced-reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item active">Consultations Report</li>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof flatpickr === 'undefined') {
            console.warn('Flatpickr not loaded for consultations report dates.');
            return;
        }

        const inputIds = ['start_date', 'end_date'];
        inputIds.forEach(function(id) {
            const input = document.getElementById(id);
            if (!input) {
                return;
            }

            if (input._flatpickr) {
                input._flatpickr.destroy();
            }

            const instance = flatpickr(input, {
                dateFormat: "d/m/Y",
                altInput: false,
                altFormat: "d/m/Y",
                allowInput: true,
                clickOpens: true,
                locale: { firstDayOfWeek: 1 }
            });

            input._flatpickr = instance;
            input.setAttribute('data-flatpickr-initialized', 'true');
        });
    });
</script>
@endpush

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="modern-page-title">
                        <i class="fas fa-chart-bar me-2"></i>
                        Consultations Report
                    </h1>
                    <p class="modern-page-subtitle">Monthly consultations analysis with duration metrics</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.consultations-report.export-excel', request()->all()) }}" class="btn btn-success">
                        <i class="fas fa-file-excel me-2"></i>Export Excel
                    </a>
                    <a href="{{ route('admin.consultations-report.export-pdf', request()->all()) }}" class="btn btn-danger">
                        <i class="fas fa-file-pdf me-2"></i>Export PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="modern-card fade-in-up mb-4">
        <div class="modern-card-header">
            <h6 class="modern-card-title mb-0">
                <i class="fas fa-filter me-2"></i>Filters
            </h6>
        </div>
        <div class="modern-card-body">
            <form method="GET" action="{{ route('admin.consultations-report.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="modern-form-label">Start Date</label>
                    <input type="text" 
                           id="start_date" 
                           name="start_date" 
                           class="form-control modern-form-control uk-date" 
                           value="{{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }}"
                           placeholder="dd/mm/yyyy"
                           pattern="\d{2}/\d{2}/\d{4}"
                           maxlength="10"
                           data-uk-date="true"
                           required>
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="modern-form-label">End Date</label>
                    <input type="text" 
                           id="end_date" 
                           name="end_date" 
                           class="form-control modern-form-control uk-date" 
                           value="{{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}"
                           placeholder="dd/mm/yyyy"
                           pattern="\d{2}/\d{2}/\d{4}"
                           maxlength="10"
                           data-uk-date="true"
                           required>
                </div>
                <div class="col-md-4">
                    <label for="department_id" class="modern-form-label">Clinic/Department</label>
                    <select id="department_id" 
                            name="department_id" 
                            class="form-select modern-form-select">
                        <option value="">All Clinics</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ $departmentId == $dept->id ? 'selected' : '' }}>
                            {{ $dept->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="group_by" class="modern-form-label">Group By</label>
                    <select id="group_by"
                            name="group_by"
                            class="form-select modern-form-select">
                        <option value="month" {{ ($groupBy ?? 'department') === 'month' ? 'selected' : '' }}>Month (merged departments)</option>
                        <option value="department" {{ ($groupBy ?? 'department') === 'department' ? 'selected' : '' }}>Month + Department</option>
                    </select>
                </div>
                <div class="col-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search me-2"></i>Apply Filters
                    </button>
                    <a href="{{ route('admin.consultations-report.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-redo me-2"></i>Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    @php
        $totalMins = (int) ($summary['total_duration_minutes'] ?? 0);
        $summaryHours = (int) floor($totalMins / 60);
        $summaryMinutes = $totalMins % 60;
        $summaryDurationText = $summaryHours . ' hours ' . $summaryMinutes . ' minutes';
    @endphp
    <!-- Summary Cards -->
    <div class="row g-3 mb-4 fade-in-up" style="animation-delay: 0.1s;">
        <div class="col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($summary['total_consultations']) }}</div>
                        <div class="stat-label">Total Consultations</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="stat-card-enhanced">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number" style="font-size: 1.1rem;">{{ $summaryDurationText }}</div>
                        <div class="stat-label">Total Duration</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Report Table -->
    <div class="modern-card fade-in-up">
        <div class="modern-card-header">
            <h6 class="modern-card-title mb-0">
                <i class="fas fa-table me-2"></i>Monthly Consultations Report
            </h6>
        </div>
        <div class="modern-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Month</th>
                            <th>Clinic/Department</th>
                            <th class="text-end">Consultations</th>
                            <th class="text-end">Total Duration</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginator as $monthData)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $monthData->month_name }}</div>
                                <small class="text-muted">{{ $monthData->month_key }}</small>
                            </td>
                            <td>
                                @if(!empty($monthData->department_id))
                                    <a class="badge bg-primary text-decoration-none"
                                       href="{{ route('admin.consultations-report.details', array_merge(request()->all(), ['month' => $monthData->month_key, 'department_id' => $monthData->department_id])) }}">
                                        {{ $monthData->department_name }}
                                    </a>
                                @else
                                    <span class="badge bg-primary">{{ $monthData->department_name }}</span>
                                @endif
                            </td>
                            @php
                                $rowMins = (int) $monthData->total_duration_minutes;
                                $rowH = (int) floor($rowMins / 60);
                                $rowM = $rowMins % 60;
                                $rowDurationText = $rowH . ' hours ' . $rowM . ' minutes';
                            @endphp
                            <td class="text-end">
                                <span class="fw-semibold">{{ number_format($monthData->total_consultations) }}</span>
                            </td>
                            <td class="text-end">
                                <span class="fw-semibold text-info">{{ $rowDurationText }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No consultations found for the selected period.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                {{ $paginator->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

