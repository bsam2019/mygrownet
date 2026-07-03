<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserProductSubscription extends Model
{
    protected $fillable = ["user_id", "product_id", "status", "starts_at", "expires_at"];
}