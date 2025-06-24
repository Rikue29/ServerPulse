<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Alert extends Model
{
    use HasFactory;

    protected $fillable = [
        'threshold_id',
        'server_id',
        'metric_value',
        'status',
        'alert_type',
        'alert_message',
        'alert_time',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'alert_time' => 'datetime',
        'resolved_at' => 'datetime',
        'metric_value' => 'decimal:2',
    ];

    // Relationships
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function threshold(): BelongsTo
    {
        return $this->belongsTo(AlertThreshold::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    // Scopes
    public function scopeUnresolved($query)
    {
        return $query->where('status', 'triggered');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeRecent($query, $hours = 24)
    {
        return $query->where('alert_time', '>=', Carbon::now()->subHours($hours));
    }

    // Accessors
    public function getIsResolvedAttribute(): bool
    {
        return $this->status === 'resolved';
    }

    public function getSeverityAttribute(): string
    {
        if ($this->metric_value >= 90) return 'critical';
        if ($this->metric_value >= 80) return 'high';
        if ($this->metric_value >= 70) return 'medium';
        return 'low';
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'bg-red-100 text-red-800',
            'high' => 'bg-orange-100 text-orange-800',
            'medium' => 'bg-yellow-100 text-yellow-800',
            'low' => 'bg-blue-100 text-blue-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }
}
