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
    // Add properties to cache metrics and reduce load when polling frequently
    private $lastLocalMetricsTimestamp = 0;
    private $cachedLocalMetrics = null;
    private $lastRemoteMetricsTimestamp = [];
    private $cachedRemoteMetrics = [];
    
    // Cache threshold in seconds - don't poll more frequently than this
    private $localCacheThreshold = 2; // For local server
    private $remoteCacheThreshold = 4; // For remote servers

    /**
     * Get server metrics via SSH
     */
    public function getMetrics(Server $server)
    {
        try {
            if ($this->isLocalhost($server->ip_address)) {
                // Check if we need fresh metrics or can use cached ones
                $currentTime = time();
                if ($this->cachedLocalMetrics !== null && 
                    ($currentTime - $this->lastLocalMetricsTimestamp) < $this->localCacheThreshold) {
                    Log::info("[Monitoring] Using cached metrics for localhost (last updated " . 
                              ($currentTime - $this->lastLocalMetricsTimestamp) . " seconds ago)");
                    return $this->cachedLocalMetrics;
                }
                
                $metrics = $this->getLocalMetrics();
                $this->cachedLocalMetrics = $metrics;
                $this->lastLocalMetricsTimestamp = $currentTime;
                return $metrics;
            }
            
            // For remote servers, check cache first
            $serverId = $server->id;
            $currentTime = time();
            if (isset($this->cachedRemoteMetrics[$serverId]) && 
                isset($this->lastRemoteMetricsTimestamp[$serverId]) && 
                ($currentTime - $this->lastRemoteMetricsTimestamp[$serverId]) < $this->remoteCacheThreshold) {
                Log::info("[Monitoring] Using cached metrics for {$server->ip_address} (last updated " . 
                          ($currentTime - $this->lastRemoteMetricsTimestamp[$serverId]) . " seconds ago)");
                return $this->cachedRemoteMetrics[$serverId];
            }

            Log::info("[Monitoring] Attempting SSH to {$server->ip_address}:{$server->ssh_port} as {$server->ssh_user}");
            
            // For remote servers, use SSH
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
            $metrics = [
                'cpu_usage' => floatval($cpu),
                'ram_usage' => floatval($memoryUsage),
                'disk_usage' => floatval($diskUsage),
                'status' => 'online',
                'uptime' => $uptime,
                'load_average' => $loadAvg
            ];
            
            // Cache the metrics for this server
            $this->cachedRemoteMetrics[$server->id] = $metrics;
            $this->lastRemoteMetricsTimestamp[$server->id] = time();
            
            return $metrics;
        } catch (Exception $e) {
            $errorMetrics = [
                'cpu_usage' => 0,
                'ram_usage' => 0,
                'disk_usage' => 0,
                'status' => 'offline',
                'error' => $e->getMessage()
            ];
            
            // Cache even error results to avoid hammering servers that are down
            if (!$this->isLocalhost($server->ip_address)) {
                $this->cachedRemoteMetrics[$server->id] = $errorMetrics;
                $this->lastRemoteMetricsTimestamp[$server->id] = time();
            } else {
                $this->cachedLocalMetrics = $errorMetrics;
                $this->lastLocalMetricsTimestamp = time();
            }
            
            return $errorMetrics;
        }
    }

    /**
     * Get metrics for localhost
     */
    private function getLocalMetrics()
    {
        try {
            // We're always in a Linux environment in Docker.
            
            // CPU Usage
            $cpuStat1 = explode(' ', trim(shell_exec("cat /proc/stat | grep '^cpu '")));
            sleep(1);
            $cpuStat2 = explode(' ', trim(shell_exec("cat /proc/stat | grep '^cpu '")));

            $prevIdle = (float)($cpuStat1[4] ?? 0) + (float)($cpuStat1[5] ?? 0);
            $idle = (float)($cpuStat2[4] ?? 0) + (float)($cpuStat2[5] ?? 0);

            $prevTotal = array_sum(array_slice($cpuStat1, 1));
            $total = array_sum(array_slice($cpuStat2, 1));

            $totalDiff = $total - $prevTotal;
            $idleDiff = $idle - $prevIdle;

            $cpuUsage = $totalDiff > 0 ? 100 * ($totalDiff - $idleDiff) / $totalDiff : 0;

            // Memory Usage
            $memInfo = shell_exec('free -m');
            preg_match('/Mem:\s+(\d+)\s+(\d+)\s+(\d+)/', $memInfo, $matches);
            $totalMem = $matches[1] ?? 0;
            $usedMem = $matches[2] ?? 0;
            $memoryUsage = $totalMem > 0 ? ($usedMem / $totalMem) * 100 : 0;

            // Disk Usage
            $diskUsage = (float)shell_exec("df / | tail -1 | awk '{print $5}' | sed 's/%//'");

            return [
                'cpu_usage' => round($cpuUsage, 2),
                'ram_usage' => round($memoryUsage, 2),
                'disk_usage' => round($diskUsage, 2),
                'status' => 'online'
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Error getting local metrics: " . $e->getMessage());
            return [
                'cpu_usage' => 0,
                'ram_usage' => 0,
                'disk_usage' => 0,
                'status' => 'error'
            ];
        }
    }

    /**
     * Check if the IP address is localhost
     */
    private function isLocalhost($ip)
    {
        return in_array($ip, ['127.0.0.1', 'localhost', '::1', 'host.docker.internal']);
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