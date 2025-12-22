# Troubleshoot Nginx Static Files Not Working

## Quick Checks

### 1. Test if file is accessible now

```bash
curl -I https://notes.thanksdoc.co.uk/js/quill-init.js
```

**What status do you get?** (200, 404, 403, etc.)

---

### 2. Check nginx error logs

```bash
# Check nginx error log
sudo tail -50 /var/log/nginx/error.log

# OR Plesk nginx log
sudo tail -50 /var/www/vhosts/system/notes.thanksdoc.co.uk/logs/error_log
```

Look for any errors related to the location block or the file.

---

### 3. Verify nginx config syntax

```bash
sudo nginx -t
```

Should say: `nginx: configuration file /etc/nginx/nginx.conf test is successful`

---

### 4. Check if location block is in the right place

**The static file location block MUST come BEFORE `location /` block.**

In Plesk "Additional nginx directives", it adds directives at the END by default, which might be AFTER the `location /` block.

---

## Solution: Use more specific location block

Try this instead - it's more specific and should work regardless of order:

```nginx
location ~* ^/js/.+\.js$ {
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
    try_files $uri =404;
}

location ~* \.(css|js|jpg|jpeg|gif|png|ico|xml|svg|woff|woff2|ttf|eot|otf|json)$ {
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
    try_files $uri =404;
}
```

---

## Alternative: Check if using PHP-FPM routing

If nginx is still passing requests to PHP, try this more explicit block:

```nginx
# Explicitly serve JS files from js directory
location ^~ /js/ {
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
    try_files $uri =404;
}
```

The `^~` prefix makes it match first before other location blocks.

---

## Debug: See what nginx is actually doing

Add logging to see what's happening:

```nginx
location ~* \.(js)$ {
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;
    access_log /var/log/nginx/js-access.log;
    error_log /var/log/nginx/js-error.log;
    try_files $uri =404;
}
```

Then check the logs:
```bash
sudo tail -f /var/log/nginx/js-access.log
curl https://notes.thanksdoc.co.uk/js/quill-init.js
```

---

## Check actual file path

Verify the file actually exists at the path you're using:

```bash
# Test the exact path from nginx config
sudo ls -la /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public/js/quill-init.js

# OR if using httpdocs (not httpdocs/public)
sudo ls -la /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/js/quill-init.js
```

---

## Plesk-specific: Location block priority

In Plesk, the "Additional nginx directives" are appended, so they might not override existing blocks.

**Try this workaround:**

1. Remove the location block from "Additional nginx directives"
2. Instead, edit the vhost file directly:

```bash
sudo nano /var/www/vhosts/notes.thanksdoc.co.uk/conf/vhost_nginx.conf
```

3. Find the `location /` block
4. Add the static file location block **BEFORE** `location /`
5. Save and reload:

```bash
sudo nginx -t
sudo /usr/local/psa/bin/nginxmng -u
```

---

## Most Common Issue: Path Mismatch

The `root` path in the location block must match where the file actually is.

**Check this:**
```bash
# Find where file actually is
sudo find /var/www/vhosts/notes.thanksdoc.co.uk -name "quill-init.js" -type f 2>/dev/null

# Example output: /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public/js/quill-init.js
# So root should be: /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public
```

**Then use that exact path in the root directive.**

---

## Quick Test Script

Run this to see what's happening:

```bash
#!/bin/bash
echo "=== Nginx Static File Debug ==="
echo ""

echo "1. File exists?"
sudo ls -la /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public/js/quill-init.js 2>&1
echo ""

echo "2. Nginx config test:"
sudo nginx -t 2>&1
echo ""

echo "3. Current nginx location blocks:"
sudo grep -A 5 "location" /var/www/vhosts/notes.thanksdoc.co.uk/conf/vhost_nginx.conf | head -30
echo ""

echo "4. Test file access:"
curl -I https://notes.thanksdoc.co.uk/js/quill-init.js 2>&1 | head -5
echo ""

echo "5. Nginx error log (last 10 lines):"
sudo tail -10 /var/log/nginx/error.log 2>&1
echo ""

echo "=== End Debug ==="
```

---

## Still Not Working?

Share:
1. Output of: `curl -I https://notes.thanksdoc.co.uk/js/quill-init.js`
2. Output of: `sudo nginx -t`
3. Where the file actually is: `sudo find /var/www/vhosts/notes.thanksdoc.co.uk -name "quill-init.js" -type f 2>/dev/null`

