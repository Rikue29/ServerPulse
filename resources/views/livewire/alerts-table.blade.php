<div wire:key="alerts-table-{{ now()->timestamp }}" wire:poll.5s>
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Alert Management</h1>
                <p class="mt-2 text-gray-600">Monitor and manage system alerts</p>
            </div>
            <div class="flex items-center space-x-4">
                <!-- Quick Stats -->
                <div class="flex space-x-4">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-4 py-2">
                        <div class="text-sm text-gray-600">Unresolved</div>
                        <div class="text-xl font-bold text-red-600">{{ $stats['total_unresolved'] }}</div>
                    </div>
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 px-4 py-2">
                        <div class="text-sm text-gray-600">Critical</div>
                        <div class="text-xl font-bold text-red-800">{{ $stats['critical'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Recent Alerts Card (only for unresolved view and if there are recent unresolved alerts) -->
        @if(!$showResolved && $recentAlerts->count() > 0)
        <div class="bg-white rounded-lg shadow-sm border border-red-200 mb-6 p-6 border-l-4 border-l-red-500">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <svg class="w-5 h-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                Recent Critical Alerts
            </h3>
            <div class="space-y-3">
                @foreach($recentAlerts as $alert)
                <div wire:key="recent-alert-{{ $alert->id }}" class="flex items-center justify-between p-3 bg-red-50 rounded-lg border border-red-200">
                    <div class="flex items-center space-x-3">
                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                        <div>
                            <div class="font-medium text-gray-900">{{ $alert->server->name ?? 'Server #' . $alert->server_id }}</div>
                            <div class="text-sm text-gray-600">{{ $alert->alert_message }}</div>
                        </div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span class="px-2 py-1 text-xs rounded-full {{ $alert->severity_color }}">
                            {{ ucfirst($alert->severity) }}
                        </span>
                        <button 
                            wire:click="resolveAlert({{ $alert->id }})"
                            wire:loading.attr="disabled"
                            wire:target="resolveAlert({{ $alert->id }})"
                            class="px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors disabled:opacity-50"
                        >
                            <span wire:loading.remove wire:target="resolveAlert({{ $alert->id }})">Resolve</span>
                            <span wire:loading wire:target="resolveAlert({{ $alert->id }})" class="inline-flex items-center">
                                <svg class="animate-spin h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Resolving...
                            </span>
                        </button>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- Filters and Controls -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <input 
                        type="text" 
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search alerts..."
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500"
                    >
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select wire:model.live="showResolved" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="0">All Alerts</option>
                        <option value="1">Resolved Only</option>
                    </select>
                </div>

                <!-- Severity Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                    <select wire:model.live="filterSeverity" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Severities</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                    <select wire:model.live="filterType" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Types</option>
                        <option value="performance">Performance</option>
                        <option value="network">Network</option>
                        <option value="heartbeat">Heartbeat</option>
                        <option value="system">System</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Alerts Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            @if($alerts->count() > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('alert_time')">
                                <div class="flex items-center space-x-1">
                                    <span>Time</span>
                                    @if($sortBy === 'alert_time')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('server_id')">
                                <div class="flex items-center space-x-1">
                                    <span>Server</span>
                                    @if($sortBy === 'server_id')
                                        <i class="fas fa-sort-{{ $sortDirection === 'asc' ? 'up' : 'down' }} text-blue-500"></i>
                                    @endif
                                </div>
                            </th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Value</th>
                            @if($showResolved)
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resolved</th>
                            @else
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($alerts as $alert)
                        <tr wire:key="alert-{{ $alert->id }}" class="hover:bg-gray-50 {{ $alert->status === 'resolved' ? 'bg-gray-100 opacity-60' : ($alert->severity === 'critical' ? 'bg-red-25' : '') }}">>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $alert->status === 'resolved' ? 'text-gray-500' : 'text-gray-900' }}">
                                <div>{{ $alert->alert_time->format('M d, Y') }}</div>
                                <div class="text-xs text-gray-500">{{ $alert->alert_time->format('H:i:s') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $alert->status === 'resolved' ? 'bg-gray-200 text-gray-600' : $alert->severity_color }}">
                                    {{ ucfirst($alert->severity) }}
                                    @if($alert->status === 'resolved')
                                        <span class="ml-1">✓</span>
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-2 h-2 rounded-full mr-2 {{ $alert->server->status === 'online' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                    <div>
                                        <div class="text-sm font-medium {{ $alert->status === 'resolved' ? 'text-gray-500' : 'text-gray-900' }}">{{ $alert->server->name ?? 'Unknown' }}</div>
                                        <div class="text-xs text-gray-500">{{ $alert->server->ip_address }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs {{ $alert->status === 'resolved' ? 'bg-gray-200 text-gray-600' : 'bg-blue-100 text-blue-800' }} rounded-full">
                                    {{ ucfirst($alert->alert_type) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm {{ $alert->status === 'resolved' ? 'text-gray-500 line-through' : 'text-gray-900' }} max-w-xs truncate">
                                {{ $alert->alert_message }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $alert->status === 'resolved' ? 'text-gray-500' : 'text-gray-900' }}">
                                <div class="flex items-center">
                                    <span class="font-medium">{{ number_format($alert->metric_value, 1) }}%</span>
                                    <span class="text-xs text-gray-500 ml-1">/ {{ $alert->threshold->threshold_value }}%</span>
                                </div>
                            </td>
                            @if($showResolved)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                @if($alert->resolved_at)
                                <div>{{ $alert->resolved_at->format('M d, Y H:i') }}</div>
                                @if($alert->resolvedBy)
                                <div class="text-xs text-gray-500">by {{ $alert->resolvedBy->name }}</div>
                                @endif
                                @endif
                            </td>
                            @else
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                @if($alert->status === 'resolved')
                                    <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-xs rounded-md">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Resolved
                                    </span>
                                @else
                                    <button 
                                    wire:click="resolveAlert({{ $alert->id }})"
                                    wire:loading.attr="disabled"
                                    wire:target="resolveAlert({{ $alert->id }})"
                                    class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs rounded-md hover:bg-green-700 transition-colors duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <span wire:loading.remove wire:target="resolveAlert({{ $alert->id }})">
                                        <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Resolve
                                    </span>
                                    <span wire:loading wire:target="resolveAlert({{ $alert->id }})" class="inline-flex items-center">
                                        <svg class="animate-spin -ml-1 mr-2 h-3 w-3 text-white" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        Resolving...
                                    </span>
                                    </button>
                                @endif
                            </td>
                            @endif
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $alerts->links() }}
            </div>
            @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <i class="fas fa-bell-slash text-gray-400 text-4xl mb-4"></i>
                <h3 class="text-lg font-medium text-gray-900 mb-2">
                    @if($showResolved)
                        No resolved alerts found
                    @else
                        No unresolved alerts
                    @endif
                </h3>
                <p class="text-gray-500">
                    @if($showResolved)
                        There are no resolved alerts matching your criteria.
                    @else
                        Great! All systems are running smoothly.
                    @endif
                </p>
            </div>
            @endif
        </div>

    <!-- Loading Indicator -->
    <div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Loading alerts...</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('livewire:init', () => {
    // Global toast handler
    Livewire.on('show-toast', (event) => {
        console.log('Toast event received:', event);
        if (window.toastManager && event.length > 0) {
            const data = event[0];
            const { type, title, message } = data;
            window.toastManager[type](title, message);
        }
    });
    
    // Listen for alert resolved events
    Livewire.on('alert-resolved', (event) => {
        // Component will auto-refresh via listener
        console.log('Alert resolved event received:', event);
    });
    
    // Listen for external resolve events (e.g., from navigation dropdown)
    window.addEventListener('alert-resolved-external', function(event) {
        Livewire.dispatch('refresh-alerts');
        Livewire.dispatch('alertResolvedFromDropdown', { alertId: event.detail.alertId });
    });
    
    // Listen for custom events from other components
    document.addEventListener('alert-resolved-globally', function(event) {
        Livewire.dispatch('refresh-alerts');
        Livewire.dispatch('alertResolvedFromDropdown', { alertId: event.detail.alertId });
    });
    
    // Add Livewire error handling
    document.addEventListener('livewire:error', function (e) {
        console.error('Livewire Error:', e.detail);
        if (window.toastManager) {
            window.toastManager.error('Error', 'A network or server error occurred');
        }
    });
});

// Make alert resolution function globally available
window.refreshAlertsTable = function() {
    if (typeof Livewire !== 'undefined') {
        Livewire.dispatch('refresh-alerts');
    }
};

// Debug function for testing
window.testResolveAlert = function(alertId) {
    console.log('Testing resolve for alert:', alertId);
    fetch(`/alerts/${alertId}/resolve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json',
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);
        return response.json();
    })
    .then(data => {
        console.log('Response data:', data);
    })
    .catch(error => {
        console.error('Fetch error:', error);
    });
};
</script>
