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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 20px;
            text-align: center;
            color: #ffffff;
            position: relative;
            overflow: hidden;
        }
        .email-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            opacity: 0.3;
        }
        .email-header-content {
            position: relative;
            z-index: 1;
        }
        .email-header img {
            max-width: 180px;
            max-height: 70px;
            height: auto;
            width: auto;
            margin-bottom: 20px;
            display: inline-block;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }
        .clinic-name {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 8px 0;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            letter-spacing: 0.5px;
        }
        .patient-details-header {
            font-size: 14px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.95);
            margin: 12px 0 0 0;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
        }
        .patient-info-box {
            background-color: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .patient-info-item {
            font-size: 13px;
            color: #ffffff;
            margin: 5px 0;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 8px;
        }
        .patient-info-item strong {
            font-weight: 600;
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
            <div class="email-header-content">
                @if(!empty($emailData['clinic_logo']) || !empty($emailData['department_logo']))
                    <img src="{{ $emailData['clinic_logo'] ?? $emailData['department_logo'] }}" 
                         alt="{{ $emailData['clinic_name'] }} Logo" 
                         style="max-width: 180px; max-height: 70px; height: auto; width: auto; display: block; margin: 0 auto 20px auto; background-color: rgba(255, 255, 255, 0.95); padding: 10px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);" />
                @endif
                <h1 class="clinic-name">{{ $emailData['clinic_name'] }}</h1>
                <p class="patient-details-header">
                    <i class="fas fa-user-circle" style="margin-right: 6px;"></i>Patient Details
                </p>
                <div class="patient-info-box">
                    <div class="patient-info-item">
                        <strong>Name:</strong> {{ $emailData['patient_name'] ?? 'N/A' }}
                    </div>
                    @if(!empty($emailData['patient_id']))
                    <div class="patient-info-item">
                        <strong>Patient ID:</strong> {{ $emailData['patient_id'] }}
                    </div>
                    @endif
                </div>
            </div>
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
                <p>{{ $emailData['clinic_name'] }}</p>
                <p>{{ $emailData['date_sent'] }}</p>
            </div>

            <div class="footer-divider"></div>

            <div class="disclaimer">
                <p><strong>Disclaimer:</strong> This email is for informational purposes only. Please do not reply to this email as this mailbox is not monitored. For urgent medical concerns, please contact {{ $emailData['department_name'] ?? 'your healthcare provider' }} via {{ $emailData['doctor_phone'] ?? 'your healthcare provider' }} directly or visit your nearest emergency department.</p>
            </div>
        </div>
    </div>

    <!-- Email Open Tracking Pixel -->
    @if(isset($trackingToken) && isset($emailLogId))
        <img src="{{ config('app.url') }}/track/email/open/{{ $trackingToken }}/{{ $emailLogId }}" 
             width="1" 
             height="1" 
             style="display:none;" 
             alt="" />
    @endif
</body>
</html>

