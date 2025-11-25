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
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card-enhanced fade-in-up stagger-1">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-cogs"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['total_settings'] ?? 0) }}</div>
                        <div class="stat-label">Total Settings</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-enhanced fade-in-up stagger-2">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['active_features'] ?? 0) }}</div>
                        <div class="stat-label">Active Features</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-enhanced fade-in-up stagger-3">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ number_format($stats['pending_updates'] ?? 0) }}</div>
                        <div class="stat-label">Pending Updates</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card-enhanced fade-in-up stagger-4">
                <div class="stat-card-content">
                    <div class="stat-icon-wrapper">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <div class="stat-info">
                        <div class="stat-number">{{ $stats['system_health'] ?? 0 }}%</div>
                        <div class="stat-label">System Health</div>
                    </div>
                </div>
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
                            <p class="text-muted mb-0 small">SMS gateway and notification settings</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Security Settings -->
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('admin.settings.security') }}" class="text-decoration-none">
                <div class="modern-card fade-in-up stagger-4" style="cursor: pointer;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-card-icon" style="background: var(--gradient-danger); width: 60px; height: 60px;">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">Security</h5>
                            <p class="text-muted mb-0 small">Authentication and security policies</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Alert Settings -->
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('admin.settings.alerts') }}" class="text-decoration-none">
                <div class="modern-card fade-in-up stagger-5" style="cursor: pointer;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-card-icon" style="background: var(--gradient-warning); width: 60px; height: 60px;">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">Patient Alerts</h5>
                            <p class="text-muted mb-0 small">Configure alert logging and notifications</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Maintenance Settings -->
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('admin.settings.maintenance') }}" class="text-decoration-none">
                <div class="modern-card fade-in-up stagger-6" style="cursor: pointer;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-card-icon" style="background: var(--gradient-warning); width: 60px; height: 60px;">
                            <i class="fas fa-tools"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">Maintenance</h5>
                            <p class="text-muted mb-0 small">Maintenance mode and updates</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Backup Settings -->
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('admin.settings.backup') }}" class="text-decoration-none">
                <div class="modern-card fade-in-up stagger-7" style="cursor: pointer;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-card-icon" style="background: var(--gradient-success); width: 60px; height: 60px;">
                            <i class="fas fa-database"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">Backup</h5>
                            <p class="text-muted mb-0 small">Database backup and restore</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Role-Based Menu Visibility -->
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('admin.role-menu-visibility.index') }}" class="text-decoration-none">
                <div class="modern-card fade-in-up stagger-8" style="cursor: pointer;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-card-icon" style="background: var(--gradient-success); width: 60px; height: 60px;">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">Role Menu Visibility</h5>
                            <p class="text-muted mb-0 small">Configure menu visibility by user role</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Custom Menu Items -->
        <div class="col-lg-4 col-md-6">
            <a href="{{ route('admin.custom-menu-items.index', ['type' => 'staff']) }}" class="text-decoration-none">
                <div class="modern-card fade-in-up stagger-9" style="cursor: pointer;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="stat-card-icon" style="background: var(--gradient-info); width: 60px; height: 60px;">
                            <i class="fas fa-link"></i>
                        </div>
                        <div>
                            <h5 class="mb-1 fw-bold">Custom Menu Items</h5>
                            <p class="text-muted mb-0 small">Add custom links to staff sidebar</p>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- Modern Quick Actions -->
    <div class="modern-card mt-4">
        <div class="modern-card-header">
            <h5 class="modern-card-title mb-0">
                <i class="fas fa-bolt"></i>Quick Actions
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <button class="btn-modern btn-modern-outline w-100" onclick="clearCache()">
                        <i class="fas fa-broom"></i>Clear Application Cache
                    </button>
                </div>
                <div class="col-md-6">
                    <button class="btn-modern btn-modern-outline w-100" onclick="optimizeApp()">
                        <i class="fas fa-rocket"></i>Optimize Application
                    </button>
                </div>
                <div class="col-md-6">
                    <a href="{{ route('admin.settings.system-info') }}" class="btn-modern btn-modern-outline w-100 text-decoration-none">
                        <i class="fas fa-info-circle"></i>System Information
                    </a>
                </div>
                <div class="col-md-6">
                    <button class="btn-modern btn-modern-outline w-100" onclick="downloadLogs()">
                        <i class="fas fa-download"></i>Download Logs
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
@endsection

@push('scripts')
<script>
    async function clearCache() {
        try {
            const result = await Swal.fire({
                title: 'Clear Application Cache?',
                text: 'This will clear all cached data including config, routes, and views. The application may be slower on the next request while caches rebuild.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-broom me-2"></i>Clear Cache',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            });
            
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Clearing Cache...',
                    text: 'Please wait while we clear the application cache.',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Make the request
                const response = await fetch('{{ route('admin.settings.clear-cache') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message || 'Cache cleared successfully!',
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        // Reload page after cache clear
                        location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Failed to clear cache');
                }
            }
        } catch (error) {
            console.error('Cache clear error:', error);
            Swal.fire({
                title: 'Error!',
                text: error.message || 'Failed to clear cache. Please try again.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
        }
    }

    async function optimizeApp() {
        try {
            const result = await Swal.fire({
                title: 'Optimize Application?',
                text: 'This will optimize the application by caching configuration, routes, and views for better performance.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-rocket me-2"></i>Optimize',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            });
            
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Optimizing Application...',
                    text: 'This may take a few moments. Please wait...',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Make the request
                const response = await fetch('{{ route('admin.settings.optimize') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    }
                });
                
                const data = await response.json();
                
                if (data.success) {
                    Swal.fire({
                        title: 'Success!',
                        text: data.message || 'Application optimized successfully!',
                        icon: 'success',
                        confirmButtonColor: '#10b981',
                        confirmButtonText: 'OK'
                    });
                } else {
                    throw new Error(data.message || 'Failed to optimize application');
                }
            }
        } catch (error) {
            console.error('Optimize error:', error);
            Swal.fire({
                title: 'Error!',
                text: error.message || 'Failed to optimize application. Please try again.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
        }
    }

    async function downloadLogs() {
        try {
            const result = await Swal.fire({
                title: 'Download System Logs?',
                text: 'This will download the Laravel log file containing system errors and activity logs.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="fas fa-download me-2"></i>Download',
                cancelButtonText: 'Cancel',
                reverseButtons: true
            });
            
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Preparing Logs...',
                    text: 'Please wait while we prepare the log file for download.',
                    icon: 'info',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                // Open download in new window
                const downloadUrl = '{{ route('admin.settings.download-logs') }}';
                window.open(downloadUrl, '_blank');
                
                // Hide loading after a short delay
                setTimeout(() => {
                    Swal.close();
                }, 1500);
            }
        } catch (error) {
            console.error('Download logs error:', error);
            Swal.fire({
                title: 'Error!',
                text: 'Failed to download logs. Please try again.',
                icon: 'error',
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'OK'
            });
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

