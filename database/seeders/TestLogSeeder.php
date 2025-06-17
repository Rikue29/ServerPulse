<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Server;
use App\Models\Log;

class TestLogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create test servers if they don't exist
        $server1 = Server::firstOrCreate([
            'name' => 'Web Server 01',
            'ip_address' => '192.168.1.100'
        ], [
            'status' => 'online',
            'created_by' => 1
        ]);

        $server2 = Server::firstOrCreate([
            'name' => 'Database Server',
            'ip_address' => '192.168.1.200'
        ], [
            'status' => 'online',
            'created_by' => 1
        ]);

        // Create test logs
        $logs = [
            [
                'server_id' => $server1->id,
                'level' => 'info',
                'source' => 'apache',
                'message' => 'Server started successfully',
                'context' => [
                    'cpu_usage' => 25,
                    'memory_usage' => 60,
                    'disk_usage' => 45
                ]
            ],
            [
                'server_id' => $server1->id,
                'level' => 'warning',
                'source' => 'system',
                'message' => 'High memory usage detected',
                'context' => [
                    'cpu_usage' => 85,
                    'memory_usage' => 92,
                    'disk_usage' => 45
                ]
            ],
            [
                'server_id' => $server2->id,
                'level' => 'error',
                'source' => 'mysql',
                'message' => 'Connection timeout error',
                'context' => [
                    'cpu_usage' => 95,
                    'memory_usage' => 98,
                    'disk_usage' => 85
                ]
            ],
            [
                'server_id' => $server1->id,
                'level' => 'info',
                'source' => 'nginx',
                'message' => 'Request processed successfully',
                'context' => [
                    'cpu_usage' => 30,
                    'memory_usage' => 65,
                    'disk_usage' => 50
                ]
            ]
        ];

        foreach ($logs as $logData) {
            Log::create($logData);
        }
    }
}
