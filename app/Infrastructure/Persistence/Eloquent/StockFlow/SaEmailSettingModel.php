<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaEmailSettingModel extends Model
{
    protected $table = 'sa_email_settings';
    protected $fillable = [
        'sa_company_id', 'provider', 'smtp_host', 'smtp_port', 'smtp_username',
        'smtp_password', 'smtp_encryption', 'from_address', 'from_name', 'verified',
    ];
    protected $casts = [
        'smtp_port' => 'integer',
        'verified' => 'boolean',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
}
