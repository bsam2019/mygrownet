<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailUnsubscribeModel extends Model
{
    protected $table = 'cms_email_unsubscribes';

    public $timestamps = false;

    protected $fillable = [
        'company_id',
        'email_address',
        'unsubscribe_type',
        'reason',
        'unsubscribed_at',
    ];

    protected $casts = [
        'unsubscribed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function scopeForEmail($query, string $email)
    {
        return $query->where('email_address', $email);
    }
}
