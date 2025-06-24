<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AlertMonitoringService;
use App\Models\Server;

class MonitorAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'alerts:monitor 
                           {--simulate : Simulate a critical alert for testing}
                           {--server= : Check specific server ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor servers and trigger alerts when thresholds are exceeded';

    protected $alertService;

    public function __construct(AlertMonitoringService $alertService)
    {
        parent::__construct();
        $this->alertService = $alertService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 Starting alert monitoring...');

        // Handle simulation mode
        if ($this->option('simulate')) {
            return $this->simulateAlert();
        }

        // Handle specific server monitoring
        if ($serverId = $this->option('server')) {
            return $this->monitorSpecificServer($serverId);
        }

        // Monitor all servers
        return $this->monitorAllServers();
    }

    /**
     * Monitor all servers for threshold violations
     */
    protected function monitorAllServers()
    {
        $results = $this->alertService->checkAllThresholds();
        
        if (empty($results)) {
            $this->info('✅ All systems operating normally - no alerts triggered');
        } else {
            $totalAlerts = 0;
            foreach ($results as $serverId => $alerts) {
                $server = Server::find($serverId);
                $serverName = $server->name ?? "Server #$serverId";
                
                $this->warn("🚨 Alerts triggered for $serverName:");
                foreach ($alerts as $alert) {
                    if (isset($alert['alert'])) {
                        $alertData = $alert['alert'];
                        $this->line("   - {$alertData['alert_type']}: {$alertData['alert_message']}");
                        $totalAlerts++;
                    }
                }
                $this->newLine();
            }
            
            $this->info("📊 Total alerts triggered: $totalAlerts");
        }

        // Show system health summary
        $this->showHealthSummary();
        
        return Command::SUCCESS;
    }

    /**
     * Monitor a specific server
     */
    protected function monitorSpecificServer($serverId)
    {
        $server = Server::find($serverId);
        
        if (!$server) {
            $this->error("❌ Server with ID $serverId not found");
            return Command::FAILURE;
        }

        $this->info("🔍 Monitoring server: {$server->name} (ID: $serverId)");
        
        $results = $this->alertService->checkServerThresholds($server);
        
        if (empty($results)) {
            $this->info("✅ Server {$server->name} is operating normally");
        } else {
            $this->warn("🚨 Alerts triggered for {$server->name}:");
            foreach ($results as $alert) {
                if (isset($alert['alert'])) {
                    $alertData = $alert['alert'];
                    $this->line("   - {$alertData['alert_type']}: {$alertData['alert_message']}");
                }
            }
        }

        return Command::SUCCESS;
    }

    /**
     * Simulate a critical alert for testing
     */
    protected function simulateAlert()
    {
        $servers = Server::with('alertThresholds')->get();
        
        if ($servers->isEmpty()) {
            $this->error('❌ No servers found for simulation');
            return Command::FAILURE;
        }

        $server = $servers->where('alertThresholds.count', '>', 0)->first() ?? $servers->first();
        
        $this->info("🧪 Simulating critical alert for server: {$server->name}");
        
        $result = $this->alertService->simulateCriticalAlert($server);
        
        if ($result && isset($result['alert'])) {
            $alert = $result['alert'];
            $this->info("✅ Critical alert simulated successfully!");
            $this->line("   Alert ID: {$alert['id']}");
            $this->line("   Type: {$alert['alert_type']}");
            $this->line("   Message: {$alert['alert_message']}");
            $this->line("   Value: {$alert['metric_value']}%");
        } else {
            $this->error('❌ Failed to simulate alert');
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }

    /**
     * Show system health summary
     */
    protected function showHealthSummary()
    {
        $summary = $this->alertService->getSystemHealthSummary();
        
        $this->newLine();
        $this->line('📊 <info>System Health Summary</info>');
        $this->line('─────────────────────────');
        $this->line("Total Servers: {$summary['total_servers']}");
        $this->line("Online Servers: {$summary['online_servers']}");
        $this->line("Active Thresholds: {$summary['active_thresholds']}");
        $this->line("Unresolved Alerts: {$summary['unresolved_alerts']}");
        $this->line("Critical Alerts: {$summary['critical_alerts']}");
        
        $healthStatus = match($summary['system_health']) {
            'healthy' => '<info>🟢 HEALTHY</info>',
            'warning' => '<comment>🟡 WARNING</comment>',
            'critical' => '<error>🔴 CRITICAL</error>',
            default => '<comment>🟡 UNKNOWN</comment>'
        };
        
        $this->line("System Status: $healthStatus");
    }
}
