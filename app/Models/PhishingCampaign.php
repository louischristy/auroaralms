<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PhishingCampaign extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'template_id',
        'target_type',
        'target_department_ids',
        'scheduled_at',
        'sent_at',
        'status',
        'custom_click_rate',
        'custom_report_rate',
        'training_aware',
        'created_by',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
        'target_department_ids' => 'array',
        'training_aware' => 'boolean',
    ];

    public function template()
    {
        return $this->belongsTo(PhishingTemplate::class, 'template_id');
    }

    public function results()
    {
        return $this->hasMany(PhishingResult::class, 'campaign_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
