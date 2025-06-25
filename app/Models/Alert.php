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
        $value = $this->metric_value;
        $type = strtolower($this->alert_type);
        
        // Apply specific thresholds based on metric type
        switch ($type) {
            case 'performance':
            case 'cpu':
                if ($value >= 90) return 'critical';
                if ($value >= 75) return 'high';
                if ($value >= 60) return 'medium';
                return 'low';
                
            case 'memory':
                if ($value >= 80) return 'high';
                if ($value >= 65) return 'medium';
                return 'low';
                
            case 'system':
            case 'disk':
                if ($value >= 75) return 'medium';
                if ($value >= 60) return 'low';
                return 'normal';
                
            default:
                if ($value >= 90) return 'critical';
                if ($value >= 75) return 'high';
                if ($value >= 60) return 'medium';
                return 'low';
        }
    }

    public function getSeverityColorAttribute(): string
    {
        return match($this->severity) {
            'critical' => 'bg-red-500 text-white',
            'high' => 'bg-orange-500 text-white',
            'medium' => 'bg-yellow-500 text-black',
            'low' => 'bg-blue-500 text-white',
            'normal' => 'bg-green-500 text-white',
            default => 'bg-gray-500 text-white'
        };
    }

    public function getRowStyleAttribute(): string
    {
        if ($this->status === 'resolved') {
            return 'bg-gray-50 opacity-75';
        }
        
        $value = $this->metric_value;
        
        // Excessive values get prominent styling
        if ($value >= 95) {
            return 'bg-red-50 border-l-4 border-red-500 shadow-md';
        }
        
        if ($this->severity === 'critical') {
            return 'bg-red-25 border-l-2 border-red-400';
        }
        
        if ($this->severity === 'high') {
            return 'bg-orange-25 border-l-2 border-orange-400';
        }
        
        return 'bg-white hover:bg-gray-50';
    }
}
