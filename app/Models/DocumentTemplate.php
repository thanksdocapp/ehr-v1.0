<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Auth;

class DocumentTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'icon',
        'type',
        'category_id',
        'slug',
        'builder_config',
        'render_mode',
        'content',
        'schema',
        'is_active',
        'version',
        'parent_template_id',
        'is_latest',
        'allowed_roles',
        'allowed_departments',
        'requires_signature',
        'requires_witness',
        'is_confidential',
        'retention_days',
        'usage_count',
        'last_used_at',
        'favorited_by',
        'tags',
        'print_settings',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'builder_config' => 'array',
        'schema' => 'array',
        'is_active' => 'boolean',
        'is_latest' => 'boolean',
        'requires_signature' => 'boolean',
        'requires_witness' => 'boolean',
        'is_confidential' => 'boolean',
        'allowed_roles' => 'array',
        'allowed_departments' => 'array',
        'favorited_by' => 'array',
        'tags' => 'array',
        'print_settings' => 'array',
        'last_used_at' => 'datetime',
    ];

    /**
     * Get the category.
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

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
     * Get the parent template (for versioning).
     */
    public function parentTemplate(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class, 'parent_template_id');
    }

    /**
     * Get child versions of this template.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(DocumentTemplate::class, 'parent_template_id')->orderByDesc('version');
    }

    /**
     * Get the patient documents using this template.
     */
    public function patientDocuments(): HasMany
    {
        return $this->hasMany(PatientDocument::class, 'template_id');
    }

    /**
     * Get users who favorited this template.
     */
    public function favoritedByUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'document_template_favorites', 'template_id', 'user_id')
            ->withTimestamps();
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
     * Scope to filter latest versions only.
     */
    public function scopeLatestVersion($query)
    {
        return $query->where('is_latest', true);
    }

    /**
     * Scope to filter by category.
     */
    public function scopeInCategory($query, $categoryId)
    {
        if ($categoryId) {
            return $query->where('category_id', $categoryId);
        }
        return $query;
    }

    /**
     * Scope to filter by tags.
     */
    public function scopeWithTags($query, array $tags)
    {
        foreach ($tags as $tag) {
            $query->whereJsonContains('tags', $tag);
        }
        return $query;
    }

    /**
     * Scope to filter accessible templates for a user.
     */
    public function scopeAccessibleBy($query, User $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->whereNull('allowed_roles')
                ->orWhereJsonLength('allowed_roles', 0);
        })->orWhere(function ($q) use ($user) {
            $q->whereJsonContains('allowed_roles', $user->role);
        });
    }

    /**
     * Scope favorites for a user.
     */
    public function scopeFavoritedBy($query, User $user)
    {
        return $query->whereHas('favoritedByUsers', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
    }

    /**
     * Scope to filter templates owned by a specific user (doctor).
     * Doctors can only see templates they created.
     * Admin can see all templates.
     */
    public function scopeOwnedBy($query, User $user)
    {
        // Admin can see all
        if ($user->is_admin || $user->role === 'admin') {
            return $query;
        }

        // For doctors and other staff, only show templates they created
        return $query->where('created_by', $user->id);
    }

    /**
     * Scope to filter templates visible to user (for selection purposes).
     * This shows templates the user created OR system templates (created by admin).
     */
    public function scopeVisibleTo($query, User $user)
    {
        // Admin can see all
        if ($user->is_admin || $user->role === 'admin') {
            return $query;
        }

        // For doctors and other staff, show templates they created OR global system templates
        return $query->where(function ($q) use ($user) {
            $q->where('created_by', $user->id)
              ->orWhereHas('creator', function ($q2) {
                  $q2->where('is_admin', true)->orWhere('role', 'admin');
              });
        });
    }

    /**
     * Check if template is owned by user.
     */
    public function isOwnedBy(User $user): bool
    {
        // Admin owns all
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
        if (!$this->creator) {
            return false;
        }

        return $this->creator->is_admin || $this->creator->role === 'admin';
    }

    /**
     * Check if user has access to this template.
     */
    public function isAccessibleBy(User $user): bool
    {
        if ($user->role === 'admin') {
            return true;
        }

        if (!empty($this->allowed_roles)) {
            if (!in_array($user->role, $this->allowed_roles)) {
                return false;
            }
        }

        if (!empty($this->allowed_departments)) {
            $userDepartments = $user->doctor?->departments?->pluck('id')->toArray() ?? [];
            if (empty(array_intersect($userDepartments, $this->allowed_departments))) {
                return false;
            }
        }

        return true;
    }

    /**
     * Check if template is favorited by current user.
     */
    public function isFavorited(?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return false;
        }
        return $this->favoritedByUsers()->where('user_id', $user->id)->exists();
    }

    /**
     * Toggle favorite status for current user.
     */
    public function toggleFavorite(?User $user = null): bool
    {
        $user = $user ?? Auth::user();
        if (!$user) {
            return false;
        }

        if ($this->isFavorited($user)) {
            $this->favoritedByUsers()->detach($user->id);
            return false;
        } else {
            $this->favoritedByUsers()->attach($user->id);
            return true;
        }
    }

    /**
     * Increment usage count.
     */
    public function incrementUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }

    /**
     * Create a new version of this template.
     */
    public function createVersion(array $changes = []): self
    {
        $this->update(['is_latest' => false]);

        $rootId = $this->parent_template_id ?? $this->id;

        $maxVersion = self::where('parent_template_id', $rootId)
            ->orWhere('id', $rootId)
            ->max('version') ?? 0;

        $newVersion = $this->replicate();
        $newVersion->version = $maxVersion + 1;
        $newVersion->parent_template_id = $rootId;
        $newVersion->is_latest = true;
        $newVersion->created_by = Auth::id();
        $newVersion->updated_by = null;

        foreach ($changes as $key => $value) {
            $newVersion->{$key} = $value;
        }

        $newVersion->save();

        return $newVersion;
    }

    /**
     * Get all versions of this template.
     */
    public function getAllVersions()
    {
        $rootId = $this->parent_template_id ?? $this->id;

        return self::where('id', $rootId)
            ->orWhere('parent_template_id', $rootId)
            ->orderByDesc('version')
            ->get();
    }

    /**
     * Clone template with new name.
     */
    public function duplicate(string $newName = null): self
    {
        $clone = $this->replicate([
            'usage_count',
            'last_used_at',
            'favorited_by',
        ]);

        $clone->name = $newName ?? $this->name . ' (Copy)';
        $clone->slug = self::generateUniqueSlug($clone->name);
        $clone->is_active = false;
        $clone->version = 1;
        $clone->parent_template_id = null;
        $clone->is_latest = true;
        $clone->created_by = Auth::id();
        $clone->updated_by = null;
        $clone->usage_count = 0;
        $clone->last_used_at = null;

        $clone->save();

        return $clone;
    }

    /**
     * Generate unique slug.
     */
    public static function generateUniqueSlug(string $name): string
    {
        $slug = \Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (self::where('slug', $slug)->exists()) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Get default print settings.
     */
    public function getPrintSettingsWithDefaults(): array
    {
        $defaults = [
            'paper_size' => DocumentSetting::get('pdf_paper_size', 'A4'),
            'orientation' => DocumentSetting::get('pdf_orientation', 'portrait'),
            'include_header' => DocumentSetting::get('pdf_include_header', true),
            'include_footer' => DocumentSetting::get('pdf_include_footer', true),
            'margin_top' => 20,
            'margin_bottom' => 20,
            'margin_left' => 15,
            'margin_right' => 15,
        ];

        return array_merge($defaults, $this->print_settings ?? []);
    }

    /**
     * Get category name or default.
     */
    public function getCategoryNameAttribute(): string
    {
        return $this->category?->name ?? 'Uncategorized';
    }

    /**
     * Get icon with fallback.
     */
    public function getIconClassAttribute(): string
    {
        if ($this->icon) {
            return $this->icon;
        }
        return $this->type === 'letter' ? 'fa-envelope-open-text' : 'fa-clipboard-list';
    }

    /**
     * Get formatted tags for display.
     */
    public function getFormattedTagsAttribute(): string
    {
        return implode(', ', $this->tags ?? []);
    }

    /**
     * Get statistics for this template.
     */
    public function getStatistics(): array
    {
        $documents = $this->patientDocuments();

        return [
            'total_documents' => $documents->count(),
            'draft_documents' => (clone $documents)->where('status', 'draft')->count(),
            'final_documents' => (clone $documents)->where('status', 'final')->count(),
            'void_documents' => (clone $documents)->where('status', 'void')->count(),
            'sent_documents' => (clone $documents)->whereHas('deliveries', function ($q) {
                $q->where('status', 'sent');
            })->count(),
            'signed_documents' => (clone $documents)->where('signed_by_patient', true)->count(),
            'usage_this_month' => (clone $documents)->whereMonth('created_at', now()->month)->count(),
            'favorites_count' => $this->favoritedByUsers()->count(),
        ];
    }
}
