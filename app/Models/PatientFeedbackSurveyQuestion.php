<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientFeedbackSurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'question_id',
        'question_text',
        'cqc_domain',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(PatientFeedbackSurvey::class, 'survey_id');
    }

    public function masterQuestion(): BelongsTo
    {
        return $this->belongsTo(PatientFeedbackQuestion::class, 'question_id');
    }
}


