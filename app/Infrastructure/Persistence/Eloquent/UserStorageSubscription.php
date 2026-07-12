<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class UserStorageSubscription extends Model
{
    protected $table = "user_storage_subscriptions";
    protected $fillable = ["user_id", "plan_id", "status", "expires_at"];
}