<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\AlertThreshold;
use App\Models\Server;
use App\Models\User;

class AlertThresholdSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get the first user or create one if none exists
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Admin User',
                'email' => 'admin@serverpulse.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }

        // Get all servers
        $servers = Server::all();
        
        if ($servers->isEmpty()) {
            // Create some sample servers if none exist
            $servers = collect([
                Server::create([
                    'name' => 'Web Server 1',
                    'ip_address' => '192.168.1.100',
                    'port' => 80,
                    'status' => 'online',
                ]),
                Server::create([
                    'name' => 'Database Server',
                    'ip_address' => '192.168.1.101',
                    'port' => 3306,
                    'status' => 'online',
                ]),
                Server::create([
                    'name' => 'API Server',
                    'ip_address' => '192.168.1.102',
                    'port' => 8080,
                    'status' => 'online',
                ]),
            ]);
        }

        // Create alert thresholds for each server
        foreach ($servers as $server) {
            // CPU threshold
            AlertThreshold::create([
                'server_id' => $server->id,
                'metric_type' => 'CPU',
                'threshold_value' => 80.0,
                'notification_channel' => 'infra',
                'created_by' => $user->id,
                'is_active' => true,
            ]);

            // Memory/RAM threshold
            AlertThreshold::create([
                'server_id' => $server->id,
                'metric_type' => 'RAM',
                'threshold_value' => 85.0,
                'notification_channel' => 'infra',
                'created_by' => $user->id,
                'is_active' => true,
            ]);

            // Disk threshold
            AlertThreshold::create([
                'server_id' => $server->id,
                'metric_type' => 'DISK',
                'threshold_value' => 90.0,
                'notification_channel' => 'infra',
                'created_by' => $user->id,
                'is_active' => true,
            ]);

            // Network threshold (for some servers)
            if ($server->name !== 'Database Server') {
                AlertThreshold::create([
                    'server_id' => $server->id,
                    'metric_type' => 'NETWORK',
                    'threshold_value' => 75.0,
                    'notification_channel' => 'dev',
                    'created_by' => $user->id,
                    'is_active' => true,
                ]);
            }
        }

        $this->command->info('Alert thresholds created successfully!');
        $this->command->info('Servers: ' . $servers->count());
        $this->command->info('Thresholds: ' . AlertThreshold::count());
    }
}
