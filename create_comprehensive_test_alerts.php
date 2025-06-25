<?php

require 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Alert;
use App\Models\Server;
use App\Models\AlertThreshold;
use Carbon\Carbon;

echo "🚀 Creating comprehensive test alerts...\n\n";

// Get or create a server
$server = Server::first();
if (!$server) {
    $server = Server::create([
        'name' => 'Test Production Server',
        'ip_address' => '192.168.1.100',
        'status' => 'online',
        'os' => 'Ubuntu 22.04 LTS',
        'cpu_cores' => 8,
        'memory_total' => 16384, // 16GB
        'disk_total' => 512000,  // 512GB
    ]);
    echo "✅ Created test server: {$server->name}\n";
} else {
    echo "✅ Using existing server: {$server->name}\n";
}

// Get or create alert thresholds
$thresholds = [];
$thresholdConfigs = [
    ['metric_type' => 'cpu', 'threshold_value' => 80.0],
    ['metric_type' => 'memory', 'threshold_value' => 75.0],
    ['metric_type' => 'disk', 'threshold_value' => 70.0],
    ['metric_type' => 'network', 'threshold_value' => 60.0],
];

foreach ($thresholdConfigs as $config) {
    $threshold = AlertThreshold::where('server_id', $server->id)
        ->where('metric_type', $config['metric_type'])
        ->first();    if (!$threshold) {
        $threshold = AlertThreshold::create([
            'server_id' => $server->id,
            'metric_type' => $config['metric_type'],
            'threshold_value' => $config['threshold_value'],
            'comparison_operator' => '>',
            'alert_frequency' => 5,
            'notification_channel' => 'email',
            'created_by' => 1, // Admin user
            'is_active' => true,
        ]);
    }
    $thresholds[$config['metric_type']] = $threshold;
}

echo "✅ Thresholds configured\n\n";

// Clear existing test alerts
Alert::where('server_id', $server->id)->where('status', 'triggered')->delete();
echo "🧹 Cleared existing triggered alerts\n\n";

$alertsCreated = 0;

// 1. CRITICAL CPU ALERT (95%+) - Should get prominent styling
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $thresholds['cpu']->id,
    'alert_type' => 'cpu',
    'alert_message' => '🔴 CRITICAL: CPU usage has reached dangerous levels - immediate attention required!',
    'metric_value' => 97.8,
    'alert_time' => Carbon::now(),
    'status' => 'triggered'
]);
$alertsCreated++;
echo "🔴 CRITICAL CPU Alert (97.8%) - Excessive usage, prominent styling\n";

// 2. HIGH CPU ALERT (90-95%)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $thresholds['cpu']->id,
    'alert_type' => 'cpu',
    'alert_message' => '🟠 High CPU usage detected - system performance may be impacted',
    'metric_value' => 92.4,
    'alert_time' => Carbon::now()->subMinutes(8),
    'status' => 'triggered'
]);
$alertsCreated++;
echo "🟠 HIGH CPU Alert (92.4%) - Critical severity\n";

// 3. HIGH MEMORY ALERT (85%+)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $thresholds['memory']->id,
    'alert_type' => 'memory',
    'alert_message' => '🟠 Memory usage is elevated - consider investigating memory leaks',
    'metric_value' => 87.2,
    'alert_time' => Carbon::now()->subMinutes(15),
    'status' => 'triggered'
]);
$alertsCreated++;
echo "🟠 HIGH Memory Alert (87.2%) - Above 80% threshold\n";

// 4. MEDIUM DISK ALERT (78%)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $thresholds['disk']->id,
    'alert_type' => 'disk',
    'alert_message' => '🟡 Disk usage approaching capacity - consider cleanup or expansion',
    'metric_value' => 78.9,
    'alert_time' => Carbon::now()->subMinutes(25),
    'status' => 'triggered'
]);
$alertsCreated++;
echo "🟡 MEDIUM Disk Alert (78.9%) - Above 75% threshold\n";

// 5. MEDIUM CPU ALERT (Just above threshold)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $thresholds['cpu']->id,
    'alert_type' => 'cpu',
    'alert_message' => '🟡 CPU usage slightly elevated during peak hours',
    'metric_value' => 82.1,
    'alert_time' => Carbon::now()->subMinutes(35),
    'status' => 'triggered'
]);
$alertsCreated++;
echo "🟡 MEDIUM CPU Alert (82.1%) - Just above threshold\n";

// 6. LOW NETWORK ALERT
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $thresholds['network']->id,
    'alert_type' => 'network',
    'alert_message' => '🔵 Network latency has increased - monitoring connectivity',
    'metric_value' => 65.7,
    'alert_time' => Carbon::now()->subMinutes(45),
    'status' => 'triggered'
]);
$alertsCreated++;
echo "🔵 LOW Network Alert (65.7%) - Informational\n";

// 7. RESOLVED ALERT (for testing resolved state)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $thresholds['cpu']->id,
    'alert_type' => 'cpu',
    'alert_message' => '✅ CPU spike detected and resolved - temporary load increase',
    'metric_value' => 89.3,
    'alert_time' => Carbon::now()->subHours(2),
    'status' => 'resolved',
    'resolved_at' => Carbon::now()->subHour(),
    'resolved_by' => 1
]);
$alertsCreated++;
echo "✅ RESOLVED CPU Alert (89.3%) - Shows resolved styling\n";

// 8. ANOTHER CRITICAL ALERT (Different type)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $thresholds['memory']->id,
    'alert_type' => 'memory',
    'alert_message' => '🔴 CRITICAL: Memory exhaustion imminent - applications may crash!',
    'metric_value' => 94.6,
    'alert_time' => Carbon::now()->subMinutes(3),
    'status' => 'triggered'
]);
$alertsCreated++;
echo "🔴 CRITICAL Memory Alert (94.6%) - Urgent attention needed\n";

// 9. OLDER ALERT (Different timestamp)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $thresholds['disk']->id,
    'alert_type' => 'disk',
    'alert_message' => '🟡 Log files consuming significant disk space',
    'metric_value' => 76.4,
    'alert_time' => Carbon::now()->subHours(6),
    'status' => 'triggered'
]);
$alertsCreated++;
echo "🟡 MEDIUM Disk Alert (76.4%) - Older alert for variety\n";

// 10. PERFORMANCE ALERT (Generic type)
Alert::create([
    'server_id' => $server->id,
    'threshold_id' => $thresholds['cpu']->id,
    'alert_type' => 'performance',
    'alert_message' => '⚡ System performance degradation detected',
    'metric_value' => 88.7,
    'alert_time' => Carbon::now()->subMinutes(12),
    'status' => 'triggered'
]);
$alertsCreated++;
echo "⚡ HIGH Performance Alert (88.7%) - General performance issue\n";

echo "\n" . str_repeat("=", 60) . "\n";
echo "🎉 SUCCESS: Created {$alertsCreated} test alerts!\n";
echo str_repeat("=", 60) . "\n\n";

echo "📊 ALERT BREAKDOWN:\n";
echo "   🔴 Critical (90%+): 3 alerts\n";
echo "   🟠 High (80-89%):   2 alerts\n";
echo "   🟡 Medium (75-79%): 3 alerts\n";
echo "   🔵 Low (60-74%):    1 alert\n";
echo "   ✅ Resolved:        1 alert\n\n";

echo "🎯 TESTING SCENARIOS:\n";
echo "   • Prominent styling for 95%+ CPU alerts\n";
echo "   • Different severity colors and icons\n";
echo "   • Various alert types (CPU, Memory, Disk, Network)\n";
echo "   • Resolved alert with gray-out effect\n";
echo "   • Mixed timestamps for sorting tests\n";
echo "   • Progress bars showing metric vs threshold\n\n";

echo "🌐 VIEW YOUR ALERTS:\n";
echo "   Visit: http://serverpulse.test/alerts\n\n";

echo "🔧 TEST THE RESOLVE FUNCTIONALITY:\n";
echo "   • Click 'Resolve Alert' buttons\n";
echo "   • Watch for toast notifications\n";
echo "   • See alerts gray out when resolved\n";
echo "   • Test different severity levels\n\n";

echo "✨ The alerts are ready for testing!\n";
