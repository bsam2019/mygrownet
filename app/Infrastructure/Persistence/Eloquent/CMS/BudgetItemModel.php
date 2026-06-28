<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BudgetItemModel extends Model
{
    protected $table = 'cms_budget_items';

    protected $fillable = [
        'budget_id',
        'category',
        'item_type',
        'budgeted_amount',
        'notes',
    ];

    protected $casts = [
        'budgeted_amount' => 'decimal:2',
    ];

    public function budget(): BelongsTo
    {
        return $this->belongsTo(BudgetModel::class, 'budget_id');
    }
}
