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
                $rx = 0;
                $tx = 0;
                $ioRead = 0;
                $ioWrite = 0;
                
                if (!empty($server->ssh_key)) {
                    $key = PublicKeyLoader::load(trim($server->ssh_key));
                    $success = $ssh->login($server->ssh_user, $key);
                } else {
                    $success = $ssh->login($server->ssh_user, $server->ssh_password);
                }

                if (!$success) {
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
                    $memoryUsage = ($memoryTotal > 0) ? ($memoryTotal - $memoryAvailable) / $memoryTotal * 100 : 0;
                } else {
                    $memoryTotal = trim($ssh->exec("free | grep Mem | awk '{print $2}'"));
                    $memoryUsed = trim($ssh->exec("free | grep Mem | awk '{print $3}'"));
                    $memoryUsage = ($memoryTotal > 0) ? ($memoryUsed / $memoryTotal) * 100 : 0;
                }

                $diskUsage = trim($ssh->exec("df / | tail -1 | awk '{print $5}' | sed 's/%//'"));

                $uptimeSeconds = floatval(trim($ssh->exec("awk '{print $1}' /proc/uptime")));
                $systemUptime = $this->formatUptimeFromSeconds($uptimeSeconds);
                $loadAvg = trim($ssh->exec("uptime | awk -F'load average:' '{print $2}'"));

                // Network traffic - dynamically find the default interface
                $defaultInterface = trim($ssh->exec("ip route | grep default | awk '{print $5}'"));
                if (empty($defaultInterface)) {
                    $defaultInterface = 'eth0'; // Fallback
                }
                
                $netDev = $ssh->exec('cat /proc/net/dev');
                $lines = explode("\n", $netDev);
                foreach ($lines as $line) {
                    if (strpos($line, $defaultInterface . ':') !== false) {
                        $parts = preg_split('/\s+/', trim($line));
                        $rx = (int)$parts[1];
                        $tx = (int)$parts[9];
                        break;
                    }
                }

                // Disk I/O
                $diskStats = $ssh->exec('cat /proc/diskstats');
                $lines = explode("\n", $diskStats);
                foreach ($lines as $line) {
                    // Look for common disk names like sda, vda, nvme0n1
                    if (preg_match('/(sd|vd|nvme[0-9]n[0-9])\s/i', $line)) {
                        $parts = preg_split('/\s+/', trim($line));
                        $ioRead += (int)$parts[5] * 512; // sectors read to bytes
                        $ioWrite += (int)$parts[9] * 512; // sectors written to bytes
                        // We will sum all primary disks, but you could configure a specific one
                    }
                }

                $metrics = [
                    'cpu_usage' => floatval($cpu),
                    'ram_usage' => floatval($memoryUsage),
                    'disk_usage' => floatval($diskUsage),
                    'status' => 'online',
                    'system_uptime' => $systemUptime,
                    'load_average' => $loadAvg,
                    'network_rx' => $rx,
                    'network_tx' => $tx,
                    'disk_io_read' => $ioRead,
                    'disk_io_write' => $ioWrite,
                ];
            }

            // EXACTLY copy the working downtime pattern for uptime
            if (($metrics['status'] ?? 'offline') === 'online') {
                if (!$wasOnline || !$server->running_since) {
                    $server->running_since = now();
                    $server->last_down_at = null;
                    $server->status = 'online';
                    $server->save();
                }
                // This is the key part - exactly like downtime calculation
                $metrics['current_uptime'] = $server->running_since ? now()->diffInSeconds($server->running_since) : null;
            } else {
                if ($wasOnline || !$server->last_down_at) {
                    $server->last_down_at = now();
                    $server->running_since = null;
                    $server->status = 'offline';
                    $server->save();
                }
                // This is the working downtime calculation - don't touch it
                $metrics['current_downtime'] = $server->last_down_at ? now()->diffInSeconds($server->last_down_at) : null;
            }

            // Basic metrics that we know work
            $metrics['status'] = $server->status;
            $metrics['running_since'] = $server->running_since;
            $metrics['last_down_at'] = $server->last_down_at;

            // Calculate simple network health metrics (more reliable than speed)
            $metrics['network_health'] = 'unknown';
            $metrics['ping_response'] = 0;
            $metrics['network_activity'] = 'low';
            
            // Simple ping test for network health
            try {
                if ($this->isLocalhost($server->ip_address)) {
                    $metrics['network_health'] = 'excellent';
                    $metrics['ping_response'] = 1;
                } else {
                    // For remote servers, do a simple ping test
                    $pingResult = shell_exec("ping -c 1 -W 3 " . escapeshellarg($server->ip_address) . " 2>/dev/null");
                    if (strpos($pingResult, '1 received') !== false) {
                        $metrics['network_health'] = 'good';
                        $metrics['ping_response'] = 1;
                    } else {
                        $metrics['network_health'] = 'poor';
                        $metrics['ping_response'] = 0;
                    }
                }
            } catch (Exception $e) {
                $metrics['network_health'] = 'unknown';
                $metrics['ping_response'] = 0;
            }
            
            // Determine network activity level based on recent transfers
            if ($server->last_checked_at) {
                $rxDiff = $metrics['network_rx'] - $server->network_rx;
                $txDiff = $metrics['network_tx'] - $server->network_tx;
                $totalDiff = $rxDiff + $txDiff;
                
                if ($totalDiff > 1000000) { // > 1MB
                    $metrics['network_activity'] = 'high';
                } elseif ($totalDiff > 100000) { // > 100KB
                    $metrics['network_activity'] = 'medium';
                } else {
                    $metrics['network_activity'] = 'low';
                }
            }
            
            // Update last_checked_at
            \DB::table('servers')->where('id', $server->id)->update(['last_checked_at' => \DB::raw('CURRENT_TIMESTAMP')]);

            return $metrics;
        } catch (Exception $e) {
            // Leave the catch block exactly as is - it works
            $wasOnline = $server->status === 'online';
            
            if ($wasOnline || !$server->last_down_at) {
                $server->last_down_at = now();
                $server->running_since = null;
                $server->status = 'offline';
                $server->save();
            }
            
            return [
                'cpu_usage' => 0,
                'ram_usage' => 0,
                'disk_usage' => 0,
                'status' => 'offline',
                'error' => $e->getMessage(),
                'last_down_at' => $server->last_down_at,
                'current_downtime' => $server->last_down_at ? now()->diffInSeconds($server->last_down_at) : null,
                'network_rx' => 0,
                'network_tx' => 0,
                'network_speed' => 0,
                'disk_io_read' => 0,
                'disk_io_write' => 0,
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
            // Windows metrics with system uptime
            $cpu = shell_exec('powershell "(Get-Counter -Counter \"\\Processor(_Total)\\% Processor Time\" -SampleInterval 1 -MaxSamples 1).CounterSamples.CookedValue"');
            $memory = shell_exec('powershell "(Get-Counter -Counter \"\\Memory\\% Committed Bytes In Use\" -SampleInterval 1 -MaxSamples 1).CounterSamples.CookedValue"');
            $disk = shell_exec('powershell "Get-WmiObject Win32_LogicalDisk -Filter \"DeviceID=\'C:\'\" | ForEach-Object { ($_.Size - $_.FreeSpace) / $_.Size * 100 }"');
            
            // Final robust uptime calculation for Windows that returns total seconds as an integer
            $uptimeCmd = 'powershell "[int]((Get-Date) - (Get-CimInstance -ClassName Win32_OperatingSystem).LastBootUpTime).TotalSeconds"';
            $uptimeSeconds = floatval(trim(shell_exec($uptimeCmd)));
            $systemUptime = $this->formatUptimeFromSeconds($uptimeSeconds);

            // Network traffic for Windows is more complex and might require more specific commands.
            // For now, returning 0.
            return [
                'cpu_usage' => floatval(trim($cpu)),
                'ram_usage' => floatval(trim($memory)),
                'disk_usage' => floatval(trim($disk)),
                'system_uptime' => $systemUptime,
                'status' => 'online',
                'network_rx' => 0,
                'network_tx' => 0,
                'network_speed' => 0,
                'disk_io_read' => 0,
                'disk_io_write' => 0,
            ];
        } catch (\Throwable $e) {
            $metrics = [
                'cpu_usage' => 0,
                'ram_usage' => 0,
                'disk_usage' => 0,
                'status' => 'offline',
                'error' => $e->getMessage(),
                'network_rx' => 0,
                'network_tx' => 0,
                'network_speed' => 0,
                'disk_io_read' => 0,
                'disk_io_write' => 0,
            ];
            
            if ($server) {
                // Handle downtime tracking for failed localhost
                $wasOnline = $server->status === 'online';
                
                if ($wasOnline || !$server->last_down_at) {
                    // Server just went offline
                    if ($wasOnline === true && $server->running_since) {
                        // Calculate uptime that just ended
                        $uptimeDuration = now()->diffInSeconds($server->running_since);
                        $server->total_uptime_seconds += $uptimeDuration;
                    }
                    $server->last_down_at = now();
                    $server->running_since = null;
                    $server->status = 'offline';
                    $server->save();
                }
                
                $metrics['last_down_at'] = $server->last_down_at;
                $metrics['total_downtime_seconds'] = $server->total_downtime_seconds;
                $metrics['current_downtime'] = $server->last_down_at ? now()->diffInSeconds($server->last_down_at) : null;
            }
            
            return $metrics;
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
        $systemUptime = 'N/A';
        if (file_exists('/proc/uptime')) {
            $uptime_seconds = (int)floatval(explode(' ', file_get_contents('/proc/uptime'))[0]);
            $systemUptime = $this->formatUptimeFromSeconds($uptime_seconds);
        }
        // Load Average
        if (file_exists('/proc/loadavg')) {
            $loadAvg = trim(file_get_contents('/proc/loadavg'));
        }

        // Network traffic for local Linux - dynamically find the default interface
        $defaultInterface = trim(shell_exec("ip route | grep default | awk '{print $5}'"));
        if (empty($defaultInterface)) {
            $defaultInterface = 'eth0'; // Fallback
        }
        $netDev = file_get_contents('/proc/net/dev');
        $lines = explode("\n", $netDev);
        $rx = 0;
        $tx = 0;
        foreach ($lines as $line) {
            if (strpos($line, $defaultInterface . ':') !== false) { 
                $parts = preg_split('/\s+/', trim($line));
                $rx = (int)$parts[1];
                $tx = (int)$parts[9];
                break;
            }
        }

        // Disk I/O for local Linux
        $diskStats = file_get_contents('/proc/diskstats');
        $lines = explode("\n", $diskStats);
        $ioRead = 0;
        $ioWrite = 0;
        foreach ($lines as $line) {
            if (preg_match('/(sd|vd|nvme[0-9]n[0-9])\s/i', $line)) {
                $parts = preg_split('/\s+/', trim($line));
                $ioRead += (int)$parts[5] * 512;
                $ioWrite += (int)$parts[9] * 512;
            }
        }

        $metrics = [
            'cpu_usage' => $cpuUsage,
            'ram_usage' => $memoryUsage,
            'disk_usage' => $diskUsage,
            'status' => 'online',
            'system_uptime' => $systemUptime,
            'load_average' => $loadAvg,
            'network_rx' => $rx,
            'network_tx' => $tx,
            'network_speed' => 0, // Speed calculation will be handled in the main getMetrics function
            'disk_io_read' => $ioRead,
            'disk_io_write' => $ioWrite,
        ];
        
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
                
                // Use correct timezone for log creation
                $currentTime = \Carbon\Carbon::now('Asia/Kuala_Lumpur')->utc();
                
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
                    'created_at' => $currentTime,
                    'updated_at' => $currentTime,
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

    private function formatUptimeFromSeconds(float $seconds): string
    {
        if ($seconds < 1) {
            return '0s';
        }

        $days = floor($seconds / (3600 * 24));
        $secondsPart = $seconds % (3600 * 24);
        $hours = floor($secondsPart / 3600);
        $secondsPart %= 3600;
        $minutes = floor($secondsPart / 60);
        $remainingSeconds = floor($secondsPart % 60);

        $parts = [];
        if ($days > 0) {
            $parts[] = "{$days}d";
        }
        if ($hours > 0) {
            $parts[] = "{$hours}h";
        }
        if ($minutes > 0) {
            $parts[] = "{$minutes}m";
        }
        if ($remainingSeconds > 0) {
            $parts[] = "{$remainingSeconds}s";
        }

        if (empty($parts)) {
            return '0s';
        }

        return implode(' ', $parts);
    }
}
