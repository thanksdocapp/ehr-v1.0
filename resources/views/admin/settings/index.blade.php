@extends('admin.layouts.app')

@section('title', 'Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@section('content')
<div class="fade-in">
    <!-- Modern Page Header -->
    <div class="modern-page-header fade-in-up">
        <div class="modern-page-header-content">
            <div>
                <h1 class="modern-page-title">Settings</h1>
                <p class="modern-page-subtitle">Manage your application settings and configurations</p>
            </div>
        </div>
    </div>

    <!-- Modern Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card-modern fade-in-up stagger-1">
                <div class="stat-card-icon" style="background: var(--gradient-primary);">
                    <i class="fas fa-cogs"></i>
                </div>
                <div class="stat-card-number">{{ number_format($stats['total_settings'] ?? 0) }}</div>
                <div class="stat-card-label">Total Settings</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern fade-in-up stagger-2">
                <div class="stat-card-icon" style="background: var(--gradient-success);">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-card-number">{{ number_format($stats['active_features'] ?? 0) }}</div>
                <div class="stat-card-label">Active Features</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern fade-in-up stagger-3">
                <div class="stat-card-icon" style="background: var(--gradient-warning);">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <div class="stat-card-number">{{ number_format($stats['pending_updates'] ?? 0) }}</div>
                <div class="stat-card-label">Pending Updates</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-modern fade-in-up stagger-4">
                <div class="stat-card-icon" style="background: var(--gradient-info);">
                    <i class="fas fa-heartbeat"></i>
                </div>
                <div class="stat-card-number">{{ $stats['system_health'] ?? 0 }}%</div>
                <div class="stat-card-label">System Health</div>
            </div>
        </div>
    </div>

    <!-- Modern Settings Grid -->
    <div class="row g-4">
        <!-- General Settings -->
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('admin.settings.general') }}" class="text-decoration-none">
                <div class="modern-card fade-in-up stagger-1" style="cursor: pointer;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-card-icon" style="background: var(--gradient-primary); width: 60px; height: 60px;">
                            <i class="fas fa-cog"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">General</h5>
                            <p class="text-muted mb-0 small">Application settings and basic configuration</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Email Settings -->
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('admin.email-config') }}" class="text-decoration-none">
                <div class="modern-card fade-in-up stagger-2" style="cursor: pointer;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-card-icon" style="background: var(--gradient-info); width: 60px; height: 60px;">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">Email</h5>
                            <p class="text-muted mb-0 small">Email server and template settings</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- SMS Settings -->
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('admin.sms-config') }}" class="text-decoration-none">
                <div class="modern-card fade-in-up stagger-3" style="cursor: pointer;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-card-icon" style="background: var(--gradient-warning); width: 60px; height: 60px;">
                            <i class="fas fa-sms"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">SMS</h5>
                    <div class="stat-label">SMS gateway and notification settings</div>
                    <div class="stat-note">Redirects to Communication section</div>
                </div>
            </a>
        </div>

        <!-- Security Settings -->
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('admin.settings.security') }}" class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon danger">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                </div>
                <div class="stat-content">
                    <div class="stat-value">Security</div>
                    <div class="stat-label">Authentication and security policies</div>
                </div>
            </a>
        </div>

        <!-- Alert Settings -->
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('admin.settings.alerts') }}" class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon warning">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                </div>
                <div class="stat-content">
                    <div class="stat-value">Patient Alerts</div>
                    <div class="stat-label">Configure alert logging and notifications</div>
                </div>
            </a>
        </div>

        <!-- Maintenance Settings -->
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('admin.settings.maintenance') }}" class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon warning">
                        <i class="fas fa-tools"></i>
                    </div>
                </div>
                <div class="stat-content">
                    <div class="stat-value">Maintenance</div>
                    <div class="stat-label">Maintenance mode and updates</div>
                </div>
            </a>
        </div>

        <!-- Backup Settings -->
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('admin.settings.backup') }}" class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon success">
                        <i class="fas fa-database"></i>
                    </div>
                </div>
                <div class="stat-content">
                    <div class="stat-value">Backup</div>
                    <div class="stat-label">Database backup and restore</div>
                </div>
            </a>
        </div>


        <!-- Role-Based Menu Visibility -->
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('admin.role-menu-visibility.index') }}" class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon success">
                        <i class="fas fa-user-shield"></i>
                    </div>
                </div>
                <div class="stat-content">
                    <div class="stat-value">Role Menu Visibility</div>
                    <div class="stat-label">Configure menu visibility by user role</div>
                </div>
            </a>
        </div>

        <!-- Custom Menu Items -->
        <div class="col-lg-4 col-md-6 mb-4">
            <a href="{{ route('admin.custom-menu-items.index', ['type' => 'staff']) }}" class="stat-card">
                <div class="stat-card-header">
                    <div class="stat-icon info">
                        <i class="fas fa-link"></i>
                    </div>
                </div>
                <div class="stat-content">
                    <div class="stat-value">Custom Menu Items</div>
                    <div class="stat-label">Add custom links to staff sidebar</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="admin-card">
        <div class="card-header">
            <h5 class="card-title">Quick Actions</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <button class="btn btn-outline-primary btn-lg w-100" onclick="clearCache()">
                        <i class="fas fa-broom me-2"></i>
                        Clear Application Cache
                    </button>
                </div>
                <div class="col-md-6 mb-3">
                    <button class="btn btn-outline-warning btn-lg w-100" onclick="optimizeApp()">
                        <i class="fas fa-rocket me-2"></i>
                        Optimize Application
                    </button>
                </div>
                <div class="col-md-6 mb-3">
                    <a href="{{ route('admin.settings.system-info') }}" class="btn btn-outline-info btn-lg w-100">
                        <i class="fas fa-info-circle me-2"></i>
                        System Information
                    </a>
                </div>
                <div class="col-md-6 mb-3">
                    <button class="btn btn-outline-secondary btn-lg w-100" onclick="downloadLogs()">
                        <i class="fas fa-download me-2"></i>
                        Download Logs
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Application Footer -->
    @if(shouldShowPoweredBy())
    <div class="text-center mt-5 py-4" style="border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px;">
        <div style="display: flex; align-items: center; justify-content: center; gap: 10px;">
            <i class="fas fa-hospital" style="color: #e94560;"></i>
            <span>{!! getPoweredByText() !!}</span>
        </div>
        <div class="mt-2" style="font-size: 12px; opacity: 0.8;">
            {{ getCopyrightText() }}
        </div>
    </div>
    @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
    async function clearCache() {
        const confirmed = await confirmAction('Are you sure you want to clear the application cache?', {
            title: 'Clear Cache',
            confirmText: 'Clear Cache',
            icon: 'fas fa-broom',
            confirmClass: 'btn-warning'
        });
        
        if (confirmed) {
            ajaxWithModal({
                url: '{{ route('admin.settings.clear-cache') }}',
                method: 'POST',
                loadingMessage: 'Clearing cache...',
                successMessage: 'Cache cleared successfully!',
                errorMessage: 'Failed to clear cache. Please try again.',
                onSuccess: function(response) {
                    // Optionally reload the page after cache clear
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                }
            });
        }
    }

    async function optimizeApp() {
        const confirmed = await confirmAction('Are you sure you want to optimize the application?', {
            title: 'Optimize Application',
            confirmText: 'Optimize',
            icon: 'fas fa-rocket',
            confirmClass: 'btn-success'
        });
        
        if (confirmed) {
            ajaxWithModal({
                url: '{{ route('admin.settings.optimize') }}',
                method: 'POST',
                loadingMessage: 'Optimizing application...',
                successMessage: 'Application optimized successfully!',
                errorMessage: 'Failed to optimize application. Please try again.'
            });
        }
    }

    async function downloadLogs() {
        const confirmed = await confirmAction('Do you want to download the system logs?', {
            title: 'Download Logs',
            confirmText: 'Download',
            icon: 'fas fa-download',
            confirmClass: 'btn-info'
        });
        
        if (confirmed) {
            showLoading('Preparing logs for download...');
            window.open('{{ route('admin.settings.download-logs') }}', '_blank');
            
            // Hide loading after a short delay
            setTimeout(() => {
                document.querySelectorAll('#loadingModal .modal').forEach(modal => {
                    const instance = bootstrap.Modal.getInstance(modal);
                    if (instance) instance.hide();
                });
            }, 2000);
        }
    }
</script>
@endpush

@push('styles')
<style>
    .stat-note {
        font-size: 0.75rem;
        color: #6c757d;
        margin-top: 5px;
        font-style: italic;
    }
</style>
@endpush

