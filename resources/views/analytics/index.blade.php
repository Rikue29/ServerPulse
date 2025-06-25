@extends('layouts.app')

@section('content')
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">
                    Analytics 
                    @if(isset($selected_server_id) && $servers->where('id', $selected_server_id)->first())
                        <span id="selected-server-name" class="text-blue-600 ml-1">| {{ $servers->where('id', $selected_server_id)->first()->name }}</span>
                    @endif
                </h1>
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" data-metric="cpu-usage">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">CPU Usage</h3>
                    <i class="fas fa-chart-line text-blue-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900 metric-value">{{ number_format($summary['system_performance'], 1) }}%</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Current</p>
            </div>
            <!-- Network Health -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" data-metric="network-activity">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">Network Activity</h3>
                    <i class="fas fa-network-wired text-green-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900 metric-value">{{ $summary['current_network_activity'] }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Current Activity Level</p>
                <div class="mt-2">
                    <div class="w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $summary['current_network_activity'] }}%"></div>
                    </div>
                </div>
            </div>
            <!-- Disk I/O -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" data-metric="disk-io">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">Disk I/O</h3>
                    <i class="fas fa-hdd text-yellow-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900 metric-value">{{ $summary['disk_io'] < 1 ? number_format($summary['disk_io'], 3) : number_format($summary['disk_io'], 1) }}</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">MB/s</p>
            </div>
            <!-- Resource Allocation -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" data-metric="memory-usage">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">Memory Usage</h3>
                    <i class="fas fa-cogs text-purple-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900 metric-value">{{ number_format($summary['resource_allocation'], 1) }}%</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Current</p>
            </div>
        </div>

        <!-- Additional Metrics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
            <!-- Disk Usage -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" data-metric="disk-usage">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">Disk Usage</h3>
                    <i class="fas fa-hdd text-purple-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900 metric-value">{{ number_format($summary['storage_usage'], 1) }}%</span>
                </div>
                <p class="text-xs text-gray-500 mt-1">Current</p>
            </div>
            <!-- Network Throughput -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" data-metric="network-throughput">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">Network Throughput</h3>
                    <i class="fas fa-tachometer-alt text-pink-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900 metric-value">
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" data-metric="response-time">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">Response Time</h3>
                    <i class="fas fa-clock text-green-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900 metric-value">
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
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6" data-metric="system-uptime">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-medium text-gray-500">System Uptime</h3>
                    <i class="fas fa-server text-blue-500"></i>
                </div>
                <div class="mt-4">
                    <span class="text-3xl font-bold text-gray-900 metric-value">
                        @if(!empty($chart_data['system_uptime']))
                            {{ number_format(end($chart_data['system_uptime']), 1) }}
                        @else
                            0
                        @endif
                    </span>
                </div>
                <p class="text-xs text-gray-500 mt-1">hours</p>
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
                            <input type="checkbox" id="diskToggle" class="form-checkbox h-4 w-4 text-orange-600" checked>
                            <span class="ml-2 text-gray-700">Disk I/O (MB/s)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="diskUsageToggle" class="form-checkbox h-4 w-4 text-purple-600" checked>
                            <span class="ml-2 text-gray-700">Disk Usage (%)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="networkThroughputToggle" class="form-checkbox h-4 w-4 text-pink-600" checked>
                            <span class="ml-2 text-gray-700">Network Throughput (KB/s)</span>
                        </label>
                        <label class="flex items-center">
                            <input type="checkbox" id="responseTimeToggle" class="form-checkbox h-4 w-4 text-green-600" checked>
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
            const serverId = document.getElementById('server_id') ? document.getElementById('server_id').value : null;
            const serverSelect = document.getElementById('server_id');
            
            // Function to update server name in header when changed
            if (serverSelect) {
                serverSelect.addEventListener('change', function() {
                    // The form will auto-submit, but we can update the UI immediately for better UX
                    const selectedServerName = this.options[this.selectedIndex].text;
                    let headerServerName = document.getElementById('selected-server-name');
                    const analyticsHeader = document.querySelector('.text-2xl.font-semibold');
                    
                    if (headerServerName) {
                        headerServerName.textContent = '| ' + selectedServerName;
                    } else if (analyticsHeader) {
                        // Create the span if it doesn't exist
                        headerServerName = document.createElement('span');
                        headerServerName.id = 'selected-server-name';
                        headerServerName.className = 'text-blue-600 ml-1';
                        headerServerName.textContent = '| ' + selectedServerName;
                        analyticsHeader.appendChild(headerServerName);
                    }
                });
            }

            // Utility for localStorage graph state (per-server)
            function getActiveGraphKey(serverId) {
                return `serverpulse_active_graphs_${serverId}`;
            }
            function saveActiveGraphs(serverId, activeIds) {
                if (serverId && activeIds) {
                    localStorage.setItem(getActiveGraphKey(serverId), JSON.stringify(activeIds));
                    console.log('✅ Saved active graphs:', activeIds);
                }
            }
            function loadActiveGraphs(serverId) {
                if (serverId) {
                    const val = localStorage.getItem(getActiveGraphKey(serverId));
                    if (val) {
                        try {
                            const parsed = JSON.parse(val);
                            console.log('📊 Loaded active graphs:', parsed);
                            return parsed;
                        } catch (e) {
                            console.error('❌ Error parsing active graphs:', e);
                            return null;
                        }
                    }
                }
                return null;
            }

            // Create chart and assign it to window so it can be accessed by the WebSocket event handler
            window.performanceChart = new Chart(ctx, {
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
                            tension: 0.4,
                            hidden: false
                        },
                        {
                            label: 'Memory Usage',
                            data: chartData.memory_usage,
                            borderColor: 'rgba(139, 92, 246, 1)',
                            backgroundColor: 'rgba(139, 92, 246, 0.2)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4,
                            hidden: false
                        },
                        {
                            label: 'Network Activity',
                            data: chartData.network_activity,
                            borderColor: 'rgba(16, 185, 129, 1)',
                            backgroundColor: 'rgba(16, 185, 129, 0.2)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4,
                            hidden: false
                        },
                        {
                            label: 'Disk I/O',
                            data: chartData.disk_io,
                            borderColor: 'rgba(249, 115, 22, 1)',
                            backgroundColor: 'rgba(249, 115, 22, 0.2)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4,
                            hidden: false
                        },
                        {
                            label: 'Disk Usage',
                            data: chartData.disk_usage,
                            borderColor: 'rgba(168, 85, 247, 1)',
                            backgroundColor: 'rgba(168, 85, 247, 0.2)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4,
                            hidden: false
                        },
                        {
                            label: 'Network Throughput',
                            data: chartData.network_throughput,
                            borderColor: 'rgba(236, 72, 153, 1)',
                            backgroundColor: 'rgba(236, 72, 153, 0.2)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4,
                            hidden: false
                        },
                        {
                            label: 'Response Time',
                            data: chartData.response_time,
                            borderColor: 'rgba(34, 197, 94, 1)',
                            backgroundColor: 'rgba(34, 197, 94, 0.2)',
                            borderWidth: 2,
                            pointRadius: 0,
                            tension: 0.4,
                            hidden: false
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    // Add smooth transition animations
                    animation: {
                        duration: 600,
                        easing: 'easeOutQuad',
                        mode: 'active'
                    },
                    transitions: {
                        show: {
                            animations: {
                                properties: ['opacity'],
                                from: 0,
                                to: 1,
                                duration: 600
                            }
                        },
                        hide: {
                            animations: {
                                properties: ['opacity'],
                                from: 1,
                                to: 0,
                                duration: 400
                            }
                        }
                    },
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
                            },
                            // Add animation for scales too
                            animation: {
                                duration: 500
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

            function initializeGraphToggles() {
            // --- Define toggle mapping between checkbox IDs and dataset indices ---
            const toggleMapping = {
                'cpuToggle': 0,
                'memoryToggle': 1, 
                'networkToggle': 2, 
                'diskToggle': 3,
                'diskUsageToggle': 4, 
                'networkThroughputToggle': 5, 
                'responseTimeToggle': 6
            };

            const toggleIds = Object.keys(toggleMapping);
            const chart = window.performanceChart;

            // Function to synchronize checkbox state with chart visibility
            function syncCheckboxWithChart(toggleId) {
                const checkbox = document.getElementById(toggleId);
                const datasetIndex = toggleMapping[toggleId];
                
                if (!checkbox || datasetIndex === undefined) return;
                
                // Ensure dataset at index exists
                if (!chart.data.datasets[datasetIndex]) {
                    console.error(`❌ Dataset at index ${datasetIndex} does not exist`);
                    return;
                }

                    // Set dataset visibility based on checkbox state
                    chart.data.datasets[datasetIndex].hidden = !checkbox.checked;
                
                // Update chart with smooth transition animation
                chart.update({
                    duration: 400,
                    easing: 'easeOutQuad'
                });
            }
            
            // Initialize all checkboxes and dataset visibility
            toggleIds.forEach(toggleId => {
                const checkbox = document.getElementById(toggleId);
                if (checkbox) {
                        // Always ensure checkbox is checked on page load
                        checkbox.checked = true;

                        // Make sure dataset is visible
                        const datasetIndex = toggleMapping[toggleId];
                        if (chart.data.datasets[datasetIndex]) {
                            chart.data.datasets[datasetIndex].hidden = false;
                        }
                    
                        // Add change event listener
                        checkbox.addEventListener('change', function () {
                        // Update dataset visibility when checkbox changes
                        syncCheckboxWithChart(toggleId);
                        
                        // Save current state to localStorage
                        const currentActiveIds = toggleIds.filter(id => {
                            const cb = document.getElementById(id);
                            return cb && cb.checked;
                        });
                        
                            // Save preferences for future sessions
                        saveActiveGraphs(serverId, currentActiveIds);
                    });
                }
            });

                // Force an update to ensure all datasets are visible
                chart.update();
            }

            // Defer initialization until the window is fully loaded
            window.onload = function() {
                initializeGraphToggles();
            };
        });
    </script>
@endsection
