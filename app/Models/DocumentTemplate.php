<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'slug',
        'builder_config',
        'render_mode',
        'content',
        'schema',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'builder_config' => 'array',
        'schema' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user who created the template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the template.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the patient documents using this template.
     */
    public function patientDocuments(): HasMany
    {
        return $this->hasMany(PatientDocument::class, 'template_id');
    }

    /**
     * Scope to filter active templates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
