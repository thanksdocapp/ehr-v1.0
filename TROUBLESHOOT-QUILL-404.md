# Troubleshoot quill-init.js 404 Even Though File Exists

## Problem
File exists on server but `https://notes.thanksdoc.co.uk/js/quill-init.js` returns 404.

## Possible Causes & Solutions

### 1. File Permissions Issue

**Check permissions:**
```bash
ls -la public/js/quill-init.js
```

**Should show:**
```
-rw-r--r-- 1 www-data www-data 141 quill-init.js
```

**If permissions are wrong:**
```bash
chmod 644 public/js/quill-init.js
chown www-data:www-data public/js/quill-init.js  # Adjust user/group as needed
```

---

### 2. Wrong File Location

**Verify file path:**
```bash
# On server
pwd  # Should show Laravel root (not public directory)

# Check if file exists
ls -la public/js/quill-init.js

# Full path should be:
/path/to/laravel/app/public/js/quill-init.js
```

**Web-accessible path:**
- File system: `public/js/quill-init.js`
- Web URL: `https://notes.thanksdoc.co.uk/js/quill-init.js`

---

### 3. .htaccess Blocking JS Files

**Check if .htaccess is blocking:**
```bash
# Check public/.htaccess
cat public/.htaccess
```

**Look for any rules that might block .js files.** The .htaccess should have:
```apache
# Serve existing static files directly
RewriteCond %{REQUEST_FILENAME} -f
RewriteRule ^ - [L]
```

**If JS files are blocked, add this to public/.htaccess:**
```apache
# Allow JS files
<FilesMatch "\.(js)$">
    Order allow,deny
    Allow from all
</FilesMatch>
```

---

### 4. Web Server Not Serving Static Files

**Test if web server can access the file:**
```bash
# From server
curl http://localhost/js/quill-init.js
# OR
wget http://localhost/js/quill-init.js

# Should return JavaScript code, not 404
```

**If localhost doesn't work:**
- Check web server configuration (Apache/Nginx)
- Verify DocumentRoot points to `public` directory
- Check if static file serving is enabled

---

### 5. Case Sensitivity Issue

**Check exact filename (case-sensitive):**
```bash
ls -la public/js/ | grep -i quill
```

**Ensure it's exactly:**
- `quill-init.js` (lowercase)
- NOT `Quill-init.js` or `QUILL-INIT.JS`

---

### 6. Laravel Public Path Issue

**Check if Laravel's public directory is correctly set:**

Verify in your web server config:
- **Apache:** DocumentRoot should point to `/path/to/app/public`
- **Nginx:** root should point to `/path/to/app/public`

**Test by accessing:**
```
https://notes.thanksdoc.co.uk/
```
Should load Laravel app, not show directory listing.

---

### 7. File Encoding or BOM Issue

**Check file encoding:**
```bash
file public/js/quill-init.js
```

**Should show:** `ASCII text` or `UTF-8 text`

**If it has BOM (Byte Order Mark), remove it:**
```bash
# Remove BOM if present
sed -i '1s/^\xEF\xBB\xBF//' public/js/quill-init.js
```

---

### 8. Web Server Cache

**Clear web server cache:**
```bash
# Apache
sudo service apache2 reload
# OR
sudo systemctl reload apache2

# Nginx
sudo service nginx reload
# OR
sudo systemctl reload nginx
```

---

## Quick Diagnostic Script

Run this on your server to diagnose:

```bash
#!/bin/bash
echo "=== Quill Init File Diagnostic ==="
echo ""
echo "1. File exists?"
ls -la public/js/quill-init.js 2>&1
echo ""
echo "2. File permissions:"
stat -c "%a %n" public/js/quill-init.js 2>&1
echo ""
echo "3. File size:"
wc -c public/js/quill-init.js 2>&1
echo ""
echo "4. First few lines:"
head -5 public/js/quill-init.js 2>&1
echo ""
echo "5. Web server can access (localhost test):"
curl -I http://localhost/js/quill-init.js 2>&1 | head -1
echo ""
echo "6. Actual URL test:"
curl -I https://notes.thanksdoc.co.uk/js/quill-init.js 2>&1 | head -1
echo ""
echo "=== End Diagnostic ==="
```

---

## Most Common Fixes

### Fix #1: Permissions (Most Common)
```bash
chmod 644 public/js/quill-init.js
```

### Fix #2: Web Server Reload
```bash
# Apache
sudo service apache2 reload

# Nginx  
sudo service nginx reload
```

### Fix #3: Clear Laravel Cache
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Fix #4: Verify Document Root
Check web server config points to `public` directory, not root.

---

## Still Not Working?

If file exists but still 404, check:

1. **Web server error logs:**
   ```bash
   # Apache
   tail -f /var/log/apache2/error.log
   
   # Nginx
   tail -f /var/log/nginx/error.log
   ```

2. **Browser Network tab:**
   - Check exact URL being requested
   - Check response headers
   - Look for redirects (301/302)

3. **Test with wget/curl:**
   ```bash
   # From server
   wget -O test.js https://notes.thanksdoc.co.uk/js/quill-init.js
   cat test.js
   # Should show JavaScript code
   ```

---

**Share the diagnostic script output if you need more help!**

