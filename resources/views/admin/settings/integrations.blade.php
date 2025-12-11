@extends('admin.layouts.app')

@section('title', 'External Integrations')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">Integrations</li>
@endsection

@push('styles')
@include('admin.shared.styles')
<style>
    .api-key-input {
        font-family: monospace;
    }
    .integration-card {
        border-left: 4px solid #6366f1;
        transition: all 0.3s ease;
    }
    .integration-card:hover {
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .integration-card .card-header {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-bottom: 1px solid #e2e8f0;
    }
    .integration-logo {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        font-size: 1.25rem;
    }
    .status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-plug me-2 text-primary"></i>External Integrations</h1>
        <p class="page-subtitle text-muted">Configure API keys and external service integrations</p>
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

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Please fix the following errors:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('admin.settings.integrations.update') }}" id="integrationsForm">
        @csrf

        <div class="row">
            <!-- TinyMCE Integration -->
            <div class="col-lg-6 mb-4">
                <div class="card integration-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="integration-logo bg-primary text-white me-3">
                                <i class="fas fa-edit"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">TinyMCE Editor</h5>
                                <small class="text-muted">Rich text editor for forms and templates</small>
                            </div>
                        </div>
                        @if(!empty($settings['tinymce_api_key']))
                            <span class="badge bg-success status-badge"><i class="fas fa-check me-1"></i>Configured</span>
                        @else
                            <span class="badge bg-warning text-dark status-badge"><i class="fas fa-exclamation me-1"></i>Not Set</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="tinymce_api_key" class="form-label">
                                <i class="fas fa-key me-1"></i>API Key
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control api-key-input"
                                       id="tinymce_api_key"
                                       name="tinymce_api_key"
                                       value="{{ old('tinymce_api_key', $settings['tinymce_api_key'] ?? '') }}"
                                       placeholder="Enter your TinyMCE API key">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('tinymce_api_key')">
                                    <i class="fas fa-eye" id="tinymce_api_key_icon"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Get your free API key from <a href="https://www.tiny.cloud/get-tiny/" target="_blank" rel="noopener">tiny.cloud</a>
                            </div>
                        </div>
                        <div class="alert alert-info mb-0">
                            <small>
                                <i class="fas fa-lightbulb me-1"></i>
                                TinyMCE is used for rich text editing in document templates, email composition, and form builders.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google Maps Integration -->
            <div class="col-lg-6 mb-4">
                <div class="card integration-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="integration-logo bg-danger text-white me-3">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Google Maps</h5>
                                <small class="text-muted">Location services and mapping</small>
                            </div>
                        </div>
                        @if(!empty($settings['google_maps_api_key']))
                            <span class="badge bg-success status-badge"><i class="fas fa-check me-1"></i>Configured</span>
                        @else
                            <span class="badge bg-secondary status-badge"><i class="fas fa-minus me-1"></i>Optional</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="google_maps_api_key" class="form-label">
                                <i class="fas fa-key me-1"></i>API Key
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control api-key-input"
                                       id="google_maps_api_key"
                                       name="google_maps_api_key"
                                       value="{{ old('google_maps_api_key', $settings['google_maps_api_key'] ?? '') }}"
                                       placeholder="Enter your Google Maps API key">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('google_maps_api_key')">
                                    <i class="fas fa-eye" id="google_maps_api_key_icon"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Get your API key from <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">Google Cloud Console</a>
                            </div>
                        </div>
                        <div class="alert alert-secondary mb-0">
                            <small>
                                <i class="fas fa-lightbulb me-1"></i>
                                Used for displaying clinic locations and patient address verification. (Optional)
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Google reCAPTCHA Integration -->
            <div class="col-lg-6 mb-4">
                <div class="card integration-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="integration-logo bg-success text-white me-3">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Google reCAPTCHA</h5>
                                <small class="text-muted">Bot protection for forms</small>
                            </div>
                        </div>
                        @if(!empty($settings['recaptcha_site_key']) && !empty($settings['recaptcha_secret_key']))
                            <span class="badge bg-success status-badge"><i class="fas fa-check me-1"></i>Configured</span>
                        @else
                            <span class="badge bg-secondary status-badge"><i class="fas fa-minus me-1"></i>Optional</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="recaptcha_site_key" class="form-label">
                                <i class="fas fa-globe me-1"></i>Site Key
                            </label>
                            <input type="text"
                                   class="form-control api-key-input"
                                   id="recaptcha_site_key"
                                   name="recaptcha_site_key"
                                   value="{{ old('recaptcha_site_key', $settings['recaptcha_site_key'] ?? '') }}"
                                   placeholder="Enter reCAPTCHA site key">
                        </div>
                        <div class="mb-3">
                            <label for="recaptcha_secret_key" class="form-label">
                                <i class="fas fa-key me-1"></i>Secret Key
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control api-key-input"
                                       id="recaptcha_secret_key"
                                       name="recaptcha_secret_key"
                                       value="{{ old('recaptcha_secret_key', $settings['recaptcha_secret_key'] ?? '') }}"
                                       placeholder="Enter reCAPTCHA secret key">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('recaptcha_secret_key')">
                                    <i class="fas fa-eye" id="recaptcha_secret_key_icon"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Get keys from <a href="https://www.google.com/recaptcha/admin" target="_blank" rel="noopener">Google reCAPTCHA Admin</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Whereby Video Consultation Integration -->
            <div class="col-lg-6 mb-4">
                <div class="card integration-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="integration-logo bg-purple text-white me-3" style="background: #6C63FF;">
                                <i class="fas fa-video"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Whereby Video Consultation</h5>
                                <small class="text-muted">Auto-generate video meeting rooms</small>
                            </div>
                        </div>
                        @if(($settings['whereby_enabled'] ?? '0') === '1' && !empty($settings['whereby_api_key']))
                            <span class="badge bg-success status-badge"><i class="fas fa-check me-1"></i>Active</span>
                        @elseif(!empty($settings['whereby_api_key']))
                            <span class="badge bg-warning text-dark status-badge"><i class="fas fa-pause me-1"></i>Disabled</span>
                        @else
                            <span class="badge bg-secondary status-badge"><i class="fas fa-minus me-1"></i>Not Configured</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="whereby_enabled" name="whereby_enabled" value="1"
                                    {{ old('whereby_enabled', $settings['whereby_enabled'] ?? '0') === '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="whereby_enabled">
                                    <strong>Enable Whereby Integration</strong>
                                </label>
                            </div>
                            <div class="form-text">When enabled, online appointments will automatically get a Whereby meeting room.</div>
                        </div>
                        <div class="mb-3">
                            <label for="whereby_api_key" class="form-label">
                                <i class="fas fa-key me-1"></i>API Key <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control api-key-input"
                                       id="whereby_api_key"
                                       name="whereby_api_key"
                                       value="{{ old('whereby_api_key', $settings['whereby_api_key'] ?? '') }}"
                                       placeholder="Enter your Whereby API key">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('whereby_api_key')">
                                    <i class="fas fa-eye" id="whereby_api_key_icon"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Get your API key from <a href="https://whereby.com/org/api-keys" target="_blank" rel="noopener">Whereby Dashboard</a>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="whereby_room_prefix" class="form-label">
                                <i class="fas fa-tag me-1"></i>Room Name Prefix
                            </label>
                            <input type="text"
                                   class="form-control"
                                   id="whereby_room_prefix"
                                   name="whereby_room_prefix"
                                   value="{{ old('whereby_room_prefix', $settings['whereby_room_prefix'] ?? 'consultation') }}"
                                   placeholder="consultation"
                                   maxlength="39">
                            <div class="form-text">Prefix for room names (e.g., "consultation" creates "consultation-abc123")</div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="whereby_room_mode" class="form-label">
                                    <i class="fas fa-users me-1"></i>Room Mode
                                </label>
                                <select class="form-select" id="whereby_room_mode" name="whereby_room_mode">
                                    <option value="normal" {{ old('whereby_room_mode', $settings['whereby_room_mode'] ?? 'normal') === 'normal' ? 'selected' : '' }}>
                                        Normal (Up to 4 participants)
                                    </option>
                                    <option value="group" {{ old('whereby_room_mode', $settings['whereby_room_mode'] ?? 'normal') === 'group' ? 'selected' : '' }}>
                                        Group (4+ participants)
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label d-block">
                                    <i class="fas fa-lock me-1"></i>Room Lock
                                </label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="whereby_rooms_locked" name="whereby_rooms_locked" value="1"
                                        {{ old('whereby_rooms_locked', $settings['whereby_rooms_locked'] ?? '1') === '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="whereby_rooms_locked">Lock rooms by default</label>
                                </div>
                            </div>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="testWherebyBtn" onclick="testWherebyConnection()">
                                <i class="fas fa-plug me-1"></i>Test Connection
                            </button>
                            <span id="wherebyTestResult" class="align-self-center small"></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Push Notifications Integration -->
            <div class="col-lg-6 mb-4">
                <div class="card integration-card h-100">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="integration-logo bg-info text-white me-3">
                                <i class="fas fa-bell"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">Push Notifications</h5>
                                <small class="text-muted">OneSignal / Firebase Cloud Messaging</small>
                            </div>
                        </div>
                        @if(!empty($settings['onesignal_app_id']))
                            <span class="badge bg-success status-badge"><i class="fas fa-check me-1"></i>Configured</span>
                        @else
                            <span class="badge bg-secondary status-badge"><i class="fas fa-minus me-1"></i>Optional</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="onesignal_app_id" class="form-label">
                                <i class="fas fa-fingerprint me-1"></i>OneSignal App ID
                            </label>
                            <input type="text"
                                   class="form-control api-key-input"
                                   id="onesignal_app_id"
                                   name="onesignal_app_id"
                                   value="{{ old('onesignal_app_id', $settings['onesignal_app_id'] ?? '') }}"
                                   placeholder="Enter OneSignal App ID">
                        </div>
                        <div class="mb-3">
                            <label for="onesignal_rest_api_key" class="form-label">
                                <i class="fas fa-key me-1"></i>REST API Key
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       class="form-control api-key-input"
                                       id="onesignal_rest_api_key"
                                       name="onesignal_rest_api_key"
                                       value="{{ old('onesignal_rest_api_key', $settings['onesignal_rest_api_key'] ?? '') }}"
                                       placeholder="Enter OneSignal REST API key">
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword('onesignal_rest_api_key')">
                                    <i class="fas fa-eye" id="onesignal_rest_api_key_icon"></i>
                                </button>
                            </div>
                            <div class="form-text">
                                <i class="fas fa-info-circle me-1"></i>
                                Get your keys from <a href="https://onesignal.com/" target="_blank" rel="noopener">OneSignal Dashboard</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="card mt-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <a href="{{ route('admin.settings.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Settings
                        </a>
                    </div>
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Save Integration Settings
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + '_icon');

    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function testWherebyConnection() {
    const btn = document.getElementById('testWherebyBtn');
    const result = document.getElementById('wherebyTestResult');
    const apiKey = document.getElementById('whereby_api_key').value;

    if (!apiKey) {
        result.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Please enter an API key first</span>';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Testing...';
    result.innerHTML = '';

    fetch('{{ route("admin.settings.whereby.test") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ api_key: apiKey })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            result.innerHTML = '<span class="text-success"><i class="fas fa-check-circle me-1"></i>' + data.message + '</span>';
        } else {
            result.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>' + data.message + '</span>';
        }
    })
    .catch(error => {
        result.innerHTML = '<span class="text-danger"><i class="fas fa-times-circle me-1"></i>Connection error</span>';
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-plug me-1"></i>Test Connection';
    });
}
</script>
@endpush
