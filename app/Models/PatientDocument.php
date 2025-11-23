<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class PatientDocument extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'template_id',
        'type',
        'title',
        'status',
        'content',
        'form_data',
        'pdf_path',
        'created_by',
        'updated_by',
        'signed_by_patient',
        'signed_at',
    ];

    protected $casts = [
        'form_data' => 'array',
        'signed_by_patient' => 'boolean',
        'signed_at' => 'datetime',
    ];

    /**
     * Get the patient that owns the document.
     */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Get the template used for this document.
     */
    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    /**
     * Get the user who created the document.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the document.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the deliveries for this document.
     */
    public function deliveries(): HasMany
    {
        return $this->hasMany(DocumentDelivery::class, 'patient_document_id');
    }

    /**
     * Check if document is final.
     */
    public function isFinal(): bool
    {
        return $this->status === 'final';
    }

    /**
     * Check if document is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if document is void.
     */
    public function isVoid(): bool
    {
        return $this->status === 'void';
    }

    /**
     * Scope to filter by status.
     */
    public function scopeOfStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
