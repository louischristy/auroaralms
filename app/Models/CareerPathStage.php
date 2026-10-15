<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerPathStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'career_path_id',
        'title',
        'description',
        'stage_order',
        'required_courses',
        'required_certifications',
        'estimated_duration_days',
    ];

    protected $casts = [
        'required_courses' => 'array',
        'required_certifications' => 'array',
    ];

    public function careerPath(): BelongsTo
    {
        return $this->belongsTo(CareerPath::class);
    }
}
