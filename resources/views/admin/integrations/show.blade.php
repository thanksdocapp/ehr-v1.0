@extends('admin.layouts.app')

@section('title', $module->name . ' Integration')
@section('page-title', $module->name)

@section('content')
<div class="container-fluid">
    <!-- Back Button -->
    <div class="mb-4">
        <a href="{{ route('admin.integrations.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Integrations
        </a>
    </div>

    <div class="row">
        <!-- Configuration Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-cog me-2 text-primary"></i>Configuration
                    </h5>
                    <span class="badge {{ $module->getStatusBadgeClass() }}">{{ $module->getStatusText() }}</span>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.integrations.update', $module) }}" method="POST" id="configForm">
                        @csrf
                        @method('PUT')

                        <!-- Environment Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Environment</label>
                            <div class="btn-group w-100" role="group">
                                <input type="radio" class="btn-check" name="environment" id="env_sandbox" value="sandbox"
                                       {{ ($module->environment ?? 'sandbox') === 'sandbox' ? 'checked' : '' }}>
                                <label class="btn btn-outline-warning" for="env_sandbox">
                                    <i class="fas fa-flask me-2"></i>Sandbox (Testing)
                                </label>

                                <input type="radio" class="btn-check" name="environment" id="env_production" value="production"
                                       {{ ($module->environment ?? 'sandbox') === 'production' ? 'checked' : '' }}>
                                <label class="btn btn-outline-success" for="env_production">
                                    <i class="fas fa-rocket me-2"></i>Production (Live)
                                </label>
                            </div>
                            <small class="text-muted">Use Sandbox for testing before going live.</small>
                        </div>

                        <hr class="my-4">

                        <!-- Dynamic Config Fields -->
                        <h6 class="text-uppercase text-muted mb-3">API Credentials</h6>

                        @foreach($configFields as $field)
                            <div class="mb-3">
                                <label for="config_{{ $field['name'] }}" class="form-label">
                                    {{ $field['label'] }}
                                    @if($field['required'] ?? false)
                                        <span class="text-danger">*</span>
                                    @endif
                                </label>

                                @if($field['type'] === 'password')
                                    <div class="input-group">
                                        <input type="password"
                                               class="form-control @error('config.'.$field['name']) is-invalid @enderror"
                                               id="config_{{ $field['name'] }}"
                                               name="config[{{ $field['name'] }}]"
                                               value="{{ old('config.'.$field['name'], $module->config[$field['name']] ?? '') }}"
                                               placeholder="{{ $field['placeholder'] ?? '' }}"
                                               {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                        <button class="btn btn-outline-secondary toggle-password" type="button">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                @elseif($field['type'] === 'textarea')
                                    <textarea class="form-control @error('config.'.$field['name']) is-invalid @enderror"
                                              id="config_{{ $field['name'] }}"
                                              name="config[{{ $field['name'] }}]"
                                              rows="3"
                                              placeholder="{{ $field['placeholder'] ?? '' }}"
                                              {{ ($field['required'] ?? false) ? 'required' : '' }}>{{ old('config.'.$field['name'], $module->config[$field['name']] ?? '') }}</textarea>
                                @elseif($field['type'] === 'select')
                                    <select class="form-select @error('config.'.$field['name']) is-invalid @enderror"
                                            id="config_{{ $field['name'] }}"
                                            name="config[{{ $field['name'] }}]"
                                            {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                        <option value="">Select...</option>
                                        @foreach($field['options'] ?? [] as $value => $label)
                                            <option value="{{ $value }}" {{ old('config.'.$field['name'], $module->config[$field['name']] ?? '') == $value ? 'selected' : '' }}>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="{{ $field['type'] ?? 'text' }}"
                                           class="form-control @error('config.'.$field['name']) is-invalid @enderror"
                                           id="config_{{ $field['name'] }}"
                                           name="config[{{ $field['name'] }}]"
                                           value="{{ old('config.'.$field['name'], $module->config[$field['name']] ?? '') }}"
                                           placeholder="{{ $field['placeholder'] ?? '' }}"
                                           {{ ($field['required'] ?? false) ? 'required' : '' }}>
                                @endif

                                @if(isset($field['help']))
                                    <small class="text-muted">{{ $field['help'] }}</small>
                                @endif

                                @error('config.'.$field['name'])
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endforeach

                        <hr class="my-4">

                        <!-- Module Settings -->
                        <h6 class="text-uppercase text-muted mb-3">Module Settings</h6>

                        @if($module->type === 'lab_tests')
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="settings_auto_notify_patient"
                                           name="settings[auto_notify_patient]" value="1"
                                           {{ ($module->settings['auto_notify_patient'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="settings_auto_notify_patient">
                                        Automatically notify patient when results are ready
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="settings_auto_notify_doctor"
                                           name="settings[auto_notify_doctor]" value="1"
                                           {{ ($module->settings['auto_notify_doctor'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="settings_auto_notify_doctor">
                                        Notify doctor when results require review
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="settings_results_require_review"
                                           name="settings[results_require_review]" value="1"
                                           {{ ($module->settings['results_require_review'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="settings_results_require_review">
                                        Results require doctor review before patient access
                                    </label>
                                </div>
                            </div>
                        @elseif($module->type === 'prescriptions')
                            <div class="mb-3">
                                <label class="form-label">Default Delivery Method</label>
                                <select class="form-select" name="settings[default_delivery_method]">
                                    <option value="collection" {{ ($module->settings['default_delivery_method'] ?? 'collection') === 'collection' ? 'selected' : '' }}>
                                        Pharmacy Collection
                                    </option>
                                    <option value="delivery" {{ ($module->settings['default_delivery_method'] ?? '') === 'delivery' ? 'selected' : '' }}>
                                        Home Delivery
                                    </option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="settings_auto_notify_patient"
                                           name="settings[auto_notify_patient]" value="1"
                                           {{ ($module->settings['auto_notify_patient'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="settings_auto_notify_patient">
                                        Notify patient of prescription status updates
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="settings_require_pharmacy_selection"
                                           name="settings[require_pharmacy_selection]" value="1"
                                           {{ ($module->settings['require_pharmacy_selection'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="settings_require_pharmacy_selection">
                                        Require pharmacy selection before submission
                                    </label>
                                </div>
                            </div>
                        @elseif($module->type === 'imaging')
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="settings_auto_notify_patient"
                                           name="settings[auto_notify_patient]" value="1"
                                           {{ ($module->settings['auto_notify_patient'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="settings_auto_notify_patient">
                                        Notify patient of appointment and report updates
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="settings_auto_notify_doctor"
                                           name="settings[auto_notify_doctor]" value="1"
                                           {{ ($module->settings['auto_notify_doctor'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="settings_auto_notify_doctor">
                                        Notify doctor when reports are ready
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="settings_reports_require_review"
                                           name="settings[reports_require_review]" value="1"
                                           {{ ($module->settings['reports_require_review'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="settings_reports_require_review">
                                        Reports require doctor review before patient access
                                    </label>
                                </div>
                            </div>
                            <div class="mb-3">
                                <div class="form-check form-switch">
                                    <input type="checkbox" class="form-check-input" id="settings_allow_patient_booking"
                                           name="settings[allow_patient_booking]" value="1"
                                           {{ ($module->settings['allow_patient_booking'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="settings_allow_patient_booking">
                                        Allow patients to book their own imaging appointments
                                    </label>
                                </div>
                            </div>
                        @endif

                        <hr class="my-4">

                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-outline-primary" id="testConnectionBtn">
                                <i class="fas fa-plug me-2"></i>Test Connection
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Save Configuration
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Webhook Configuration -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-broadcast-tower me-2 text-primary"></i>Webhook Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Configure the following webhook URL in your {{ $module->provider }} dashboard to receive real-time updates:
                    </p>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" id="webhookUrl"
                               value="{{ route('admin.integrations.webhook', $module) }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyWebhookUrl()">
                            <i class="fas fa-copy"></i>
                        </button>
                    </div>
                    <small class="text-muted">
                        <i class="fas fa-info-circle me-1"></i>
                        This URL should be added to your {{ $module->provider }} API settings to receive status updates.
                    </small>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Module Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-info-circle me-2 text-primary"></i>Module Information
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="rounded-circle bg-{{ $module->type === 'lab_tests' ? 'danger' : ($module->type === 'prescriptions' ? 'success' : 'info') }} bg-opacity-10 p-3 me-3">
                            <i class="fas {{ $module->getTypeIcon() }} fa-lg text-{{ $module->type === 'lab_tests' ? 'danger' : ($module->type === 'prescriptions' ? 'success' : 'info') }}"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $module->name }}</h6>
                            <small class="text-muted">{{ $module->getTypeLabel() }}</small>
                        </div>
                    </div>

                    <p class="text-muted small">{{ $module->description }}</p>

                    @if($module->website)
                        <a href="{{ $module->website }}" target="_blank" class="btn btn-sm btn-outline-primary w-100 mb-3">
                            <i class="fas fa-external-link-alt me-2"></i>Visit Provider Website
                        </a>
                    @endif

                    <hr>

                    <h6 class="small text-uppercase text-muted mb-2">Capabilities</h6>
                    <div class="d-flex flex-wrap gap-1">
                        @foreach($module->capabilities ?? [] as $capability)
                            <span class="badge bg-light text-dark">{{ str_replace('_', ' ', ucfirst($capability)) }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Status Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-toggle-on me-2 text-primary"></i>Module Status
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.integrations.toggle-status', $module) }}" method="POST">
                        @csrf
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span>Integration Active</span>
                            <div class="form-check form-switch">
                                <input type="checkbox" class="form-check-input" id="toggleActive"
                                       {{ $module->is_active ? 'checked' : '' }}
                                       {{ !$module->is_configured ? 'disabled' : '' }}
                                       onchange="this.form.submit()">
                            </div>
                        </div>
                    </form>

                    @if(!$module->is_configured)
                        <div class="alert alert-warning py-2 small mb-0">
                            <i class="fas fa-exclamation-triangle me-1"></i>
                            Complete configuration before activating
                        </div>
                    @endif

                    @if($module->last_error_at && $module->last_error_at->isAfter(now()->subHours(24)))
                        <div class="alert alert-danger py-2 small mb-0 mt-2">
                            <i class="fas fa-exclamation-circle me-1"></i>
                            <strong>Recent Error:</strong><br>
                            {{ $module->last_error_message }}
                            <small class="d-block mt-1">{{ $module->last_error_at->diffForHumans() }}</small>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-semibold">
                        <i class="fas fa-history me-2 text-primary"></i>Recent Requests
                    </h5>
                    <a href="{{ route('admin.integrations.requests', $module) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    @if($recentRequests->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                            <p class="text-muted small mb-0">No requests yet</p>
                        </div>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach($recentRequests as $request)
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <small class="fw-semibold">{{ $request->request_type }}</small>
                                            <small class="text-muted d-block">
                                                {{ $request->patient ? $request->patient->full_name : 'N/A' }}
                                            </small>
                                        </div>
                                        <span class="badge {{ $request->getStatusBadgeClass() }}">
                                            {{ $request->getStatusLabel() }}
                                        </span>
                                    </div>
                                    <small class="text-muted">{{ $request->created_at->diffForHumans() }}</small>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Connection Modal -->
<div class="modal fade" id="testConnectionModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Testing Connection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center py-4" id="testConnectionResult">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Testing...</span>
                </div>
                <p class="mt-3 mb-0">Testing connection to {{ $module->provider }}...</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const icon = this.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    });

    // Copy webhook URL
    function copyWebhookUrl() {
        const webhookInput = document.getElementById('webhookUrl');
        webhookInput.select();
        document.execCommand('copy');

        // Show feedback
        const btn = webhookInput.nextElementSibling;
        const originalHTML = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-check text-success"></i>';
        setTimeout(() => {
            btn.innerHTML = originalHTML;
        }, 2000);
    }

    // Test connection
    document.getElementById('testConnectionBtn').addEventListener('click', function() {
        const modal = new bootstrap.Modal(document.getElementById('testConnectionModal'));
        const resultDiv = document.getElementById('testConnectionResult');

        // Show loading state
        resultDiv.innerHTML = `
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Testing...</span>
            </div>
            <p class="mt-3 mb-0">Testing connection to {{ $module->provider }}...</p>
        `;
        modal.show();

        // Get current form values
        const formData = new FormData(document.getElementById('configForm'));

        fetch('{{ route('admin.integrations.test-connection', $module) }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(Object.fromEntries(formData))
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = `
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5 class="text-success">Connection Successful!</h5>
                    <p class="text-muted mb-0">${data.message || 'Successfully connected to the API.'}</p>
                `;
            } else {
                resultDiv.innerHTML = `
                    <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                    <h5 class="text-danger">Connection Failed</h5>
                    <p class="text-muted mb-0">${data.message || 'Unable to connect. Please check your credentials.'}</p>
                `;
            }
        })
        .catch(error => {
            resultDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle fa-3x text-warning mb-3"></i>
                <h5 class="text-warning">Error</h5>
                <p class="text-muted mb-0">An unexpected error occurred. Please try again.</p>
            `;
        });
    });
</script>
@endpush
