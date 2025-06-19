@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Analytics</h1>
                <p class="text-sm text-gray-500 mt-1">Monitor and analyze your server performance metrics</p>
            </div>
            <div class="flex items-center space-x-2">
                <button id="live-updates-button" class="inline-flex items-center px-4 py-2 bg-green-100 text-green-700 rounded-lg hover:bg-green-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-colors duration-200">
                    <i class="fas fa-sync-alt mr-2 animate-spin"></i>
                    Live Updates
                </button>
            </div>
        </div>
    </div>

    <div class="p-6">
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- System Performance -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">CPU Usage</h3>
                    <i class="fas fa-chart-line text-blue-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900">{{ number_format($summary['system_performance'], 1) }}%</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Current</p>
            </div>
            <!-- Network Health -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">Network Activity</h3>
                    <i class="fas fa-network-wired text-green-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900">{{ $summary['current_network_activity'] }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Current Activity Level</p>
                <div class="mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $summary['current_network_activity'] }}%"></div>
                    </div>
                </div>
            </div>
            <!-- Storage Usage -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">Storage Usage</h3>
                    <i class="fas fa-hdd text-yellow-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900">{{ number_format($summary['storage_usage'], 1) }}%</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Current</p>
            </div>
            <!-- Resource Allocation -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">Memory Usage</h3>
                    <i class="fas fa-cogs text-purple-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900">{{ number_format($summary['resource_allocation'], 1) }}%</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Current</p>
            </div>
        </div>

        <!-- Additional Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Disk Usage -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">Disk Usage</h3>
                    <i class="fas fa-hdd text-purple-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900">{{ number_format($summary['storage_usage'], 1) }}%</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Current</p>
            </div>
            <!-- Network Throughput -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">Network Throughput</h3>
                    <i class="fas fa-tachometer-alt text-pink-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900">
                        @if(!empty($chart_data['network_throughput']))
                            {{ number_format(end($chart_data['network_throughput']), 1) }}
                        @else
                            0
                        @endif
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">KB/s</p>
            </div>
            <!-- Response Time -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">Response Time</h3>
                    <i class="fas fa-clock text-green-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900">
                        @if(!empty($chart_data['response_time']))
                            {{ number_format(end($chart_data['response_time']), 1) }}
                        @else
                            0
                        @endif
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">ms</p>
            </div>
            <!-- System Uptime -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">
                        @if($selected_server->status === 'online')
                            System Uptime
                        @else
                            System Downtime
                        @endif
                    </h3>
                    <i class="fas fa-server text-{{ $selected_server->status === 'online' ? 'blue' : 'red' }}-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-{{ $selected_server->status === 'online' ? 'gray' : 'red' }}-900">
                        @if($selected_server->status === 'online')
                            {{ $summary['system_uptime'] }}
                        @else
                            {{ $selected_server->current_downtime_formatted ?? 'N/A' }}
                        @endif
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">
                    @if($selected_server->status === 'online')
                        Current Uptime
                    @else
                        Current Downtime
                    @endif
                </p>
            </div>
        </div>

        <!-- Performance Graphs -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200 flex flex-wrap justify-between items-center gap-4">
                <h3 class="text-lg font-medium text-gray-900">Performance Graphs (Last 24h)</h3>
                
                <div class="flex items-center space-x-4">
                    <!-- Server Selector -->
                    <form action="{{ route('analytics') }}" method="GET" class="flex items-center space-x-2">
                        <label for="server_id" class="text-sm font-medium text-gray-700">Server:</label>
                        <select name="server_id" id="server_id" onchange="this.form.submit()" class="block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                            @foreach($servers as $server)
                                <option value="{{ $server->id }}" {{ $selected_server_id == $server->id ? 'selected' : '' }}>
                                    {{ $server->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>

                    <!-- Metrics Toggles -->
                    <div class="flex items-center space-x-4 text-sm">
                        <label class="flex items-center">
                            <input type="checkbox" id="cpuToggle" class="form-checkbox h-4 w-4 text-blue-600" checked>
                            <span class="ml-2 text-gray-700">CPU Load</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="memoryToggle" class="form-checkbox h-4 w-4 text-purple-600" checked>
                            <span class="ml-2 text-gray-700">Memory Usage</span>
                        </label>
                         <label class="flex items-center">
                            <input type="checkbox" id="networkToggle" class="form-checkbox h-4 w-4 text-green-600" checked>
                            <span class="ml-2 text-gray-700">Network Activity</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="diskToggle" class="form-checkbox h-4 w-4 text-orange-600">
                            <span class="ml-2 text-gray-700">Disk I/O (MB/s)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="diskUsageToggle" class="form-checkbox h-4 w-4 text-purple-600">
                            <span class="ml-2 text-gray-700">Disk Usage (%)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="networkThroughputToggle" class="form-checkbox h-4 w-4 text-pink-600">
                            <span class="ml-2 text-gray-700">Network Throughput (KB/s)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="responseTimeToggle" class="form-checkbox h-4 w-4 text-rose-600">
                            <span class="ml-2 text-gray-700">Response Time (ms)</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="p-6">
                <canvas id="performanceChart" height="100"></canvas>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('performanceChart').getContext('2d');
            const chartData = @json($chart_data);

            const performanceChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'CPU Load',
                            data: chartData.cpu_load,
                            borderColor: 'rgba(59, 130, 246, 1)',
                            backgroundColor: 'rgba(59, 130, 246, 0.2)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4
                        },
                        {
                            label: 'Memory Usage',
                            data: chartData.memory_usage,
                            borderColor: 'rgba(139, 92, 246, 1)',
                            backgroundColor: 'rgba(139, 92, 246, 0.2)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4
                        },
                        {
                            label: 'Network Activity',
                            data: chartData.network_activity,
                            borderColor: 'rgba(16, 185, 129, 1)',
                            backgroundColor: 'rgba(16, 185, 129, 0.2)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4
                        },
                        {
                            label: 'Disk I/O',
                            data: chartData.disk_io,
                            borderColor: 'rgba(249, 115, 22, 1)',
                            backgroundColor: 'rgba(249, 115, 22, 0.2)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4,
                            hidden: true
                        },
                        {
                            label: 'Disk Usage',
                            data: chartData.disk_usage,
                            borderColor: 'rgba(168, 85, 247, 1)',
                            backgroundColor: 'rgba(168, 85, 247, 0.2)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4,
                            hidden: true
                        },
                        {
                            label: 'Network Throughput',
                            data: chartData.network_throughput,
                            borderColor: 'rgba(236, 72, 153, 1)',
                            backgroundColor: 'rgba(236, 72, 153, 0.2)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4,
                            hidden: true
                        },
                        {
                            label: 'Response Time',
                            data: chartData.response_time,
                            borderColor: 'rgba(220, 38, 127, 1)',
                            backgroundColor: 'rgba(220, 38, 127, 0.2)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4,
                            hidden: true
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value, index, values) {
                                    if (this.getLabelForValue(value).includes('%')) {
                                        return value + '%';
                                    }
                                    return value;
                                }
                            }
                        }
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        },
                    },
                    interaction: {
                        mode: 'nearest',
                        axis: 'x',
                        intersect: false
                    }
                }
            });

            function toggleDataset(id, chart) {
                const isVisible = chart.isDatasetVisible(id);
                if (isVisible) {
                    chart.hide(id);
                } else {
                    chart.show(id);
                }
            }

            document.getElementById('cpuToggle').addEventListener('change', () => toggleDataset(0, performanceChart));
            document.getElementById('memoryToggle').addEventListener('change', () => toggleDataset(1, performanceChart));
            document.getElementById('networkToggle').addEventListener('change', () => toggleDataset(2, performanceChart));
            document.getElementById('diskToggle').addEventListener('change', () => toggleDataset(3, performanceChart));
            document.getElementById('diskUsageToggle').addEventListener('change', () => toggleDataset(4, performanceChart));
            document.getElementById('networkThroughputToggle').addEventListener('change', () => toggleDataset(5, performanceChart));
            document.getElementById('responseTimeToggle').addEventListener('change', () => toggleDataset(6, performanceChart));
        });
    </script>
@endsection
