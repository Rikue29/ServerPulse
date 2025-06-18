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

        $summary = [
            'system_performance' => $selected_server->cpu_usage ?? 0,
            'network_usage' => $selected_server->network_speed ?? 0,
            'storage_usage' => $selected_server->disk_usage ?? 0,
            'resource_allocation' => $selected_server->ram_usage ?? 0,
        ];

        // For the graph, we'll get the last 24 hours of logs for a specific server
        $chart_data = [
            'labels' => [],
            'cpu_load' => [],
            'memory_usage' => [],
            'network_traffic' => [],
            'disk_io' => [],
        ];

        if ($selected_server_id) {
            $performance_logs = PerformanceLog::where('server_id', $selected_server_id)
                       ->where('created_at', '>=', now()->subDay())
                       ->orderBy('created_at', 'asc')
                       ->get();
            
            $last_log = null;

            foreach ($performance_logs as $log) {
                $chart_data['labels'][] = $log->created_at->format('H:i');
                $chart_data['cpu_load'][] = $log->cpu_usage;
                $chart_data['memory_usage'][] = $log->ram_usage;

                if ($last_log) {
                    $time_diff = $log->created_at->diffInSeconds($last_log->created_at);
                    if ($time_diff > 0) {
                        // Network speed in MB/s
                        $net_rx_diff = $log->network_rx - $last_log->network_rx;
                        $net_tx_diff = $log->network_tx - $last_log->network_tx;
                        $network_speed = ($net_rx_diff + $net_tx_diff) / $time_diff / 1024 / 1024;
                        $chart_data['network_traffic'][] = round($network_speed, 2);

                        // Disk I/O in MB/s
                        $disk_read_diff = $log->disk_io_read - $last_log->disk_io_read;
                        $disk_write_diff = $log->disk_io_write - $last_log->disk_io_write;
                        $disk_io_speed = ($disk_read_diff + $disk_write_diff) / $time_diff / 1024 / 1024;
                        $chart_data['disk_io'][] = round($disk_io_speed, 2);
                    } else {
                        $chart_data['network_traffic'][] = 0;
                        $chart_data['disk_io'][] = 0;
                    }
                } else {
                    $chart_data['network_traffic'][] = 0;
                    $chart_data['disk_io'][] = 0;
                }
                $last_log = $log;
            }
        }

        return view('analytics.index', [
            'summary' => $summary,
            'chart_data' => $chart_data,
            'servers' => $servers,
            'selected_server_id' => $selected_server_id
        ]);
    }
}
