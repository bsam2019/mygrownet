<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $fillable = array (
  0 => 'name',
  1 => 'type',
  2 => 'status',
  3 => 'starts_at',
  4 => 'ends_at',
  5 => 'budget',
);

    protected $casts = array (
  'starts_at' => 'datetime',
  'ends_at' => 'datetime',
  'budget' => 'decimal:2',
);
}
