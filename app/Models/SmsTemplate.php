<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SmsTemplate extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'message',
        'description',
        'category',
        'status',
        'variables',
        'sender_id',
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
        'metadata' => 'json',
        'last_used_at' => 'datetime'
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
     * Get the message preview.
     *
     * @return string
     */
    public function getMessagePreviewAttribute()
    {
        return strlen($this->message) > 100 
            ? substr($this->message, 0, 100) . '...' 
            : $this->message;
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
            'user_phone' => 'User\'s phone number',
            'user_id' => 'User\'s ID',
            'site_name' => 'Site name',
            'site_url' => 'Site URL',
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
            'draft' => 'bg-info'
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
        $content = $this->message;
        
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
     * Get the SMS logs for this template.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function smsLogs()
    {
        return $this->hasMany(SmsLog::class);
    }
}
