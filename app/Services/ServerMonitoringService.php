<?php

namespace App\Services;

use App\Models\Server;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;
use Exception;
use Illuminate\Support\Facades\Log;

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
            // For local testing, we'll use direct commands instead of SSH
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
            
            // Try to connect with SSH key first
            if (!empty($server->ssh_key)) {
                $key = PublicKeyLoader::load($server->ssh_key);
                $success = $ssh->login($server->ssh_user, $key);
            } else {
                // Fall back to password authentication
                $success = $ssh->login($server->ssh_user, $server->ssh_password);
            }

            if (!$success) {
                Log::error("[Monitoring] SSH login failed for {$server->ip_address} as {$server->ssh_user}");
                throw new Exception('SSH login failed');
            }

            // Detect Linux distribution
            $distro = strtolower(trim($ssh->exec('cat /etc/os-release | grep -w "ID" | cut -d= -f2')));

            // Get CPU usage based on distribution
            switch ($distro) {
                case 'ubuntu':
                case 'debian':
                case 'centos':
                case 'rhel':
                    $cpu = trim($ssh->exec("top -bn1 | grep 'Cpu(s)' | awk '{print $2}'"));
                    break;
                default:
                    // Fallback method that works on most Linux systems
                    $cpu = trim($ssh->exec("grep 'cpu ' /proc/stat | awk '{usage=($2+$4)*100/($2+$4+$5)} END {print usage}'"));
            }
            
            // Get memory usage (works on most Linux distributions)
            $memInfo = $ssh->exec('free');
            if (strpos($memInfo, 'available') !== false) {
                // Modern versions of free command
                $memoryTotal = trim($ssh->exec("free | grep Mem | awk '{print $2}'"));
                $memoryAvailable = trim($ssh->exec("free | grep Mem | awk '{print $7}'"));
                $memoryUsage = ($memoryTotal - $memoryAvailable) / $memoryTotal * 100;
            } else {
                // Older versions
                $memoryTotal = trim($ssh->exec("free | grep Mem | awk '{print $2}'"));
                $memoryUsed = trim($ssh->exec("free | grep Mem | awk '{print $3}'"));
                $memoryUsage = ($memoryUsed / $memoryTotal) * 100;
            }

            // Get disk usage (works on all Linux distributions)
            $diskUsage = trim($ssh->exec("df / | tail -1 | awk '{print $5}' | sed 's/%//'"));

            // Get additional system information
            $uptime = trim($ssh->exec('uptime -p'));
            $loadAvg = trim($ssh->exec("uptime | awk -F'load average:' '{print $2}'"));

            Log::info("[Monitoring] Metrics for {$server->ip_address}: CPU={$cpu}, RAM={$memoryUsage}, DISK={$diskUsage}");
            
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
     * Get metrics for localhost (Windows/Laragon environment)
     */
    private function getLocalMetrics()
    {
        try {
            // Get CPU usage using PowerShell - use lighter commands for faster response
            $cpu = shell_exec('powershell "Get-WmiObject Win32_Processor | Measure-Object -Property LoadPercentage -Average | Select-Object -ExpandProperty Average"');
            $cpuUsage = floatval(trim($cpu));

            // Get memory usage using PowerShell
            $memory = shell_exec('powershell "(Get-Counter \'\Memory\% Committed Bytes In Use\' -SampleInterval 1 -MaxSamples 1).CounterSamples.CookedValue"');
            $memoryUsage = floatval(trim($memory));

            // Get disk usage using PowerShell
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
}