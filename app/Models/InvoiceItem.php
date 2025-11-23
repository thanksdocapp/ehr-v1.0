<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'item_type',
        'item_name',
        'description',
        'quantity',
        'unit_price',
        'total_price',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    // Mutators
    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = $value;
        $this->calculateTotal();
    }

    public function setUnitPriceAttribute($value)
    {
        $this->attributes['unit_price'] = $value;
        $this->calculateTotal();
    }

    // Helper methods
    private function calculateTotal()
    {
        if (isset($this->attributes['quantity']) && isset($this->attributes['unit_price'])) {
            $this->attributes['total_price'] = $this->attributes['quantity'] * $this->attributes['unit_price'];
        }
    }

    public function getTotalAttribute()
    {
        return $this->quantity * $this->unit_price;
    }
}
