@extends('install.layout')

@section('title', 'Environment Setup - Hospital Management System Installation')

@section('content')
<div class="text-center mb-4">
    <i class="fas fa-cogs fa-3x text-primary mb-3"></i>
    <h2 class="step-title">Environment Configuration</h2>
    <p class="text-muted">
        Configure your application settings and environment variables
    </p>
</div>

<form id="environmentForm" action="{{ route('install.process', 'environment') }}" method="POST">
    @csrf
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="app_name" class="form-label">
                    <i class="fas fa-tag me-2"></i>
                    Application Name
                </label>
                <input type="text" 
                       class="form-control" 
                       id="app_name" 
                       name="app_name" 
                       value="Hospital Management System" 
                       required>
                <small class="text-muted">This will be displayed as your site title</small>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="app_url" class="form-label">
                    <i class="fas fa-globe me-2"></i>
                    Application URL
                </label>
                <input type="url" 
                       class="form-control" 
                       id="app_url" 
                       name="app_url" 
                       value="{{ request()->getSchemeAndHttpHost() }}" 
                       required>
                <small class="text-muted">Your site's full URL (including https://)</small>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="app_env" class="form-label">
                    <i class="fas fa-server me-2"></i>
                    Environment
                </label>
                <select class="form-control" id="app_env" name="app_env" required>
                    <option value="production">Production (Live Site)</option>
                    <option value="local">Development (Testing)</option>
                </select>
                <small class="text-muted">Choose 'Production' for live sites</small>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                <label for="log_level" class="form-label">
                    <i class="fas fa-list-alt me-2"></i>
                    Log Level
                </label>
                <select class="form-control" id="log_level" name="log_level" required>
                    <option value="error">Error (Production)</option>
                    <option value="warning">Warning</option>
                    <option value="info">Info</option>
                    <option value="debug">Debug (Development)</option>
                </select>
                <small class="text-muted">Level of detail for application logs</small>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <div class="form-check">
                    <input class="form-check-input" 
                           type="checkbox" 
                           id="app_debug" 
                           name="app_debug" 
                           value="1">
                    <label class="form-check-label" for="app_debug">
                        <i class="fas fa-bug me-2"></i>
                        Enable Debug Mode
                    </label>
                </div>
                <small class="text-muted">Only enable for development. Disable for production sites.</small>
            </div>
        </div>
    </div>
</form>

<div class="mt-4">
    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        <strong>Environment Settings:</strong> These settings configure how your application behaves. 
        You can change most of these later in the admin settings panel.
    </div>
    
    <div class="alert alert-warning">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>Production Settings:</strong> For live sites, ensure you:
        <ul class="mt-2 mb-0">
            <li>Set Environment to "Production"</li>
            <li>Set Log Level to "Error"</li>
            <li>Keep Debug Mode disabled</li>
            <li>Use HTTPS for your application URL</li>
        </ul>
    </div>
</div>

<div class="environment-preview mt-4">
    <h6>
        <i class="fas fa-eye me-2"></i>
        Environment File Preview
    </h6>
    <div class="bg-light p-3 rounded" style="font-family: monospace; font-size: 0.85rem; max-height: 200px; overflow-y: auto;">
        <div id="envPreview">
            APP_NAME="Hospital Management System"<br>
            APP_ENV=production<br>
            APP_DEBUG=false<br>
            APP_URL={{ request()->getSchemeAndHttpHost() }}<br>
            LOG_LEVEL=error<br>
            <span class="text-muted"># Database settings will be configured in the next step</span><br>
            <span class="text-muted"># Additional settings will be auto-generated</span>
        </div>
    </div>
</div>
@endsection

@section('footer')
<div class="text-muted">
    <small>
        <i class="fas fa-info-circle me-1"></i>
        Step 4 of 7 - Environment Configuration
    </small>
</div>
<div>
    <a href="{{ route('install.step', 'permissions') }}" class="btn btn-outline-secondary me-2">
        <i class="fas fa-arrow-left me-2"></i>
        Back
    </a>
    <button type="button" onclick="submitEnvironment()" class="btn btn-primary">
        <i class="fas fa-arrow-right me-2"></i>
        Save & Continue
    </button>
</div>
@endsection

@push('scripts')
<script>
    // Update environment preview in real-time
    function updateEnvPreview() {
        const appName = document.getElementById('app_name').value || 'Hospital Management System';
        const appUrl = document.getElementById('app_url').value || '{{ request()->getSchemeAndHttpHost() }}';
        const appEnv = document.getElementById('app_env').value;
        const appDebug = document.getElementById('app_debug').checked;
        const logLevel = document.getElementById('log_level').value;
        
        const preview = `
            APP_NAME="${appName}"<br>
            APP_ENV=${appEnv}<br>
            APP_DEBUG=${appDebug ? 'true' : 'false'}<br>
            APP_URL=${appUrl}<br>
            LOG_LEVEL=${logLevel}<br>
            <span class="text-muted"># Database settings will be configured in the next step</span><br>
            <span class="text-muted"># Additional settings will be auto-generated</span>
        `;
        
        document.getElementById('envPreview').innerHTML = preview;
    }
    
    // Add event listeners for real-time preview
    document.addEventListener('DOMContentLoaded', function() {
        const inputs = ['app_name', 'app_url', 'app_env', 'app_debug', 'log_level'];
        inputs.forEach(function(inputId) {
            const element = document.getElementById(inputId);
            if (element) {
                element.addEventListener('input', updateEnvPreview);
                element.addEventListener('change', updateEnvPreview);
            }
        });
        
        // Auto-update log level based on environment
        document.getElementById('app_env').addEventListener('change', function() {
            const logLevel = document.getElementById('log_level');
            const appDebug = document.getElementById('app_debug');
            
            if (this.value === 'production') {
                logLevel.value = 'error';
                appDebug.checked = false;
            } else {
                logLevel.value = 'debug';
            }
            updateEnvPreview();
        });
        
        // Initial preview update
        updateEnvPreview();
    });
    
    function submitEnvironment() {
        const form = document.getElementById('environmentForm');
        
        submitForm(form, function(data) {
            clearAlerts();
            
            if (data.success) {
                showAlert('success', data.message);
                setTimeout(function() {
                    window.location.href = "{{ route('install.step', 'database') }}";
                }, 1500);
            } else {
                showAlert('danger', data.message);
            }
        });
    }
</script>
@endpush

@push('styles')
<style>
    .environment-preview {
        border: 1px solid #e2e8f0;
        border-radius: 10px;
        padding: 1rem;
        background: #f8fafc;
    }
    
    .form-check-input:checked {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
    }
    
    .form-control:focus,
    .form-check-input:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(37, 99, 235, 0.25);
    }
</style>
@endpush
