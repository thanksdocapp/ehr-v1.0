<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Template extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'content',
        'placeholders',
        'description',
        'is_active',
        'is_system',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_active' => 'boolean',
        'is_system' => 'boolean',
    ];

    /**
     * Default placeholders available for all templates.
     */
    public const DEFAULT_PLACEHOLDERS = [
        '{{patient_name}}' => 'Patient full name',
        '{{patient_first_name}}' => 'Patient first name',
        '{{patient_last_name}}' => 'Patient last name',
        '{{patient_email}}' => 'Patient email',
        '{{patient_phone}}' => 'Patient phone number',
        '{{patient_dob}}' => 'Patient date of birth',
        '{{patient_age}}' => 'Patient age',
        '{{patient_gender}}' => 'Patient gender',
        '{{patient_address}}' => 'Patient full address',
        '{{patient_id}}' => 'Patient ID number',
        '{{doctor_name}}' => 'Doctor/Creator name',
        '{{doctor_email}}' => 'Doctor email',
        '{{doctor_specialization}}' => 'Doctor specialization',
        '{{clinic_name}}' => 'Clinic/Hospital name',
        '{{clinic_address}}' => 'Clinic address',
        '{{clinic_phone}}' => 'Clinic phone',
        '{{current_date}}' => 'Current date',
        '{{current_time}}' => 'Current time',
    ];

    /**
     * Get the user who created this template.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated this template.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get generated documents using this template.
     */
    public function generatedDocuments(): HasMany
    {
        return $this->hasMany(GeneratedDocument::class);
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

    /**
     * Scope to filter letters only.
     */
    public function scopeLetters($query)
    {
        return $query->where('type', 'letter');
    }

    /**
     * Scope to filter forms only.
     */
    public function scopeForms($query)
    {
        return $query->where('type', 'form');
    }

    /**
     * Scope: Templates visible to a user.
     * Staff see their own templates + system templates (created by admin).
     * Admins see all templates.
     */
    public function scopeVisibleTo($query, User $user)
    {
        if ($user->is_admin || $user->role === 'admin') {
            return $query;
        }

        // Staff roles see their own templates + system templates
        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhere('is_system', true);
        })->where('is_active', true);
    }

    /**
     * Scope: Templates owned by a user (for editing purposes).
     * Doctors can only edit their own templates.
     * Admins can edit all templates.
     */
    public function scopeOwnedBy($query, User $user)
    {
        if ($user->is_admin || $user->role === 'admin') {
            return $query;
        }

        return $query->where('created_by', $user->id);
    }

    /**
     * Check if user can edit this template.
     */
    public function canBeEditedBy(User $user): bool
    {
        if ($user->is_admin || $user->role === 'admin') {
            return true;
        }

        return $this->created_by === $user->id;
    }

    /**
     * Check if this is a system template (created by admin).
     */
    public function isSystemTemplate(): bool
    {
        return $this->is_system;
    }

    /**
     * Get all available placeholders for this template.
     */
    public function getAvailablePlaceholders(): array
    {
        $custom = $this->placeholders ?? [];
        return array_merge(self::DEFAULT_PLACEHOLDERS, $custom);
    }

    /**
     * Extract placeholders used in the template content.
     */
    public function extractUsedPlaceholders(): array
    {
        preg_match_all('/\{\{([a-z_]+)\}\}/i', $this->content, $matches);
        return array_unique($matches[0] ?? []);
    }

    /**
     * Get the type badge class for display.
     */
    public function getTypeBadgeClassAttribute(): string
    {
        return $this->type === 'letter' ? 'bg-primary' : 'bg-success';
    }

    /**
     * Get the type icon class.
     */
    public function getTypeIconAttribute(): string
    {
        return $this->type === 'letter' ? 'fa-envelope' : 'fa-file-alt';
    }

    /**
     * Get usage count.
     */
    public function getUsageCountAttribute(): int
    {
        return $this->generatedDocuments()->count();
    }
}
