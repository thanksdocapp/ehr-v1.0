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

