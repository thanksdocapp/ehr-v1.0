# Find Nginx Configuration File

## Method 1: Search for the config file

```bash
# Search for files containing your domain
sudo find /etc/nginx -type f -name "*.conf" | xargs grep -l "notes.thanksdoc.co.uk"

# OR search in all nginx directories
sudo grep -r "notes.thanksdoc.co.uk" /etc/nginx/
```

## Method 2: List all nginx config files

```bash
# Check sites-available
ls -la /etc/nginx/sites-available/

# Check sites-enabled
ls -la /etc/nginx/sites-enabled/

# Check conf.d
ls -la /etc/nginx/conf.d/

# Check main nginx.conf
cat /etc/nginx/nginx.conf | grep include
```

## Method 3: Use nginx -T to see all configs

```bash
# This shows all configuration files nginx is using
sudo nginx -T 2>&1 | grep -A 20 "notes.thanksdoc.co.uk"
```

## Method 4: Check common locations

```bash
# Ubuntu/Debian
/etc/nginx/sites-available/
/etc/nginx/sites-enabled/
/etc/nginx/conf.d/

# CentOS/RHEL
/etc/nginx/conf.d/

# Custom installations
/usr/local/nginx/conf/
/opt/nginx/conf/
```

## Method 5: Check which config is active

```bash
# See what nginx is actually using
sudo nginx -T | grep "server_name notes.thanksdoc.co.uk" -B 10 -A 30
```

---

## Once Found, Edit It

```bash
# Replace with actual path found above
sudo nano /path/to/nginx/config/file
```

---

## Alternative: Check if using a control panel

If you're using a control panel (cPanel, Plesk, etc.), the config might be managed there:

```bash
# Check for control panel
which plesk
which cpanel

# Or check common panel configs
ls -la /etc/nginx/conf.d/ | grep -i panel
```

