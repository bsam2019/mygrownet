<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaMessageModel extends Model
{
    protected $table = 'sa_messages';
    protected $fillable = [
        'sa_company_id', 'sender_id', 'recipient_id',
        'subject', 'body', 'is_read', 'read_at', 'parent_id',
    ];
    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(SaUserModel::class, 'sender_id');
    }

    public function recipient(): BelongsTo
    {
        return $this->belongsTo(SaUserModel::class, 'recipient_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(SaCompanyModel::class, 'sa_company_id');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function scopeForRecipient($query, int $userId)
    {
        return $query->where('recipient_id', $userId);
    }

    public function scopeForSender($query, int $userId)
    {
        return $query->where('sender_id', $userId);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('sa_company_id', $companyId);
    }
}
