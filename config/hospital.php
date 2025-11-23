<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Hospital Information
    |--------------------------------------------------------------------------
    |
    | This contains the hospital's contact and configuration information
    | used throughout the application, especially in email templates.
    |
    */

    'name' => env('HOSPITAL_NAME', 'ThanksDoc EHR'),
    'address' => env('HOSPITAL_ADDRESS', '123 Healthcare Street, Medical City, MC 12345'),
    'phone' => env('HOSPITAL_PHONE', '+1 (555) 123-4567'),
    'emergency_phone' => env('HOSPITAL_EMERGENCY_PHONE', '+1 (555) 911-HELP'),
    'billing_phone' => env('HOSPITAL_BILLING_PHONE', '+1 (555) 123-BILL'),
    'pharmacy_phone' => env('HOSPITAL_PHARMACY_PHONE', '+1 (555) 123-MEDS'),
    'email' => env('HOSPITAL_EMAIL', 'info@hospital.com'),
    'website' => env('HOSPITAL_WEBSITE', 'https://hospital.com'),

    /*
    |--------------------------------------------------------------------------
    | Date & Time Format Settings
    |--------------------------------------------------------------------------
    |
    | Configure how dates and times should be displayed throughout the application.
    |
    */

    'date_format' => env('DATE_FORMAT', 'd-m-Y'), // Default: dd-mm-yyyy (British style)
    'time_format' => env('TIME_FORMAT', 'H:i'), // Default: 24-hour format
    'datetime_format' => env('DATETIME_FORMAT', 'd-m-Y H:i'), // Default: dd-mm-yyyy HH:mm (British style)

    /*
    |--------------------------------------------------------------------------
    | Email Notification Settings
    |--------------------------------------------------------------------------
    |
    | Configure which email notifications should be sent automatically
    | and to whom they should be sent.
    |
    */

    'notifications' => [
        'patient_welcome' => [
            'enabled' => env('NOTIFY_PATIENT_WELCOME', true),
            'delay_minutes' => 0, // Send immediately
        ],
        
        'appointment_confirmation' => [
            'enabled' => env('NOTIFY_APPOINTMENT_CONFIRMATION', true),
            'delay_minutes' => 0, // Send immediately
            'send_to_patient' => true,
            'send_to_doctor' => true,
            'send_to_staff' => false,
        ],
        
        'appointment_reminder' => [
            'enabled' => env('NOTIFY_APPOINTMENT_REMINDER', true),
            'days_before' => [1, 3], // Send reminders 1 and 3 days before
            'times' => ['09:00', '14:00'], // Times to send reminders
        ],
        
        'test_results_ready' => [
            'enabled' => env('NOTIFY_TEST_RESULTS', true),
            'delay_minutes' => 30, // Wait 30 minutes before sending
        ],
        
        'prescription_ready' => [
            'enabled' => env('NOTIFY_PRESCRIPTION_READY', true),
            'delay_minutes' => 15,
        ],
        
        'discharge_instructions' => [
            'enabled' => env('NOTIFY_DISCHARGE', true),
            'delay_minutes' => 0,
        ],
        
        'payment_reminder' => [
            'enabled' => env('NOTIFY_PAYMENT_REMINDER', true),
            'days_after_due' => [7, 14, 30], // Send reminders after due date
            'send_time' => '10:00',
        ],
        
        'emergency_contact' => [
            'enabled' => env('NOTIFY_EMERGENCY_CONTACT', true),
            'delay_minutes' => 0, // Send immediately
        ],
        
        'medical_record_updates' => [
            'enabled' => env('NOTIFY_MEDICAL_RECORD_UPDATES', true),
            'include_private' => env('NOTIFY_MEDICAL_RECORD_INCLUDE_PRIVATE', false),
            'delay_minutes' => 0, // Send immediately
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Staff Notification Recipients
    |--------------------------------------------------------------------------
    |
    | Define which staff members should receive notifications for various events.
    |
    */

    'staff_notifications' => [
        'new_patient_registration' => [
            'enabled' => env('NOTIFY_STAFF_NEW_PATIENT', true),
            'roles' => ['admin', 'receptionist'], // Which roles should be notified
        ],
        
        'new_appointment' => [
            'enabled' => env('NOTIFY_STAFF_NEW_APPOINTMENT', true),
            'roles' => ['admin', 'receptionist'],
            'notify_doctor' => true,
        ],
        
        'emergency_admission' => [
            'enabled' => env('NOTIFY_STAFF_EMERGENCY', true),
            'roles' => ['admin', 'doctor', 'nurse'],
        ],
        
        'lab_results_critical' => [
            'enabled' => env('NOTIFY_STAFF_CRITICAL_RESULTS', true),
            'roles' => ['admin', 'doctor'],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Business Hours
    |--------------------------------------------------------------------------
    |
    | Define the hospital's operating hours for various departments.
    |
    */

    'hours' => [
        'general' => [
            'monday' => '08:00-20:00',
            'tuesday' => '08:00-20:00',
            'wednesday' => '08:00-20:00',
            'thursday' => '08:00-20:00',
            'friday' => '08:00-20:00',
            'saturday' => '09:00-17:00',
            'sunday' => '10:00-16:00',
        ],
        
        'pharmacy' => [
            'monday' => '08:00-20:00',
            'tuesday' => '08:00-20:00',
            'wednesday' => '08:00-20:00',
            'thursday' => '08:00-20:00',
            'friday' => '08:00-20:00',
            'saturday' => '09:00-17:00',
            'sunday' => 'CLOSED',
        ],
        
        'emergency' => '24/7',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Settings
    |--------------------------------------------------------------------------
    |
    | Configure queue settings specifically for hospital notifications.
    |
    */

    'queue' => [
        'email_notifications' => env('HOSPITAL_EMAIL_QUEUE', 'emails'),
        'sms_notifications' => env('HOSPITAL_SMS_QUEUE', 'sms'),
        'high_priority' => env('HOSPITAL_HIGH_PRIORITY_QUEUE', 'high-priority'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Electronic Dispenser API Settings
    |--------------------------------------------------------------------------
    |
    | Configure the third-party electronic dispenser API integration.
    | This sends approved prescriptions to the electronic dispenser system.
    |
    */

    'dispenser_api' => [
        'enabled' => env('DISPENSER_API_ENABLED', false),
        'base_url' => env('DISPENSER_API_BASE_URL', ''),
        'api_key' => env('DISPENSER_API_KEY', ''),
        'api_secret' => env('DISPENSER_API_SECRET', ''),
        'auth_type' => env('DISPENSER_API_AUTH_TYPE', 'bearer'), // 'bearer', 'api_key', 'basic'
        'timeout' => env('DISPENSER_API_TIMEOUT', 30), // seconds
        'send_on_approval' => env('DISPENSER_SEND_ON_APPROVAL', true), // Send when prescription is approved
        'retry_on_failure' => env('DISPENSER_RETRY_ON_FAILURE', false), // Retry failed requests
        'max_retries' => env('DISPENSER_MAX_RETRIES', 3), // Maximum retry attempts
    ],
];
