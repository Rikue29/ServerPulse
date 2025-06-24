<div class="bg-gray-100 p-6">
    <!-- Server Filter Section -->
    <div class="bg-white p-4 rounded-lg shadow mb-6">
        <h2 class="text-lg font-bold mb-4">Server Filter</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-4">
            @foreach($servers as $server)
                @php 
                    $isSelected = in_array($server->id, $selectedServers);
                    $bgColor = $isSelected ? 'background-color: #EBF5FF;' : 'background-color: #F9FAFB;';
                    $borderColor = $isSelected ? 'border-color: #3B82F6;' : 'border-color: #E5E7EB;';
                    $textColor = $isSelected ? 'color: #1E40AF;' : 'color: #111827;';
                @endphp
                <a href="{{ route('toggle.server', $server->id) }}"
                   class="p-3 rounded border cursor-pointer block transition-all duration-200 server-filter-item"
                   style="{{ $bgColor }} {{ $borderColor }} {{ $textColor }}"
                   data-server-id="{{ $server->id }}"
                >
                    <div class="flex items-center">
                        <div class="w-3 h-3 rounded-full {{ $server->status === 'online' ? 'bg-green-500' : 'bg-red-500' }} mr-2"></div>
                        <div>
                            <div class="font-medium">{{ $server->name }}</div>
                            <div class="text-xs" style="{{ $isSelected ? 'color: #3B82F6;' : 'color: #6B7280;' }}">{{ $server->ip_address }}</div>
                        </div>
                        @if($isSelected)
                            <div class="ml-auto">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('select.all.servers') }}" class="px-4 py-2 bg-blue-50 text-blue-600 border border-blue-200 rounded text-sm font-medium hover:bg-blue-100 transition-colors">Select All</a>
            <a href="{{ route('clear.server.selection') }}" class="px-4 py-2 bg-gray-50 text-gray-600 border border-gray-200 rounded text-sm font-medium hover:bg-gray-100 transition-colors">Clear All</a>
        </div>
    </div>

    <!-- Dashboard Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- Server Details Section -->
        <div class="lg:col-span-3">
            <div class="bg-white p-4 rounded-lg shadow h-full">
                <h2 class="text-lg font-bold mb-4">Selected Servers</h2>
                
                @if(count($selectedServers) > 0)
                    <div class="space-y-4">
                        @foreach($selectedServersList as $server)
                            <div class="border border-gray-200 rounded p-3 bg-gray-50">
                                <div class="flex justify-between items-center mb-2">
                                    <div class="font-medium">{{ $server->name }}</div>
                                    <div class="text-xs px-2 py-1 rounded-full {{ $server->status === 'online' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($server->status) }}
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500 mb-2">{{ $server->ip_address }}</div>
                                <div class="text-sm">
                                    <div class="flex justify-between mb-1">
                                        <span>CPU:</span>
                                        <span>{{ $server->cpu_usage ?? 0 }}%</span>
                                    </div>
                                    <div class="flex justify-between mb-1">
                                        <span>RAM:</span>
                                        <span>{{ $server->ram_usage ?? 0 }}%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span>Disk:</span>
                                        <span>{{ $server->disk_usage ?? 0 }}%</span>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-8 text-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-gray-500 mb-1">No servers selected</p>
                        <p class="text-sm text-gray-400">Please select at least one server to view metrics</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Charts Section -->
        <div class="lg:col-span-9">
            @if(count($selectedServers) > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- CPU & RAM Usage Chart -->
                    <div class="md:col-span-2 bg-white p-4 rounded-lg shadow">
                        <h3 class="text-lg font-bold mb-2">CPU & RAM Usage</h3>
                        <div class="h-80"><canvas id="performanceLineChart"></canvas></div>
                    </div>

                    <!-- Disk Utilization Chart -->
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="text-lg font-bold mb-2">Disk Utilization</h3>
                        <div class="h-64"><canvas id="diskUtilizationChart"></canvas></div>
                    </div>

                    <!-- Bandwidth Chart -->
                    <div class="bg-white p-4 rounded-lg shadow">
                        <h3 class="text-lg font-bold mb-2">Bandwidth</h3>
                        <div class="h-64"><canvas id="networkChart"></canvas></div>
                    </div>
                </div>
            @else
                <div class="bg-white p-8 rounded-lg shadow flex flex-col items-center justify-center text-center h-full">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-gray-300 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <h3 class="text-xl font-bold text-gray-700 mb-2">No Data to Display</h3>
                    <p class="text-gray-500 mb-6 max-w-md">Select one or more servers from the filter above to view performance metrics and charts</p>
                    <a href="{{ route('select.all.servers') }}" class="px-4 py-2 bg-blue-50 text-blue-600 border border-blue-200 rounded text-sm font-medium hover:bg-blue-100 transition-colors">
                        Select All Servers
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let charts = {};
    
    // Chart configuration
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: { color: '#6B7280' },
                grid: { color: '#E5E7EB' }
            },
            x: {
                ticks: { color: '#6B7280' },
                grid: { color: 'rgba(0,0,0,0)' }
            }
        },
        plugins: {
            legend: { labels: { color: '#374151' } }
        },
        interaction: {
            intersect: false,
            mode: 'index',
        },
    };

    // Function to create or update chart
    function createOrUpdateChart(elementId, data) {
        const ctx = document.getElementById(elementId);
        if (!ctx) return;
        
        if (charts[elementId]) {
            charts[elementId].data = data;
            charts[elementId].update();
        } else {
            charts[elementId] = new Chart(ctx, {
                type: 'line',
                data: data,
                options: chartOptions
            });
        }
    }
    
    // Function to load chart data
    function loadChartData() {
        // Only load charts if we have selected servers
        if (selectedServers && selectedServers.length > 0) {
            fetch('{{ route('chart.data') }}')
                .then(response => response.json())
                .then(data => {
                    createOrUpdateChart('performanceLineChart', data.performance);
                    createOrUpdateChart('diskUtilizationChart', data.disk);
                    createOrUpdateChart('networkChart', data.network);
                })
                .catch(error => console.error('Error loading chart data:', error));
        }
    }
    
    // Server selection highlighting
    const serverLinks = document.querySelectorAll('.server-filter-item');
    const selectedServers = @json($selectedServers);
    
    serverLinks.forEach(link => {
        const serverId = parseInt(link.dataset.serverId);
        
        // Set initial state based on server selection
        if (selectedServers.includes(serverId)) {
            link.classList.add('server-active');
        }
        
        link.addEventListener('click', function(e) {
            // Don't prevent default - let the link work normally
        });
    });
    
    // Load initial chart data
    loadChartData();
    
    // Refresh charts every 30 seconds
    setInterval(loadChartData, 30000);
});
</script>
@endpush 