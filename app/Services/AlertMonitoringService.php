<?php

namespace App\Services;

use App\Models\Server;
use App\Models\AlertThreshold;
use App\Models\Alert;
use App\Http\Controllers\AlertController;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class AlertMonitoringService
{
    protected $alertController;

    public function __construct()
    {
        $this->alertController = new AlertController();
    }

    /**
     * Check all servers against their thresholds and trigger alerts if needed
     */
    public function checkAllThresholds(): array
    {
        $results = [];
        $servers = Server::with('alertThresholds')->get();

        foreach ($servers as $server) {
            $serverResults = $this->checkServerThresholds($server);
            if (!empty($serverResults)) {
                $results[$server->id] = $serverResults;
            }
        }

        return $results;
    }

    /**
     * Check a specific server against its thresholds
     */
    public function checkServerThresholds(Server $server): array
    {
        $results = [];
        $thresholds = $server->alertThresholds()->where('is_active', true)->get();

        foreach ($thresholds as $threshold) {
            $metricValue = $this->getServerMetricValue($server, $threshold->metric_type);
            
            if ($metricValue !== null && $threshold->shouldTrigger($metricValue)) {
                $alertResult = $this->triggerAlert($threshold, $server, $metricValue);
                if ($alertResult) {
                    $results[] = $alertResult;
                }
            }
        }

        return $results;
    }

    /**
     * Get the current metric value for a server
     */
    protected function getServerMetricValue(Server $server, string $metricType): ?float
    {
        // In a real application, you would fetch this from your monitoring system
        // For demo purposes, we'll generate realistic values
        
        switch (strtoupper($metricType)) {
            case 'CPU':
                return $this->generateRealisticCpuUsage($server);
            case 'RAM':
            case 'MEMORY':
                return $this->generateRealisticMemoryUsage($server);
            case 'DISK':
                return $this->generateRealisticDiskUsage($server);
            case 'NETWORK':
                return $this->generateRealisticNetworkUsage($server);
            default:
                return null;
        }
    }

    /**
     * Generate realistic CPU usage based on server load patterns
     */
    protected function generateRealisticCpuUsage(Server $server): float
    {
        $hour = Carbon::now()->hour;
        $baseUsage = 20; // Base usage
        
        // Simulate daily patterns
        if ($hour >= 9 && $hour <= 17) {
            $baseUsage += 30; // Business hours higher usage
        }
        
        // Add some randomness but bias towards normal operation
        $randomFactor = mt_rand(-15, 25);
        $usage = $baseUsage + $randomFactor;
        
        // Occasionally simulate high usage (5% chance)
        if (mt_rand(1, 20) === 1) {
            $usage += mt_rand(40, 60);
        }
        
        return max(0, min(100, $usage));
    }

    /**
     * Generate realistic memory usage
     */
    protected function generateRealisticMemoryUsage(Server $server): float
    {
        $baseUsage = 45; // Typical memory usage
        $randomFactor = mt_rand(-10, 20);
        $usage = $baseUsage + $randomFactor;
        
        // Occasionally simulate memory leak (3% chance)
        if (mt_rand(1, 33) === 1) {
            $usage += mt_rand(30, 45);
        }
        
        return max(10, min(100, $usage));
    }

    /**
     * Generate realistic disk usage
     */
    protected function generateRealisticDiskUsage(Server $server): float
    {
        // Disk usage typically grows slowly over time
        $baseUsage = 60;
        $randomFactor = mt_rand(-5, 10);
        $usage = $baseUsage + $randomFactor;
        
        // Rarely simulate disk space issues (1% chance)
        if (mt_rand(1, 100) === 1) {
            $usage += mt_rand(25, 35);
        }
        
        return max(20, min(100, $usage));
    }

    /**
     * Generate realistic network usage
     */
    protected function generateRealisticNetworkUsage(Server $server): float
    {
        $hour = Carbon::now()->hour;
        $baseUsage = 15;
        
        // Higher network usage during business hours
        if ($hour >= 8 && $hour <= 18) {
            $baseUsage += 25;
        }
        
        $randomFactor = mt_rand(-10, 30);
        $usage = $baseUsage + $randomFactor;
        
        // Occasionally simulate network spike (8% chance)
        if (mt_rand(1, 12) === 1) {
            $usage += mt_rand(30, 50);
        }
        
        return max(0, min(100, $usage));
    }

    /**
     * Trigger an alert using the AlertController
     */
    protected function triggerAlert(AlertThreshold $threshold, Server $server, float $metricValue): ?array
    {
        try {
            $request = new \Illuminate\Http\Request([
                'threshold_id' => $threshold->id,
                'server_id' => $server->id,
                'metric_value' => $metricValue,
            ]);

            $response = $this->alertController->trigger($request);
            $responseData = json_decode($response->getContent(), true);

            if ($response->getStatusCode() === 201) {
                Log::info('Alert triggered successfully', [
                    'server_id' => $server->id,
                    'threshold_id' => $threshold->id,
                    'metric_value' => $metricValue,
                    'alert_id' => $responseData['alert']['id'] ?? null
                ]);

                return $responseData;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to trigger alert', [
                'server_id' => $server->id,
                'threshold_id' => $threshold->id,
                'metric_value' => $metricValue,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * Simulate a critical alert for testing
     */
    public function simulateCriticalAlert(Server $server): ?array
    {
        $threshold = $server->alertThresholds()->where('is_active', true)->first();
        
        if (!$threshold) {
            Log::warning('No active thresholds found for server', ['server_id' => $server->id]);
            return null;
        }

        // Generate a value that definitely exceeds the threshold
        $criticalValue = $threshold->threshold_value + mt_rand(10, 30);
        
        return $this->triggerAlert($threshold, $server, $criticalValue);
    }

    /**
     * Get system health summary
     */
    public function getSystemHealthSummary(): array
    {
        $servers = Server::all();
        $activeThresholds = AlertThreshold::where('is_active', true)->count();
        $unresolvedAlerts = Alert::unresolved()->count();
        $criticalAlerts = Alert::unresolved()->where('metric_value', '>=', 90)->count();

        return [
            'total_servers' => $servers->count(),
            'online_servers' => $servers->where('status', 'online')->count(),
            'active_thresholds' => $activeThresholds,
            'unresolved_alerts' => $unresolvedAlerts,
            'critical_alerts' => $criticalAlerts,
            'system_health' => $unresolvedAlerts === 0 ? 'healthy' : ($criticalAlerts > 0 ? 'critical' : 'warning')
        ];
    }
}
