@extends(auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Send Document')
@section('page-title', 'Send Document via Email')

@section('content')
<div class="fade-in-up">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('staff.generated-documents.show', $generatedDocument) }}" class="text-muted text-decoration-none">
                <i class="fas fa-arrow-left me-2"></i>Back to Document
            </a>
            <h1 class="h3 mb-1 mt-2 text-gray-800 fw-bold">
                <i class="fas fa-envelope me-2 text-primary"></i>Send Document via Email
            </h1>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="doctor-card">
                <div class="doctor-card-body">
                    <!-- Document Info -->
                    <div class="alert alert-info mb-4">
                        <div class="d-flex align-items-center">
                            @if($generatedDocument->template && $generatedDocument->template->type === 'form')
                                <i class="fas fa-edit fa-2x me-3 text-success"></i>
                            @else
                                <i class="fas fa-file-pdf fa-2x me-3 text-danger"></i>
                            @endif
                            <div>
                                <strong>{{ $generatedDocument->title }}</strong>
                                @if($generatedDocument->template && $generatedDocument->template->type === 'form')
                                    <span class="badge bg-success ms-2">Fillable Form</span>
                                @endif
                                <br>
                                <small>
                                    Patient: {{ $generatedDocument->patient->full_name ?? 'Unknown' }}
                                    | Created: {{ $generatedDocument->created_at->format('M d, Y') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    @if($generatedDocument->template && $generatedDocument->template->type === 'form')
                    <div class="alert alert-success mb-4">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Fillable Form:</strong> This form will be sent as a link. The recipient can fill it out online and submit it back to you.
                    </div>
                    @endif

                    <form action="{{ route('staff.generated-documents.send', $generatedDocument) }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label for="email" class="form-label">Recipient Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror"
                                   id="email" name="email"
                                   value="{{ old('email', $generatedDocument->patient->email ?? '') }}"
                                   placeholder="Enter recipient email address" required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($generatedDocument->patient && $generatedDocument->patient->email)
                                <small class="text-muted">
                                    Patient's email pre-filled: {{ $generatedDocument->patient->email }}
                                </small>
                            @endif
                        </div>

                        <div class="mb-4">
                            <label for="subject" class="form-label">Email Subject</label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                   id="subject" name="subject"
                                   value="{{ old('subject', 'Document: ' . $generatedDocument->title) }}"
                                   placeholder="Email subject line">
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="message" class="form-label">Additional Message</label>
                            <textarea class="form-control @error('message') is-invalid @enderror"
                                      id="message" name="message" rows="4"
                                      placeholder="Add a personal message to include in the email (optional)">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This message will be included in the email body.</small>
                        </div>

                        <div class="mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    @if($generatedDocument->template && $generatedDocument->template->type === 'form')
                                    <h6 class="card-title">
                                        <i class="fas fa-link me-2"></i>What Will Be Sent
                                    </h6>
                                    <p class="mb-0">
                                        <i class="fas fa-edit text-success me-2"></i>
                                        A secure link to fill out the form online
                                    </p>
                                    @else
                                    <h6 class="card-title">
                                        <i class="fas fa-paperclip me-2"></i>Attachment
                                    </h6>
                                    <p class="mb-0">
                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                        {{ $generatedDocument->file_name }}
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-paper-plane me-2"></i>Send Email
                            </button>
                            <a href="{{ route('staff.generated-documents.show', $generatedDocument) }}"
                               class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
