# Deploy quill-init.js to Live Server

## Problem
The file `https://notes.thanksdoc.co.uk/js/quill-init.js` is not loading (404 error).

## Solution

The file exists in the repository but needs to be on your live server. Choose one method:

---

## Method 1: Pull from GitHub (Recommended)

SSH into your live server and run:

```bash
cd /path/to/your/laravel/app
git pull origin main
```

This will download `public/js/quill-init.js` automatically.

Then verify:
```bash
ls -la public/js/quill-init.js
# Should show the file exists

# Test accessibility
curl https://notes.thanksdoc.co.uk/js/quill-init.js
# Should return JavaScript code
```

---

## Method 2: Manual File Creation

If git pull doesn't work, create the file manually:

### Step 1: SSH into server
```bash
ssh user@your-server
```

### Step 2: Navigate to public directory
```bash
cd /path/to/your/laravel/app/public
mkdir -p js
cd js
```

### Step 3: Create the file
```bash
nano quill-init.js
```

### Step 4: Paste this complete code:

```javascript
/**
 * Shared Quill Editor Initialization
 * Provides consistent Quill editor setup across the application
 */

(function(window) {
    'use strict';

    // Default Quill configuration
    const defaultConfig = {
        theme: 'snow',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link'],
                ['clean']
            ]
        },
        formats: ['bold', 'italic', 'underline', 'header', 'list', 'link'],
        placeholder: 'Start typing...'
    };

    /**
     * Initialize a Quill editor instance
     * @param {string} selector - CSS selector for the editor container
     * @param {object} options - Quill configuration options
     * @returns {Quill|null} Quill instance or null if Quill not loaded
     */
    window.initQuillEditor = function(selector, options) {
        // Check if Quill is loaded
        if (typeof Quill === 'undefined') {
            console.error('Quill is not loaded. Please include quill.min.js before this script.');
            return null;
        }

        // Get the element
        const element = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (!element) {
            console.error('Quill editor container not found: ' + selector);
            return null;
        }

        // Merge options with defaults
        const config = Object.assign({}, defaultConfig, options || {});

        // Initialize Quill
        try {
            const quill = new Quill(element, config);
            return quill;
        } catch (error) {
            console.error('Failed to initialize Quill editor:', error);
            return null;
        }
    };

    /**
     * Set content in a Quill editor
     * @param {Quill} quill - Quill instance
     * @param {string} html - HTML content to set
     */
    window.setQuillContent = function(quill, html) {
        if (!quill || !quill.root) {
            console.error('Invalid Quill instance');
            return;
        }
        
        if (html) {
            quill.root.innerHTML = html;
        } else {
            quill.setText('');
        }
    };

    /**
     * Sync Quill editor content to a textarea on changes
     * @param {Quill} quill - Quill instance
     * @param {string} textareaSelector - CSS selector for the textarea
     * @param {number} debounceMs - Debounce delay in milliseconds (default: 300)
     */
    window.syncQuillToTextarea = function(quill, textareaSelector, debounceMs) {
        if (!quill || !quill.root) {
            console.error('Invalid Quill instance');
            return;
        }

        debounceMs = debounceMs || 300;
        const textarea = typeof textareaSelector === 'string' 
            ? document.querySelector(textareaSelector) 
            : textareaSelector;

        if (!textarea) {
            console.error('Textarea not found: ' + textareaSelector);
            return;
        }

        // Initial sync
        textarea.value = quill.root.innerHTML;

        // Debounce function
        let debounceTimer;
        const updateTextarea = function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function() {
                textarea.value = quill.root.innerHTML;
            }, debounceMs);
        };

        // Listen for text changes
        quill.on('text-change', updateTextarea);
        quill.on('selection-change', updateTextarea);
    };

    /**
     * Get HTML content from Quill editor
     * @param {Quill} quill - Quill instance
     * @returns {string} HTML content
     */
    window.getQuillContent = function(quill) {
        if (!quill || !quill.root) {
            return '';
        }
        return quill.root.innerHTML;
    };

    /**
     * Get plain text content from Quill editor
     * @param {Quill} quill - Quill instance
     * @returns {string} Plain text content
     */
    window.getQuillText = function(quill) {
        if (!quill) {
            return '';
        }
        return quill.getText();
    };

})(window);
```

### Step 5: Save the file
- Press `Ctrl+X`
- Press `Y` to confirm
- Press `Enter` to save

### Step 6: Set permissions
```bash
chmod 644 quill-init.js
```

### Step 7: Verify
```bash
# Check file exists
ls -la quill-init.js

# Test it's accessible
curl https://notes.thanksdoc.co.uk/js/quill-init.js
# Should return JavaScript code, not 404
```

---

## Method 3: Using cPanel File Manager

If you don't have SSH access:

1. Log into cPanel
2. Open **File Manager**
3. Navigate to: `public/js/` (or create the `js` folder if it doesn't exist)
4. Click **+ File** to create a new file
5. Name it: `quill-init.js`
6. Double-click to edit
7. Paste the JavaScript code from Method 2, Step 4
8. Save
9. Set permissions to `644` (right-click → Change Permissions)

---

## Verification

After deploying, test:

1. **Direct URL test:**
   ```
   https://notes.thanksdoc.co.uk/js/quill-init.js
   ```
   Should show JavaScript code (not 404)

2. **Browser console test:**
   - Open page with Quill editor
   - Press F12 → Console tab
   - Type: `typeof window.initQuillEditor`
   - Should return: `"function"`

3. **Network tab test:**
   - Open F12 → Network tab
   - Reload page
   - Look for `quill-init.js`
   - Status should be `200` (not 404)

---

## Troubleshooting

### Still getting 404?

1. **Check file path:**
   ```bash
   # On server
   pwd  # Should show Laravel root
   ls -la public/js/quill-init.js  # Should exist
   ```

2. **Check web server can access it:**
   ```bash
   # Test from server
   curl http://localhost/js/quill-init.js
   # Or
   wget http://localhost/js/quill-init.js
   ```

3. **Check .htaccess:**
   - Verify `public/.htaccess` exists
   - Check if there are any rewrite rules blocking JS files

4. **Check file permissions:**
   ```bash
   chmod 644 public/js/quill-init.js
   chown www-data:www-data public/js/quill-init.js  # Adjust user/group as needed
   ```

5. **Clear Laravel cache:**
   ```bash
   php artisan cache:clear
   php artisan config:clear
   php artisan route:clear
   php artisan view:clear
   ```

---

## Quick One-Liner (if you have SSH)

```bash
cd /path/to/your/app/public/js && cat > quill-init.js << 'EOF'
[paste the entire JavaScript code here]
EOF
chmod 644 quill-init.js
```

---

**Once the file is deployed, the Quill editor should work!**

