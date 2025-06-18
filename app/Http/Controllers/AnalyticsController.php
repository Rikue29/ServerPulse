<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Server;
use App\Models\Log;
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
            $logs = Log::where('server_id', $selected_server_id)
                       ->where('source', 'performance_log')
                       ->where('created_at', '>=', now()->subDay())
                       ->orderBy('created_at', 'asc')
                       ->get();

            $last_log = null;

            foreach ($logs as $log) {
                if (isset($log->context['all_metrics'])) {
                    $metrics = $log->context['all_metrics'];
                    $chart_data['labels'][] = $log->created_at->format('H:i');
                    $chart_data['cpu_load'][] = $metrics['cpu_usage'] ?? 0;
                    $chart_data['memory_usage'][] = $metrics['ram_usage'] ?? 0;
                    // network speed in MB/s
                    $chart_data['network_traffic'][] = isset($metrics['network_speed']) ? $metrics['network_speed'] / 1024 / 1024 : 0;
                    // disk I/O in MB/s
                    if (isset($last_log)) {
                        $time_diff = $log->created_at->diffInSeconds($last_log->created_at);
                        if ($time_diff > 0) {
                            $read_diff = ($metrics['disk_io_read'] ?? 0) - ($last_log->context['all_metrics']['disk_io_read'] ?? 0);
                            $write_diff = ($metrics['disk_io_write'] ?? 0) - ($last_log->context['all_metrics']['disk_io_write'] ?? 0);
                            $disk_io_speed = ($read_diff + $write_diff) / $time_diff / 1024 / 1024; // MB/s
                            $chart_data['disk_io'][] = round($disk_io_speed, 2);
                        } else {
                            $chart_data['disk_io'][] = 0;
                        }
                    } else {
                        $chart_data['disk_io'][] = 0;
                    }
                    $last_log = $log;
                }
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
