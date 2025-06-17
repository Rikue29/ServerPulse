<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Server;
use App\Models\AlertThreshold;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // Create a test user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password')
            ]
        );

        // Create a test server
        $server = Server::create([
            'name' => 'Ubuntu Test VM',
            'ip_address' => '192.168.159.128',
            'environment' => 'dev',
            'monitoring_type' => 'online',
            'created_by' => $user->id,
            'ssh_user' => 'user',
            'ssh_password' => 'password',
            'ssh_port' => 22
        ]);

        // Create thresholds
        AlertThreshold::create([
            'server_id' => $server->id,
            'metric_type' => 'CPU',
            'threshold_value' => 80,
            'notification_channel' => 'web',
            'created_by' => $user->id
        ]);

        AlertThreshold::create([
            'server_id' => $server->id,
            'metric_type' => 'RAM',
            'threshold_value' => 85,
            'notification_channel' => 'web',
            'created_by' => $user->id
        ]);

        AlertThreshold::create([
            'server_id' => $server->id,
            'metric_type' => 'Disk',
            'threshold_value' => 90,
            'notification_channel' => 'web',
            'created_by' => $user->id
        ]);

        AlertThreshold::create([
            'server_id' => $server->id,
            'metric_type' => 'Load',
            'threshold_value' => 2.0,
            'notification_channel' => 'web',
            'created_by' => $user->id
        ]);

        echo "Test server and thresholds created successfully!\n";
    }
}
