# Fix Quill Editor CSP Blocking Issue

## Problem Identified

From the Network tab, I can see:
1. ✅ `quill-init.js` - Status 404 (file not found)
2. ❌ `quill.snow.css` - Status `blocked:csp` (Content Security Policy blocking)
3. ❌ `quill.min.js` - Status `blocked:csp` (Content Security Policy blocking)

## Root Cause

The Content Security Policy (CSP) in `.htaccess` was blocking `https://cdn.quilljs.com` because it wasn't in the allowed list of CDNs.

## Solution Applied

I've updated both `.htaccess` files to include `https://cdn.quilljs.com` in:
- `script-src` (for quill.min.js)
- `style-src` (for quill.snow.css)

## Files Updated

1. ✅ `public/.htaccess` - Updated CSP to allow Quill CDN
2. ✅ `.htaccess` - Updated CSP to allow Quill CDN (for consistency)

## Additional Fix Needed: 404 Error

The `quill-init.js` file shows 404. Even though the file exists on the server, it's not accessible via web.

### On Live Server, Check:

1. **File exists and permissions:**
   ```bash
   ls -la public/js/quill-init.js
   # Should show: -rw-r--r--
   ```

2. **If file doesn't exist, create it:**
   ```bash
   mkdir -p public/js
   # Then create the file with the content from QUILL-INIT-FIX.md
   ```

3. **Fix permissions:**
   ```bash
   chmod 644 public/js/quill-init.js
   ```

4. **Test accessibility:**
   ```bash
   curl https://notes.thanksdoc.co.uk/js/quill-init.js
   # Should return JavaScript code, not 404
   ```

## Deployment Steps

1. **Pull latest code:**
   ```bash
   git pull origin main
   ```

2. **Or manually update `.htaccess`:**
   - Update `public/.htaccess` with the new CSP that includes `https://cdn.quilljs.com`
   - See the updated CSP line in the file

3. **Ensure `quill-init.js` exists:**
   ```bash
   # Verify file exists
   ls -la public/js/quill-init.js
   
   # If missing, create it (see QUILL-INIT-FIX.md for content)
   ```

4. **Clear browser cache:**
   - Hard refresh: `Ctrl+Shift+R` (Windows) or `Cmd+Shift+R` (Mac)

5. **Test:**
   - Visit: `https://notes.thanksdoc.co.uk/staff/patient-email/compose`
   - Open Network tab (F12)
   - Look for:
     - ✅ `quill.min.js` - Status 200 (not blocked)
     - ✅ `quill.snow.css` - Status 200 (not blocked)
     - ✅ `quill-init.js` - Status 200 (not 404)

## Expected Result

After these fixes:
- ✅ Quill CDN resources load (no CSP blocking)
- ✅ `quill-init.js` loads successfully (no 404)
- ✅ Quill editor appears and works on the page

## Verification

After deploying, check browser console for:
- ✅ No CSP violation errors
- ✅ `typeof Quill` returns `"function"`
- ✅ `typeof window.initQuillEditor` returns `"function"`
- ✅ Editor appears on the page

