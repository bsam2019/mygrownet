<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    protected $table = 'sa_departments';

    protected $fillable = [
        'sa_company_id', 'name', 'slug', 'description', 'sort_order',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'sa_company_id');
    }

    public function bins(): HasMany
    {
        return $this->hasMany(Bin::class, 'sa_department_id');
    }
}
