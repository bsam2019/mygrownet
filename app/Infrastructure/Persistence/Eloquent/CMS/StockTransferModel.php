<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class StockTransferModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_stock_transfers';

    protected $fillable = [
        'company_id',
        'transfer_number',
        'from_location_id',
        'to_location_id',
        'transfer_date',
        'requested_by',
        'approved_by',
        'approved_date',
        'received_by',
        'received_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'approved_date' => 'datetime',
        'received_date' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function fromLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocationModel::class, 'from_location_id');
    }

    public function toLocation(): BelongsTo
    {
        return $this->belongsTo(StockLocationModel::class, 'to_location_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(StockTransferItemModel::class, 'transfer_id');
    }
}
