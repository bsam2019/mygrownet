<?php

namespace App\Infrastructure\Persistence\Eloquent\Investor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvestorDocumentAccessModel extends Model
{
    protected $table = 'investor_document_access';

    public $timestamps = false;

    protected $fillable = [
        'investor_account_id',
        'investor_document_id',
        'accessed_at',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'accessed_at' => 'datetime',
    ];

    public function investorAccount(): BelongsTo
    {
        return $this->belongsTo(InvestorAccountModel::class, 'investor_account_id');
    }

    public function document(): BelongsTo
    {
        return $this->belongsTo(InvestorDocumentModel::class, 'investor_document_id');
    }
}