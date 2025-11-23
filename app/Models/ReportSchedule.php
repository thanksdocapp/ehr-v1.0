<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'report_id',
        'frequency',
        'schedule_config',
        'recipients',
        'export_format',
        'is_active',
        'created_by',
        'last_run_at',
        'next_run_at',
        'run_count'
    ];

    protected $casts = [
        'schedule_config' => 'array',
        'recipients' => 'array',
        'is_active' => 'boolean',
        'last_run_at' => 'datetime',
        'next_run_at' => 'datetime',
        'run_count' => 'integer'
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(SavedReport::class, 'report_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
