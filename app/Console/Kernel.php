<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * These commands may be used to schedule tasks to run on the server.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Update server metrics every minute
        $schedule->command('servers:update-metrics')->everyMinute();
        
        // Broadcast server status every minute
        $schedule->command('monitor:server')->everyMinute();
        
        // Monitor alerts every 5 minutes
        $schedule->command('alerts:monitor')
            ->everyFiveMinutes()
            ->withoutOverlapping()
            ->runInBackground();

        // Run the dedicated offline server update command every 5 seconds
        // This ensures downtime counters update in real-time
        // $schedule->command('server:update-offline-downtime')
        //     ->everyFiveSeconds()
        //     ->withoutOverlapping()
        //     ->runInBackground();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}