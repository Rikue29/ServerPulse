<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Server;
use phpseclib3\Net\SSH2;
use phpseclib3\Crypt\PublicKeyLoader;

class TestSSH extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ssh-network {server_id?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SSH network interface reading for debugging';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $serverId = $this->argument('server_id');
        
        if ($serverId) {
            $servers = Server::where('id', $serverId)->get();
        } else {
            $servers = Server::where('ip_address', '!=', '127.0.0.1')->get();
        }

        if ($servers->isEmpty()) {
            $this->error('No remote servers found to test.');
            return;
        }

        foreach ($servers as $server) {
            $this->info("\n=== Testing SSH Network Reading for: {$server->name} ({$server->ip_address}) ===");
            
            try {
                $ssh = new SSH2($server->ip_address, $server->ssh_port ?? 22);
                
                if (!empty($server->ssh_key)) {
                    $key = PublicKeyLoader::load(trim($server->ssh_key));
                    $success = $ssh->login($server->ssh_user, $key);
                } else {
                    $success = $ssh->login($server->ssh_user, $server->ssh_password);
                }

                if (!$success) {
                    $this->error("SSH login failed for {$server->name}");
                    continue;
                }

                $this->info("✓ SSH connection established");

                // Test default interface detection
                $defaultInterface = trim($ssh->exec("ip route | grep default | awk '{print \$5}'"));
                $this->info("Default interface detected: " . ($defaultInterface ?: 'none (using fallback eth0)'));
                
                if (empty($defaultInterface)) {
                    $defaultInterface = 'eth0';
                }

                // Test network stats reading
                $netDev = $ssh->exec('cat /proc/net/dev');
                $this->info("Raw /proc/net/dev output:");
                $this->line($netDev);
                
                $lines = explode("\n", $netDev);
                $rx = 0;
                $tx = 0;
                $interfaceFound = false;
                
                foreach ($lines as $line) {
                    if (strpos($line, $defaultInterface . ':') !== false) {
                        $parts = preg_split('/\s+/', trim($line));
                        $rx = (int)$parts[1];
                        $tx = (int)$parts[9];
                        $interfaceFound = true;
                        $this->info("✓ Found interface {$defaultInterface}: RX={$rx}, TX={$tx}");
                        break;
                    }
                }
                
                if (!$interfaceFound) {
                    $this->warn("⚠ Interface {$defaultInterface} not found in /proc/net/dev");
                    $this->info("Available interfaces:");
                    foreach ($lines as $line) {
                        if (strpos($line, ':') !== false && !strpos($line, 'Inter-')) {
                            $this->line("  " . trim($line));
                        }
                    }
                }

                // Test current database values
                $this->info("\nCurrent database values:");
                $this->info("  RX: {$server->network_rx}");
                $this->info("  TX: {$server->network_tx}");
                $this->info("  Speed: {$server->network_speed}");
                $this->info("  Last checked: {$server->last_checked_at}");

                // Calculate what the speed should be
                if ($server->last_checked_at && $server->network_rx > 0 && $server->network_tx > 0) {
                    $timeDiff = now()->diffInSeconds($server->last_checked_at);
                    if ($timeDiff > 0) {
                        $rxDiff = $rx - $server->network_rx;
                        $txDiff = $tx - $server->network_tx;
                        $calculatedSpeed = ($rxDiff + $txDiff) / $timeDiff;
                        
                        $this->info("\nSpeed calculation:");
                        $this->info("  Time difference: {$timeDiff} seconds");
                        $this->info("  RX difference: {$rxDiff} bytes");
                        $this->info("  TX difference: {$txDiff} bytes");
                        $this->info("  Calculated speed: {$calculatedSpeed} bytes/s (" . round($calculatedSpeed/1024/1024, 2) . " MB/s)");
                    }
                }

            } catch (\Exception $e) {
                $this->error("Error testing {$server->name}: " . $e->getMessage());
            }
        }
    }
}
