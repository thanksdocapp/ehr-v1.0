<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'image',
        'color',
        'head_of_department',
        'location',
        'phone',
        'email',
        'services',
        'working_hours',
        'sort_order',
        'is_emergency',
        'is_active'
    ];

    protected $casts = [
        'services' => 'array',
        'is_emergency' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Relationships
    // Many-to-many relationship with doctors
    public function doctors(): BelongsToMany
    {
        return $this->belongsToMany(Doctor::class, 'doctor_department')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    // Legacy relationship for backward compatibility
    public function directDoctors(): HasMany
    {
        return $this->hasMany(Doctor::class);
    }

    // Many-to-many relationship with users
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_department')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    // Legacy relationship for backward compatibility
    public function directUsers(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    // Many-to-many relationship with patients
    public function patients(): BelongsToMany
    {
        return $this->belongsToMany(Patient::class, 'department_patient')
            ->withPivot('is_primary')
            ->withTimestamps();
    }
    
    // Legacy relationship for backward compatibility
    public function directPatients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeEmergency($query)
    {
        return $query->where('is_emergency', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    // Accessors
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            // Clean the image path
            $imagePath = ltrim($this->image, '/');
            
            // Primary paths with symlink
            $symlinkPaths = [
                'storage/departments/' . $imagePath,
                'storage/' . $imagePath,
                'storage/uploads/departments/' . $imagePath,
            ];
            
            foreach ($symlinkPaths as $symlinkPath) {
                if (file_exists(public_path($symlinkPath))) {
                    return asset($symlinkPath);
                }
            }
            
            // Fallback for shared hosting without symlinks
            $storagePaths = [
                'app/public/departments/' . $imagePath,
                'app/public/' . $imagePath,
                'app/public/uploads/departments/' . $imagePath,
            ];
            
            foreach ($storagePaths as $storagePath) {
                if (file_exists(storage_path($storagePath))) {
                    return url('storage-access/' . str_replace('app/public/', '', $storagePath));
                }
            }
        }
        
        return null;
    }

    public function getFullNameAttribute()
    {
        return $this->name . ' Department';
    }

    // Helper methods
    public function getTotalDoctorsAttribute()
    {
        return $this->doctors()->where('is_active', true)->count();
    }

    public function getTotalAppointmentsAttribute()
    {
        return $this->appointments()->whereDate('appointment_date', today())->count();
    }
}
