<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FaqsSection extends Model
{
    use HasFactory;

    protected $fillable = [
        'section_title',
        'section_subtitle',
        'section_description',
        'background_image',
        'background_color',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
