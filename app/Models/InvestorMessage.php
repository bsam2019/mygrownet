<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestorMessage extends Model
{
    protected $fillable = ["investor_account_id", "sender_id", "subject", "content", "is_read"];
}