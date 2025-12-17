@extends('admin.layouts.app')

@section('title', 'Email Template Seeder')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Email Template Seeder</li>
@endsection

@section('content')
<div class="page-title">
    <h1>🛠️ Email Template Seeder</h1>
    <p class="page-subtitle">Fix missing email templates on shared hosting without SSH access</p>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="admin-card">
            <div class="card-header">
                <h3 class="card-title mb-0">
                    <i class="fas fa-envelope me-2"></i>
                    Email Template Management
                </h3>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>For Shared Hosting Users:</strong> Use this tool to seed email templates when you don't have SSH access to run artisan commands.
                </div>

                <!-- Seed Options -->
                <div class="row g-3 align-items-end mb-4">
                    <div class="col-md-8">
                        <label class="form-label fw-semibold mb-1">Seed mode</label>
                        <select id="seedTemplateSelect" class="form-select">
                            <option value="__all__" selected>All templates (may update existing)</option>
                            <option value="patient_feedback_request">patient_feedback_request (only)</option>
                        </select>
                        <div class="form-text">
                            Select <code>patient_feedback_request</code> to add only the feedback email template without updating other templates.
                        </div>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="button" class="btn btn-outline-primary w-100" id="seedFeedbackOnlyBtn">
                            <i class="fas fa-comment-dots me-2"></i>
                            Seed feedback template only
                        </button>
                    </div>
                </div>
                
                <!-- Action Buttons -->
                <div class="d-flex gap-3 mb-4">
                    <button type="button" class="btn btn-primary" id="seedTemplatesBtn">
                        <i class="fas fa-seedling me-2"></i>
                        Seed Email Templates
                    </button>
                    <button type="button" class="btn btn-warning" id="clearCacheBtn">
                        <i class="fas fa-trash me-2"></i>
                        Clear Cache
                    </button>
                        
                    <button type="button" class="btn btn-danger" id="repairTemplatesBtn">
                        <i class="fas fa-tools me-2"></i>
                        Repair Templates
                    </button>
                    <button type="button" class="btn btn-info" id="runDiagnosticsBtn">
                        <i class="fas fa-stethoscope me-2"></i>
                        Run Diagnostics
                    </button>
                </div>
                
                <!-- Status Message -->
                <div id="statusMessage" class="alert alert-secondary" style="display: none;">
                    <i class="fas fa-spinner fa-spin me-2"></i>
                    Processing...
                </div>
                
                <!-- Results -->
                <div id="resultsContainer" class="mt-4" style="display: none;">
                    <h5>Results</h5>
                    <pre id="resultsContent" class="bg-light p-3 rounded"></pre>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="admin-card">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-chart-line me-2"></i>
                    Current Status
                </h5>
            </div>
            <div class="card-body">
                @if($diagnostics)
                    @php
                        // Convert to array if it's an object
                        $diag = is_array($diagnostics) ? $diagnostics : (array) $diagnostics;
                    @endphp
                    
                    <!-- Database Status -->
                    @if(isset($diag['database']))
                    <div class="status-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Database</span>
                            @if((is_array($diag['database']) ? $diag['database']['status'] : $diag['database']->status ?? null) === 'success')
                                <span class="badge bg-success">✅ Connected</span>
                            @else
                                <span class="badge bg-danger">❌ Failed</span>
                            @endif
                        </div>
                        <small class="text-muted">{{ is_array($diag['database']) ? $diag['database']['message'] : ($diag['database']->message ?? 'Status unknown') }}</small>
                    </div>
                    @endif
                    
                    <!-- Table Status -->
                    @if(isset($diag['table']))
                    <div class="status-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Table</span>
                            @if((is_array($diag['table']) ? $diag['table']['status'] : $diag['table']->status ?? null) === 'success')
                                <span class="badge bg-success">✅ Exists</span>
                            @else
                                <span class="badge bg-danger">❌ Missing</span>
                            @endif
                        </div>
                        <small class="text-muted">{{ is_array($diag['table']) ? $diag['table']['message'] : ($diag['table']->message ?? 'Status unknown') }}</small>
                    </div>
                    @endif
                    
                    <!-- Templates Status -->
                    @if(isset($diag['templates']))
                    <div class="status-item mb-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-semibold">Templates</span>
                            @php
                                $templateStatus = is_array($diag['templates']) ? $diag['templates']['status'] : ($diag['templates']->status ?? null);
                                $templateCount = is_array($diag['templates']) ? ($diag['templates']['count'] ?? 0) : ($diag['templates']->count ?? 0);
                            @endphp
                            @if($templateStatus === 'success')
                                <span class="badge bg-success">✅ {{ $templateCount }} Found</span>
                            @elseif($templateStatus === 'warning')
                                <span class="badge bg-warning">⚠️ None Found</span>
                            @else
                                <span class="badge bg-danger">❌ Error</span>
                            @endif
                        </div>
                        <small class="text-muted">{{ is_array($diag['templates']) ? $diag['templates']['message'] : ($diag['templates']->message ?? 'Status unknown') }}</small>
                    </div>
                    @endif
                    
                    <!-- Environment -->
                    @if(isset($diag['environment']))
                    <div class="status-item">
                        <div class="fw-semibold mb-2">Environment</div>
                        <div class="small text-muted">
                            @php
                                $env = is_array($diag['environment']) ? $diag['environment'] : (array) $diag['environment'];
                            @endphp
                            <div>Environment: <code>{{ $env['app_env'] ?? 'unknown' }}</code></div>
                            <div>Debug: <code>{{ isset($env['app_debug']) ? ($env['app_debug'] ? 'true' : 'false') : 'unknown' }}</code></div>
                            <div>Cache: <code>{{ $env['cache_driver'] ?? 'unknown' }}</code></div>
                            <div>PHP: <code>{{ $env['php_version'] ?? phpversion() }}</code></div>
                        </div>
                    </div>
                    @endif
                @endif
            </div>
        </div>
        
        <!-- Instructions -->
        <div class="admin-card mt-4">
            <div class="card-header">
                <h5 class="card-title mb-0">
                    <i class="fas fa-question-circle me-2"></i>
                    Instructions
                </h5>
            </div>
            <div class="card-body">
                <div class="small">
                    <h6>If Email Templates Not Showing:</h6>
                    <ol>
                        <li class="mb-2">Click <strong>"Seed Email Templates"</strong> to create missing templates</li>
                        <li class="mb-2">Click <strong>"Clear Cache"</strong> to refresh the system</li>
                        <li class="mb-2">Navigate to <strong>Communication → Email Templates</strong></li>
                        <li class="mb-2">Templates should now be visible</li>
                    </ol>
                    
                    <h6 class="mt-3">Common Issues:</h6>
                    <ul>
                        <li class="mb-1">Database connection issues</li>
                        <li class="mb-1">Missing migrations</li>
                        <li class="mb-1">Cached data conflicts</li>
                        <li class="mb-1">Environment differences (local vs production)</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Templates List (if available) -->
@if($diagnostics && isset($diag['templates']))
    @php
        $templatesList = is_array($diag['templates']) ? ($diag['templates']['list'] ?? []) : (isset($diag['templates']->list) ? (array) $diag['templates']->list : []);
    @endphp
    @if(count($templatesList) > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="admin-card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-list me-2"></i>
                        Current Email Templates
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Subject</th>
                                    <th>Category</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($templatesList as $template)
                                    @php
                                        $temp = is_array($template) ? $template : (array) $template;
                                    @endphp
                                    <tr>
                                        <td><code>{{ $temp['name'] ?? 'N/A' }}</code></td>
                                        <td>{{ isset($temp['subject']) ? (strlen($temp['subject']) > 50 ? substr($temp['subject'], 0, 50) . '...' : $temp['subject']) : 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ ucfirst($temp['category'] ?? 'general') }}</span>
                                        </td>
                                        <td>
                                            @if(($temp['status'] ?? '') === 'active')
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-warning">{{ ucfirst($temp['status'] ?? 'unknown') }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
@endif

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const statusMessage = document.getElementById('statusMessage');
    const resultsContainer = document.getElementById('resultsContainer');
    const resultsContent = document.getElementById('resultsContent');
    const seedTemplateSelect = document.getElementById('seedTemplateSelect');
    
    function showStatus(message, type = 'info') {
        statusMessage.innerHTML = `<i class="fas fa-spinner fa-spin me-2"></i>${message}`;
        statusMessage.className = `alert alert-${type}`;
        statusMessage.style.display = 'block';
    }
    
    function hideStatus() {
        statusMessage.style.display = 'none';
    }
    
    function showResults(content) {
        resultsContent.textContent = content;
        resultsContainer.style.display = 'block';
    }
    
    // Seed Templates Button
    document.getElementById('seedTemplatesBtn').addEventListener('click', function() {
        this.disabled = true;
        showStatus('Seeding email templates...');
        
        fetch('{{ route("admin.tools.email-template-seeder.seed") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                template_name: seedTemplateSelect ? seedTemplateSelect.value : '__all__'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showStatus(data.message, 'success');
                showResults(JSON.stringify(data.data, null, 2));
                setTimeout(() => location.reload(), 3000);
            } else {
                showStatus(data.message || 'Error occurred', 'danger');
                if (data.error) {
                    showResults(data.error);
                }
            }
        })
        .catch(error => {
            showStatus('Network error occurred', 'danger');
            showResults(error.toString());
        })
        .finally(() => {
            this.disabled = false;
        });
    });

    // Seed Feedback Only quick button
    const seedFeedbackOnlyBtn = document.getElementById('seedFeedbackOnlyBtn');
    if (seedFeedbackOnlyBtn) {
        seedFeedbackOnlyBtn.addEventListener('click', function () {
            if (seedTemplateSelect) seedTemplateSelect.value = 'patient_feedback_request';
            document.getElementById('seedTemplatesBtn').click();
        });
    }
    
    // Clear Cache Button
    document.getElementById('clearCacheBtn').addEventListener('click', function() {
        this.disabled = true;
        showStatus('Clearing cache...');
        
        fetch('{{ route("admin.tools.email-template-seeder.clear-cache") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showStatus(data.message, 'success');
                setTimeout(() => location.reload(), 2000);
            } else {
                showStatus(data.message || 'Error occurred', 'danger');
            }
        })
        .catch(error => {
            showStatus('Network error occurred', 'danger');
            showResults(error.toString());
        })
        .finally(() => {
            this.disabled = false;
        });
    });
    
    // Run Diagnostics Button
    document.getElementById('runDiagnosticsBtn').addEventListener('click', function() {
        this.disabled = true;
        showStatus('Running diagnostics...');
        
        fetch('{{ route("admin.tools.email-template-seeder.diagnose") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                hideStatus();
                showResults(JSON.stringify(data.data, null, 2));
            } else {
                showStatus(data.message || 'Error occurred', 'danger');
            }
        })
        .catch(error => {
            showStatus('Network error occurred', 'danger');
            showResults(error.toString());
        })
        .finally(() => {
            this.disabled = false;
        });
    });

    // Repair Templates Button
    document.getElementById('repairTemplatesBtn').addEventListener('click', function() {
        this.disabled = true;

        if (!confirm('Are you sure you want to repair email templates? This will set all soft-deleted templates back to active.')) {
            this.disabled = false;
            return;
        }

        showStatus('Repairing email templates...', 'warning');

        fetch('{{ route("admin.tools.email-template-seeder.repair") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showStatus(data.message, 'success');
                showResults(JSON.stringify(data.details, null, 2));
                setTimeout(() => location.reload(), 3000);
            } else {
                showStatus(data.message || 'Error occurred', 'danger');
                if (data.trace) showResults(data.trace);
            }
        })
        .catch(error => {
            showStatus('Network error occurred', 'danger');
            showResults(error.toString());
        })
        .finally(() => {
            this.disabled = false;
        });
    });
});
</script>
@endpush

@push('styles')
<style>
    .admin-card {
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        margin-bottom: 1.5rem;
    }
    
    .status-item {
        padding: 0.75rem 0;
        border-bottom: 1px solid #e9ecef;
    }
    
    .status-item:last-child {
        border-bottom: none;
    }
    
    .btn {
        border-radius: 8px;
        font-weight: 600;
    }
    
    .btn-primary {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
    }
    
    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
    }
    
    .btn-warning {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        border: none;
        color: white;
    }
    
    .btn-info {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        border: none;
        color: white;
    }
    
    .badge {
        font-size: 0.75rem;
    }
    
    pre {
        font-size: 0.85rem;
        max-height: 300px;
        overflow-y: auto;
    }
</style>
@endpush
