<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $formRequest->template->name ?? ($formRequest->patientDocument->title ?? 'Form') }} - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.0.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #0d6efd;
            --primary-dark: #0b5ed7;
            --success-color: #198754;
            --border-color: #dee2e6;
            --bg-light: #f8f9fa;
        }
        body {
            background-color: #f5f5f5;
            min-height: 100vh;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
        }
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .form-card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .form-header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 24px 30px;
        }
        .form-header h1 {
            margin: 0;
            font-size: 1.5rem;
            font-weight: 600;
            color: #212529;
        }
        .form-header p {
            margin: 8px 0 0;
            color: #6c757d;
        }
        .form-body {
            padding: 30px;
        }
        .patient-info {
            background: var(--bg-light);
            border: 1px solid var(--border-color);
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 24px;
        }
        .patient-info h6 {
            color: #495057;
            margin-bottom: 12px;
            font-weight: 600;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-label {
            font-weight: 500;
            color: #212529;
            margin-bottom: 8px;
        }
        .form-control, .form-select {
            border-radius: 6px;
            border: 1px solid var(--border-color);
            padding: 10px 14px;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.15);
        }
        .required-star {
            color: #dc3545;
        }
        .btn-submit {
            background: var(--primary-color);
            border: none;
            padding: 12px 32px;
            font-size: 1rem;
            border-radius: 6px;
            color: white;
            font-weight: 500;
            transition: background-color 0.15s ease-in-out;
        }
        .btn-submit:hover {
            background: var(--primary-dark);
            color: white;
        }
        .signature-pad {
            border: 1px solid var(--border-color);
            border-radius: 6px;
            background: #fafafa;
        }
        .signature-pad canvas {
            width: 100%;
            height: 150px;
            cursor: crosshair;
        }
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        .expires-notice {
            background: #fff3cd;
            border: 1px solid #ffecb5;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            color: #664d03;
        }
        .clinic-branding {
            text-align: center;
            padding: 16px;
            border-top: 1px solid var(--border-color);
            color: #6c757d;
            font-size: 0.85rem;
            background: var(--bg-light);
        }
        .logo-section {
            text-align: center;
            padding: 20px 0;
        }
        .logo-section img {
            max-height: 60px;
            max-width: 200px;
        }
        .section-divider {
            border-top: 1px solid var(--border-color);
            margin: 24px 0;
        }
    </style>
</head>
<body>
    <div class="form-container py-4">
        <div class="form-card">
            @if(config('app.logo'))
            <div class="logo-section">
                <img src="{{ asset('storage/' . config('app.logo')) }}" alt="{{ config('app.name') }}">
            </div>
            @endif

            <div class="form-header">
                <h1><i class="fas fa-file-alt me-2 text-primary"></i>{{ $formRequest->template->name ?? ($formRequest->patientDocument->title ?? 'Form') }}</h1>
                <p>Please complete all required fields below</p>
            </div>

            <div class="form-body">
                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>Please correct the errors below.
                    </div>
                @endif

                @if($formRequest->expires_at)
                    <div class="expires-notice">
                        <i class="fas fa-clock me-2"></i>
                        This form expires on {{ $formRequest->expires_at->format('F d, Y \a\t H:i') }}
                    </div>
                @endif

                <div class="patient-info">
                    <h6><i class="fas fa-user me-2"></i>Patient Information</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Name:</strong> {{ $formRequest->patient->full_name ?? $formRequest->patient->first_name . ' ' . $formRequest->patient->last_name }}
                        </div>
                        <div class="col-md-6">
                            <strong>Date of Birth:</strong> {{ $formRequest->patient->date_of_birth?->format('d/m/Y') ?? 'N/A' }}
                        </div>
                    </div>
                </div>

                <form action="{{ route('forms.submit', $formRequest->token) }}" method="POST" id="patientForm">
                    @csrf

                    @forelse($formFields as $field)
                        <div class="form-group">
                            <label class="form-label" for="{{ $field['name'] }}">
                                {{ $field['label'] }}
                                @if($field['required'])
                                    <span class="required-star">*</span>
                                @endif
                            </label>

                            @switch($field['type'])
                                @case('input')
                                    <input type="{{ $field['input_type'] }}"
                                           class="form-control @error($field['name']) is-invalid @enderror"
                                           id="{{ $field['name'] }}"
                                           name="{{ $field['name'] }}"
                                           value="{{ old($field['name']) }}"
                                           {{ $field['required'] ? 'required' : '' }}>
                                    @break

                                @case('textarea')
                                    <textarea class="form-control @error($field['name']) is-invalid @enderror"
                                              id="{{ $field['name'] }}"
                                              name="{{ $field['name'] }}"
                                              rows="4"
                                              {{ $field['required'] ? 'required' : '' }}>{{ old($field['name']) }}</textarea>
                                    @break

                                @case('select')
                                    <select class="form-select @error($field['name']) is-invalid @enderror"
                                            id="{{ $field['name'] }}"
                                            name="{{ $field['name'] }}"
                                            {{ $field['required'] ? 'required' : '' }}>
                                        <option value="">-- Select --</option>
                                        @foreach($field['options'] as $option)
                                            <option value="{{ trim($option) }}" {{ old($field['name']) == trim($option) ? 'selected' : '' }}>
                                                {{ trim($option) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @break

                                @case('checkbox')
                                    <div class="form-check">
                                        <input type="checkbox"
                                               class="form-check-input @error($field['name']) is-invalid @enderror"
                                               id="{{ $field['name'] }}"
                                               name="{{ $field['name'] }}"
                                               value="1"
                                               {{ old($field['name']) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="{{ $field['name'] }}">
                                            Yes
                                        </label>
                                    </div>
                                    @break

                                @case('radio')
                                    @foreach($field['options'] as $option)
                                        <div class="form-check">
                                            <input type="radio"
                                                   class="form-check-input @error($field['name']) is-invalid @enderror"
                                                   id="{{ $field['name'] }}_{{ Str::slug($option) }}"
                                                   name="{{ $field['name'] }}"
                                                   value="{{ trim($option) }}"
                                                   {{ old($field['name']) == trim($option) ? 'checked' : '' }}
                                                   {{ $field['required'] ? 'required' : '' }}>
                                            <label class="form-check-label" for="{{ $field['name'] }}_{{ Str::slug($option) }}">
                                                {{ trim($option) }}
                                            </label>
                                        </div>
                                    @endforeach
                                    @break

                                @case('signature')
                                    <div class="signature-pad">
                                        <canvas id="signature-canvas-{{ $field['name'] }}"></canvas>
                                        <input type="hidden"
                                               name="{{ $field['name'] }}"
                                               id="{{ $field['name'] }}"
                                               class="@error($field['name']) is-invalid @enderror">
                                        <div class="p-2 border-top">
                                            <button type="button" class="btn btn-sm btn-outline-secondary clear-signature" data-canvas="signature-canvas-{{ $field['name'] }}" data-input="{{ $field['name'] }}">
                                                <i class="fas fa-eraser me-1"></i>Clear
                                            </button>
                                        </div>
                                    </div>
                                    @break
                            @endswitch

                            @error($field['name'])
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    @empty
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            This form has no fillable fields. Please contact the sender if you believe this is an error.
                        </div>
                    @endforelse

                    @if(count($formFields) > 0)
                        <div class="section-divider"></div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-submit">
                                <i class="fas fa-paper-plane me-2"></i>Submit Form
                            </button>
                        </div>
                    @endif
                </form>
            </div>

            <div class="clinic-branding">
                <p class="mb-0">Powered by {{ config('app.name') }}</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize signature pads
            document.querySelectorAll('.signature-pad canvas').forEach(function(canvas) {
                const signaturePad = new SignaturePad(canvas, {
                    backgroundColor: 'rgb(250, 250, 250)'
                });

                // Resize canvas
                function resizeCanvas() {
                    const ratio = Math.max(window.devicePixelRatio || 1, 1);
                    canvas.width = canvas.offsetWidth * ratio;
                    canvas.height = canvas.offsetHeight * ratio;
                    canvas.getContext('2d').scale(ratio, ratio);
                    signaturePad.clear();
                }
                window.addEventListener('resize', resizeCanvas);
                resizeCanvas();

                // Store reference for form submission
                canvas.signaturePad = signaturePad;

                // Update hidden input on change
                signaturePad.addEventListener('endStroke', function() {
                    const inputId = canvas.id.replace('signature-canvas-', '');
                    document.getElementById(inputId).value = signaturePad.toDataURL();
                });
            });

            // Clear signature buttons
            document.querySelectorAll('.clear-signature').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    const canvasId = this.getAttribute('data-canvas');
                    const inputId = this.getAttribute('data-input');
                    const canvas = document.getElementById(canvasId);
                    if (canvas && canvas.signaturePad) {
                        canvas.signaturePad.clear();
                        document.getElementById(inputId).value = '';
                    }
                });
            });

            // Form validation
            document.getElementById('patientForm').addEventListener('submit', function(e) {
                // Check signature fields
                document.querySelectorAll('.signature-pad canvas').forEach(function(canvas) {
                    if (canvas.signaturePad && !canvas.signaturePad.isEmpty()) {
                        const inputId = canvas.id.replace('signature-canvas-', '');
                        document.getElementById(inputId).value = canvas.signaturePad.toDataURL();
                    }
                });
            });
        });
    </script>
</body>
</html>
