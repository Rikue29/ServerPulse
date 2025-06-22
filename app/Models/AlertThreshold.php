<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertThreshold extends Model
{    protected $fillable = [
        'server_id',
        'metric_type',
        'threshold_value',
        'notification_channel',
        'created_by',
    ];

    /**
     * Get the server that owns the alert threshold.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }
}