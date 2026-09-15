<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;

class PolicyAcknowledgment extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'policy_id',
        'user_id',
        'version',
        'acknowledged_at',
        'ip_address',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
    ];

    public function policy()
    {
        return $this->belongsTo(Policy::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
