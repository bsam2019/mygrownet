<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockCountModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_stock_counts';

    protected $fillable = [
        'company_id',
        'count_number',
        'count_date',
        'count_type',
        'location_id',
        'counted_by',
        'verified_by',
        'verified_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'count_date' => 'date',
        'verified_date' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(StockLocationModel::class, 'location_id');
    }

    public function countedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'counted_by');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockCountItemModel::class, 'count_id');
    }
}
