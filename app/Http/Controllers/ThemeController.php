<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ThemeController extends Controller
{
    /**
     * Generate dynamic CSS based on current theme settings
     */
    public function dynamicCss()
    {
        try {
            // Check if application is installed
            if (!\Illuminate\Support\Facades\File::exists(storage_path('installed'))) {
                $colors = $this->getDefaultColors();
            } else {
                // Get current theme colors from appearance settings group
                $appearanceSettings = Setting::getGroup('appearance');
                $colors = [
                    'primary' => $appearanceSettings['primary_color'] ?? '#007bff',
                    'secondary' => $appearanceSettings['secondary_color'] ?? '#6c757d',
                    'accent' => $appearanceSettings['accent_color'] ?? '#FF6B35',
                    'success' => $appearanceSettings['success_color'] ?? '#28A745',
                    'danger' => $appearanceSettings['danger_color'] ?? '#DC3545',
                    'warning' => $appearanceSettings['warning_color'] ?? '#FFC107',
                    'info' => $appearanceSettings['info_color'] ?? '#17A2B8',
                    'text' => $appearanceSettings['text_color'] ?? '#2C3E50',
                    'background' => $appearanceSettings['background_color'] ?? '#FFFFFF',
                    'sidebar' => $appearanceSettings['sidebar_color'] ?? '#2C3E50',
                    'button_height' => $appearanceSettings['button_height'] ?? 38,
                    'button_hover_primary' => $appearanceSettings['button_hover_color'] ?? '#0056b3',
                    'button_hover_secondary' => $appearanceSettings['button_secondary_hover_color'] ?? '#545b62',
                    'button_hover_success' => $appearanceSettings['button_success_hover_color'] ?? '#1e7e34',
                    'button_hover_danger' => $appearanceSettings['button_danger_hover_color'] ?? '#c82333',
                    'button_hover_warning' => $appearanceSettings['button_warning_hover_color'] ?? '#d39e00',
                    'button_hover_info' => $appearanceSettings['button_info_hover_color'] ?? '#138496',
                ];
            }

            // Generate CSS content with dynamic colors
            $css = $this->generateDynamicCss($colors);

            // Return CSS response with proper headers
            return response($css, 200, [
                'Content-Type' => 'text/css',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        } catch (\Exception $e) {
            // Return default CSS if there's any error
            $css = $this->generateDynamicCss($this->getDefaultColors());
            return response($css, 200, [
                'Content-Type' => 'text/css',
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]);
        }
    }

    /**
     * Get default color values
     */
    private function getDefaultColors(): array
    {
        return [
            'primary' => '#007bff',
            'secondary' => '#6c757d',
            'accent' => '#FF6B35',
            'success' => '#28A745',
            'danger' => '#DC3545',
            'warning' => '#FFC107',
            'info' => '#17A2B8',
            'text' => '#2C3E50',
            'background' => '#FFFFFF',
            'sidebar' => '#2C3E50',
            'button_height' => 38,
            'button_hover_primary' => '#0056b3',
            'button_hover_secondary' => '#545b62',
            'button_hover_success' => '#1e7e34',
            'button_hover_danger' => '#c82333',
            'button_hover_warning' => '#d39e00',
            'button_hover_info' => '#138496',
        ];
    }

    /**
     * Generate the CSS content with dynamic colors
     */
    private function generateDynamicCss(array $colors)
    {
        // Extract colors
        $primaryColor = $colors['primary'];
        $secondaryColor = $colors['secondary'];
        $accentColor = $colors['accent'];
        $successColor = $colors['success'];
        $dangerColor = $colors['danger'];
        $warningColor = $colors['warning'];
        $infoColor = $colors['info'];
        $textColor = $colors['text'];
        $backgroundColor = $colors['background'];
        $sidebarColor = $colors['sidebar'];

        // Button styling
        $buttonHeight = $colors['button_height'] ?? 38;
        $buttonHoverPrimary = $colors['button_hover_primary'] ?? '#0056b3';
        $buttonHoverSecondary = $colors['button_hover_secondary'] ?? '#545b62';
        $buttonHoverSuccess = $colors['button_hover_success'] ?? '#1e7e34';
        $buttonHoverDanger = $colors['button_hover_danger'] ?? '#c82333';
        $buttonHoverWarning = $colors['button_hover_warning'] ?? '#d39e00';
        $buttonHoverInfo = $colors['button_hover_info'] ?? '#138496';

        // Calculate variations
        $primaryLight = $this->lightenColor($primaryColor, 20);
        $primaryDark = $this->darkenColor($primaryColor, 15);
        $secondaryLight = $this->lightenColor($secondaryColor, 20);
        $secondaryDark = $this->darkenColor($secondaryColor, 20);
        $successLight = $this->lightenColor($successColor, 20);
        $successDark = $this->darkenColor($successColor, 15);
        $dangerLight = $this->lightenColor($dangerColor, 20);
        $dangerDark = $this->darkenColor($dangerColor, 15);
        $warningLight = $this->lightenColor($warningColor, 15);
        $warningDark = $this->darkenColor($warningColor, 15);
        $infoLight = $this->lightenColor($infoColor, 20);
        $infoDark = $this->darkenColor($infoColor, 15);
        $accentLight = $this->lightenColor($accentColor, 20);

        // Calculate RGB values for rgba() usage
        $primaryRgb = $this->hexToRgb($primaryColor);
        $successRgb = $this->hexToRgb($successColor);
        $dangerRgb = $this->hexToRgb($dangerColor);
        $warningRgb = $this->hexToRgb($warningColor);
        $infoRgb = $this->hexToRgb($infoColor);

        return "
/* Dynamic Theme Colors - Generated by PHP */
/* This file is dynamically generated based on admin settings */

:root {
    /* Primary Colors */
    --primary-color: {$primaryColor};
    --primary-light: {$primaryLight};
    --primary-dark: {$primaryDark};
    --primary-rgb: {$primaryRgb};

    /* Secondary Colors */
    --secondary-color: {$secondaryColor};
    --secondary-light: {$secondaryLight};
    --secondary-dark: {$secondaryDark};

    /* Accent Color */
    --accent-color: {$accentColor};
    --accent-light: {$accentLight};

    /* Status Colors */
    --success-color: {$successColor};
    --success-light: {$successLight};
    --success-dark: {$successDark};
    --success-rgb: {$successRgb};

    --danger-color: {$dangerColor};
    --danger-light: {$dangerLight};
    --danger-dark: {$dangerDark};
    --danger-rgb: {$dangerRgb};

    --warning-color: {$warningColor};
    --warning-light: {$warningLight};
    --warning-dark: {$warningDark};
    --warning-rgb: {$warningRgb};

    --info-color: {$infoColor};
    --info-light: {$infoLight};
    --info-dark: {$infoDark};
    --info-rgb: {$infoRgb};

    /* Text & Background */
    --text-color: {$textColor};
    --background-color: {$backgroundColor};
    --sidebar-color: {$sidebarColor};

    /* Gradients */
    --primary-gradient: linear-gradient(135deg, {$primaryColor} 0%, {$primaryLight} 100%);
    --success-gradient: linear-gradient(135deg, {$successColor} 0%, {$successLight} 100%);
    --danger-gradient: linear-gradient(135deg, {$dangerColor} 0%, {$dangerLight} 100%);

    /* Button Styling */
    --button-height: {$buttonHeight}px;
    --button-hover-primary: {$buttonHoverPrimary};
    --button-hover-secondary: {$buttonHoverSecondary};
    --button-hover-success: {$buttonHoverSuccess};
    --button-hover-danger: {$buttonHoverDanger};
    --button-hover-warning: {$buttonHoverWarning};
    --button-hover-info: {$buttonHoverInfo};

    /* Legacy Variables (for backward compatibility) */
    --gold-color: #F7931E;
    --text-light: #ffffff;
    --text-dark: {$textColor};
    --text-muted: {$secondaryColor};
    --bg-light: #F8FAFB;
    --shadow-light: rgba(0, 0, 0, 0.08);
    --shadow-medium: rgba(0, 0, 0, 0.15);
    --shadow-heavy: rgba(0, 0, 0, 0.25);
}

/* ==================== GLOBAL BUTTON STYLES ==================== */
.btn {
    min-height: var(--button-height);
    line-height: calc(var(--button-height) - 12px);
}

.btn-sm {
    min-height: calc(var(--button-height) - 8px);
    line-height: calc(var(--button-height) - 18px);
}

.btn-lg {
    min-height: calc(var(--button-height) + 10px);
    line-height: calc(var(--button-height) - 2px);
}

/* ==================== PRIMARY BUTTON ==================== */
.btn-primary,
.bg-primary {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    color: #fff !important;
}

.btn-primary:hover,
.btn-primary:focus {
    background-color: var(--button-hover-primary) !important;
    border-color: var(--button-hover-primary) !important;
    color: #fff !important;
}

.btn-primary:active,
.btn-primary.active {
    background-color: var(--button-hover-primary) !important;
    border-color: var(--button-hover-primary) !important;
}

.btn-primary:disabled,
.btn-primary.disabled {
    background-color: var(--primary-light) !important;
    border-color: var(--primary-light) !important;
    opacity: 0.65;
}

.text-primary {
    color: var(--primary-color) !important;
}

.border-primary {
    border-color: var(--primary-color) !important;
}

/* ==================== SECONDARY BUTTON ==================== */
.btn-secondary,
.bg-secondary {
    background-color: var(--secondary-color) !important;
    border-color: var(--secondary-color) !important;
    color: #fff !important;
}

.btn-secondary:hover,
.btn-secondary:focus {
    background-color: var(--button-hover-secondary) !important;
    border-color: var(--button-hover-secondary) !important;
    color: #fff !important;
}

.btn-secondary:active,
.btn-secondary.active {
    background-color: var(--button-hover-secondary) !important;
    border-color: var(--button-hover-secondary) !important;
}

.text-secondary {
    color: var(--secondary-color) !important;
}

.border-secondary {
    border-color: var(--secondary-color) !important;
}

/* ==================== SUCCESS BUTTON ==================== */
.btn-success,
.bg-success {
    background-color: var(--success-color) !important;
    border-color: var(--success-color) !important;
    color: #fff !important;
}

.btn-success:hover,
.btn-success:focus {
    background-color: var(--button-hover-success) !important;
    border-color: var(--button-hover-success) !important;
    color: #fff !important;
}

.btn-success:active,
.btn-success.active {
    background-color: var(--button-hover-success) !important;
    border-color: var(--button-hover-success) !important;
}

.text-success {
    color: var(--success-color) !important;
}

.border-success {
    border-color: var(--success-color) !important;
}

/* ==================== DANGER BUTTON ==================== */
.btn-danger,
.bg-danger {
    background-color: var(--danger-color) !important;
    border-color: var(--danger-color) !important;
    color: #fff !important;
}

.btn-danger:hover,
.btn-danger:focus {
    background-color: var(--button-hover-danger) !important;
    border-color: var(--button-hover-danger) !important;
    color: #fff !important;
}

.btn-danger:active,
.btn-danger.active {
    background-color: var(--button-hover-danger) !important;
    border-color: var(--button-hover-danger) !important;
}

.text-danger {
    color: var(--danger-color) !important;
}

.border-danger {
    border-color: var(--danger-color) !important;
}

/* ==================== WARNING BUTTON ==================== */
.btn-warning,
.bg-warning {
    background-color: var(--warning-color) !important;
    border-color: var(--warning-color) !important;
    color: #212529 !important;
}

.btn-warning:hover,
.btn-warning:focus {
    background-color: var(--button-hover-warning) !important;
    border-color: var(--button-hover-warning) !important;
    color: #212529 !important;
}

.btn-warning:active,
.btn-warning.active {
    background-color: var(--button-hover-warning) !important;
    border-color: var(--button-hover-warning) !important;
}

.text-warning {
    color: var(--warning-color) !important;
}

.border-warning {
    border-color: var(--warning-color) !important;
}

/* ==================== INFO BUTTON ==================== */
.btn-info,
.bg-info {
    background-color: var(--info-color) !important;
    border-color: var(--info-color) !important;
    color: #fff !important;
}

.btn-info:hover,
.btn-info:focus {
    background-color: var(--button-hover-info) !important;
    border-color: var(--button-hover-info) !important;
    color: #fff !important;
}

.btn-info:active,
.btn-info.active {
    background-color: var(--button-hover-info) !important;
    border-color: var(--button-hover-info) !important;
}

.text-info {
    color: var(--info-color) !important;
}

.border-info {
    border-color: var(--info-color) !important;
}

/* ==================== OUTLINE BUTTONS ==================== */
.btn-outline-primary {
    color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    background-color: transparent !important;
}

.btn-outline-primary:hover,
.btn-outline-primary:focus,
.btn-outline-primary:active {
    background-color: var(--primary-color) !important;
    border-color: var(--primary-color) !important;
    color: #fff !important;
}

.btn-outline-secondary {
    color: var(--secondary-color) !important;
    border-color: var(--secondary-color) !important;
    background-color: transparent !important;
}

.btn-outline-secondary:hover,
.btn-outline-secondary:focus,
.btn-outline-secondary:active {
    background-color: var(--secondary-color) !important;
    border-color: var(--secondary-color) !important;
    color: #fff !important;
}

.btn-outline-success {
    color: var(--success-color) !important;
    border-color: var(--success-color) !important;
    background-color: transparent !important;
}

.btn-outline-success:hover,
.btn-outline-success:focus,
.btn-outline-success:active {
    background-color: var(--success-color) !important;
    border-color: var(--success-color) !important;
    color: #fff !important;
}

.btn-outline-danger {
    color: var(--danger-color) !important;
    border-color: var(--danger-color) !important;
    background-color: transparent !important;
}

.btn-outline-danger:hover,
.btn-outline-danger:focus,
.btn-outline-danger:active {
    background-color: var(--danger-color) !important;
    border-color: var(--danger-color) !important;
    color: #fff !important;
}

.btn-outline-warning {
    color: var(--warning-color) !important;
    border-color: var(--warning-color) !important;
    background-color: transparent !important;
}

.btn-outline-warning:hover,
.btn-outline-warning:focus,
.btn-outline-warning:active {
    background-color: var(--warning-color) !important;
    border-color: var(--warning-color) !important;
    color: #212529 !important;
}

.btn-outline-info {
    color: var(--info-color) !important;
    border-color: var(--info-color) !important;
    background-color: transparent !important;
}

.btn-outline-info:hover,
.btn-outline-info:focus,
.btn-outline-info:active {
    background-color: var(--info-color) !important;
    border-color: var(--info-color) !important;
    color: #fff !important;
}

/* ==================== BADGES ==================== */
.badge-primary,
.badge.bg-primary {
    background-color: var(--primary-color) !important;
    color: #fff !important;
}

.badge-secondary,
.badge.bg-secondary {
    background-color: var(--secondary-color) !important;
    color: #fff !important;
}

.badge-success,
.badge.bg-success {
    background-color: var(--success-color) !important;
    color: #fff !important;
}

.badge-danger,
.badge.bg-danger {
    background-color: var(--danger-color) !important;
    color: #fff !important;
}

.badge-warning,
.badge.bg-warning {
    background-color: var(--warning-color) !important;
    color: #212529 !important;
}

.badge-info,
.badge.bg-info {
    background-color: var(--info-color) !important;
    color: #fff !important;
}

/* ==================== ALERTS ==================== */
.alert-primary {
    color: var(--primary-dark);
    background-color: rgba({$primaryRgb}, 0.15);
    border-color: var(--primary-color);
}

.alert-success {
    color: var(--success-dark);
    background-color: rgba({$successRgb}, 0.15);
    border-color: var(--success-color);
}

.alert-danger {
    color: var(--danger-dark);
    background-color: rgba({$dangerRgb}, 0.15);
    border-color: var(--danger-color);
}

.alert-warning {
    color: var(--warning-dark);
    background-color: rgba({$warningRgb}, 0.15);
    border-color: var(--warning-color);
}

.alert-info {
    color: var(--info-dark);
    background-color: rgba({$infoRgb}, 0.15);
    border-color: var(--info-color);
}

/* ==================== NAVIGATION ==================== */
.navbar-light .navbar-brand {
    color: var(--primary-color) !important;
}

.navbar-light .navbar-nav .nav-link {
    color: var(--text-color) !important;
    transition: color 0.3s ease;
}

.navbar-light .navbar-nav .nav-link:hover,
.navbar-light .navbar-nav .nav-link:focus {
    color: var(--primary-color) !important;
}

.navbar-light .navbar-nav .nav-link.active {
    color: var(--primary-color) !important;
    font-weight: 500;
}

.navbar-dark .navbar-brand,
.bg-primary .navbar-brand {
    color: #fff !important;
}

.navbar-dark .navbar-nav .nav-link,
.bg-primary .navbar-nav .nav-link {
    color: rgba(255, 255, 255, 0.9) !important;
    transition: color 0.3s ease;
}

.navbar-dark .navbar-nav .nav-link:hover,
.navbar-dark .navbar-nav .nav-link:focus,
.bg-primary .navbar-nav .nav-link:hover,
.bg-primary .navbar-nav .nav-link:focus {
    color: var(--accent-color) !important;
}

.navbar-light .navbar-toggler:focus {
    box-shadow: 0 0 0 0.25rem rgba({$primaryRgb}, 0.25);
}

/* ==================== HERO & SECTIONS ==================== */
.hero-section {
    background: var(--primary-gradient) !important;
}

.section-title {
    color: var(--primary-color) !important;
}

/* ==================== FOOTER ==================== */
.footer,
.bg-dark {
    background-color: var(--sidebar-color) !important;
}

/* ==================== CARDS ==================== */
.card-header {
    background-color: var(--bg-light) !important;
    border-bottom: 1px solid rgba({$primaryRgb}, 0.1) !important;
}

/* ==================== LINKS ==================== */
a {
    color: var(--primary-color);
    transition: color 0.3s ease;
}

a:hover,
a:focus {
    color: var(--primary-dark);
    text-decoration: none;
}

/* ==================== FORM ELEMENTS ==================== */
.form-control:focus,
.form-select:focus {
    border-color: var(--primary-color);
    box-shadow: 0 0 0 0.2rem rgba({$primaryRgb}, 0.25);
}

.form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

.form-check-input:focus {
    border-color: var(--primary-light);
    box-shadow: 0 0 0 0.2rem rgba({$primaryRgb}, 0.25);
}

.form-switch .form-check-input:checked {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

/* ==================== PROGRESS BARS ==================== */
.progress-bar {
    background-color: var(--primary-color) !important;
}

.progress-bar.bg-success {
    background-color: var(--success-color) !important;
}

.progress-bar.bg-danger {
    background-color: var(--danger-color) !important;
}

.progress-bar.bg-warning {
    background-color: var(--warning-color) !important;
}

.progress-bar.bg-info {
    background-color: var(--info-color) !important;
}

/* ==================== TABLES ==================== */
.table-primary {
    background-color: rgba({$primaryRgb}, 0.1);
}

.table-success {
    background-color: rgba({$successRgb}, 0.1);
}

.table-danger {
    background-color: rgba({$dangerRgb}, 0.1);
}

.table-warning {
    background-color: rgba({$warningRgb}, 0.1);
}

.table-info {
    background-color: rgba({$infoRgb}, 0.1);
}

.table-hover tbody tr:hover {
    background-color: rgba({$primaryRgb}, 0.05);
}

/* ==================== PAGINATION ==================== */
.page-link {
    color: var(--primary-color);
}

.page-link:hover {
    color: var(--primary-dark);
    background-color: var(--bg-light);
    border-color: var(--primary-color);
}

.page-item.active .page-link {
    background-color: var(--primary-color);
    border-color: var(--primary-color);
}

/* ==================== BREADCRUMB ==================== */
.breadcrumb-item + .breadcrumb-item::before {
    color: var(--secondary-color);
}

.breadcrumb-item.active {
    color: var(--primary-color);
}

/* ==================== CUSTOM COMPONENTS ==================== */
.feature-icon {
    color: var(--primary-color) !important;
}

.service-card:hover {
    border-color: var(--primary-color) !important;
    box-shadow: 0 4px 15px rgba({$primaryRgb}, 0.15) !important;
}

.doctor-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
}

/* ==================== UTILITIES ==================== */
.text-gradient {
    background: var(--primary-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.bg-gradient-primary {
    background: var(--primary-gradient) !important;
}

.bg-gradient-success {
    background: var(--success-gradient) !important;
}

.bg-gradient-danger {
    background: var(--danger-gradient) !important;
}

/* ==================== ADMIN SIDEBAR ==================== */
.admin-sidebar,
.sidebar {
    background-color: var(--sidebar-color) !important;
}

/* ==================== RESPONSIVE ==================== */
@media (max-width: 768px) {
    .hero-section {
        background: var(--primary-color) !important;
    }
}

/* ==================== ANIMATIONS ==================== */
@keyframes primaryPulse {
    0% {
        box-shadow: 0 0 0 0 rgba({$primaryRgb}, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba({$primaryRgb}, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba({$primaryRgb}, 0);
    }
}

.pulse-primary {
    animation: primaryPulse 2s infinite;
}

@keyframes successPulse {
    0% {
        box-shadow: 0 0 0 0 rgba({$successRgb}, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba({$successRgb}, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba({$successRgb}, 0);
    }
}

.pulse-success {
    animation: successPulse 2s infinite;
}
";
    }

    /**
     * Convert hex color to RGB string
     */
    private function hexToRgb($hex): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        return "{$r}, {$g}, {$b}";
    }

    /**
     * Lighten a hex color by a percentage
     */
    private function lightenColor($hex, $percent)
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = min(255, $r + ($percent / 100) * (255 - $r));
        $g = min(255, $g + ($percent / 100) * (255 - $g));
        $b = min(255, $b + ($percent / 100) * (255 - $b));

        return '#' . sprintf('%02x%02x%02x', round($r), round($g), round($b));
    }

    /**
     * Darken a hex color by a percentage
     */
    private function darkenColor($hex, $percent)
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }

        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, $r - ($percent / 100) * $r);
        $g = max(0, $g - ($percent / 100) * $g);
        $b = max(0, $b - ($percent / 100) * $b);

        return '#' . sprintf('%02x%02x%02x', round($r), round($g), round($b));
    }
}
