<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;

class StoragePlan extends Model
{
    protected $table = "storage_plans";
    protected $fillable = ["name", "storage_gb", "price"];
}