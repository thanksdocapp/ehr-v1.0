<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'filename',
        'file_path',
        'export_type',
        'report_type',
        'report_id',
        'exported_by',
        'file_size',
        'status',
        'error_message',
        'completed_at'
    ];

    protected $casts = [
        'file_size' => 'integer',
        'completed_at' => 'datetime'
    ];

    public function report(): BelongsTo
    {
        return $this->belongsTo(SavedReport::class, 'report_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'exported_by');
    }
}
