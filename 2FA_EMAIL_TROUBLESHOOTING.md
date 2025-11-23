# 2FA Email Troubleshooting Guide

## Issue: 2FA verification emails not being received

This guide helps diagnose and fix issues with 2FA email delivery.

## Common Causes

1. **Email Template Missing or Inactive**
   - The `two_factor_code` email template must exist and be active
   - Check: Admin > Email Templates > Search for "two_factor_code"
   - If missing, the system will auto-create it, but verify it's active

2. **SMTP Configuration Not Set**
   - SMTP settings must be configured in the database
   - Check: Admin > Settings > Email Configuration
   - Required settings:
     - SMTP Host
     - SMTP Port (usually 587 for TLS, 465 for SSL)
     - SMTP Username
     - SMTP Password
     - SMTP Encryption (TLS or SSL)
     - From Email Address

3. **Email Logs Show Errors**
   - Check: Admin > Email Logs (or database `email_logs` table)
   - Look for entries with status "failed"
   - Check the `error_message` column for specific errors

4. **Email Going to Spam**
   - Check spam/junk folder
   - Verify sender email is properly configured
   - Check SPF/DKIM records for your domain

## How to Check Email Logs

1. **Via Admin Panel:**
   - Navigate to Admin > Email Logs
   - Filter by template: "two_factor_code"
   - Check status and error messages

2. **Via Database:**
   ```sql
   SELECT * FROM email_logs 
   WHERE email_template_id IN (
       SELECT id FROM email_templates WHERE name = 'two_factor_code'
   )
   ORDER BY created_at DESC 
   LIMIT 10;
   ```

3. **Via Laravel Logs:**
   ```bash
   tail -f storage/logs/laravel.log | grep -i "2FA\|email\|smtp"
   ```

## Quick Fixes

### 1. Verify Email Template Exists
```php
// Run in tinker or create a route
$template = \App\Models\EmailTemplate::where('name', 'two_factor_code')->first();
if (!$template) {
    // Template will be auto-created on next 2FA enable attempt
    echo "Template missing - will be created automatically";
} else {
    echo "Template exists, status: " . $template->status;
    if ($template->status !== 'active') {
        $template->update(['status' => 'active']);
        echo " - Activated";
    }
}
```

### 2. Test SMTP Configuration
- Go to Admin > Settings > Email Configuration
- Use the "Test Email" feature if available
- Or send a test email to yourself

### 3. Check Email Settings in Database
```sql
SELECT * FROM site_settings 
WHERE `key` IN ('smtp_host', 'smtp_port', 'smtp_username', 'smtp_encryption', 'from_email');
```

## Recent Improvements

The following improvements have been made to help diagnose email issues:

1. **Better Error Messages:**
   - More specific error messages when email fails
   - Links to email logs and configuration pages
   - Clearer instructions on what to check

2. **Auto-Activation of Templates:**
   - If template exists but is inactive, it will be auto-activated
   - Template will be auto-created if missing

3. **Enhanced Logging:**
   - More detailed error logging
   - SMTP connection errors are specifically identified
   - Full error traces for debugging

4. **Improved Error Handling:**
   - Better exception handling in email service
   - More informative error messages in UI
   - Email log entries show specific failure reasons

## Testing 2FA Email

1. **Enable 2FA:**
   - Go to Profile > Two-Factor Authentication
   - Click "Enable 2FA"
   - Select "Email" method
   - Check email inbox (and spam folder)

2. **Resend Code:**
   - If code not received, click "Resend Code"
   - Check email logs for any errors
   - Verify SMTP settings are correct

3. **Check Email Logs:**
   - After attempting to send, check email logs
   - Status should be "sent" if successful
   - If "failed", check error_message for details

## Production Checklist

Before deploying to production, ensure:

- [ ] SMTP settings are configured in Admin > Settings > Email Configuration
- [ ] Email template "two_factor_code" exists and is active
- [ ] Test email can be sent successfully
- [ ] From email address is valid and verified
- [ ] SPF/DKIM records are set up for your domain (reduces spam)
- [ ] Email logs are being monitored

## Support

If emails still don't work after checking the above:

1. Check Laravel logs: `storage/logs/laravel.log`
2. Check email logs in database: `email_logs` table
3. Verify SMTP credentials with your email provider
4. Test SMTP connection using a tool like `telnet` or `openssl`
5. Contact your hosting provider if SMTP ports are blocked

