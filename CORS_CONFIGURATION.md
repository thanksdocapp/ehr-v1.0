# CORS Configuration for ThankDoc

## Production Domains

- **Main Domain:** `https://thanksdoc.co.uk`
- **EPR Subdomain:** `https://notes.thanksdoc.co.uk` (Electronic Patient Record)

## Environment Configuration

Add the following to your `.env` file for production:

```env
# CORS Configuration - ThankDoc Production Domains
CORS_ALLOWED_ORIGINS=https://thanksdoc.co.uk,https://www.thanksdoc.co.uk,https://notes.thanksdoc.co.uk
```

## Local Development

For local development, you can either:

1. **Leave empty** (will default to `['*']` in local environment):
```env
# CORS_ALLOWED_ORIGINS=
```

2. **Or specify local origins:**
```env
CORS_ALLOWED_ORIGINS=http://localhost:8000,http://127.0.0.1:8000,http://localhost:3000
```

## Configuration Details

The CORS configuration is set in `config/cors.php`:

- **Production:** Uses `CORS_ALLOWED_ORIGINS` from `.env` (comma-separated list)
- **Local:** Falls back to `['*']` if `CORS_ALLOWED_ORIGINS` is not set and `APP_ENV=local`

## Testing

After setting the CORS configuration:

1. **Test API calls from main domain:**
   ```bash
   curl -H "Origin: https://thanksdoc.co.uk" \
        -H "Access-Control-Request-Method: GET" \
        -X OPTIONS \
        https://thanksdoc.co.uk/api/v1/public/departments
   ```

2. **Test API calls from EPR subdomain:**
   ```bash
   curl -H "Origin: https://notes.thanksdoc.co.uk" \
        -H "Access-Control-Request-Method: GET" \
        -X OPTIONS \
        https://thanksdoc.co.uk/api/v1/public/departments
   ```

3. **Verify in browser console:**
   - Open browser DevTools
   - Check Network tab for CORS headers
   - Look for `Access-Control-Allow-Origin` header

## Security Notes

- ✅ Only specified origins are allowed
- ✅ Credentials are not supported (`supports_credentials: false`)
- ✅ Specific HTTP methods are allowed
- ✅ Specific headers are allowed
- ✅ CORS preflight cache is set to 3600 seconds

## Troubleshooting

### Issue: CORS errors in browser
**Solution:** 
- Verify `CORS_ALLOWED_ORIGINS` includes the exact origin (including protocol and port)
- Check that the origin matches exactly (case-sensitive, no trailing slash)

### Issue: API calls work locally but fail in production
**Solution:**
- Ensure `.env` file has `CORS_ALLOWED_ORIGINS` set
- Clear config cache: `php artisan config:clear`
- Verify `APP_ENV=production` in production

### Issue: EPR subdomain cannot access API
**Solution:**
- Ensure `https://notes.thanksdoc.co.uk` is in `CORS_ALLOWED_ORIGINS`
- Verify both domains use HTTPS
- Check that API endpoints are accessible from the subdomain

## Additional Domains

If you need to add more domains (e.g., mobile app domains, staging environments), add them to the comma-separated list:

```env
CORS_ALLOWED_ORIGINS=https://thanksdoc.co.uk,https://www.thanksdoc.co.uk,https://notes.thanksdoc.co.uk,https://staging.thanksdoc.co.uk
```

---

**Last Updated:** 2025-01-27  
**Configuration File:** `config/cors.php`  
**Environment Variable:** `CORS_ALLOWED_ORIGINS`

