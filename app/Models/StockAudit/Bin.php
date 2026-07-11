<?php

namespace App\Models\StockAudit;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bin extends Model
{
    protected $table = 'sa_bins';

    protected $fillable = [
        'sa_company_id', 'sa_department_id', 'name', 'label',
        'description', 'sort_order',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'sa_company_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'sa_department_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'sa_bin_id');
    }
}
