<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PerformanceLog extends Model
{
    use HasFactory;

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
}
