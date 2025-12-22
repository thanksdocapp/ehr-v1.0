# Find Document Root Using SSH

## Method 1: Check Plesk Domain Settings

```bash
# Find domain ID
sudo /usr/local/psa/bin/domain --info notes.thanksdoc.co.uk | grep "Document root"

# OR
sudo plesk bin site -l | grep notes.thanksdoc.co.uk
```

## Method 2: Check Plesk Database

```bash
# Query Plesk database for document root
sudo /usr/local/psa/bin/domain --info notes.thanksdoc.co.uk
```

## Method 3: Find Laravel Public Directory

```bash
# Search for Laravel app structure
find /var/www/vhosts/notes.thanksdoc.co.uk -name "artisan" -type f 2>/dev/null

# If found, the public directory is:
# /path/to/artisan's/directory/public

# Then check if quill-init.js exists
find /var/www/vhosts/notes.thanksdoc.co.uk -name "quill-init.js" -type f 2>/dev/null
```

## Method 4: Check nginx Config (Current Active Config)

```bash
# Check what nginx is actually using
sudo /usr/sbin/nginx -T 2>&1 | grep -A 5 "notes.thanksdoc.co.uk" | grep root

# OR
sudo grep -r "root" /var/www/vhosts/notes.thanksdoc.co.uk/conf/ 2>/dev/null
```

## Method 5: Check Plesk Vhost Config File

```bash
# Plesk stores configs here
cat /var/www/vhosts/notes.thanksdoc.co.uk/conf/vhost_nginx.conf | grep root

# OR Apache config
cat /var/www/vhosts/notes.thanksdoc.co.uk/conf/vhost.conf | grep DocumentRoot
```

## Method 6: List Directory Structure

```bash
# Check common Plesk structure
ls -la /var/www/vhosts/notes.thanksdoc.co.uk/

# Check if httpdocs or httpdocs/public exists
ls -la /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/
ls -la /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public/ 2>/dev/null

# Check for quill-init.js
find /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs -name "quill-init.js" 2>/dev/null
```

## Method 7: Quick One-Liner to Find Document Root

```bash
# Find where quill-init.js actually is
sudo find /var/www/vhosts/notes.thanksdoc.co.uk -name "quill-init.js" -type f 2>/dev/null

# This will show the full path, document root is the parent of 'js' directory
# Example: /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public/js/quill-init.js
# Document root = /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public
```

## Method 8: Check Plesk PHP Settings

```bash
# Check PHP handler config (might show document root)
cat /var/www/vhosts/notes.thanksdoc.co.uk/conf/vhost.conf | grep -i "documentroot\|document root"
```

---

## Quick Diagnostic Script

Run this to find everything:

```bash
#!/bin/bash
echo "=== Finding Document Root for notes.thanksdoc.co.uk ==="
echo ""

echo "1. Plesk domain info:"
sudo /usr/local/psa/bin/domain --info notes.thanksdoc.co.uk 2>/dev/null | grep -i "document\|root\|httpdocs" || echo "Plesk CLI not available"
echo ""

echo "2. Nginx config root:"
sudo grep -r "root" /var/www/vhosts/notes.thanksdoc.co.uk/conf/vhost_nginx.conf 2>/dev/null | grep -v "^#" | head -3
echo ""

echo "3. Apache config DocumentRoot:"
sudo grep -i "documentroot" /var/www/vhosts/notes.thanksdoc.co.uk/conf/vhost.conf 2>/dev/null | grep -v "^#"
echo ""

echo "4. Where is quill-init.js?"
sudo find /var/www/vhosts/notes.thanksdoc.co.uk -name "quill-init.js" -type f 2>/dev/null
echo ""

echo "5. Directory structure:"
ls -la /var/www/vhosts/notes.thanksdoc.co.uk/ 2>/dev/null | head -10
echo ""

echo "6. httpdocs structure:"
ls -la /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/ 2>/dev/null | head -10
echo ""

echo "=== End Diagnostic ==="
```

---

## Most Common Plesk Document Root Paths

- **Standard:** `/var/www/vhosts/notes.thanksdoc.co.uk/httpdocs`
- **Laravel with public:** `/var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public`
- **Subdomain:** `/var/www/vhosts/notes.thanksdoc.co.uk/subdomains/www/httpdocs`
- **Custom:** Check in Plesk GUI or use methods above

---

## After Finding Document Root

Once you know the document root, use it in the nginx config:

```nginx
location ~* \.(css|js|jpg|jpeg|gif|png|ico|xml|svg|woff|woff2|ttf|eot|otf|json)$ {
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;  # Use your actual path here
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
    try_files $uri =404;
}
```

