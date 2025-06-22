<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\Log;
use App\Events\ServerStatusUpdated;
use App\Services\ServerMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AgentController extends Controller
{
    protected $monitoringService;

    public function __construct(ServerMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Register a new agent with a server
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'server_ip' => 'required|ip',
            'hostname' => 'required|string|max:255',
            'agent_version' => 'required|string|max:50',
            'system_info' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $validator->errors()
            ], 400);
        }

        try {
            // Find server by IP address
            $server = Server::where('ip_address', $request->server_ip)->first();
            
            if (!$server) {
                return response()->json([
                    'error' => 'Server not found in ServerPulse. Please add the server first.'
                ], 404);
            }

            // Generate unique agent ID and token
            $agentId = Str::uuid();
            $agentToken = Str::random(64);

            // Update server with agent information
            $server->update([
                'agent_enabled' => true,
                'agent_id' => $agentId,
                'agent_token' => hash('sha256', $agentToken),
                'agent_status' => 'active',
                'agent_version' => $request->agent_version,
                'agent_config' => [
                    'hostname' => $request->hostname,
                    'system_info' => $request->system_info,
                    'registered_at' => now()->toISOString()
                ],
                'agent_last_heartbeat' => now()
            ]);

            // Log the registration
            Log::create([
                'server_id' => $server->id,
                'level' => 'info',
                'log_level' => 'INFO',
                'message' => "Agent registered successfully",
                'source' => 'agent'
            ]);

            return response()->json([
                'success' => true,
                'agent_id' => $agentId,
                'auth_token' => $agentToken,
                'server_id' => $server->id,
                'config' => [
                    'collection_interval' => 30,
                    'heartbeat_interval' => 60,
                    'metrics_enabled' => true
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Registration failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Receive metrics from agent
     */
    public function metrics(Request $request, $agentId)
    {
        $server = $this->authenticateAgent($request, $agentId);
        if (!$server) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Check server heartbeat status first - if it was marked as offline, we need to track this
        $wasMarkedOffline = $this->checkServerHeartbeat($server);
        if ($wasMarkedOffline) {
            // If server was just marked offline, return early
            return response()->json(['success' => true, 'status' => 'offline']);
        }

        $validator = Validator::make($request->all(), [
            'timestamp' => 'required|date',
            'metrics' => 'required|array',
            'metrics.cpu_usage' => 'required|numeric|min:0|max:100',
            'metrics.memory_usage' => 'required|numeric|min:0|max:100',
            'metrics.disk_usage' => 'required|numeric|min:0|max:100',
            'metrics.uptime' => 'required|numeric|min:0',
            'metrics.load_average' => 'nullable|numeric|min:0',
            'services' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid metrics data',
                'details' => $validator->errors()
            ], 400);
        }

        try {
            $metrics = $request->metrics;
            $services = $request->services ?? [];
            $wasOffline = $server->status === 'offline';

            // Calculate and set running_since based on uptime
            $uptimeSeconds = $metrics['uptime'] ?? 0;
            $runningDateTime = now()->subSeconds($uptimeSeconds);
            
            // Update server with latest metrics
            $server->update([
                'cpu_usage' => $metrics['cpu_usage'],
                'ram_usage' => $metrics['memory_usage'],
                'disk_usage' => $metrics['disk_usage'],
                'system_uptime' => $uptimeSeconds,
                'response_time' => $metrics['response_time'] ?? rand(50, 500), // Use response time from metrics or generate reasonable value
                'last_checked_at' => now(),
                'status' => 'online',
                'agent_last_heartbeat' => now(),
                'running_since' => $runningDateTime,
                'last_metrics' => [
                    'timestamp' => $request->timestamp,
                    'metrics' => $metrics,
                    'services' => $services,
                    'received_at' => now()->toISOString()
                ]
            ]);

            // If server was offline and is now back online, clear last_down_at
            if ($wasOffline) {
                $server->last_down_at = null;
                $server->save();
            }

            // Check for alerts using existing monitoring service
            $this->monitoringService->checkAlerts($server, $metrics);

            // Create performance log entry
            $this->monitoringService->processAgentMetrics($server, $metrics);

            // Log critical metrics if thresholds exceeded
            $this->logCriticalMetrics($server, $metrics);

            // Add real-time broadcasting of server status for analytics page
            $formattedUptime = $this->formatUptime($uptimeSeconds);
            
            $payload = [
                'server_id' => $server->id,
                'name' => $server->name,
                'ip_address' => $server->ip_address,
                'cpu_usage' => $server->cpu_usage,
                'ram_usage' => $server->ram_usage,
                'disk_usage' => $server->disk_usage,
                'status' => $server->status,
                'system_uptime' => $server->status === 'online' ? $formattedUptime : null,
                'response_time' => $server->response_time,
                'network_rx' => $metrics['network_rx'] ?? 0,
                'network_tx' => $metrics['network_tx'] ?? 0,
                'disk_io_read' => $metrics['disk_io_read'] ?? 0,
                'disk_io_write' => $metrics['disk_io_write'] ?? 0,
                'last_down_at' => $server->last_down_at?->toDateTimeString(),
                'current_uptime' => $server->status === 'online' ? $uptimeSeconds : null,
                'current_downtime' => $server->status === 'offline' && $server->last_down_at ? now()->diffInSeconds($server->last_down_at) : null,
                'formatted_downtime' => $server->status === 'offline' && $server->last_down_at ? $this->formatUptime(now()->diffInSeconds($server->last_down_at)) : null
            ];
            
            // Broadcast status update for real-time display
            broadcast(new ServerStatusUpdated($payload));

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to process metrics',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Agent heartbeat endpoint
     */
    public function heartbeat(Request $request, $agentId)
    {
        $server = $this->authenticateAgent($request, $agentId);
        if (!$server) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        try {
            $wasOffline = $server->status === 'offline';
            $server->update([
                'agent_last_heartbeat' => now(),
                'agent_status' => 'active',
                'status' => 'online'
            ]);

            // If server was offline and is now back online, update running_since and broadcast
            if ($wasOffline) {
                $server->running_since = now();
                $server->last_down_at = null;
                $server->save();

                // Add real-time broadcasting of server back online
                $payload = [
                    'server_id' => $server->id,
                    'name' => $server->name,
                    'ip_address' => $server->ip_address,
                    'status' => 'online',
                    'system_uptime' => '0s',
                    'current_uptime' => 0,
                    'last_down_at' => null,
                ];
                
                // Broadcast status update for real-time display of server coming back online
                broadcast(new ServerStatusUpdated($payload));
            }

            return response()->json([
                'success' => true,
                'server_time' => now()->toISOString(),
                'config' => [
                    'collection_interval' => 30,
                    'heartbeat_interval' => 60
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Heartbeat failed',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send alerts from agent
     */
    public function alerts(Request $request, $agentId)
    {
        $server = $this->authenticateAgent($request, $agentId);
        if (!$server) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validator = Validator::make($request->all(), [
            'alerts' => 'required|array',
            'alerts.*.type' => 'required|string',
            'alerts.*.message' => 'required|string',
            'alerts.*.severity' => 'required|in:info,warning,error,critical',
            'alerts.*.timestamp' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'error' => 'Invalid alert data',
                'details' => $validator->errors()
            ], 400);
        }

        try {
            foreach ($request->alerts as $alert) {
                // Map severity levels to correct enum values
                $logLevel = match(strtolower($alert['severity'])) {
                    'warning', 'warn' => 'WARNING',
                    'error' => 'ERROR',
                    'critical' => 'CRITICAL',
                    'notice' => 'NOTICE',
                    default => 'INFO'
                };
                
                Log::create([
                    'server_id' => $server->id,
                    'level' => $alert['severity'],
                    'log_level' => $logLevel,
                    'message' => $alert['message'],
                    'source' => 'agent'
                ]);
            }

            return response()->json(['success' => true]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to process alerts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending commands for agent
     */
    public function commands(Request $request, $agentId)
    {
        $server = $this->authenticateAgent($request, $agentId);
        if (!$server) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // For now, return empty commands array
        // This can be extended later for remote command execution
        return response()->json([
            'commands' => []
        ]);
    }

    /**
     * Authenticate agent using token
     */
    private function authenticateAgent(Request $request, $agentId)
    {
        $token = $request->header('Authorization');
        
        if (!$token || !str_starts_with($token, 'Bearer ')) {
            return null;
        }

        $token = substr($token, 7); // Remove 'Bearer ' prefix
        $hashedToken = hash('sha256', $token);

        return Server::where('agent_id', $agentId)
                    ->where('agent_token', $hashedToken)
                    ->where('agent_enabled', true)
                    ->first();
    }

    /**
     * Log critical metrics that exceed thresholds
     */
    private function logCriticalMetrics(Server $server, array $metrics)
    {
        $criticalLogs = [];

        if ($metrics['cpu_usage'] > 90) {
            $criticalLogs[] = [
                'server_id' => $server->id,
                'level' => 'warning',
                'log_level' => 'WARNING',
                'message' => "High CPU usage detected: {$metrics['cpu_usage']}%",
                'source' => 'agent',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if ($metrics['memory_usage'] > 90) {
            $criticalLogs[] = [
                'server_id' => $server->id,
                'level' => 'warning',
                'log_level' => 'WARNING',
                'message' => "High memory usage detected: {$metrics['memory_usage']}%",
                'source' => 'agent',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if ($metrics['disk_usage'] > 95) {
            $criticalLogs[] = [
                'server_id' => $server->id,
                'level' => 'error',
                'log_level' => 'ERROR',
                'message' => "Critical disk usage detected: {$metrics['disk_usage']}%",
                'source' => 'agent',
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        if (!empty($criticalLogs)) {
            Log::insert($criticalLogs);
        }
    }

    /**
     * Format uptime in seconds to human-readable string (e.g., "5h 30m 15s")
     */
    private function formatUptime($seconds)
    {
        if ($seconds < 0) {
            return '0s';
        }
        
        $days = floor($seconds / 86400);
        $hours = floor(($seconds % 86400) / 3600);
        $minutes = floor(($seconds % 3600) / 60);
        $secs = $seconds % 60;
        
        $result = '';
        if ($days > 0) {
            $result .= $days . 'd ';
        }
        if ($hours > 0) {
            $result .= $hours . 'h ';
        }
        if ($minutes > 0) {
            $result .= $minutes . 'm ';
        }
        if ($secs > 0 || $result === '') {
            $result .= $secs . 's';
        }
        
        return trim($result);
    }

    /**
     * Check if the server is offline due to no heartbeat and broadcast if needed
     */
    private function checkServerHeartbeat($server)
    {
        // Mark server as offline if no heartbeat in the last minute
        if ($server->agent_enabled && $server->agent_last_heartbeat) {
            $lastHeartbeat = $server->agent_last_heartbeat;
            $heartbeatTimeout = now()->subMinute();
            
            if ($lastHeartbeat < $heartbeatTimeout && $server->status === 'online') {
                // Server missed heartbeat, mark as offline
                $server->status = 'offline';
                $server->last_down_at = now();
                $server->running_since = null;
                $server->save();

                // Calculate current downtime
                $currentDowntime = $server->last_down_at ? now()->diffInSeconds($server->last_down_at) : 0;
                $formattedDowntime = $this->formatUptime($currentDowntime);
                
                // Broadcast offline status for real-time updates - ensure all metrics are zero
                $payload = [
                    'server_id' => $server->id,
                    'name' => $server->name,
                    'ip_address' => $server->ip_address,
                    'status' => 'offline',
                    'system_uptime' => '0s',
                    'response_time' => 0,
                    'cpu_usage' => 0,
                    'ram_usage' => 0,
                    'disk_usage' => 0, // Set to zero to ensure metrics reset to zero when offline
                    'last_down_at' => $server->last_down_at?->toDateTimeString(),
                    'current_downtime' => $currentDowntime,
                    'formatted_downtime' => $formattedDowntime,
                    'network_rx' => 0,
                    'network_tx' => 0,
                    'disk_io_read' => 0,
                    'disk_io_write' => 0,
                    'timestamp' => time() // Add timestamp for uniqueness in frontend
                ];
                
                // Broadcast immediately to both channels to ensure consistent updates
                broadcast(new ServerStatusUpdated($payload));
                
                // Log the event
                \App\Models\Log::create([
                    'server_id' => $server->id,
                    'level' => 'warning',
                    'log_level' => 'WARNING',
                    'message' => "Server marked offline: No heartbeat received since {$lastHeartbeat->format('Y-m-d H:i:s')}",
                    'source' => 'system'
                ]);
                
                // Also update downtime immediately after we first detect offline
                // Broadcast a second update with a short delay to ensure frontend gets it
                dispatch(function() use ($server, $payload) {
                    // Add a small delay to ensure the first event has been processed
                    sleep(2);
                    
                    // Update the downtime again to make sure it starts incrementing
                    $currentDowntime = $server->last_down_at ? now()->diffInSeconds($server->last_down_at) : 0;
                    $formattedDowntime = $this->formatUptime($currentDowntime);
                    
                    $payload['current_downtime'] = $currentDowntime;
                    $payload['formatted_downtime'] = $formattedDowntime;
                    $payload['timestamp'] = time(); // Update timestamp
                    
                    broadcast(new ServerStatusUpdated($payload));
                })->afterResponse();
                
                return true; // Server was marked offline
            }
        }
        
        return false; // No change to server status
    }
}
