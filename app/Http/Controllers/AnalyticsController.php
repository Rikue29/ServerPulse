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

        // Calculate network health summary
        $network_health_summary = $this->calculateNetworkHealthSummary($selected_server);

        $summary = [
            'system_performance' => $selected_server->cpu_usage ?? 0,
            'network_health' => 0,
            'network_activity' => 0,
            'current_network_activity' => 0,
            'storage_usage' => $selected_server->disk_usage ?? 0,
            'resource_allocation' => $selected_server->ram_usage ?? 0,
            'network_throughput' => 0,
            'response_time' => 0,
            'system_uptime' => $selected_server->system_uptime ?? '0h 0m 0s',
            'disk_io' => 0
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
            'system_uptime' => [],
            'network_throughput' => [],
        ];

        if ($selected_server_id) {
            // Get the most recent performance logs by created_at to ensure we get the latest data
            $performanceLogs = PerformanceLog::where('server_id', $selected_server_id)
                       ->orderBy('created_at', 'desc')
                       ->limit(100) // Limit to most recent 100 logs
                       ->get()
                       ->reverse(); // Reverse to get chronological order for the graph

            $last_log = null;

            foreach ($performanceLogs as $log) {
                // Use the actual log timestamp in 'H:i:s' format for the X-axis
                $logTime = $log->created_at->setTimezone('Asia/Kuala_Lumpur');
                $chart_data['labels'][] = $logTime->format('h:i:s A');
                $chart_data['cpu_load'][] = $log->cpu_usage ?? 0;
                $chart_data['memory_usage'][] = $log->ram_usage ?? 0;
                $chart_data['disk_usage'][] = $log->disk_usage ?? 0;
                
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
                        
                        // Calculate disk I/O speed in MB/s
                        $read_diff = ($log->disk_io_read ?? 0) - ($last_log->disk_io_read ?? 0);
                        $write_diff = ($log->disk_io_write ?? 0) - ($last_log->disk_io_write ?? 0);
                        $disk_io_speed = ($read_diff + $write_diff) / $time_diff / 1024 / 1024; // MB/s
                        $chart_data['disk_io'][] = round($disk_io_speed, 2);
                        
                        // Calculate network throughput (bytes per second)
                        $network_throughput = $total_transfer / $time_diff;
                        $chart_data['network_throughput'][] = round($network_throughput / 1024, 2); // KB/s
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
                
                // Add response time from logs if available, otherwise use 0
                $chart_data['response_time'][] = $log->response_time ?? 0;
                
                // Convert system uptime string to hours if possible
                $uptimeHours = 0;
                if ($log->system_uptime) {
                    // Improved uptime parsing to handle edge cases
                    preg_match('/(\d+)h\s+(\d+)m\s+(\d+)s/', $log->system_uptime, $matches);
                    if (count($matches) >= 4) {
                        // Round to 2 decimal places to prevent minor fluctuations
                        $uptimeHours = round(
                            intval($matches[1]) + 
                            (intval($matches[2]) / 60) + 
                            (intval($matches[3]) / 3600),
                            2
                        );
                    }
                }
                $chart_data['system_uptime'][] = $uptimeHours;
                
                $last_log = $log;
            }
            
            // Set current network activity to the last calculated value from the graph
            if (!empty($chart_data['network_activity'])) {
                $summary['current_network_activity'] = end($chart_data['network_activity']);
            } else {
                $summary['current_network_activity'] = 0;
            }
        }

        return view('analytics.index', [
            'summary' => $summary,
            'chart_data' => $chart_data,
            'servers' => $servers,
            'selected_server_id' => $selected_server_id,
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

    public function getServerStatus(Request $request)
    {
        $selected_server_id = $request->input('server_id', Server::first()->id ?? null);
        $selected_server = Server::find($selected_server_id);

        // Get the two most recent logs to calculate disk I/O
        $recentLogs = PerformanceLog::where('server_id', $selected_server_id)
            ->orderBy('created_at', 'desc')
            ->limit(2)
            ->get();

        $disk_io = 0;
        if ($recentLogs->count() == 2) {
            $current = $recentLogs->first();
            $previous = $recentLogs->last();
            $time_diff = $current->created_at->diffInSeconds($previous->created_at);
            
            if ($time_diff > 0) {
                $read_diff = ($current->disk_io_read ?? 0) - ($previous->disk_io_read ?? 0);
                $write_diff = ($current->disk_io_write ?? 0) - ($previous->disk_io_write ?? 0);
                $disk_io = ($read_diff + $write_diff) / $time_diff / 1024 / 1024; // Convert to MB/s
            }
        }

        $summary = [
            'system_performance' => $selected_server->cpu_usage ?? 0,
            'network_health' => 0,
            'network_activity' => 0,
            'current_network_activity' => 0,
            'storage_usage' => $selected_server->disk_usage ?? 0,
            'resource_allocation' => $selected_server->ram_usage ?? 0,
            'network_throughput' => 0,
            'response_time' => 0,
            'system_uptime' => $selected_server->system_uptime ?? '0h 0m 0s',
            'disk_io' => round($disk_io, 2)
        ];

        // Get performance logs for chart data
        $performanceLogs = PerformanceLog::where('server_id', $selected_server_id)
            ->orderBy('created_at', 'desc')
            ->limit(100)
            ->get()
            ->reverse();
    }
}
