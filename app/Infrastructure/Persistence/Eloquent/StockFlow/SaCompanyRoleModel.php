<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaCompanyRoleModel extends Model
{
    protected $table = 'sa_company_roles';
    protected $guarded = ['id'];
    protected $casts = [
        'permissions' => 'array',
        'is_system' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(SaCompanyModel::class, 'sa_company_id');
    }
}