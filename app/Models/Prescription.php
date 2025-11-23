<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Prescription extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'medical_record_id',
        'prescription_number',
        'medications',
        'status',
        'prescription_type',
        'prescription_date',
        'diagnosis',
        'notes',
        'prescribed_date',
        'expiry_date',
        'follow_up_date',
        'refills_allowed',
        'pharmacist_notes',
        'pharmacist_id',
        'created_by',
        'updated_by',
        'dispensed_at',
        'approved_at',
    ];

    protected $casts = [
        'medications' => 'array',
        'prescription_date' => 'date',
        'prescribed_date' => 'date',
        'expiry_date' => 'date',
        'follow_up_date' => 'date',
        'dispensed_at' => 'datetime',
        'approved_at' => 'datetime',
        'refills_allowed' => 'integer',
        'patient_id' => 'integer',
        'doctor_id' => 'integer',
        'appointment_id' => 'integer',
        'medical_record_id' => 'integer',
        'pharmacist_id' => 'integer',
        'created_by' => 'integer',
        'updated_by' => 'integer',
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

    public function pharmacist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pharmacist_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    // Generate unique prescription number
    public static function generatePrescriptionNumber(): string
    {
        do {
            $number = 'RX' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('prescription_number', $number)->exists());

        return $number;
    }
}
