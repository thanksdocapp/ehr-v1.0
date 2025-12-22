# Fix Nginx Configuration for Static Files (quill-init.js 404)

## Problem Identified

- **Server:** Nginx (not Apache)
- **Issue:** Nginx is passing all requests (including static files) to PHP/Laravel
- **Result:** Laravel returns 404 because `/js/quill-init.js` is not a route

## Solution

Configure nginx to serve static files directly before passing to PHP.

---

## Nginx Configuration Fix

Edit your nginx server block configuration file:

```bash
# Find your nginx config file
sudo nano /etc/nginx/sites-available/notes.thanksdoc.co.uk
# OR
sudo nano /etc/nginx/sites-enabled/notes.thanksdoc.co.uk
# OR
sudo nano /etc/nginx/conf.d/notes.thanksdoc.co.uk.conf
```

### Update the `location` block:

Find the section that looks like this:

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

**Replace it with:**

```nginx
# Serve static files directly (images, CSS, JS, fonts, etc.)
location ~* \.(jpg|jpeg|gif|png|css|js|ico|xml|svg|woff|woff2|ttf|eot)$ {
    root /path/to/your/laravel/app/public;
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
    try_files $uri =404;
}

# Laravel routes
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

**OR use this more comprehensive version:**

```nginx
# Serve static files directly (CSS, JS, images, fonts, etc.)
location ~* \.(css|js|jpg|jpeg|gif|png|ico|xml|svg|woff|woff2|ttf|eot|otf|json)$ {
    root /path/to/your/laravel/app/public;
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
    try_files $uri =404;
}

# Serve JS files specifically (for quill-init.js)
location ~* ^/js/.+\.js$ {
    root /path/to/your/laravel/app/public;
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
    try_files $uri =404;
}

# Laravel routes
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

**IMPORTANT:** Replace `/path/to/your/laravel/app/public` with your actual Laravel public directory path.

---

## Complete Nginx Server Block Example

Here's a complete example server block configuration:

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name notes.thanksdoc.co.uk;
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name notes.thanksdoc.co.uk;
    
    root /var/www/your-app/public;  # CHANGE THIS to your actual path
    index index.php index.html index.htm;

    # SSL configuration (your existing SSL settings)
    ssl_certificate /path/to/cert.pem;
    ssl_certificate_key /path/to/key.pem;
    # ... your SSL settings ...

    # Serve static files directly (MUST come before location /)
    location ~* \.(css|js|jpg|jpeg|gif|png|ico|xml|svg|woff|woff2|ttf|eot|otf|json)$ {
        root /var/www/your-app/public;  # CHANGE THIS
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
        try_files $uri =404;
    }

    # Laravel routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP handler
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;  # Adjust PHP version
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
        access_log off;
        log_not_found off;
    }
}
```

---

## Steps to Apply Fix

### 1. Edit nginx configuration

```bash
sudo nano /etc/nginx/sites-available/notes.thanksdoc.co.uk
```

### 2. Add static file location block

Add the static file location block **BEFORE** the `location /` block (order matters in nginx).

### 3. Find your Laravel public path

```bash
# On your server, find where Laravel is installed
pwd  # If you're in the Laravel root
# Then the public path is: /path/to/app/public

# OR check nginx config for existing root directive
grep -r "root" /etc/nginx/sites-available/
```

### 4. Test nginx configuration

```bash
sudo nginx -t
```

Should output: `nginx: configuration file /etc/nginx/nginx.conf test is successful`

### 5. Reload nginx

```bash
sudo systemctl reload nginx
# OR
sudo service nginx reload
```

### 6. Test the file

```bash
curl -I https://notes.thanksdoc.co.uk/js/quill-init.js
```

Should now return: `HTTP/2 200` (not 404)

---

## Verify File Exists First

Before editing nginx, verify the file exists:

```bash
# Find Laravel root
cd /var/www/your-app  # Adjust path as needed

# Check if file exists
ls -la public/js/quill-init.js

# If missing, create it or pull from git
cd /path/to/your/app
git pull origin main
```

---

## Quick Fix Script

If you know your Laravel path, you can use this:

```bash
# Set your Laravel public path
LARAVEL_PUBLIC="/var/www/your-app/public"  # CHANGE THIS

# Find nginx config
NGINX_CONFIG=$(grep -r "notes.thanksdoc.co.uk" /etc/nginx/sites-available/ -l | head -1)

# Backup config
sudo cp $NGINX_CONFIG ${NGINX_CONFIG}.backup

# Add static file location (you'll need to edit manually)
echo "Static file location block needs to be added to: $NGINX_CONFIG"
echo "Add this BEFORE location / block:"
echo ""
echo "location ~* \.(css|js|jpg|jpeg|gif|png|ico|xml|svg|woff|woff2|ttf|eot|otf|json)\$ {"
echo "    root $LARAVEL_PUBLIC;"
echo "    expires 1y;"
echo "    add_header Cache-Control \"public, immutable\";"
echo "    access_log off;"
echo "    try_files \$uri =404;"
echo "}"
```

---

## Common Nginx Config Locations

- **Ubuntu/Debian:** `/etc/nginx/sites-available/` and `/etc/nginx/sites-enabled/`
- **CentOS/RHEL:** `/etc/nginx/conf.d/`
- **Custom:** Check with `nginx -T` to see all config files

---

## Troubleshooting

### If still 404 after fix:

1. **Check file path:**
   ```bash
   ls -la /var/www/your-app/public/js/quill-init.js
   ```

2. **Check nginx root path matches:**
   ```bash
   # In nginx config
   root /var/www/your-app/public;
   
   # Should match actual file location
   ```

3. **Check nginx error logs:**
   ```bash
   sudo tail -f /var/log/nginx/error.log
   ```

4. **Verify file permissions:**
   ```bash
   chmod 644 /var/www/your-app/public/js/quill-init.js
   chown www-data:www-data /var/www/your-app/public/js/quill-init.js
   ```

5. **Test nginx can read the file:**
   ```bash
   sudo -u www-data cat /var/www/your-app/public/js/quill-init.js | head -5
   ```

---

## After Fix

Once nginx is configured correctly:

1. ✅ Static files will be served directly by nginx
2. ✅ No PHP processing for `.js`, `.css`, etc.
3. ✅ Faster performance
4. ✅ `quill-init.js` will return 200 OK

---

**The key is: nginx must serve static files BEFORE passing to PHP/Laravel!**

