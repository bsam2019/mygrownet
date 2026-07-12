<?php

namespace App\Infrastructure\GrowBuilder\Models;

use Illuminate\Database\Eloquent\Model;

class SiteBlogPost extends Model
{
    protected $table = "growbuilder_site_blog_posts";
    protected $fillable = ["site_id", "title", "content", "status"];
}