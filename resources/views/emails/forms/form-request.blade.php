<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Please Complete This Form - {{ config('app.name') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background: #ffffff;
            padding: 30px;
            border: 1px solid #e0e0e0;
            border-top: none;
        }
        .footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border: 1px solid #e0e0e0;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .info-box {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .info-box table {
            width: 100%;
        }
        .info-box td {
            padding: 5px 0;
        }
        .info-box td:first-child {
            font-weight: bold;
            width: 100px;
        }
        .message-box {
            background: #e8f4fd;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
        }
        .btn {
            display: inline-block;
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 20px 0;
            font-weight: bold;
            font-size: 16px;
        }
        .btn-container {
            text-align: center;
            margin: 30px 0;
        }
        .expires-notice {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 10px 15px;
            margin: 20px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1 style="margin: 0;">Form Request</h1>
    </div>

    <div class="content">
        <p>Hello {{ $formRequest->patient->first_name ?? 'Patient' }},</p>

        <p>You have been requested to complete a form by {{ $formRequest->requester->name ?? 'your healthcare provider' }}.</p>

        <div class="info-box">
            <table>
                <tr>
                    <td>Form:</td>
                    <td>{{ $formRequest->template->name }}</td>
                </tr>
                <tr>
                    <td>From:</td>
                    <td>{{ config('app.name') }}</td>
                </tr>
            </table>
        </div>

        @if($customMessage)
            <div class="message-box">
                <strong>Message from sender:</strong><br>
                {{ $customMessage }}
            </div>
        @endif

        <div class="btn-container">
            <a href="{{ $formRequest->getPublicUrl() }}" class="btn">
                Complete Form Now
            </a>
        </div>

        @if($formRequest->expires_at)
            <div class="expires-notice">
                <strong>Important:</strong> This form link will expire on {{ $formRequest->expires_at->format('F d, Y') }}.
                Please complete it before then.
            </div>
        @endif

        <p>If you have any questions, please contact your healthcare provider.</p>

        <p>
            Best regards,<br>
            {{ config('app.name') }} Team
        </p>
    </div>

    <div class="footer">
        <p>This email was sent from {{ config('app.name') }}.</p>
        <p>If you did not expect this email, please ignore it.</p>
        <p style="font-size: 10px; color: #999; margin-top: 15px;">
            Link not working? Copy and paste this URL into your browser:<br>
            {{ $formRequest->getPublicUrl() }}
        </p>
    </div>
</body>
</html>
