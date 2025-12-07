<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class UserExpenseCategoryModel extends Model
{
    protected $table = 'user_expense_categories';

    protected $fillable = [
        'user_id',
        'category_name',
        'category_type',
        'estimated_monthly_amount',
        'is_fixed',
        'is_active',
        'display_order',
    ];

    protected $casts = [
        'estimated_monthly_amount' => 'decimal:2',
        'is_fixed' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('category_type', $type);
    }
}
