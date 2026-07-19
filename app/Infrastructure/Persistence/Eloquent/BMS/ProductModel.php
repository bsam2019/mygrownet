<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    protected $table = "cms_products";
    protected $fillable = ["name", "description", "price", "status"];
}
