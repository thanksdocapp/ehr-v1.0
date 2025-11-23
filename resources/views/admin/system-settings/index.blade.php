@extends('admin.layouts.app')

@section('title', 'System Settings')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0 text-gray-800">System Settings</h1>
            <p class="text-muted">Manage system configurations and preferences</p>
        </div>
    </div>

    <!-- Settings Categories -->
    <div class="row">
        <div class="col-lg-3 mb-4">
            <div class="card bg-light shadow h-100 py-2">
                <div class="card-body">
                    <h5 class="card-title text-primary"><i class="fas fa-cog"></i> General</h5>
                    <p class="card-text">Basic settings like site title, contact information, and timezone.</p>
                    <a href="#general-settings" class="btn btn-outline-primary btn-sm">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 mb-4">
            <div class="card bg-light shadow h-100 py-2">
                <div class="card-body">
                    <h5 class="card-title text-success"><i class="fas fa-envelope"></i> Email</h5>
                    <p class="card-text">SMTP settings, email templates, and notifications.</p>
                    <a href="#email-settings" class="btn btn-outline-success btn-sm">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 mb-4">
            <div class="card bg-light shadow h-100 py-2">
                <div class="card-body">
                    <h5 class="card-title text-warning"><i class="fas fa-lock"></i> Security</h5>
                    <p class="card-text">Password policies, user permissions, and access control.</p>
                    <a href="#security-settings" class="btn btn-outline-warning btn-sm">Manage</a>
                </div>
            </div>
        </div>
        <div class="col-lg-3 mb-4">
            <div class="card bg-light shadow h-100 py-2">
                <div class="card-body">
                    <h5 class="card-title text-info"><i class="fas fa-paint-brush"></i> Appearance</h5>
                    <p class="card-text">Themes, logos, and user interface customization.</p>
                    <a href="#appearance-settings" class="btn btn-outline-info btn-sm">Manage</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Settings Content -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4" id="general-settings">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">General Settings</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label for="siteTitle" class="form-label">Site Title</label>
                            <input type="text" class="form-control" id="siteTitle" value="City Hospital Management">
                        </div>
                        <div class="mb-3">
                            <label for="contactEmail" class="form-label">Contact Email</label>
                            <input type="email" class="form-control" id="contactEmail" value="contact@cityhospital.com">
                        </div>
                        <div class="mb-3">
                            <label for="timezone" class="form-label">Timezone</label>
                            <select class="form-select" id="timezone">
                                <option value="UTC">UTC</option>
                                <option value="America/New_York">America/New York</option>
                                <option value="Europe/London" selected>Europe/London</option>
                            </select>
                        </div>
                        <button type="button" class="btn btn-primary" onclick="saveGeneralSettings()">Save Changes</button>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-4" id="email-settings">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-success">Email Settings</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label for="smtpHost" class="form-label">SMTP Host</label>
                            <input type="text" class="form-control" id="smtpHost" value="smtp.mailtrap.io">
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="smtpPort" class="form-label">SMTP Port</label>
                                <input type="number" class="form-control" id="smtpPort" value="2525">
                            </div>
                            <div class="col-md-6">
                                <label for="smtpEncryption" class="form-label">Encryption</label>
                                <select class="form-select" id="smtpEncryption">
                                    <option value="none">None</option>
                                    <option value="ssl">SSL</option>
                                    <option value="tls" selected>TLS</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="smtpUsername" class="form-label">SMTP Username</label>
                            <input type="text" class="form-control" id="smtpUsername" value="username">
                        </div>
                        <div class="mb-3">
                            <label for="smtpPassword" class="form-label">SMTP Password</label>
                            <input type="password" class="form-control" id="smtpPassword" value="password">
                        </div>
                        <button type="button" class="btn btn-success" onclick="saveEmailSettings()">Save Changes</button>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-4" id="security-settings">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-warning">Security Settings</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label for="passwordPolicy" class="form-label">Password Policy</label>
                            <select class="form-select" id="passwordPolicy">
                                <option value="1">Weak (6 characters)</option>
                                <option value="2">Medium (8 characters, alphanumeric)</option>
                                <option value="3" selected>Strong (12 characters, includes symbols)</option>
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="twoFactorAuth" checked>
                            <label class="form-check-label" for="twoFactorAuth">Enable Two-Factor Authentication</label>
                        </div>
                        <button type="button" class="btn btn-warning" onclick="saveSecuritySettings()">Save Changes</button>
                    </form>
                </div>
            </div>

            <div class="card shadow mb-4" id="appearance-settings">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">Appearance Settings</h6>
                </div>
                <div class="card-body">
                    <form>
                        <div class="mb-3">
                            <label for="theme" class="form-label">Theme</label>
                            <select class="form-select" id="theme">
                                <option value="light">Light</option>
                                <option value="dark" selected>Dark</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="logoUpload" class="form-label">Upload Logo</label>
                            <input type="file" class="form-control" id="logoUpload">
                        </div>
                        <button type="button" class="btn btn-info" onclick="saveAppearanceSettings()">Save Changes</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function saveGeneralSettings() {
        alert('General settings saved successfully!');
    }

    function saveEmailSettings() {
        alert('Email settings saved successfully!');
    }

    function saveSecuritySettings() {
        alert('Security settings saved successfully!');
    }

    function saveAppearanceSettings() {
        alert('Appearance settings saved successfully!');
    }
</script>
@endpush

