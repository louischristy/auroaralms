<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhishingTemplate extends Model
{
    protected $fillable = [
        'name',
        'scenario_type',
        'subject',
        'sender_name',
        'sender_email',
        'body_html',
        'landing_page_html',
        'difficulty',
        'is_system',
    ];

    protected $casts = [
        'is_system' => 'boolean',
    ];

    public function campaigns()
    {
        return $this->hasMany(PhishingCampaign::class, 'template_id');
    }
}
