@extends('admin.layouts.app')

@section('title', 'Send Document - ' . $generatedDocument->title)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.generated-documents.index') }}">Generated Documents</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.generated-documents.show', $generatedDocument) }}">{{ Str::limit($generatedDocument->title, 20) }}</a></li>
    <li class="breadcrumb-item active">Send</li>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-envelope me-2 text-primary"></i>Send Document via Email
                    </h5>
                </div>
                <div class="card-body">
                    <!-- Document Info -->
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-file-pdf fa-2x me-3 text-danger"></i>
                            <div>
                                <strong>{{ $generatedDocument->title }}</strong>
                                <br>
                                <small>
                                    Patient: {{ $generatedDocument->patient->full_name ?? 'Unknown' }}
                                    | Created: {{ $generatedDocument->created_at->format('M d, Y') }}
                                </small>
                            </div>
                        </div>
                    </div>

                    <form action="{{ route('admin.generated-documents.send', $generatedDocument) }}" method="POST">
                        @csrf

                        <!-- Email Address -->
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

                        <!-- Subject -->
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

                        <!-- Message -->
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

                        <!-- Preview -->
                        <div class="mb-4">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <h6 class="card-title">
                                        <i class="fas fa-paperclip me-2"></i>Attachment
                                    </h6>
                                    <p class="mb-0">
                                        <i class="fas fa-file-pdf text-danger me-2"></i>
                                        {{ $generatedDocument->file_name }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>Send Email
                            </button>
                            <a href="{{ route('admin.generated-documents.show', $generatedDocument) }}"
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
