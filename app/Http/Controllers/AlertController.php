<?php

namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\AlertThreshold;
use App\Models\Server;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Notifications\AlertTriggered;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class AlertController extends Controller
{
    /**
     * Trigger an alert when threshold is exceeded
     */
    public function trigger(Request $request)
    {
        $validated = $request->validate([
            'threshold_id' => 'required|exists:alert_thresholds,id',
            'server_id'    => 'required|exists:servers,id',
            'metric_value' => 'required|numeric',
        ]);

        Log::info('Alert trigger request received', $validated);

        $threshold = AlertThreshold::with('server')->find($validated['threshold_id']);
        $server = Server::find($validated['server_id']);

        // Check if threshold should trigger
        if (!$threshold->shouldTrigger($validated['metric_value'])) {
            return response()->json(['message' => 'Threshold not exceeded'], 200);
        }

        // Check for existing unresolved alert and limit notifications to once per minute
        $existingAlert = Alert::where('threshold_id', $threshold->id)
            ->where('server_id', $validated['server_id'])
            ->where('status', 'triggered')
            ->first();

        if ($existingAlert) {
            // Calculate time since last notification
            $lastNotificationTime = $existingAlert->last_notification_at ?? $existingAlert->alert_time;
            $minutesSinceLastNotification = $lastNotificationTime ? Carbon::now()->diffInMinutes($lastNotificationTime) : 999;
            
            // Update the alert with new values
            $updateData = [
                'metric_value' => $validated['metric_value'],
                'alert_time' => Carbon::now(),
            ];
            
            // Only send a notification if more than 1 minute has passed
            if ($minutesSinceLastNotification >= 1) {
                Log::info('Similar alert exists, sending notification after cooldown period', [
                    'existing_alert_id' => $existingAlert->id,
                    'minutes_since_last_notification' => $minutesSinceLastNotification
                ]);
                
                // Send email notification and update the notification timestamp
                $this->sendNotifications($existingAlert, $threshold);
                $updateData['last_notification_at'] = Carbon::now();
                
                $message = 'Alert updated and notification sent';
            } else {
                Log::info('Similar alert exists, updating without notification due to cooldown period', [
                    'existing_alert_id' => $existingAlert->id,
                    'minutes_since_last_notification' => $minutesSinceLastNotification
                ]);
                $message = 'Alert updated (no notification - cooldown period)';
            }
            
            $existingAlert->update($updateData);
            return response()->json(['message' => $message, 'alert' => $existingAlert], 200);
        }

        // Create the alert
        $alert = Alert::create([
            'threshold_id'  => $threshold->id,
            'server_id'     => $validated['server_id'],
            'metric_value'  => $validated['metric_value'],
            'status'        => 'triggered',
            'alert_type'    => $this->getAlertType($threshold->metric_type),
            'alert_message' => $this->generateAlertMessage($threshold, $server, $validated['metric_value']),
            'alert_time'    => Carbon::now(),
        ]);

        // Send email notifications
        $this->sendNotifications($alert, $threshold);

        Log::info('Alert created and notifications sent', ['alert_id' => $alert->id]);

        return response()->json(['alert' => $alert->load('server', 'threshold')], 201);
    }

    /**
     * Resolve an alert
     */
    public function resolve($id)
    {
        try {
            $alert = Alert::findOrFail($id);

            if ($alert->status === 'resolved') {
                return response()->json([
                    'success' => false,
                    'message' => 'Alert is already resolved.',
                    'alert' => $alert
                ], 200);
            }

            $alert->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => Auth::id(),
            ]);

            Log::info('Alert resolved', [
                'alert_id' => $alert->id,
                'resolved_by' => Auth::id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Alert resolved successfully.',
                'alert' => $alert
            ], 200);

        } catch (\Exception $e) {
            Log::error('Failed to resolve alert', [
                'alert_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resolve alert: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all unresolved alerts
     */
    public function index()
    {
        return view('alerts');
    }

    /**
     * Get recent alerts for notifications
     */
    public function recent()
    {
        $alerts = Alert::with(['server', 'threshold'])
            ->recent(24) // Last 24 hours
            ->orderBy('alert_time', 'desc')
            ->limit(10)
            ->get();

        return response()->json(['alerts' => $alerts]);
    }

    /**
     * Generate alert message based on threshold and server info
     */
    private function generateAlertMessage(AlertThreshold $threshold, Server $server, float $metricValue): string
    {
        $serverName = $server->name ?? "Server #{$server->id}";
        $metricType = $threshold->metric_type;
        $thresholdValue = $threshold->threshold_value;
        
        return "{$metricType} usage on {$serverName} has exceeded the threshold of {$thresholdValue}%. Current value: {$metricValue}%";
    }

    /**
     * Map metric type to alert type
     */
    private function getAlertType(string $metricType): string
    {
        return match(strtoupper($metricType)) {
            'CPU', 'RAM', 'MEMORY', 'DISK' => 'performance',
            'NETWORK' => 'network',
            'HEARTBEAT', 'STATUS' => 'heartbeat',
            default => 'system'
        };
    }

    /**
     * Send email notifications
     */
    private function sendNotifications(Alert $alert, AlertThreshold $threshold): void
    {
        try {
            // Always include your specific email for testing
            $emails = ['215746@student.upm.edu.my'];
            
            Log::info('Starting direct API email notification process', [
                'alert_id' => $alert->id,
                'emails' => $emails
            ]);
                
            // Direct API call - bypassing Laravel mailer completely
            $apiKey = '88986abb0e180651f5ae5da5782eb0fe-a1dad75f-46d63fad';
            $domain = 'sandbox1903e7c34fd549419d635a5a38e4bf39.mailgun.org';
            $serverName = $alert->server->name ?? "Server #{$alert->server_id}";
            
            foreach ($emails as $email) {
                $ch = curl_init("https://api.mailgun.net/v3/{$domain}/messages");
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_USERPWD, "api:{$apiKey}");
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, [
                    'from' => "ServerPulse Monitoring <postmaster@{$domain}>",
                    'to' => $email,
                    'subject' => "Server Monitor: {$threshold->metric_type} on {$serverName}",
                    'text' => "Server monitoring notification\n\nServer: {$serverName}\nMetric: {$threshold->metric_type}\nCurrent Value: {$alert->metric_value}%\nThreshold: {$threshold->threshold_value}%\nTime: {$alert->alert_time}\n\nThis is an automated message from your server monitoring system.",
                    'html' => "<!DOCTYPE html><html><body style='font-family: Arial, sans-serif; line-height: 1.5;'>" .
                              "<div style='max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 5px;'>" .
                              "<h2 style='color: #3366cc;'>ServerPulse Monitoring Alert</h2>" .
                              "<p style='margin-bottom: 20px;'>An important system metric has exceeded the configured threshold on one of your monitored servers. This alert requires your attention.</p>" .
                              
                              "<div style='background-color: #f9f9f9; border-left: 4px solid #3366cc; padding: 12px; margin-bottom: 20px;'>" .
                              "<h3 style='margin-top: 0; color: #333;'>Alert Details</h3>" .
                              "<table style='width: 100%; border-collapse: collapse;'>" .
                              "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; width: 30%; font-weight: bold;'>Server Name:</td>" .
                                  "<td style='padding: 8px; border-bottom: 1px solid #eee;'>{$serverName}</td></tr>" .
                              "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Server IP:</td>" .
                                  "<td style='padding: 8px; border-bottom: 1px solid #eee;'>{$alert->server->ip_address}</td></tr>" .
                              "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Metric Type:</td>" .
                                  "<td style='padding: 8px; border-bottom: 1px solid #eee;'>{$threshold->metric_type}</td></tr>" .
                              "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Current Value:</td>" .
                                  "<td style='padding: 8px; border-bottom: 1px solid #eee;'><strong style='color: " .
                                  ($threshold->metric_type === 'Load' ? 
                                      ($alert->metric_value > $threshold->threshold_value * 1.5 ? '#cc3300' : '#ff9900') : 
                                      ($alert->metric_value > 90 ? '#cc3300' : ($alert->metric_value > 75 ? '#ff9900' : '#3366cc'))
                                  ) . ";'>{$alert->metric_value}" . ($threshold->metric_type !== 'Load' ? '%' : '') . "</strong> " .
                                  "(" . ($threshold->metric_type === 'Load' ? 
                                      ($alert->metric_value > $threshold->threshold_value * 1.5 ? 'critically high' : 'elevated') : 
                                      ($alert->metric_value > 90 ? 'critical' : ($alert->metric_value > 75 ? 'high' : 'moderate'))
                                  ) . ")</td></tr>" .
                              "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Threshold:</td>" .
                                  "<td style='padding: 8px; border-bottom: 1px solid #eee;'>{$threshold->threshold_value}" . ($threshold->metric_type !== 'Load' ? '%' : '') . "</td></tr>" .
                              "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Alert Time:</td>" .
                                  "<td style='padding: 8px; border-bottom: 1px solid #eee;'>{$alert->alert_time}</td></tr>" .
                              "<tr><td style='padding: 8px; border-bottom: 1px solid #eee; font-weight: bold;'>Alert ID:</td>" .
                                  "<td style='padding: 8px; border-bottom: 1px solid #eee;'>{$alert->id}</td></tr>" .
                              "</table></div>" .
                              
                              "<div style='margin-bottom: 20px;'>" .
                              "<h3 style='color: #333;'>Recommended Actions</h3>" .
                              "<ul style='padding-left: 20px; line-height: 1.6;'>" .
                              "<li>Check server resources and active processes</li>" .
                              "<li>Investigate potential causes for the " . strtolower($threshold->metric_type) . " increase</li>" .
                              "<li>" . ($threshold->metric_type === 'CPU' ? 
                                  "Look for processes consuming high CPU" : 
                                  ($threshold->metric_type === 'RAM' ? 
                                      "Check for memory leaks or applications using excessive memory" : 
                                      ($threshold->metric_type === 'Disk' ? 
                                          "Consider cleaning up disk space or expanding storage" : 
                                          "Review system load and running processes"))
                              ) . "</li>" .
                              "<li>Access the ServerPulse dashboard for more detailed metrics</li>" .
                              "</ul></div>" .
                              
                              "<div style='margin-top: 30px; padding-top: 15px; border-top: 1px solid #eee;'>" .
                              "<p style='margin-top: 0; color: #666; font-size: 13px;'>This is an automated notification from your ServerPulse monitoring system.</p>" .
                              "<p style='color: #666; font-size: 12px;'>Alert notifications are limited to one per minute per alert condition. Additional alerts may not trigger new emails during this period.</p>" .
                              "</div>" .
                              "</div></body></html>",
                    'h:X-Mailgun-Variables' => json_encode(['server_id' => $alert->server_id]),
                    'h:Reply-To' => 'no-reply@serverpulse.com', 
                    'h:List-Unsubscribe' => '<mailto:unsubscribe@serverpulse.com?subject=unsubscribe>',
                    'h:Precedence' => 'bulk',
                    'h:Auto-Submitted' => 'auto-generated',
                    'o:tag' => ['monitoring', 'serverpulse']
                ]);
                $result = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);
                
                Log::info('Direct API email attempt', [
                    'to' => $email,
                    'result' => $result,
                    'http_code' => $httpCode
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send alert notifications', [
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }
}

