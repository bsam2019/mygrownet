<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaDepartmentModel extends Model
{
    protected $table = 'sa_departments';
    protected $fillable = ['sa_company_id', 'name', 'slug', 'description', 'sort_order'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function bins(): HasMany { return $this->hasMany(SaBinModel::class, 'sa_department_id'); }
}
