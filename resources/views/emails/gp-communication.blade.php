<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GP Communication</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f7fa; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background-color: #1a202c; padding: 30px; text-align: center;">
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px; font-weight: 600;">{{ $hospital_name }}</h1>
                            <p style="color: #cbd5e1; margin: 10px 0 0 0; font-size: 14px;">GP Communication</p>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="color: #2d3748; font-size: 16px; margin: 0 0 20px 0; font-weight: 500;">Dear {{ $gp_name }},</p>
                            
                            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">
                                This email is regarding your patient:
                            </p>
                            
                            <div style="background-color: #f8f9fc; border-left: 4px solid #1a202c; padding: 15px; margin: 20px 0;">
                                <p style="margin: 5px 0; color: #2d3748; font-size: 14px;">
                                    <strong>Patient Name:</strong> {{ $patient_name }}
                                </p>
                                <p style="margin: 5px 0; color: #2d3748; font-size: 14px;">
                                    <strong>Patient ID:</strong> {{ $patient_id }}
                                </p>
                                <p style="margin: 5px 0; color: #2d3748; font-size: 14px;">
                                    <strong>Date of Birth:</strong> {{ $patient_dob }}
                                </p>
                            </div>
                            
                            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 20px 0;">
                                <strong>Message from {{ $doctor_name }}:</strong>
                            </p>
                            
                            <div style="background-color: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 20px; margin: 20px 0;">
                                <div style="color: #2d3748; font-size: 14px; line-height: 1.8;">
                                    {!! $message !!}
                                </div>
                            </div>
                            
                            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 20px 0 0 0;">
                                If you have any questions or need further information, please do not hesitate to contact us.
                            </p>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f8f9fc; padding: 20px; border-top: 1px solid #e2e8f0;">
                            <p style="color: #a0aec0; font-size: 11px; margin: 0; text-align: center;">
                                This is an automated message sent on {{ $date }} at {{ $time }}.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

