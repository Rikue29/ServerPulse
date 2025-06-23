<?php

require_once 'vendor/autoload.php';

use Illuminate\Foundation\Application;

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    $log = App\Models\Log::with('server')->first();
    
    if ($log) {
        echo "✅ PDF Test Ready\n";
        echo "Log ID: " . $log->id . "\n";
        echo "Level: " . $log->level . "\n";
        echo "Server: " . ($log->server ? $log->server->name : 'No server') . "\n";
        echo "Message: " . substr($log->message, 0, 50) . "...\n";
        echo "\nRoutes available:\n";
        echo "- View details: /logs/" . $log->id . "\n";
        echo "- Download PDF: /logs/" . $log->id . "/download\n";
        echo "- View report: /logs/" . $log->id . "/report\n";
    } else {
        echo "❌ No logs found\n";
    }
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
