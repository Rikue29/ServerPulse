<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Alert;

class TestAlert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:alert';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test alert status';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $alert = Alert::where('status', 'triggered')->first();
        if ($alert) {
            $this->info("Alert {$alert->id} status: {$alert->status}");
            $this->info("Alert message: {$alert->alert_message}");
            $this->info("Server: {$alert->server->name}");
            
            // Ask if we want to resolve it
            if ($this->confirm('Do you want to resolve this alert?')) {
                $alert->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                    'resolved_by' => 1,
                ]);
                $this->info("Alert {$alert->id} has been resolved!");
            }
        } else {
            $this->info("No unresolved alerts found");
        }
    }
}
