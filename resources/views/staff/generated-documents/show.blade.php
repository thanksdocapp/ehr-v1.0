@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', $generatedDocument->title)
@section('page-title', 'View Document')

@section('content')
<div class="fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('staff.generated-documents.index') }}" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Documents
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="doctor-card mb-4">
                <div class="doctor-card-header d-flex justify-content-between align-items-center">
                    <h5 class="doctor-card-title mb-0">{{ $generatedDocument->title }}</h5>
                    <span class="badge bg-{{ $generatedDocument->status === 'sent' ? 'success' : ($generatedDocument->status === 'final' ? 'primary' : 'secondary') }}">
                        {{ ucfirst($generatedDocument->status) }}
                    </span>
                </div>
                <div class="doctor-card-body">
                    <div class="border rounded p-4 bg-white" style="min-height: 400px; max-height: 600px; overflow-y: auto;">
                        {!! $generatedDocument->rendered_content !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Patient Info -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-user me-2"></i>Patient
                    </h5>
                </div>
                <div class="doctor-card-body">
                    @if($generatedDocument->patient)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3"
                                 style="width: 50px; height: 50px;">
                                {{ strtoupper(substr($generatedDocument->patient->first_name ?? 'P', 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="mb-0">{{ $generatedDocument->patient->full_name }}</h6>
                                <small class="text-muted">
                                    DOB: {{ $generatedDocument->patient->date_of_birth?->format('d/m/Y') ?? 'N/A' }}
                                </small>
                            </div>
                        </div>
                        <a href="{{ route('staff.patients.show', $generatedDocument->patient) }}"
                           class="btn btn-outline-primary btn-sm w-100">
                            <i class="fas fa-external-link-alt me-1"></i>View Patient
                        </a>
                    @else
                        <p class="text-muted mb-0">Patient information not available</p>
                    @endif
                </div>
            </div>

            <!-- Document Info -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>Document Info
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Template</td>
                            <td class="text-end">{{ $generatedDocument->template->name ?? 'Unknown' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Created</td>
                            <td class="text-end">{{ formatDateTimeUk($generatedDocument->created_at) }}</td>
                        </tr>
                        @if($generatedDocument->sent_at)
                        <tr>
                            <td class="text-muted">Sent</td>
                            <td class="text-end">{{ formatDateTimeUk($generatedDocument->sent_at) }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Sent To</td>
                            <td class="text-end">{{ $generatedDocument->sent_to }}</td>
                        </tr>
                        @endif
                    </table>

                    @if($generatedDocument->notes)
                        <hr>
                        <h6 class="text-muted">Notes</h6>
                        <p class="small mb-0">{{ $generatedDocument->notes }}</p>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0">
                        <i class="fas fa-bolt me-2"></i>Actions
                    </h5>
                </div>
                <div class="doctor-card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('staff.generated-documents.download', $generatedDocument) }}"
                           class="btn btn-success">
                            <i class="fas fa-download me-2"></i>Download PDF
                        </a>

                        @if($generatedDocument->status === 'draft')
                            <form action="{{ route('staff.generated-documents.finalize', $generatedDocument) }}" method="POST">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-check me-2"></i>Finalize Document
                                </button>
                            </form>
                        @endif

                        @if($generatedDocument->status === 'final')
                            <a href="{{ route('staff.generated-documents.send-form', $generatedDocument) }}"
                               class="btn btn-info">
                                <i class="fas fa-envelope me-2"></i>Send via Email
                            </a>
                        @endif

                        @can('delete', $generatedDocument)
                            <form action="{{ route('staff.generated-documents.destroy', $generatedDocument) }}" method="POST"
                                  onsubmit="return confirm('Are you sure you want to delete this document?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger w-100">
                                    <i class="fas fa-trash me-2"></i>Delete Document
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
