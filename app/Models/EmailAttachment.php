<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class EmailAttachment extends Model
{
    use HasFactory;

    protected $fillable = [
        'email_log_id',
        'file_name',
        'file_path',
        'file_type',
        'file_extension',
        'file_size',
        'storage_disk',
        'attachment_type',
        'source_id',
        'description',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the email log that owns this attachment.
     */
    public function emailLog(): BelongsTo
    {
        return $this->belongsTo(EmailLog::class);
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
     * Get the file URL.
     */
    public function getFileUrlAttribute(): string
    {
        return Storage::disk($this->storage_disk)->url($this->file_path);
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
