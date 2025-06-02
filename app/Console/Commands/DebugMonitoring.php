<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Server;
use App\Models\AlertThreshold;
use App\Models\Log;
use App\Services\ServerMonitoringService;

class DebugMonitoring extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'debug:monitoring';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Debug monitoring data collection and threshold checking';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $monitoringService = app(ServerMonitoringService::class);
        
        $servers = Server::all();
        
        if ($servers->isEmpty()) {
            $this->error('No servers found in database.');
            return 1;
        }

        foreach ($servers as $server) {
            $this->info("=== Debugging Server: {$server->name} ({$server->ip_address}) ===");
            
            // Get metrics
            $this->info("Collecting metrics...");
            $metrics = $monitoringService->getMetrics($server);
            
            $this->info("Collected metrics:");
            foreach ($metrics as $key => $value) {
                $this->line("  {$key}: {$value}");
            }
            
            // Show thresholds
            $thresholds = AlertThreshold::where('server_id', $server->id)->get();
            $this->info("\nConfigured thresholds:");
            foreach ($thresholds as $threshold) {
                $this->line("  {$threshold->metric_type}: {$threshold->threshold_value}");
            }
            
            // Check thresholds
            $this->info("\nChecking thresholds...");
            $logCountBefore = Log::where('server_id', $server->id)->count();
            
            $monitoringService->checkAndLogThresholds($server, $metrics);
            
            $logCountAfter = Log::where('server_id', $server->id)->count();
            $newLogs = $logCountAfter - $logCountBefore;
            
            if ($newLogs > 0) {
                $this->warn("  {$newLogs} new threshold violation log(s) created!");
                
                // Show latest logs
                $latestLogs = Log::where('server_id', $server->id)
                    ->orderBy('created_at', 'desc')
                    ->limit($newLogs)
                    ->get();
                    
                foreach ($latestLogs as $log) {
                    $this->error("  LOG: [{$log->level}] {$log->message}");
                }
            } else {
                $this->info("  No threshold violations detected.");
            }
            
            $this->info("\n" . str_repeat("-", 60) . "\n");
        }
        
        // Show total log count
        $totalLogs = Log::count();
        $this->info("Total logs in database: {$totalLogs}");
        
        return 0;
    }
}
