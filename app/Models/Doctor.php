<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Doctor extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',        // Link to users table
        'name',           // Added for compatibility
        'title',
        'first_name',
        'last_name',
        'slug',
        'specialization',
        'department_id',  // Kept for backward compatibility
        'bio',
        'photo',
        'image',          // Added for compatibility
        'qualification',
        'experience_years',
        'languages',
        'specialties',
        'email',
        'phone',
        'room_number',
        'consultation_fee',
        'availability',
        'working_hours',  // Added for admin compatibility
        'license_number', // Added for admin compatibility
        'is_available_online',
        'sort_order',
        'is_featured',
        'is_active',
        'status'
    ];

    protected $casts = [
        'languages' => 'array',
        'specialties' => 'array',
        'availability' => 'array',
        'consultation_fee' => 'decimal:2',
        'experience_years' => 'integer',
        'is_available_online' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Many-to-many relationship with departments
    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'doctor_department')
            ->withPivot('is_primary')
            ->withTimestamps()
            ->orderByPivot('is_primary', 'desc');
    }

    // Primary department relationship (for backward compatibility)
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    // Get primary department from pivot table
    public function primaryDepartment()
    {
        return $this->departments()->wherePivot('is_primary', true)->first() 
            ?? $this->departments()->first() 
            ?? $this->department; // Fallback to old department_id
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeAvailableOnline($query)
    {
        return $query->where('is_available_online', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeByDepartment($query, $departmentId)
    {
        // Support both old department_id and new pivot table
        return $query->where(function($q) use ($departmentId) {
            $q->where('department_id', $departmentId)
              ->orWhereHas('departments', function($deptQuery) use ($departmentId) {
                  $deptQuery->where('departments.id', $departmentId);
              });
        });
    }

    // Scope to get doctors with any of the given departments
    public function scopeByDepartments($query, array $departmentIds)
    {
        return $query->where(function($q) use ($departmentIds) {
            $q->whereIn('department_id', $departmentIds)
              ->orWhereHas('departments', function($deptQuery) use ($departmentIds) {
                  $deptQuery->whereIn('departments.id', $departmentIds);
              });
        });
    }

    // Accessors
    public function getFullNameAttribute()
    {
        return $this->title . ' ' . $this->first_name . ' ' . $this->last_name;
    }

    // Frontend compatibility - name accessor
    public function getNameAttribute()
    {
        return $this->getFullNameAttribute();
    }

    // Frontend compatibility - image accessor
    public function getImageAttribute()
    {
        return $this->photo;
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            // Clean the photo path
            $photoPath = ltrim($this->photo, '/');
            
            // Check if path already contains 'uploads/doctors/'
            if (str_starts_with($photoPath, 'uploads/doctors/')) {
                // Use the path as is
                return Storage::disk('public')->url($photoPath);
            } else {
                // Add the uploads/doctors/ prefix
                return Storage::disk('public')->url('uploads/doctors/' . $photoPath);
            }
        }
        
        return asset('assets/images/default-doctor.svg');
    }

    // Frontend compatibility - image_url accessor
    public function getImageUrlAttribute()
    {
        return $this->getPhotoUrlAttribute();
    }

    public function getExperienceTextAttribute()
    {
        return $this->experience_years . '+ years experience';
    }

    // Helper methods
    public function getTotalAppointmentsAttribute()
    {
        return $this->appointments()->count();
    }

    public function getTodayAppointmentsAttribute()
    {
        return $this->appointments()
            ->whereDate('appointment_date', today())
            ->count();
    }

    public function getAverageRatingAttribute()
    {
        return $this->testimonials()
            ->where('is_approved', true)
            ->avg('rating') ?? 5;
    }

    public function isAvailableOn($day)
    {
        if (!$this->availability) {
            return false;
        }

        return isset($this->availability[$day]) && $this->availability[$day]['available'] === true;
    }

    public function getAvailableTimesOn($day)
    {
        if (!$this->isAvailableOn($day)) {
            return [];
        }

        return $this->availability[$day]['times'] ?? [];
    }
}
