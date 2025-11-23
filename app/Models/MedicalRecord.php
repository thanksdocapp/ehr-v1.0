<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'record_type',
        'record_date',
        'presenting_complaint', // PC
        'history_of_presenting_complaint', // HPC
        'past_medical_history', // PMH
        'drug_history', // DH
        'allergies', // Allergies
        'social_history', // SH
        'family_history', // FH
        'ideas_concerns_expectations', // ICE
        'plan', // Plan
        'diagnosis',
        'symptoms',
        'treatment',
        'notes',
        'vital_signs',
        'follow_up_date',
        'is_private',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'patient_id' => 'integer',
        'doctor_id' => 'integer',
        'appointment_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'vital_signs' => 'array',
        'record_date' => 'date',
        'follow_up_date' => 'date',
        'is_private' => 'boolean',
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function labReports(): HasMany
    {
        return $this->hasMany(LabReport::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MedicalRecordAttachment::class);
    }

    // Scopes
    public function scopeByRecordType($query, $type)
    {
        return $query->where('record_type', $type);
    }

    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }
}
