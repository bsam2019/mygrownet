<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaCompanyUserModel extends Model
{
    protected $table = 'sa_company_users';
    protected $guarded = ['id'];
    protected $casts = [
        'invited_at' => 'datetime',
        'joined_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(SaCompanyModel::class, 'sa_company_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(SaCompanyRoleModel::class, 'sa_company_role_id');
    }
}