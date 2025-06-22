<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Server extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $fillable = [
        'name',
        'ip_address',
        'environment',
        'monitoring_type',
        'cpu_usage',
        'ram_usage',
        'disk_usage',
        'response_time',
        'location',
        'created_by',
        'status',
        'last_checked_at',
        'last_down_at',
        'running_since',
        'total_uptime_seconds',
        'total_downtime_seconds',
        'ssh_user',
        'ssh_password',
        'ssh_key',
        'ssh_port',
        'system_uptime'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cpu_usage' => 'float',
        'ram_usage' => 'float',
        'disk_usage' => 'float',
        'response_time' => 'float',
        'last_checked_at' => 'datetime',
        'last_down_at' => 'datetime',
        'running_since' => 'datetime',
        'total_uptime_seconds' => 'integer',
        'total_downtime_seconds' => 'integer'
    ];

    /**
     * Get the user that created the server.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the logs for the server.
     */
    public function logs(): HasMany
    {
        return $this->hasMany(Log::class);
    }

    /**
     * Get the alert thresholds for the server.
     */
    public function alertThresholds(): HasMany
    {
        return $this->hasMany(AlertThreshold::class);
    }

    protected $hidden = [
        'ssh_password',
        'ssh_key'
    ];

    /**
     * Accessor for the current downtime, formatted as a human-readable string.
     *
     * @return string|null
     */
    public function getCurrentDowntimeFormattedAttribute(): ?string
    {
        if ($this->status === 'offline' && $this->last_down_at) {
            return \Carbon\CarbonInterval::seconds($this->last_down_at->diffInSeconds(now()))
                ->cascade()
                ->forHumans(['short' => true]);
        }
        return null;
    }
}
