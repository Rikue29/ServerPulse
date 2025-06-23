<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    protected $fillable = [
        'threshold_id',
        'server_id',
        'metric_value',
        'status',
        'alert_type',
        'alert_message',
        'alert_time',
        'resolved_at', 
    ];

}
