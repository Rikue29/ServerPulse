<?php
$alert = App\Models\Alert::where('status', 'triggered')->first();
if ($alert) {
    echo "Alert {$alert->id} status: {$alert->status}\n";
    echo "Alert message: {$alert->alert_message}\n";
    echo "Server: {$alert->server->name}\n";
} else {
    echo "No unresolved alerts found\n";
}
