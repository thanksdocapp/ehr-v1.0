# Local Email Setup Guide for XAMPP

This guide explains how to receive 2FA emails and other email notifications when running the application locally on XAMPP.

## Option 1: Use Log Driver (Easiest - Recommended for Quick Testing)

This option writes all emails to Laravel's log file instead of actually sending them.

### Steps:

1. Open your `.env` file in the project root
2. Update these mail settings:

```env
MAIL_MAILER=log
MAIL_HOST=localhost
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@localhost"
MAIL_FROM_NAME="${APP_NAME}"
```

3. Clear config cache:
```bash
php artisan config:clear
```

4. **View emails**: Check `storage/logs/laravel.log` file - all emails will be logged there with full content.

**Pros**: 
- No setup required
- See all email content in log file
- Fast and simple

**Cons**: 
- Emails don't actually get sent
- Need to check log file manually

---

## Option 2: Use Mailtrap (Best for Testing - Recommended)

Mailtrap is a free service that captures emails for testing. You can see all emails in a web interface.

### Steps:

1. **Sign up for Mailtrap** (free): https://mailtrap.io/
2. **Get your credentials**:
   - Go to Mailtrap dashboard
   - Select "Inboxes" → "My Inbox"
   - Copy the SMTP credentials

3. **Update your `.env` file**:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="${APP_NAME}"
```

4. Clear config cache:
```bash
php artisan config:clear
```

5. **View emails**: Go to Mailtrap dashboard → Inboxes → My Inbox to see all captured emails

**Pros**: 
- See emails in a nice web interface
- Test email formatting
- Free tier available
- Works like real email

**Cons**: 
- Requires internet connection
- Need to sign up for account

---

## Option 3: Use Gmail SMTP (For Real Email Testing)

Use your Gmail account to send emails. Note: You'll need to use an "App Password" for this.

### Steps:

1. **Enable 2-Step Verification** on your Gmail account
2. **Generate App Password**:
   - Go to: https://myaccount.google.com/apppasswords
   - Select "Mail" and "Other (Custom name)"
   - Enter "Laravel Local" as name
   - Copy the generated 16-character password

3. **Update your `.env` file**:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_gmail@gmail.com
MAIL_PASSWORD=your_16_character_app_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your_gmail@gmail.com"
MAIL_FROM_NAME="${APP_NAME}"
```

4. Clear config cache:
```bash
php artisan config:clear
```

**Pros**: 
- Real emails sent to actual email addresses
- Test full email flow

**Cons**: 
- Requires Gmail account
- Need to set up App Password
- Limited by Gmail sending limits

---

## Option 4: Use MailHog (Local SMTP Server)

MailHog runs a local SMTP server that captures all emails. You view them in a web interface.

### Steps:

1. **Download MailHog**:
   - Windows: Download from https://github.com/mailhog/MailHog/releases
   - Extract and run `MailHog.exe`

2. **Update your `.env` file**:

```env
MAIL_MAILER=smtp
MAIL_HOST=127.0.0.1
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@localhost"
MAIL_FROM_NAME="${APP_NAME}"
```

3. Clear config cache:
```bash
php artisan config:clear
```

4. **View emails**: Open http://127.0.0.1:8025 in your browser to see all captured emails

**Pros**: 
- Works completely offline
- Nice web interface
- No account needed

**Cons**: 
- Need to download and run MailHog
- Only works on your local machine

---

## Quick Setup (Recommended for First Time)

For the quickest setup, use **Option 1 (Log Driver)**:

1. Open `.env` file
2. Change this line:
   ```
   MAIL_MAILER=log
   ```
3. Run:
   ```bash
   php artisan config:clear
   ```
4. Check `storage/logs/laravel.log` for all emails

---

## Testing Email Configuration

After setting up, test your email configuration:

1. Go to: Admin → Settings → Communication → Email Configuration
2. Use the "Test Email" feature
3. Or run:
   ```bash
   php artisan tinker
   ```
   Then:
   ```php
   Mail::raw('Test email', function($message) {
       $message->to('your-email@example.com')
                ->subject('Test Email');
   });
   ```

---

## Troubleshooting

### Emails not appearing?

1. **Check `.env` file** - Make sure values are correct (no quotes around values)
2. **Clear cache**: `php artisan config:clear`
3. **Check logs**: `storage/logs/laravel.log` for errors
4. **Check permissions**: Make sure `storage/logs` directory is writable

### For 2FA specifically:

- 2FA emails are sent when you enable 2FA in your profile
- Check the email template exists: Admin → Communication → Email Templates
- Look for templates with "2fa" or "two-factor" in the name

---

## Recommended Setup for Development

**Best combination:**
- Use **Mailtrap** for testing email formatting and content
- Use **Log driver** for quick debugging

Switch between them by changing `MAIL_MAILER` in `.env`:
- `MAIL_MAILER=log` - for log file
- `MAIL_MAILER=smtp` - for Mailtrap/Gmail

