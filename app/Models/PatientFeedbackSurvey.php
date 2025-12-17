<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientFeedbackSurvey extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'doctor_id',
        'patient_id',
        'token_hash',
        'token_encrypted',
        'is_anonymous',
        'sent_at',
        'submitted_at',
        'additional_comments',
        'meta',
    ];

    protected $casts = [
        'is_anonymous' => 'boolean',
        'sent_at' => 'datetime',
        'submitted_at' => 'datetime',
        'meta' => 'array',
    ];

    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function questions(): HasMany
    {
        return $this->hasMany(PatientFeedbackSurveyQuestion::class, 'survey_id')->orderBy('sort_order');
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PatientFeedbackResponse::class, 'survey_id');
    }

    public function isSubmitted(): bool
    {
        return !is_null($this->submitted_at);
    }
}


