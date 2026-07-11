<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaBinModel extends Model
{
    protected $table = 'sa_bins';
    protected $fillable = ['sa_company_id', 'sa_department_id', 'name', 'label', 'description', 'sort_order'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function department(): BelongsTo { return $this->belongsTo(SaDepartmentModel::class, 'sa_department_id'); }
    public function items(): HasMany { return $this->hasMany(SaItemModel::class, 'sa_bin_id'); }
}
