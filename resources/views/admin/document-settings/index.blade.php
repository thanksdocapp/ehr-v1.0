@extends('admin.layouts.app')

@section('title', 'Document Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.document-templates.index') }}">Documents</a></li>
    <li class="breadcrumb-item active">Settings</li>
@endsection

@push('styles')
<style>
    .settings-card {
        border: none;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }
    .settings-card-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #fff 100%);
        border-bottom: 1px solid #eee;
        padding: 1.25rem 1.5rem;
        border-radius: 16px 16px 0 0;
    }
    .settings-card-body {
        padding: 1.5rem;
    }
    .setting-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem 0;
        border-bottom: 1px solid #f1f3f4;
    }
    .setting-item:last-child {
        border-bottom: none;
    }
    .setting-info {
        flex: 1;
        padding-right: 1rem;
    }
    .setting-label {
        font-weight: 600;
        color: #333;
        margin-bottom: 0.25rem;
    }
    .setting-description {
        font-size: 0.85rem;
        color: #666;
    }
    .setting-control {
        min-width: 200px;
    }
    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        text-align: center;
        transition: all 0.3s;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0,0,0,0.12);
    }
    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 1rem;
    }
    .stat-icon.primary { background: rgba(102, 126, 234, 0.15); color: #667eea; }
    .stat-icon.success { background: rgba(40, 167, 69, 0.15); color: #28a745; }
    .stat-icon.info { background: rgba(23, 162, 184, 0.15); color: #17a2b8; }
    .stat-icon.warning { background: rgba(255, 193, 7, 0.15); color: #ffc107; }
    .nav-tabs-custom {
        border-bottom: 2px solid #e9ecef;
    }
    .nav-tabs-custom .nav-link {
        border: none;
        padding: 1rem 1.5rem;
        color: #666;
        font-weight: 500;
        position: relative;
    }
    .nav-tabs-custom .nav-link:hover {
        color: #333;
    }
    .nav-tabs-custom .nav-link.active {
        color: #667eea;
        background: transparent;
    }
    .nav-tabs-custom .nav-link.active::after {
        content: '';
        position: absolute;
        bottom: -2px;
        left: 0;
        right: 0;
        height: 2px;
        background: #667eea;
    }
    .group-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
    }
    .group-icon.general { background: rgba(102, 126, 234, 0.15); color: #667eea; }
    .group-icon.templates { background: rgba(17, 153, 142, 0.15); color: #11998e; }
    .group-icon.pdf { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
    .group-icon.email { background: rgba(0, 123, 255, 0.15); color: #007bff; }
</style>
@endpush

@section('content')
<div class="fade-in">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">
                <i class="fas fa-cog me-2 text-primary"></i>Document Settings
            </h1>
            <p class="text-muted mb-0">Configure document templates, PDF generation, and email settings</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.document-settings.categories') }}" class="btn btn-outline-primary">
                <i class="fas fa-folder-tree me-2"></i>Manage Categories
            </a>
            <a href="{{ route('admin.document-templates.index') }}" class="btn btn-primary">
                <i class="fas fa-file-alt me-2"></i>View Templates
            </a>
        </div>
    </div>

    <!-- Stats Row -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon primary">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="h4 mb-1">{{ $stats['total_templates'] }}</div>
                <div class="text-muted small">Total Templates</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon success">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="h4 mb-1">{{ $stats['active_templates'] }}</div>
                <div class="text-muted small">Active Templates</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon info">
                    <i class="fas fa-envelope-open-text"></i>
                </div>
                <div class="h4 mb-1">{{ $stats['letter_templates'] }}</div>
                <div class="text-muted small">Letter Templates</div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="stat-card">
                <div class="stat-icon warning">
                    <i class="fas fa-clipboard-list"></i>
                </div>
                <div class="h4 mb-1">{{ $stats['form_templates'] }}</div>
                <div class="text-muted small">Form Templates</div>
            </div>
        </div>
    </div>

    <!-- Settings Tabs -->
    <form action="{{ route('admin.document-settings.update') }}" method="POST">
        @csrf
        @method('PUT')

        <ul class="nav nav-tabs nav-tabs-custom mb-4" role="tablist">
            @foreach($groups as $groupKey => $group)
            <li class="nav-item">
                <a class="nav-link {{ $loop->first ? 'active' : '' }}"
                   data-bs-toggle="tab"
                   href="#tab-{{ $groupKey }}">
                    <i class="fas {{ $group['icon'] }} me-2"></i>{{ $group['label'] }}
                </a>
            </li>
            @endforeach
        </ul>

        <div class="tab-content">
            @foreach($groups as $groupKey => $group)
            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="tab-{{ $groupKey }}">
                <div class="settings-card">
                    <div class="settings-card-header">
                        <div class="d-flex align-items-center">
                            <div class="group-icon {{ $groupKey }}">
                                <i class="fas {{ $group['icon'] }}"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $group['label'] }}</h5>
                                <small class="text-muted">{{ $group['description'] }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="settings-card-body">
                        @if(isset($settings[$groupKey]))
                            @foreach($settings[$groupKey] as $key => $setting)
                            <div class="setting-item">
                                <div class="setting-info">
                                    <div class="setting-label">{{ ucwords(str_replace('_', ' ', preg_replace('/^(document_|pdf_|email_|template_)/', '', $key))) }}</div>
                                    <div class="setting-description">{{ $setting['description'] ?? '' }}</div>
                                </div>
                                <div class="setting-control">
                                    @if($setting['type'] === 'boolean')
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox"
                                                   id="settings_{{ $key }}"
                                                   name="settings[{{ $key }}]"
                                                   value="1"
                                                   {{ $setting['value'] ? 'checked' : '' }}>
                                        </div>
                                    @elseif($setting['type'] === 'integer')
                                        <input type="number"
                                               class="form-control"
                                               id="settings_{{ $key }}"
                                               name="settings[{{ $key }}]"
                                               value="{{ $setting['value'] }}">
                                    @elseif(str_contains($key, 'paper_size'))
                                        <select class="form-select" name="settings[{{ $key }}]">
                                            <option value="A4" {{ $setting['value'] === 'A4' ? 'selected' : '' }}>A4</option>
                                            <option value="A5" {{ $setting['value'] === 'A5' ? 'selected' : '' }}>A5</option>
                                            <option value="Letter" {{ $setting['value'] === 'Letter' ? 'selected' : '' }}>Letter</option>
                                            <option value="Legal" {{ $setting['value'] === 'Legal' ? 'selected' : '' }}>Legal</option>
                                        </select>
                                    @elseif(str_contains($key, 'orientation'))
                                        <select class="form-select" name="settings[{{ $key }}]">
                                            <option value="portrait" {{ $setting['value'] === 'portrait' ? 'selected' : '' }}>Portrait</option>
                                            <option value="landscape" {{ $setting['value'] === 'landscape' ? 'selected' : '' }}>Landscape</option>
                                        </select>
                                    @else
                                        <input type="text"
                                               class="form-control"
                                               id="settings_{{ $key }}"
                                               name="settings[{{ $key }}]"
                                               value="{{ $setting['value'] }}">
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        @else
                            <p class="text-muted text-center py-4">No settings available in this group.</p>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Save Button -->
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="{{ route('admin.document-templates.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Back to Templates
            </a>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save me-2"></i>Save Settings
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Auto-dismiss alerts
    setTimeout(function() {
        $('.alert').fadeOut();
    }, 5000);
});
</script>
@endpush
