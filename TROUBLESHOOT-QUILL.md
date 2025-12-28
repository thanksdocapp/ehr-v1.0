# Troubleshooting Quill Editor on Live Server

## File Exists ✓
The file `public/js/quill-init.js` exists on your server.

## Next Steps to Diagnose

### 1. Verify File is Accessible via Web

**Test in browser:**
- Visit: `https://your-domain.com/js/quill-init.js`
- OR: `http://your-domain.com/js/quill-init.js`

**Expected:** You should see JavaScript code (starting with `/**`)
**If you see 404:** The file exists but web server can't serve it (permissions or path issue)

---

### 2. Check File Permissions

```bash
ls -la public/js/quill-init.js
```

**Should show:**
```
-rw-r--r--  1 www-data www-data  141 quill-init.js
```

**If permissions are wrong:**
```bash
chmod 644 public/js/quill-init.js
chown www-data:www-data public/js/quill-init.js  # Adjust user/group as needed
```

---

### 3. Check Browser Console for Errors

1. Open the page where Quill should work (e.g., Send Email to Patient)
2. Open Developer Tools (F12)
3. Go to **Console** tab
4. Look for errors like:
   - `Failed to load resource: the server responded with a status of 404`
   - `initQuillEditor is not defined`
   - `Quill is not loaded`

---

### 4. Verify Script Tags in HTML

**Check page source (Ctrl+U or View Source):**

Look for these script tags (should appear in this order):

```html
<!-- Quill CSS -->
<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">

<!-- Quill JS (CDN) -->
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>

<!-- Your initialization script -->
<script src="/js/quill-init.js"></script>
```

**If `/js/quill-init.js` is missing:** The script tag isn't being included.

---

### 5. Check Network Tab

1. Open Developer Tools (F12)
2. Go to **Network** tab
3. Reload the page
4. Look for `quill-init.js` in the list
5. Check its status:
   - **200 OK** = File loads successfully
   - **404 Not Found** = File not accessible
   - **403 Forbidden** = Permission denied

---

### 6. Test Quill CDN is Loading

Visit directly in browser:
- `https://cdn.quilljs.com/1.3.7/quill.min.js`

**Expected:** JavaScript code loads
**If blocked:** Your server/firewall might be blocking CDN requests

---

### 7. Manual Browser Test

Open browser console (F12) and type:

```javascript
// Check if Quill is loaded
typeof Quill

// Should return: "function"
// If returns "undefined": Quill CDN not loading

// Check if initQuillEditor is defined
typeof window.initQuillEditor

// Should return: "function"
// If returns "undefined": quill-init.js not loaded
```

---

### 8. Common Issues & Fixes

#### Issue: "initQuillEditor is not defined"
**Fix:**
- Verify `/js/quill-init.js` is accessible via web
- Check script tag is after Quill CDN script
- Clear browser cache (Ctrl+Shift+R)

#### Issue: "Quill is not loaded"
**Fix:**
- Check Quill CDN URL is correct: `https://cdn.quilljs.com/1.3.7/quill.min.js`
- Verify CDN isn't blocked by firewall/CORS
- Check browser console for CDN load errors

#### Issue: File exists but returns 404
**Fix:**
```bash
# Check file path is correct
pwd  # Should show Laravel root
ls -la public/js/quill-init.js  # Should exist

# Fix permissions
chmod 644 public/js/quill-init.js

# Check web server can access it
# Test: curl http://localhost/js/quill-init.js
```

#### Issue: Script loads but editor doesn't appear
**Fix:**
- Check the container element exists: `#quill-email-editor` or `#quill-editor`
- Verify initialization code is running (check console for errors)
- Check if other JavaScript errors are preventing execution

---

### 9. Quick Test Script

Add this temporarily to your page to debug:

```html
<script>
console.log('Quill loaded:', typeof Quill !== 'undefined');
console.log('initQuillEditor loaded:', typeof window.initQuillEditor !== 'undefined');
console.log('Container exists:', document.querySelector('#quill-email-editor') !== null);
</script>
```

---

### 10. Verify Laravel Asset Helper

Check if Laravel's `asset()` helper is working:

In your view, verify:
```blade
<script src="{{ asset('js/quill-init.js') }}"></script>
```

This should output:
```html
<script src="/js/quill-init.js"></script>
```

If `APP_URL` is set incorrectly, it might output a full URL. Check your `.env`:
```env
APP_URL=https://your-domain.com
```

---

## Still Not Working?

If none of the above works, check:

1. **Web server configuration** - Apache/Nginx might need a rule to serve `.js` files
2. **Laravel routes** - Check if there's a route conflict
3. **.htaccess** - Verify `public/.htaccess` allows serving JS files
4. **File encoding** - Ensure file is UTF-8 encoded (not UTF-8 BOM)
5. **Line endings** - Should be LF (Unix) or CRLF (Windows), not mixed

---

## Success Indicators

You'll know it's working when:
- ✅ `https://your-domain.com/js/quill-init.js` returns JavaScript code
- ✅ Browser console shows no errors for `quill-init.js`
- ✅ `typeof window.initQuillEditor` returns `"function"` in console
- ✅ Quill editor appears on the page
- ✅ You can type and format text in the editor

