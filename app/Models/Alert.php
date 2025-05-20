<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'threshold_id',
        'server_id',
        'metric_value',
        'status',
        'alert_type',
        'alert_time',
        'resolved_at'
    ];

    protected $casts = [
        'alert_time' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function threshold()
    {
        return $this->belongsTo(AlertThreshold::class, 'threshold_id');
    }
}
