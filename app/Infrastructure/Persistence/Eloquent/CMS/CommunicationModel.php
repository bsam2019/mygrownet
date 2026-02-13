<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CommunicationModel extends Model
{
    protected $table = 'cms_communications';

    protected $fillable = [
        'company_id', 'communicable_type', 'communicable_id',
        'type', 'direction', 'subject', 'content', 'communicated_at',
        'duration_minutes', 'attachments', 'created_by',
    ];

    protected $casts = [
        'communicated_at' => 'datetime',
        'attachments' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function communicable(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }
}
