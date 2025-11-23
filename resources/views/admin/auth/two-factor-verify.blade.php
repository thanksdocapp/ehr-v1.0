<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Two-Factor Authentication - {{ config('app.name') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .auth-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            max-width: 450px;
            width: 100%;
        }
        
        .auth-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        
        .auth-header i {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .auth-body {
            padding: 40px;
        }
        
        .code-input {
            font-size: 24px;
            text-align: center;
            letter-spacing: 10px;
            font-weight: bold;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .code-input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-verify {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .btn-verify:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }
        
        .recovery-link {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .recovery-link:hover {
            color: #764ba2;
            text-decoration: underline;
        }
        
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .recovery-form {
            display: none;
        }
        
        .recovery-input {
            text-transform: uppercase;
            font-family: monospace;
            font-size: 16px;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-header">
            <i class="fas fa-shield-alt"></i>
            <h3 class="mb-0">Two-Factor Authentication</h3>
            <p class="mb-0 mt-2">Verify your identity</p>
        </div>
        
        <div class="auth-body">
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
                    <i class="fas fa-exclamation-circle me-2"></i>
                    @foreach($errors->all() as $error)
                        {{ $error }}
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            
            <!-- Code Verification Form -->
            <form id="codeForm" action="{{ route('admin.two-factor.verify.post') }}" method="POST">
                @csrf
                
                @if(session('code_sent'))
                    <div class="info-box">
                        <i class="fas fa-info-circle text-primary me-2"></i>
                        <small>A 6-digit verification code has been sent to your email address. Please enter it below.</small>
                    </div>
                @endif
                
                <div class="mb-4">
                    <label for="code" class="form-label fw-bold">Verification Code</label>
                    <input type="text" 
                           class="form-control code-input" 
                           id="code" 
                           name="code" 
                           maxlength="6" 
                           pattern="[0-9]{6}"
                           placeholder="000000"
                           required 
                           autofocus>
                    <div class="form-text">Enter the 6-digit code from your email</div>
                </div>
                
                <button type="submit" class="btn btn-primary btn-verify w-100 mb-3">
                    <i class="fas fa-check me-2"></i>Verify Code
                </button>
                
                <div class="text-center">
                    <button type="button" id="resendBtn" class="btn btn-link text-muted p-0">
                        <i class="fas fa-redo me-1"></i>Resend Code
                    </button>
                </div>
            </form>
            
            <!-- Recovery Code Form -->
            <form id="recoveryForm" class="recovery-form" action="{{ route('admin.two-factor.verify.recovery') }}" method="POST">
                @csrf
                
                <div class="info-box">
                    <i class="fas fa-info-circle text-warning me-2"></i>
                    <small>Enter one of your recovery codes if you don't have access to your email.</small>
                </div>
                
                <div class="mb-4">
                    <label for="recovery_code" class="form-label fw-bold">Recovery Code</label>
                    <input type="text" 
                           class="form-control recovery-input" 
                           id="recovery_code" 
                           name="recovery_code" 
                           maxlength="10" 
                           placeholder="XXXXXXXXXX"
                           required>
                    <div class="form-text">Enter a 10-character recovery code</div>
                </div>
                
                <button type="submit" class="btn btn-warning w-100 mb-3">
                    <i class="fas fa-key me-2"></i>Verify Recovery Code
                </button>
                
                <div class="text-center">
                    <button type="button" class="btn btn-link recovery-link" id="useCodeBtn">
                        <i class="fas fa-arrow-left me-1"></i>Back to Verification Code
                    </button>
                </div>
            </form>
            
            <hr class="my-4">
            
            <div class="text-center">
                <a href="{{ route('admin.login') }}" class="btn btn-link text-muted">
                    <i class="fas fa-arrow-left me-1"></i>Back to Login
                </a>
            </div>
        </div>
    </div>
    <form id="resendForm" action="{{ route('admin.two-factor.resend') }}" method="POST" class="d-none">
        @csrf
    </form>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        $(document).ready(function() {
            // Auto-format code input
            $('#code').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
            
            // Auto-format recovery code input
            $('#recovery_code').on('input', function() {
                this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            });
            
            // Toggle between code and recovery form (if recovery form exists)
            if ($('#useRecoveryBtn').length) {
                $('#useRecoveryBtn').click(function() {
                    $('#codeForm').hide();
                    $('#recoveryForm').show();
                });
            }
            
            if ($('#useCodeBtn').length) {
                $('#useCodeBtn').click(function() {
                    $('#recoveryForm').hide();
                    $('#codeForm').show();
                });
            }
            
            $('#resendBtn').click(function() {
                $('#resendForm').trigger('submit');
            });
        });
    </script>
</body>
</html>

