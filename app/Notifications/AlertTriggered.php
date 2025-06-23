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
        return ['mail']; // Only sending via email for now
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("🚨 Alert: {$this->alert->alert_type} triggered on Server #{$this->alert->server_id}")
            ->line("Metric value: {$this->alert->metric_value}")
            ->line("Message: {$this->alert->alert_message}")
            ->line("Triggered at: {$this->alert->alert_time}")
            ->line("Please take action as needed.");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'alert_id'      => $this->alert->id,
            'server_id'     => $this->alert->server_id,
            'metric_value'  => $this->alert->metric_value,
            'alert_type'    => $this->alert->alert_type,
        ];
    }
}
