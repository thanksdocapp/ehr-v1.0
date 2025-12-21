<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $emailData['subject'] }}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
            background-color: #f5f5f5;
        }
        .email-container {
            background-color: #ffffff;
            margin: 20px auto;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .email-header {
            background-color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 2px solid #f0f0f0;
        }
        .email-header img {
            max-width: 200px;
            max-height: 80px;
            height: auto;
            margin-bottom: 15px;
        }
        .clinic-name {
            font-size: 24px;
            font-weight: 600;
            color: #1a202c;
            margin: 0;
        }
        .email-body {
            padding: 30px 20px;
            font-size: 16px;
            color: #2d3748;
        }
        .email-body p {
            margin: 0 0 15px 0;
        }
        .email-body p:last-child {
            margin-bottom: 0;
        }
        .email-footer {
            background-color: #f8f9fa;
            padding: 25px 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #4a5568;
        }
        .email-footer p {
            margin: 8px 0;
        }
        .footer-section {
            margin-bottom: 20px;
        }
        .footer-section:last-child {
            margin-bottom: 0;
        }
        .footer-divider {
            border-top: 1px solid #e2e8f0;
            margin: 20px 0;
            padding-top: 20px;
        }
        .disclaimer {
            font-size: 12px;
            color: #718096;
            font-style: italic;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            @if(!empty($emailData['department_logo']))
                <img src="{{ $emailData['department_logo'] }}" alt="{{ $emailData['clinic_name'] }} Logo" />
            @endif
            <h1 class="clinic-name">{{ $emailData['clinic_name'] }}</h1>
        </div>

        <!-- Body -->
        <div class="email-body">
            {!! $emailData['body'] !!}
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <div class="footer-section">
                <p><strong>{{ $emailData['doctor_name'] }}</strong></p>
                @if(!empty($emailData['doctor_specialization']))
                    <p>{{ $emailData['doctor_specialization'] }}</p>
                @endif
                @if(!empty($emailData['department_name']))
                    <p>{{ $emailData['department_name'] }}</p>
                @endif
                <p>{{ $emailData['clinic_name'] }}</p>
                <p>{{ $emailData['date_sent'] }}</p>
            </div>

            <div class="footer-divider"></div>

            <div class="disclaimer">
                <p><strong>Disclaimer:</strong> This email is for informational purposes only. Please do not reply to this email as this mailbox is not monitored. For urgent medical concerns, please contact your healthcare provider directly or visit your nearest emergency department.</p>
            </div>
        </div>
    </div>

    <!-- Email Open Tracking Pixel -->
    @if(isset($trackingToken) && isset($emailLogId))
        <img src="{{ route('email.track', ['token' => $trackingToken, 'id' => $emailLogId]) }}" 
             width="1" 
             height="1" 
             style="display:none;" 
             alt="" />
    @endif
</body>
</html>

