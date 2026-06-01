<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'payment_date',
        'amount',
        'payment_method',
        'payment_gateway',
        'gateway_transaction_id',
        'transaction_id',
        'transaction_reference',
        'status',
        'gateway_response',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'datetime',
        'amount' => 'decimal:2',
        'gateway_response' => 'array',
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('payment_method', $method);
    }

    /**
     * Exclude admin billing-sync rows when the same invoice already has a portal/checkout payment.
     */
    public function scopeWithoutDuplicateBillingSync($query)
    {
        return $query->where(function ($q) {
            $q->where(function ($inner) {
                $inner->whereNull('transaction_reference')
                    ->orWhere('transaction_reference', 'not like', 'BILLING_%');
            })->orWhereNotExists(function ($sub) {
                $sub->from('payments as portal_payment')
                    ->whereColumn('portal_payment.invoice_id', 'payments.invoice_id')
                    ->where('portal_payment.status', 'completed')
                    ->whereColumn('portal_payment.id', '!=', 'payments.id')
                    ->where(function ($w) {
                        $w->whereNull('portal_payment.transaction_reference')
                            ->orWhere('portal_payment.transaction_reference', 'not like', 'BILLING_%');
                    });
            });
        });
    }

    // Helper methods
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function getFormattedAmountAttribute(): string
    {
        return '$' . number_format($this->amount, 2);
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return match($this->payment_method) {
            'credit_card' => 'Credit Card',
            'debit_card' => 'Debit Card',
            'bank_transfer' => 'Bank Transfer',
            'cash' => 'Cash',
            'insurance' => 'Insurance',
            default => ucfirst(str_replace('_', ' ', $this->payment_method))
        };
    }
}
