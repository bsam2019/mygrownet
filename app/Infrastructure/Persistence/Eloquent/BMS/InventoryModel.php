<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;

class InventoryModel extends Model
{
    protected $table = "cms_inventory";
    protected $fillable = ["name", "sku", "quantity", "price"];
}