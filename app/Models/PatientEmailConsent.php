<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientEmailConsent extends Model
{
    use HasFactory;

    protected $table = 'patient_email_consent';

    protected $fillable = [
        'patient_id',
        'email_consent',
        'marketing_consent',
        'phi_consent',
        'consent_notes',
        'consent_date',
        'consent_expires_at',
        'consent_recorded_by',
        'consent_method'
    ];

    protected $casts = [
        'email_consent' => 'boolean',
        'marketing_consent' => 'boolean',
        'phi_consent' => 'boolean',
        'consent_date' => 'datetime',
        'consent_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the patient.
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the user who recorded the consent.
     */
    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'consent_recorded_by');
    }

    /**
     * Check if consent is valid (not expired).
     */
    public function isValid()
    {
        if (!$this->email_consent) {
            return false;
        }

        if ($this->consent_expires_at && $this->consent_expires_at->isPast()) {
            return false;
        }

        return true;
    }

    /**
     * Check if PHI consent is valid.
     */
    public function hasPhiConsent()
    {
        return $this->phi_consent && $this->isValid();
    }
}

