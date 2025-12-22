<style>
        /* IMPORTANT: Scope all preview styles so they don't affect the staff/admin page layout */
        .patient-email-preview {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
        }
        .patient-email-preview .email-container {
            background-color: #ffffff;
            margin: 0 auto;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }
        .patient-email-preview .email-header {
            background-color: #ffffff;
            padding: 30px 20px;
            text-align: center;
            border-bottom: 2px solid #f0f0f0;
        }
        .patient-email-preview .email-header img {
            max-width: 200px;
            max-height: 80px;
            height: auto;
            margin-bottom: 15px;
        }
        .patient-email-preview .clinic-name {
            font-size: 24px;
            font-weight: 600;
            color: #1a202c;
            margin: 0;
        }
        .patient-email-preview .email-body {
            padding: 30px 20px;
            font-size: 16px;
            color: #2d3748;
        }
        .patient-email-preview .email-body p {
            margin: 0 0 15px 0;
        }
        .patient-email-preview .email-body p:last-child {
            margin-bottom: 0;
        }
        .patient-email-preview .email-footer {
            background-color: #f8f9fa;
            padding: 25px 20px;
            border-top: 1px solid #e2e8f0;
            font-size: 14px;
            color: #4a5568;
        }
        .patient-email-preview .email-footer p {
            margin: 8px 0;
        }
        .patient-email-preview .footer-section {
            margin-bottom: 20px;
        }
        .patient-email-preview .footer-section:last-child {
            margin-bottom: 0;
        }
        .patient-email-preview .footer-divider {
            border-top: 1px solid #e2e8f0;
            margin: 20px 0;
            padding-top: 20px;
        }
        .patient-email-preview .disclaimer {
            font-size: 12px;
            color: #718096;
            font-style: italic;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #e2e8f0;
        }
        /* Basic content hygiene for rich text */
        .patient-email-preview .email-body img { max-width: 100%; height: auto; }
        .patient-email-preview .email-body table { width: 100%; border-collapse: collapse; }
        .patient-email-preview .email-body table td,
        .patient-email-preview .email-body table th { border: 1px solid #e3e6f0; padding: 8px; }
</style>

<div class="patient-email-preview">
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
                <p>{{ $emailData['department_name'] ?? $emailData['clinic_name'] }}</p>
                <p>{{ $emailData['date_sent'] }}</p>
            </div>

            <div class="footer-divider"></div>

            <div class="disclaimer">
                <p><strong>Disclaimer:</strong> This email is for informational purposes only. Please do not reply to this email as this mailbox is not monitored. For any queries, please contact {{ $emailData['department_name'] ?? 'your healthcare provider' }} directly. For emergencies, please 999.</p>
            </div>
        </div>
    </div>
</div>
