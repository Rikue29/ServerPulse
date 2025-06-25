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

        // Check for existing unresolved alert to prevent spam
        $existingAlert = Alert::where('threshold_id', $threshold->id)
            ->where('server_id', $validated['server_id'])
            ->where('status', 'triggered')
            ->where('alert_time', '>=', Carbon::now()->subMinutes(5)) // Don't spam alerts within 5 minutes
            ->first();

        if ($existingAlert) {
            Log::info('Similar alert already exists, skipping', ['existing_alert_id' => $existingAlert->id]);
            return response()->json(['message' => 'Similar alert already active'], 200);
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
            $emails = $threshold->getNotificationEmails();
            
            // Add your personal email
            $adminEmail = config('mail.admin_email');
            if ($adminEmail && !in_array($adminEmail, $emails)) {
                $emails[] = $adminEmail;
            }

            Log::info('Sending alert notifications', [
                'alert_id' => $alert->id,
                'emails' => $emails
            ]);

            foreach ($emails as $email) {
                Notification::route('mail', $email)->notify(new AlertTriggered($alert));
            }
        } catch (\Exception $e) {
            Log::error('Failed to send alert notifications', [
                'alert_id' => $alert->id,
                'error' => $e->getMessage()
            ]);
        }
    }
}

