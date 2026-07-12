<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class StorageUsage extends Model
{
    protected $table = "storage_usage";
    protected $fillable = ["user_id", "storage_gb_used"];
}