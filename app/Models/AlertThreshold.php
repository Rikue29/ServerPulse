<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AlertThreshold extends Model
{    protected $fillable = [
        'server_id',
        'metric_type',
        'threshold_value',
        'notification_channel',
        'created_by',
    ];
}