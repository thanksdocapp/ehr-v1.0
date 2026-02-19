@extends('admin.layouts.app')

@section('title', 'Form Submission Details')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.generated-documents.index') }}">Documents</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.form-requests.index') }}">Form Submissions</a></li>
    <li class="breadcrumb-item active">{{ $formRequest->template->name ?? ($formRequest->patientDocument->title ?? 'Details') }}</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-check me-2 text-success"></i>Form Submission Details
            </h1>
            <p class="text-muted mb-0">View submitted form data</p>
        </div>
        <div>
            <a href="{{ route('admin.form-requests.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>Back to List
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Form Details Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Request Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <td class="text-muted" style="width: 120px;">Form:</td>
                            <td class="fw-semibold">{{ $formRequest->template->name ?? ($formRequest->patientDocument->title ?? 'Form') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Patient:</td>
                            <td>
                                @if($formRequest->patient)
                                    <a href="{{ route('admin.patients.show', $formRequest->patient) }}">
                                        {{ $formRequest->patient->full_name ?? $formRequest->patient->first_name . ' ' . $formRequest->patient->last_name }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Email:</td>
                            <td><small>{{ $formRequest->recipient_email }}</small></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status:</td>
                            <td>
                                <span class="badge {{ $formRequest->status_badge_class }}">
                                    {{ $formRequest->status_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Requested By:</td>
                            <td>{{ $formRequest->requester->name ?? 'Unknown' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sent:</td>
                            <td>
                                @if($formRequest->sent_at)
                                    {{ formatDateTimeUk($formRequest->sent_at) }}
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Opened:</td>
                            <td>
                                @if($formRequest->opened_at)
                                    {{ formatDateTimeUk($formRequest->opened_at) }}
                                @else
                                    <span class="text-muted">Not yet opened</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Completed:</td>
                            <td>
                                @if($formRequest->completed_at)
                                    <span class="text-success">
                                        {{ formatDateTimeUk($formRequest->completed_at) }}
                                    </span>
                                @else
                                    <span class="text-muted">Not yet completed</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted">Expires:</td>
                            <td>
                                @if($formRequest->expires_at)
                                    @if($formRequest->isExpired())
                                        <span class="text-danger">
                                            Expired {{ formatDateUk($formRequest->expires_at) }}
                                        </span>
                                    @else
                                        {{ formatDateUk($formRequest->expires_at) }}
                                    @endif
                                @else
                                    <span class="text-muted">No expiry</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                @if(!$formRequest->isCompleted() && !$formRequest->isExpired())
                    <div class="card-footer bg-white">
                        <form action="{{ route('admin.form-requests.resend', $formRequest) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-sm"
                                    onclick="return confirm('Resend this form request?')">
                                <i class="fas fa-paper-plane me-1"></i>Resend Form
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            @if($formRequest->notes)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-sticky-note me-2"></i>Notes</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $formRequest->notes }}</p>
                    </div>
                </div>
            @endif

            @if($formRequest->generatedDocument)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Linked Document</h5>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('admin.generated-documents.show', $formRequest->generatedDocument) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye me-1"></i>View Document
                        </a>
                    </div>
                </div>
            @endif
        </div>

        <!-- Submitted Data Card -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-list-alt me-2"></i>Submitted Form Data</h5>
                </div>
                <div class="card-body">
                    @if($formRequest->isCompleted() && $formRequest->form_data)
                        @php
                            $formData = is_array($formRequest->form_data)
                                ? $formRequest->form_data
                                : json_decode($formRequest->form_data, true);
                        @endphp

                        @if(is_array($formData) && count($formData) > 0)
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th style="width: 30%;">Field</th>
                                            <th>Response</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($formData as $fieldName => $value)
                                            <tr>
                                                <td class="fw-semibold">
                                                    {{ ucwords(str_replace(['_', '-'], ' ', $fieldName)) }}
                                                </td>
                                                <td>
                                                    @if(is_array($value))
                                                        {{ implode(', ', $value) }}
                                                    @elseif(str_starts_with($value, 'data:image/'))
                                                        <img src="{{ $value }}" alt="Signature" class="img-fluid" style="max-height: 100px; border: 1px solid #ddd; padding: 5px;">
                                                    @elseif(strlen($value) > 200)
                                                        <div class="text-wrap" style="white-space: pre-wrap;">{{ $value }}</div>
                                                    @elseif($value === '1' || $value === 1)
                                                        <span class="badge bg-success">Yes</span>
                                                    @elseif($value === '0' || $value === 0 || $value === '')
                                                        <span class="badge bg-secondary">No</span>
                                                    @else
                                                        {{ $value ?: '-' }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                <p class="text-muted mb-0">No form data available.</p>
                            </div>
                        @endif
                    @elseif($formRequest->status === 'pending')
                        <div class="text-center py-5">
                            <i class="fas fa-hourglass-half fa-3x text-warning mb-3"></i>
                            <h5>Awaiting Response</h5>
                            <p class="text-muted">The patient has not yet opened this form.</p>
                        </div>
                    @elseif($formRequest->status === 'opened')
                        <div class="text-center py-5">
                            <i class="fas fa-edit fa-3x text-info mb-3"></i>
                            <h5>Form Opened</h5>
                            <p class="text-muted">The patient has opened the form but has not yet submitted it.</p>
                            <small class="text-muted">Opened on {{ formatDateTimeUk($formRequest->opened_at) }}</small>
                        </div>
                    @elseif($formRequest->isExpired())
                        <div class="text-center py-5">
                            <i class="fas fa-clock fa-3x text-danger mb-3"></i>
                            <h5>Form Expired</h5>
                            <p class="text-muted">This form request has expired without completion.</p>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted mb-0">No form data available.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
