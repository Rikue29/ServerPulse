<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Http\Request;
use Carbon\Carbon;

class LogController extends Controller
{
    public function index(Request $request)
    {
        $query = Log::query();

        // Apply filters if provided
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        if ($request->has('server')) {
            $query->where('server', $request->server);
        }
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        if ($request->has('search')) {
            $query->where('message', 'like', '%' . $request->search . '%');
        }
        if ($request->has('from_date')) {
            $query->where('timestamp', '>=', Carbon::parse($request->from_date));
        }
        if ($request->has('to_date')) {
            $query->where('timestamp', '<=', Carbon::parse($request->to_date));
        }

        // Order by timestamp descending
        $query->orderBy('timestamp', 'desc');

        return $query->paginate($request->per_page ?? 10);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'category' => 'required|string',
            'server' => 'required|string',
            'status' => 'required|string',
            'message' => 'required|string',
        ]);

        $validated['timestamp'] = now();

        $log = Log::create($validated);

        return response()->json($log, 201);
    }

    public function getStats()
    {
        $stats = [
            'total_logs' => Log::count(),
            'critical_logs' => Log::where('status', 'Critical')->count(),
            'warning_logs' => Log::where('status', 'Warning')->count(),
            'info_logs' => Log::where('status', 'Info')->count(),
            'recent_logs' => Log::where('timestamp', '>=', now()->subDay())->count(),
        ];

        return response()->json($stats);
    }
} 