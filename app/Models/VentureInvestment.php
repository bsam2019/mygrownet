<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentureInvestment extends Model
{
    use HasFactory;

    protected $fillable = array (
  0 => 'user_id',
  1 => 'venture_id',
  2 => 'amount',
  3 => 'status',
  4 => 'invested_at',
);

    protected $casts = array (
  'amount' => 'decimal:2',
  'invested_at' => 'datetime',
);
}
