@extends('layouts.app')

@section('content')
<div class="py-6" x-data="logsPage()" x-init="init()">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">System Logs</h1>
        <p class="text-sm text-gray-500 mt-1">Monitor and analyze system events and activities</p>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Total Logs -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Total Logs</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $logs->total() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-list text-blue-600"></i>
                </div>
            </div>
        </div>

        <!-- Critical Alerts -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Critical</p>
                    <p class="text-2xl font-bold text-red-600">{{ $logs->where('level', 'error')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-triangle text-red-600"></i>
                </div>
            </div>
        </div>

        <!-- Warnings -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Warning</p>
                    <p class="text-2xl font-bold text-yellow-600">{{ $logs->where('level', 'warning')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-exclamation-circle text-yellow-600"></i>
                </div>
            </div>
        </div>

        <!-- Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500">Info</p>
                    <p class="text-2xl font-bold text-blue-600">{{ $logs->where('level', 'info')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <i class="fas fa-info-circle text-blue-600"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <input type="text" 
                       x-model="searchQuery" 
                       @input="filterLogs"
                       placeholder="Search logs by message or server..." 
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
            </div>
            <div>
                <select x-model="selectedLevel" @change="filterLogs" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Levels</option>
                    <option value="error">Error</option>
                    <option value="warning">Warning</option>
                    <option value="info">Info</option>
                </select>
            </div>
            <div>
                <select x-model="selectedServer" @change="filterLogs" class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">All Servers</option>
                    @foreach($servers as $server)
                        <option value="{{ $server->id }}">{{ $server->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-medium text-gray-900">Recent Logs</h3>
                <div class="flex items-center space-x-2 text-sm text-gray-500">
                    <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                    <span>Auto-refresh enabled</span>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Server</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition-colors duration-200" data-log-item>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->created_at->format('M d, Y H:i:s') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->level === 'error')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                    Error
                                </span>
                            @elseif($log->level === 'warning')
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                    Warning
                                </span>
                            @else
                                <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Info
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->server ? $log->server->name : 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ Str::limit($log->message, 80) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <a href="{{ route('logs.show', $log) }}" class="text-blue-600 hover:text-blue-900">
                                View Details
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <i class="fas fa-inbox text-4xl text-gray-300 mb-4"></i>
                                <p class="text-lg font-medium">No logs found</p>
                                <p class="text-sm">Run the monitoring command to generate logs</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($logs->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
                <div class="text-sm text-gray-500">
                    Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} results
                </div>
                <div class="flex space-x-2">
                    @if($logs->onFirstPage())
                        <span class="px-3 py-2 text-sm text-gray-400 border border-gray-300 rounded-md">Previous</span>
                    @else
                        <a href="{{ $logs->previousPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">Previous</a>
                    @endif
                    
                    <span class="px-3 py-2 text-sm text-blue-600 border border-blue-300 rounded-md bg-blue-50">
                        Page {{ $logs->currentPage() }} of {{ $logs->lastPage() }}
                    </span>
                    
                    @if($logs->hasMorePages())
                        <a href="{{ $logs->nextPageUrl() }}" class="px-3 py-2 text-sm text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">Next</a>
                    @else
                        <span class="px-3 py-2 text-sm text-gray-400 border border-gray-300 rounded-md">Next</span>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
</div>

<script>
function logsPage() {
    return {
        searchQuery: '',
        selectedLevel: '',
        selectedServer: '',
        allLogs: @json($logs->items()),
        
        init() {
            this.startAutoRefresh();
        },
        
        filterLogs() {
            const tableRows = document.querySelectorAll('[data-log-item]');
            tableRows.forEach(row => {
                let showRow = true;
                
                // Search filter
                if (this.searchQuery) {
                    const rowText = row.textContent.toLowerCase();
                    showRow = showRow && rowText.includes(this.searchQuery.toLowerCase());
                }
                
                // Level filter
                if (this.selectedLevel) {
                    const levelBadge = row.querySelector('span');
                    const levelText = levelBadge ? levelBadge.textContent.toLowerCase() : '';
                    showRow = showRow && levelText.includes(this.selectedLevel);
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        },
        
        startAutoRefresh() {
            setInterval(() => {
                if (!document.hidden) {
                    location.reload();
                }
            }, 30000);
        }
    }
}
</script>
@endsection
