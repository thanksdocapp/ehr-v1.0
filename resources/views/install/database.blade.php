@extends('install.layout')

@section('title', 'Database Configuration - ' . $productInfo['name'])

@section('content')
<div class="install-step" data-step="database">
    <div class="step-header text-center">
        <div class="step-icon">
            <i class="fas fa-database text-primary"></i>
        </div>
        <h2>Database Configuration</h2>
        <p class="step-description">Set up your MySQL database connection with automatic import</p>
    </div>

    <!-- Database Information Alert -->
    <div class="alert alert-info mb-4">
        <h5><i class="fas fa-info-circle me-2"></i>Database Requirements</h5>
        <div class="row">
            <div class="col-md-6">
                <ul class="mb-0">
                    <li>MySQL 5.7+ or MariaDB 10.3+</li>
                    <li>Empty database or existing {{ $productInfo['name'] }} database</li>
                    <li>Database user with full privileges</li>
                </ul>
            </div>
            <div class="col-md-6">
                <ul class="mb-0">
                    <li>At least 50MB available space</li>
                    <li>UTF8MB4 charset support</li>
                    <li>InnoDB storage engine</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="database-config-card">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-cog me-2"></i>
                    Database Connection Settings
                </h5>
            </div>
            <div class="card-body">
                <form id="databaseForm" class="needs-validation" novalidate>
                    @csrf
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="db_host" class="form-label">
                                    <i class="fas fa-server me-2"></i>
                                    Database Host <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="db_host" 
                                       name="db_host" 
                                       value="localhost" 
                                       required>
                                <div class="invalid-feedback">
                                    Please enter a valid database host.
                                </div>
                                <small class="text-muted">Usually 'localhost' or an IP address</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="db_port" class="form-label">
                                    <i class="fas fa-plug me-2"></i>
                                    Database Port <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       class="form-control" 
                                       id="db_port" 
                                       name="db_port" 
                                       value="3306" 
                                       min="1" 
                                       max="65535" 
                                       required>
                                <div class="invalid-feedback">
                                    Please enter a valid port number (1-65535).
                                </div>
                                <small class="text-muted">Default MySQL port is 3306</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="db_database" class="form-label">
                                    <i class="fas fa-database me-2"></i>
                                    Database Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="db_database" 
                                       name="db_database" 
                       placeholder="e.g., hospital_db" 
                                       required>
                                <div class="invalid-feedback">
                                    Please enter a database name.
                                </div>
                                <small class="text-muted">Create this database in your hosting panel first</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="db_username" class="form-label">
                                    <i class="fas fa-user me-2"></i>
                                    Database Username <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="db_username" 
                                       name="db_username" 
                                       placeholder="Database username" 
                                       required>
                                <div class="invalid-feedback">
                                    Please enter a database username.
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="db_password" class="form-label">
                                    <i class="fas fa-lock me-2"></i>
                                    Database Password
                                </label>
                                <div class="input-group">
                                    <input type="password" 
                                           class="form-control" 
                                           id="db_password" 
                                           name="db_password" 
                                           placeholder="Database password">
                                    <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('db_password')">
                                        <i class="fas fa-eye" id="db_password_icon"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Leave empty if no password is required</small>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-flask me-2"></i>
                                    Connection Test
                                </label>
                                <button type="button" class="btn btn-outline-info w-100" onclick="testConnection()" id="testBtn">
                                    <i class="fas fa-bolt me-2"></i>
                                    Test Database Connection
                                </button>
                                <div id="connectionResult" class="mt-2"></div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <div class="alert alert-warning">
                                <h6><i class="fas fa-exclamation-triangle me-2"></i>Important Notice</h6>
                                <p class="mb-2">This process will:</p>
                                <ul class="mb-0">
                                    <li>Create all necessary database tables</li>
                                    <li>Import sample data and default settings</li>
                    <li>Set up the Hospital Management System database structure</li>
                                    <li><strong>Replace any existing data</strong> in the database</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="text-center">
                        <button type="submit" class="btn btn-primary btn-lg" id="configureBtn">
                            <i class="fas fa-cogs me-2"></i>
                            Configure Database & Import Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Import Progress Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-database me-2"></i>
                        Database Import Progress
                    </h5>
                </div>
                <div class="modal-body">
                    <div class="import-status">
                        <div class="progress mb-3" style="height: 25px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" 
                                 style="width: 0%" 
                                 id="importProgress">
                                0%
                            </div>
                        </div>
                        
                        <div class="import-steps">
                            <div class="step-item" id="step-connect">
                                <i class="fas fa-spinner fa-spin me-2"></i>
                                <span>Connecting to database...</span>
                            </div>
                            <div class="step-item" id="step-tables">
                                <i class="fas fa-circle me-2 text-muted"></i>
                                <span>Creating database tables...</span>
                            </div>
                            <div class="step-item" id="step-data">
                                <i class="fas fa-circle me-2 text-muted"></i>
                                <span>Importing default data...</span>
                            </div>
                            <div class="step-item" id="step-complete">
                                <i class="fas fa-circle me-2 text-muted"></i>
                                <span>Finalizing database setup...</span>
                            </div>
                        </div>
                        
                        <div class="import-message text-center mt-3">
                            <p class="text-muted mb-0" id="importMessage">
                                Please wait while we set up your database...
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + "_icon");
    
    if (field.type === "password") {
        field.type = "text";
        icon.classList.remove("fa-eye");
        icon.classList.add("fa-eye-slash");
    } else {
        field.type = "password";
        icon.classList.remove("fa-eye-slash");
        icon.classList.add("fa-eye");
    }
}

function testConnection() {
    const testBtn = document.getElementById('testBtn');
    const connectionResult = document.getElementById('connectionResult');
    const originalText = testBtn.innerHTML;
    
    // Get form values
    const dbHost = document.getElementById('db_host').value.trim();
    const dbPort = document.getElementById('db_port').value.trim();
    const dbDatabase = document.getElementById('db_database').value.trim();
    const dbUsername = document.getElementById('db_username').value.trim();
    const dbPassword = document.getElementById('db_password').value;
    
    // Validate required fields
    if (!dbHost || !dbPort || !dbDatabase || !dbUsername) {
        connectionResult.innerHTML = '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle me-2"></i>Please fill in all required fields first.</div>';
        return;
    }
    
    // Update button state
    testBtn.disabled = true;
    testBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Testing Connection...';
    connectionResult.innerHTML = '';
    
    // Get CSRF token from meta tag or form
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                     document.querySelector('input[name="_token"]')?.value || 
                     '{{ csrf_token() }}';
    
    // Prepare form data
    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('db_host', dbHost);
    formData.append('db_port', dbPort);
    formData.append('db_database', dbDatabase);
    formData.append('db_username', dbUsername);
    formData.append('db_password', dbPassword);
    
    // Make AJAX request
    fetch('{{ route('install.process', 'database') }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Test-Only': 'true',
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            connectionResult.innerHTML = '<div class="alert alert-success"><i class="fas fa-check-circle me-2"></i>' + data.message + '</div>';
        } else {
            connectionResult.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>' + data.message + '</div>';
        }
    })
    .catch(error => {
        console.error('Connection test error:', error);
        let errorMessage = 'Connection test failed. ';
        if (error.message.includes('419')) {
            errorMessage += 'Session expired. Please refresh the page and try again.';
        } else if (error.message.includes('500')) {
            errorMessage += 'Server error. Please check your server logs.';
        } else {
            errorMessage += 'Please check your settings and try again.';
        }
        connectionResult.innerHTML = '<div class="alert alert-danger"><i class="fas fa-times-circle me-2"></i>' + errorMessage + '</div>';
    })
    .finally(() => {
        // Restore button state
        testBtn.disabled = false;
        testBtn.innerHTML = originalText;
    });
}

// Handle database form submission
document.addEventListener('DOMContentLoaded', function() {
    const databaseForm = document.getElementById('databaseForm');
    
    if (databaseForm) {
        databaseForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const submitBtn = document.getElementById('configureBtn');
            const originalText = submitBtn.innerHTML;
            
            // Show import modal
            const importModal = new bootstrap.Modal(document.getElementById('importModal'));
            importModal.show();
            
            // Update button state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Configuring Database...';
            
            // Prepare form data
            const formData = new FormData(databaseForm);
            
            // Make AJAX request
            fetch('{{ route('install.process', 'database') }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Update progress to 100%
                    document.getElementById('importProgress').style.width = '100%';
                    document.getElementById('importProgress').textContent = '100%';
                    document.getElementById('importMessage').textContent = 'Database configuration completed successfully!';
                    
                    // Update all steps to completed
                    ['step-connect', 'step-tables', 'step-data', 'step-complete'].forEach(stepId => {
                        const step = document.getElementById(stepId);
                        const icon = step.querySelector('i');
                        icon.className = 'fas fa-check-circle me-2 text-success';
                    });
                    
                    // Redirect after delay
                    setTimeout(() => {
                        window.location.href = '{{ route('install.step', 'admin') }}';
                    }, 2000);
                } else {
                    // Hide modal and show error
                    importModal.hide();
                    alert('Database configuration failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                importModal.hide();
                alert('Database configuration failed: ' + error.message);
            })
            .finally(() => {
                // Restore button state
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            });
        });
    }
});
</script>
@endsection

@section('footer')
<div class="text-muted">
    <small>
        <i class="fas fa-info-circle me-1"></i>
        Step 4 of 5 - Database Configuration
    </small>
</div>
<div>
    <a href="{{ route('install.step', 'requirements') }}" class="btn btn-outline-secondary me-2">
        <i class="fas fa-arrow-left me-2"></i>
        Back to Requirements
    </a>
</div>
@endsection

@push('styles')
<style>
.step-icon {
    font-size: 4rem;
    margin-bottom: 1rem;
}

.database-config-card {
    margin-bottom: 2rem;
}

.step-item {
    padding: 0.5rem 0;
    border-bottom: 1px solid #eee;
}

.step-item:last-child {
    border-bottom: none;
}

.import-steps {
    max-height: 200px;
}

.was-validated .form-control:valid {
    border-color: #198754;
    background-image: url("data:image/svg+xml,%3csvg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 8 8\"%3e%3cpath fill=\"%23198754\" d=\"m2.3 6.73.94-.94 1.44 1.44L7.88 4 7 3.06l-2.2 2.2-.94-.94z\"/%3e%3c/svg%3e");
}
</style>
@endpush