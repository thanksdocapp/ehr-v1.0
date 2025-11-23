/**
 * Admin Date Input Handler
 * Converts date inputs from HTML5 date type to text inputs with dd-mm-yyyy format
 * and handles conversion to yyyy-mm-dd for form submission
 */

(function() {
    'use strict';

    // Initialize date input masking for all date inputs
    function initDateInputs() {
        // Find all date inputs and convert them
        $('input[type="date"]').each(function() {
            const $input = $(this);
            const id = $input.attr('id');
            const name = $input.attr('name');
            const value = $input.val();
            const placeholder = $input.attr('placeholder') || 'dd-mm-yyyy';
            const required = $input.prop('required');
            const classes = $input.attr('class') || '';
            const min = $input.attr('min');
            const max = $input.attr('max');
            
            // Convert value from yyyy-mm-dd to dd-mm-yyyy if it exists
            let formattedValue = '';
            if (value && value.match(/^\d{4}-\d{2}-\d{2}$/)) {
                const parts = value.split('-');
                formattedValue = parts[2] + '-' + parts[1] + '-' + parts[0];
            }
            
            // Replace with text input
            const $textInput = $('<input>', {
                type: 'text',
                id: id,
                name: name,
                class: classes.replace('is-invalid', '').trim(),
                value: formattedValue,
                placeholder: placeholder,
                pattern: '\\d{2}-\\d{2}-\\d{4}',
                maxlength: '10',
                required: required
            });
            
            $input.replaceWith($textInput);
        });
    }

    // Date input mask for dd-mm-yyyy format
    function applyDateMask() {
        $(document).on('input', 'input[pattern="\\d{2}-\\d{2}-\\d{4}"]', function() {
            let value = $(this).val().replace(/\D/g, ''); // Remove non-digits
            if (value.length >= 2) {
                value = value.substring(0, 2) + '-' + value.substring(2);
            }
            if (value.length >= 5) {
                value = value.substring(0, 5) + '-' + value.substring(5, 9);
            }
            $(this).val(value);
        });
    }

    // Convert date format from dd-mm-yyyy to yyyy-mm-dd before form submission
    function convertDatesOnSubmit() {
        $(document).on('submit', 'form', function() {
            $(this).find('input[pattern="\\d{2}-\\d{2}-\\d{4}"]').each(function() {
                const dateStr = $(this).val();
                if (dateStr && dateStr.match(/^\d{2}-\d{2}-\d{4}$/)) {
                    const parts = dateStr.split('-');
                    const yyyyMmDd = parts[2] + '-' + parts[1] + '-' + parts[0];
                    $(this).val(yyyyMmDd);
                }
            });
        });
    }

    // Initialize when document is ready
    $(document).ready(function() {
        initDateInputs();
        applyDateMask();
        convertDatesOnSubmit();
    });
})();

