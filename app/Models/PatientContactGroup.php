<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PatientContactGroup extends Model
{
    protected $fillable = [
        'label',
        'description',
    ];

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'contact_group_id');
    }
}
