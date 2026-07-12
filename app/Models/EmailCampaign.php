<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailCampaign extends Model
{
    use HasFactory;

    protected $fillable = array (
  0 => 'name',
  1 => 'subject',
  2 => 'content',
  3 => 'status',
  4 => 'sent_at',
  5 => 'recipients_count',
);

    protected $casts = array (
  'sent_at' => 'datetime',
  'recipients_count' => 'integer',
);
}
