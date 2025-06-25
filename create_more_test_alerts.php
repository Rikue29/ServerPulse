<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Alert;
use App\Models\Server;
use App\Models\AlertThreshold;
use Carbon\Carbon;

echo "Creating additional test alerts (performance type only)...\n";

$server = Server::first();
$threshold = AlertThreshold::first();

try {
    // High CPU alert
    $alert1 = new Alert();
    $alert1->server_id = $server->id;
    $alert1->threshold_id = $threshold->id;
    $alert1->alert_type = 'performance';
    $alert1->alert_message = 'High CPU usage - 92%';
    $alert1->metric_value = 92.4;
    $alert1->alert_time = Carbon::now()->subMinutes(5);
    $alert1->status = 'triggered';
    $alert1->save();
    echo "✅ High CPU alert (92.4%)\n";

    // Medium alert
    $alert2 = new Alert();
    $alert2->server_id = $server->id;
    $alert2->threshold_id = $threshold->id;
    $alert2->alert_type = 'performance';
    $alert2->alert_message = 'CPU elevated - 82%';
    $alert2->metric_value = 82.1;
    $alert2->alert_time = Carbon::now()->subMinutes(15);
    $alert2->status = 'triggered';
    $alert2->save();
    echo "✅ Medium CPU alert (82.1%)\n";

    // Low priority alert
    $alert3 = new Alert();
    $alert3->server_id = $server->id;
    $alert3->threshold_id = $threshold->id;
    $alert3->alert_type = 'performance';
    $alert3->alert_message = 'Performance monitor - 65%';
    $alert3->metric_value = 65.7;
    $alert3->alert_time = Carbon::now()->subMinutes(25);
    $alert3->status = 'triggered';
    $alert3->save();
    echo "✅ Low performance alert (65.7%)\n";

    echo "\n🎉 Created additional test alerts!\n";
    echo "You now have alerts with different severities:\n";
    echo "- CRITICAL (95%+): Red background, prominent styling\n";
    echo "- HIGH (90-94%): Critical severity, red badges\n";
    echo "- MEDIUM (75-89%): High/medium severity, orange/yellow badges\n";
    echo "- LOW (60-74%): Blue badges\n\n";
    
    echo "🔧 Test the resolve functionality:\n";
    echo "1. Click any 'Resolve Alert' button\n";
    echo "2. Watch the loading spinner\n";
    echo "3. See the toast notification (success/error)\n";
    echo "4. Alert should gray out if resolved\n";
    echo "5. Resolved alerts show with checkmark\n\n";
    
    echo "Visit: http://serverpulse.test/alerts\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
