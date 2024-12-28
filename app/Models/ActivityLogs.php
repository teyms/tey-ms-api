<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLogs extends Model
{
    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'method',
        'path',
        'controller',
        'action',
        'request_data',
        'response_data',
        'response_status',
        'error_message'
    ];

    protected $casts = [
        'request_data' => 'array',
        'response_data' => 'array'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Helper method to check if the activity was by a guest
    public function isGuest()
    {
        return is_null($this->user_id);
    }

    // Scope for guest activities
    public function scopeGuests($query)
    {
        return $query->whereNull('user_id');
    }

    // Scope for authenticated user activities
    public function scopeAuthenticated($query)
    {
        return $query->whereNotNull('user_id');
    }

    // Scope for specific IP address
    public function scopeFromIp($query, $ip)
    {
        return $query->where('ip_address', $ip);
    }
}
