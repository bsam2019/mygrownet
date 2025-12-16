<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LifePlusExpenseCategoryModel extends Model
{
    protected $table = 'lifeplus_expense_categories';

    protected $fillable = [
        'user_id',
        'name',
        'icon',
        'color',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(LifePlusExpenseModel::class, 'category_id');
    }
}
