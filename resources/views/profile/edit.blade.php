@extends(auth()->check() && auth()->user()->role === 'doctor' ? 'layouts.doctor' : 'layouts.staff')

@section('title', 'Profile')

@section('page-title', 'Profile')
@section('page-subtitle', 'Manage your account information and settings')

@section('content')
<div class="fade-in-up">

    <div class="row g-4">
        <!-- Profile Information -->
        <div class="col-lg-8">
            <!-- Profile Information Form -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-user me-2"></i>Profile Information</h5>
                </div>
                <div class="doctor-card-body">
                    <p class="text-muted mb-4">Update your account's profile information and email address.</p>
                    
                    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
                        @csrf
                    </form>

                    <form method="post" action="{{ route('profile.update') }}" id="profileForm">
                        @csrf
                        @method('patch')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-semibold">Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" 
                                       class="form-control @error('name') is-invalid @enderror" 
                                       value="{{ old('name', $user->name) }}" required autofocus autocomplete="name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label fw-semibold">Email <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="email" 
                                       class="form-control @error('email') is-invalid @enderror" 
                                       value="{{ old('email', $user->email) }}" required autocomplete="username">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                
                                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                                    <div class="mt-2">
                                        <p class="text-sm text-warning">
                                            <i class="fas fa-exclamation-triangle me-1"></i>
                                            Your email address is unverified.
                                            <button form="send-verification" class="btn btn-link p-0 align-baseline text-decoration-underline">
                                                Click here to re-send the verification email.
                                            </button>
                                        </p>
                                        @if (session('status') === 'verification-link-sent')
                                            <p class="text-sm text-success mt-2">
                                                <i class="fas fa-check me-1"></i>
                                                A new verification link has been sent to your email address.
                                            </p>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label fw-semibold">Phone Number</label>
                                <input type="tel" name="phone" id="phone" 
                                       class="form-control @error('phone') is-invalid @enderror" 
                                       value="{{ old('phone', $user->phone) }}" autocomplete="tel">
                                @error('phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="role" class="form-label fw-semibold">Role</label>
                                <input type="text" class="form-control" value="{{ $user->role_display }}" readonly>
                            </div>
                        </div>

                        @if($user->role === 'doctor')
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="specialization" class="form-label fw-semibold">
                                    <i class="fas fa-stethoscope me-1"></i>Specialisation
                                </label>
                                <input type="text" name="specialization" id="specialization" 
                                       class="form-control @error('specialization') is-invalid @enderror" 
                                       value="{{ old('specialization', $user->specialization ?? ($user->doctor->specialization ?? 'GP')) }}" 
                                       placeholder="e.g., GP, Cardiology, Pediatrics">
                                @error('specialization')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Your medical specialisation</small>
                            </div>
                        </div>
                        @endif

                        <div class="row">
                            <div class="col-12 mb-3">
                                <label for="bio" class="form-label fw-semibold">Bio</label>
                                <textarea name="bio" id="bio" rows="3" 
                                          class="form-control @error('bio') is-invalid @enderror" 
                                          placeholder="Tell us about yourself...">{{ old('bio', $user->bio) }}</textarea>
                                @error('bio')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-doctor-primary">
                                <i class="fas fa-save me-2"></i>Save Changes
                            </button>
                            @if (session('status') === 'profile-updated')
                                <span class="text-success" id="profile-saved">
                                    <i class="fas fa-check me-1"></i>Saved.
                                </span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Password Update Form -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h5 class="doctor-card-title mb-0"><i class="fas fa-key me-2"></i>Update Password</h5>
                </div>
                <div class="doctor-card-body">
                    <p class="text-muted mb-4">Ensure your account is using a long, random password to stay secure.</p>
                    
                    <form method="post" action="{{ route('password.update') }}" id="passwordForm">
                        @csrf
                        @method('put')

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="current_password" class="form-label fw-semibold">Current Password <span class="text-danger">*</span></label>
                                <input type="password" name="current_password" id="current_password" 
                                       class="form-control @error('current_password', 'updatePassword') is-invalid @enderror" 
                                       autocomplete="current-password">
                                @error('current_password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="password" 
                                       class="form-control @error('password', 'updatePassword') is-invalid @enderror" 
                                       autocomplete="new-password">
                                @error('password', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label fw-semibold">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" id="password_confirmation" 
                                       class="form-control @error('password_confirmation', 'updatePassword') is-invalid @enderror" 
                                       autocomplete="new-password">
                                @error('password_confirmation', 'updatePassword')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <button type="submit" class="btn btn-success" style="background: var(--doctor-success); border: none; border-radius: 12px; padding: 0.75rem 1.5rem; font-weight: 600;">
                                <i class="fas fa-shield-alt me-2"></i>Update Password
                            </button>
                            @if (session('status') === 'password-updated')
                                <span class="text-success" id="password-saved">
                                    <i class="fas fa-check me-1"></i>Saved.
                                </span>
                            @endif
                        </div>
                    </form>
                </div>
            </div>

            <!-- Delete Account Form -->
            <div class="doctor-card mb-4" style="border-color: var(--doctor-danger);">
                <div class="doctor-card-header" style="background: var(--doctor-danger); border-bottom-color: var(--doctor-danger);">
                    <h5 class="doctor-card-title mb-0 text-white"><i class="fas fa-trash me-2"></i>Delete Account</h5>
                </div>
                <div class="doctor-card-body">
                    <p class="text-muted mb-4">
                        Once your account is deleted, all of its resources and data will be permanently deleted. 
                        Before deleting your account, please download any data or information that you wish to retain.
                    </p>
                    
                    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">
                        <i class="fas fa-trash me-2"></i>Delete Account
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Actions -->
            <div class="doctor-card mb-4">
                <div class="doctor-card-header">
                    <h6 class="doctor-card-title mb-0 fw-semibold">Quick Actions</h6>
                </div>
                <div class="doctor-card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('staff.dashboard') }}" class="btn btn-doctor-primary">
                            <i class="fas fa-tachometer-alt me-2"></i>Back to Dashboard
                        </a>
                        <a href="{{ route('change-password') }}" class="btn btn-outline-primary" style="border-color: var(--doctor-info); color: var(--doctor-info);">
                            <i class="fas fa-key me-2"></i>Change Password
                        </a>
                    </div>
                </div>
            </div>

            <!-- Account Information -->
            <div class="doctor-card">
                <div class="doctor-card-header">
                    <h6 class="doctor-card-title mb-0">Account Information</h6>
                </div>
                <div class="doctor-card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">User ID</small>
                        <strong>#{{ $user->id }}</strong>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Role</small>
                        <span class="badge bg-{{ $user->role === 'admin' ? 'danger' : ($user->role === 'doctor' ? 'success' : 'primary') }}">
                            {{ $user->role_display }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Status</small>
                        <span class="badge bg-{{ $user->is_active ? 'success' : 'danger' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Member Since</small>
                        <strong>{{ $user->created_at->format('M d, Y') }}</strong>
                    </div>
                    @if($user->last_login_at)
                    <div class="mb-0">
                        <small class="text-muted d-block">Last Login</small>
                        <strong>{{ $user->last_login_at->format('M d, Y H:i') }}</strong>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Account Modal -->
<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-labelledby="deleteAccountModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAccountModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>Delete Account
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="post" action="{{ route('profile.destroy') }}">
                <div class="modal-body">
                    @csrf
                    @method('delete')
                    
                    <p class="text-danger fw-semibold mb-3">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        Are you sure you want to delete your account?
                    </p>
                    
                    <p class="text-muted mb-3">
                        Once your account is deleted, all of its resources and data will be permanently deleted. 
                        Please enter your password to confirm you would like to permanently delete your account.
                    </p>

                    <div class="mb-3">
                        <label for="password_delete" class="form-label fw-semibold">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" id="password_delete" 
                               class="form-control @error('password', 'userDeletion') is-invalid @enderror" 
                               placeholder="Enter your password">
                        @error('password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash me-2"></i>Delete Account
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Auto-hide success messages
document.addEventListener('DOMContentLoaded', function() {
    const profileSaved = document.getElementById('profile-saved');
    const passwordSaved = document.getElementById('password-saved');
    
    if (profileSaved) {
        setTimeout(() => {
            profileSaved.style.opacity = '0';
            setTimeout(() => profileSaved.remove(), 300);
        }, 3000);
    }
    
    if (passwordSaved) {
        setTimeout(() => {
            passwordSaved.style.opacity = '0';
            setTimeout(() => passwordSaved.remove(), 300);
        }, 3000);
    }

});
</script>

@endsection
