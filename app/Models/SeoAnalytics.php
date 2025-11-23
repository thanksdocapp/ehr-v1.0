<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoAnalytics extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'views',
        'clicks',
        'impressions',
        'ctr',
        'position',
        'recorded_at'
    ];

    protected $casts = [
        'recorded_at' => 'datetime',
        'ctr' => 'decimal:2',
        'position' => 'decimal:2'
    ];

    public function page()
    {
        return $this->belongsTo(SeoPage::class);
    }
}
