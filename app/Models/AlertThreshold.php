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

    public function server()
    {
        return $this->belongsTo(Server::class);
    }

    public function alerts()
    {
        return $this->hasMany(Alert::class, 'threshold_id');
    }
}
