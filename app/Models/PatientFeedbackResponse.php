<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientFeedbackResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'survey_question_id',
        'score',
    ];

    protected $casts = [
        'score' => 'integer',
    ];

    public function survey(): BelongsTo
    {
        return $this->belongsTo(PatientFeedbackSurvey::class, 'survey_id');
    }

    public function surveyQuestion(): BelongsTo
    {
        return $this->belongsTo(PatientFeedbackSurveyQuestion::class, 'survey_question_id');
    }
}


