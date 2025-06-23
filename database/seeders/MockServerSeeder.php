<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Server;
use App\Models\Log;
use App\Models\AlertThreshold;
use Carbon\Carbon;

class MockServerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create mock servers
        $servers = [
            [
                'name' => 'Web Server 01',
                'ip_address' => '192.168.1.10',
                'environment' => 'prod',
                'monitoring_type' => 'online',
                'cpu_usage' => 45.2,
                'ram_usage' => 67.8,
                'disk_usage' => 34.5,
                'response_time' => 12.3,
                'location' => 'US East',
                'status' => 'online',
                'last_checked_at' => now(),
                'running_since' => now()->subDays(15),
                'total_uptime_seconds' => 1296000,
                'total_downtime_seconds' => 0,
                'ssh_user' => 'admin',
                'ssh_port' => 22,
                'system_uptime' => '15 days, 2 hours, 30 minutes'
            ],
            [
                'name' => 'Database Server 01',
                'ip_address' => '192.168.1.11',
                'environment' => 'prod',
                'monitoring_type' => 'online',
                'cpu_usage' => 78.9,
                'ram_usage' => 89.2,
                'disk_usage' => 67.8,
                'response_time' => 8.7,
                'location' => 'US East',
                'status' => 'online',
                'last_checked_at' => now(),
                'running_since' => now()->subDays(8),
                'total_uptime_seconds' => 691200,
                'total_downtime_seconds' => 3600,
                'ssh_user' => 'dbadmin',
                'ssh_port' => 22,
                'system_uptime' => '8 days, 5 hours, 15 minutes'
            ],
            [
                'name' => 'Load Balancer 01',
                'ip_address' => '192.168.1.12',
                'environment' => 'prod',
                'monitoring_type' => 'online',
                'cpu_usage' => 23.4,
                'ram_usage' => 45.6,
                'disk_usage' => 28.9,
                'response_time' => 5.2,
                'location' => 'US East',
                'status' => 'online',
                'last_checked_at' => now(),
                'running_since' => now()->subDays(25),
                'total_uptime_seconds' => 2160000,
                'total_downtime_seconds' => 0,
                'ssh_user' => 'lbadmin',
                'ssh_port' => 22,
                'system_uptime' => '25 days, 0 hours, 0 minutes'
            ],
            [
                'name' => 'Development Server',
                'ip_address' => '192.168.1.20',
                'environment' => 'dev',
                'monitoring_type' => 'online',
                'cpu_usage' => 12.3,
                'ram_usage' => 34.7,
                'disk_usage' => 56.8,
                'response_time' => 15.6,
                'location' => 'US West',
                'status' => 'online',
                'last_checked_at' => now(),
                'running_since' => now()->subDays(3),
                'total_uptime_seconds' => 259200,
                'total_downtime_seconds' => 7200,
                'ssh_user' => 'dev',
                'ssh_port' => 22,
                'system_uptime' => '3 days, 12 hours, 45 minutes'
            ],
            [
                'name' => 'Backup Server 01',
                'ip_address' => '192.168.1.30',
                'environment' => 'prod',
                'monitoring_type' => 'online',
                'cpu_usage' => 8.9,
                'ram_usage' => 23.4,
                'disk_usage' => 89.2,
                'response_time' => 22.1,
                'location' => 'US East',
                'status' => 'offline',
                'last_checked_at' => now()->subHours(2),
                'last_down_at' => now()->subHours(2),
                'running_since' => now()->subDays(10),
                'total_uptime_seconds' => 864000,
                'total_downtime_seconds' => 7200,
                'ssh_user' => 'backup',
                'ssh_port' => 22,
                'system_uptime' => '10 days, 0 hours, 0 minutes'
            ],
            [
                'name' => 'Monitoring Server',
                'ip_address' => '192.168.1.40',
                'environment' => 'prod',
                'monitoring_type' => 'online',
                'cpu_usage' => 34.5,
                'ram_usage' => 56.7,
                'disk_usage' => 42.3,
                'response_time' => 9.8,
                'location' => 'US Central',
                'status' => 'online',
                'last_checked_at' => now(),
                'running_since' => now()->subDays(20),
                'total_uptime_seconds' => 1728000,
                'total_downtime_seconds' => 1800,
                'ssh_user' => 'monitor',
                'ssh_port' => 22,
                'system_uptime' => '20 days, 0 hours, 30 minutes'
            ],
            [
                'name' => 'Test Server 01',
                'ip_address' => '192.168.1.50',
                'environment' => 'staging',
                'monitoring_type' => 'online',
                'cpu_usage' => 67.8,
                'ram_usage' => 78.9,
                'disk_usage' => 45.6,
                'response_time' => 18.9,
                'location' => 'US West',
                'status' => 'maintenance',
                'last_checked_at' => now()->subMinutes(30),
                'running_since' => now()->subDays(5),
                'total_uptime_seconds' => 432000,
                'total_downtime_seconds' => 1800,
                'ssh_user' => 'test',
                'ssh_port' => 22,
                'system_uptime' => '5 days, 0 hours, 30 minutes'
            ],
            [
                'name' => 'API Gateway',
                'ip_address' => '192.168.1.60',
                'environment' => 'prod',
                'monitoring_type' => 'online',
                'cpu_usage' => 56.7,
                'ram_usage' => 67.8,
                'disk_usage' => 38.9,
                'response_time' => 7.4,
                'location' => 'US East',
                'status' => 'online',
                'last_checked_at' => now(),
                'running_since' => now()->subDays(12),
                'total_uptime_seconds' => 1036800,
                'total_downtime_seconds' => 900,
                'ssh_user' => 'api',
                'ssh_port' => 22,
                'system_uptime' => '12 days, 0 hours, 15 minutes'
            ]
        ];

        foreach ($servers as $serverData) {
            $serverData['created_by'] = 1; // Assuming user ID 1 exists
            $server = Server::create($serverData);
            
            // Create logs for each server
            $this->createLogsForServer($server);
            
            // Create alert thresholds for some servers
            if (in_array($server->name, ['Web Server 01', 'Database Server 01', 'Load Balancer 01'])) {
                $this->createAlertThresholdsForServer($server);
            }
        }

        $this->command->info('Mock servers created successfully!');
    }

    private function createLogsForServer($server)
    {
        $logMessages = [
            'ERROR' => [
                'Database connection timeout',
                'High CPU usage detected',
                'Memory usage exceeded threshold',
                'Disk space running low',
                'Network connectivity issues',
                'Service failed to start',
                'Authentication failed',
                'SSL certificate expired'
            ],
            'WARNING' => [
                'CPU usage above 80%',
                'Memory usage approaching limit',
                'Disk usage at 85%',
                'Slow response time detected',
                'Backup job running longer than usual',
                'High network traffic detected',
                'Service restart required',
                'Configuration file modified'
            ],
            'INFO' => [
                'Server health check completed',
                'Backup completed successfully',
                'Service started successfully',
                'Configuration updated',
                'User login successful',
                'System update completed',
                'Monitoring check passed',
                'Log rotation completed'
            ],
            'NOTICE' => [
                'Debug information logged',
                'Performance metrics collected',
                'System status check',
                'Configuration validation',
                'Connection pool status',
                'Cache hit ratio calculated',
                'Query execution time logged',
                'Memory allocation tracked'
            ]
        ];

        $sources = ['system', 'application', 'database', 'network', 'security', 'monitoring'];

        // Create logs for the last 30 days
        for ($i = 0; $i < rand(50, 200); $i++) {
            $logLevel = array_rand($logMessages);
            $message = $logMessages[$logLevel][array_rand($logMessages[$logLevel])];
            $source = $sources[array_rand($sources)];
            
            // Random timestamp within last 30 days
            $timestamp = now()->subDays(rand(0, 30))->subHours(rand(0, 23))->subMinutes(rand(0, 59));
            
            Log::create([
                'server_id' => $server->id,
                'level' => strtolower($logLevel),
                'source' => $source,
                'log_level' => $logLevel,
                'message' => $message,
                'context' => [
                    'timestamp' => $timestamp->toISOString(),
                    'source' => $source,
                    'server_ip' => $server->ip_address,
                    'environment' => $server->environment
                ],
                'created_at' => $timestamp,
                'updated_at' => $timestamp
            ]);
        }
    }

    private function createAlertThresholdsForServer($server)
    {
        $metricTypes = ['CPU', 'RAM', 'Disk', 'Load'];
        $notificationChannels = ['email', 'slack', 'webhook', 'sms'];

        foreach ($metricTypes as $metricType) {
            $thresholdValue = match($metricType) {
                'CPU' => rand(70, 95),
                'RAM' => rand(75, 90),
                'Disk' => rand(80, 95),
                'Load' => rand(2, 8),
                default => 85
            };

            AlertThreshold::create([
                'server_id' => $server->id,
                'metric_type' => $metricType,
                'threshold_value' => $thresholdValue,
                'notification_channel' => $notificationChannels[array_rand($notificationChannels)],
                'created_by' => 1
            ]);
        }
    }
} 