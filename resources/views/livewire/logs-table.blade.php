<div class="space-y-10">

<!-- Stat Cards: Responsive, always in a single grid container -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-4 w-full mb-8">
    <!-- Total Logs -->
    <div class="bg-white p-6 rounded-2xl shadow border flex flex-col items-center justify-center">
        <span class="text-4xl text-indigo-500 mb-1"><i class="fas fa-list"></i></span>
        <span class="text-3xl font-extrabold text-gray-900">{{ $stats['total'] }}</span>
        <span class="text-base text-gray-500 mt-1">Total Logs</span>
    </div>
    <!-- Errors -->
    <div class="bg-white p-6 rounded-2xl shadow border flex flex-col items-center justify-center">
        <span class="text-4xl text-red-500 mb-1"><i class="fas fa-exclamation-triangle"></i></span>
        <span class="text-3xl font-extrabold text-red-700">{{ $stats['errors'] }}</span>
        <span class="text-base text-red-500 mt-1">Errors</span>
    </div>
    <!-- Warnings -->
    <div class="bg-white p-6 rounded-2xl shadow border flex flex-col items-center justify-center">
        <span class="text-4xl text-yellow-500 mb-1"><i class="fas fa-exclamation-circle"></i></span>
        <span class="text-3xl font-extrabold text-yellow-600">{{ $stats['warnings'] }}</span>
        <span class="text-base text-yellow-500 mt-1">Warnings</span>
    </div>
</div>

    <!-- Actions & Filters -->
    <div class="bg-white/90 rounded-xl p-4 mb-4 shadow border flex flex-wrap gap-3 items-center justify-between">
        <div class="flex flex-wrap gap-3 items-center flex-1">
            <input wire:model.debounce.300ms="search"
                type="search"
                placeholder="Search logs..."
                class="flex-1 min-w-[200px] rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-200 text-sm px-3 py-2" />

            <select wire:model="selectedLevel"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-200 text-sm">
                <option value="">All Levels</option>
                <option value="error">Errors</option>
                <option value="warning">Warnings</option>
                <option value="info">Info</option>
            </select>

            <select wire:model="selectedServer"
                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-200 text-sm">
                <option value="">All Servers</option>
                @foreach($servers as $server)
                    <option value="{{ $server->id }}">{{ $server->name }}</option>
                @endforeach
            </select>

            <button wire:click="clearFilters"
                class="text-gray-600 text-xs hover:text-red-600 px-2 py-2 flex items-center border rounded bg-gray-100">
                <i class="fas fa-times-circle mr-1"></i> Clear
            </button>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" wire:click="exportCsv"
                class="bg-green-600 text-black text-xs px-4 py-2 rounded hover:bg-green-700 flex items-center">
                <i class="fas fa-download mr-2"></i> Export CSV
            </button>

            <button type="button" onclick="window.print()"
                class="bg-blue-600 text-white text-xs px-4 py-2 rounded hover:bg-blue-700 flex items-center">
                <i class="fas fa-print mr-2"></i> Print
            </button>

            <label class="inline-flex items-center ml-2 text-xs">
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
                            @if($log->level === 'error')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> Error
                                </span>
                            @elseif($log->level === 'warning')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation-circle mr-1"></i> Warning
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                    <i class="fas fa-info-circle mr-1"></i> Info
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-700">
                            {{ $log->server->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-800">
                            {{ \Illuminate\Support\Str::limit($log->message, 80) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <a href="{{ route('logs.show', $log) }}"
                                class="inline-flex items-center px-3 py-1 text-xs font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                <i class="fas fa-eye mr-1"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-10 text-center text-gray-400 text-xs">No logs found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50">
            {{ $logs->links() }}
        </div>
    </div>

    @if($autoRefresh)
        <script>
            setInterval(() => Livewire.emit('refreshLogs'), 5000);
        </script>
    @endif

</div>
