<?php

namespace App\Services;

use App\Models\Server;
use App\Models\AlertThreshold;
use App\Models\Log; // This is your Eloquent Log model
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use Exception;

class ServerMonitoringService
{
    /**
     * Get server metrics via SSH
     */
    public function getMetrics(Server $server)
    {
        try {
            // Use info logging only if you need it, e.g.:
            // \Illuminate\Support\Facades\Log::info("[Monitoring] Attempting SSH to {$server->ip_address}:{$server->ssh_port} as {$server->ssh_user}");
            if ($this->isLocalhost($server->ip_address)) {
                return $this->getLocalMetrics();
            }

            $ssh = new SSH2($server->ip_address, $server->ssh_port ?? 22);
            
            if (!empty($server->ssh_key)) {
                $key = PublicKeyLoader::load($server->ssh_key);
                $success = $ssh->login($server->ssh_user, $key);
            } else {
                $success = $ssh->login($server->ssh_user, $server->ssh_password);
            }

            if (!$success) {
                // \Illuminate\Support\Facades\Log::error("[Monitoring] SSH login failed for {$server->ip_address} as {$server->ssh_user}");
                throw new Exception('SSH login failed');
            }

            $distro = strtolower(trim($ssh->exec('cat /etc/os-release | grep -w "ID" | cut -d= -f2')));

            switch ($distro) {
                case 'ubuntu':
                case 'debian':
                case 'centos':
                case 'rhel':
                    $cpu = trim($ssh->exec("top -bn1 | grep 'Cpu(s)' | awk '{print $2}'"));
                    break;
                default:
                    $cpu = trim($ssh->exec("grep 'cpu ' /proc/stat | awk '{usage=($2+$4)*100/($2+$4+$5)} END {print usage}'"));
            }
            
            $memInfo = $ssh->exec('free');
            if (strpos($memInfo, 'available') !== false) {
                $memoryTotal = trim($ssh->exec("free | grep Mem | awk '{print $2}'"));
                $memoryAvailable = trim($ssh->exec("free | grep Mem | awk '{print $7}'"));
                $memoryUsage = ($memoryTotal - $memoryAvailable) / $memoryTotal * 100;
            } else {
                $memoryTotal = trim($ssh->exec("free | grep Mem | awk '{print $2}'"));
                $memoryUsed = trim($ssh->exec("free | grep Mem | awk '{print $3}'"));
                $memoryUsage = ($memoryUsed / $memoryTotal) * 100;
            }

            $diskUsage = trim($ssh->exec("df / | tail -1 | awk '{print $5}' | sed 's/%//'"));

            $uptime = trim($ssh->exec('uptime -p'));
            $loadAvg = trim($ssh->exec("uptime | awk -F'load average:' '{print $2}'"));

            // \Illuminate\Support\Facades\Log::info("[Monitoring] Metrics for {$server->ip_address}: CPU={$cpu}, RAM={$memoryUsage}, DISK={$diskUsage}");
            return [
                'cpu_usage' => floatval($cpu),
                'ram_usage' => floatval($memoryUsage),
                'disk_usage' => floatval($diskUsage),
                'status' => 'online',
                'uptime' => $uptime,
                'load_average' => $loadAvg
            ];
        } catch (Exception $e) {
            return [
                'cpu_usage' => 0,
                'ram_usage' => 0,
                'disk_usage' => 0,
                'status' => 'offline',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get metrics for localhost (Windows/Laragon environment)
     */
    private function getLocalMetrics()
    {
        try {
            $cpu = shell_exec('powershell "Get-WmiObject Win32_Processor | Measure-Object -Property LoadPercentage -Average | Select-Object -ExpandProperty Average"');
            $cpuUsage = floatval(trim($cpu));

            $memory = shell_exec('powershell "(Get-Counter \'\Memory\% Committed Bytes In Use\' -SampleInterval 1 -MaxSamples 1).CounterSamples.CookedValue"');
            $memoryUsage = floatval(trim($memory));

            $disk = shell_exec('powershell "Get-WmiObject Win32_LogicalDisk -Filter \"DeviceID=\'C:\'\" | Select-Object Size,FreeSpace | ConvertTo-Json"');
            $diskInfo = json_decode($disk, true);
            
            if ($diskInfo) {
                $totalDisk = floatval($diskInfo['Size']);
                $freeDisk = floatval($diskInfo['FreeSpace']);
                $diskUsage = $totalDisk > 0 ? (($totalDisk - $freeDisk) / $totalDisk) * 100 : 0;
            } else {
                $diskUsage = 0;
            }

            return [
                'cpu_usage' => $cpuUsage,
                'ram_usage' => $memoryUsage,
                'disk_usage' => $diskUsage,
                'status' => 'online'
            ];
        } catch (Exception $e) {
            return [
                'cpu_usage' => 0,
                'ram_usage' => 0,
                'disk_usage' => 0,
                'status' => 'offline',
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Check if the IP address is localhost
     */
    private function isLocalhost($ip)
    {
        return in_array($ip, ['127.0.0.1', 'localhost', '::1']);
    }

    /**
     * Check and log thresholds for server metrics
     */
    public function checkAndLogThresholds(Server $server, array $metrics)
    {
        // Define default critical thresholds for infrastructure monitoring
        $defaultThresholds = [
            'CPU' => ['warning' => 70, 'critical' => 85],
            'RAM' => ['warning' => 75, 'critical' => 90],
            'Disk' => ['warning' => 80, 'critical' => 95],
            'Load' => ['warning' => 2.0, 'critical' => 4.0]
        ];

        $customThresholds = \App\Models\AlertThreshold::where('server_id', $server->id)->get();

        // Process each metric type
        foreach (['CPU', 'RAM', 'Disk', 'Load'] as $metricType) {
            $currentValue = $this->getMetricValue($metrics, $metricType);
            if ($currentValue === null) continue;

            // Get custom threshold or use default
            $customThreshold = $customThresholds->where('metric_type', $metricType)->first();
            $thresholds = $customThreshold ? 
                ['warning' => $customThreshold->threshold_value, 'critical' => $customThreshold->threshold_value * 1.2] :
                $defaultThresholds[$metricType];

            // Check for violations and create detailed logs
            $this->checkThresholdViolation($server, $metricType, $currentValue, $thresholds, $metrics);
        }
    }

    /**
     * Get metric value by type
     */
    private function getMetricValue(array $metrics, string $metricType)
    {
        switch ($metricType) {
            case 'CPU':
                return $metrics['cpu_usage'] ?? null;
            case 'RAM':
                return $metrics['ram_usage'] ?? null;
            case 'Disk':
                return $metrics['disk_usage'] ?? null;
            case 'Load':
                $loadStr = $metrics['load_average'] ?? '0.0';
                $loadValues = explode(',', $loadStr);
                return floatval(trim($loadValues[0] ?? '0'));
            default:
                return null;
        }
    }

    /**
     * Check threshold violation and create comprehensive log
     */
    private function checkThresholdViolation(Server $server, string $metricType, float $currentValue, array $thresholds, array $allMetrics)
    {
        $violationType = null;
        $level = 'info';

        if ($currentValue >= $thresholds['critical']) {
            $violationType = 'critical';
            $level = 'error';
        } elseif ($currentValue >= $thresholds['warning']) {
            $violationType = 'warning';
            $level = 'warning';
        }

        // Only log if there's a threshold violation
        if ($violationType) {
            $message = $this->generateThresholdMessage($metricType, $currentValue, $thresholds[$violationType], $violationType);
            $context = $this->generateThresholdContext($server, $metricType, $currentValue, $thresholds, $allMetrics, $violationType);

            \App\Models\Log::create([
                'server_id' => $server->id,
                'level' => $level,
                'source' => 'infrastructure_monitor',
                'log_level' => strtoupper($level),
                'message' => $message,
                'context' => $context,
            ]);

            // Log to Laravel's system log for debugging
            \Illuminate\Support\Facades\Log::warning(
                "Infrastructure Alert: {$server->name} - {$message}"
            );
        }
    }

    /**
     * Generate detailed threshold violation message
     */
    private function generateThresholdMessage(string $metricType, float $currentValue, float $threshold, string $violationType)
    {
        $unit = in_array($metricType, ['CPU', 'RAM', 'Disk']) ? '%' : '';
        $exceedAmount = $currentValue - $threshold;
        $exceedPercent = round(($exceedAmount / $threshold) * 100, 1);

        $severityText = $violationType === 'critical' ? 'CRITICAL SPIKE' : 'WARNING SPIKE';
        
        return sprintf(
            '%s: %s %s reached %.2f%s (%.2f%s above %s threshold of %.2f%s)',
            $severityText,
            $metricType,
            $metricType === 'Load' ? 'average' : 'usage',
            $currentValue,
            $unit,
            $exceedAmount,
            $unit,
            $violationType,
            $threshold,
            $unit
        );
    }

    /**
     * Generate comprehensive context for infrastructure analysis
     */
    private function generateThresholdContext(Server $server, string $metricType, float $currentValue, array $thresholds, array $allMetrics, string $violationType)
    {
        $context = [
            // Violation details
            'violation_type' => $violationType,
            'metric_type' => strtolower($metricType),
            'current_value' => $currentValue,
            'threshold_value' => $thresholds[$violationType],
            'warning_threshold' => $thresholds['warning'],
            'critical_threshold' => $thresholds['critical'],
            'exceed_percentage' => round((($currentValue - $thresholds[$violationType]) / $thresholds[$violationType]) * 100, 2),
            
            // Server information
            'server_name' => $server->name,
            'server_ip' => $server->ip_address,
            'server_id' => $server->id,
            
            // All current metrics for analysis
            'cpu_usage' => $allMetrics['cpu_usage'] ?? 0,
            'memory_usage' => $allMetrics['ram_usage'] ?? 0,
            'disk_usage' => $allMetrics['disk_usage'] ?? 0,
            'load_average' => $allMetrics['load_average'] ?? '0.0',
            'server_status' => $allMetrics['status'] ?? 'unknown',
            
            // Infrastructure analysis
            'spike_severity' => $this->calculateSpikeSeverity($currentValue, $thresholds),
            'affected_services' => $this->predictAffectedServices($metricType, $currentValue),
            'recommended_actions' => $this->getRecommendedActions($metricType, $violationType, $currentValue),
            'predicted_impact' => $this->predictImpact($metricType, $violationType, $allMetrics),
            
            // Timing
            'detected_at' => now()->toISOString(),
            'uptime' => $allMetrics['uptime'] ?? 'unknown'
        ];

        return $context;
    }

    /**
     * Calculate spike severity for infrastructure analysis
     */
    private function calculateSpikeSeverity(float $currentValue, array $thresholds)
    {
        $criticalThreshold = $thresholds['critical'];
        if ($currentValue >= $criticalThreshold * 1.5) return 'extreme';
        if ($currentValue >= $criticalThreshold * 1.3) return 'severe';
        if ($currentValue >= $criticalThreshold) return 'high';
        return 'moderate';
    }

    /**
     * Predict affected services based on metric type and severity
     */
    private function predictAffectedServices(string $metricType, float $currentValue)
    {
        $services = [];
        
        switch ($metricType) {
            case 'CPU':
                if ($currentValue >= 90) {
                    $services = ['Web Applications', 'Database Queries', 'Background Jobs', 'API Responses'];
                } elseif ($currentValue >= 80) {
                    $services = ['Web Applications', 'Database Performance'];
                }
                break;
            case 'RAM':
                if ($currentValue >= 95) {
                    $services = ['System Stability', 'Application Memory', 'Cache Systems', 'Database Buffer'];
                } elseif ($currentValue >= 85) {
                    $services = ['Application Performance', 'Cache Systems'];
                }
                break;
            case 'Disk':
                if ($currentValue >= 98) {
                    $services = ['File System', 'Log Files', 'Database Storage', 'Application Uploads'];
                } elseif ($currentValue >= 90) {
                    $services = ['Log Rotation', 'Temporary Files'];
                }
                break;
            case 'Load':
                if ($currentValue >= 5.0) {
                    $services = ['System Responsiveness', 'Process Scheduling', 'I/O Operations'];
                } elseif ($currentValue >= 3.0) {
                    $services = ['System Performance'];
                }
                break;
        }
        
        return $services;
    }

    /**
     * Get recommended actions for infrastructure teams
     */
    private function getRecommendedActions(string $metricType, string $violationType, float $currentValue)
    {
        $actions = [];
        
        switch ($metricType) {
            case 'CPU':
                if ($violationType === 'critical') {
                    $actions = [
                        'Scale CPU resources immediately',
                        'Identify and optimize CPU-intensive processes',
                        'Implement load balancing if not already in place',
                        'Check for infinite loops or runaway processes'
                    ];
                } else {
                    $actions = [
                        'Monitor CPU usage trends',
                        'Review recent deployments and changes',
                        'Consider CPU optimization'
                    ];
                }
                break;
            case 'RAM':
                if ($violationType === 'critical') {
                    $actions = [
                        'Add more RAM or scale memory resources',
                        'Identify memory leaks in applications',
                        'Restart memory-intensive services if safe',
                        'Clear unnecessary cache and buffers'
                    ];
                } else {
                    $actions = [
                        'Monitor memory usage patterns',
                        'Review application memory consumption',
                        'Plan for memory expansion'
                    ];
                }
                break;
            case 'Disk':
                if ($violationType === 'critical') {
                    $actions = [
                        'Free up disk space immediately',
                        'Implement log rotation policies',
                        'Move or archive old files',
                        'Expand disk capacity'
                    ];
                } else {
                    $actions = [
                        'Clean up temporary files',
                        'Review disk usage patterns',
                        'Plan for storage expansion'
                    ];
                }
                break;
            case 'Load':
                if ($violationType === 'critical') {
                    $actions = [
                        'Reduce system load immediately',
                        'Check for I/O bottlenecks',
                        'Scale server resources',
                        'Optimize running processes'
                    ];
                } else {
                    $actions = [
                        'Monitor load average trends',
                        'Review system performance',
                        'Check for resource contention'
                    ];
                }
                break;
        }
        
        return $actions;
    }

    /**
     * Predict impact on infrastructure
     */
    private function predictImpact(string $metricType, string $violationType, array $allMetrics)
    {
        $impact = [
            'immediate_risk' => 'low',
            'service_degradation' => false,
            'potential_downtime' => false,
            'user_impact' => 'minimal'
        ];
        
        if ($violationType === 'critical') {
            $impact['immediate_risk'] = 'high';
            $impact['service_degradation'] = true;
            
            switch ($metricType) {
                case 'CPU':
                case 'RAM':
                    $impact['potential_downtime'] = true;
                    $impact['user_impact'] = 'severe';
                    break;
                case 'Disk':
                    if (($allMetrics['disk_usage'] ?? 0) >= 98) {
                        $impact['potential_downtime'] = true;
                        $impact['user_impact'] = 'severe';
                    } else {
                        $impact['user_impact'] = 'moderate';
                    }
                    break;
                case 'Load':
                    $impact['user_impact'] = 'moderate';
                    break;
            }
        } elseif ($violationType === 'warning') {
            $impact['immediate_risk'] = 'medium';
            $impact['user_impact'] = 'minor';
        }
        
        return $impact;
    }
}