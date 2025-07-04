<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;    protected $fillable = [
        'server_id',
        'level',
        'source',
        'log_level',
        'message',
        'context',
    ];

    protected $casts = [
        'context' => 'array',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
} 