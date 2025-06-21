@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Servers</h1>
                <p class="text-sm text-gray-500 mt-1">Manage and monitor your server infrastructure</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="flex items-center text-sm text-green-600">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                    {{ $servers->count() }} servers online
                </div>
            </div>
        </div>
    </div>

    <div class="p-6">
        @if (session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
        @endif

        @if (session('agent_info'))
            <div class="mb-6 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded-lg relative" role="alert">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <h4 class="font-semibold mb-2">📡 Agent Installation Instructions</h4>
                        <p class="text-sm mb-3">To enable real-time monitoring for this server, install the ServerPulse agent:</p>
                        <div class="bg-blue-50 p-3 rounded border border-blue-200">
                            <p class="text-xs text-blue-600 mb-2">1. SSH to your server ({{ session('agent_info')['server_ip'] }})</p>
                            <p class="text-xs text-blue-600 mb-2">2. Run the following commands:</p>
                            <code class="text-xs bg-blue-900 text-blue-100 p-2 rounded block font-mono whitespace-pre-wrap">wget https://github.com/shane-kennedy-se/serverpulse-agent/archive/main.zip
unzip main.zip && cd serverpulse-agent-main
sudo chmod +x install.sh && sudo ./install.sh</code>
                            <p class="text-xs text-blue-600 mt-2">3. The agent will automatically register and start monitoring</p>
                        </div>
                    </div>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-blue-400 hover:text-blue-600">
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        @endif
        <!-- Search and Add Server Section -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex justify-between items-center">
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" placeholder="Search servers..." 
                           class="pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent w-80 transition-all duration-200">
                </div>

                <a href="{{ route('servers.create') }}" 
                   class="inline-flex items-center px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <i class="fas fa-plus mr-2"></i>
                    Add Server
                </a>
            </div>
        </div>

        <!-- Servers Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900 flex items-center">
                    <i class="fas fa-server text-blue-600 mr-2"></i>
                    Server Infrastructure
                </h3>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Server</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Location</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">CPU</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Memory</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200" id="servers-tbody">
                                @forelse($servers as $server)
                                    <tr id="server-row-{{ $server->id }}">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div>
                                                    <div class="text-sm font-medium text-gray-900">{{ $server->name }}</div>
                                                    <div class="text-sm text-gray-500">{{ $server->ip_address }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" data-col="status">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $server->status === 'online' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ ucfirst($server->status) }}
                                            </span>
                                            @if($server->agent_enabled)
                                                <div class="flex items-center mt-1">
                                                    <span class="inline-flex items-center text-xs">
                                                        <div class="w-2 h-2 rounded-full mr-1 {{ $server->agent_status === 'active' ? 'bg-green-400' : ($server->agent_status === 'disconnected' ? 'bg-yellow-400' : 'bg-gray-400') }}"></div>
                                                        Agent {{ ucfirst($server->agent_status ?? 'inactive') }}
                                                    </span>
                                                </div>
                                            @endif
                                            <div class="server-uptime-info text-xs text-gray-500 mt-1">
                                                @if($server->status === 'online')
                                                    Uptime: {{ $server->running_since ? \Carbon\CarbonInterval::seconds(now()->diffInSeconds($server->running_since))->cascade()->forHumans(['short' => true]) : 'N/A' }}
                                                @else
                                                    Downtime: {{ $server->current_downtime_formatted ?? 'N/A' }}
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $server->location }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ ucfirst($server->environment) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap" data-col="cpu">
                                            <div class="flex items-center">
                                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 rounded-full h-2" style="width: {{ $server->cpu_usage }}%"></div>
                                                </div>
                                                <span class="ml-2 text-sm text-gray-600">{{ number_format($server->cpu_usage, 1) }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" data-col="ram">
                                            <div class="flex items-center">
                                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 rounded-full h-2" style="width: {{ $server->ram_usage }}%"></div>
                                                </div>
                                                <span class="ml-2 text-sm text-gray-600">{{ number_format($server->ram_usage, 1) }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap" data-col="disk">
                                            <div class="flex items-center">
                                                <div class="w-16 bg-gray-200 rounded-full h-2">
                                                    <div class="bg-blue-600 rounded-full h-2" style="width: {{ $server->disk_usage }}%"></div>
                                                </div>
                                                <span class="ml-2 text-sm text-gray-600">{{ number_format($server->disk_usage, 1) }}%</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div x-data="{ open: false }" class="relative inline-block text-left">
                                                <button @click="open = !open" type="button" class="inline-flex items-center text-gray-400 hover:text-gray-500 focus:outline-none">
                                                    <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                                    </svg>
                                                </button>

                                                <div x-show="open" 
                                                     @click.away="open = false"
                                                     class="absolute right-0 mt-2 w-48 rounded-md shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 focus:outline-none z-[100]"
                                                     x-transition:enter="transition ease-out duration-100"
                                                     x-transition:enter-start="transform opacity-0 scale-95"
                                                     x-transition:enter-end="transform opacity-100 scale-100"
                                                     x-transition:leave="transition ease-in duration-75"
                                                     x-transition:leave-start="transform opacity-100 scale-100"
                                                     x-transition:leave-end="transform opacity-0 scale-95">
                                                    <div class="py-1">
                                                        <a href="{{ route('servers.edit', $server->id) }}" 
                                                           class="group flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900">
                                                            <svg class="mr-3 h-5 w-5 text-gray-400 group-hover:text-gray-500" 
                                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                                                      d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                            </svg>
                                                            Edit
                                                        </a>
                                                    </div>
                                                    <div class="py-1">
                                                        <form action="{{ route('servers.destroy', $server->id) }}" method="POST" class="w-full">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit"
                                                                    onclick="return confirm('Are you sure you want to delete this server?')"
                                                                    class="group flex w-full items-center px-4 py-2 text-sm text-red-700 hover:bg-red-100 hover:text-red-900">
                                                                <svg class="mr-3 h-5 w-5 text-red-400 group-hover:text-red-500"
                                                                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                                </svg>
                                                                Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">
                                            No servers found. Click "Add Server" to add your first server.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Auto-refresh server metrics every 30 seconds
    setInterval(function() {
        // Only refresh if we're on the servers page and not editing
        if (window.location.pathname === '/servers' && !document.querySelector('[x-data]').open) {
            fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const newDoc = parser.parseFromString(html, 'text/html');
                const newTable = newDoc.querySelector('tbody');
                const currentTable = document.querySelector('tbody');
                
                if (newTable && currentTable) {
                    // Update table content with smooth transition
                    currentTable.style.opacity = '0.7';
                    setTimeout(() => {
                        currentTable.innerHTML = newTable.innerHTML;
                        currentTable.style.opacity = '1';
                    }, 200);
                    
                    console.log('Server metrics updated');
                }
            })
            .catch(error => {
                console.log('Auto-refresh failed:', error);
            });
        }
    }, 30000); // Refresh every 30 seconds

    // Add visual indicator for auto-refresh
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.querySelector('.flex.items-center.space-x-2');
        if (header) {
            const indicator = document.createElement('div');
            indicator.innerHTML = `
                <div class="flex items-center text-sm text-blue-600">
                    <div class="w-2 h-2 bg-blue-500 rounded-full mr-2 animate-pulse"></div>
                    Auto-refresh active
                </div>
            `;
            header.appendChild(indicator);
        }
    });
    </script>
@endsection