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
        $thresholds = \App\Models\AlertThreshold::where('server_id', $server->id)->get();

        foreach ($thresholds as $threshold) {
            $currentValue = null;
            $metricKey = '';

            // Map metric types to actual metric keys
            switch ($threshold->metric_type) {
                case 'CPU':
                    $metricKey = 'cpu_usage';
                    $currentValue = $metrics['cpu_usage'] ?? null;
                    break;
                case 'RAM':
                    $metricKey = 'ram_usage';
                    $currentValue = $metrics['ram_usage'] ?? null;
                    break;
                case 'Disk':
                    $metricKey = 'disk_usage';
                    $currentValue = $metrics['disk_usage'] ?? null;
                    break;
                case 'Load':
                    $metricKey = 'load_average';
                    $loadStr = $metrics['load_average'] ?? '0.0';
                    // Extract first load average value (1-minute)
                    $loadValues = explode(',', $loadStr);
                    $currentValue = floatval(trim($loadValues[0] ?? '0'));
                    break;
            }

            if ($currentValue !== null && $currentValue > $threshold->threshold_value) {
                $level = $this->determineLevelBySeverity($threshold->metric_type, $currentValue, $threshold->threshold_value);
                
                \App\Models\Log::create([
                    'server_id' => $server->id,
                    'level' => $level,
                    'source' => 'threshold_monitor',
                    'log_level' => strtoupper($level),
                    'message' => sprintf(
                        '%s %s exceeded threshold: %.2f%s (threshold: %.2f%s)',
                        ucfirst($threshold->metric_type),
                        $threshold->metric_type === 'Load' ? 'average' : 'usage',
                        $currentValue,
                        $threshold->metric_type === 'Load' ? '' : '%',
                        $threshold->threshold_value,
                        $threshold->metric_type === 'Load' ? '' : '%'
                    ),
                    'context' => [
                        'metric_type' => $threshold->metric_type,
                        'current_value' => $currentValue,
                        'threshold_value' => $threshold->threshold_value,
                        'server_name' => $server->name,
                        'server_ip' => $server->ip_address,
                        'all_metrics' => $metrics
                    ],
                ]);
                
                // Log to Laravel's system log as well for debugging
                \Illuminate\Support\Facades\Log::warning(
                    "Threshold exceeded for {$server->name}: {$threshold->metric_type} = {$currentValue}"
                );
            }
        }
    }

    /**
     * Determine log level based on how much the threshold was exceeded
     */
    private function determineLevelBySeverity($metricType, $currentValue, $thresholdValue)
    {
        $exceedPercentage = ($currentValue - $thresholdValue) / $thresholdValue * 100;
        
        if ($exceedPercentage >= 50) { // 50% over threshold
            return 'critical';
        } elseif ($exceedPercentage >= 25) { // 25% over threshold
            return 'error';
        } elseif ($exceedPercentage >= 10) { // 10% over threshold
            return 'warning';
        } else {
            return 'notice';
        }
    }
}