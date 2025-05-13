<div class="py-6 px-4 sm:px-6 lg:px-8">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Total Logs -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="text-gray-500 text-sm font-medium">Total Logs</div>
            <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['total_logs'] }}</div>
        </div>

        <!-- Critical Logs -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="text-gray-500 text-sm font-medium">Critical Logs</div>
            <div class="mt-2 text-3xl font-semibold text-red-600">{{ $stats['critical_logs'] }}</div>
        </div>

        <!-- Warning Logs -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="text-gray-500 text-sm font-medium">Warning Logs</div>
            <div class="mt-2 text-3xl font-semibold text-yellow-600">{{ $stats['warning_logs'] }}</div>
        </div>

        <!-- Recent Logs -->
        <div class="bg-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="text-gray-500 text-sm font-medium">Recent Logs (24h)</div>
            <div class="mt-2 text-3xl font-semibold text-gray-900">{{ $stats['recent_logs'] }}</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white shadow-sm rounded-lg p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <input 
                    wire:model.live="search" 
                    type="text" 
                    placeholder="Search logs..." 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >
            </div>

            <!-- Type Filter -->
            <div class="w-full sm:w-48">
                <select 
                    wire:model.live="type" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >
                    <option value="">All Types</option>
                    <option value="CPU">CPU</option>
                    <option value="Memory">Memory</option>
                    <option value="Disk">Disk</option>
                    <option value="System">System</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div class="w-full sm:w-48">
                <select 
                    wire:model.live="status" 
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                >
                    <option value="">All Statuses</option>
                    <option value="Critical">Critical</option>
                    <option value="Warning">Warning</option>
                    <option value="Info">Info</option>
                </select>
            </div>

            <!-- Clear Filters -->
            <button 
                wire:click="resetFilters" 
                class="w-full sm:w-auto px-4 py-2 bg-white border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
                Clear Filters
            </button>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Server</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($logs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->timestamp->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $log->type }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $log->server }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if($log->status === 'Critical') bg-red-100 text-red-800
                                @elseif($log->status === 'Warning') bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800 @endif">
                                {{ $log->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $log->message }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="px-4 py-3 border-t border-gray-200">
            {{ $logs->links() }}
        </div>
    </div>
</div> 