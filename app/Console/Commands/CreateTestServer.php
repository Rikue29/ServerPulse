<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Server;
use App\Models\AlertThreshold;
use Illuminate\Support\Facades\Hash;

class CreateTestServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:create-server';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a test server for monitoring';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create or get test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password')
            ]
        );

        $this->info("User created/found: {$user->email}");

        $this->info("Server created: {$server->name} ({$server->ip_address})");

        // Create thresholds (set low to trigger alerts for testing)
        $thresholds = [
            ['metric_type' => 'CPU', 'threshold_value' => 10], // Low threshold to trigger
            ['metric_type' => 'RAM', 'threshold_value' => 20], // Low threshold to trigger
            ['metric_type' => 'Disk', 'threshold_value' => 50],
            ['metric_type' => 'Load', 'threshold_value' => 0.5], // Low threshold to trigger
        ];

        foreach ($thresholds as $threshold) {
            AlertThreshold::create([
                'server_id' => $server->id,
                'metric_type' => $threshold['metric_type'],
                'threshold_value' => $threshold['threshold_value'],
                'notification_channel' => 'web',
                'created_by' => $user->id
            ]);
            
            $this->info("Threshold created: {$threshold['metric_type']} > {$threshold['threshold_value']}");
        }

        $this->info('Test server and thresholds created successfully!');
        $this->info("Server ID: {$server->id}");
        
        return 0;
    }
}
