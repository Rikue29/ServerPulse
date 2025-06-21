<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Models\AlertThreshold;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class ServerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $servers = Server::with('creator')->get();
        return view('servers.index', compact('servers'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('servers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'ip_address' => 'required|string|max:45|unique:servers',
            'environment' => 'required|in:prod,staging,dev',
            'location' => 'nullable|string',
            'ssh_user' => 'nullable|string|max:255',
            'ssh_password' => 'nullable|string',
            'ssh_key' => 'nullable|string',
            'ssh_port' => 'nullable|integer|min:1|max:65535',
            'cpu_threshold' => 'nullable|numeric|min:1|max:100',
            'memory_threshold' => 'nullable|numeric|min:1|max:100',
            'disk_threshold' => 'nullable|numeric|min:1|max:100',
            'load_threshold' => 'nullable|numeric|min:0.1|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('servers.create')
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $server = new Server($request->all());
            $server->created_by = Auth::id(); // Authenticated user required
            $server->monitoring_type = 'online'; // Default value
            $server->save();

            // Create alert thresholds if provided
            $thresholds = [
                'CPU' => $request->input('cpu_threshold', 80),
                'RAM' => $request->input('memory_threshold', 85),
                'Disk' => $request->input('disk_threshold', 90),
                'Load' => $request->input('load_threshold', 2.0),
            ];

            foreach ($thresholds as $metricType => $thresholdValue) {
                if ($thresholdValue) {
                    AlertThreshold::create([
                        'server_id' => $server->id,
                        'metric_type' => $metricType, // Already uppercase to match enum values
                        'threshold_value' => $thresholdValue,
                        'notification_channel' => 'web', // Default notification channel
                        'created_by' => Auth::id(), // Authenticated user required
                    ]);
                }
            }

            DB::commit();

            // Generate agent installation info if server was created successfully
            $agentInstallInfo = [
                'server_ip' => $server->ip_address,
                'server_id' => $server->id,
                'install_command' => $this->generateAgentInstallCommand($server),
                'registration_endpoint' => route('api.agents.register', [], true)
            ];

            return redirect()
                ->route('servers.index')
                ->with('success', 'Server and alert thresholds added successfully.')
                ->with('agent_info', $agentInstallInfo);

        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()
                ->route('servers.create')
                ->withErrors(['error' => 'Failed to create server: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Server $server)
    {
        return view('servers.show', compact('server'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Server $server)
    {
        return view('servers.edit', compact('server'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Server $server)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ip_address' => 'required|string|max:255',
            'location' => 'nullable|string|max:255',
            'environment' => 'required|in:prod,staging,dev',
        ]);

        $server->update($validated);

        return redirect()->route('servers.index')
            ->with('success', 'Server updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Server $server)
    {
        $server->delete();

        return redirect()->route('servers.index')
            ->with('success', 'Server deleted successfully.');
    }

    /**
     * Generate agent installation command for the server
     */
    private function generateAgentInstallCommand(Server $server)
    {
        $serverUrl = config('app.url');
        $installScript = "#!/bin/bash\n";
        $installScript .= "# ServerPulse Agent Installation Script\n";
        $installScript .= "# Server: {$server->name} ({$server->ip_address})\n\n";
        $installScript .= "# Download and install the agent\n";
        $installScript .= "wget https://github.com/shane-kennedy-se/serverpulse-agent/archive/main.zip\n";
        $installScript .= "unzip main.zip\n";
        $installScript .= "cd serverpulse-agent-main\n";
        $installScript .= "sudo chmod +x install.sh\n";
        $installScript .= "sudo ./install.sh\n\n";
        $installScript .= "# Configure the agent\n";
        $installScript .= "sudo tee /etc/serverpulse-agent/config.yml > /dev/null <<EOF\n";
        $installScript .= "server:\n";
        $installScript .= "  endpoint: \"{$serverUrl}\"\n";
        $installScript .= "  auth_token: \"GENERATED_AFTER_REGISTRATION\"\n";
        $installScript .= "  agent_id: \"GENERATED_AFTER_REGISTRATION\"\n\n";
        $installScript .= "collection:\n";
        $installScript .= "  interval: 30\n";
        $installScript .= "  metrics:\n";
        $installScript .= "    - system_stats\n";
        $installScript .= "    - disk_usage\n";
        $installScript .= "    - network_stats\n";
        $installScript .= "    - process_list\n\n";
        $installScript .= "monitoring:\n";
        $installScript .= "  services:\n";
        $installScript .= "    - ssh\n";
        $installScript .= "    - nginx\n";
        $installScript .= "    - mysql\n";
        $installScript .= "    - docker\n\n";
        $installScript .= "alerts:\n";
        $installScript .= "  cpu_threshold: 80\n";
        $installScript .= "  memory_threshold: 85\n";
        $installScript .= "  disk_threshold: 90\n";
        $installScript .= "  load_threshold: 5.0\n";
        $installScript .= "EOF\n\n";
        $installScript .= "# Start the agent service\n";
        $installScript .= "sudo systemctl enable serverpulse-agent\n";
        $installScript .= "sudo systemctl start serverpulse-agent\n\n";
        $installScript .= "echo \"Agent installed! Check status with: sudo systemctl status serverpulse-agent\"\n";
        $installScript .= "echo \"View logs with: sudo journalctl -u serverpulse-agent -f\"\n";

        return $installScript;
    }
}
