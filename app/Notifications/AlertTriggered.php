<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use App\Models\Alert;

class AlertTriggered extends Notification
{
    use Queueable;

    protected $alert;

    public function __construct(Alert $alert)
    {
        $this->alert = $alert;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $severity = $this->alert->severity;
        $severityEmoji = match($severity) {
            'critical' => '🔴',
            'high' => '🟠',
            'medium' => '🟡',
            'low' => '🔵',
            default => '⚠️'
        };

        $serverName = $this->alert->server->name ?? "Server #{$this->alert->server_id}";
        
        return (new MailMessage)
            ->subject("{$severityEmoji} ALERT [{$severity}]: {$this->alert->threshold->metric_type} threshold exceeded on {$serverName}")
            ->greeting("🚨 Server Alert Triggered")
            ->line("**Server:** {$serverName}")
            ->line("**Alert Type:** {$this->alert->alert_type}")
            ->line("**Metric:** {$this->alert->threshold->metric_type}")
            ->line("**Current Value:** {$this->alert->metric_value}%")
            ->line("**Threshold:** {$this->alert->threshold->threshold_value}%")
            ->line("**Severity:** " . ucfirst($severity))
            ->line("**Message:** {$this->alert->alert_message}")
            ->line("**Time:** {$this->alert->alert_time->format('Y-m-d H:i:s')}")
            ->line("")
            ->line("Please investigate this issue immediately.")
            ->action('View Dashboard', url('/dashboard'))
            ->action('Resolve Alert', url("/alerts/{$this->alert->id}/resolve"))
            ->line("You can resolve this alert by clicking the 'Resolve Alert' button above or through the dashboard.")
            ->salutation("ServerPulse Monitoring System");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'alert_id'      => $this->alert->id,
            'server_id'     => $this->alert->server_id,
            'server_name'   => $this->alert->server->name ?? "Server #{$this->alert->server_id}",
            'metric_type'   => $this->alert->threshold->metric_type,
            'metric_value'  => $this->alert->metric_value,
            'threshold_value' => $this->alert->threshold->threshold_value,
            'alert_type'    => $this->alert->alert_type,
            'severity'      => $this->alert->severity,
            'alert_time'    => $this->alert->alert_time,
        ];
    }
}
