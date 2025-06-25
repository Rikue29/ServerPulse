<?php

namespace App\Console\Commands;

use App\Models\Alert;
use App\Models\Server;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateTestAlerts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-test-alerts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clears old test alerts and generates new ones for the UBUNTU server.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Clearing old test alerts...');
        Alert::query()->where('alert_message', 'like', 'Test Alert%')->delete();

        $server = Server::where('name', 'UBUNTU')->first();
        if (!$server) {
            $this->error("ERROR: Could not find the 'UBUNTU' server. Please ensure it exists in the 'servers' table.");
            return 1;
        }
        $this->info("Found server: {$server->name} (ID: {$server->id})");

        $this->info("\nAvailable alert_thresholds for server ID: {$server->id}:");
        $thresholds = \App\Models\AlertThreshold::where('server_id', $server->id)->get(['id', 'metric_type']);
        if ($thresholds->isEmpty()) {
            $this->error('No alert_thresholds found for this server. Please create at least one threshold.');
            return 1;
        }
        foreach ($thresholds as $t) {
            $this->line("ID: {$t->id}, Metric: {$t->metric_type}");
        }

        $alertsToCreate = [
            [
                'alert_type' => 'performance',
                'metric_value' => 95.5,
                'alert_message' => 'Test Alert: CPU usage is critical',
                'threshold_id' => 41,
            ],
            [
                'alert_type' => 'performance',
                'metric_value' => 85.2,
                'alert_message' => 'Test Alert: Memory usage is high',
                'threshold_id' => 42, // RAM
            ],
            [
                'alert_type' => 'system',
                'metric_value' => 78.0,
                'alert_message' => 'Test Alert: Disk space is medium',
                'threshold_id' => 43, // Disk
            ],
        ];

        $this->info("Creating new test alerts for server ID: {$server->id}...");
        foreach ($alertsToCreate as $alertData) {
            Alert::create([
                'server_id' => $server->id,
                'alert_type' => $alertData['alert_type'],
                'metric_value' => $alertData['metric_value'],
                'threshold_id' => $alertData['threshold_id'],
                'alert_message' => $alertData['alert_message'],
                'status' => 'triggered',
                'alert_time' => Carbon::now(),
            ]);
        }
        $this->info("Successfully created " . count($alertsToCreate) . " test alerts.");
        return 0;
    }
}
