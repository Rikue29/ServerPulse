<div class="space-y-4">

    <!-- Critical Alerts Summary -->
    <div class="bg-gradient-to-r from-red-50 to-orange-50 border border-red-200 rounded-xl p-4">
        <div class="flex items-center justify-between mb-3">
            <div class="flex items-center space-x-3">
                <div class="p-2 bg-red-100 rounded-full">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
                <h2 class="text-lg font-semibold text-red-900">Infrastructure Alert Dashboard</h2>
            </div>
            <div class="text-sm text-red-700">
                <i class="fas fa-clock mr-1"></i> Real-time monitoring
            </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="bg-white/60 rounded-lg p-4 border border-red-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-red-600 font-medium">Critical Alerts</p>
                        <p class="text-2xl font-bold text-red-900">{{ $stats['errors'] }}</p>
                    </div>
                    <i class="fas fa-fire text-red-500 text-xl"></i>
                </div>
            </div>
            <div class="bg-white/60 rounded-lg p-4 border border-yellow-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-yellow-600 font-medium">Threshold Warnings</p>
                        <p class="text-2xl font-bold text-yellow-900">{{ $stats['warnings'] }}</p>
                    </div>
                    <i class="fas fa-chart-line text-yellow-500 text-xl"></i>
                </div>
            </div>
            <div class="bg-white/60 rounded-lg p-4 border border-blue-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-blue-600 font-medium">Total Events</p>
                        <p class="text-2xl font-bold text-blue-900">{{ $stats['total'] }}</p>
                    </div>
                    <i class="fas fa-database text-blue-500 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Actions & Filters -->
    <div class="bg-white/90 rounded-xl p-3 mb-3 shadow border flex flex-wrap gap-2 items-center justify-between">
        <div class="flex flex-wrap gap-2 items-center flex-1">
            <input wire:model.lazy="search"
                type="search"
                placeholder="Search logs, levels, servers..."
                class="flex-1 min-w-[200px] rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-200 text-sm px-3 py-1.5" />

            <select wire:model="selectedLevel"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-200 text-sm py-1.5">
                <option value="">All Levels</option>
                <option value="error">Errors</option>
                <option value="warning">Warnings</option>
                <option value="info">Info</option>
            </select>

            <select wire:model="selectedServer"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-200 text-sm py-1.5">
                <option value="">All Servers</option>
                @foreach($servers as $server)
                    <option value="{{ $server->id }}">{{ $server->name }}</option>
                @endforeach
            </select>

            <button wire:click="clearFilters"
                class="text-gray-600 text-xs hover:text-red-600 px-2 py-1.5 flex items-center border rounded bg-gray-100">
                <i class="fas fa-times-circle mr-1"></i> Clear
            </button>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('logs.export', request()->query()) }}"
                class="bg-green-600 text-white text-xs px-3 py-1.5 rounded hover:bg-green-700 flex items-center font-medium border border-green-700">
                <i class="fas fa-download mr-2"></i> Export CSV
            </a>

            <button type="button" onclick="window.print()"
                class="bg-gray-600 text-white text-xs px-3 py-1.5 rounded hover:bg-gray-700 flex items-center font-medium border border-gray-700">
                <i class="fas fa-print mr-2"></i> Print
            </button>

            <label class="inline-flex items-center text-xs">
                <input type="checkbox" wire:model="autoRefresh" class="form-checkbox rounded text-indigo-600" />
                <span class="ml-2">Auto Refresh</span>
            </label>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-2xl shadow border overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Timestamp</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Level</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Server</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Message</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($logs as $log)
                    <tr class="hover:bg-indigo-50/70 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            <span class="font-mono">{{ $log->created_at->format('Y-m-d H:i:s') }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->level === 'error' || $log->level === 'critical')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-800 border border-red-200">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> CRITICAL
                                </span>
                            @elseif($log->level === 'warning' || $log->level === 'warn')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 border border-yellow-200">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> WARNING
                                </span>
                            @elseif($log->level === 'info' || $log->level === 'information')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800 border border-blue-200">
                                    <i class="fas fa-info-circle mr-1"></i> INFO
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800 border border-gray-200">
                                    <i class="fas fa-question-circle mr-1"></i> {{ strtoupper($log->level) }}
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-700">
                            <div class="flex flex-col">
                                <span class="font-medium">{{ $log->server->name ?? '-' }}</span>
                                @if($log->server && $log->server->ip_address)
                                    <span class="text-gray-500 text-xs">{{ $log->server->ip_address }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-800">
                            <div class="flex flex-col space-y-1">
                                <span class="font-medium">{{ \Illuminate\Support\Str::limit($log->message, 60) }}</span>
                                @php
                                    $context = $log->context;
                                    if (is_string($context)) {
                                        $context = json_decode($context, true) ?? [];
                                    } elseif (!is_array($context)) {
                                        $context = [];
                                    }
                                    $alertType = $context['alert_type'] ?? null;
                                @endphp
                                @if($alertType)
                                    <span class="text-xs px-2 py-1 rounded @if($log->level === 'error') bg-red-50 text-red-600 @elseif($log->level === 'warning') bg-yellow-50 text-yellow-600 @else bg-blue-50 text-blue-600 @endif">
                                        @switch($alertType)
                                            @case('cpu_spike')
                                                🔥 CPU Overload
                                                @break
                                            @case('memory_warning')
                                                💾 Memory Alert
                                                @break
                                            @case('disk_critical')
                                                💿 Disk Space
                                                @break
                                            @case('load_spike')
                                                ⚡ High Load
                                                @break
                                            @case('network_congestion')
                                                🌐 Network Issue
                                                @break
                                            @case('system_overload')
                                                🚨 System Overload
                                                @break
                                            @default
                                                📊 Infrastructure Alert
                                        @endswitch
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs">
                            <div class="flex gap-2">
                                <a href="{{ route('logs.show', $log) }}" 
                                   class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-xs font-medium transition"
                                   title="View Details">
                                    <i class="fas fa-eye mr-1"></i> View
                                </a>
                                <a href="{{ route('logs.report', $log) }}" 
                                   class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1 rounded text-xs font-medium transition"
                                   title="View Full Report">
                                    <i class="fas fa-file-alt mr-1"></i> Report
                                </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-gray-300 text-3xl mb-3"></i>
                                <p>No logs found</p>
                                <p class="text-xs">Try adjusting your search filters</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <!-- Pagination -->
        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        @endif
    </div>

    @if($autoRefresh)
        <script>
            setInterval(() => {
                Livewire.emit('refreshLogs');
            }, 30000); // Refresh every 30 seconds
        </script>
    @endif
</div>
