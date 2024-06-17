<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TngFileConvert extends Model
{
    use HasFactory;

    protected $table = 'tng_file_convert';

    protected $fillable = [
        'name',
        'content',
        'size',
        'type',
        'converted_name',
        'converted_content',
        'converted_size',
        'converted_type',
        'ip_address',
    ];
}
