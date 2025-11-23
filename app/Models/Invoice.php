<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_id',
        'patient_id',
        'appointment_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_amount',
        'status',
        'description',
        'notes',
        'paid_date',
    ];

    protected $casts = [
        'billing_id' => 'integer',
        'patient_id' => 'integer',
        'appointment_id' => 'integer',
        'invoice_date' => 'date',
        'due_date' => 'date',
        'paid_date' => 'datetime',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
    ];

    // Relationships
    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    // Scopes
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                    ->where('due_date', '<', now());
    }

    // Helper methods
    public function getOutstandingAmountAttribute()
    {
        $paidAmount = $this->payments()->where('status', 'completed')->sum('amount');
        return $this->total_amount - $paidAmount;
    }

    public function getPaidAmountAttribute()
    {
        return $this->payments()->where('status', 'completed')->sum('amount');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && $this->due_date->isPast();
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === 'partial';
    }

    // Generate unique invoice number
    public static function generateInvoiceNumber(): string
    {
        do {
            $number = 'INV' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('invoice_number', $number)->exists());

        return $number;
    }
}
