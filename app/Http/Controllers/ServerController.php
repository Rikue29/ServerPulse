<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('servers.create')
                ->withErrors($validator)
                ->withInput();
        }

        $server = new Server($request->all());
        $server->created_by = Auth::id();
        $server->monitoring_type = 'online'; // Default value
        $server->save();

        return redirect()
            ->route('servers.index')
            ->with('success', 'Server added successfully.');
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
