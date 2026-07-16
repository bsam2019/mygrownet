<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaBackupConfigModel extends Model
{
    protected $table = 'sa_backup_configs';
    protected $fillable = ['sa_company_id', 'email', 'enabled', 'frequency', 'include_files', 'last_backup_at'];
    protected $casts = [
        'enabled' => 'boolean',
        'last_backup_at' => 'datetime',
    ];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
}
