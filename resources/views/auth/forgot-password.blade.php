<x-guest-layout>
    <h4 class="text-center mb-4 fw-bold">{{ __('Reset Password') }}</h4>
    
    <div class="mb-4 text-muted small">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    @if (session('status'))
        <div class="alert alert-success mb-4" role="alert">
            {{ session('status') }}
        </div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div class="mb-3">
            <label for="email" class="form-label">{{ __('Email') }}</label>
            <input id="email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="d-grid gap-2">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-envelope me-2"></i>{{ __('Email Password Reset Link') }}
            </button>
        </div>
        
        <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i>{{ __('Back to login') }}
            </a>
        </div>
    </form>
</x-guest-layout>
