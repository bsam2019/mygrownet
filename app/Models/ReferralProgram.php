<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReferralProgram extends Model
{
    use HasFactory;

    protected $fillable = array (
  0 => 'name',
  1 => 'description',
  2 => 'reward_type',
  3 => 'reward_value',
  4 => 'is_active',
);

    protected $casts = array (
  'reward_value' => 'decimal:2',
  'is_active' => 'boolean',
);
}
