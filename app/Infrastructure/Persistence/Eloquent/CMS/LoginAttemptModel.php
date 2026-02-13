<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LoginAttemptModel extends Model
{
    protected $table = 'cms_login_attempts';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'email',
        'ip_address',
        'user_agent',
        'successful',
        'failure_reason',
        'attempted_at',
    ];

    protected $casts = [
        'successful' => 'boolean',
        'attempted_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'user_id');
    }

    public function scopeRecent($query, int $minutes = 30)
    {
        return $query->where('attempted_at', '>=', now()->subMinutes($minutes));
    }

    public function scopeFailed($query)
    {
        return $query->where('successful', false);
    }

    public function scopeSuccessful($query)
    {
        return $query->where('successful', true);
    }
}
