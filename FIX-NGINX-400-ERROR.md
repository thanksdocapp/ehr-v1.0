# Fix Nginx 400 Error for quill-init.js

## Problem: HTTP 400 Bad Request

A 400 error means nginx is receiving the request but rejecting it. This is different from 404 (file not found).

---

## Common Causes

### 1. Location Block Syntax Error

The location block might have incorrect syntax. Try this simpler version:

```nginx
location /js/ {
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;
    try_files $uri =404;
}
```

### 2. Root Path Issue

The root path might be incorrect or causing conflicts. Check:

```bash
# Verify file exists at the path
sudo ls -la /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public/js/quill-init.js
```

### 3. Conflicting Location Blocks

Another location block might be intercepting the request. Try a more specific location:

```nginx
location = /js/quill-init.js {
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;
    add_header Content-Type application/javascript;
    try_files $uri =404;
}
```

### 4. Missing Content-Type Header

Add explicit Content-Type:

```nginx
location ~* \.(js)$ {
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;
    add_header Content-Type application/javascript;
    expires 1y;
    try_files $uri =404;
}
```

---

## Solution: Try This Step-by-Step

### Step 1: Remove Current Config

In Plesk "Additional nginx directives", remove any existing location blocks for static files.

### Step 2: Add This Simple Config

```nginx
location = /js/quill-init.js {
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;
    add_header Content-Type "application/javascript; charset=utf-8";
    try_files $uri =404;
}
```

### Step 3: Test

```bash
curl -I https://notes.thanksdoc.co.uk/js/quill-init.js
```

### Step 4: If That Works, Add Full Static Files Config

```nginx
# Specific file first (most specific)
location = /js/quill-init.js {
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;
    add_header Content-Type "application/javascript; charset=utf-8";
    try_files $uri =404;
}

# Then general JS files
location ~* \.(js)$ {
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;
    add_header Content-Type "application/javascript; charset=utf-8";
    expires 1y;
    try_files $uri =404;
}
```

---

## Alternative: Check Nginx Error Log

```bash
# Check nginx error log for details
sudo tail -50 /var/log/nginx/error.log | grep -i "400\|bad request\|quill"

# OR Plesk specific log
sudo tail -50 /var/www/vhosts/system/notes.thanksdoc.co.uk/logs/error_log
```

The error log will show WHY it's returning 400.

---

## Quick Fix: Use Absolute Path Test

Test if the file is accessible directly:

```bash
# Test file exists and is readable
sudo cat /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public/js/quill-init.js | head -5

# Test nginx can serve it (localhost)
curl -I http://localhost/js/quill-init.js
```

---

## Most Likely Fix

The 400 error often happens when:
1. **Root path is wrong** - Check actual file location
2. **Location block conflicts** - Use more specific location
3. **Missing Content-Type** - Add explicit header

**Try this minimal config first:**

```nginx
location = /js/quill-init.js {
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;
    try_files $uri =404;
}
```

Then test. If that works, gradually add more features.

---

## Verify File Path First

Before configuring, verify the exact path:

```bash
sudo find /var/www/vhosts/notes.thanksdoc.co.uk -name "quill-init.js" -type f 2>/dev/null
```

Use that exact path in the `root` directive (minus `/js/quill-init.js`).

---

**Share the nginx error log output if the simple config doesn't work!**

