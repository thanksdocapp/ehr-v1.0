<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - Patient Portal</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
            padding: 20px;
            line-height: 1.6;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #1a1a2e 0%, #e94560 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .email-header .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        .email-body {
            padding: 40px 30px;
        }
        .email-body h2 {
            color: #1a1a2e;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .email-body p {
            color: #666;
            font-size: 16px;
            margin-bottom: 15px;
        }
        .reset-button {
            display: inline-block;
            background: linear-gradient(135deg, #e94560 0%, #c73650 100%);
            color: white;
            text-decoration: none;
            padding: 15px 30px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 16px;
            margin: 20px 0;
            text-align: center;
        }
        .reset-button:hover {
            color: white;
            text-decoration: none;
        }
        .reset-link {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 4px;
            padding: 15px;
            word-break: break-all;
            font-family: monospace;
            font-size: 14px;
            color: #495057;
            margin: 20px 0;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            border-top: 1px solid #e9ecef;
            text-align: center;
        }
        .email-footer p {
            color: #6c757d;
            font-size: 14px;
            margin: 5px 0;
        }
        .security-notice {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .security-notice p {
            color: #856404;
            margin: 0;
            font-size: 14px;
        }
        .expiry-notice {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            border-radius: 4px;
            padding: 15px;
            margin: 20px 0;
        }
        .expiry-notice p {
            color: #721c24;
            margin: 0;
            font-size: 14px;
        }
        @media (max-width: 600px) {
            .email-container {
                margin: 0;
                border-radius: 0;
            }
            .email-body {
                padding: 30px 20px;
            }
            .email-footer {
                padding: 15px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="email-header">
            <div class="icon">🔒</div>
            <h1>Password Reset Request</h1>
        </div>
        
        <div class="email-body">
            <h2>Hello {{ $patient->first_name ?? 'Patient' }},</h2>
            
            <p>We received a request to reset your password for your Patient Portal account. If you made this request, please click the button below to reset your password:</p>
            
            <div style="text-align: center; margin: 30px 0;">
                <a href="{{ $resetUrl }}" class="reset-button">Reset Password</a>
            </div>
            
            <p>If the button doesn't work, you can copy and paste the following link into your browser:</p>
            
            <div class="reset-link">
                {{ $resetUrl }}
            </div>
            
            <div class="expiry-notice">
                <p><strong>⏰ Important:</strong> This password reset link will expire in 60 minutes for security reasons.</p>
            </div>
            
            <div class="security-notice">
                <p><strong>🛡️ Security Notice:</strong> If you did not request this password reset, please ignore this email. Your password will remain unchanged, and no action is needed.</p>
            </div>
            
            <p>For your security, please:</p>
            <ul>
                <li>Use a strong, unique password</li>
                <li>Don't share your login credentials</li>
                <li>Contact us immediately if you notice any suspicious activity</li>
            </ul>
            
            <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
            
            <p>Best regards,<br>
            <strong>{{ config('app.name', 'Hospital Management System') }} Team</strong></p>
        </div>
        
        <div class="email-footer">
            <p>This email was sent to {{ $patient->email ?? 'your email address' }}</p>
            <p>{{ config('app.name', 'Hospital Management System') }}</p>
            <p>© {{ date('Y') }} All rights reserved.</p>
        </div>
    </div>
</body>
</html>
