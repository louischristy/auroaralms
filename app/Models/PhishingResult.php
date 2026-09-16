<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PhishingResult extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'campaign_id',
        'user_id',
        'tenant_id',
        'email_sent_at',
        'email_opened_at',
        'link_clicked_at',
        'data_submitted_at',
        'reported_at',
        'status',
    ];

    protected $casts = [
        'email_sent_at' => 'datetime',
        'email_opened_at' => 'datetime',
        'link_clicked_at' => 'datetime',
        'data_submitted_at' => 'datetime',
        'reported_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(PhishingCampaign::class, 'campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
