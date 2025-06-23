@extends('layouts.app')

@section('content')
<div class="max-w-xl mx-auto bg-white p-6 rounded shadow">
    <h2 class="text-xl font-semibold mb-4">Notification Preferences</h2>

    <form action="{{ route('notification-preferences.update') }}" method="POST">
        @csrf
        @method('PATCH')

        <!-- Delivery Channels -->
        <div class="mb-4">
            <label class="block font-medium">Delivery Methods:</label>
            <label><input type="checkbox" name="via_email" {{ $pref->via_email ? 'checked' : '' }}> Email</label><br>
            <label><input type="checkbox" disabled> Slack (coming soon)</label><br>
            <label><input type="checkbox" disabled> SMS (coming soon)</label>
        </div>

        <!-- Severity -->
        <div class="mb-4">
            <label for="severity_min" class="block font-medium">Minimum Severity:</label>
            <select name="severity_min" id="severity_min" class="w-full border rounded p-2">
                @foreach(['low', 'medium', 'high', 'critical'] as $level)
                    <option value="{{ $level }}" {{ $pref->severity_min === $level ? 'selected' : '' }}>
                        {{ ucfirst($level) }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Alert Types -->
        <div class="mb-4">
            <label class="block font-medium">Alert Types:</label>
            @foreach(['performance', 'log', 'heartbeat', 'system'] as $type)
                <label>
                    <input type="checkbox" name="alert_types[]" value="{{ $type }}"
                        {{ in_array($type, $userAlertTypes) ? 'checked' : '' }}>
                    {{ ucfirst($type) }}
                </label><br>
            @endforeach
        </div>

        <button class="bg-blue-500 text-white px-4 py-2 rounded">Save Preferences</button>
    </form>
</div>
@endsection
