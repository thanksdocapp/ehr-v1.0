<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Alert Categories Configuration
    |--------------------------------------------------------------------------
    |
    | Predefined alert categories with default severity and title prefixes.
    | Used when creating new alerts to auto-populate defaults.
    |
    */

    'categories' => [
        'clinical' => [
            'drug_allergy' => [
                'default_severity' => 'critical',
                'default_title_prefix' => 'Allergy: ',
                'restricted' => false,
            ],
            'anaphylaxis_risk' => [
                'default_severity' => 'critical',
                'default_title_prefix' => 'Anaphylaxis risk: ',
                'restricted' => false,
            ],
            'dnr_status' => [
                'default_severity' => 'critical',
                'default_title_prefix' => 'DNAR: ',
                'restricted' => false,
            ],
            'high_risk_medication' => [
                'default_severity' => 'high',
                'default_title_prefix' => 'High-risk medication: ',
                'restricted' => false,
            ],
            'infection_risk' => [
                'default_severity' => 'high',
                'default_title_prefix' => 'Infection risk: ',
                'restricted' => false,
            ],
            'fall_risk' => [
                'default_severity' => 'high',
                'default_title_prefix' => 'Fall risk: ',
                'restricted' => false,
            ],
            'pressure_sore_risk' => [
                'default_severity' => 'medium',
                'default_title_prefix' => 'Pressure sore risk: ',
                'restricted' => false,
            ],
        ],

        'safeguarding' => [
            'child_safeguarding' => [
                'default_severity' => 'high',
                'default_title_prefix' => 'Child safeguarding: ',
                'restricted' => true,
            ],
            'adult_safeguarding' => [
                'default_severity' => 'high',
                'default_title_prefix' => 'Adult safeguarding: ',
                'restricted' => true,
            ],
            'domestic_abuse' => [
                'default_severity' => 'high',
                'default_title_prefix' => 'Domestic abuse risk: ',
                'restricted' => true,
            ],
            'vulnerable_adult' => [
                'default_severity' => 'high',
                'default_title_prefix' => 'Vulnerable adult: ',
                'restricted' => true,
            ],
        ],

        'behaviour' => [
            'violence_risk' => [
                'default_severity' => 'high',
                'default_title_prefix' => 'Behavioural risk: ',
                'restricted' => false,
            ],
            'staff_safety_concern' => [
                'default_severity' => 'medium',
                'default_title_prefix' => 'Staff safety: ',
                'restricted' => false,
            ],
            'self_harm_risk' => [
                'default_severity' => 'critical',
                'default_title_prefix' => 'Self-harm risk: ',
                'restricted' => true,
            ],
            'suicide_risk' => [
                'default_severity' => 'critical',
                'default_title_prefix' => 'Suicide risk: ',
                'restricted' => true,
            ],
        ],

        'communication' => [
            'interpreter_required' => [
                'default_severity' => 'medium',
                'default_title_prefix' => 'Interpreter required: ',
                'restricted' => false,
            ],
            'learning_disability' => [
                'default_severity' => 'medium',
                'default_title_prefix' => 'Learning disability: ',
                'restricted' => false,
            ],
            'hearing_impairment' => [
                'default_severity' => 'medium',
                'default_title_prefix' => 'Hearing impairment: ',
                'restricted' => false,
            ],
            'visual_impairment' => [
                'default_severity' => 'medium',
                'default_title_prefix' => 'Visual impairment: ',
                'restricted' => false,
            ],
            'mental_capacity' => [
                'default_severity' => 'high',
                'default_title_prefix' => 'Mental capacity: ',
                'restricted' => false,
            ],
        ],

        'admin' => [
            'missing_id' => [
                'default_severity' => 'low',
                'default_title_prefix' => 'ID missing: ',
                'restricted' => false,
            ],
            'missing_consent' => [
                'default_severity' => 'low',
                'default_title_prefix' => 'Consent missing: ',
                'restricted' => false,
            ],
            'missing_gp_details' => [
                'default_severity' => 'low',
                'default_title_prefix' => 'GP details missing: ',
                'restricted' => false,
            ],
            'missing_guardian_id' => [
                'default_severity' => 'medium',
                'default_title_prefix' => 'Guardian ID missing: ',
                'restricted' => false,
            ],
        ],

        'medication' => [
            'drug_interaction' => [
                'default_severity' => 'high',
                'default_title_prefix' => 'Drug interaction: ',
                'restricted' => false,
            ],
            'allergy_conflict' => [
                'default_severity' => 'critical',
                'default_title_prefix' => 'Allergy conflict: ',
                'restricted' => false,
            ],
            'contraindication' => [
                'default_severity' => 'high',
                'default_title_prefix' => 'Contraindication: ',
                'restricted' => false,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Types
    |--------------------------------------------------------------------------
    |
    | List of valid alert types.
    |
    */
    'types' => [
        'clinical',
        'safeguarding',
        'behaviour',
        'communication',
        'admin',
        'medication',
    ],

    /*
    |--------------------------------------------------------------------------
    | Alert Severities
    |--------------------------------------------------------------------------
    |
    | List of valid alert severities.
    |
    */
    'severities' => [
        'critical',
        'high',
        'medium',
        'low',
        'info',
    ],
];

