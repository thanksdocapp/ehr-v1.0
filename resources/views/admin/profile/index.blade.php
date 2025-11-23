@extends('admin.layouts.app')

@section('title', 'Admin Profile')

@php
use Illuminate\Support\Facades\Storage;
@endphp

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Page Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-0 text-gray-800">My Profile</h1>
                    <p class="mb-0 text-muted">Manage your account settings and security</p>
                </div>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row">
                <!-- Profile Information -->
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-user me-2"></i>Profile Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ contextRoute('profile.update') }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                
                                <!-- Avatar -->
                                <div class="text-center mb-4">
                                    <div class="position-relative d-inline-block">
                                        @if(auth()->user()->avatar)
                                            <img src="{{ Storage::disk('public')->url(auth()->user()->avatar) }}"
                                                 alt="Avatar" class="rounded-circle" width="120" height="120" 
                                                 style="object-fit: cover;">
                                        @else
                                            <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 120px; height: 120px;">
                                                <i class="fas fa-user text-white" style="font-size: 3rem;"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mt-2">
                                        <input type="file" class="form-control @error('avatar') is-invalid @enderror" 
                                               id="avatar" name="avatar" accept="image/*">
                                        @error('avatar')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <small class="text-muted">JPG, PNG, GIF up to 2MB</small>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="name" class="form-label">Full Name</label>
                                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                                   id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required>
                                            @error('name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email" class="form-label">Email Address</label>
                                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                                   id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required>
                                            @error('email')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="phone" class="form-label">Phone Number</label>
                                            <input type="text" class="form-control @error('phone') is-invalid @enderror" 
                                                   id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}">
                                            @error('phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-2"></i>Update Profile
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="col-lg-6">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-lock me-2"></i>Change Password
                            </h6>
                        </div>
                        <div class="card-body">
                            <form action="{{ contextRoute('profile.password.change') }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Current Password</label>
                                    <input type="password" class="form-control @error('current_password') is-invalid @enderror" 
                                           id="current_password" name="current_password" required>
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">New Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                           id="password" name="password" required>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Minimum 8 characters</small>
                                </div>

                                <div class="mb-3">
                                    <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                    <input type="password" class="form-control" 
                                           id="password_confirmation" name="password_confirmation" required>
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-key me-2"></i>Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Account Information -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-info-circle me-2"></i>Account Information
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    <p class="mb-2"><strong>Member Since:</strong></p>
                                    <p class="text-muted">{{ formatDate(auth()->user()->created_at) }}</p>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-2"><strong>Last Login:</strong></p>
                                    <p class="text-muted">
                                        @if(auth()->user()->last_login_at)
                                            {{ formatDateTime(auth()->user()->last_login_at) }}
                                        @else
                                            Never
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <p class="mb-2"><strong>Role:</strong></p>
                                    <span class="badge bg-primary">Administrator</span>
                                </div>
                                <div class="col-sm-6">
                                    <p class="mb-2"><strong>Status:</strong></p>
                                    <span class="badge bg-success">Active</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Preview avatar on file select
    $('#avatar').on('change', function() {
        const file = this.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = $(this).closest('.card-body').find('img, .bg-primary');
                if (img.is('img')) {
                    img.attr('src', e.target.result);
                } else {
                    img.replaceWith(`<img src="${e.target.result}" alt="Avatar" class="rounded-circle" width="120" height="120" style="object-fit: cover;">`);
                }
            };
            reader.readAsDataURL(file);
        }
    });

});
</script>
@endpush
