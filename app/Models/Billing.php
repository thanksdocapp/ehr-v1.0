<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class Billing extends Model
{
    use HasFactory;

    protected $fillable = [
        'bill_number',
        'patient_id',
        'doctor_id',
        'appointment_id',
        'billing_date',
        'due_date',
        'type',
        'description',
        'subtotal',
        'discount',
        'tax',
        'total_amount',
        'paid_amount',
        'balance',
        'payment_method',
        'payment_reference',
        'status',
        'notes',
        'created_by',
        'updated_by',
        'paid_at',
    ];

    protected $casts = [
        'billing_date' => 'date',
        'due_date' => 'date',
        'paid_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'tax' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance' => 'decimal:2',
    ];

    // Relationships
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    // Scopes
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
        return $query->where('status', 'overdue')
                    ->orWhere(function($q) {
                        $q->where('status', 'pending')
                          ->where('due_date', '<', now());
                    });
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    // Helper methods
    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || 
               ($this->status === 'pending' && $this->due_date && $this->due_date->isPast());
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'paid' => 'success',
            'pending' => 'warning',
            'partial' => 'info',
            'overdue' => 'danger',
            'cancelled' => 'secondary',
            default => 'secondary'
        };
    }

    public function getTypeDisplayAttribute(): string
    {
        return match($this->type) {
            'consultation' => 'Consultation',
            'procedure' => 'Medical Procedure',
            'medication' => 'Medication',
            'lab_test' => 'Laboratory Test',
            'other' => 'Other',
            default => 'Unknown'
        };
    }

    // Generate unique bill number
    public static function generateBillNumber(): string
    {
        do {
            $number = 'BILL' . date('Y') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (static::where('bill_number', $number)->exists());

        return $number;
    }

    // Relationship with Invoice (for patient portal sync)
    public function invoice(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Invoice::class, 'billing_id');
    }

    // Calculate balance automatically when saving
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($billing) {
            $billing->balance = $billing->total_amount - $billing->paid_amount;
            
            // Update status based on payment
            if ($billing->paid_amount >= $billing->total_amount) {
                $billing->status = 'paid';
                if (!$billing->paid_at) {
                    $billing->paid_at = now();
                }
            } elseif ($billing->paid_amount > 0) {
                $billing->status = 'partially_paid';
            } else {
                // Only auto-update status for unpaid bills
                // If due date is in the past and status is pending, make it overdue
                // If due date is in the future and status is overdue, make it pending
                if ($billing->due_date) {
                    if ($billing->due_date->isPast() && $billing->status === 'pending') {
                        $billing->status = 'overdue';
                    } elseif ($billing->due_date->isFuture() && $billing->status === 'overdue') {
                        $billing->status = 'pending';
                    }
                }
                
                // For new records without status, set default based on due date
                if (!$billing->status) {
                    if ($billing->due_date && $billing->due_date->isPast()) {
                        $billing->status = 'overdue';
                    } else {
                        $billing->status = 'pending';
                    }
                }
            }
        });

        // Sync with patient invoice after saving
        static::saved(function ($billing) {
            $billing->syncWithInvoice();
        });

        // Clean up invoice when billing is deleted
        static::deleting(function ($billing) {
            if ($billing->invoice) {
                $billing->invoice->delete();
            }
        });
    }

    /**
     * Synchronize billing data with patient invoice
     */
    public function syncWithInvoice()
    {
        // Find or create corresponding invoice
        $invoice = $this->invoice ?: new Invoice();
        
        // Calculate due_date: use billing's due_date if set, otherwise default to 30 days from billing_date
        $dueDate = $this->due_date;
        if (!$dueDate && $this->billing_date) {
            $dueDate = \Carbon\Carbon::parse($this->billing_date)->addDays(30);
        } elseif (!$dueDate) {
            // Fallback: use current date + 30 days if billing_date is also null
            $dueDate = now()->addDays(30);
        }
        
        $invoice->fill([
            'billing_id' => $this->id,
            'patient_id' => $this->patient_id,
            'appointment_id' => $this->appointment_id,
            'invoice_number' => $this->bill_number, // Use same number for consistency
            'invoice_date' => $this->billing_date ?? now(),
            'due_date' => $dueDate,
            'subtotal' => $this->subtotal,
            'tax_amount' => $this->tax,
            'discount_amount' => $this->discount,
            'total_amount' => $this->total_amount,
            'status' => $this->mapStatusToInvoice($this->status),
            'description' => $this->description,
            'notes' => $this->notes,
            'paid_date' => $this->paid_at,
        ]);
        
        $invoice->save();
        
        // Generate payment token for public payment access if not already set
        // Only if the column exists in the database
        if (Schema::hasColumn('invoices', 'payment_token')) {
            if (!$invoice->payment_token) {
                $invoice->generatePaymentToken();
            }
        }
        
        // If payment was made, create/update payment record
        if ($this->paid_amount > 0 && $this->payment_method) {
            $this->syncPaymentRecord($invoice);
        }
    }

    /**
     * Map billing status to invoice status
     */
    private function mapStatusToInvoice($status)
    {
        return match($status) {
            'pending' => 'pending',
            'paid' => 'paid', 
            'partially_paid' => 'partial',
            'partial' => 'partial',
            'overdue' => 'overdue',
            'cancelled' => 'cancelled',
            default => 'pending'
        };
    }

    /**
     * Sync payment record with invoice
     */
    private function syncPaymentRecord($invoice)
    {
        // Check if payment record already exists
        $existingPayment = Payment::where('invoice_id', $invoice->id)
            ->where('transaction_reference', 'BILLING_' . $this->id)
            ->first();
            
        if ($existingPayment) {
            // Update existing payment
            $existingPayment->update([
                'amount' => $this->paid_amount,
                'payment_method' => $this->mapPaymentMethod($this->payment_method),
                'status' => 'completed',
                'notes' => 'Payment processed via admin panel' . ($this->payment_reference ? ' - Ref: ' . $this->payment_reference : ''),
            ]);
        } else {
            // Create new payment record
            Payment::create([
                'invoice_id' => $invoice->id,
                'payment_date' => $this->paid_at ?: now(),
                'amount' => $this->paid_amount,
                'payment_method' => $this->mapPaymentMethod($this->payment_method),
                'transaction_id' => $this->generateTransactionId(),
                'transaction_reference' => 'BILLING_' . $this->id, // Reference to original billing
                'status' => 'completed',
                'notes' => 'Payment processed via admin panel' . ($this->payment_reference ? ' - Ref: ' . $this->payment_reference : ''),
            ]);
        }
    }

    /**
     * Map admin payment methods to patient portal payment methods
     */
    private function mapPaymentMethod($method)
    {
        return match($method) {
            'card' => 'credit_card',
            'cash' => 'cash',
            'insurance' => 'insurance',
            'bank_transfer' => 'bank_transfer',
            default => 'cash'
        };
    }

    /**
     * Generate transaction ID for payment
     */
    private function generateTransactionId()
    {
        return 'TXN' . date('Ymd') . $this->id . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
    }

    // Get available billing types
    public static function getTypes(): array
    {
        return [
            'consultation' => 'Consultation',
            'procedure' => 'Medical Procedure',
            'medication' => 'Medication',
            'lab_test' => 'Laboratory Test',
            'other' => 'Other',
        ];
    }

    // Get available payment methods
    public static function getPaymentMethods(): array
    {
        return [
            'cash' => 'Cash',
            'card' => 'Credit/Debit Card',
            'insurance' => 'Insurance',
            'bank_transfer' => 'Bank Transfer',
        ];
    }

    // Get available statuses
    public static function getStatuses(): array
    {
        return [
            'pending' => 'Pending',
            'partial' => 'Partially Paid',
            'paid' => 'Paid',
            'overdue' => 'Overdue',
            'cancelled' => 'Cancelled',
        ];
    }
}
