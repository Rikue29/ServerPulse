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
        'system_uptime',
        'network_rx',
        'network_tx',
        'disk_io_read',
        'disk_io_write',
        'network_speed'
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
        'network_rx' => 'integer',
        'network_tx' => 'integer',
        'disk_io_read' => 'integer',
        'disk_io_write' => 'integer',
        'network_speed' => 'integer'
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
     * Accessor for the formatted system uptime.
     * Parses the `system_uptime` string and returns it in a consistent, readable format.
     *
     * @return string
     */
    public function getFormattedSystemUptimeAttribute(): string
    {
        if (empty($this->system_uptime)) {
            return 'N/A';
        }

        // The format is expected to be like "Xh Ym Zs"
        preg_match('/(\d+)h\s*(\d+)m\s*(\d+)s/', $this->system_uptime, $matches);

        if (count($matches) === 4) {
            $hours = (int) $matches[1];
            $minutes = (int) $matches[2];

            $days = floor($hours / 24);
            $remainingHours = $hours % 24;

            $formatted = '';
            if ($days > 0) {
                $formatted .= $days . 'd ';
            }
            if ($remainingHours > 0) {
                $formatted .= $remainingHours . 'h ';
            }
            if ($minutes > 0) {
                $formatted .= $minutes . 'm';
            }
            
            // If uptime is less than a minute, show seconds.
            if(empty(trim($formatted))) {
                return ((int) $matches[3]) . 's';
            }

            return trim($formatted);
        }

        return $this->system_uptime; // Fallback to original string if parsing fails
    }

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
