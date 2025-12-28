/**
 * Formeo Form Builder Initialization
 * 
 * This module provides Formeo form builder initialization for form templates.
 * Used only for structured forms and questionnaires.
 */

(function() {
    'use strict';

    /**
     * Initialize Formeo form builder
     * @param {string|HTMLElement} containerSelector - CSS selector or DOM element for Formeo container
     * @param {object} formData - Initial form data/schema (optional)
     * @returns {Promise<FormeoEditor>} FormeoEditor instance
     */
    window.initFormeoBuilder = async function(containerSelector, formData = null) {
        // Formeo is loaded as UMD, so it should be available globally
        if (typeof FormeoEditor === 'undefined' && typeof window.FormeoEditor === 'undefined') {
            // Try alternative global names
            if (typeof Formeo !== 'undefined' && Formeo.FormeoEditor) {
                window.FormeoEditor = Formeo.FormeoEditor;
            } else {
                console.error('FormeoEditor is not loaded. Please include Formeo CSS and JS.');
                return null;
            }
        }

        const FormeoEditorClass = window.FormeoEditor || FormeoEditor;

        const container = typeof containerSelector === 'string' 
            ? document.querySelector(containerSelector) 
            : containerSelector;

        if (!container) {
            console.error('Formeo container not found');
            return null;
        }

        try {
            // Formeo configuration
            const editorOptions = {
                editorContainer: containerSelector,
            };

            const formeo = new FormeoEditorClass(editorOptions);

            // Load initial form data if provided
            if (formData) {
                try {
                    // If formData is a string, parse it
                    const schema = typeof formData === 'string' ? JSON.parse(formData) : formData;
                    // FormeoEditor uses render method or formData property to set initial data
                    if (typeof formeo.render === 'function') {
                        formeo.render(schema);
                    } else if (formeo.formData !== undefined) {
                        formeo.formData = schema;
                    }
                } catch (error) {
                    console.error('Error loading Formeo form data:', error);
                }
            }

            return formeo;
        } catch (error) {
            console.error('Failed to initialize Formeo builder:', error);
            return null;
        }
    };

    /**
     * Get form schema from Formeo builder
     * @param {FormeoEditor} formeo - Formeo instance
     * @returns {object|null} Form schema as JSON object
     */
    window.getFormeoSchema = function(formeo) {
        if (!formeo) return null;
        try {
            // FormeoEditor exposes formData property
            return formeo.formData || null;
        } catch (error) {
            console.error('Error getting Formeo schema:', error);
            return null;
        }
    };

    /**
     * Set form schema in Formeo builder
     * @param {Formeo} formeo - Formeo instance
     * @param {object|string} schema - Form schema (object or JSON string)
     */
    window.setFormeoSchema = function(formeo, schema) {
        if (!formeo) return;
        try {
            const formSchema = typeof schema === 'string' ? JSON.parse(schema) : schema;
            formeo.render(formSchema);
        } catch (error) {
            console.error('Error setting Formeo schema:', error);
        }
    };

})();
