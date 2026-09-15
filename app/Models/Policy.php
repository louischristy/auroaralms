<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Policy extends Model
{
    use BelongsToTenant, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'title',
        'content',
        'version',
        'requires_acknowledgment',
        'acknowledgment_deadline_days',
        'is_published',
        'published_at',
        'created_by',
    ];

    protected $casts = [
        'requires_acknowledgment' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function acknowledgments()
    {
        return $this->hasMany(PolicyAcknowledgment::class);
    }

    public function acknowledgedByUser(User $user): bool
    {
        return $this->acknowledgments()
            ->where('user_id', $user->id)
            ->where('version', $this->version)
            ->exists();
    }
}
