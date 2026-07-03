<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VentureProject extends Model
{
    use HasFactory;

    protected $fillable = array (
  0 => 'name',
  1 => 'description',
  2 => 'target_amount',
  3 => 'raised_amount',
  4 => 'status',
  5 => 'deadline',
);

    protected $casts = array (
  'target_amount' => 'decimal:2',
  'raised_amount' => 'decimal:2',
  'deadline' => 'date',
);
}
