<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biller extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'code',
        'fee_percentage',
        'minimum_fee',
        'maximum_fee',
        'status',
        'description',
        'metadata'
    ];

    protected $casts = [
        'fee_percentage' => 'decimal:2',
        'minimum_fee' => 'decimal:2',
        'maximum_fee' => 'decimal:2',
        'metadata' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    /**
     * Biller type constants
     */
    const TYPE_UTILITIES = 'utilities';
    const TYPE_TELECOMMUNICATIONS = 'telecommunications';
    const TYPE_INSURANCE = 'insurance';
    const TYPE_GOVERNMENT = 'government';
    const TYPE_EDUCATION = 'education';
    const TYPE_HEALTHCARE = 'healthcare';
    const TYPE_OTHER = 'other';

    /**
     * Get bill payments for this biller.
     */
    public function billPayments()
    {
        return $this->hasMany(BillPayment::class, 'biller_name', 'name');
    }

    /**
     * Check if biller is active.
     */
    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Get payment count.
     */
    public function getPaymentCountAttribute()
    {
        return $this->billPayments()->count();
    }

    /**
     * Get total amount processed.
     */
    public function getTotalAmountAttribute()
    {
        return $this->billPayments()->where('status', 'completed')->sum('amount');
    }

    /**
     * Get formatted fee percentage.
     */
    public function getFormattedFeePercentageAttribute()
    {
        return number_format($this->fee_percentage, 2) . '%';
    }

    /**
     * Get display name for biller type.
     */
    public function getTypeDisplayAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->type));
    }

    /**
     * Scope for active billers.
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    /**
     * Scope for specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Get biller type options for forms.
     */
    public static function getTypeOptions()
    {
        return [
            self::TYPE_UTILITIES => 'Utilities',
            self::TYPE_TELECOMMUNICATIONS => 'Telecommunications',
            self::TYPE_INSURANCE => 'Insurance',
            self::TYPE_GOVERNMENT => 'Government Services',
            self::TYPE_EDUCATION => 'Education',
            self::TYPE_HEALTHCARE => 'Healthcare',
            self::TYPE_OTHER => 'Other',
        ];
    }

    /**
     * Get status options for forms.
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_INACTIVE => 'Inactive',
        ];
    }
}
