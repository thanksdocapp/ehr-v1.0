# Configure Nginx Static Files in Plesk GUI

## Method 1: Additional nginx Directives (Recommended)

### Steps:

1. **Log into Plesk Panel**
   - Go to: `https://your-server-ip:8443` (or your Plesk URL)

2. **Navigate to Your Domain**
   - Click on **"Websites & Domains"** in the left menu
   - Click on **"notes.thanksdoc.co.uk"**

3. **Open Apache & nginx Settings**
   - Scroll down or look for **"Apache & nginx Settings"**
   - Click on it

4. **Add Custom nginx Directives**
   - Scroll to the bottom of the page
   - Find **"Additional directives for nginx"** or **"Custom nginx configuration"**
   - Add this configuration:

```nginx
# Serve static files directly (CSS, JS, images, fonts)
location ~* \.(css|js|jpg|jpeg|gif|png|ico|xml|svg|woff|woff2|ttf|eot|otf|json)$ {
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs;  # Plesk default path
    expires 1y;
    add_header Cache-Control "public, immutable";
    access_log off;
    try_files $uri =404;
}
```

5. **Apply Changes**
   - Click **"OK"** or **"Apply"**
   - Plesk will automatically reload nginx

---

## Method 2: Using Plesk File Manager (Manual Edit)

If Method 1 doesn't work or you need more control:

1. **Log into Plesk**
2. **Go to File Manager**
   - Click on your domain
   - Go to **"Files"** tab
   - Or use **"File Manager"** in the left menu

3. **Navigate to nginx config**
   - The config files are usually at:
     ```
     /var/www/vhosts/system/notes.thanksdoc.co.uk/conf/nginx.conf
     ```
   - But you need to edit the vhost file:
     ```
     /var/www/vhosts/notes.thanksdoc.co.uk/conf/vhost_nginx.conf
     ```

4. **Edit the File**
   - Find the `location /` block
   - Add the static file location block **BEFORE** it

---

## Method 3: SSH + Plesk Config File

If you have SSH access:

1. **Find Plesk nginx config file:**
   ```bash
   sudo find /var/www/vhosts -name "*nginx*" -type f | grep notes.thanksdoc.co.uk
   ```

2. **Edit the vhost nginx config:**
   ```bash
   sudo nano /var/www/vhosts/notes.thanksdoc.co.uk/conf/vhost_nginx.conf
   ```

3. **Add the static file location block BEFORE `location /`**

4. **Reload nginx:**
   ```bash
   sudo /usr/sbin/nginx -t
   sudo /usr/sbin/nginx -s reload
   ```

---

## Important Notes for Plesk

### Default Document Root
Plesk typically uses:
```
/var/www/vhosts/notes.thanksdoc.co.uk/httpdocs
```

But if your Laravel app is installed differently, it might be:
```
/var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public
```

### Check Your Actual Path
In Plesk GUI:
1. Go to **"Websites & Domains"** → Your domain
2. Click **"Hosting Settings"**
3. Check **"Document root"** - this shows your actual path

### Laravel Setup in Plesk
If Laravel is in a subdirectory or the document root points to `httpdocs` (not `httpdocs/public`), you may need to:

1. **Change Document Root** in Plesk:
   - Go to **"Hosting Settings"**
   - Change **"Document root"** to: `httpdocs/public`
   - Save

2. **OR** keep current setup and adjust the nginx config accordingly

---

## Complete nginx Config for Plesk

If editing via SSH, your `/var/www/vhosts/notes.thanksdoc.co.uk/conf/vhost_nginx.conf` should have:

```nginx
server {
    # ... existing server config ...
    
    root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;  # Adjust if needed
    
    # STATIC FILES - Add this block
    location ~* \.(css|js|jpg|jpeg|gif|png|ico|xml|svg|woff|woff2|ttf|eot|otf|json)$ {
        root /var/www/vhosts/notes.thanksdoc.co.uk/httpdocs/public;
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
        try_files $uri =404;
    }

    # Existing location / block
    location / {
        # ... existing config ...
    }
}
```

---

## Quick Check: Verify File Path

Before configuring, verify where your Laravel app actually is:

**In Plesk GUI:**
1. Go to **"File Manager"**
2. Navigate to your domain folder
3. Check if you see:
   - `public/js/quill-init.js` → If yes, document root should be `httpdocs/public`
   - OR `httpdocs/public/js/quill-init.js` exists

**OR via SSH:**
```bash
# Find Laravel app
find /var/www/vhosts/notes.thanksdoc.co.uk -name "quill-init.js" 2>/dev/null
```

---

## After Configuration

1. **Test nginx config:**
   ```bash
   sudo /usr/sbin/nginx -t
   ```

2. **Reload nginx:**
   ```bash
   sudo /usr/sbin/nginx -s reload
   # OR in Plesk, clicking "OK" in Apache & nginx Settings will reload automatically
   ```

3. **Test the file:**
   ```bash
   curl -I https://notes.thanksdoc.co.uk/js/quill-init.js
   ```
   Should return `HTTP/2 200` (not 404)

---

## Troubleshooting in Plesk

### If "Additional nginx directives" doesn't exist:
- You might be on an older Plesk version
- Use Method 2 or 3 (SSH/File Manager)

### If changes don't take effect:
- Make sure you clicked **"Apply"** or **"OK"**
- Check Plesk logs: **"Logs"** → **"Nginx Error Log"**
- Try reloading nginx via SSH: `sudo /usr/sbin/nginx -s reload`

### If you get permission errors:
- Plesk manages these files, so you might need to use Plesk GUI
- OR use: `sudo plesk bin site -u notes.thanksdoc.co.uk` to update

---

## Recommended: Use Plesk GUI (Method 1)

The easiest way is through **"Apache & nginx Settings"** → **"Additional directives for nginx"** in the Plesk GUI. This ensures Plesk manages the configuration properly.

---

**Once configured via Plesk GUI, nginx will serve static files directly and `quill-init.js` will load correctly!**

