{{-- 
    Admin Menu Item Partial
    Renders a single admin sidebar menu item based on menu_key
    @param array $item - Menu item with menu_key, label, icon
--}}

@php
    $menuKey = $item['menu_key'] ?? '';
    $label = $item['label'] ?? '';
    $icon = $item['icon'] ?? 'fa-circle';
    $isAdmin = auth()->user()->is_admin ?? false;
    $userRole = auth()->user()->role ?? 'admin';
@endphp

@switch($menuKey)
    @case('dashboard')
        <div class="menu-item">
            <a href="{{ route('admin.dashboard') }}" class="menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="menu-icon fas {{ $icon }}"></i>
                <span class="menu-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('patient-management')
        <div class="menu-item">
            <div class="dropdown">
                <a href="#" class="menu-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i class="menu-icon fas {{ $icon }}"></i>
                    <span class="menu-text">{{ $label }}</span>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('admin.patients.index') }}">All Patients</a>
                    <a class="dropdown-item" href="{{ route('admin.patients.create') }}">Add New Patient</a>
                    <a class="dropdown-item" href="{{ route('admin.appointments.index') }}">Patient Appointments</a>
                </div>
            </div>
        </div>
        @break

    @case('doctors')
        <div class="menu-item">
            <a href="{{ route('admin.doctors.index') }}" class="menu-link {{ request()->routeIs('admin.doctors.*') ? 'active' : '' }}">
                <i class="menu-icon fas {{ $icon }}"></i>
                <span class="menu-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('appointments')
        <div class="menu-item">
            <a href="{{ route('admin.appointments.index') }}" class="menu-link {{ request()->routeIs('admin.appointments.*') ? 'active' : '' }}">
                <i class="menu-icon fas {{ $icon }}"></i>
                <span class="menu-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('medical-records')
        <div class="menu-item">
            <div class="dropdown">
                <a href="#" class="menu-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i class="menu-icon fas {{ $icon }}"></i>
                    <span class="menu-text">{{ $label }}</span>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('admin.medical-records.index') }}">All Medical Records</a>
                    <a class="dropdown-item" href="{{ route('admin.medical-records.create') }}">Add New Record</a>
                    <a class="dropdown-item" href="{{ route('admin.prescriptions.index') }}">Prescriptions</a>
                    <a class="dropdown-item" href="{{ route('admin.lab-reports.index') }}">Lab Reports</a>
                </div>
            </div>
        </div>
        @break

    @case('billing-management')
        <div class="menu-item">
            <div class="dropdown">
                <a href="#" class="menu-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i class="menu-icon fas {{ $icon }}"></i>
                    <span class="menu-text">{{ $label }}</span>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('admin.billing.index') }}">All Bills</a>
                    <a class="dropdown-item" href="{{ route('admin.billing.create') }}">Create New Bill</a>
                    <a class="dropdown-item" href="{{ route('admin.payment-gateways.index') }}">Payment Gateways</a>
                </div>
            </div>
        </div>
        @break

    @case('departments')
        <div class="menu-item">
            <a href="{{ route('admin.departments.index') }}" class="menu-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">
                <i class="menu-icon fas {{ $icon }}"></i>
                <span class="menu-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('staff-management')
        <div class="menu-item">
            <div class="dropdown">
                <a href="#" class="menu-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i class="menu-icon fas {{ $icon }}"></i>
                    <span class="menu-text">{{ $label }}</span>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('admin.users.index') }}">All Staffs</a>
                    <a class="dropdown-item" href="{{ route('admin.users.create') }}">Create New Staff</a>
                </div>
            </div>
        </div>
        @break

    @case('communication')
        <div class="menu-item">
            <div class="dropdown">
                <a href="#" class="menu-link dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i class="menu-icon fas {{ $icon }}"></i>
                    <span class="menu-text">{{ $label }}</span>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('admin.email-config') }}">Email Services</a>
                    <a class="dropdown-item" href="{{ route('admin.sms-config') }}">SMS Services</a>
                    <a class="dropdown-item" href="{{ route('admin.email-templates.index') }}">Email Templates</a>
                    <a class="dropdown-item" href="{{ route('admin.sms-templates.index') }}">SMS Templates</a>
                </div>
            </div>
        </div>
        @break

    @case('advanced-reports')
        <div class="menu-item">
            <div class="dropdown">
                <a href="#" class="menu-link dropdown-toggle {{ request()->routeIs('admin.advanced-reports.*') ? 'active' : '' }}" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i class="menu-icon fas {{ $icon }}"></i>
                    <span class="menu-text">{{ $label }}</span>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item {{ request()->routeIs('admin.advanced-reports.index') ? 'active' : '' }}" href="{{ route('admin.advanced-reports.index') }}">Reports Dashboard</a>
                    <a class="dropdown-item {{ request()->routeIs('admin.advanced-reports.custom-reports') ? 'active' : '' }}" href="{{ route('admin.advanced-reports.custom-reports') }}">Custom Reports</a>
                    <a class="dropdown-item {{ request()->routeIs('admin.advanced-reports.financial-analytics') ? 'active' : '' }}" href="{{ route('admin.advanced-reports.financial-analytics') }}">Financial Analytics</a>
                    <a class="dropdown-item {{ request()->routeIs('admin.advanced-reports.patient-analytics') ? 'active' : '' }}" href="{{ route('admin.advanced-reports.patient-analytics') }}">Patient Analytics</a>
                    <a class="dropdown-item {{ request()->routeIs('admin.advanced-reports.doctor-analytics') ? 'active' : '' }}" href="{{ route('admin.advanced-reports.doctor-analytics') }}">Doctor Analytics</a>
                    <a class="dropdown-item {{ request()->routeIs('admin.advanced-reports.audit-trail*') ? 'active' : '' }}" href="{{ route('admin.advanced-reports.audit-trail') }}">Audit Trail</a>
                </div>
            </div>
        </div>
        @break

    @case('alerts')
        <div class="menu-item">
            <a href="{{ route('admin.alerts.index') }}" class="menu-link {{ request()->routeIs('admin.alerts.*') ? 'active' : '' }}">
                <i class="menu-icon fas {{ $icon }}"></i>
                <span class="menu-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('document-templates')
        <div class="menu-item">
            <a href="{{ route('admin.document-templates.index') }}" class="menu-link {{ request()->routeIs('admin.document-templates.*') || request()->routeIs('admin.patients.documents.*') ? 'active' : '' }}">
                <i class="menu-icon fas {{ $icon }}"></i>
                <span class="menu-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('system-settings')
        <div class="menu-item">
            <div class="dropdown">
                <a href="#" class="menu-link dropdown-toggle {{ request()->routeIs('admin.settings.*') || request()->routeIs('admin.role-menu-visibility.*') ? 'active' : '' }}" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i class="menu-icon fas {{ $icon }}"></i>
                    <span class="menu-text">{{ $label }}</span>
                </a>
                <div class="dropdown-menu">
                    <a class="dropdown-item {{ request()->routeIs('admin.settings.index') ? 'active' : '' }}" href="{{ route('admin.settings.index') }}">General Settings</a>
                    <a class="dropdown-item {{ request()->routeIs('admin.settings.general') ? 'active' : '' }}" href="{{ route('admin.settings.general') }}">Basic Settings</a>
                    <a class="dropdown-item {{ request()->routeIs('admin.settings.security') ? 'active' : '' }}" href="{{ route('admin.settings.security') }}">Security</a>
                    <a class="dropdown-item {{ request()->routeIs('admin.settings.appearance') ? 'active' : '' }}" href="{{ route('admin.settings.appearance') }}">Appearance</a>
                    <a class="dropdown-item {{ request()->routeIs('admin.settings.maintenance') ? 'active' : '' }}" href="{{ route('admin.settings.maintenance') }}">Maintenance</a>
                    <a class="dropdown-item {{ request()->routeIs('admin.settings.backup') ? 'active' : '' }}" href="{{ route('admin.settings.backup') }}">Backup</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item {{ request()->routeIs('admin.role-menu-visibility.*') ? 'active' : '' }}" href="{{ route('admin.role-menu-visibility.index') }}">Role Menu Visibility</a>
                    <div class="dropdown-divider"></div>
                    <a class="dropdown-item {{ request()->routeIs('admin.settings.system-info') ? 'active' : '' }}" href="{{ route('admin.settings.system-info') }}">System Info</a>
                </div>
            </div>
        </div>
        @break

    @default
        {{-- Unknown menu key - render as simple link if route exists --}}
        @if($isAdmin || \App\Models\RoleMenuVisibility::isVisible($userRole, 'admin', $menuKey))
        <div class="menu-item">
            <a href="#" class="menu-link">
                <i class="menu-icon fas {{ $icon }}"></i>
                <span class="menu-text">{{ $label }}</span>
            </a>
        </div>
        @endif
@endswitch

