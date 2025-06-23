<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotificationPreference;

class NotificationPreferenceController extends Controller
{
    /**
     * Show the notification preferences form.
     */
    public function edit()
    {
        $user = auth()->user();

        // Get or create a default preference for the user
        $pref = NotificationPreference::firstOrCreate(
            ['user_id' => $user->id],
            [
                'alert_type' => 'performance',
                'severity_min' => 'medium',
                'via_email' => true,
                'via_slack' => false,
                'via_sms' => false,
            ]
        );

        return view('settings.notifications-settings', compact('pref'));
    }

    /**
     * Update the notification preferences.
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $data = $request->validate([
            'via_email' => 'nullable|boolean',
            'via_slack' => 'nullable|boolean',
            'via_sms' => 'nullable|boolean',
            'severity_min' => 'required|in:low,medium,high,critical',
            'alert_type' => 'required|in:performance,log,heartbeat,system',
        ]);

        $preference = NotificationPreference::updateOrCreate(
            ['user_id' => $user->id],
            [
                'alert_type' => $data['alert_type'],
                'severity_min' => $data['severity_min'],
                'via_email' => $request->has('via_email'),
                'via_slack' => $request->has('via_slack'),
                'via_sms' => $request->has('via_sms'),
            ]
        );

        return redirect()->route('notification-preferences.edit')->with('message', 'Preferences updated.');
    }
}
