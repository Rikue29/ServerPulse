<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory;
    
    protected $table = 'logs';
    
    protected $fillable = [
        'server_id',
        'source',
        'log_level',
        'message',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
} 