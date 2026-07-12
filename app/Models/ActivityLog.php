<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = array (
  0 => 'user_id',
  1 => 'type',
  2 => 'description',
  3 => 'metadata',
);

    protected $casts = array (
  'metadata' => 'array',
);
}
