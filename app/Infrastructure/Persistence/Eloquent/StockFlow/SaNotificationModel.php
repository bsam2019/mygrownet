<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;

class SaNotificationModel extends Model
{
    protected $table = 'sa_notifications';
    protected $fillable = ['sa_company_id', 'sa_user_id', 'type', 'title', 'message', 'action_url', 'action_text', 'data', 'priority', 'read_at'];
    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    public function scopeUnread($q) { $q->whereNull('read_at'); }
    public function scopeForCompany($q, $id) { $q->where('sa_company_id', $id); }
    public function scopeForUser($q, $id) { $q->where('sa_user_id', $id); }
    public function scopeOfType($q, $type) { $q->where('type', $type); }
}
