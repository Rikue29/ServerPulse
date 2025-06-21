<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;
use App\Models\PerformanceLog;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $servers = Server::all();
        $selected_server_id = $request->input('server_id', $servers->first()->id ?? null);
        $selected_server = Server::find($selected_server_id);

        // Get fresh metrics from monitoring service for initial load
        $fresh_metrics = [];
        if ($selected_server) {
            $monitoringService = new \App\Services\ServerMonitoringService();
            $fresh_metrics = $monitoringService->getMetrics($selected_server);
        }

        // Calculate network health summary
        $network_health_summary = $this->calculateNetworkHealthSummary($selected_server);

        $summary = [
            'system_performance' => $fresh_metrics['cpu_usage'] ?? $selected_server->cpu_usage ?? 0,
            'network_health' => $network_health_summary['health_score'],
            'network_activity' => $network_health_summary['activity_level'],
            'current_network_activity' => 0, // Will be updated below
            'storage_usage' => $fresh_metrics['disk_usage'] ?? $selected_server->disk_usage ?? 0,
            'resource_allocation' => $fresh_metrics['ram_usage'] ?? $selected_server->ram_usage ?? 0,
            'system_uptime' => $selected_server->status === 'online' && $selected_server->running_since 
                ? \Carbon\CarbonInterval::seconds(now()->diffInSeconds($selected_server->running_since))->cascade()->forHumans(['short' => true])
                : '0s',
        ];

        // For the graph, we'll get the last 24 hours of performance logs for a specific server
        $chart_data = [
            'labels' => [],
            'cpu_load' => [],
            'memory_usage' => [],
            'network_activity' => [],
            'disk_io' => [],
            'disk_usage' => [],
            'response_time' => [],
            'network_throughput' => [],
        ];

        if ($selected_server_id) {
            // Get the most recent performance logs by ID to ensure we get the latest data
            $performanceLogs = PerformanceLog::where('server_id', $selected_server_id)
                       ->orderBy('id', 'desc')
                       ->limit(200) // Increased from 100 to 200 for better network throughput calculation
                       ->get()
                       ->reverse(); // Reverse to get chronological order for the graph

            $last_log = null;

            foreach ($performanceLogs as $log) {
                // Ensure the log timestamp is in Asia/Kuala_Lumpur timezone
                $logTime = $log->created_at->setTimezone('Asia/Kuala_Lumpur');
                $chart_data['labels'][] = $logTime->format('H:i');
                $chart_data['cpu_load'][] = $log->cpu_usage ?? 0;
                $chart_data['memory_usage'][] = $log->ram_usage ?? 0;
                $chart_data['disk_usage'][] = $log->disk_usage ?? 0;
                
                // For response time, only use logs with actual response time measurements
                $chart_data['response_time'][] = ($log->response_time && $log->response_time > 0) ? $log->response_time : 0;
                
                // Calculate network activity level (0-100 scale)
                if (isset($last_log)) {
                    // Ensure both timestamps are in the same timezone for accurate calculation
                    $logTime = $log->created_at->setTimezone('Asia/Kuala_Lumpur');
                    $lastLogTime = $last_log->created_at->setTimezone('Asia/Kuala_Lumpur');
                    $time_diff = abs($logTime->diffInSeconds($lastLogTime));
                    
                    if ($time_diff > 0) {
                        $rx_diff = max(0, ($log->network_rx ?? 0) - ($last_log->network_rx ?? 0));
                        $tx_diff = max(0, ($log->network_tx ?? 0) - ($last_log->network_tx ?? 0));
                        $total_transfer = $rx_diff + $tx_diff;
                        
                        // Convert to activity level (0-100)
                        if ($total_transfer > 1000000) { // > 1MB
                            $activity_level = 100;
                        } elseif ($total_transfer > 100000) { // > 100KB
                            $activity_level = 60;
                        } elseif ($total_transfer > 10000) { // > 10KB
                            $activity_level = 30;
                        } else {
                            $activity_level = 0;
                        }
                        $chart_data['network_activity'][] = $activity_level;
                        
                        // Calculate network throughput (bytes per second)
                        // Handle network counter reset (if total_transfer is 0 but we have current values, it might be a reset)
                        if ($total_transfer == 0 && (($log->network_rx ?? 0) > 0 || ($log->network_tx ?? 0) > 0)) {
                            $network_throughput = 0;
                        } else {
                            $network_throughput = $total_transfer / $time_diff;
                        }
                        
                        // Ensure minimum time difference to avoid division by zero or very small values
                        if ($time_diff < 1) {
                            $time_diff = 1; // Minimum 1 second
                            $network_throughput = $total_transfer / $time_diff;
                        }
                        
                        // Spike detection and smoothing for network throughput
                        // If throughput is more than 10x the average of previous values, cap it
                        if (!empty($chart_data['network_throughput'])) {
                            $recent_values = array_slice($chart_data['network_throughput'], -5); // Last 5 values
                            $average_throughput = array_sum($recent_values) / count($recent_values);
                            
                            // If current throughput is more than 10x the average, it's likely a spike
                            if ($average_throughput > 0 && $network_throughput > ($average_throughput * 10)) {
                                $network_throughput = $average_throughput * 2; // Cap at 2x average
                            }
                            
                            // Additional safety cap: if throughput > 1000 KB/s (1 MB/s), cap it
                            if ($network_throughput > 1000) {
                                $network_throughput = 1000;
                            }
                        }
                        
                        $chart_data['network_throughput'][] = round($network_throughput / 1024, 2); // KB/s
                        
                        // Calculate disk I/O speed in MB/s
                        $read_diff = ($log->disk_io_read ?? 0) - ($last_log->disk_io_read ?? 0);
                        $write_diff = ($log->disk_io_write ?? 0) - ($last_log->disk_io_write ?? 0);
                        
                        // Handle disk I/O counter reset (negative values indicate reset)
                        if ($read_diff < 0 || $write_diff < 0) {
                            $disk_io_speed = 0;
                        } else {
                            $disk_io_speed = ($read_diff + $write_diff) / $time_diff / 1024 / 1024; // MB/s
                        }
                        
                        $chart_data['disk_io'][] = round($disk_io_speed, 2);
                    } else {
                        $chart_data['network_activity'][] = 0;
                        $chart_data['disk_io'][] = 0;
                        $chart_data['network_throughput'][] = 0;
                    }
                } else {
                    // For the first log, use a baseline activity level based on current network bytes
                    $current_total = ($log->network_rx ?? 0) + ($log->network_tx ?? 0);
                    if ($current_total > 1000000) {
                        $activity_level = 100;
                    } elseif ($current_total > 100000) {
                        $activity_level = 60;
                    } elseif ($current_total > 10000) {
                        $activity_level = 30;
                    } else {
                        $activity_level = 0;
                    }
                    $chart_data['network_activity'][] = $activity_level;
                    $chart_data['disk_io'][] = 0;
                    $chart_data['network_throughput'][] = 0;
                }
                
                $last_log = $log;
            }
            
            // Set current network activity to the last calculated value from the graph
            if (!empty($chart_data['network_activity'])) {
                $summary['current_network_activity'] = end($chart_data['network_activity']);
            } else {
                $summary['current_network_activity'] = 0;
            }
        }

        // Handle AJAX requests for real-time updates
        if ($request->has('ajax') && $request->ajax) {
            return response()->json([
                'chart_data' => $chart_data,
                'summary' => $summary
            ]);
        }

        return view('analytics.index', [
            'summary' => $summary,
            'chart_data' => $chart_data,
            'servers' => $servers,
            'selected_server_id' => $selected_server_id,
            'selected_server' => $selected_server,
            'network_health_summary' => $network_health_summary
        ]);
    }

    private function calculateNetworkHealthSummary($server)
    {
        if (!$server) {
            return [
                'health_score' => 0,
                'activity_level' => 'unknown',
                'health_status' => 'unknown',
                'ping_response' => 0,
                'total_transfer' => 0
            ];
        }

        // Calculate health score based on server status and ping response
        $health_score = 0;
        $health_status = 'unknown';
        
        if ($server->status === 'online') {
            $health_score += 50;
            $health_status = 'online';
        }
        
        // Add points for ping response (if we had this data)
        // For now, assume good health if online
        if ($server->status === 'online') {
            $health_score += 50;
        }

        // Determine activity level
        $activity_level = 'low';
        if ($server->network_rx > 1000000 || $server->network_tx > 1000000) {
            $activity_level = 'high';
        } elseif ($server->network_rx > 100000 || $server->network_tx > 100000) {
            $activity_level = 'medium';
        }

        return [
            'health_score' => $health_score,
            'activity_level' => $activity_level,
            'health_status' => $health_status,
            'ping_response' => $server->status === 'online' ? 1 : 0,
            'total_transfer' => ($server->network_rx ?? 0) + ($server->network_tx ?? 0)
        ];
    }

    private function getCurrentSystemUptime($server)
    {
        if (!$server) {
            return '0s';
        }

        // For localhost, get the actual system uptime
        if ($this->isLocalhost($server->ip_address)) {
            return $this->getLocalSystemUptime();
        }

        // For remote servers, use the stored running_since if available
        if ($server->status === 'online' && $server->running_since) {
            $uptime = now()->diffInSeconds($server->running_since);
            return \Carbon\CarbonInterval::seconds($uptime)->cascade()->forHumans(['short' => true]);
        }

        return '0s';
    }

    private function getLocalSystemUptime()
    {
        if (PHP_OS_FAMILY === 'Linux') {
            if (file_exists('/proc/uptime')) {
                $uptime_seconds = (int)floatval(explode(' ', file_get_contents('/proc/uptime'))[0]);
                return $this->formatUptimeFromSeconds($uptime_seconds);
            }
        } else {
            // Windows
            try {
                $uptimeCmd = 'powershell "[int]((Get-Date) - (Get-CimInstance -ClassName Win32_OperatingSystem).LastBootUpTime).TotalSeconds"';
                $uptimeSeconds = floatval(trim(shell_exec($uptimeCmd)));
                return $this->formatUptimeFromSeconds($uptimeSeconds);
            } catch (\Throwable $e) {
                return '0s';
            }
        }
        
        return '0s';
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

    private function isLocalhost($ip)
    {
        return in_array($ip, ['127.0.0.1', 'localhost', '::1']);
    }
}
