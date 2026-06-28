<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContractSignatureModel extends Model
{
    protected $table = 'cms_contract_signatures';

    protected $fillable = [
        'contract_id', 'party', 'signer_name', 'signer_email',
        'signature_data', 'ip_address', 'user_agent',
        'signing_token', 'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(ContractModel::class, 'contract_id');
    }
}
