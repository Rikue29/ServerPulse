<div class="min-h-screen bg-gray-50" wire:poll.10s>
    <!-- Header Section -->
    <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">System Alerts</h1>
                    <p class="mt-2 text-gray-600">Monitor and manage system performance alerts</p>
                    
                    <!-- Debug Test Button -->
                    <button wire:click="testLivewire" class="mt-2 px-3 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600">
                        Test Livewire
                    </button>
                </div>
                <div class="flex items-center space-x-6 w-full">
                    <!-- Modern Stats Cards -->
                    <div class="flex flex-row gap-4 w-full justify-center md:justify-between">
                        <!-- Critical Alerts Card -->
                        <div class="relative group flex-1 min-w-[180px] max-w-xs">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-red-600 to-red-400 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-300"></div>
                            <div class="relative bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 mb-1">Critical</p>
                                        <p class="text-3xl font-bold text-red-600">{{ $stats['critical'] }}</p>
                                    </div>
                                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-50 text-red-800">
                                        Urgent Action Required
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- High Priority Card -->
                        <div class="relative group flex-1 min-w-[180px] max-w-xs">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-orange-500 to-yellow-400 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-300"></div>
                            <div class="relative bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 mb-1">High</p>
                                        <p class="text-3xl font-bold text-orange-600">{{ $stats['high'] }}</p>
                                    </div>
                                    <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-orange-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-50 text-orange-800">
                                        Monitor Closely
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Total Active Card -->
                        <div class="relative group flex-1 min-w-[180px] max-w-xs">
                            <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl blur opacity-20 group-hover:opacity-40 transition duration-300"></div>
                            <div class="relative bg-white rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 transform hover:scale-105 p-6">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-sm font-medium text-gray-600 mb-1">Total Active</p>
                                        <p class="text-3xl font-bold text-blue-600">{{ $stats['total_unresolved'] }}</p>
                                    </div>
                                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                                        <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-50 text-blue-800">
                                        All Alerts
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <!-- Filters Section -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Search Alerts</label>
                    <div class="relative">
                        <input 
                            type="text" 
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search by server, message..."
                            class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        >
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                    <select wire:model.live="showResolved" class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="0">Active Alerts</option>
                        <option value="1">Resolved Alerts</option>
                    </select>
                </div>

                <!-- Severity Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Severity</label>
                    <select wire:model.live="filterSeverity" class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Severities</option>
                        <option value="critical">Critical</option>
                        <option value="high">High</option>
                        <option value="medium">Medium</option>
                        <option value="low">Low</option>
                    </select>
                </div>

                <!-- Type Filter -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Alert Type</label>
                    <select wire:model.live="filterType" class="w-full py-3 px-4 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">All Types</option>
                        <option value="performance">CPU/Performance</option>
                        <option value="memory">Memory</option>
                        <option value="system">Disk/System</option>
                        <option value="network">Network</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Alerts Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            @if($alerts->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('alert_time')">
                                    <div class="flex items-center space-x-1">
                                        <span>Timestamp</span>
                                        @if($sortBy === 'alert_time')
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer" wire:click="sortBy('server_id')">
                                    <div class="flex items-center space-x-1">
                                        <span>Server</span>
                                        @if($sortBy === 'server_id')
                                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $sortDirection === 'asc' ? 'M5 15l7-7 7 7' : 'M19 9l-7 7-7-7' }}"></path>
                                            </svg>
                                        @endif
                                    </div>
                                </th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alert Details</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Metric Value</th>
                                <th scope="col" class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($alerts as $alert)
                        <tr wire:key="alert-{{ $alert->id }}" 
                            class="hover:bg-gray-50 transition-all duration-300 {{ $alert->row_style }} {{ $alert->status === 'resolved' ? 'opacity-60 bg-gray-50' : '' }}"
                            style="{{ $alert->status === 'resolved' ? 'filter: grayscale(50%);' : '' }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm {{ $alert->status === 'resolved' ? 'text-gray-500' : 'text-gray-900' }}">
                                <div class="flex flex-col">
                                    <span class="font-medium">{{ $alert->alert_time->format('M d, Y') }}</span>
                                    <span class="text-xs text-gray-500">{{ $alert->alert_time->format('H:i:s') }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-1 text-xs font-semibold rounded-full {{ $alert->status === 'resolved' ? 'bg-gray-200 text-gray-600' : $alert->severity_color }}">
                                    @if($alert->severity === 'critical')
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                    </svg>
                                    @elseif($alert->severity === 'high')
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                                    @else
                                        <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                    {{ ucfirst($alert->severity) }}
                                    @if($alert->status === 'resolved')
                                        <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                    @endif
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        <div class="w-2.5 h-2.5 rounded-full {{ $alert->server->status === 'online' ? 'bg-green-500' : 'bg-red-500' }}"></div>
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <div class="text-sm font-medium text-gray-900 truncate">{{ $alert->server->name ?? 'Unknown Server' }}</div>
                                        <div class="text-xs text-gray-500">{{ $alert->server->ip_address ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-col space-y-2">
                                    <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium rounded-full {{ $alert->status === 'resolved' ? 'bg-gray-100 text-gray-600' : 'bg-blue-100 text-blue-800' }}">
                                        {{ ucfirst($alert->alert_type) }}
                                    </span>
                                    <div class="text-sm {{ $alert->status === 'resolved' ? 'text-gray-500 line-through' : 'text-gray-900' }} max-w-xs">
                                        {{ $alert->alert_message }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex flex-col space-y-1">
                                    <div class="flex items-center space-x-2">
                                        <span class="text-lg font-bold {{ $alert->status === 'resolved' ? 'text-gray-500' : ($alert->severity === 'critical' ? 'text-red-600' : 'text-gray-900') }}">
                                            {{ number_format($alert->metric_value, 1) }}%
                                        </span>
                                        <span class="text-xs text-gray-400">/ {{ $alert->threshold->threshold_value }}%</span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ $alert->metric_value > $alert->threshold->threshold_value ? 'bg-red-500' : 'bg-green-500' }}" 
                                             style="width: {{ min(100, ($alert->metric_value / max($alert->threshold->threshold_value, 100)) * 100) }}%"></div>
                                    </div>
                                </div>
                            </td>
                            @if($showResolved)
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($alert->resolved_at)
                                    <div class="flex flex-col space-y-1">
                                        <span class="text-sm text-gray-900">{{ $alert->resolved_at->format('M d, Y') }}</span>
                                        <span class="text-xs text-gray-500">{{ $alert->resolved_at->format('H:i:s') }}</span>
                                        @if($alert->resolvedBy)
                                            <span class="text-xs text-blue-600">by {{ $alert->resolvedBy->name }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-sm text-gray-400">—</span>
                                @endif
                            </td>
                            @else
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($alert->status === 'resolved')
                                    <div class="inline-flex items-center px-3 py-1.5 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg">
                                        <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                        </svg>
                                        Resolved
                                    </div>
                                @else
                                    <button 
                                        wire:click="resolveAlert({{ $alert->id }})"
                                        wire:loading.attr="disabled"
                                        wire:target="resolveAlert"
                                        onclick="console.log('Button clicked for alert {{ $alert->id }}'); setTimeout(() => { console.log('Livewire component:', window.Livewire); }, 100);"
                                        class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-green-400 text-white text-sm font-medium rounded-lg transition-all duration-200 transform hover:scale-105 disabled:hover:scale-100 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 shadow-sm"
                                        title="Resolve this alert if the metric value has returned to normal"
                                        id="resolve-btn-{{ $alert->id }}"
                                    >
                                        <span wire:loading.remove wire:target="resolveAlert" class="flex items-center">
                                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                            </svg>
                                            Resolve Alert
                                        </span>
                                        <span wire:loading wire:target="resolveAlert" class="flex items-center">
                                            <svg class="animate-spin w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
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

    <!-- New Alert Banner -->
    <div id="new-alert-banner" style="display:none; position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:9999;" class="bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg flex items-center space-x-3 animate-bounce">
        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        <span id="new-alert-message">New alert triggered!</span>
        <button onclick="document.getElementById('new-alert-banner').style.display='none'" class="ml-4 bg-white text-red-600 px-2 py-1 rounded">Dismiss</button>
    </div>

    <!-- Loading Indicator -->
    <div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Loading alerts...</span>
        </div>
    </div>
</div>

<!-- Debug and Script Section -->
<script>
// Check if Livewire is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded');
    console.log('Livewire available:', typeof window.Livewire !== 'undefined');
    console.log('Alpine available:', typeof window.Alpine !== 'undefined');
});

document.addEventListener('livewire:init', () => {
    console.log('Livewire initialized');
    
    // Global toast handler
    Livewire.on('show-toast', (event) => {
        // Accept both array and object payloads
        let data = event;
        if (Array.isArray(event)) data = event[0];
        if (window.toastManager && data && data.type) {
            window.toastManager[data.type](data.title, data.message);
        }
    });
    
    // Listen for alert resolved events
    Livewire.on('alert-resolved', (event) => {
        // Accept both array and object payloads
        let alertId = event && event.alertId ? event.alertId : (Array.isArray(event) && event[0] && event[0].alertId ? event[0].alertId : null);
        if(alertId) {
            const row = document.querySelector(`[wire\\:key="alert-${alertId}"]`);
            if(row) {
                row.style.transition = 'opacity 0.5s, height 0.5s';
                row.style.opacity = '0';
                setTimeout(() => { row.style.display = 'none'; }, 500);
            }
        }
    });
});

// Listen for new alert events and show banner
window.Echo && window.Echo.channel('alerts')
    .listen('NewAlertCreated', (event) => {
        const banner = document.getElementById('new-alert-banner');
        const msg = document.getElementById('new-alert-message');
        if (banner && msg) {
            msg.textContent = `🚨 New ${event.severity} alert: ${event.message}`;
            banner.style.display = 'flex';
        }
        console.log('New alert banner shown:', event);
    });
</script>
