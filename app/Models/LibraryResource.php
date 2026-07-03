<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LibraryResource extends Model
{
    use HasFactory;

    protected $fillable = array (
  0 => 'title',
  1 => 'description',
  2 => 'type',
  3 => 'url',
  4 => 'category',
  5 => 'is_published',
);

    protected $casts = array (
  'is_published' => 'boolean',
);
}
