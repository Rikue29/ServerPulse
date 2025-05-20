namespace App\Http\Controllers;

use App\Models\Alert;
use App\Models\AlertThreshold;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AlertController extends Controller
{
    public function trigger(Request $request)
    {
        $validated = $request->validate([
            'threshold_id' => 'required|exists:alert_thresholds,id',
            'server_id'    => 'required|exists:servers,id',
            'metric_value' => 'required|numeric',
        ]);

        $threshold = AlertThreshold::find($validated['threshold_id']);

        // Only trigger if value exceeds threshold
        if ($validated['metric_value'] >= $threshold->metric_value) {
            $alert = Alert::create([
                'threshold_id'  => $threshold->id,
                'server_id'     => $validated['server_id'],
                'metric_value'  => $validated['metric_value'],
                'status'        => 'triggered',
                'alert_type'    => $threshold->alert_type,
                'alert_message' => ucfirst($threshold->alert_type) . " threshold exceeded: {$validated['metric_value']}%",
                'alert_time'    => Carbon::now(),
            ]);

            // You can call the notification logic here too
            // $this->notify($alert);

            return response()->json(['alert' => $alert], 201);
        }

        return response()->json(['message' => 'No threshold breached'], 200);
    }
}
