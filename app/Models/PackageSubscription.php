<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PackageSubscription extends Model
{
    protected $fillable = ["user_id", "package_id", "status", "starts_at", "expires_at"];
    protected $casts = ["starts_at" => "datetime", "expires_at" => "datetime"];
}