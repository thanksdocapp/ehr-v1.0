<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'billing_id',
        'patient_id',
        'appointment_id',
        'invoice_number',
        'payment_token',
        'payment_token_expires_at',
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
        'payment_token_expires_at' => 'datetime',
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

    /**
     * Generate a secure payment token for public payment access
     */
    public function generatePaymentToken(): string
    {
        // Check if payment_token columns exist in database
        if (!Schema::hasColumn('invoices', 'payment_token')) {
            \Log::warning('payment_token column does not exist in invoices table. Please run migration: 2025_11_27_004106_add_payment_token_to_invoices_table');
            return ''; // Return empty string if column doesn't exist
        }

        if ($this->payment_token && $this->payment_token_expires_at && $this->payment_token_expires_at->isFuture()) {
            return $this->payment_token; // Return existing valid token
        }

        $token = bin2hex(random_bytes(32)); // 64 character token
        
        try {
            $this->update([
                'payment_token' => $token,
                'payment_token_expires_at' => now()->addDays(90), // Token valid for 90 days
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to update payment_token', [
                'invoice_id' => $this->id,
                'error' => $e->getMessage()
            ]);
            // If update fails, return empty string
            return '';
        }

        return $token;
    }

    /**
     * Get public payment URL
     */
    public function getPublicPaymentUrl(): string
    {
        $token = $this->generatePaymentToken();
        
        if (empty($token)) {
            \Log::error('Cannot generate public payment URL - token is empty', [
                'invoice_id' => $this->id,
                'has_payment_token_column' => Schema::hasColumn('invoices', 'payment_token'),
                'current_payment_token' => $this->payment_token ?? 'null'
            ]);
            throw new \Exception('Cannot generate payment URL. Payment token column may not exist or token generation failed.');
        }
        
        try {
            $url = route('public.billing.pay', ['token' => $token]);
            
            if (empty($url)) {
                \Log::error('Route generated empty URL', [
                    'invoice_id' => $this->id,
                    'token' => $token,
                    'route_name' => 'public.billing.pay'
                ]);
                throw new \Exception('Failed to generate payment URL. Route may not be defined.');
            }
            
            return $url;
        } catch (\Exception $e) {
            \Log::error('Failed to generate payment URL route', [
                'invoice_id' => $this->id,
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            throw new \Exception('Failed to generate payment URL: ' . $e->getMessage());
        }
    }

    /**
     * Check if payment token is valid
     */
    public function isPaymentTokenValid(?string $token): bool
    {
        if (!$token || !$this->payment_token) {
            return false;
        }

        if ($this->payment_token !== $token) {
            return false;
        }

        if ($this->payment_token_expires_at && $this->payment_token_expires_at->isPast()) {
            return false;
        }

        return true;
    }
}
