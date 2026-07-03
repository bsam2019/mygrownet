<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = array (
  0 => 'name',
  1 => 'subject',
  2 => 'content',
  3 => 'variables',
);

    protected $casts = NULL;
}
