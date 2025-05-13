<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'timestamp',
        'type',
        'category',
        'server',
        'status',
        'message'
    ];

    protected $casts = [
        'timestamp' => 'datetime'
    ];
} 