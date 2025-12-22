# 🚀 Production Deployment Security Checklist

## Pre-Deployment

### ✅ 1. Environment Configuration
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Generate unique `APP_KEY` with `php artisan key:generate`
- [ ] Backup `APP_KEY` to secure vault (AWS Secrets Manager, 1Password, etc.)
- [ ] Set `APP_URL` to your HTTPS domain
- [ ] Configure database SSL certificates (`MYSQL_ATTR_SSL_CA`)

### ✅ 2. HTTPS & SSL
- [ ] Obtain valid SSL certificate (Let's Encrypt, commercial CA)
- [ ] Configure web server (Nginx/Apache) for HTTPS
- [ ] Set `SESSION_SECURE_COOKIE=true`
- [ ] Set `SESSION_SAME_SITE=strict`
- [ ] Test HTTPS redirect (HTTP → HTTPS)

### ✅ 3. Database Security
- [ ] Enable MySQL SSL/TLS connection
- [ ] Set strong database passwords
- [ ] Limit database user permissions (principle of least privilege)
- [ ] Enable database connection encryption
- [ ] Consider MySQL Transparent Data Encryption (TDE)

### ✅ 4. Encrypt Existing Data
```bash
# CRITICAL: Backup database first!
mysqldump -u user -p database > backup_pre_encryption.sql

# Run encryption migration
php artisan data:encrypt-existing --model=all

# Verify encryption worked
php artisan tinker
> \App\Models\Patient::first()->insurance_number;  # Should decrypt correctly
```

### ✅ 5. File Permissions
```bash
chmod 600 .env                          # Only owner can read
chmod -R 775 storage                    # Web server can write
chmod -R 775 bootstrap/cache           # Laravel can cache
chown -R www-data:www-data storage     # Web server owns storage
chown -R www-data:www-data bootstrap/cache
```

### ✅ 6. Stripe Configuration
- [ ] Enter LIVE Stripe keys in admin panel
- [ ] Set `test_mode=false` for live payments
- [ ] Configure webhook endpoints
- [ ] Test payment flow end-to-end
- [ ] Verify webhook signature validation

### ✅ 7. Email Security
- [ ] Configure SMTP with TLS/SSL
- [ ] Set valid `MAIL_FROM_ADDRESS`
- [ ] Test email delivery
- [ ] Verify email open tracking works

### ✅ 8. Session & Auth Security
- [ ] Set `SESSION_LIFETIME` appropriately (120 minutes recommended)
- [ ] Enable 2FA for all admin/doctor accounts
- [ ] Test session timeout
- [ ] Verify CSRF protection works

## Post-Deployment

### ✅ 9. Verification Tests
- [ ] Test login flow (admin, doctor, patient)
- [ ] Test 2FA authentication
- [ ] Create test patient record - verify encryption
- [ ] Create test medical record - verify encryption
- [ ] Create test prescription - verify encryption
- [ ] Test payment link generation
- [ ] Complete test payment (use test card if test mode)
- [ ] Test email sending with PHI
- [ ] Verify HTTPS redirects work

### ✅ 10. Security Hardening
- [ ] Disable directory listing
- [ ] Hide server version headers
- [ ] Configure rate limiting
- [ ] Set up Web Application Firewall (WAF)
- [ ] Configure fail2ban or similar
- [ ] Set up monitoring and alerts

### ✅ 11. Backup Strategy
- [ ] Schedule automated daily backups
- [ ] Encrypt backup files
- [ ] Store backups in separate location
- [ ] Test backup restoration
- [ ] Include `APP_KEY` in backup metadata (encrypted separately)

### ✅ 12. Monitoring & Logging
- [ ] Set up application monitoring (New Relic, Datadog)
- [ ] Configure error reporting (Sentry, Rollbar)
- [ ] Enable Laravel log rotation
- [ ] Monitor encryption/decryption errors
- [ ] Set up alerts for failed decryption attempts

## Compliance Documentation

### ✅ 13. HIPAA Requirements
- [ ] Document encryption implementation
- [ ] Create security policies
- [ ] Train staff on data handling
- [ ] Establish breach notification procedures
- [ ] Conduct security risk assessment
- [ ] Sign Business Associate Agreements (BAAs)

### ✅ 14. GDPR Requirements
- [ ] Document data processing activities
- [ ] Implement right to erasure (account deletion)
- [ ] Create privacy policy
- [ ] Set up data export functionality
- [ ] Establish data retention policies

## Emergency Procedures

### If APP_KEY is Compromised
1. Immediately rotate key
2. Force all users to log out
3. Require password reset for all accounts
4. Re-encrypt all data with new key
5. Notify affected parties (breach notification)
6. Document incident

### If Database is Breached
1. Isolate affected systems
2. Review access logs
3. Assess scope of data exposure
4. Notify regulatory authorities (within 72 hours for GDPR)
5. Notify affected patients
6. Conduct forensic analysis

## Ongoing Maintenance

### Daily
- Review error logs
- Monitor failed login attempts
- Check system resource usage

### Weekly
- Review user activity logs
- Check backup completion
- Test backup restoration (sample)

### Monthly
- Review access permissions
- Update dependencies (security patches)
- Test disaster recovery plan

### Quarterly
- Security audit
- Penetration testing
- Review and update security policies
- Staff security training

### Annually
- Full security assessment
- Consider key rotation
- Review compliance documentation
- Third-party security audit

---

**Remember**: Security is an ongoing process, not a one-time task!

