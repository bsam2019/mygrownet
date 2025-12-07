<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BizBoostCustomerModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_customers';

    protected $fillable = [
        'business_id',
        'name',
        'phone',
        'email',
        'whatsapp',
        'address',
        'notes',
        'source',
        'birthday',
        'total_spent',
        'total_orders',
        'last_purchase_at',
        'is_active',
    ];

    protected $casts = [
        'birthday' => 'date',
        'total_spent' => 'decimal:2',
        'last_purchase_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            BizBoostCustomerTagModel::class,
            'bizboost_customer_tag_pivot',
            'customer_id',
            'tag_id'
        );
    }

    public function sales(): HasMany
    {
        return $this->hasMany(BizBoostSaleModel::class, 'customer_id');
    }

    public function updatePurchaseStats(): void
    {
        $this->total_spent = $this->sales()->sum('total_amount');
        $this->total_orders = $this->sales()->count();
        $this->last_purchase_at = $this->sales()->latest('sale_date')->value('sale_date');
        $this->save();
    }
}
