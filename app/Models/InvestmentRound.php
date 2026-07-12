<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvestmentRound extends Model
{
    protected $fillable = ["name", "target_amount", "raised_amount", "status", "starts_at", "ends_at"];
}