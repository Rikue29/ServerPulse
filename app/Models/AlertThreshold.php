<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlertThreshold extends Model
{
   use HasFactory;

    protected $fillable = [
        'server_id',
        'metric_value',
        'alert_type',
    ];
}
