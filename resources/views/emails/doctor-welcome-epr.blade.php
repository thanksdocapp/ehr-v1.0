<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ThanksDoc – Your EPR Login Details</title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f5f7fa;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color: #f5f7fa; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <tr>
                        <td style="padding: 30px;">
                            <p style="color: #2d3748; font-size: 16px; margin: 0 0 20px 0;">Dear {{ $doctor_name }},</p>
                            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">Welcome to ThanksDoc.</p>
                            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">You have been successfully registered as a doctor on the EPR platform.</p>
                            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">Please find your login details below to access the Electronic Patient Records platform:</p>
                            <div style="background-color: #f8f9fc; border-left: 4px solid #1a202c; padding: 15px; margin: 20px 0;">
                                <p style="margin: 5px 0; color: #2d3748; font-size: 14px;"><strong>Email:</strong> {{ $doctor_email }}</p>
                                <p style="margin: 5px 0; color: #2d3748; font-size: 14px;"><strong>Password:</strong> {{ $password }}</p>
                            </div>
                            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">You can log in here:</p>
                            <p style="margin: 0 0 20px 0;"><a href="{{ $login_url }}" style="color: #2563eb; text-decoration: none;">{{ $login_url }}</a></p>
                            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">After logging in for the first time, please complete the following steps:</p>
                            <ol style="color: #4a5568; font-size: 14px; line-height: 1.8; margin: 0 0 20px 0; padding-left: 20px;">
                                <li>Enable two-factor authentication (2FA). This is required before you can fully use the platform.</li>
                                <li>Set up and configure your services. These services must be created before they can be linked to your booking link and made available for patients.</li>
                            </ol>
                            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">Please note that you can only reset your password while logged into the platform. There is no "Forgot Password" function for security reasons. If you forget your password and are unable to log in, please contact the support team or, in an emergency, call {{ $support_phone }}.</p>
                            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">If you have any questions or need assistance, please contact us at <a href="mailto:{{ $support_email }}" style="color: #2563eb;">{{ $support_email }}</a>.</p>
                            <p style="color: #4a5568; font-size: 14px; line-height: 1.6; margin: 0 0 20px 0;">Thank you for joining ThanksDoc.</p>
                            <p style="color: #2d3748; font-size: 14px; margin: 20px 0 0 0;">Kind regards,</p>
                            <p style="color: #2d3748; font-size: 14px; margin: 5px 0 0 0;">The ThanksDoc Team</p>
                            <p style="color: #718096; font-size: 13px; margin: 10px 0 0 0;"><a href="{{ $website_url }}" style="color: #2563eb;">{{ $website_url }}</a></p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
