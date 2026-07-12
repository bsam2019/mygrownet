<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StarterKitPurchase extends Model
{
    use HasFactory;

    protected $fillable = array (
  0 => 'user_id',
  1 => 'starter_kit_id',
  2 => 'amount',
  3 => 'status',
  4 => 'purchased_at',
);

    protected $casts = array (
  'amount' => 'decimal:2',
  'purchased_at' => 'datetime',
);
}
