<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractRenewalModel extends Model
{
    protected $table = 'cms_contract_renewals';

    protected $fillable = [
        'contract_id', 'renewed_contract_id', 'renewal_date', 'status', 'notes', 'created_by',
    ];

    protected $casts = [
        'renewal_date' => 'date',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ContractModel::class, 'contract_id');
    }

    public function renewedContract(): BelongsTo
    {
        return $this->belongsTo(ContractModel::class, 'renewed_contract_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(CmsUserModel::class, 'created_by');
    }
}
