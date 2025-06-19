<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceLog extends Model
{
    use HasFactory;

    protected $table = 'performance_logs';

    protected $fillable = [
        'server_id',
        'cpu_usage',
        'ram_usage',
        'disk_usage',
        'network_rx',
        'network_tx',
        'disk_io_read',
        'disk_io_write',
    ];

    protected $casts = [
        'cpu_usage' => 'float',
        'ram_usage' => 'float',
        'disk_usage' => 'float',
        'network_rx' => 'integer',
        'network_tx' => 'integer',
        'disk_io_read' => 'integer',
        'disk_io_write' => 'integer',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
} 