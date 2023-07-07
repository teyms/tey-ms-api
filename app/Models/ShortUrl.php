<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShortUrl extends Model
{
    use HasFactory;

    protected $table = 'short_url';

    protected $fillable = [
        'url',
        'ori_url',
        'ip_address',
        'used_count',
        'expired_date',
    ];
}
