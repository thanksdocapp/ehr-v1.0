<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BannerSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'description',
        'image',
        'button_text',
        'button_url',
        'text_color',
        'background_color',
        'sort_order',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at');
    }

    // Accessors for shared hosting compatibility
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            // Clean the image path
            $imagePath = ltrim($this->image, '/');
            
            // Primary path with symlink
            $symlinkPath = 'storage/' . $imagePath;
            if (file_exists(public_path($symlinkPath))) {
                return asset($symlinkPath);
            }
            
            // Alternative paths for banner slides
            $altPaths = [
                'storage/banner-slides/' . basename($imagePath),
                'storage/uploads/banner-slides/' . basename($imagePath),
            ];
            
            foreach ($altPaths as $altPath) {
                if (file_exists(public_path($altPath))) {
                    return asset($altPath);
                }
            }
            
            // Fallback for shared hosting without symlinks
            $storagePaths = [
                'app/public/' . $imagePath,
                'app/public/banner-slides/' . basename($imagePath),
                'app/public/uploads/banner-slides/' . basename($imagePath),
            ];
            
            foreach ($storagePaths as $storagePath) {
                if (file_exists(storage_path($storagePath))) {
                    return url('storage-access/' . str_replace('app/public/', '', $storagePath));
                }
            }
        }
        
        return asset('assets/images/default-banner.jpg');
    }
}
