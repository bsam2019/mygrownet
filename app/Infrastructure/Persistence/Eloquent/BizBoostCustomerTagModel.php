<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BizBoostCustomerTagModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_customer_tags';

    protected $fillable = ['business_id', 'name', 'color'];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BizBoostBusinessModel::class, 'business_id');
    }
}
