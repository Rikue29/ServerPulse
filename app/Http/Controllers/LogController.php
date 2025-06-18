<?php

namespace App\Http\Controllers;

use App\Models\Log;
use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;

class LogController extends Controller
{
    public function index(Request $request)
    {
        return view('logs'); 
    }

    public function show($id)
    {
        $log = Log::findOrFail($id);
        return view('logs.show', compact('log'));
    }

    public function generateReport($id)
    {
        $log = Log::with('server')->findOrFail($id);
        
        // Get related logs from the same server around the same time
        $relatedLogs = Log::where('server_id', $log->server_id)
            ->where('created_at', '>=', $log->created_at->subMinutes(30))
            ->where('created_at', '<=', $log->created_at->addMinutes(30))
            ->where('id', '!=', $log->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        return view('reports.log-report', compact('log', 'relatedLogs'));
    }

    public function downloadReport($id)
    {
        $log = Log::with('server')->findOrFail($id);
        
        // Get related logs from the same server around the same time
        $relatedLogs = Log::where('server_id', $log->server_id)
            ->where('created_at', '>=', $log->created_at->subMinutes(30))
            ->where('created_at', '<=', $log->created_at->addMinutes(30))
            ->where('id', '!=', $log->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Generate PDF
        $pdf = Pdf::loadView('reports.log-report-pdf', compact('log', 'relatedLogs'))
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isPhpEnabled' => true,
                'defaultFont' => 'Arial'
            ]);

        $filename = 'system_analysis_report_' . str_pad($log->id, 6, '0', STR_PAD_LEFT) . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    public function exportCsv(Request $request)
    {
        $query = Log::with('server');
        
        // Apply filters if any
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('message', 'like', "%{$search}%")
                  ->orWhere('level', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('level')) {
            $query->where('level', $request->level);
        }
        
        if ($request->filled('server')) {
            $query->where('server_id', $request->server);
        }
        
        $logs = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'server_logs_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];
        
        $callback = function() use ($logs) {
            $file = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($file, [
                'ID',
                'Timestamp',
                'Server',
                'Level',
                'Source',
                'Message',
                'Context'
            ]);
            
            // Add data rows
            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->id,
                    $log->created_at->format('Y-m-d H:i:s'),
                    $log->server->name ?? 'Unknown',
                    $log->level,
                    $log->source,
                    $log->message,
                    $log->context ? json_encode($log->context) : ''
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}
