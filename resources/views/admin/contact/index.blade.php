@extends('admin.layouts.app')

@section('title', 'Edit Contact Settings')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ contextRoute('dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ contextRoute('contact.index') }}">Contact Page</a></li>
    <li class="breadcrumb-item active">Edit Settings</li>
@endsection

@push('styles')
<style>
.form-section {
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
    border: 1px solid #e3e6f0;
}

.form-section-header {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    color: white;
    padding: 1.5rem 2rem;
    border-radius: 12px 12px 0 0;
}

.form-section-body {
    padding: 2rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.form-label {
    font-weight: 600;
    color: #5a5c69;
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
}

.form-control, .form-select {
    border: 2px solid #e3e6f0;
    border-radius: 8px;
    padding: 0.75rem 1rem;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #1cc88a;
    box-shadow: 0 0 0 0.2rem rgba(28, 200, 138, 0.25);
}

.btn {
    padding: 0.75rem 2rem;
    font-weight: 600;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.btn-primary {
    background: linear-gradient(135deg, #1cc88a 0%, #36b9cc 100%);
    border: none;
    box-shadow: 0 4px 15px rgba(28, 200, 138, 0.3);
}

.btn-primary:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(28, 200, 138, 0.4);
}

.form-help {
    font-size: 0.85rem;
    color: #6c757d;
    margin-top: 0.5rem;
    font-style: italic;
}

.info-card {
    background: #f8f9fc;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
    padding: 1.5rem;
    margin-bottom: 1.5rem;
}

.info-card h6 {
    color: #5a5c69;
    margin-bottom: 1rem;
}

.info-card ul {
    margin: 0;
    padding-left: 1.5rem;
}

.info-card li {
    margin-bottom: 0.5rem;
    color: #858796;
}

.status-indicator {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 4px;
    font-size: 0.75rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-complete {
    background-color: #d4edda;
    color: #155724;
}

.status-incomplete {
    background-color: #f8d7da;
    color: #721c24;
}

.status-optional {
    background-color: #fff3cd;
    color: #856404;
}
</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="page-title mb-4">
        <h1><i class="fas fa-edit me-2 text-primary"></i>Edit Contact Settings</h1>
        <p class="page-subtitle text-muted">Update your hospital's contact page content and settings</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <form id="contactSettingsForm" action="{{ contextRoute('contact.update') }}" method="POST">
                @csrf
                @method('PUT')

                <!-- Hero Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-star me-2"></i>Hero Section</h4>
                        <small class="opacity-75">Main banner content for the contact page</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_hero_title" class="form-label">
                                        <i class="fas fa-heading me-1"></i>Hero Title *
                                    </label>
                                    <input type="text" class="form-control @error('contact_hero_title') is-invalid @enderror" 
                                           id="contact_hero_title" name="contact_hero_title" 
                                           value="{{ old('contact_hero_title', $settings['contact_hero_title'] ?? '') }}" 
                                           placeholder="Enter hero title" required>
                                    <div class="form-help">Main title displayed on the contact page</div>
                                    @error('contact_hero_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_hero_subtitle" class="form-label">
                                        <i class="fas fa-align-left me-1"></i>Hero Subtitle *
                                    </label>
                                    <input type="text" class="form-control @error('contact_hero_subtitle') is-invalid @enderror" 
                                           id="contact_hero_subtitle" name="contact_hero_subtitle" 
                                           value="{{ old('contact_hero_subtitle', $settings['contact_hero_subtitle'] ?? '') }}" 
                                           placeholder="Enter hero subtitle" required>
                                    <div class="form-help">Subtitle displayed below the main title</div>
                                    @error('contact_hero_subtitle')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Contact Form Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-envelope me-2"></i>Contact Form</h4>
                        <small class="opacity-75">Contact form settings and messages</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_form_title" class="form-label">
                                        <i class="fas fa-text-width me-1"></i>Form Title *
                                    </label>
                                    <input type="text" class="form-control @error('contact_form_title') is-invalid @enderror" 
                                           id="contact_form_title" name="contact_form_title" 
                                           value="{{ old('contact_form_title', $settings['contact_form_title'] ?? '') }}" 
                                           placeholder="Enter form title" required>
                                    @error('contact_form_title')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_form_subtitle" class="form-label">
                                        <i class="fas fa-paragraph me-1"></i>Form Subtitle *
                                    </label>
                                    <input type="text" class="form-control @error('contact_form_subtitle') is-invalid @enderror" 
                                           id="contact_form_subtitle" name="contact_form_subtitle" 
                                           value="{{ old('contact_form_subtitle', $settings['contact_form_subtitle'] ?? '') }}" 
                                           placeholder="Enter form subtitle" required>
                                    @error('contact_form_subtitle')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact_form_success_message" class="form-label">
                                <i class="fas fa-check-circle me-1"></i>Success Message *
                            </label>
                            <textarea class="form-control @error('contact_form_success_message') is-invalid @enderror" 
                                      id="contact_form_success_message" name="contact_form_success_message" rows="2" 
                                      placeholder="Enter success message" required>{{ old('contact_form_success_message', $settings['contact_form_success_message'] ?? '') }}</textarea>
                            <div class="form-help">Message shown when form is submitted successfully</div>
                            @error('contact_form_success_message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-info-circle me-2"></i>Contact Information</h4>
                        <small class="opacity-75">Essential contact details and communications</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_emergency_phone" class="form-label">
                                        <i class="fas fa-phone me-1"></i>Emergency Phone *
                                    </label>
                                    <input type="tel" class="form-control @error('contact_emergency_phone') is-invalid @enderror" 
                                           id="contact_emergency_phone" name="contact_emergency_phone" 
                                           value="{{ old('contact_emergency_phone', $settings['contact_emergency_phone'] ?? '') }}" 
                                           placeholder="Enter emergency phone number" required>
                                    <div class="form-help">24/7 emergency contact number</div>
                                    @error('contact_emergency_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_general_phone" class="form-label">
                                        <i class="fas fa-phone-alt me-1"></i>General Phone *
                                    </label>
                                    <input type="tel" class="form-control @error('contact_general_phone') is-invalid @enderror" 
                                           id="contact_general_phone" name="contact_general_phone" 
                                           value="{{ old('contact_general_phone', $settings['contact_general_phone'] ?? '') }}" 
                                           placeholder="Enter general phone number" required>
                                    <div class="form-help">General inquiries and information</div>
                                    @error('contact_general_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact_appointments_email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Appointments Email *
                            </label>
                            <input type="email" class="form-control @error('contact_appointments_email') is-invalid @enderror" 
                                   id="contact_appointments_email" name="contact_appointments_email" 
                                   value="{{ old('contact_appointments_email', $settings['contact_appointments_email'] ?? '') }}" 
                                   placeholder="Enter appointments email" required>
                            <div class="form-help">Email for appointment bookings and inquiries</div>
                            @error('contact_appointments_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="alert alert-info mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Important:</strong> The fields below control what appears in the website header, footer, and contact page.
                </div>

                <!-- Basic Contact Details - These appear in header/footer -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="contact_phone" class="form-label">
                                <i class="fas fa-phone me-1"></i>Main Contact Phone * <span class="badge bg-primary">Header/Footer</span>
                            </label>
                            <input type="tel" class="form-control @error('contact_phone') is-invalid @enderror" 
                                   id="contact_phone" name="contact_phone" 
                                   value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}" 
                                   placeholder="Enter main phone number" required>
                            <div class="form-help">Displays in website header and footer</div>
                            @error('contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="contact_email" class="form-label">
                                <i class="fas fa-envelope me-1"></i>Main Contact Email * <span class="badge bg-primary">Header/Footer</span>
                            </label>
                            <input type="email" class="form-control @error('contact_email') is-invalid @enderror" 
                                   id="contact_email" name="contact_email" 
                                   value="{{ old('contact_email', $settings['contact_email'] ?? '') }}" 
                                   placeholder="Enter main email address" required>
                            <div class="form-help">Displays in website header and footer</div>
                            @error('contact_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="contact_address" class="form-label">
                                <i class="fas fa-map-marker-alt me-1"></i>Hospital Address * <span class="badge bg-primary">Header/Footer</span>
                            </label>
                            <textarea class="form-control @error('contact_address') is-invalid @enderror" 
                                      id="contact_address" name="contact_address" rows="3" 
                                      placeholder="Enter hospital address" required>{{ old('contact_address', $settings['contact_address'] ?? '') }}</textarea>
                            <div class="form-help">Displays in website header and footer</div>
                            @error('contact_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Operating Hours Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-clock me-2"></i>Operating Hours</h4>
                        <small class="opacity-75">Hospital and department operating hours</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_emergency_hours" class="form-label">
                                        <i class="fas fa-ambulance me-1"></i>Emergency Hours *
                                    </label>
                                    <input type="text" class="form-control @error('contact_emergency_hours') is-invalid @enderror" 
                                           id="contact_emergency_hours" name="contact_emergency_hours" 
                                           value="{{ old('contact_emergency_hours', $settings['contact_emergency_hours'] ?? '') }}" 
                                           placeholder="e.g., 24/7" required>
                                    <div class="form-help">Emergency department operating hours</div>
                                    @error('contact_emergency_hours')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_outpatient_hours" class="form-label">
                                        <i class="fas fa-user-md me-1"></i>Outpatient Hours *
                                    </label>
                                    <input type="text" class="form-control @error('contact_outpatient_hours') is-invalid @enderror" 
                                           id="contact_outpatient_hours" name="contact_outpatient_hours" 
                                           value="{{ old('contact_outpatient_hours', $settings['contact_outpatient_hours'] ?? '') }}" 
                                           placeholder="e.g., Mon-Fri 8:00 AM - 5:00 PM" required>
                                    <div class="form-help">Outpatient clinic hours</div>
                                    @error('contact_outpatient_hours')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_visitor_hours" class="form-label">
                                        <i class="fas fa-users me-1"></i>Visitor Hours *
                                    </label>
                                    <input type="text" class="form-control @error('contact_visitor_hours') is-invalid @enderror" 
                                           id="contact_visitor_hours" name="contact_visitor_hours" 
                                           value="{{ old('contact_visitor_hours', $settings['contact_visitor_hours'] ?? '') }}" 
                                           placeholder="e.g., Daily 10:00 AM - 8:00 PM" required>
                                    <div class="form-help">Patient visiting hours</div>
                                    @error('contact_visitor_hours')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="contact_pharmacy_hours" class="form-label">
                                        <i class="fas fa-pills me-1"></i>Pharmacy Hours *
                                    </label>
                                    <input type="text" class="form-control @error('contact_pharmacy_hours') is-invalid @enderror" 
                                           id="contact_pharmacy_hours" name="contact_pharmacy_hours" 
                                           value="{{ old('contact_pharmacy_hours', $settings['contact_pharmacy_hours'] ?? '') }}" 
                                           placeholder="e.g., Mon-Sat 9:00 AM - 6:00 PM" required>
                                    <div class="form-help">Hospital pharmacy hours</div>
                                    @error('contact_pharmacy_hours')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Map Settings Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-map-marked-alt me-2"></i>Map Settings</h4>
                        <small class="opacity-75">Location and map configuration</small>
                    </div>
                    <div class="form-section-body">
                        <div class="form-group">
                            <label for="contact_map_embed_url" class="form-label">
                                <i class="fas fa-map me-1"></i>Map Embed URL
                            </label>
                            <input type="url" class="form-control @error('contact_map_embed_url') is-invalid @enderror" 
                                   id="contact_map_embed_url" name="contact_map_embed_url" 
                                   value="{{ old('contact_map_embed_url', $settings['contact_map_embed_url'] ?? '') }}" 
                                   placeholder="https://www.google.com/maps/embed?pb=...">
                            <div class="form-help">Get embed URL from Google Maps by clicking "Share" → "Embed a map"</div>
                            @error('contact_map_embed_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Social Media Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-share-alt me-2"></i>Social Media</h4>
                        <small class="opacity-75">Social media links and contact information</small>
                    </div>
                    <div class="form-section-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="social_facebook" class="form-label mb-0">
                                            <i class="fab fa-facebook text-primary me-1"></i>Facebook URL
                                        </label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="social_facebook_enabled" 
                                                   name="social_facebook_enabled" value="1" 
                                                   {{ old('social_facebook_enabled', $settings['social_facebook_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="social_facebook_enabled">
                                                <small>Enable Display</small>
                                            </label>
                                        </div>
                                    </div>
                                    <input type="url" class="form-control @error('social_facebook') is-invalid @enderror" 
                                           id="social_facebook" name="social_facebook" 
                                           value="{{ old('social_facebook', $settings['social_facebook'] ?? '') }}" 
                                           placeholder="https://facebook.com/your-hospital">
                                    <div class="form-help">Your hospital's Facebook page URL</div>
                                    @error('social_facebook')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="social_twitter" class="form-label mb-0">
                                            <i class="fab fa-twitter text-info me-1"></i>Twitter URL
                                        </label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="social_twitter_enabled" 
                                                   name="social_twitter_enabled" value="1" 
                                                   {{ old('social_twitter_enabled', $settings['social_twitter_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="social_twitter_enabled">
                                                <small>Enable Display</small>
                                            </label>
                                        </div>
                                    </div>
                                    <input type="url" class="form-control @error('social_twitter') is-invalid @enderror" 
                                           id="social_twitter" name="social_twitter" 
                                           value="{{ old('social_twitter', $settings['social_twitter'] ?? '') }}" 
                                           placeholder="https://twitter.com/your-hospital">
                                    <div class="form-help">Your hospital's Twitter profile URL</div>
                                    @error('social_twitter')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="social_instagram" class="form-label mb-0">
                                            <i class="fab fa-instagram text-danger me-1"></i>Instagram URL
                                        </label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="social_instagram_enabled" 
                                                   name="social_instagram_enabled" value="1" 
                                                   {{ old('social_instagram_enabled', $settings['social_instagram_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="social_instagram_enabled">
                                                <small>Enable Display</small>
                                            </label>
                                        </div>
                                    </div>
                                    <input type="url" class="form-control @error('social_instagram') is-invalid @enderror" 
                                           id="social_instagram" name="social_instagram" 
                                           value="{{ old('social_instagram', $settings['social_instagram'] ?? '') }}" 
                                           placeholder="https://instagram.com/your-hospital">
                                    <div class="form-help">Your hospital's Instagram profile URL</div>
                                    @error('social_instagram')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="social_linkedin" class="form-label mb-0">
                                            <i class="fab fa-linkedin text-primary me-1"></i>LinkedIn URL
                                        </label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="social_linkedin_enabled" 
                                                   name="social_linkedin_enabled" value="1" 
                                                   {{ old('social_linkedin_enabled', $settings['social_linkedin_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="social_linkedin_enabled">
                                                <small>Enable Display</small>
                                            </label>
                                        </div>
                                    </div>
                                    <input type="url" class="form-control @error('social_linkedin') is-invalid @enderror" 
                                           id="social_linkedin" name="social_linkedin" 
                                           value="{{ old('social_linkedin', $settings['social_linkedin'] ?? '') }}" 
                                           placeholder="https://linkedin.com/company/your-hospital">
                                    <div class="form-help">Your hospital's LinkedIn company page URL</div>
                                    @error('social_linkedin')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="social_youtube" class="form-label mb-0">
                                            <i class="fab fa-youtube text-danger me-1"></i>YouTube URL
                                        </label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="social_youtube_enabled" 
                                                   name="social_youtube_enabled" value="1" 
                                                   {{ old('social_youtube_enabled', $settings['social_youtube_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="social_youtube_enabled">
                                                <small>Enable Display</small>
                                            </label>
                                        </div>
                                    </div>
                                    <input type="url" class="form-control @error('social_youtube') is-invalid @enderror" 
                                           id="social_youtube" name="social_youtube" 
                                           value="{{ old('social_youtube', $settings['social_youtube'] ?? '') }}" 
                                           placeholder="https://youtube.com/c/your-hospital">
                                    <div class="form-help">Your hospital's YouTube channel URL</div>
                                    @error('social_youtube')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label for="social_whatsapp" class="form-label mb-0">
                                            <i class="fab fa-whatsapp text-success me-1"></i>WhatsApp Number
                                        </label>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="social_whatsapp_enabled" 
                                                   name="social_whatsapp_enabled" value="1" 
                                                   {{ old('social_whatsapp_enabled', $settings['social_whatsapp_enabled'] ?? '1') == '1' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="social_whatsapp_enabled">
                                                <small>Enable Display</small>
                                            </label>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control @error('social_whatsapp') is-invalid @enderror" 
                                           id="social_whatsapp" name="social_whatsapp" 
                                           value="{{ old('social_whatsapp', $settings['social_whatsapp'] ?? '') }}" 
                                           placeholder="+1234567890"
                                           title="Enter WhatsApp number in international format: +[country code][number]">
                                    <div class="form-help">WhatsApp number in international format (e.g., +1234567890). Must start with + followed by country code.</div>
                                    @error('social_whatsapp')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Global Offices Section -->
                <div class="form-section">
                    <div class="form-section-header">
                        <h4 class="mb-0"><i class="fas fa-globe me-2"></i>Global Offices</h4>
                        <small class="opacity-75">Manage multiple office locations displayed on contact page</small>
                    </div>
                    <div class="form-section-body">
                        <div class="alert alert-info mb-4">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Global Offices:</strong> These appear in the "Global Offices" section of your contact page. Leave fields blank to hide that office.
                        </div>

                        <!-- Office 1 -->
                        <div class="border rounded p-3 mb-4" style="background: #f8f9fc;">
                            <h6 class="text-primary mb-3"><i class="fas fa-building me-1"></i>Office 1</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="global_office_1_name" class="form-label">
                                            <i class="fas fa-tag me-1"></i>Office Name
                                        </label>
                                        <input type="text" class="form-control" 
                                               id="global_office_1_name" name="global_office_1_name" 
                                               value="{{ old('global_office_1_name', $settings['global_office_1_name'] ?? 'New York (HQ)') }}" 
                                               placeholder="e.g., New York (HQ)">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="global_office_1_address" class="form-label">
                                            <i class="fas fa-map-marker-alt me-1"></i>Address
                                        </label>
                                        <textarea class="form-control" rows="2"
                                                  id="global_office_1_address" name="global_office_1_address" 
                                                  placeholder="Full address with city, state, zip">{{ old('global_office_1_address', $settings['global_office_1_address'] ?? '123 Financial District
New York, NY 10005') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="global_office_1_phone" class="form-label">
                                            <i class="fas fa-phone me-1"></i>Phone
                                        </label>
                                        <input type="tel" class="form-control" 
                                               id="global_office_1_phone" name="global_office_1_phone" 
                                               value="{{ old('global_office_1_phone', $settings['global_office_1_phone'] ?? '+1 (555) 123-4567') }}" 
                                               placeholder="+1 (555) 123-4567">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Office 2 -->
                        <div class="border rounded p-3 mb-4" style="background: #f8f9fc;">
                            <h6 class="text-primary mb-3"><i class="fas fa-building me-1"></i>Office 2</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="global_office_2_name" class="form-label">
                                            <i class="fas fa-tag me-1"></i>Office Name
                                        </label>
                                        <input type="text" class="form-control" 
                                               id="global_office_2_name" name="global_office_2_name" 
                                               value="{{ old('global_office_2_name', $settings['global_office_2_name'] ?? 'London') }}" 
                                               placeholder="e.g., London">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="global_office_2_address" class="form-label">
                                            <i class="fas fa-map-marker-alt me-1"></i>Address
                                        </label>
                                        <textarea class="form-control" rows="2"
                                                  id="global_office_2_address" name="global_office_2_address" 
                                                  placeholder="Full address with city, state, zip">{{ old('global_office_2_address', $settings['global_office_2_address'] ?? '456 Canary Wharf
London E14 5AB, UK') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="global_office_2_phone" class="form-label">
                                            <i class="fas fa-phone me-1"></i>Phone
                                        </label>
                                        <input type="tel" class="form-control" 
                                               id="global_office_2_phone" name="global_office_2_phone" 
                                               value="{{ old('global_office_2_phone', $settings['global_office_2_phone'] ?? '+44 20 7946 0958') }}" 
                                               placeholder="+44 20 7946 0958">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Office 3 -->
                        <div class="border rounded p-3 mb-4" style="background: #f8f9fc;">
                            <h6 class="text-primary mb-3"><i class="fas fa-building me-1"></i>Office 3</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="global_office_3_name" class="form-label">
                                            <i class="fas fa-tag me-1"></i>Office Name
                                        </label>
                                        <input type="text" class="form-control" 
                                               id="global_office_3_name" name="global_office_3_name" 
                                               value="{{ old('global_office_3_name', $settings['global_office_3_name'] ?? 'Singapore') }}" 
                                               placeholder="e.g., Singapore">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="global_office_3_address" class="form-label">
                                            <i class="fas fa-map-marker-alt me-1"></i>Address
                                        </label>
                                        <textarea class="form-control" rows="2"
                                                  id="global_office_3_address" name="global_office_3_address" 
                                                  placeholder="Full address with city, state, zip">{{ old('global_office_3_address', $settings['global_office_3_address'] ?? '789 Marina Bay
Singapore 018956') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="global_office_3_phone" class="form-label">
                                            <i class="fas fa-phone me-1"></i>Phone
                                        </label>
                                        <input type="tel" class="form-control" 
                                               id="global_office_3_phone" name="global_office_3_phone" 
                                               value="{{ old('global_office_3_phone', $settings['global_office_3_phone'] ?? '+65 6789 0123') }}" 
                                               placeholder="+65 6789 0123">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Office 4 -->
                        <div class="border rounded p-3 mb-4" style="background: #f8f9fc;">
                            <h6 class="text-primary mb-3"><i class="fas fa-building me-1"></i>Office 4</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="global_office_4_name" class="form-label">
                                            <i class="fas fa-tag me-1"></i>Office Name
                                        </label>
                                        <input type="text" class="form-control" 
                                               id="global_office_4_name" name="global_office_4_name" 
                                               value="{{ old('global_office_4_name', $settings['global_office_4_name'] ?? 'Dubai') }}" 
                                               placeholder="e.g., Dubai">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="global_office_4_address" class="form-label">
                                            <i class="fas fa-map-marker-alt me-1"></i>Address
                                        </label>
                                        <textarea class="form-control" rows="2"
                                                  id="global_office_4_address" name="global_office_4_address" 
                                                  placeholder="Full address with city, state, zip">{{ old('global_office_4_address', $settings['global_office_4_address'] ?? '321 DIFC
Dubai, UAE') }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="global_office_4_phone" class="form-label">
                                            <i class="fas fa-phone me-1"></i>Phone
                                        </label>
                                        <input type="tel" class="form-control" 
                                               id="global_office_4_phone" name="global_office_4_phone" 
                                               value="{{ old('global_office_4_phone', $settings['global_office_4_phone'] ?? '+971 4 123 4567') }}" 
                                               placeholder="+971 4 123 4567">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-lightbulb me-2"></i>
                            <strong>Tip:</strong> Leave any office fields blank to hide that office from the contact page. Only offices with at least a name will be displayed.
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="form-section">
                    <div class="form-section-body text-center">
                        <button type="submit" class="btn btn-primary btn-lg me-3">
                            <i class="fas fa-save me-2"></i>Update Contact Settings
                        </button>
                        <a href="{{ contextRoute('contact.index') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Helper Information -->
        <div class="col-lg-4">
            <div class="info-card">
                <h6><i class="fas fa-info-circle me-2"></i>Settings Status</h6>
                <div class="mb-3">
                    <strong>Hero Section:</strong> 
                    <span class="status-indicator {{ $settings && isset($settings['contact_hero_title']) && isset($settings['contact_hero_subtitle']) ? 'status-complete' : 'status-incomplete' }}">
                        {{ $settings && isset($settings['contact_hero_title']) && isset($settings['contact_hero_subtitle']) ? 'Complete' : 'Incomplete' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Contact Form:</strong> 
                    <span class="status-indicator {{ $settings && isset($settings['contact_form_title']) && isset($settings['contact_form_subtitle']) ? 'status-complete' : 'status-incomplete' }}">
                        {{ $settings && isset($settings['contact_form_title']) && isset($settings['contact_form_subtitle']) ? 'Complete' : 'Incomplete' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Contact Info:</strong> 
                    <span class="status-indicator {{ $settings && isset($settings['contact_emergency_phone']) && isset($settings['contact_general_phone']) ? 'status-complete' : 'status-incomplete' }}">
                        {{ $settings && isset($settings['contact_emergency_phone']) && isset($settings['contact_general_phone']) ? 'Complete' : 'Incomplete' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Operating Hours:</strong> 
                    <span class="status-indicator {{ $settings && isset($settings['contact_emergency_hours']) && isset($settings['contact_outpatient_hours']) ? 'status-complete' : 'status-incomplete' }}">
                        {{ $settings && isset($settings['contact_emergency_hours']) && isset($settings['contact_outpatient_hours']) ? 'Complete' : 'Incomplete' }}
                    </span>
                </div>
                <div class="mb-3">
                    <strong>Map:</strong> 
                    <span class="status-indicator {{ $settings && isset($settings['contact_map_embed_url']) ? 'status-complete' : 'status-optional' }}">
                        {{ $settings && isset($settings['contact_map_embed_url']) ? 'Set' : 'Optional' }}
                    </span>
                </div>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-lightbulb me-2"></i>Content Guidelines</h6>
                <ul>
                    <li>Use clear, professional language</li>
                    <li>Include all essential contact information</li>
                    <li>Ensure phone numbers are formatted correctly</li>
                    <li>Use 24-hour format for operating hours</li>
                    <li>Test all contact methods regularly</li>
                    <li>Keep information up to date</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-phone me-2"></i>Contact Best Practices</h6>
                <ul>
                    <li>Display emergency numbers prominently</li>
                    <li>Provide multiple contact methods</li>
                    <li>Include department-specific hours</li>
                    <li>Use accessible phone number formats</li>
                    <li>Add map for easy location finding</li>
                    <li>Update seasonal hour changes</li>
                </ul>
            </div>

            <div class="info-card">
                <h6><i class="fas fa-clock me-2"></i>Quick Actions</h6>
                <div class="d-grid gap-2">
                    <a href="{{ contextRoute('contact.index') }}" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye me-1"></i>View Contact Page
                    </a>
                    <a href="{{ contextRoute('contact.index') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-arrow-left me-1"></i>Back to Contact
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Form validation
    $('#contactSettingsForm').on('submit', function(e) {
        let isValid = true;

        // Check required fields
        $('.form-control[required]').each(function() {
            if ($(this).val().trim() === '') {
                $(this).addClass('is-invalid');
                isValid = false;
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        // Validate email format
        const email = $('#contact_appointments_email').val();
        if (email && !isValidEmail(email)) {
            $('#contact_appointments_email').addClass('is-invalid');
            isValid = false;
        }

        // Validate URL format
        const mapUrl = $('#contact_map_embed_url').val();
        if (mapUrl && !isValidURL(mapUrl)) {
            $('#contact_map_embed_url').addClass('is-invalid');
            isValid = false;
        }

        if (!isValid) {
            e.preventDefault();
            alert('Please fill in all required fields correctly.');
        }
    });

    // Email validation helper
    function isValidEmail(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    }

    // URL validation helper
    function isValidURL(string) {
        try {
            new URL(string);
            return true;
        } catch (_) {
            return false;
        }
    }

    // Remove validation errors on input
    $('.form-control').on('input change', function() {
        $(this).removeClass('is-invalid');
    });

    // Phone number formatting
    $('input[type="tel"]').on('input', function() {
        let value = $(this).val().replace(/\D/g, '');
        if (value.length >= 10) {
            value = value.replace(/(\d{3})(\d{3})(\d{4})/, '($1) $2-$3');
        }
        $(this).val(value);
    });

    // Character counter for textareas
    $('textarea').on('input', function() {
        const maxLength = 200;
        const currentLength = $(this).val().length;
        
        if (!$(this).next('.char-counter').length) {
            $(this).after('<small class="text-muted char-counter"></small>');
        }
        
        $(this).next('.char-counter').text(currentLength + '/' + maxLength + ' characters');
        
        if (currentLength > maxLength) {
            $(this).addClass('is-invalid');
        } else {
            $(this).removeClass('is-invalid');
        }
    });

    // Initialize character counters
    $('textarea').trigger('input');
});
</script>
@endpush