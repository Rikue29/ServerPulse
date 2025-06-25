<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckServerData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-server-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks server and alert data in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('--- Servers in `servers` table ---');
        $servers = DB::table('servers')->get(['id', 'name', 'status']);
        if ($servers->isEmpty()) {
            $this->line('No servers found.');
        } else {
            $this->table(['ID', 'Name', 'Status'], $servers->map(fn($s) => [$s->id, $s->name, $s->status]));
        }

        $this->info('\n--- Distinct server_ids in `alerts` table ---');
        $alertServerIds = DB::table('alerts')->select('server_id')->distinct()->get();
        if ($alertServerIds->isEmpty()) {
            $this->line('No alerts found.');
        } else {
            $this->table(['server_id'], $alertServerIds->map(fn($a) => [$a->server_id]));
        }

        return 0;
    }
}
