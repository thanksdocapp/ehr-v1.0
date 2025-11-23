<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class PaymentTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_id',
        'billing_id',
        'invoice_id',
        'payment_gateway_id',
        'user_id',
        'amount',
        'currency',
        'gateway_fee',
        'net_amount',
        'gateway_transaction_id',
        'gateway_payment_url',
        'status',
        'gateway_status',
        'crypto_currency',
        'crypto_amount',
        'crypto_address',
        'transaction_hash',
        'confirmations',
        'required_confirmations',
        'gateway_response',
        'webhook_data',
        'notes',
        'expires_at',
        'paid_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'gateway_fee' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'crypto_amount' => 'decimal:8',
        'gateway_response' => 'array',
        'webhook_data' => 'array',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
        'confirmations' => 'integer',
        'required_confirmations' => 'integer',
    ];

    // Relationships
    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentGateway(): BelongsTo
    {
        return $this->belongsTo(PaymentGateway::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->whereIn('status', ['failed', 'expired', 'cancelled']);
    }

    public function scopeCrypto($query)
    {
        return $query->whereNotNull('crypto_currency');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now())
                    ->where('status', 'pending');
    }

    // Mutators & Accessors
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast() && $this->status === 'pending';
    }

    public function getIsCryptocurrencyAttribute(): bool
    {
        return !empty($this->crypto_currency);
    }

    public function getStatusBadgeAttribute(): string
    {
        $badges = [
            'pending' => 'warning',
            'processing' => 'info',
            'completed' => 'success',
            'failed' => 'danger',
            'expired' => 'secondary',
            'cancelled' => 'secondary',
            'refunded' => 'dark'
        ];

        return $badges[$this->status] ?? 'secondary';
    }

    // Methods
    public function markAsPaid(array $additionalData = []): bool
    {
        return $this->update(array_merge([
            'status' => 'completed',
            'paid_at' => now(),
        ], $additionalData));
    }

    public function markAsFailed(string $reason = null): bool
    {
        return $this->update([
            'status' => 'failed',
            'notes' => $reason,
        ]);
    }

    public function updateConfirmations(int $confirmations): bool
    {
        $this->confirmations = $confirmations;
        
        // Auto-complete if confirmations meet requirement
        if ($confirmations >= $this->required_confirmations && $this->status === 'processing') {
            $this->status = 'completed';
            $this->paid_at = now();
        }
        
        return $this->save();
    }

    public function generateTransactionId(): string
    {
        return 'TXN_' . time() . '_' . strtoupper(substr(md5(uniqid()), 0, 8));
    }

    // Boot method
    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($transaction) {
            if (empty($transaction->transaction_id)) {
                $transaction->transaction_id = $transaction->generateTransactionId();
            }
            
            // Calculate net amount if not set
            if (empty($transaction->net_amount)) {
                $transaction->net_amount = $transaction->amount - $transaction->gateway_fee;
            }
        });
    }
}
