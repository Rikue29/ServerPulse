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
            $wasOnline = $server->status === 'online';
            if ($this->isLocalhost($server->ip_address)) {
                $metrics = $this->getLocalMetrics($server);
            } else {
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

                $metrics = [
                    'cpu_usage' => floatval($cpu),
                    'ram_usage' => floatval($memoryUsage),
                    'disk_usage' => floatval($diskUsage),
                    'status' => 'online',
                    'uptime' => $uptime,
                    'load_average' => $loadAvg
                ];
            }
            // Update running_since and last_down_at for remote servers
            if (!$this->isLocalhost($server->ip_address)) {
                if (($metrics['status'] ?? 'offline') === 'online') {
                    if (!$wasOnline || !$server->running_since) {
                        $server->running_since = now();
                        $server->last_down_at = null;
                        $server->save();
                    }
                } else {
                    if ($wasOnline || !$server->last_down_at) {
                        $server->last_down_at = now();
                        $server->running_since = null;
                        $server->save();
                    }
                }
            }
            // Add uptime/downtime info
            $metrics['running_since'] = $server->running_since;
            $metrics['last_down_at'] = $server->last_down_at;
            $metrics['total_uptime_seconds'] = $server->total_uptime_seconds;
            $metrics['total_downtime_seconds'] = $server->total_downtime_seconds;
            if ($metrics['status'] === 'online' && $server->running_since) {
                $metrics['current_uptime'] = now()->diffInSeconds($server->running_since);
            } elseif ($metrics['status'] === 'offline' && $server->last_down_at) {
                $metrics['current_downtime'] = now()->diffInSeconds($server->last_down_at);
            }
            return $metrics;
        } catch (Exception $e) {
            return [
                'cpu_usage' => 0,
                'ram_usage' => 0,
                'disk_usage' => 0,
                'status' => 'offline',
                'error' => $e->getMessage(),
                'last_down_at' => $server->last_down_at,
                'total_downtime_seconds' => $server->total_downtime_seconds,
                'current_downtime' => $server->last_down_at ? now()->diffInSeconds($server->last_down_at) : null
            ];
        }
    }

    /**
     * Get metrics for localhost (Windows/Laragon environment or Linux)
     */
    private function getLocalMetrics(Server $server = null)
    {
        if (PHP_OS_FAMILY === 'Linux') {
            return $this->getLinuxLocalMetrics($server);
        }
        // Windows metrics collection
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
            $metrics = [
                'cpu_usage' => $cpuUsage,
                'ram_usage' => $memoryUsage,
                'disk_usage' => $diskUsage,
                'status' => 'online'
            ];
            if ($server) {
                $metrics['running_since'] = $server->running_since;
                $metrics['last_down_at'] = $server->last_down_at;
                $metrics['total_uptime_seconds'] = $server->total_uptime_seconds;
                $metrics['total_downtime_seconds'] = $server->total_downtime_seconds;
                if ($server->running_since) {
                    $metrics['current_uptime'] = now()->diffInSeconds($server->running_since);
                }
            }
            return $metrics;
        } catch (\Throwable $e) {
            return [
                'cpu_usage' => 0,
                'ram_usage' => 0,
                'disk_usage' => 0,
                'status' => 'offline',
                'error' => $e->getMessage(),
                'last_down_at' => $server ? $server->last_down_at : null,
                'total_downtime_seconds' => $server ? $server->total_downtime_seconds : null,
                'current_downtime' => $server && $server->last_down_at ? now()->diffInSeconds($server->last_down_at) : null
            ];
        }
    }

    /**
     * Get metrics for Linux localhost
     */
    private function getLinuxLocalMetrics(Server $server = null)
    {
        $cpuUsage = 0;
        $memoryUsage = 0;
        $diskUsage = 0;
        $uptime = '';
        $loadAvg = '';

        // CPU Usage
        if (file_exists('/proc/stat')) {
            $stat1 = file_get_contents('/proc/stat');
            usleep(100000); // 100ms
            $stat2 = file_get_contents('/proc/stat');
            $cpu1 = preg_split('/\s+/', explode("\n", $stat1)[0]);
            $cpu2 = preg_split('/\s+/', explode("\n", $stat2)[0]);
            if (count($cpu1) >= 5 && count($cpu2) >= 5) {
                $total1 = array_sum(array_slice($cpu1, 1, 7));
                $total2 = array_sum(array_slice($cpu2, 1, 7));
                $idle1 = $cpu1[4];
                $idle2 = $cpu2[4];
                $totalDiff = $total2 - $total1;
                $idleDiff = $idle2 - $idle1;
                if ($totalDiff > 0) {
                    $cpuUsage = round((1 - ($idleDiff / $totalDiff)) * 100, 2);
                }
            }
        }
        // Fallback if /proc/stat fails
        if ($cpuUsage === 0) {
            $load = sys_getloadavg();
            if ($load && is_array($load)) {
                // This is not percent, but gives an idea (1-min load average)
                $cpuUsage = $load[0];
            }
        }

        // Memory Usage
        if (file_exists('/proc/meminfo')) {
            $meminfo = file_get_contents('/proc/meminfo');
            if (preg_match('/MemTotal:\\s+(\\d+)/i', $meminfo, $totalMatch)) {
                $total = (int)$totalMatch[1];
                if ($total > 0) {
                    if (preg_match('/MemAvailable:\\s+(\\d+)/i', $meminfo, $availMatch)) {
                        $available = (int)$availMatch[1];
                        $memoryUsage = round(($total - $available) / $total * 100, 2);
                    } elseif (
                        preg_match('/MemFree:\\s+(\\d+)/i', $meminfo, $freeMatch) &&
                        preg_match('/Buffers:\\s+(\\d+)/i', $meminfo, $buffersMatch) &&
                        preg_match('/Cached:\\s+(\\d+)/i', $meminfo, $cachedMatch)
                    ) {
                        $free = (int)$freeMatch[1];
                        $buffers = (int)$buffersMatch[1];
                        $cached = (int)$cachedMatch[1];
                        $used = $total - $free - $buffers - $cached;
                        $memoryUsage = round($used / $total * 100, 2);
                    }
                }
            }
        }

        // Disk Usage
        $total = @disk_total_space('/');
        $free = @disk_free_space('/');
        if ($total > 0) {
            $diskUsage = round(($total - $free) / $total * 100, 2);
        }

        // Uptime
        if (file_exists('/proc/uptime')) {
            $uptime_seconds = (int)floatval(explode(' ', file_get_contents('/proc/uptime'))[0]);
            $uptime = 'up ' . gmdate('H:i:s', $uptime_seconds);
        }
        // Load Average
        if (file_exists('/proc/loadavg')) {
            $loadAvg = trim(file_get_contents('/proc/loadavg'));
        }

        $metrics = [
            'cpu_usage' => $cpuUsage,
            'ram_usage' => $memoryUsage,
            'disk_usage' => $diskUsage,
            'status' => 'online',
            'uptime' => $uptime,
            'load_average' => $loadAvg
        ];
        if ($server) {
            $metrics['running_since'] = $server->running_since;
            $metrics['last_down_at'] = $server->last_down_at;
            $metrics['total_uptime_seconds'] = $server->total_uptime_seconds;
            $metrics['total_downtime_seconds'] = $server->total_downtime_seconds;
            if ($server->running_since) {
                $metrics['current_uptime'] = now()->diffInSeconds($server->running_since);
            }
        }
        return $metrics;
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
