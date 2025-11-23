<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ThanksDoc EPR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .verification-container {
            max-width: 450px;
            margin: 50px auto;
        }
        .card {
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .card-header {
            background-color: #0d6efd;
            color: white;
            text-align: center;
            padding: 1.5rem;
            border-bottom: none;
        }
        .avatar-container {
            position: relative;
            width: 120px;
            height: 120px;
            margin: 0 auto;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid white;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            margin-bottom: 15px;
        }
        .avatar-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .pin-input-group {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 1.5rem 0;
        }
        .pin-input {
            width: 60px;
            height: 60px;
            text-align: center;
            font-size: 24px;
            border: 2px solid #ced4da;
            border-radius: 8px;
        }
        .pin-input:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .btn-verify {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 8px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 1rem;
            color: #6c757d;
            text-decoration: none;
        }
        .back-link:hover {
            color: #0d6efd;
        }
        .alert {
            border-radius: 8px;
        }
        .hidden-pin {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }
        .user-info {
            text-align: center;
        }
        .user-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .user-email {
            font-size: 1rem;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="container verification-container">
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Account Verification</h4>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger">
                        @foreach ($errors->all() as $error)
                            {{ $error }}<br>
                        @endforeach
                    </div>
                @endif

                <div class="avatar-container">
                    @if($user->avatar)
                        <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}'s profile picture">
                    @else
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=0D8ABC&color=fff" alt="Default avatar">
                    @endif
                </div>

                <div class="user-info mb-4">
                    <div class="user-name">{{ $user->name }}</div>
                    <div class="user-email">{{ $user->email }}</div>
                    <p class="mt-2 text-muted">Please enter your 4-digit PIN to verify your identity</p>
                </div>

                <form action="{{ route('verification.verify') }}" method="POST">
                    @csrf
                    <div class="pin-input-group">
                        <input type="text" class="form-control pin-input" id="pin1" maxlength="1" pattern="[0-9]" inputmode="numeric" autofocus>
                        <input type="text" class="form-control pin-input" id="pin2" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="form-control pin-input" id="pin3" maxlength="1" pattern="[0-9]" inputmode="numeric">
                        <input type="text" class="form-control pin-input" id="pin4" maxlength="1" pattern="[0-9]" inputmode="numeric">
                    </div>
                    
                    <!-- Hidden field that will hold the actual PIN value -->
                    <input type="hidden" name="pin_code" id="pin_code" required>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-verify">
                            Verify & Login
                        </button>
                    </div>
                </form>
                
                <a href="{{ route('login') }}" class="back-link">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const pinInputs = document.querySelectorAll('.pin-input');
            const pinCodeInput = document.getElementById('pin_code');
            
            // Set focus to the first input
            pinInputs[0].focus();
            
            // Auto-advance to next input and update hidden field
            pinInputs.forEach((input, index) => {
                input.addEventListener('input', function(e) {
                    // Allow only numbers
                    this.value = this.value.replace(/[^0-9]/g, '');
                    
                    // Move to next input if this one is filled
                    if (this.value.length === 1 && index < pinInputs.length - 1) {
                        pinInputs[index + 1].focus();
                    }
                    
                    // Update hidden field with complete PIN
                    updatePinCode();
                });
                
                // Handle backspace to go back to previous input
                input.addEventListener('keydown', function(e) {
                    if (e.key === 'Backspace' && this.value.length === 0 && index > 0) {
                        pinInputs[index - 1].focus();
                    }
                });
            });
            
            // Handle form submission
            document.querySelector('form').addEventListener('submit', function(e) {
                updatePinCode();
                
                // Validate that we have a complete 4-digit PIN
                if (pinCodeInput.value.length !== 4) {
                    e.preventDefault();
                    alert('Please enter all 4 digits of your PIN code.');
                }
            });
            
            // Update the hidden PIN code field
            function updatePinCode() {
                let pin = '';
                pinInputs.forEach(input => {
                    pin += input.value;
                });
                pinCodeInput.value = pin;
            }
        });
    </script>
</body>
</html>
