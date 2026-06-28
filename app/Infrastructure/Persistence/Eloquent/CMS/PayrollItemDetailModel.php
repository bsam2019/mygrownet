<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollItemDetailModel extends Model
{
    protected $table = 'cms_payroll_item_details';

    protected $fillable = [
        'payroll_item_id',
        'item_type',
        'item_name',
        'amount',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payrollItem(): BelongsTo
    {
        return $this->belongsTo(PayrollItemModel::class, 'payroll_item_id');
    }
}
