@extends('admin.layouts.app')

@section('title', 'Consultations Report Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.advanced-reports.index') }}">Reports</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.consultations-report.index', request()->except(['month', 'department_id', 'page'])) }}">Consultations Report</a></li>
    <li class="breadcrumb-item active">Details</li>
@endsection

@section('content')
<div class="fade-in">
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h1 class="modern-page-title">
                        <i class="fas fa-clipboard-list me-2"></i>
                        Consultations Details
                    </h1>
                    <p class="modern-page-subtitle">
                        {{ $department->name ?? 'Department' }} • {{ \Carbon\Carbon::createFromFormat('Y-m', $monthKey)->format('F Y') }}
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.consultations-report.index', request()->except(['month', 'department_id', 'page'])) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to Report
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="modern-card fade-in-up">
        <div class="modern-card-header">
            <h6 class="modern-card-title mb-0">
                <i class="fas fa-list me-2"></i>Consultations
            </h6>
        </div>
        <div class="modern-card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Patient</th>
                            <th>Doctor</th>
                            <th>Type</th>
                            <th>Source</th>
                            <th class="text-end">Duration (Minutes)</th>
                            <th class="text-end">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ \Carbon\Carbon::parse($row->record_date)->format('d M Y') }}</div>
                                <small class="text-muted">{{ $row->department_name ?? 'N/A' }}</small>
                            </td>
                            <td>{{ $row->patient_name ?? 'N/A' }}</td>
                            <td>{{ $row->doctor_name ?? 'N/A' }}</td>
                            <td>
                                <span class="badge bg-info text-dark">
                                    {{ ucfirst(str_replace('_', ' ', $row->consultation_type)) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">
                                    {{ ucwords(str_replace('_', ' ', $row->source)) }}
                                </span>
                            </td>
                            <td class="text-end">
                                {{ number_format($row->duration_minutes) }}
                            </td>
                            <td class="text-end">
                                @if(!empty($row->appointment_id))
                                    <a href="{{ route('admin.appointments.show', $row->appointment_id) }}" class="btn btn-sm btn-outline-primary">Appointment</a>
                                @elseif(!empty($row->medical_record_id))
                                    <a href="{{ route('admin.medical-records.show', $row->medical_record_id) }}" class="btn btn-sm btn-outline-primary">Record</a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No consultations found for this department and month.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="p-3 border-top">
                {{ $rows->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
