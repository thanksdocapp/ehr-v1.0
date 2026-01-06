/**
 * Centralized Flatpickr Date Picker Initialization
 * Auto-initializes Flatpickr on all date inputs with UK format (dd/mm/yyyy)
 * 
 * Usage:
 * - Add class "uk-date" to any date input for standard date picker
 * - Add class "uk-datetime" to any datetime input for date + time picker
 * - Add data-min-date="YYYY-MM-DD" for minimum date
 * - Add data-max-date="YYYY-MM-DD" for maximum date
 * - Add data-default-date="today" for default date
 */

(function() {
    'use strict';

    // Wait for DOM and Flatpickr to be ready
    function initFlatpickr() {
        if (typeof flatpickr === 'undefined') {
            console.warn('Flatpickr library not loaded. Date pickers will not work.');
            return;
        }

        // Initialize date pickers (dd/mm/yyyy format)
        const dateInputs = document.querySelectorAll('input.uk-date, input[data-uk-date="true"]');
        dateInputs.forEach(function(input) {
            // Skip if already initialized or readonly
            if (input.readOnly || input.hasAttribute('data-flatpickr-initialized')) {
                return;
            }

            const options = {
                dateFormat: "d/m/Y",
                altInput: false,
                altFormat: "d/m/Y",
                locale: {
                    firstDayOfWeek: 1 // Monday
                },
                allowInput: true,
                clickOpens: true,
                onChange: function(selectedDates, dateStr, instance) {
                    // Ensure format is dd/mm/yyyy
                    if (dateStr && dateStr.match(/^\d{4}-\d{2}-\d{2}$/)) {
                        const date = new Date(dateStr);
                        const dd = String(date.getDate()).padStart(2, '0');
                        const mm = String(date.getMonth() + 1).padStart(2, '0');
                        const yyyy = date.getFullYear();
                        instance.input.value = dd + '/' + mm + '/' + yyyy;
                    }
                }
            };

            // Set min date
            if (input.hasAttribute('data-min-date')) {
                const minDate = input.getAttribute('data-min-date');
                options.minDate = minDate === 'today' ? 'today' : minDate;
            }

            // Set max date
            if (input.hasAttribute('data-max-date')) {
                const maxDate = input.getAttribute('data-max-date');
                options.maxDate = maxDate === 'today' ? 'today' : maxDate;
            } else if (input.classList.contains('uk-date-dob')) {
                // Default max date for date of birth
                options.maxDate = 'today';
                options.minDate = new Date(new Date().setFullYear(new Date().getFullYear() - 150));
            }

            // Set default date
            if (input.hasAttribute('data-default-date')) {
                const defaultDate = input.getAttribute('data-default-date');
                if (defaultDate === 'today') {
                    options.defaultDate = 'today';
                } else {
                    options.defaultDate = defaultDate;
                }
            }

            // Initialize Flatpickr
            try {
                flatpickr(input, options);
                input.setAttribute('data-flatpickr-initialized', 'true');
            } catch (e) {
                console.error('Error initializing Flatpickr on input:', input.id || input.name, e);
            }
        });

        // Initialize datetime pickers (dd/mm/yyyy HH:MM format)
        const datetimeInputs = document.querySelectorAll('input.uk-datetime, input[data-uk-datetime="true"]');
        datetimeInputs.forEach(function(input) {
            // Skip if already initialized or readonly
            if (input.readOnly || input.hasAttribute('data-flatpickr-initialized')) {
                return;
            }

            const options = {
                dateFormat: "d/m/Y H:i",
                altInput: false,
                altFormat: "d/m/Y H:i",
                enableTime: true,
                time_24hr: true,
                locale: {
                    firstDayOfWeek: 1 // Monday
                },
                allowInput: true,
                clickOpens: true,
                onChange: function(selectedDates, dateStr, instance) {
                    // Format is already handled by Flatpickr for datetime
                }
            };

            // Set min date
            if (input.hasAttribute('data-min-date')) {
                const minDate = input.getAttribute('data-min-date');
                options.minDate = minDate === 'today' ? 'today' : minDate;
            }

            // Set max date
            if (input.hasAttribute('data-max-date')) {
                const maxDate = input.getAttribute('data-max-date');
                options.maxDate = maxDate === 'today' ? 'today' : maxDate;
            }

            // Set default date
            if (input.hasAttribute('data-default-date')) {
                const defaultDate = input.getAttribute('data-default-date');
                if (defaultDate === 'today') {
                    options.defaultDate = 'today';
                } else {
                    options.defaultDate = defaultDate;
                }
            }

            // Initialize Flatpickr
            try {
                flatpickr(input, options);
                input.setAttribute('data-flatpickr-initialized', 'true');
            } catch (e) {
                console.error('Error initializing Flatpickr on input:', input.id || input.name, e);
            }
        });

        // Convert dd/mm/yyyy to yyyy-mm-dd before form submission
        const forms = document.querySelectorAll('form');
        forms.forEach(function(form) {
            form.addEventListener('submit', function(e) {
                // Convert date inputs
                const dateInputs = form.querySelectorAll('input.uk-date, input[data-uk-date="true"]');
                dateInputs.forEach(function(input) {
                    const value = input.value.trim();
                    if (value && value.match(/^\d{2}\/\d{2}\/\d{4}$/)) {
                        const parts = value.split('/');
                        input.value = parts[2] + '-' + parts[1] + '-' + parts[0];
                    }
                });

                // Convert datetime inputs
                const datetimeInputs = form.querySelectorAll('input.uk-datetime, input[data-uk-datetime="true"]');
                datetimeInputs.forEach(function(input) {
                    const value = input.value.trim();
                    // Handle dd/mm/yyyy HH:MM format
                    if (value && value.match(/^\d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/)) {
                        const [datePart, timePart] = value.split(' ');
                        const parts = datePart.split('/');
                        input.value = parts[2] + '-' + parts[1] + '-' + parts[0] + ' ' + timePart;
                    }
                });
            });
        });
    }

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initFlatpickr);
    } else {
        initFlatpickr();
    }

    // Also initialize for dynamically added inputs (e.g., AJAX loaded content)
    if (typeof jQuery !== 'undefined') {
        jQuery(document).ready(function($) {
            initFlatpickr();
            
            // Re-initialize on AJAX complete (for forms loaded via AJAX)
            $(document).ajaxComplete(function() {
                setTimeout(initFlatpickr, 100);
            });
        });
    }
})();

