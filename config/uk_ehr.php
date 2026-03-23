<?php

return [

    /*
    |--------------------------------------------------------------------------
    | UK-oriented core demographics (private EHR)
    |--------------------------------------------------------------------------
    |
    | Used by Patient::hasIncompleteInformation() to decide when a record is
    | incomplete for clinical unlock (e.g. clearing is_guest after profile save).
    |
    | Core = minimum identity & contact expected before treating the record as
    | demographically complete. Next-of-kin is recommended but not required —
    | see recommended_* labels in Patient.
    |
    */

    'core_labels' => [
        'placeholder_name' => 'Patient name — replace booking placeholder (new patient default)',
        'placeholder_email' => 'Email — replace temporary booking address',
        'email_invalid' => 'Valid email address',
        'date_of_birth' => 'Date of birth',
        'gender' => 'Gender',
        'phone' => 'Phone number',
        'address' => 'Address',
    ],

    'recommended_labels' => [
        'emergency_contact' => 'Emergency / next-of-kin name (recommended)',
        'emergency_phone' => 'Emergency / next-of-kin phone (recommended)',
    ],

];
