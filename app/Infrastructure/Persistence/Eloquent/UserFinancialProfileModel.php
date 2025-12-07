<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;

class UserFinancialProfileModel extends Model
{
    protected $table = 'user_financial_profiles';

    protected $fillable = [
        'user_id',
        'setup_completed',
        'setup_completed_at',
        'budget_method',
        'budget_period',
        'currency',
        'alert_preferences',
    ];

    protected $casts = [
        'setup_completed' => 'boolean',
        'setup_completed_at' => 'datetime',
        'alert_preferences' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function incomeSources(): HasMany
    {
        return $this->hasMany(IncomeSourceModel::class, 'user_id', 'user_id');
    }

    public function expenseCategories(): HasMany
    {
        return $this->hasMany(UserExpenseCategoryModel::class, 'user_id', 'user_id');
    }

    public function savingsGoals(): HasMany
    {
        return $this->hasMany(SavingsGoalModel::class, 'user_id', 'user_id');
    }
}
