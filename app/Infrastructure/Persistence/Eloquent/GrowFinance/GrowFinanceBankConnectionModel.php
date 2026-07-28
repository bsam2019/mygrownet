<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowFinance;

use Illuminate\Database\Eloquent\Model;

class GrowFinanceBankConnectionModel extends Model
{
    protected $table = 'growfinance_bank_connections';

    protected $fillable = [
        'business_id',
        'bank_name',
        'account_name',
        'account_number',
        'connection_type',
        'status',
        'last_sync_at',
        'credentials',
    ];

    protected $casts = [
        'business_id' => 'integer',
        'credentials' => 'encrypted',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
