<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaActivityLogModel extends Model
{
    protected $table = 'sa_activity_log';

    protected $fillable = [
        'sa_company_id', 'actor_user_id', 'context', 'event_name',
        'subject_type', 'subject_id', 'description', 'payload', 'occurred_at',
    ];

    protected $casts = [
        'payload' => 'json',
        'occurred_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'actor_user_id');
    }
}
