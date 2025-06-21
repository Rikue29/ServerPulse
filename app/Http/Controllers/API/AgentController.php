<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\Log;
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

            // Update server with latest metrics
            $server->update([
                'cpu_usage' => $metrics['cpu_usage'],
                'ram_usage' => $metrics['memory_usage'],
                'disk_usage' => $metrics['disk_usage'],
                'system_uptime' => $metrics['uptime'],
                'last_checked_at' => now(),
                'status' => 'online',
                'agent_last_heartbeat' => now(),
                'last_metrics' => [
                    'timestamp' => $request->timestamp,
                    'metrics' => $metrics,
                    'services' => $services,
                    'received_at' => now()->toISOString()
                ]
            ]);

            // Check for alerts using existing monitoring service
            $this->monitoringService->checkAlerts($server, $metrics);

            // Create performance log entry
            $this->monitoringService->processAgentMetrics($server, $metrics);

            // Log critical metrics if thresholds exceeded
            $this->logCriticalMetrics($server, $metrics);

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
            $server->update([
                'agent_last_heartbeat' => now(),
                'agent_status' => 'active',
                'status' => 'online'
            ]);

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
                Log::create([
                    'server_id' => $server->id,
                    'level' => $alert['severity'],
                    'log_level' => strtoupper($alert['severity']),
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
                'log_level' => 'WARN',
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
                'log_level' => 'WARN',
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
}
