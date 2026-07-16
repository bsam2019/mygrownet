<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SaSaleReturnModel extends Model
{
    protected $table = 'sa_sale_returns';
    protected $fillable = ['sa_company_id', 'sa_sale_id', 'return_number', 'return_date', 'reason', 'total_refund', 'refund_method', 'notes', 'created_by'];
    protected $casts = ['return_date' => 'date', 'total_refund' => 'decimal:2'];

    public function company(): BelongsTo { return $this->belongsTo(SaCompanyModel::class, 'sa_company_id'); }
    public function sale(): BelongsTo { return $this->belongsTo(SaSaleModel::class, 'sa_sale_id'); }
    public function items(): HasMany { return $this->hasMany(SaSaleReturnItemModel::class, 'sa_sale_return_id'); }
    public function creator(): BelongsTo { return $this->belongsTo(SaUserModel::class, 'created_by'); }
}
