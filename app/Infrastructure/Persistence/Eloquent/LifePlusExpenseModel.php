<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LifePlusExpenseModel extends Model
{
    protected $table = 'lifeplus_expenses';

    protected $fillable = [
        'user_id',
        'category_id',
        'amount',
        'description',
        'expense_date',
        'is_synced',
        'local_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'is_synced' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(LifePlusExpenseCategoryModel::class, 'category_id');
    }
}
