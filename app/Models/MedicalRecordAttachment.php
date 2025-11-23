<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class MedicalRecordAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_record_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_type',
        'file_extension',
        'file_size',
        'storage_disk',
        'file_category',
        'description',
        'is_private',
        'virus_scan_status',
        'virus_scan_at',
        'virus_scan_result',
        'expires_at',
    ];

    protected $casts = [
        'medical_record_id' => 'integer',
        'uploaded_by' => 'integer',
        'file_size' => 'integer',
        'is_private' => 'boolean',
        'virus_scan_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the medical record that owns this attachment.
     */
    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * Get the user who uploaded this attachment.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Get the file size in human-readable format.
     */
    public function getFileSizeHumanAttribute(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get a signed URL for accessing the file (with expiration).
     */
    public function getSignedUrl(int $expirationMinutes = 60): string
    {
        if ($this->storage_disk === 'public') {
            // Public files can use regular URL
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk('public');
            return $disk->url($this->file_path);
        }

        // Private files - try to get temporary URL, fallback to download route
        try {
            /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
            $disk = Storage::disk($this->storage_disk);
            if (method_exists($disk, 'temporaryUrl')) {
                return $disk->temporaryUrl(
                    $this->file_path,
                    now()->addMinutes($expirationMinutes)
                );
            }
        } catch (\Exception $e) {
            // If temporary URL not supported, return download route
        }

        // Fallback: return download route (will be handled by controller)
        return route('admin.medical-record-attachments.download', $this->id);
    }

    /**
     * Check if the file has expired based on retention policy.
     */
    public function isExpired(): bool
    {
        if (!$this->expires_at) {
            return false; // No expiration set
        }

        return $this->expires_at->isPast();
    }

    /**
     * Check if the file has been virus scanned and is clean.
     */
    public function isVirusScanned(): bool
    {
        return $this->virus_scan_status !== 'pending';
    }

    /**
     * Check if the file is safe (virus scanned and clean).
     */
    public function isSafe(): bool
    {
        return $this->virus_scan_status === 'clean';
    }

    /**
     * Get the file icon based on file type.
     */
    public function getFileIconAttribute(): string
    {
        $extension = strtolower($this->file_extension);
        
        $iconMap = [
            'pdf' => 'fa-file-pdf',
            'doc' => 'fa-file-word',
            'docx' => 'fa-file-word',
            'xls' => 'fa-file-excel',
            'xlsx' => 'fa-file-excel',
            'jpg' => 'fa-file-image',
            'jpeg' => 'fa-file-image',
            'png' => 'fa-file-image',
            'gif' => 'fa-file-image',
            'txt' => 'fa-file-alt',
            'zip' => 'fa-file-archive',
            'rar' => 'fa-file-archive',
        ];

        return $iconMap[$extension] ?? 'fa-file';
    }

    /**
     * Check if the file can be viewed in browser (images, PDFs).
     */
    public function isViewable(): bool
    {
        $extension = strtolower($this->file_extension);
        $viewableExtensions = ['pdf', 'jpg', 'jpeg', 'png', 'gif'];
        return in_array($extension, $viewableExtensions);
    }

    /**
     * Check if user has permission to access this file.
     */
    public function canAccess($user): bool
    {
        // Admin can always access
        if ($user->is_admin ?? false) {
            return true;
        }

        // If file is private, only uploader and medical record creator can access
        if ($this->is_private) {
            $medicalRecord = $this->medicalRecord;
            return $this->uploaded_by === $user->id 
                || $medicalRecord->created_by === $user->id
                || $medicalRecord->doctor_id === $user->id;
        }

        // Public files can be accessed by staff/doctors
        return in_array($user->role ?? '', ['doctor', 'nurse', 'staff', 'receptionist']);
    }

    /**
     * Delete the physical file when the model is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($attachment) {
            // Delete the physical file
            if (Storage::disk($attachment->storage_disk)->exists($attachment->file_path)) {
                Storage::disk($attachment->storage_disk)->delete($attachment->file_path);
            }
        });
    }
}
