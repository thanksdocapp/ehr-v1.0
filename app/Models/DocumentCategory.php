<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocumentCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'icon',
        'type',
        'parent_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'parent_id');
    }

    /**
     * Get child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(DocumentCategory::class, 'parent_id')->orderBy('sort_order');
    }

    /**
     * Get all descendants.
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get templates in this category.
     */
    public function templates(): HasMany
    {
        return $this->hasMany(DocumentTemplate::class, 'category_id');
    }

    /**
     * Scope to filter active categories.
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
        return $query->where(function ($q) use ($type) {
            $q->where('type', $type)->orWhere('type', 'both');
        });
    }

    /**
     * Scope to get root categories only.
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get formatted name with parent hierarchy.
     */
    public function getFullNameAttribute(): string
    {
        if ($this->parent) {
            return $this->parent->full_name . ' > ' . $this->name;
        }
        return $this->name;
    }

    /**
     * Get template count including children.
     */
    public function getTotalTemplateCountAttribute(): int
    {
        $count = $this->templates()->count();
        foreach ($this->children as $child) {
            $count += $child->total_template_count;
        }
        return $count;
    }

    /**
     * Check if category has children.
     */
    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    /**
     * Get all ancestor IDs.
     */
    public function getAncestorIds(): array
    {
        $ids = [];
        $parent = $this->parent;
        while ($parent) {
            $ids[] = $parent->id;
            $parent = $parent->parent;
        }
        return array_reverse($ids);
    }

    /**
     * Get breadcrumb path.
     */
    public function getBreadcrumbAttribute(): array
    {
        $breadcrumb = [];
        $current = $this;
        while ($current) {
            array_unshift($breadcrumb, [
                'id' => $current->id,
                'name' => $current->name,
                'slug' => $current->slug,
            ]);
            $current = $current->parent;
        }
        return $breadcrumb;
    }

    /**
     * Create a slug from name.
     */
    public static function generateSlug(string $name): string
    {
        $slug = \Str::slug($name);
        $count = static::where('slug', 'like', $slug . '%')->count();
        return $count > 0 ? "{$slug}-{$count}" : $slug;
    }
}
