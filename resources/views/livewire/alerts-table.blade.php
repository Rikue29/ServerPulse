<div class="p-6" wire:init="loadAlerts">
    <h2 class="text-2xl font-semibold mb-4 text-gray-800">Unresolved Alerts</h2>

    @if (session()->has('message'))
        <div class="mb-4 px-4 py-2 bg-green-100 text-green-800 rounded shadow-sm">
            {{ session('message') }}
        </div>
    @endif

    <div wire:loading.delay class="mb-4 text-sm text-blue-500">
        Loading alerts...
    </div>

    <table class="min-w-full border border-gray-300 bg-white shadow-sm rounded">
        <thead class="bg-gray-100 text-sm text-gray-700">
            <tr>
                <th class="px-4 py-2 text-left">Type</th>
                <th class="px-4 py-2 text-left">Message</th>
                <th class="px-4 py-2 text-left">Time</th>
                <th class="px-4 py-2 text-left">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($alerts as $alert)
                <tr class="border-t" wire:key="alert-{{ $alert->id }}">
                    <td class="px-4 py-2 capitalize">{{ $alert->alert_type }}</td>
                    <td class="px-4 py-2 text-sm text-gray-700">{{ $alert->alert_message }}</td>
                    <td class="px-4 py-2 text-xs text-gray-500">
                        {{ \Carbon\Carbon::parse($alert->alert_time)->diffForHumans() }}
                    </td>
                    <td class="px-4 py-2">
                        <button type="button" wire:click.prevent="resolveAlert({{ $alert->id }})"
                                class="bg-green-500 hover:bg-green-600 text-white text-sm px-3 py-1 rounded">
                            Mark as Resolved
                        </button>

                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center py-6 text-gray-500 text-sm">
                        No unresolved alerts
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
