<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortUrl extends Model
{
    use HasFactory;

    protected $table = 'short_url';

    protected $fillable = [
        'user_id',
        'guest_identifier',
        'url',
        'original_url',
        'title',
        'description',  
        'ip_address',
        'click_count',
        'expires_at',
        'active',
    ];
}
