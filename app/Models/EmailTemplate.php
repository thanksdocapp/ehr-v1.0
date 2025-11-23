<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmailTemplate extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'subject',
        'body',
        'description',
        'category',
        'target_roles',
        'status',
        'variables',
        'sender_name',
        'sender_email',
        'cc_emails',
        'bcc_emails',
        'attachments',
        'metadata',
        'last_used_at'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'variables' => 'json',
        'target_roles' => 'array',
        'cc_emails' => 'json', 
        'bcc_emails' => 'json',
        'attachments' => 'json',
        'metadata' => 'json',
        'last_used_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    
    /**
     * The attributes that should be set to null when empty.
     *
     * @var array
     */
    protected $nullable = [
        'description',
        'variables',
        'sender_name', 
        'sender_email',
        'cc_emails',
        'bcc_emails', 
        'attachments',
        'metadata',
        'last_used_at',
        'deleted_at'
    ];

    /**
     * Get the formatted name.
     *
     * @return string
     */
    public function getFormattedNameAttribute()
    {
        return ucwords(str_replace('_', ' ', $this->name));
    }

    /**
     * Get the preview of the body.
     *
     * @return string
     */
    public function getBodyPreviewAttribute()
    {
        return strlen($this->body) > 100 
            ? substr(strip_tags($this->body), 0, 100) . '...' 
            : strip_tags($this->body);
    }

    /**
     * Get list of available template variables.
     *
     * @return array
     */
    public function getAvailableVariablesAttribute()
    {
        return $this->variables ?? [
            'user_name' => 'User\'s full name',
            'user_email' => 'User\'s email address',
            'user_id' => 'User\'s ID',
            'site_name' => 'Site name',
            'site_url' => 'Site URL',
            'login_url' => 'Login URL',
            'date' => 'Current date',
            'time' => 'Current time'
        ];
    }

    /**
     * Get the status badge class.
     *
     * @return string
     */
    public function getStatusBadgeClassAttribute()
    {
        return [
            'active' => 'bg-success',
            'inactive' => 'bg-warning',
            'draft' => 'bg-info',
            'archived' => 'bg-secondary'
        ][$this->status] ?? 'bg-secondary';
    }

    /**
     * Replace variables in template content.
     *
     * @param array $data
     * @return string
     */
    public function parseContent(array $data)
    {
        $content = $this->body;
        
        foreach ($data as $key => $value) {
            $content = str_replace('{{'.$key.'}}', $value, $content);
            $content = str_replace('{{ '.$key.' }}', $value, $content);
        }
        
        return $content;
    }

    /**
     * Check if template has variables.
     *
     * @return bool
     */
    public function hasVariables()
    {
        return !empty($this->variables);
    }

    /**
     * Update last used timestamp.
     *
     * @return bool
     */
    public function markAsUsed()
    {
        return $this->update(['last_used_at' => now()]);
    }

    /**
     * Get related email logs.
     */
    public function emailLogs()
    {
        return $this->hasMany(EmailLog::class);
    }

    /**
     * Scope a query to only include active templates.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include templates of a given category.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $category
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope a query to only include templates for specific roles.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array $roles Single role or array of roles
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForRoles($query, $roles)
    {
        if (empty($roles)) {
            return $query;
        }

        $roles = is_array($roles) ? $roles : [$roles];

        return $query->where(function($q) use ($roles) {
            // Include templates that apply to all roles (null or empty target_roles)
            $q->whereNull('target_roles')
              ->orWhere('target_roles', '[]')
              ->orWhere('target_roles', 'null')
              ->orWhere('target_roles', '');
            
            // Also include templates that have the specified role(s) in their target_roles array
            foreach ($roles as $role) {
                $q->orWhereJsonContains('target_roles', $role);
            }
        });
    }

    /**
     * Check if template is applicable for a specific role.
     *
     * @param string $role
     * @return bool
     */
    public function isApplicableForRole(string $role): bool
    {
        // If no target roles specified, template is applicable to all
        if (empty($this->target_roles)) {
            return true;
        }

        return in_array($role, $this->target_roles ?? []);
    }

    /**
     * Get all available roles in the system.
     *
     * @return array
     */
    public static function getAvailableRoles(): array
    {
        return [
            'admin' => 'Administrator',
            'doctor' => 'Doctor',
            'nurse' => 'Nurse',
            'receptionist' => 'Receptionist',
            'pharmacist' => 'Pharmacist',
            'technician' => 'Laboratory Technician',
            'staff' => 'Staff',
            'patient' => 'Patient',
        ];
    }
}
