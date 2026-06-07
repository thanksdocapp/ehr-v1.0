<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Modality-aware availability
    |--------------------------------------------------------------------------
    |
    | When enabled, bookable slots are derived from doctor_availability_rules and
    | filtered by the modality of the chosen service (in_person / online / telephone).
    | When disabled, the booking flow falls back to the legacy doctors.availability
    | JSON path with no modality filtering (pre-feature behaviour).
    |
    | The backfill tags every existing window as "all" modality, so turning this on
    | is behaviour-preserving until a doctor narrows their rules.
    |
    */
    'modality_rules_enabled' => env('BOOKING_MODALITY_RULES_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Pending-booking resource locking
    |--------------------------------------------------------------------------
    |
    | When enabled, in-progress (unpaid, non-expired) pending bookings block their
    | physical time slot for the doctor across all modalities, and booking creation
    | re-checks under a row lock to prevent cross-modality double-booking.
    |
    */
    'lock_pending_bookings' => env('BOOKING_LOCK_PENDING', true),

];
