<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'appointment_id',
        'medical_record_id',
        'report_number',
        'test_type',
        'test_category',
        'test_name',
        'test_date',
        'specimen_type',
        'collection_date',
        'report_date',
        'results',
        'normal_range',
        'reference_range',
        'reference_values',
        'interpretation',
        'status',
        'priority',
        'lab_technician',
        'technician_name',
        'technician_notes',
        'notes',
        'report_file',
        'file_path',
        'technician_id',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'test_date' => 'date',
        'collection_date' => 'date',
        'report_date' => 'date',
        'patient_id' => 'integer',
        'doctor_id' => 'integer',
        'appointment_id' => 'integer',
        'medical_record_id' => 'integer',
        'technician_id' => 'integer',
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

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
    public function scopeByTestType($query, $type)
    {
        return $query->where('test_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    // Helper methods
    public function getFileUrlAttribute()
    {
        return $this->file_path ? asset('storage/' . $this->file_path) : null;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    // Generate unique report number
    public static function generateReportNumber(): string
    {
        do {
            $number = 'LAB' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('report_number', $number)->exists());

        return $number;
    }
}
