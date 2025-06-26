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

                // Detect OS type - simplified detection
                $osType = 'linux';
                $isWindows = false;
                
                // Quick Windows detection
                $powershellTest = trim($ssh->exec('powershell -Command "Write-Host \'Windows\'"'));
                if (strpos($powershellTest, 'Windows') !== false) {
                    $isWindows = true;
                    $osType = 'windows';
                }

                if ($osType === 'windows') {
                    // Optimized Windows monitoring - single PowerShell command
                    $powershellScript = '
$cpu = (Get-Counter -Counter "\Processor(_Total)\% Processor Time" -SampleInterval 1 -MaxSamples 1).CounterSamples.CookedValue
$memory = (Get-Counter -Counter "\Memory\% Committed Bytes In Use" -SampleInterval 1 -MaxSamples 1).CounterSamples.CookedValue
$disk = Get-WmiObject Win32_LogicalDisk -Filter "DeviceID=\'C:\'" | ForEach-Object { [math]::Round(($_.Size - $_.FreeSpace) / $_.Size * 100, 2) }
$uptime = [int]((Get-Date) - (Get-CimInstance -ClassName Win32_OperatingSystem).LastBootUpTime).TotalSeconds
$network = Get-NetAdapterStatistics | Where-Object {$_.Name -like "*Ethernet*" -or $_.Name -like "*Wi-Fi*"} | Select-Object -First 1 | ForEach-Object { $_.ReceivedBytes, $_.SentBytes -join " " }
$diskIO = Get-Counter -Counter "\PhysicalDisk(_Total)\Disk Reads/sec","\PhysicalDisk(_Total)\Disk Writes/sec" -SampleInterval 1 -MaxSamples 1 | ForEach-Object {$_.CounterSamples | ForEach-Object {$_.CookedValue}}
Write-Output "$cpu|$memory|$disk|$uptime|$network|$diskIO"
';
                    
                    $result = trim($ssh->exec('powershell -Command "' . $powershellScript . '"'));
                    $parts = explode('|', $result);
                    
                    if (count($parts) >= 6) {
                        $cpu = trim($parts[0]);
                        $memory = trim($parts[1]);
                        $diskUsage = trim($parts[2]);
                        $uptimeSeconds = floatval(trim($parts[3]));
                        $networkStats = trim($parts[4]);
                        $diskIO = trim($parts[5]);
                        
                        $systemUptime = $this->formatUptimeFromSeconds($uptimeSeconds);
                        $memoryUsage = floatval($memory);
                        
                        // Parse network stats
                        $networkParts = explode(' ', $networkStats);
                        $rx = isset($networkParts[0]) ? (int)$networkParts[0] : 0;
                        $tx = isset($networkParts[1]) ? (int)$networkParts[1] : 0;
                        
                        // Parse disk I/O
                        $ioParts = explode("\n", $diskIO);
                        $ioRead = isset($ioParts[0]) ? (float)$ioParts[0] * 512 : 0;
                        $ioWrite = isset($ioParts[1]) ? (float)$ioParts[1] * 512 : 0;
                    } else {
                        // Fallback to individual commands if combined approach fails
                        $cpu = trim($ssh->exec('powershell -Command "(Get-Counter -Counter \"\\Processor(_Total)\\% Processor Time\" -SampleInterval 1 -MaxSamples 1).CounterSamples.CookedValue"'));
                        $memory = trim($ssh->exec('powershell -Command "(Get-Counter -Counter \"\\Memory\\% Committed Bytes In Use\" -SampleInterval 1 -MaxSamples 1).CounterSamples.CookedValue"'));
                        $diskUsage = trim($ssh->exec('powershell -Command "Get-WmiObject Win32_LogicalDisk -Filter \"DeviceID=\'C:\'\" | ForEach-Object { [math]::Round(($_.Size - $_.FreeSpace) / $_.Size * 100, 2) }"'));
                        $uptimeSeconds = floatval(trim($ssh->exec('powershell -Command "[int]((Get-Date) - (Get-CimInstance -ClassName Win32_OperatingSystem).LastBootUpTime).TotalSeconds"')));
                        $systemUptime = $this->formatUptimeFromSeconds($uptimeSeconds);
                        $memoryUsage = floatval($memory);
                        
                        // Windows network stats
                        $networkStats = trim($ssh->exec('powershell -Command "Get-NetAdapterStatistics | Where-Object {$_.Name -like \'*Ethernet*\' -or $_.Name -like \'*Wi-Fi*\'} | Select-Object -First 1 | ForEach-Object { $_.ReceivedBytes, $_.SentBytes -join \' \' }"'));
                        $networkParts = explode(' ', $networkStats);
                        $rx = isset($networkParts[0]) ? (int)$networkParts[0] : 0;
                        $tx = isset($networkParts[1]) ? (int)$networkParts[1] : 0;
                        
                        // Windows disk I/O
                        $diskIO = trim($ssh->exec('powershell -Command "Get-Counter -Counter \"\\PhysicalDisk(_Total)\\Disk Reads/sec\",\"\\PhysicalDisk(_Total)\\Disk Writes/sec\" -SampleInterval 1 -MaxSamples 1 | ForEach-Object {$_.CounterSamples | ForEach-Object {$_.CookedValue}}"'));
                        $ioParts = explode("\n", $diskIO);
                        $ioRead = isset($ioParts[0]) ? (float)$ioParts[0] * 512 : 0;
                        $ioWrite = isset($ioParts[1]) ? (float)$ioParts[1] * 512 : 0;
                    }
                    
                    $loadAvg = 'N/A'; // Windows doesn't have load average like Linux
                    
                } else {
                    // Optimized Linux monitoring - single commands where possible
                    $cpu = trim($ssh->exec("grep 'cpu ' /proc/stat | awk '{usage=($2+$4)*100/($2+$4+$5)} END {print usage}'"));
                    
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

                    // Network traffic - optimized
                    $defaultInterface = trim($ssh->exec("ip route | grep default | awk '{print $5}'"));
                    if (empty($defaultInterface)) {
                        $defaultInterface = 'eth0';
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

                    // Disk I/O - optimized
                    $diskStats = $ssh->exec('cat /proc/diskstats');
                    $lines = explode("\n", $diskStats);
                    foreach ($lines as $line) {
                        if (preg_match('/(sd|vd|nvme[0-9]n[0-9]|vda|vdb|vdc|vdd|vde|vdf|vdg|vdh|vdi|vdj|vdk|vdl|vdm|vdn|vdo|vdp|vdq|vdr|vds|vdt|vdu|vdv|vdw|vdx|vdy|vdz)\s/i', $line)) {
                            $parts = preg_split('/\s+/', trim($line));
                            $ioRead += (int)$parts[5] * 512;
                            $ioWrite += (int)$parts[9] * 512;
                        }
                    }
                }

                // Handle network counter reset (server restart/offline)
                $previousRx = $server->network_rx ?? 0;
                $previousTx = $server->network_tx ?? 0;
                
                if ($rx < $previousRx || $tx < $previousTx) {
                    // Server has likely restarted - reset counters
                    $rx = 0;
                    $tx = 0;
                    // Reset running_since to now since this indicates a restart
                    $server->running_since = now();
                    $server->save();
                }

                // Handle disk I/O counter reset (server restart/offline)
                $previousRead = $server->disk_io_read ?? 0;
                $previousWrite = $server->disk_io_write ?? 0;
                
                if ($ioRead < $previousRead || $ioWrite < $previousWrite) {
                    // Server has likely restarted - reset counters
                    $ioRead = 0;
                    $ioWrite = 0;
                }

                // Check if uptime indicates a server restart
                $previousUptime = $this->parseUptimeToSeconds($server->system_uptime ?? '0h 0m 0s');
                $currentUptime = $uptimeSeconds;
                
                if ($currentUptime < $previousUptime) {
                    // Server has restarted - reset running_since
                    $server->running_since = now();
                    $server->save();
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
                $metrics['current_uptime'] = $server->running_since ? now()->diffInSeconds($server->running_since) : null;
                
                // Force status to online since we successfully got metrics
                $server->status = 'online';
                $metrics['status'] = 'online';
                $server->save();
            } else {
                if ($wasOnline || !$server->last_down_at) {
                    $server->last_down_at = now();
                    $server->running_since = null;
                    $server->status = 'offline';
                    $server->save();
                }
                $metrics['current_downtime'] = $server->last_down_at ? now()->diffInSeconds($server->last_down_at) : null;
            }

            // Basic metrics that we know work
            $metrics['status'] = $server->status;
            $metrics['running_since'] = $server->running_since;
            $metrics['last_down_at'] = $server->last_down_at;
            // Add formatted downtime for human-readable display
            if ($server->status === 'offline' && $server->last_down_at) {
                $metrics['formatted_downtime'] = $this->formatUptimeFromSeconds($metrics['current_downtime'] ?? 0);
            }

            // Calculate simple network health metrics
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
                
                if ($totalDiff > 1000000) {
                    $metrics['network_activity'] = 'high';
                } elseif ($totalDiff > 100000) {
                    $metrics['network_activity'] = 'medium';
                } else {
                    $metrics['network_activity'] = 'low';
                }
            }
            
            // Update last_checked_at
            \DB::table('servers')->where('id', $server->id)->update(['last_checked_at' => \DB::raw('CURRENT_TIMESTAMP')]);

            return $metrics;
        } catch (Exception $e) {
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
                'disk_usage' => 0, // Set disk usage to 0 when offline
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
            $cpu = shell_exec('powershell "(Get-Counter -Counter \"\\Processor(_Total)\\% Processor Time\" -SampleInterval 1 -MaxSamples 1).CounterSamples.CookedValue"');
            $memory = shell_exec('powershell "(Get-Counter -Counter \"\\Memory\\% Committed Bytes In Use\" -SampleInterval 1 -MaxSamples 1).CounterSamples.CookedValue"');
            $diskUsage = shell_exec('powershell "Get-WmiObject Win32_LogicalDisk -Filter \"DeviceID=\'C:\'\" | ForEach-Object { ($_.Size - $_.FreeSpace) / $_.Size * 100 }"');
            
            $uptimeCmd = 'powershell "[int]((Get-Date) - (Get-CimInstance -ClassName Win32_OperatingSystem).LastBootUpTime).TotalSeconds"';
            $uptimeSeconds = floatval(trim(shell_exec($uptimeCmd)));
            $systemUptime = $this->formatUptimeFromSeconds($uptimeSeconds);

            return [
                'cpu_usage' => floatval(trim($cpu)),
                'ram_usage' => floatval(trim($memory)),
                'disk_usage' => floatval(trim($diskUsage)),
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
                $wasOnline = $server->status === 'online';
                
                if ($wasOnline || !$server->last_down_at) {
                    if ($wasOnline === true && $server->running_since) {
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
     * Get metrics for Linux localhost - optimized without delays
     */
    private function getLinuxLocalMetrics(Server $server = null)
    {
        $cpuUsage = 0;
        $memoryUsage = 0;
        $diskUsage =
        $uptime = '';
        $loadAvg = '';

        // CPU Usage - optimized without delay
        if (file_exists('/proc/stat')) {
            $stat1 = file_get_contents('/proc/stat');
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

        // Network traffic for local Linux - optimized
        $defaultInterface = trim(shell_exec("ip route | grep default | awk '{print $5}'"));
        if (empty($defaultInterface)) {
            $defaultInterface = 'eth0';
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

        // Handle network counter reset (server restart/offline)
        if ($server) {
            $previousRx = $server->network_rx ?? 0;
            $previousTx = $server->network_tx ?? 0;
            
            if ($rx < $previousRx || $tx < $previousTx) {
                $rx = 0;
                $tx = 0;
            }
        }

        // Disk I/O for local Linux - optimized
        $diskStats = file_get_contents('/proc/diskstats');
        $lines = explode("\n", $diskStats);
        $ioRead = 0;
        $ioWrite = 0;
        foreach ($lines as $line) {
            if (preg_match('/(sd|vd[ab]|nvme[0-9]n[0-9])\s/i', $line)) {
                $parts = preg_split('/\s+/', trim($line));
                if (count($parts) >= 14) {  // Ensure we have enough fields
                    $ioRead += (int)$parts[5] * 512;  // sectors read * 512 bytes
                    $ioWrite += (int)$parts[9] * 512;  // sectors written * 512 bytes
                }
            }
        }

        // Handle disk I/O counter reset (server restart/offline)
        if ($server) {
            $previousRead = $server->disk_io_read ?? 0;
            $previousWrite = $server->disk_io_write ?? 0;
            
            if ($ioRead < $previousRead || $ioWrite < $previousWrite) {
                $ioRead = 0;
                $ioWrite = 0;
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
            'network_speed' => 0,
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
                $currentTime = \Carbon\Carbon::now('Asia/Kuala_Lumpur')->utc();

                // Use AlertController to create/update the alert (which will send email)
                try {
                    $alertController = new \App\Http\Controllers\AlertController();
                    $request = new \Illuminate\Http\Request([
                        'threshold_id' => $threshold->id,
                        'server_id' => $server->id,
                        'metric_value' => $currentValue,
                    ]);
                    
                    $response = $alertController->trigger($request);
                    $responseData = json_decode($response->getContent(), true);
                    
                    \Illuminate\Support\Facades\Log::info('Alert created via controller', [
                        'response' => $responseData,
                        'metric_type' => $threshold->metric_type,
                        'value' => $currentValue
                    ]);
                    
                    if (isset($responseData['alert'])) {
                        $alert = \App\Models\Alert::find($responseData['alert']['id']);
                        // Dispatch browser event for new alert (for banner)
                        if (function_exists('broadcast') && $alert) {
                            broadcast(new \App\Events\NewAlertCreated($alert));
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to create alert via controller', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                }

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
        
        if ($exceedPercentage >= 50) {
            return 'critical';
        } elseif ($exceedPercentage >= 25) {
            return 'error';
        } elseif ($exceedPercentage >= 10) {
            return 'warning';
        } else {
            return 'notice';
        }
    }

    private function formatUptimeFromSeconds(float $seconds): string
    {
        // Round seconds to prevent fluctuations
        $seconds = round($seconds);
        
        $hours = floor($seconds / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $remainingSeconds = $seconds % 60;

        return sprintf("%dh %dm %ds", $hours, $minutes, $remainingSeconds);
    }

    /**
     * Measure response time in milliseconds using curl - optimized
     */
    private function measureResponseTime($ipAddress): float
    {
        try {
            if ($this->isLocalhost($ipAddress)) {
                return 0.1;
            }

            // Optimized response time measurement
            $startTime = microtime(true);
            
            // Try to connect to the server on common ports
            $ports = [22, 80, 443];
            $responseTime = 999.9;
            
            foreach ($ports as $port) {
                $curlCmd = "curl -s -o /dev/null -w '%{time_total}' --connect-timeout 1 --max-time 2 http://{$ipAddress}:{$port} 2>/dev/null";
                $result = shell_exec($curlCmd);
                
                if ($result && is_numeric($result)) {
                    $responseTime = (float)$result * 1000;
                    break;
                }
            }
            
            return $responseTime;
            
        } catch (Exception $e) {
            return 999.9;
        }
    }

    /**
     * Parse uptime string to seconds
     */
    private function parseUptimeToSeconds(string $uptime): float
    {
        $hours = 0;
        $minutes = 0;
        $seconds = 0;

        if (preg_match('/(\d+)h/', $uptime, $matches)) {
            $hours = (int)$matches[1];
        }
        if (preg_match('/(\d+)m/', $uptime, $matches)) {
            $minutes = (int)$matches[1];
        }
        if (preg_match('/(\d+)s/', $uptime, $matches)) {
            $seconds = (int)$matches[1];
        }

        return ($hours * 3600) + ($minutes * 60) + $seconds;
    }
}
