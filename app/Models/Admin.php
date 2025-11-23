<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class Admin extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'role',
        'avatar',
        'phone',
        'is_active',
        'permissions',
        'email_verified_at',
        'last_login_at',
        'last_login_ip',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'permissions' => 'array',
    ];

    // Relationships
    public function deposits()
    {
        return $this->hasMany(UserDeposit::class, 'approved_by');
    }

    public function loanApplications()
    {
        return $this->hasMany(LoanApplication::class, 'reviewed_by');
    }

    public function kycDocuments()
    {
        return $this->hasMany(KycDocument::class, 'verified_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Accessors & Mutators
    public function getFullNameAttribute()
    {
        return $this->name;
    }

    public function getAvatarUrlAttribute()
    {
        if ($this->avatar && file_exists(public_path('storage/' . $this->avatar))) {
            return asset('storage/' . $this->avatar);
        }
        
        if (file_exists(public_path('assets/images/default-avatar.png'))) {
            return asset('assets/images/default-avatar.png');
        }
        
        if (file_exists(public_path('assets/images/default-avatar.svg'))) {
            return asset('assets/images/default-avatar.svg');
        }
        
        return 'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" width="150" height="150"><rect width="150" height="150" fill="#e94560"/><text x="75" y="80" font-family="Arial" font-size="24" fill="white" text-anchor="middle">Admin</text></svg>');
    }

    // Helper methods
    public function isSuperAdmin()
    {
        return $this->role === 'super_admin';
    }

    public function hasPermission($permission)
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        return in_array($permission, $this->permissions ?? []);
    }

    public function updateLastLogin()
    {
        $this->update([
            'last_login_at' => now(),
            'last_login_ip' => request()->ip(),
        ]);
    }
}