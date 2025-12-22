@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Email Details')
@section('page-title', 'Email Details')

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">
                        <i class="fas fa-envelope-open-text me-2 text-primary"></i>Email Details
                    </h1>
                    <p class="text-muted mb-0">View email details and content</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('staff.patient-email.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-2"></i>Back to List
                    </a>
                    <a href="{{ route('staff.patient-email.compose') }}" class="btn btn-doctor-primary">
                        <i class="fas fa-plus me-2"></i>Compose New
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Email Information -->
    <div class="doctor-card mb-4">
        <div class="doctor-card-header">
            <h5 class="doctor-card-title mb-0"><i class="fas fa-info-circle me-2"></i>Email Information</h5>
        </div>
        <div class="doctor-card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-borderless">
                        <tr>
                            <td style="width: 150px;"><strong>Status:</strong></td>
                            <td>
                                @if($emailLog->status === 'sent')
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Sent
                                    </span>
                                @elseif($emailLog->status === 'failed')
                                    <span class="badge bg-danger">
                                        <i class="fas fa-exclamation-circle me-1"></i>Failed
                                    </span>
                                @else
                                    <span class="badge bg-warning">
                                        <i class="fas fa-clock me-1"></i>{{ ucfirst($emailLog->status) }}
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Recipient:</strong></td>
                            <td>
                                @if($emailLog->patient)
                                    <a href="{{ route('staff.patients.show', $emailLog->patient) }}" class="text-decoration-none">
                                        {{ $emailLog->recipient_name }}
                                    </a>
                                @else
                                    {{ $emailLog->recipient_name }}
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Email Address:</strong></td>
                            <td>{{ $emailLog->recipient_email }}</td>
                        </tr>
                        <tr>
                            <td><strong>Subject:</strong></td>
                            <td>{{ $emailLog->subject }}</td>
                        </tr>
                        <tr>
                            <td><strong>Date Sent:</strong></td>
                            <td>
                                {{ $emailLog->created_at->format('F j, Y g:i A') }}
                                @if($emailLog->sent_at)
                                    <br><small class="text-muted">Sent: {{ $emailLog->sent_at->format('F j, Y g:i A') }}</small>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Opened:</strong></td>
                            <td>
                                @if($emailLog->opened_at)
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle me-1"></i>Opened
                                    </span>
                                    <br><small class="text-muted">{{ $emailLog->opened_at->format('F j, Y g:i A') }}</small>
                                @else
                                    <span class="badge bg-secondary">
                                        <i class="fas fa-envelope me-1"></i>Not Opened
                                    </span>
                                @endif
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="col-md-6">
                    @if($emailLog->metadata)
                        <table class="table table-borderless">
                            @if(isset($emailLog->metadata['doctor_name']))
                                <tr>
                                    <td style="width: 150px;"><strong>Sent By:</strong></td>
                                    <td>{{ $emailLog->metadata['doctor_name'] }}</td>
                                </tr>
                            @endif
                            @if(isset($emailLog->metadata['department_name']))
                                <tr>
                                    <td><strong>Department:</strong></td>
                                    <td>{{ $emailLog->metadata['department_name'] }}</td>
                                </tr>
                            @endif
                            @if(isset($emailLog->metadata['clinic_name']))
                                <tr>
                                    <td><strong>Clinic:</strong></td>
                                    <td>{{ $emailLog->metadata['clinic_name'] }}</td>
                                </tr>
                            @endif
                        </table>
                    @endif
                    @if($emailLog->status === 'failed' && $emailLog->error_message)
                        <div class="alert alert-danger mt-3">
                            <strong><i class="fas fa-exclamation-triangle me-2"></i>Error:</strong>
                            <div class="mt-2">{{ $emailLog->error_message }}</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Email Content (Full Layout) -->
    <div class="doctor-card">
        <div class="doctor-card-header">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="doctor-card-title mb-0"><i class="fas fa-envelope me-2"></i>Email Content</h5>
                <a href="{{ route('staff.patient-email.preview', $emailLog->id) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary">
                    <i class="fas fa-up-right-from-square me-2"></i>Open preview
                </a>
            </div>
        </div>
        <div class="doctor-card-body p-0">
            <div class="p-3 p-md-4" style="background: #f5f5f5;">
                @php
                    // Prefer the frozen preview HTML (no tracking pixel). Fall back to sent HTML if needed.
                    $previewHtml = data_get($emailLog->metadata, 'rendered_html_preview')
                        ?? data_get($emailLog->metadata, 'rendered_html');

                    // Safety: strip tracking pixel if we fell back to sent HTML.
                    if (is_string($previewHtml) && $previewHtml !== '') {
                        $previewHtml = preg_replace(
                            '/<img\\b[^>]*\\bsrc\\s*=\\s*([\"\\\'])[^\"\\\']*\\/track\\/email\\/open\\/[^\"\\\']*\\1[^>]*>/i',
                            '',
                            $previewHtml
                        ) ?? $previewHtml;
                    } else {
                        $previewHtml = null;
                    }
                @endphp

                <iframe
                    title="Email Preview"
                    style="width: 100%; border: 0; border-radius: 10px; min-height: 780px; background: #fff;"
                    sandbox=""
                    @if($previewHtml)
                        src="about:blank"
                        srcdoc="{{ e($previewHtml) }}"
                    @else
                        src="{{ route('staff.patient-email.preview', $emailLog->id) }}"
                    @endif
                ></iframe>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
/* email preview styling is fully scoped inside `emails.patient-email-preview` */
</style>
@endpush

@endsection

