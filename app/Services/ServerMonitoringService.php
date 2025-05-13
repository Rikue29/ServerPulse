<?php

namespace App\Services;

use App\Models\Server;
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
            // For local testing, we'll use direct commands instead of SSH
            if ($this->isLocalhost($server->ip_address)) {
                return $this->getLocalMetrics();
            }

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
            // Get CPU usage using PowerShell
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