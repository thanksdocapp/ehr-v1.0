{{-- 
    Staff Menu Item Partial
    Renders a single staff sidebar menu item based on menu_key
    @param array $item - Menu item with menu_key, label, icon
--}}

@php
    $menuKey = $item['menu_key'] ?? '';
    $label = $item['label'] ?? '';
    $icon = $item['icon'] ?? 'fa-circle';
    $isAdmin = auth()->user()->is_admin ?? false;
    $userRole = auth()->user()->role ?? 'staff';
@endphp

@php
    // Check if 2FA forced setup is active
    $isForced2FASetup = isset($isForced2FASetup) ? $isForced2FASetup : false;
    if (!$isForced2FASetup && auth()->check()) {
        $user = auth()->user();
        $twoFactorService = app(\App\Services\TwoFactorAuthService::class);
        $isForced2FASetup = $twoFactorService->isRequired($user) && !$twoFactorService->requiresTwoFactor($user);
    }
@endphp

@switch($menuKey)
    @case('dashboard')
        <div class="nav-item">
            <a href="{{ route('staff.dashboard') }}" 
               class="nav-link {{ request()->routeIs('staff.dashboard') ? 'active' : '' }} {{ $isForced2FASetup ? 'disabled' : '' }}"
               @if($isForced2FASetup) onclick="event.preventDefault(); alert('Navigation is locked. Please complete 2FA setup first.'); return false;" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" @endif>
                <i class="nav-icon fas {{ $icon }}"></i>
                <span class="nav-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('patients')
        <div class="nav-item">
            <a href="{{ route('staff.patients.index') }}" 
               class="nav-link {{ request()->routeIs('staff.patients.*') ? 'active' : '' }} {{ $isForced2FASetup ? 'disabled' : '' }}"
               @if($isForced2FASetup) onclick="event.preventDefault(); alert('Navigation is locked. Please complete 2FA setup first.'); return false;" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" @endif>
                <i class="nav-icon fas {{ $icon }}"></i>
                <span class="nav-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('appointments')
        <div class="nav-item">
            <a href="{{ route('staff.appointments.index') }}" 
               class="nav-link {{ request()->routeIs('staff.appointments.*') ? 'active' : '' }} {{ $isForced2FASetup ? 'disabled' : '' }}"
               @if($isForced2FASetup) onclick="event.preventDefault(); alert('Navigation is locked. Please complete 2FA setup first.'); return false;" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" @endif>
                <i class="nav-icon fas {{ $icon }}"></i>
                <span class="nav-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('medical-records')
        <div class="nav-item">
            <a href="{{ route('staff.medical-records.index') }}" 
               class="nav-link {{ request()->routeIs('staff.medical-records.*') ? 'active' : '' }} {{ $isForced2FASetup ? 'disabled' : '' }}"
               @if($isForced2FASetup) onclick="event.preventDefault(); alert('Navigation is locked. Please complete 2FA setup first.'); return false;" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" @endif>
                <i class="nav-icon fas {{ $icon }}"></i>
                <span class="nav-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('prescriptions')
        <div class="nav-item">
            <a href="{{ route('staff.prescriptions.index') }}" 
               class="nav-link {{ request()->routeIs('staff.prescriptions.*') ? 'active' : '' }} {{ $isForced2FASetup ? 'disabled' : '' }}"
               @if($isForced2FASetup) onclick="event.preventDefault(); alert('Navigation is locked. Please complete 2FA setup first.'); return false;" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" @endif>
                <i class="nav-icon fas {{ $icon }}"></i>
                <span class="nav-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('lab-reports')
        <div class="nav-item">
            <a href="{{ route('staff.lab-reports.index') }}" 
               class="nav-link {{ request()->routeIs('staff.lab-reports.*') ? 'active' : '' }} {{ $isForced2FASetup ? 'disabled' : '' }}"
               @if($isForced2FASetup) onclick="event.preventDefault(); alert('Navigation is locked. Please complete 2FA setup first.'); return false;" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" @endif>
                <i class="nav-icon fas {{ $icon }}"></i>
                <span class="nav-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('alerts')
        <div class="nav-item">
            <a href="{{ route('staff.alerts.index') }}" 
               class="nav-link {{ request()->routeIs('staff.alerts.*') ? 'active' : '' }} {{ $isForced2FASetup ? 'disabled' : '' }}"
               @if($isForced2FASetup) onclick="event.preventDefault(); alert('Navigation is locked. Please complete 2FA setup first.'); return false;" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" @endif>
                <i class="nav-icon fas {{ $icon }}"></i>
                <span class="nav-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('document-templates')
        <div class="nav-item">
            <a href="{{ route('staff.document-templates.index') }}" 
               class="nav-link {{ request()->routeIs('staff.document-templates.*') || request()->routeIs('staff.patients.documents.*') ? 'active' : '' }} {{ $isForced2FASetup ? 'disabled' : '' }}"
               @if($isForced2FASetup) onclick="event.preventDefault(); alert('Navigation is locked. Please complete 2FA setup first.'); return false;" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" @endif>
                <i class="nav-icon fas {{ $icon }}"></i>
                <span class="nav-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @case('billing')
        <div class="nav-item">
            <a href="{{ route('staff.billing.index') }}" 
               class="nav-link {{ request()->routeIs('staff.billing.*') ? 'active' : '' }} {{ $isForced2FASetup ? 'disabled' : '' }}"
               @if($isForced2FASetup) onclick="event.preventDefault(); alert('Navigation is locked. Please complete 2FA setup first.'); return false;" style="opacity: 0.5; cursor: not-allowed; pointer-events: none;" @endif>
                <i class="nav-icon fas {{ $icon }}"></i>
                <span class="nav-text">{{ $label }}</span>
            </a>
        </div>
        @break

    @default
        {{-- Skip custom menu items - they're handled in Quick Links section --}}
        @php
            $isCustomItem = (isset($item['is_custom']) && $item['is_custom']) || str_starts_with($menuKey, 'custom-');
        @endphp
        @if(!$isCustomItem && ($isAdmin || \App\Models\RoleMenuVisibility::isVisible($userRole, 'staff', $menuKey)))
        <div class="nav-item">
            <a href="#" class="nav-link">
                <i class="nav-icon fas {{ $icon }}"></i>
                <span class="nav-text">{{ $label }}</span>
            </a>
        </div>
        @endif
@endswitch

