<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:'Segoe UI',Tahoma,Geneva,Verdana,sans-serif}.reset-card{background:white;border-radius:15px;box-shadow:0 10px 40px rgba(0,0,0,0.2);max-width:500px;width:100%;overflow:hidden}.reset-header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;padding:30px;text-align:center}.reset-header h1{font-size:24px;margin-bottom:10px;font-weight:600}.reset-body{padding:40px}.form-label{font-weight:500;color:#333;margin-bottom:8px}.form-control{border:2px solid #e0e0e0;border-radius:8px;padding:12px 15px;font-size:15px;transition:all 0.3s}.form-control:focus{border-color:#667eea;box-shadow:0 0 0 0.2rem rgba(102,126,234,0.25)}.btn-reset{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border:none;border-radius:8px;color:white;padding:12px;font-size:16px;font-weight:600;width:100%;transition:all 0.3s}.btn-reset:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(102,126,234,0.4);color:white}.password-toggle{position:absolute;right:15px;top:50%;transform:translateY(-50%);cursor:pointer;color:#999}.password-toggle:hover{color:#667eea}.alert{border-radius:8px;border:none}.back-to-login{text-align:center;margin-top:20px}.back-to-login a{color:#667eea;text-decoration:none;font-weight:500}.back-to-login a:hover{text-decoration:underline}.password-requirements{font-size:13px;color:#666;margin-top:8px}.password-requirements li{margin-bottom:4px}
    </style>
</head>
<body>
    <div class="reset-card">
        <div class="reset-header">
            <i class="fas fa-key fa-2x mb-3"></i>
            <h1>Reset Your Password</h1>
            <p class="mb-0">Enter your new password below</p>
        </div>
        <div class="reset-body">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <form method="POST" action="{{ route('password.reset.update') }}">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div class="mb-3">
                    <label for="email" class="form-label"><i class="fas fa-envelope me-1"></i>Email Address</label>
                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" name="email" value="{{ old('email', $email) }}" required autofocus readonly>
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label"><i class="fas fa-lock me-1"></i>New Password</label>
                    <div class="position-relative">
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" name="password" required>
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                    <div class="password-requirements"><ul class="ps-3 mb-0 mt-2"><li>At least 8 characters long</li><li>Include letters and numbers</li><li>Use a unique password</li></ul></div>
                    @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="mb-4">
                    <label for="password_confirmation" class="form-label"><i class="fas fa-lock me-1"></i>Confirm New Password</label>
                    <div class="position-relative">
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                        <i class="fas fa-eye password-toggle" id="togglePasswordConfirm"></i>
                    </div>
                </div>
                <button type="submit" class="btn btn-reset"><i class="fas fa-check me-2"></i>Reset Password</button>
            </form>
            <div class="back-to-login"><a href="{{ route('login') }}"><i class="fas fa-arrow-left me-1"></i>Back to Login</a></div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click',function(){const p=document.getElementById('password');const t=p.getAttribute('type')==='password'?'text':'password';p.setAttribute('type',t);this.classList.toggle('fa-eye');this.classList.toggle('fa-eye-slash')});
        document.getElementById('togglePasswordConfirm').addEventListener('click',function(){const p=document.getElementById('password_confirmation');const t=p.getAttribute('type')==='password'?'text':'password';p.setAttribute('type',t);this.classList.toggle('fa-eye');this.classList.toggle('fa-eye-slash')});
    </script>
</body>
</html>
