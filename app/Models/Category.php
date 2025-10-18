<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $table = 'investment_categories';

    protected $fillable = [
        'name',
        'description',
        'interest_rate',
        'min_investment',
        'max_investment',
        'lock_in_period'
    ];

    protected $casts = [
        'interest_rate' => 'float',
        'min_investment' => 'decimal:2',
        'max_investment' => 'decimal:2',
    ];

    public function investments(): HasMany
    {
        return $this->hasMany(Investment::class);
    }
}
