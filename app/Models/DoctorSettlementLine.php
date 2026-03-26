<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DoctorSettlementLine extends Model
{
    protected $fillable = [
        'doctor_settlement_id',
        'billing_id',
        'description',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function settlement(): BelongsTo
    {
        return $this->belongsTo(DoctorSettlement::class, 'doctor_settlement_id');
    }

    public function billing(): BelongsTo
    {
        return $this->belongsTo(Billing::class);
    }
}
