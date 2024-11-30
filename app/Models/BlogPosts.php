<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogPosts extends Model
{
    use HasFactory;

    const STATUS = [
        'DRAFT' => 0,
        'PUBLISHED' => 1
    ];

    protected $table = 'blog_posts';

    protected $fillable = [
        'slug',
        'title',
        'content',
        'status',
    ];
}
