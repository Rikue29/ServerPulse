# ServerPulse Real-time Monitoring Setup

This document explains how to set up ServerPulse for continuous real-time monitoring of your servers.

## How ServerPulse Monitoring Works

ServerPulse uses an intelligent monitoring system:
1. It collects server metrics using the `servers:update-metrics` command
2. It broadcasts these metrics in real-time using the `monitor:server` command
3. It uses adaptive timing to ensure reliable updates even if a collection cycle takes longer than expected

The monitoring runs in cycles to ensure each update completes successfully before starting the next one.

## Setup Options

Choose one of the following options to enable continuous monitoring:

### Option 1: Quick Start (Windows - Manual)

1. Double-click the `run-monitor.bat` file
2. A command window will open showing the monitoring activity
3. To stop monitoring, close the window or press Ctrl+C

### Option 2: Background Mode (Windows)

1. Double-click the `start-monitor.vbs` file
2. The monitoring will run in the background without any visible window
3. To stop it, use Task Manager to end the `cmd.exe` or `php.exe` processes

### Option 3: Auto-Start on Windows Boot (Recommended)

This option makes the monitoring start automatically when Windows starts:

1. Right-click `setup-autostart.bat` and select "Run as Administrator"
2. The script will create a shortcut in your Windows Startup folder
3. The monitoring will now start automatically in the background each time Windows starts
4. To verify it's working, check Task Manager for cmd.exe and php.exe processes

### Option 4: Linux Cron Job

If you're running ServerPulse on Linux:

1. SSH into your server
2. Navigate to your ServerPulse directory
3. Run: `chmod +x setup-linux-cron.sh`
4. Execute: `./setup-linux-cron.sh`

This will set up a cron job to run the scheduler every minute.

## Troubleshooting

If you're not seeing real-time updates:

1. Check that the monitoring script is running
2. Try running `test-monitor.bat` to verify that commands work properly
3. Verify your Pusher credentials in `.env` file
4. Check the Laravel logs in `storage/logs/laravel.log`
5. Make sure your browser console doesn't show any JavaScript errors
6. Try manually running `php artisan monitor:server` to test the broadcast functionality

## Managing Auto-Start

### Disabling Auto-Start
If you need to stop the monitoring from starting automatically:
1. Press Win+R and type `shell:startup`
2. This opens your Startup folder
3. Delete the "ServerPulse Monitoring" shortcut

### Re-enabling Auto-Start
If you need to set up auto-start again:
1. Run `setup-autostart.bat` as Administrator

## Manual Monitoring

You can always trigger a manual update by running:
```bash
php artisan monitor:server
```

This will collect fresh metrics and broadcast them to your dashboard immediately.
