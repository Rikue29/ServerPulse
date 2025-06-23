use App\Models\NotificationPreference;
use App\Models\User; // In case you need to query user emails

class AlertController extends Controller
{
    public function trigger(Request $request)
    {
        $validated = $request->validate([
            'threshold_id' => 'required|exists:alert_thresholds,id',
            'server_id'    => 'required|exists:servers,id',
            'metric_value' => 'required|numeric',
        ]);

        \Log::info('Trigger request received', $validated);

        $threshold = AlertThreshold::find($validated['threshold_id']);

        $typeMapping = [
            'CPU' => 'performance',
            'RAM' => 'performance',
            'Disk' => 'performance',
        ];

        $alertType = $typeMapping[$threshold->metric_type] ?? 'performance';
        $severity = 'critical'; // You can change this logic later if needed

        if ($validated['metric_value'] >= $threshold->threshold_value) {
            $alert = Alert::create([
                'threshold_id'  => $threshold->id,
                'server_id'     => $validated['server_id'],
                'metric_value'  => $validated['metric_value'],
                'status'        => 'triggered',
                'alert_type'    => $alertType,
                'alert_message' => "{$threshold->metric_type} threshold exceeded: {$validated['metric_value']}%",
                'alert_time'    => Carbon::now(),
            ]);

            // Severity levels for comparison
            $severityRank = ['low' => 1, 'medium' => 2, 'high' => 3, 'critical' => 4];

            // Get users who match this alert_type AND severity level or lower
            $matchingPrefs = NotificationPreference::where('alert_type', $alertType)
                ->get()
                ->filter(function ($pref) use ($severityRank, $severity) {
                    return $severityRank[$pref->severity_min] <= $severityRank[$severity] && $pref->via_email;
                });

            $emails = $matchingPrefs->map(fn($pref) => $pref->user->email)->unique()->toArray();

            \Log::info('Sending alert to: ', $emails);
            \Log::info('Alert object:', $alert->toArray());

            foreach ($emails as $email) {
                Notification::route('mail', $email)->notify(new AlertTriggered($alert));
            }

            return response()->json(['alert' => $alert], 201);
        }

        return response()->json(['message' => 'No threshold breached'], 200);
    }

    public function resolve($id)
    {
        $alert = Alert::findOrFail($id);

        if ($alert->status === 'resolved') {
            return response()->json(['message' => 'Alert is already resolved.'], 200);
        }

        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        return response()->json(['message' => 'Alert marked as resolved.', 'alert' => $alert], 200);
    }
}
