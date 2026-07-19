<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;

class ProductModel extends Model
{
    protected $table = "cms_products";
    protected $fillable = ["name", "description", "price", "status"];
}