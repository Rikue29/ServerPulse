<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Server;

class TestSSH extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:ssh {ip?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test SSH connection to server';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $ip = $this->argument('ip') ?? '192.168.159.128';
        
        $this->info("Testing SSH connection to: {$ip}");
        
        // Test basic connectivity first
        $this->info("1. Testing ping...");
        $pingResult = shell_exec("ping -n 1 {$ip}");
        if (strpos($pingResult, 'TTL=') !== false) {
            $this->info("   ✓ Host is reachable");
        } else {
            $this->error("   ✗ Host is not reachable");
            return 1;
        }
        
        // Test SSH port
        $this->info("2. Testing SSH port 22...");
        $connection = @fsockopen($ip, 22, $errno, $errstr, 5);
        if ($connection) {
            $this->info("   ✓ SSH port 22 is open");
            fclose($connection);
        } else {
            $this->error("   ✗ SSH port 22 is not accessible: {$errstr}");
            return 1;
        }
        
        // Test SSH with different credentials
        $this->info("3. Testing SSH connection with phpseclib...");
        
        $credentials = [
            ['user', 'password'],
            ['ubuntu', 'password'],
            ['root', 'password'],
            ['user', '123456'],
            ['ubuntu', '123456'],
        ];
        
        foreach ($credentials as $cred) {
            [$username, $password] = $cred;
            $this->info("   Trying: {$username}/{$password}");
            
            try {
                $ssh = new \phpseclib3\Net\SSH2($ip, 22);
                
                if ($ssh->login($username, $password)) {
                    $this->info("   ✓ SSH login successful with {$username}/{$password}");
                    
                    // Test a simple command
                    $result = $ssh->exec('whoami');
                    $this->info("   Command 'whoami' result: " . trim($result));
                    
                    $result = $ssh->exec('uptime');
                    $this->info("   Command 'uptime' result: " . trim($result));
                    
                    // Update the server record with correct credentials
                    $server = Server::where('ip_address', $ip)->first();
                    if ($server) {
                        $server->update([
                            'ssh_user' => $username,
                            'ssh_password' => $password
                        ]);
                        $this->info("   ✓ Server credentials updated in database");
                    }
                    
                    return 0;
                } else {
                    $this->line("   ✗ Login failed with {$username}/{$password}");
                }
            } catch (\Exception $e) {
                $this->line("   ✗ SSH Error: " . $e->getMessage());
            }
        }
        
        $this->error("Unable to establish SSH connection with any of the tested credentials.");
        $this->info("\nPlease check:");
        $this->info("- SSH service is running on the Ubuntu VM");
        $this->info("- The correct username and password");
        $this->info("- Firewall settings on the Ubuntu VM");
        
        return 1;
    }
}
