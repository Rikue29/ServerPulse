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

            return redirect()
                ->route('servers.index')
                ->with('success', 'Server and alert thresholds added successfully.');

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
}
