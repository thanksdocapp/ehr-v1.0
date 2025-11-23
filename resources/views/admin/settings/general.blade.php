@extends('admin.layouts.app')

@section('title', 'General Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('settings.index') }}">Settings</a></li>
    <li class="breadcrumb-item active">General</li>
@endsection

@push('styles')
@include('admin.shared.styles')
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-cog me-2 text-primary"></i>General Settings</h1>
        <p class="page-subtitle text-muted">Configure basic application settings and preferences</p>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>Please fix the following errors:
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <form method="POST" action="{{ contextRoute('settings.general.update') }}" id="generalForm" enctype="multipart/form-data">
        @csrf

        <!-- Basic Information Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h4>
                <small class="opacity-75">Core application settings and configuration</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="app_name" class="form-label">
                                <i class="fas fa-tag me-1"></i>Application Name *
                            </label>
                            <input type="text" class="form-control" id="app_name" name="app_name"
                                   value="{{ old('app_name', $settings['app_name'] ?? '') }}" 
                                   placeholder="My Hospital System"
                                   required>
                            <div class="form-help">This will appear in the browser tab and email footers</div>
                        </div>

                        <div class="form-group">
                            <label for="app_description" class="form-label">
                                <i class="fas fa-align-left me-1"></i>Description
                            </label>
                            <textarea class="form-control" id="app_description" name="app_description" rows="3"
                                      placeholder="Brief description of your application">{{ old('app_description', $settings['app_description'] ?? '') }}</textarea>
                            <div class="form-help">A short description for SEO and meta tags</div>
                        </div>

                        <div class="form-group">
                            <label for="app_version" class="form-label">
                                <i class="fas fa-code-branch me-1"></i>Application Version
                            </label>
                            <input type="text" class="form-control" id="app_version" name="app_version"
                                   value="{{ old('app_version', $settings['app_version'] ?? '1.0') }}" 
                                   placeholder="1.0">
                            <div class="form-help">Version number shown in footers and system info (e.g., 1.0, 2.1.0)</div>
                        </div>

                        <div class="form-group">
                            <label for="company_name" class="form-label">
                                <i class="fas fa-building me-1"></i>Company/Author Name
                            </label>
                            <input type="text" class="form-control" id="company_name" name="company_name"
                                   value="{{ old('company_name', $settings['company_name'] ?? '') }}" 
                                   placeholder="Your Company Name">
                            <div class="form-help">Company or author name (used in installation and system info)</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="app_timezone" class="form-label">
                                <i class="fas fa-globe me-1"></i>Application Timezone *
                            </label>
                            <select class="form-select" id="app_timezone" name="app_timezone" required>
                                <option value="UTC" {{ old('app_timezone', $settings['app_timezone'] ?? 'Europe/London') == 'UTC' ? 'selected' : '' }}>UTC - Coordinated Universal Time</option>
                                
                                <!-- Africa -->
                                <optgroup label="🌍 Africa">
                                    <option value="Africa/Abidjan" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Abidjan' ? 'selected' : '' }}>Abidjan, Ivory Coast</option>
                                    <option value="Africa/Accra" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Accra' ? 'selected' : '' }}>Accra, Ghana</option>
                                    <option value="Africa/Addis_Ababa" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Addis_Ababa' ? 'selected' : '' }}>Addis Ababa, Ethiopia</option>
                                    <option value="Africa/Algiers" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Algiers' ? 'selected' : '' }}>Algiers, Algeria</option>
                                    <option value="Africa/Cairo" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Cairo' ? 'selected' : '' }}>Cairo, Egypt</option>
                                    <option value="Africa/Casablanca" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Casablanca' ? 'selected' : '' }}>Casablanca, Morocco</option>
                                    <option value="Africa/Dar_es_Salaam" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Dar_es_Salaam' ? 'selected' : '' }}>Dar es Salaam, Tanzania</option>
                                    <option value="Africa/Gaborone" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Gaborone' ? 'selected' : '' }}>Gaborone, Botswana</option>
                                    <option value="Africa/Harare" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Harare' ? 'selected' : '' }}>Harare, Zimbabwe</option>
                                    <option value="Africa/Johannesburg" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Johannesburg' ? 'selected' : '' }}>Johannesburg, South Africa</option>
                                    <option value="Africa/Kampala" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Kampala' ? 'selected' : '' }}>Kampala, Uganda</option>
                                    <option value="Africa/Kigali" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Kigali' ? 'selected' : '' }}>Kigali, Rwanda</option>
                                    <option value="Africa/Lagos" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Lagos' ? 'selected' : '' }}>Lagos, Nigeria</option>
                                    <option value="Africa/Lusaka" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Lusaka' ? 'selected' : '' }}>Lusaka, Zambia</option>
                                    <option value="Africa/Maputo" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Maputo' ? 'selected' : '' }}>Maputo, Mozambique</option>
                                    <option value="Africa/Nairobi" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Nairobi' ? 'selected' : '' }}>Nairobi, Kenya</option>
                                    <option value="Africa/Tunis" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Tunis' ? 'selected' : '' }}>Tunis, Tunisia</option>
                                    <option value="Africa/Windhoek" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Africa/Windhoek' ? 'selected' : '' }}>Windhoek, Namibia</option>
                                </optgroup>
                                
                                <!-- Americas -->
                                <optgroup label="🌎 Americas">
                                    <option value="America/Anchorage" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Anchorage' ? 'selected' : '' }}>Anchorage, USA (Alaska)</option>
                                    <option value="America/Argentina/Buenos_Aires" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Argentina/Buenos_Aires' ? 'selected' : '' }}>Buenos Aires, Argentina</option>
                                    <option value="America/Asuncion" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Asuncion' ? 'selected' : '' }}>Asunción, Paraguay</option>
                                    <option value="America/Bogota" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Bogota' ? 'selected' : '' }}>Bogotá, Colombia</option>
                                    <option value="America/Caracas" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Caracas' ? 'selected' : '' }}>Caracas, Venezuela</option>
                                    <option value="America/Chicago" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Chicago' ? 'selected' : '' }}>Chicago, USA (Central)</option>
                                    <option value="America/Denver" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Denver' ? 'selected' : '' }}>Denver, USA (Mountain)</option>
                                    <option value="America/La_Paz" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/La_Paz' ? 'selected' : '' }}>La Paz, Bolivia</option>
                                    <option value="America/Lima" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Lima' ? 'selected' : '' }}>Lima, Peru</option>
                                    <option value="America/Los_Angeles" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Los_Angeles' ? 'selected' : '' }}>Los Angeles, USA (Pacific)</option>
                                    <option value="America/Mexico_City" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Mexico_City' ? 'selected' : '' }}>Mexico City, Mexico</option>
                                    <option value="America/Montevideo" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Montevideo' ? 'selected' : '' }}>Montevideo, Uruguay</option>
                                    <option value="America/New_York" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/New_York' ? 'selected' : '' }}>New York, USA (Eastern)</option>
                                    <option value="America/Santiago" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Santiago' ? 'selected' : '' }}>Santiago, Chile</option>
                                    <option value="America/Sao_Paulo" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Sao_Paulo' ? 'selected' : '' }}>São Paulo, Brazil</option>
                                    <option value="America/Toronto" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Toronto' ? 'selected' : '' }}>Toronto, Canada</option>
                                    <option value="America/Vancouver" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'America/Vancouver' ? 'selected' : '' }}>Vancouver, Canada</option>
                                </optgroup>
                                
                                <!-- Asia -->
                                <optgroup label="🌏 Asia">
                                    <option value="Asia/Almaty" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Almaty' ? 'selected' : '' }}>Almaty, Kazakhstan</option>
                                    <option value="Asia/Amman" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Amman' ? 'selected' : '' }}>Amman, Jordan</option>
                                    <option value="Asia/Baghdad" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Baghdad' ? 'selected' : '' }}>Baghdad, Iraq</option>
                                    <option value="Asia/Bahrain" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Bahrain' ? 'selected' : '' }}>Manama, Bahrain</option>
                                    <option value="Asia/Bangkok" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Bangkok' ? 'selected' : '' }}>Bangkok, Thailand</option>
                                    <option value="Asia/Beirut" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Beirut' ? 'selected' : '' }}>Beirut, Lebanon</option>
                                    <option value="Asia/Bishkek" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Bishkek' ? 'selected' : '' }}>Bishkek, Kyrgyzstan</option>
                                    <option value="Asia/Colombo" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Colombo' ? 'selected' : '' }}>Colombo, Sri Lanka</option>
                                    <option value="Asia/Damascus" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Damascus' ? 'selected' : '' }}>Damascus, Syria</option>
                                    <option value="Asia/Dhaka" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Dhaka' ? 'selected' : '' }}>Dhaka, Bangladesh</option>
                                    <option value="Asia/Doha" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Doha' ? 'selected' : '' }}>Doha, Qatar</option>
                                    <option value="Asia/Dubai" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Dubai' ? 'selected' : '' }}>Dubai, UAE</option>
                                    <option value="Asia/Dushanbe" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Dushanbe' ? 'selected' : '' }}>Dushanbe, Tajikistan</option>
                                    <option value="Asia/Ho_Chi_Minh" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Ho_Chi_Minh' ? 'selected' : '' }}>Ho Chi Minh City, Vietnam</option>
                                    <option value="Asia/Hong_Kong" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Hong_Kong' ? 'selected' : '' }}>Hong Kong</option>
                                    <option value="Asia/Islamabad" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Islamabad' ? 'selected' : '' }}>Islamabad, Pakistan</option>
                                    <option value="Asia/Jakarta" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Jakarta' ? 'selected' : '' }}>Jakarta, Indonesia</option>
                                    <option value="Asia/Jerusalem" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Jerusalem' ? 'selected' : '' }}>Jerusalem, Israel</option>
                                    <option value="Asia/Kabul" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Kabul' ? 'selected' : '' }}>Kabul, Afghanistan</option>
                                    <option value="Asia/Kathmandu" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Kathmandu' ? 'selected' : '' }}>Kathmandu, Nepal</option>
                                    <option value="Asia/Kolkata" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Kolkata' ? 'selected' : '' }}>Kolkata, India</option>
                                    <option value="Asia/Kuala_Lumpur" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Kuala_Lumpur' ? 'selected' : '' }}>Kuala Lumpur, Malaysia</option>
                                    <option value="Asia/Kuwait" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Kuwait' ? 'selected' : '' }}>Kuwait City, Kuwait</option>
                                    <option value="Asia/Manila" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Manila' ? 'selected' : '' }}>Manila, Philippines</option>
                                    <option value="Asia/Muscat" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Muscat' ? 'selected' : '' }}>Muscat, Oman</option>
                                    <option value="Asia/Riyadh" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Riyadh' ? 'selected' : '' }}>Riyadh, Saudi Arabia</option>
                                    <option value="Asia/Seoul" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Seoul' ? 'selected' : '' }}>Seoul, South Korea</option>
                                    <option value="Asia/Shanghai" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Shanghai' ? 'selected' : '' }}>Shanghai, China</option>
                                    <option value="Asia/Singapore" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Singapore' ? 'selected' : '' }}>Singapore</option>
                                    <option value="Asia/Tashkent" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Tashkent' ? 'selected' : '' }}>Tashkent, Uzbekistan</option>
                                    <option value="Asia/Tehran" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Tehran' ? 'selected' : '' }}>Tehran, Iran</option>
                                    <option value="Asia/Tokyo" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Tokyo' ? 'selected' : '' }}>Tokyo, Japan</option>
                                    <option value="Asia/Yangon" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Asia/Yangon' ? 'selected' : '' }}>Yangon, Myanmar</option>
                                </optgroup>
                                
                                <!-- Europe -->
                                <optgroup label="🇪🇺 Europe">
                                    <option value="Europe/Amsterdam" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Amsterdam' ? 'selected' : '' }}>Amsterdam, Netherlands</option>
                                    <option value="Europe/Athens" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Athens' ? 'selected' : '' }}>Athens, Greece</option>
                                    <option value="Europe/Berlin" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Berlin' ? 'selected' : '' }}>Berlin, Germany</option>
                                    <option value="Europe/Brussels" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Brussels' ? 'selected' : '' }}>Brussels, Belgium</option>
                                    <option value="Europe/Bucharest" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Bucharest' ? 'selected' : '' }}>Bucharest, Romania</option>
                                    <option value="Europe/Budapest" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Budapest' ? 'selected' : '' }}>Budapest, Hungary</option>
                                    <option value="Europe/Copenhagen" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Copenhagen' ? 'selected' : '' }}>Copenhagen, Denmark</option>
                                    <option value="Europe/Helsinki" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Helsinki' ? 'selected' : '' }}>Helsinki, Finland</option>
                                    <option value="Europe/Istanbul" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Istanbul' ? 'selected' : '' }}>Istanbul, Turkey</option>
                                    <option value="Europe/Kiev" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Kiev' ? 'selected' : '' }}>Kiev, Ukraine</option>
                                    <option value="Europe/London" {{ old('app_timezone', $settings['app_timezone'] ?? 'Europe/London') == 'Europe/London' ? 'selected' : '' }}>London, United Kingdom</option>
                                    <option value="Europe/Madrid" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Madrid' ? 'selected' : '' }}>Madrid, Spain</option>
                                    <option value="Europe/Moscow" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Moscow' ? 'selected' : '' }}>Moscow, Russia</option>
                                    <option value="Europe/Oslo" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Oslo' ? 'selected' : '' }}>Oslo, Norway</option>
                                    <option value="Europe/Paris" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Paris' ? 'selected' : '' }}>Paris, France</option>
                                    <option value="Europe/Prague" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Prague' ? 'selected' : '' }}>Prague, Czech Republic</option>
                                    <option value="Europe/Rome" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Rome' ? 'selected' : '' }}>Rome, Italy</option>
                                    <option value="Europe/Sofia" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Sofia' ? 'selected' : '' }}>Sofia, Bulgaria</option>
                                    <option value="Europe/Stockholm" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Stockholm' ? 'selected' : '' }}>Stockholm, Sweden</option>
                                    <option value="Europe/Vienna" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Vienna' ? 'selected' : '' }}>Vienna, Austria</option>
                                    <option value="Europe/Warsaw" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Warsaw' ? 'selected' : '' }}>Warsaw, Poland</option>
                                    <option value="Europe/Zurich" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Europe/Zurich' ? 'selected' : '' }}>Zurich, Switzerland</option>
                                </optgroup>
                                
                                <!-- Oceania -->
                                <optgroup label="🏝️ Oceania">
                                    <option value="Australia/Adelaide" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Australia/Adelaide' ? 'selected' : '' }}>Adelaide, Australia</option>
                                    <option value="Australia/Brisbane" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Australia/Brisbane' ? 'selected' : '' }}>Brisbane, Australia</option>
                                    <option value="Australia/Darwin" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Australia/Darwin' ? 'selected' : '' }}>Darwin, Australia</option>
                                    <option value="Australia/Melbourne" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Australia/Melbourne' ? 'selected' : '' }}>Melbourne, Australia</option>
                                    <option value="Australia/Perth" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Australia/Perth' ? 'selected' : '' }}>Perth, Australia</option>
                                    <option value="Australia/Sydney" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Australia/Sydney' ? 'selected' : '' }}>Sydney, Australia</option>
                                    <option value="Pacific/Auckland" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Pacific/Auckland' ? 'selected' : '' }}>Auckland, New Zealand</option>
                                    <option value="Pacific/Fiji" {{ old('app_timezone', $settings['app_timezone'] ?? 'UTC') == 'Pacific/Fiji' ? 'selected' : '' }}>Suva, Fiji</option>
                                </optgroup>
                            </select>
                            <div class="form-help">Set the system default timezone</div>
                        </div>

                        <div class="form-group">
                            <label for="default_currency" class="form-label">
                                <i class="fas fa-dollar-sign me-1"></i>Default Currency *
                            </label>
                            <select class="form-select" id="default_currency" name="default_currency" required>
                                <!-- Major World Currencies -->
                                <optgroup label="💰 Major Currencies">
                                    <option value="USD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'USD' ? 'selected' : '' }}>USD - US Dollar ($)</option>
                                    <option value="EUR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'EUR' ? 'selected' : '' }}>EUR - Euro (€)</option>
                                    <option value="GBP" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'GBP' ? 'selected' : '' }}>GBP - British Pound (£)</option>
                                    <option value="JPY" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'JPY' ? 'selected' : '' }}>JPY - Japanese Yen (¥)</option>
                                    <option value="CHF" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'CHF' ? 'selected' : '' }}>CHF - Swiss Franc (CHF)</option>
                                    <option value="CAD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'CAD' ? 'selected' : '' }}>CAD - Canadian Dollar (C$)</option>
                                    <option value="AUD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'AUD' ? 'selected' : '' }}>AUD - Australian Dollar (A$)</option>
                                    <option value="CNY" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'CNY' ? 'selected' : '' }}>CNY - Chinese Yuan (¥)</option>
                                </optgroup>

                                <!-- African Currencies -->
                                <optgroup label="🌍 African Currencies">
                                    <option value="ZAR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'ZAR' ? 'selected' : '' }}>ZAR - South African Rand (R)</option>
                                    <option value="NGN" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'NGN' ? 'selected' : '' }}>NGN - Nigerian Naira (₦)</option>
                                    <option value="GHS" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'GHS' ? 'selected' : '' }}>GHS - Ghanaian Cedi (GH₵)</option>
                                    <option value="KES" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'KES' ? 'selected' : '' }}>KES - Kenyan Shilling (KSh)</option>
                                    <option value="UGX" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'UGX' ? 'selected' : '' }}>UGX - Ugandan Shilling (USh)</option>
                                    <option value="TZS" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'TZS' ? 'selected' : '' }}>TZS - Tanzanian Shilling (TSh)</option>
                                    <option value="EGP" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'EGP' ? 'selected' : '' }}>EGP - Egyptian Pound (E£)</option>
                                    <option value="MAD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'MAD' ? 'selected' : '' }}>MAD - Moroccan Dirham (MAD)</option>
                                    <option value="TND" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'TND' ? 'selected' : '' }}>TND - Tunisian Dinar (TND)</option>
                                    <option value="DZD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'DZD' ? 'selected' : '' }}>DZD - Algerian Dinar (DZD)</option>
                                    <option value="ETB" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'ETB' ? 'selected' : '' }}>ETB - Ethiopian Birr (Br)</option>
                                    <option value="RWF" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'RWF' ? 'selected' : '' }}>RWF - Rwandan Franc (RF)</option>
                                    <option value="ZMW" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'ZMW' ? 'selected' : '' }}>ZMW - Zambian Kwacha (ZK)</option>
                                    <option value="BWP" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'BWP' ? 'selected' : '' }}>BWP - Botswana Pula (P)</option>
                                    <option value="NAD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'NAD' ? 'selected' : '' }}>NAD - Namibian Dollar (N$)</option>
                                    <option value="MZN" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'MZN' ? 'selected' : '' }}>MZN - Mozambican Metical (MT)</option>
                                </optgroup>

                                <!-- Asian Currencies -->
                                <optgroup label="🌏 Asian Currencies">
                                    <option value="INR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'INR' ? 'selected' : '' }}>INR - Indian Rupee (₹)</option>
                                    <option value="KRW" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'KRW' ? 'selected' : '' }}>KRW - South Korean Won (₩)</option>
                                    <option value="SGD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'SGD' ? 'selected' : '' }}>SGD - Singapore Dollar (S$)</option>
                                    <option value="HKD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'HKD' ? 'selected' : '' }}>HKD - Hong Kong Dollar (HK$)</option>
                                    <option value="MYR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'MYR' ? 'selected' : '' }}>MYR - Malaysian Ringgit (RM)</option>
                                    <option value="THB" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'THB' ? 'selected' : '' }}>THB - Thai Baht (฿)</option>
                                    <option value="IDR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'IDR' ? 'selected' : '' }}>IDR - Indonesian Rupiah (Rp)</option>
                                    <option value="PHP" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'PHP' ? 'selected' : '' }}>PHP - Philippine Peso (₱)</option>
                                    <option value="VND" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'VND' ? 'selected' : '' }}>VND - Vietnamese Dong (₫)</option>
                                    <option value="PKR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'PKR' ? 'selected' : '' }}>PKR - Pakistani Rupee (Rs)</option>
                                    <option value="BDT" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'BDT' ? 'selected' : '' }}>BDT - Bangladeshi Taka (৳)</option>
                                    <option value="LKR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'LKR' ? 'selected' : '' }}>LKR - Sri Lankan Rupee (Rs)</option>
                                    <option value="NPR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'NPR' ? 'selected' : '' }}>NPR - Nepalese Rupee (Rs)</option>
                                    <option value="AFN" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'AFN' ? 'selected' : '' }}>AFN - Afghan Afghani (؋)</option>
                                    <option value="IRR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'IRR' ? 'selected' : '' }}>IRR - Iranian Rial (﷼)</option>
                                    <option value="IQD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'IQD' ? 'selected' : '' }}>IQD - Iraqi Dinar (IQD)</option>
                                    <option value="KWD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'KWD' ? 'selected' : '' }}>KWD - Kuwaiti Dinar (KD)</option>
                                    <option value="SAR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'SAR' ? 'selected' : '' }}>SAR - Saudi Arabian Riyal (SR)</option>
                                    <option value="AED" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'AED' ? 'selected' : '' }}>AED - UAE Dirham (د.إ)</option>
                                    <option value="QAR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'QAR' ? 'selected' : '' }}>QAR - Qatari Riyal (QR)</option>
                                    <option value="BHD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'BHD' ? 'selected' : '' }}>BHD - Bahraini Dinar (BD)</option>
                                    <option value="OMR" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'OMR' ? 'selected' : '' }}>OMR - Omani Rial (OMR)</option>
                                    <option value="ILS" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'ILS' ? 'selected' : '' }}>ILS - Israeli Shekel (₪)</option>
                                    <option value="JOD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'JOD' ? 'selected' : '' }}>JOD - Jordanian Dinar (JD)</option>
                                    <option value="LBP" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'LBP' ? 'selected' : '' }}>LBP - Lebanese Pound (L£)</option>
                                </optgroup>

                                <!-- European Currencies -->
                                <optgroup label="🇪🇺 European Currencies">
                                    <option value="NOK" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'NOK' ? 'selected' : '' }}>NOK - Norwegian Krone (kr)</option>
                                    <option value="SEK" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'SEK' ? 'selected' : '' }}>SEK - Swedish Krona (kr)</option>
                                    <option value="DKK" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'DKK' ? 'selected' : '' }}>DKK - Danish Krone (kr)</option>
                                    <option value="PLN" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'PLN' ? 'selected' : '' }}>PLN - Polish Zloty (zł)</option>
                                    <option value="CZK" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'CZK' ? 'selected' : '' }}>CZK - Czech Koruna (Kč)</option>
                                    <option value="HUF" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'HUF' ? 'selected' : '' }}>HUF - Hungarian Forint (Ft)</option>
                                    <option value="RON" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'RON' ? 'selected' : '' }}>RON - Romanian Leu (lei)</option>
                                    <option value="BGN" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'BGN' ? 'selected' : '' }}>BGN - Bulgarian Lev (лв)</option>
                                    <option value="TRY" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'TRY' ? 'selected' : '' }}>TRY - Turkish Lira (₺)</option>
                                    <option value="RUB" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'RUB' ? 'selected' : '' }}>RUB - Russian Ruble (₽)</option>
                                    <option value="UAH" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'UAH' ? 'selected' : '' }}>UAH - Ukrainian Hryvnia (₴)</option>
                                </optgroup>

                                <!-- American Currencies -->
                                <optgroup label="🌎 American Currencies">
                                    <option value="MXN" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'MXN' ? 'selected' : '' }}>MXN - Mexican Peso (MX$)</option>
                                    <option value="BRL" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'BRL' ? 'selected' : '' }}>BRL - Brazilian Real (R$)</option>
                                    <option value="ARS" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'ARS' ? 'selected' : '' }}>ARS - Argentine Peso (ARS$)</option>
                                    <option value="CLP" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'CLP' ? 'selected' : '' }}>CLP - Chilean Peso (CLP$)</option>
                                    <option value="COP" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'COP' ? 'selected' : '' }}>COP - Colombian Peso (COL$)</option>
                                    <option value="PEN" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'PEN' ? 'selected' : '' }}>PEN - Peruvian Sol (S/)</option>
                                    <option value="UYU" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'UYU' ? 'selected' : '' }}>UYU - Uruguayan Peso ($U)</option>
                                    <option value="PYG" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'PYG' ? 'selected' : '' }}>PYG - Paraguayan Guarani (₲)</option>
                                    <option value="BOB" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'BOB' ? 'selected' : '' }}>BOB - Bolivian Boliviano (Bs)</option>
                                    <option value="VES" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'VES' ? 'selected' : '' }}>VES - Venezuelan Bolívar (Bs.S)</option>
                                </optgroup>

                                <!-- Oceania & Others -->
                                <optgroup label="🏝️ Oceania & Others">
                                    <option value="NZD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'NZD' ? 'selected' : '' }}>NZD - New Zealand Dollar (NZ$)</option>
                                    <option value="FJD" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'FJD' ? 'selected' : '' }}>FJD - Fijian Dollar (FJ$)</option>
                                    <option value="PGK" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'PGK' ? 'selected' : '' }}>PGK - Papua New Guinea Kina (K)</option>
                                </optgroup>

                                <!-- Central Asian Currencies -->
                                <optgroup label="🏔️ Central Asian Currencies">
                                    <option value="KZT" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'KZT' ? 'selected' : '' }}>KZT - Kazakhstani Tenge (₸)</option>
                                    <option value="UZS" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'UZS' ? 'selected' : '' }}>UZS - Uzbekistani Som (som)</option>
                                    <option value="KGS" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'KGS' ? 'selected' : '' }}>KGS - Kyrgyzstani Som (som)</option>
                                    <option value="TJS" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'TJS' ? 'selected' : '' }}>TJS - Tajikistani Somoni (TJS)</option>
                                    <option value="TMT" {{ old('default_currency', $settings['default_currency'] ?? 'USD') == 'TMT' ? 'selected' : '' }}>TMT - Turkmenistani Manat (T)</option>
                                </optgroup>
                            </select>
                            <div class="form-help">Primary currency for the application</div>
                        </div>

                        <div class="form-group">
                            <label for="currency_symbol" class="form-label">
                                <i class="fas fa-coins me-1"></i>Currency Symbol *
                            </label>
                            <input type="text" class="form-control" id="currency_symbol" name="currency_symbol"
                                   value="{{ old('currency_symbol', $settings['currency_symbol'] ?? '$') }}" 
                                   placeholder="$" maxlength="5" required>
                            <div class="form-help">Symbol to display with currency amounts (e.g., $, €, £)</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-info-circle me-1"></i>Display Settings
                            </label>
                            <div class="form-check form-switch mt-2">
                                <input type="hidden" name="show_powered_by" value="0">
                                <input class="form-check-input" type="checkbox" id="show_powered_by" 
                                       name="show_powered_by" value="1" 
                                       {{ old('show_powered_by', $settings['show_powered_by'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="show_powered_by">
                                    Show "Powered by" Footer
                                </label>
                            </div>
                            <div class="form-help">Display "Powered by [App Name] v[Version]" in admin footers</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Branding Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-palette me-2"></i>Logo & Branding</h4>
                <small class="opacity-75">Upload and manage your application logos</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="logo_light" class="form-label">
                                <i class="fas fa-sun me-1"></i>Light Mode Logo
                            </label>
                            <input type="file" class="form-control" id="logo_light" name="logo_light" 
                                   accept="image/png,image/jpg,image/jpeg,image/svg+xml">
                            <div class="form-help">For light backgrounds (max 2MB)</div>
                            @if(isset($settings['logo_light']) && $settings['logo_light'])
                                <div class="mt-3">
                                    <small class="text-muted d-block mb-2">Current logo:</small>
                                    <img src="{{ asset($settings['logo_light']) }}" alt="Light Logo" 
                                         class="img-fluid border rounded p-2" style="max-height: 80px;">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="logo_dark" class="form-label">
                                <i class="fas fa-moon me-1"></i>Dark Mode Logo
                            </label>
                            <input type="file" class="form-control" id="logo_dark" name="logo_dark" 
                                   accept="image/png,image/jpg,image/jpeg,image/svg+xml">
                            <div class="form-help">For dark backgrounds (max 2MB)</div>
                            @if(isset($settings['logo_dark']) && $settings['logo_dark'])
                                <div class="mt-3">
                                    <small class="text-muted d-block mb-2">Current logo:</small>
                                    <div style="background-color: #333; padding: 10px; border-radius: 8px;">
                                        <img src="{{ asset($settings['logo_dark']) }}" alt="Dark Logo" 
                                             class="img-fluid" style="max-height: 60px;">
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="favicon" class="form-label">
                                <i class="fas fa-star me-1"></i>Favicon
                            </label>
                            <input type="file" class="form-control" id="favicon" name="favicon" 
                                   accept="image/png,image/jpg,image/jpeg,image/x-icon">
                            <div class="form-help">Browser tab icon (max 1MB)</div>
                            @if(isset($settings['favicon']) && $settings['favicon'])
                                <div class="mt-3">
                                    <small class="text-muted d-block mb-2">Current favicon:</small>
                                    <img src="{{ asset($settings['favicon']) }}" alt="Favicon" 
                                         class="border rounded p-1" style="max-height: 32px; max-width: 32px;">
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Important:</strong> Uploaded logos will be used across the entire application including:
                    <ul class="mb-0 mt-2">
                        <li>Admin dashboard header</li>
                        <li>User dashboard header</li>
                        <li>Frontend website</li>
                        <li>Email templates</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Theme Colors Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-palette me-2"></i>Theme Colors</h4>
                <small class="opacity-75">Customize your application's primary and secondary colors</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="primary_color" class="form-label">
                                <i class="fas fa-paint-brush me-1"></i>Primary Color *
                            </label>
                            <input type="color" class="form-control" id="primary_color" name="primary_color"
                                   value="{{ old('primary_color', $settings['primary_color'] ?? '#007bff') }}" required>
                            <div class="form-help">Main color used throughout the application</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="secondary_color" class="form-label">
                                <i class="fas fa-brush me-1"></i>Secondary Color *
                            </label>
                            <input type="color" class="form-control" id="secondary_color" name="secondary_color"
                                   value="{{ old('secondary_color', $settings['secondary_color'] ?? '#6c757d') }}" required>
                            <div class="form-help">Accent color for secondary elements</div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Note:</strong> Color changes will be applied to the frontend website and future patient portal. The admin panel maintains its own fixed color scheme.
                </div>
                
                <div class="alert alert-warning mt-2">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Frontend Only:</strong> These colors will only affect:
                    <ul class="mb-0 mt-2">
                        <li>Public website pages</li>
                        <li>Patient portal (when implemented)</li>
                        <li>Email templates and notifications</li>
                        <li class="text-muted"><em>Admin panel colors remain unchanged</em></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Security & Performance Section -->
        <div class="form-section">
            <div class="form-section-header">
                <h4 class="mb-0"><i class="fas fa-shield-alt me-2"></i>Security & Performance</h4>
                <small class="opacity-75">Application security and performance settings</small>
            </div>
            <div class="form-section-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-globe me-1"></i>Frontend Display
                            </label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="enable_frontend" value="0">
                                <input class="form-check-input" type="checkbox" id="enable_frontend" 
                                       name="enable_frontend" value="1" 
                                       {{ old('enable_frontend', $settings['enable_frontend'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="enable_frontend">
                                    Enable Homepage/Frontend Pages
                                </label>
                            </div>
                            <div class="form-help">When disabled, visitors will be redirected to the staff login page</div>
                        </div>

                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-user-shield me-1"></i>Patient Login
                            </label>
                            <div class="form-check form-switch">
                                <input type="hidden" name="patient_login_enabled" value="0">
                                <input class="form-check-input" type="checkbox" id="patient_login_enabled" 
                                       name="patient_login_enabled" value="1" 
                                       {{ old('patient_login_enabled', $settings['patient_login_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="form-check-label" for="patient_login_enabled">
                                    Enable Patient Login
                                </label>
                            </div>
                            <div class="form-help">When disabled, patients will not be able to log in to the patient portal</div>
                        </div>

                        <div class="form-group">
                            <label for="session_timeout" class="form-label">
                                <i class="fas fa-clock me-1"></i>Session Timeout (minutes)
                            </label>
                            <input type="number" class="form-control" id="session_timeout" name="session_timeout" 
                                   value="{{ old('session_timeout', $settings['session_timeout'] ?? 120) }}" 
                                   min="5" max="1440" placeholder="120">
                            <div class="form-help">How long before users are automatically logged out</div>
                        </div>

                        <div class="form-group">
                            <label for="maintenance_mode" class="form-label">
                                <i class="fas fa-tools me-1"></i>Maintenance Mode
                            </label>
                            <select class="form-select" id="maintenance_mode" name="maintenance_mode">
                                <option value="0" {{ old('maintenance_mode', $settings['maintenance_mode'] ?? '0') == '0' ? 'selected' : '' }}>Disabled</option>
                                <option value="1" {{ old('maintenance_mode', $settings['maintenance_mode'] ?? '0') == '1' ? 'selected' : '' }}>Enabled</option>
                            </select>
                            <div class="form-help">Put the application in maintenance mode</div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="debug_mode" class="form-label">
                                <i class="fas fa-bug me-1"></i>Debug Mode
                            </label>
                            <select class="form-select" id="debug_mode" name="debug_mode">
                                <option value="0" {{ old('debug_mode', $settings['debug_mode'] ?? '0') == '0' ? 'selected' : '' }}>Disabled (Production)</option>
                                <option value="1" {{ old('debug_mode', $settings['debug_mode'] ?? '0') == '1' ? 'selected' : '' }}>Enabled (Development)</option>
                            </select>
                            <div class="form-help">Show detailed error messages (development only)</div>
                        </div>

                        <div class="form-group">
                            <label for="cache_enabled" class="form-label">
                                <i class="fas fa-tachometer-alt me-1"></i>Application Cache
                            </label>
                            <select class="form-select" id="cache_enabled" name="cache_enabled">
                                <option value="1" {{ old('cache_enabled', $settings['cache_enabled'] ?? '1') == '1' ? 'selected' : '' }}>Enabled</option>
                                <option value="0" {{ old('cache_enabled', $settings['cache_enabled'] ?? '1') == '0' ? 'selected' : '' }}>Disabled</option>
                            </select>
                            <div class="form-help">Enable caching for better performance</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="form-section">
            <div class="form-section-body text-center">
                <button type="submit" class="btn btn-primary btn-lg me-3">
                    <i class="fas fa-save me-2"></i>Save Settings
                </button>
                <a href="{{ contextRoute('settings.index') }}" class="btn btn-secondary btn-lg">
                    <i class="fas fa-arrow-left me-2"></i>Back to Settings
                </a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Currency code to symbol mapping
    const currencySymbols = {
        'USD': '$',
        'EUR': '€',
        'GBP': '£',
        'JPY': '¥',
        'CHF': 'CHF',
        'CAD': 'C$',
        'AUD': 'A$',
        'CNY': '¥',
        'ZAR': 'R',
        'NGN': '₦',
        'GHS': 'GH₵',
        'KES': 'KSh',
        'UGX': 'USh',
        'TZS': 'TSh',
        'EGP': 'E£',
        'MAD': 'MAD',
        'TND': 'TND',
        'DZD': 'DZD',
        'ETB': 'Br',
        'RWF': 'RF',
        'ZMW': 'ZK',
        'BWP': 'P',
        'NAD': 'N$',
        'MZN': 'MT',
        'INR': '₹',
        'KRW': '₩',
        'SGD': 'S$',
        'HKD': 'HK$',
        'MYR': 'RM',
        'THB': '฿',
        'IDR': 'Rp',
        'PHP': '₱',
        'VND': '₫',
        'PKR': 'Rs',
        'BDT': '৳',
        'LKR': 'Rs',
        'NPR': 'Rs',
        'AFN': '؋',
        'IRR': '﷼',
        'IQD': 'IQD',
        'KWD': 'KD',
        'SAR': 'SR',
        'AED': 'د.إ',
        'QAR': 'QR',
        'BHD': 'BD',
        'OMR': 'OMR',
        'ILS': '₪',
        'JOD': 'JD',
        'LBP': 'L£',
        'NOK': 'kr',
        'SEK': 'kr',
        'DKK': 'kr',
        'PLN': 'zł',
        'CZK': 'Kč',
        'HUF': 'Ft',
        'RON': 'lei',
        'BGN': 'лв',
        'TRY': '₺',
        'RUB': '₽',
        'UAH': '₴',
        'MXN': 'MX$',
        'BRL': 'R$',
        'ARS': 'ARS$',
        'CLP': 'CLP$',
        'COP': 'COL$',
        'PEN': 'S/',
        'UYU': '$U',
        'PYG': '₲',
        'BOB': 'Bs',
        'VES': 'Bs.S',
        'NZD': 'NZ$',
        'FJD': 'FJ$',
        'PGK': 'K',
        'KZT': '₸',
        'UZS': 'som',
        'KGS': 'som',
        'TJS': 'TJS',
        'TMT': 'T'
    };

    // Reverse mapping: symbol to currency code (for when symbol changes)
    const symbolToCurrency = {};
    Object.keys(currencySymbols).forEach(code => {
        const symbol = currencySymbols[code];
        // Only map if symbol is unique or use the first occurrence
        if (!symbolToCurrency[symbol] || symbolToCurrency[symbol] === code) {
            symbolToCurrency[symbol] = code;
        }
    });

    let isUpdating = false; // Prevent infinite loops

    // When default currency changes, update currency symbol
    $('#default_currency').on('change', function() {
        if (isUpdating) return;
        isUpdating = true;
        const selectedCurrency = $(this).val();
        if (currencySymbols[selectedCurrency]) {
            $('#currency_symbol').val(currencySymbols[selectedCurrency]);
        }
        isUpdating = false;
    });

    // When currency symbol changes, try to find matching currency
    $('#currency_symbol').on('input change', function() {
        if (isUpdating) return;
        const symbol = $(this).val().trim();
        if (symbol && symbolToCurrency[symbol]) {
            isUpdating = true;
            $('#default_currency').val(symbolToCurrency[symbol]);
            isUpdating = false;
        }
    });

    // Initialize: Set symbol based on selected currency on page load
    const initialCurrency = $('#default_currency').val();
    if (initialCurrency && currencySymbols[initialCurrency]) {
        const currentSymbol = $('#currency_symbol').val().trim();
        if (!currentSymbol || currentSymbol === '$') {
            $('#currency_symbol').val(currencySymbols[initialCurrency]);
        }
    }

    // File upload validation
    $('input[type="file"]').on('change', function() {
        const file = this.files[0];
        if (file) {
            const maxSize = this.id === 'favicon' ? 1 : 2; // MB
            const sizeInMB = file.size / (1024 * 1024);
            
            if (sizeInMB > maxSize) {
                alert(`File size should not exceed ${maxSize}MB`);
                $(this).val('');
                return;
            }
            
            // Show preview for images
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = $(this).parent().find('.preview-image');
                    if (preview.length === 0) {
                        $(this).parent().append(`<div class="preview-image mt-2"><img src="${e.target.result}" class="img-fluid border rounded" style="max-height: 80px;"></div>`);
                    } else {
                        preview.find('img').attr('src', e.target.result);
                    }
                }.bind(this);
                reader.readAsDataURL(file);
            }
        }
    });

    // Form submission validation
    $('#generalForm').on('submit', function(e) {
        const appName = $('#app_name').val().trim();
        if (!appName) {
            e.preventDefault();
            alert('Application name is required');
            $('#app_name').focus();
            return false;
        }
        
        // Show loading message for settings save
        showLoading('Saving general settings...');
    });
});
</script>
@endpush
