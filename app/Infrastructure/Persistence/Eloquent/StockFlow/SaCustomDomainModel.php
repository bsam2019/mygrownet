<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaCustomDomainModel extends Model
{
    protected $table = 'sa_custom_domains';
    protected $fillable = ['sa_company_id', 'domain', 'status', 'verified_at'];
    protected $casts = [
        'verified_at' => 'datetime',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
}
