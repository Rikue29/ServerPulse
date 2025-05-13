<?php

namespace App\Services;

use App\Models\Log;
use Illuminate\Support\Facades\Http;

class LogMonitoringService
{
    public function monitorCPU($serverName, $cpuUsage)
    {
        if ($cpuUsage > 90) {
            $this->createLog('CPU', 'System', $serverName, 'Critical', "CPU usage exceeded 90% threshold");
        } elseif ($cpuUsage > 80) {
            $this->createLog('CPU', 'System', $serverName, 'Warning', "CPU usage above 80%");
        }
    }

    public function monitorMemory($serverName, $memoryUsage)
    {
        if ($memoryUsage > 90) {
            $this->createLog('Memory', 'System', $serverName, 'Critical', "Memory usage exceeded 90% threshold");
        } elseif ($memoryUsage > 80) {
            $this->createLog('Memory', 'System', $serverName, 'Warning', "Memory usage approaching capacity");
        }
    }

    public function monitorDiskSpace($serverName, $diskUsage)
    {
        if ($diskUsage > 90) {
            $this->createLog('Disk', 'System', $serverName, 'Critical', "Disk usage exceeded 90% threshold");
        } elseif ($diskUsage > 80) {
            $this->createLog('Disk', 'System', $serverName, 'Warning', "Disk space running low");
        }
    }

    public function logSystemUpdate($serverName, $success)
    {
        $status = $success ? 'Info' : 'Error';
        $message = $success ? 'Scheduled system update completed successfully' : 'System update failed';
        $this->createLog('System', 'Update', $serverName, $status, $message);
    }

    private function createLog($type, $category, $server, $status, $message)
    {
        Log::create([
            'timestamp' => now(),
            'type' => $type,
            'category' => $category,
            'server' => $server,
            'status' => $status,
            'message' => $message
        ]);
    }
} 