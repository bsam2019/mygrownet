<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaPhysicalCountModel extends Model
{
    protected $table = 'sa_physical_counts';
    protected $fillable = ['sa_company_id', 'title', 'count_date', 'status', 'counted_by', 'verified_by', 'notes'];
    protected $casts = ['count_date' => 'date'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function items(): HasMany { return $this->hasMany(SaCountItemModel::class, 'sa_physical_count_id'); }
    public function counter(): BelongsTo { return $this->belongsTo(\App\Models\User::class, 'counted_by'); }
}
