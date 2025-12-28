# Quill Editor Fix - Manual Installation

## Issue
The `quill-init.js` file was missing, causing Quill editor to not work on live server.

## Solution

The file has been committed to GitHub. If you cannot access it via the raw URL, here are alternative methods:

---

## Method 1: Pull from GitHub (Recommended)

```bash
cd /path/to/your/live/server
git pull origin main
```

This will automatically download the file.

---

## Method 2: Manual File Creation

Create the file manually on your live server:

**Location**: `public/js/quill-init.js`

**Content** (copy the entire code below):

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

---

## Method 3: Direct Download via SSH/SCP

If you have SSH access:

```bash
# On your local machine (if you have the repo)
scp public/js/quill-init.js user@your-server:/path/to/app/public/js/quill-init.js

# Or create it directly on the server
nano public/js/quill-init.js
# Paste the code above, save and exit
```

---

## Method 4: Create via Terminal (cPanel/SSH)

```bash
cd /path/to/your/application/public/js
nano quill-init.js
```

Then paste the JavaScript code above, save (Ctrl+X, then Y, then Enter).

---

## Verification

After creating the file:

1. **Check file exists:**
   ```bash
   ls -la public/js/quill-init.js
   ```

2. **Check file permissions:**
   ```bash
   chmod 644 public/js/quill-init.js
   ```

3. **Verify it's accessible via web:**
   - Visit: `https://your-domain.com/js/quill-init.js`
   - Should display the JavaScript code (not 404)

4. **Test the editor:**
   - Go to a page that uses Quill (e.g., Send Email to Patient)
   - The editor should load properly
   - Check browser console (F12) for any errors

---

## Troubleshooting

### File not found (404)
- Ensure the file is at: `public/js/quill-init.js` (relative to Laravel root)
- Check file permissions: `chmod 644 public/js/quill-init.js`
- Verify the `public/js/` directory exists

### Editor still not working
1. Clear browser cache (Ctrl+Shift+R)
2. Check browser console for JavaScript errors
3. Verify Quill CDN is loading: `https://cdn.quilljs.com/1.3.7/quill.min.js`
4. Verify the script tag is in the HTML: `<script src="/js/quill-init.js"></script>`

---

## File Location Reference

- **Source file**: `resources/js/quill-init.js` (development)
- **Public file**: `public/js/quill-init.js` (production - must exist for web access)
- **Git commit**: `6e66281` - "Add Quill editor initialization script (fix for live server)"

---

**Last Updated**: December 22, 2025

