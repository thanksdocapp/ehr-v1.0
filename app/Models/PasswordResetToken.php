<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PasswordResetToken extends Model
{
    protected $table = 'admin_password_reset_tokens';
    
    protected $fillable = [
        'user_id',
        'token',
        'reset_by_admin_id',
        'reason',
        'force_password_change',
        'invalidate_sessions',
        'used',
        'expires_at',
        'used_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'force_password_change' => 'boolean',
        'invalidate_sessions' => 'boolean',
        'used' => 'boolean',
        'expires_at' => 'datetime',
        'used_at' => 'datetime',
    ];

    /**
     * Get the user that this reset token belongs to
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the admin who initiated the reset
     */
    public function resetBy()
    {
        return $this->belongsTo(User::class, 'reset_by_admin_id');
    }

    /**
     * Check if the token is valid (not expired and not used)
     */
    public function isValid()
    {
        return !$this->used 
            && $this->expires_at->isFuture();
    }

    /**
     * Check if the token is expired
     */
    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    /**
     * Mark the token as used
     */
    public function markAsUsed()
    {
        $this->update([
            'used' => true,
            'used_at' => now(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Generate a secure reset token
     */
    public static function generateToken()
    {
        return hash('sha256', Str::random(60) . time());
    }

    /**
     * Create a password reset token for a user
     */
    public static function createForUser($userId, $adminId, $reason = null, $hoursValid = 24)
    {
        // Invalidate any existing tokens for this user
        static::where('user_id', $userId)
            ->where('used', false)
            ->update(['used' => true, 'used_at' => now()]);

        // Create new token
        return static::create([
            'user_id' => $userId,
            'token' => static::generateToken(),
            'reset_by_admin_id' => $adminId,
            'reason' => $reason,
            'force_password_change' => true,
            'invalidate_sessions' => true,
            'expires_at' => now()->addHours($hoursValid),
        ]);
    }

    /**
     * Clean up expired tokens
     */
    public static function cleanupExpired()
    {
        return static::where('expires_at', '<', now())
            ->where('used', false)
            ->delete();
    }
}
