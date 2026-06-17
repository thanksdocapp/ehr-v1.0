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
        'department_logo', // Logo for department/clinic in patient emails
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
        'status',
        'post_booking_redirect_url',
        'clinic_post_booking_redirect_url',
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

    /**
     * All clinic/department IDs used for patient and record visibility (pivot + legacy column).
     *
     * @return list<int>
     */
    public function accessibleDepartmentIds(): array
    {
        $this->loadMissing('departments');

        $ids = $this->departments->pluck('id')->map(fn ($id) => (int) $id)->all();
        if ($this->department_id) {
            $ids[] = (int) $this->department_id;
        }

        return array_values(array_unique(array_filter($ids)));
    }

    /**
     * Clinic name(s) for admin lists (pivot departments + legacy department).
     */
    public function clinicsDisplayLabel(): string
    {
        if ($this->relationLoaded('departments') && $this->departments->isNotEmpty()) {
            $names = $this->departments->pluck('name')
                ->map(fn ($n) => trim((string) $n))
                ->filter()
                ->unique()
                ->values();

            if ($names->isNotEmpty()) {
                return $names->implode(', ');
            }
        }

        if ($this->relationLoaded('department') && $this->department) {
            $n = trim((string) $this->department->name);

            return $n !== '' ? $n : '—';
        }

        $dept = $this->primaryDepartment();
        if ($dept && filled($dept->name ?? null)) {
            return (string) $dept->name;
        }

        return '—';
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    public function testimonials(): HasMany
    {
        return $this->hasMany(Testimonial::class);
    }

    public function servicePrices(): HasMany
    {
        return $this->hasMany(DoctorServicePrice::class);
    }

    public function availabilityRules(): HasMany
    {
        return $this->hasMany(DoctorAvailabilityRule::class);
    }

    /**
     * Whether this doctor has any active modality-aware availability rules.
     */
    public function hasAvailabilityRules(): bool
    {
        return $this->availabilityRules()->where('is_active', true)->exists();
    }

    public function bookingDiscountCodes(): HasMany
    {
        return $this->hasMany(DoctorBookingDiscountCode::class);
    }

    public function doctorSettlements(): HasMany
    {
        return $this->hasMany(DoctorSettlement::class);
    }

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(BookingService::class, 'doctor_service_prices', 'doctor_id', 'service_id')
            ->withPivot('custom_price', 'custom_duration_minutes', 'is_active')
            ->withTimestamps();
    }

    /**
     * Booking services explicitly enabled for this doctor (doctor_service_prices), not every globally active service.
     */
    public function bookableBookingServices()
    {
        return $this->services()
            ->where('booking_services.is_active', true)
            ->wherePivot('is_active', true)
            ->orderBy('booking_services.sort_order')
            ->orderBy('booking_services.name')
            ->get();
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

    /**
     * Get department logo URL
     */
    public function getDepartmentLogoUrlAttribute()
    {
        if (!$this->department_logo) {
            return null;
        }

        $logoPath = 'uploads/doctors/' . $this->department_logo;
        
        // Get the storage URL - this uses the 'url' config from filesystems.php
        // which is set to '/storage-access' for this application
        $url = Storage::disk('public')->url($logoPath);
        
        // Storage::url() returns full URL if APP_URL is set in config, otherwise relative path
        // The config has: 'url' => env('APP_URL') ? env('APP_URL').'/storage-access' : '/storage-access'
        // So if APP_URL is set, it returns full URL like 'https://domain.com/storage-access/...'
        // Otherwise it returns '/storage-access/...' which we need to convert
        
        if (!str_starts_with($url, 'http')) {
            // Relative path, make it absolute
            return url($url);
        }
        
        // Already absolute URL
        return $url;
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

    /**
     * Legacy installs may have multiple doctors rows per user.
     *
     * @return list<int>
     */
    public function bookingDoctorIds(): array
    {
        if ($this->user_id) {
            return static::query()
                ->where('user_id', $this->user_id)
                ->where('is_active', true)
                ->orderBy('id')
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();
        }

        return [(int) $this->id];
    }

    /**
     * Doctor row used for public booking links and slot availability.
     */
    public function bookingProfileDoctor(): self
    {
        if (! $this->user_id) {
            return $this;
        }

        $candidates = static::query()
            ->where('user_id', $this->user_id)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();

        if ($candidates->count() <= 1) {
            return $this;
        }

        $withRules = $candidates->first(fn (self $doctor) => $doctor->hasAvailabilityRules());
        if ($withRules) {
            return $withRules;
        }

        $withLegacyAvailability = $candidates->first(function (self $doctor) {
            return is_array($doctor->availability) && $doctor->availability !== [];
        });
        if ($withLegacyAvailability) {
            return $withLegacyAvailability;
        }

        return $candidates->first() ?? $this;
    }

    public static function findActiveForBooking(int $doctorId): self
    {
        $doctor = static::query()->where('id', $doctorId)->active()->firstOrFail();

        return $doctor->bookingProfileDoctor();
    }
}
