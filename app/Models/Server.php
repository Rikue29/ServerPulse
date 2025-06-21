<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'system_uptime',
        'agent_enabled',
        'agent_id',
        'agent_token',
        'agent_last_heartbeat',
        'agent_status',
        'agent_version',
        'agent_config',
        'last_metrics'
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
        'total_downtime_seconds' => 'integer',
        'agent_enabled' => 'boolean',
        'agent_last_heartbeat' => 'datetime',
        'agent_config' => 'array',
        'last_metrics' => 'array'
    ];

    /**
     * Get the user that created the server.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    protected $hidden = [
        'ssh_password',
        'ssh_key',
        'agent_token'
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
