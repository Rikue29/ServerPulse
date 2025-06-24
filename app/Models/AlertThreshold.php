<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AlertThreshold extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'metric_type',
        'threshold_value',
        'notification_channel',
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'threshold_value' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /**
     * Get the server that owns the alert threshold.
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get the user who created this threshold.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get all alerts for this threshold.
     */
    public function alerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'threshold_id');
    }

    /**
     * Get only unresolved alerts for this threshold.
     */
    public function unresolvedAlerts(): HasMany
    {
        return $this->hasMany(Alert::class, 'threshold_id')->where('status', 'triggered');
    }

    /**
     * Check if this threshold should trigger an alert for the given metric value.
     */
    public function shouldTrigger(float $metricValue): bool
    {
        return $this->is_active && $metricValue >= $this->threshold_value;
    }

    /**
     * Get the notification emails based on the channel.
     */
    public function getNotificationEmails(): array
    {
        return match($this->notification_channel) {
            'infra' => [
                config('mail.admin_email', 'admin@serverpulse.com'),
                'infra@serverpulse.com'
            ],
            'dev' => [
                config('mail.admin_email', 'admin@serverpulse.com'),
                'dev@serverpulse.com'
            ],
            'management' => [
                config('mail.admin_email', 'admin@serverpulse.com'),
                'management@serverpulse.com'
            ],
            default => [config('mail.admin_email', 'admin@serverpulse.com')]
        };
    }
}
