<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CareerPathEnrollment extends Model
{
    protected $fillable = [
        'user_id',
        'career_path_id',
        'current_stage_id',
        'enrolled_at',
        'completed_at',
    ];

    protected $casts = [
        'enrolled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function careerPath(): BelongsTo
    {
        return $this->belongsTo(CareerPath::class);
    }

    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(CareerPathStage::class, 'current_stage_id');
    }
}
