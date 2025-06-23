<?php

namespace App\Livewire;

use App\Models\Server;
use App\Models\Log;
use App\Models\AlertThreshold;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Collection;

class Dashboard extends Component
{
    use WithPagination;

    public $refreshInterval = 30; // seconds
    public $selectedTimeRange = 'day'; // day, week, month
    public $selectedServerId = null;
    public array $selectedServers = []; // New array to track multiple selected servers
    public $showOfflineServers = false;
    public $showRecentLogs = false;
    public $showAlertThresholds = false;
    public $showCharts = true;
    public $testProperty = 'Initial Value';
    public Collection $selectedServersList;

    protected $listeners = [
        'echo:server-status,ServerStatusUpdated' => '$refresh',
        'echo:log-created,LogCreated' => '$refresh'
    ];

    public function mount()
    {
        \Log::info('Dashboard component mounting...');
        
        
        \Log::info('Selected server ID from session:', ['selectedServerId' => $this->selectedServerId]);


    $this->selectedServers = session('selected_servers', []);
    $this->selectedServerId = session('selected_server_id');
    $this->selectedServersList = Server::whereIn('id', $this->selectedServers)->get();


        
        // Initialize selectedServers array
        if ($this->selectedServerId && $this->selectedServerId !== 'all') {
            $this->selectedServers = [$this->selectedServerId];
        } else {
            $this->selectedServers = session('selected_servers', []);
        }
        \Log::info('Selected servers initialized:', ['selectedServers' => $this->selectedServers]);
        
        // If no server was previously selected, set default server to first available server
        if (!$this->selectedServerId && empty($this->selectedServers)) {
            $firstServer = Server::first();
            if ($firstServer) {
                $this->selectedServerId = $firstServer->id;
                $this->selectedServers = [$firstServer->id];
                \Log::info('Default server selected:', ['serverId' => $firstServer->id, 'serverName' => $firstServer->name]);
            }
        }
        
        // Store the selected server in session
        session(['selected_server_id' => $this->selectedServerId]);
        session(['selected_servers' => $this->selectedServers]);

        // 🔧 Add this to update selected list!
        $this->selectedServersList = Server::whereIn('id', $this->selectedServers)->get();
        
        \Log::info('Dashboard component mounted successfully', [
            'selectedServerId' => $this->selectedServerId,
            'selectedServers' => $this->selectedServers,
            'totalServers' => Server::count()
        ]);
    }

    public function updatedSelectedTimeRange()
    {
        \Log::info('Time range updated to: ' . $this->selectedTimeRange);
        
        // Force a complete re-render of the component
        $this->dispatch('timeRangeChanged', timeRange: $this->selectedTimeRange);
        $this->dispatch('$refresh');
        
        // Force the component to re-render
        $this->render();
    }

    public function updatedSelectedServerId()
    {
        $this->resetPage();
        // Store the selected server in session
        session(['selected_server_id' => $this->selectedServerId]);
        $this->dispatch('serverChanged', serverId: $this->selectedServerId);
    }

    public function selectServer($serverId)
    {
        $this->selectedServerId = $serverId;
        // Store the selected server in session
        session(['selected_server_id' => $this->selectedServerId]);
        $this->dispatch('serverChanged', serverId: $this->selectedServerId);
    }

   public function toggleServerSelection($serverId)
{
    $serverId = (int) $serverId;

    if (!is_array($this->selectedServers)) {
        $this->selectedServers = [];
    }

    $this->selectedServers = array_map('intval', $this->selectedServers);

    if (in_array($serverId, $this->selectedServers)) {
        $this->selectedServers = array_values(array_diff($this->selectedServers, [$serverId]));
    } else {
        $this->selectedServers[] = $serverId;
    }

    if (count($this->selectedServers) === 1) {
        $this->selectedServerId = $this->selectedServers[0];
    } elseif (count($this->selectedServers) > 1) {
        $this->selectedServerId = 'multiple';
    } else {
        $this->selectedServerId = null;
    }

    // ✅ Now update the list after the selection is final
    $this->selectedServersList = Server::whereIn('id', $this->selectedServers)->get();

    session(['selected_servers' => $this->selectedServers]);
    session(['selected_server_id' => $this->selectedServerId]);

    $this->dispatch('serverSelectionChanged', selectedServers: $this->selectedServers);
    $this->dispatch('chartDataUpdated');
}


    public function selectAllServers()
    {
        $this->selectedServers = $this->servers->pluck('id')->toArray();
        $this->selectedServerId = 'all';
        session(['selected_servers' => $this->selectedServers]);
        session(['selected_server_id' => $this->selectedServerId]);
        $this->dispatch('serverChanged', $this->selectedServerId);
        $this->dispatch('serverSelectionChanged', $this->selectedServers);
    }

    public function clearServerSelection()
    {
        $this->selectedServers = [];
        $this->selectedServerId = null;
        session(['selected_servers' => $this->selectedServers]);
        session(['selected_server_id' => $this->selectedServerId]);
        $this->dispatch('serverChanged', $this->selectedServerId);
        $this->dispatch('serverSelectionChanged', $this->selectedServers);
    }

    public function toggleOfflineServers()
    {
        $this->showOfflineServers = !$this->showOfflineServers;
    }

    public function toggleRecentLogs()
    {
        $this->showRecentLogs = !$this->showRecentLogs;
    }

    public function toggleAlertThresholds()
    {
        $this->showAlertThresholds = !$this->showAlertThresholds;
    }

    public function toggleCharts()
    {
        $this->showCharts = !$this->showCharts;
    }

    public function getTotalServersProperty()
    {
        return Server::count();
    }

    public function getActiveServersProperty()
    {
        return Server::where('status', 'online')->count();
    }

    public function getOfflineServersProperty()
    {
        return Server::where('status', 'offline')->count();
    }

    public function getTotalLogsProperty()
    {
        return Log::count();
    }

    public function getHealthScoreProperty()
    {
        $activeServers = $this->activeServers;
        $totalServers = $this->totalServers;
        
        if ($totalServers === 0) {
            return 100;
        }
        
        return round(($activeServers / $totalServers) * 100);
    }

    public function getRecentLogsProperty()
    {
        return Log::with('server')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function getServerStatusesProperty()
    {
        return Server::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();
    }

    public function getLogLevelsProperty()
    {
        $timeRange = $this->getTimeRange();
        
        return Log::select('log_level', DB::raw('count(*) as count'))
            ->where('created_at', '>=', $timeRange)
            ->groupBy('log_level')
            ->get();
    }

    public function getTopServersByLogsProperty()
    {
        return Server::withCount('logs')
            ->orderBy('logs_count', 'desc')
            ->limit(5)
            ->get();
    }

    public function getAlertThresholdsProperty()
    {
        return AlertThreshold::with('server')->get();
    }

    public function getOfflineServersListProperty()
    {
        if (!$this->showOfflineServers) {
            return collect();
        }
        
        return Server::where('status', 'offline')
            ->orderBy('last_checked_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function getCriticalLogsProperty()
    {
        return Log::with('server')
            ->where('log_level', 'error')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();
    }

    public function getServersProperty()
    {
        return Server::orderBy('name')->get();
    }

    public function getSelectedServerProperty()
    {
        if (!$this->selectedServerId || $this->selectedServerId === 'all' || $this->selectedServerId === 'multiple') {
            return null;
        }
        return Server::find($this->selectedServerId);
    }

    public function getSelectedServersListProperty()
    {
        \Log::info('getSelectedServersListProperty called', [
            'selectedServers' => $this->selectedServers,
            'isEmpty' => empty($this->selectedServers),
            'timestamp' => now()->toISOString()
        ]);
        
        if (empty($this->selectedServers)) {
            \Log::info('No servers selected, returning empty collection');
            return collect();
        }
        
        $servers = Server::whereIn('id', $this->selectedServers)->get();
        \Log::info('Selected servers retrieved', [
            'count' => $servers->count(),
            'serverIds' => $servers->pluck('id')->toArray(),
            'serverNames' => $servers->pluck('name')->toArray()
        ]);
        
        return $servers;
    }

    // Server Performance Chart Data
    public function getServerPerformanceChartDataProperty()
    {
        if (empty($this->selectedServers)) {
            \Log::info('No servers selected, returning empty chart data');
            return [
                'labels' => [],
                'datasets' => []
            ];
        }

        // If multiple servers are selected, show aggregated data
        if (count($this->selectedServers) > 1) {
            \Log::info('Multiple servers selected, showing aggregated data', [
                'selectedServers' => $this->selectedServers,
                'count' => count($this->selectedServers)
            ]);
            return $this->getAggregatedChartData();
        }

        // Single server selected
        $serverId = $this->selectedServers[0];
        $timeRange = $this->getTimeRange();
        $intervals = $this->getIntervals();
        
        \Log::info('Generating chart data for single server', [
            'serverId' => $serverId,
            'selectedTimeRange' => $this->selectedTimeRange,
            'intervals' => $intervals,
            'timestamp' => now()->toISOString()
        ]);
        
        // Get performance data for the selected server
        $performanceData = $this->getPerformanceData($serverId, $this->selectedTimeRange, $intervals);
        
        \Log::info('Performance data generated', [
            'serverId' => $serverId,
            'dataCount' => count($performanceData),
            'firstData' => $performanceData[0] ?? 'no data',
            'labels' => array_column($performanceData, 'label'),
            'timeRange' => $this->selectedTimeRange
        ]);
        
        $labels = [];
        $cpuData = [];
        $memoryData = [];

        foreach ($performanceData as $data) {
            $labels[] = $data['label'];
            $cpuData[] = $data['cpu'] ?? 0;
            $memoryData[] = $data['memory'] ?? 0;
        }

        $chartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'CPU Usage (%)',
                    'data' => $cpuData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => '#EF4444',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'borderSkipped' => false
                ],
                [
                    'label' => 'Memory Usage (%)',
                    'data' => $memoryData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => '#3B82F6',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'borderSkipped' => false
                ]
            ]
        ];

        \Log::info('Chart data prepared', [
            'labelsCount' => count($labels),
            'cpuDataCount' => count($cpuData),
            'memoryDataCount' => count($memoryData),
            'finalLabels' => $labels,
            'timeRange' => $this->selectedTimeRange
        ]);

        return $chartData;
    }
    
    private function getAggregatedChartData()
    {
        $timeRange = $this->getTimeRange();
        $intervals = $this->getIntervals();
        
        \Log::info('Generating aggregated chart data for selected servers', [
            'selectedTimeRange' => $this->selectedTimeRange,
            'intervals' => $intervals,
            'selectedServers' => $this->selectedServers
        ]);
        
        $labels = [];
        $avgCpuData = [];
        $avgMemoryData = [];
        
        // Generate labels based on time range
        for ($i = $intervals - 1; $i >= 0; $i--) {
            $timestamp = $this->getTimestampForInterval($i, $this->selectedTimeRange);
            $labels[] = $this->getLabelForTimeRange($timestamp, $this->selectedTimeRange);
        }
        
        // Get selected servers
        $selectedServerList = $this->selectedServersList;
        
        // Generate aggregated data for each time interval
        foreach ($labels as $index => $label) {
            $totalCpu = 0;
            $totalMemory = 0;
            $serverCount = 0;
            
            foreach ($selectedServerList as $server) {
                $totalCpu += $server->cpu_usage ?? 50;
                $totalMemory += $server->ram_usage ?? 60;
                $serverCount++;
            }
            
            $avgCpuData[] = $serverCount > 0 ? round($totalCpu / $serverCount, 1) : 0;
            $avgMemoryData[] = $serverCount > 0 ? round($totalMemory / $serverCount, 1) : 0;
        }
        
        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Average CPU Usage (%)',
                    'data' => $avgCpuData,
                    'backgroundColor' => 'rgba(239, 68, 68, 0.8)',
                    'borderColor' => '#EF4444',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'borderSkipped' => false
                ],
                [
                    'label' => 'Average Memory Usage (%)',
                    'data' => $avgMemoryData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => '#3B82F6',
                    'borderWidth' => 1,
                    'borderRadius' => 4,
                    'borderSkipped' => false
                ]
            ]
        ];
    }

    private function getPerformanceData($serverId, $timeRange, $intervals)
    {
        $data = [];
        $server = Server::find($serverId);
        
        if (!$server) {
            return $data;
        }
        
        // Generate realistic performance data based on the server's current metrics
        for ($i = $intervals - 1; $i >= 0; $i--) {
            $timestamp = $this->getTimestampForInterval($i, $timeRange);
            
            // Base the data on the server's current metrics with some variation
            $baseCpu = $server->cpu_usage ?? 50;
            $baseMemory = $server->ram_usage ?? 60;
            
            // Add realistic variation based on time range
            $variation = match($timeRange) {
                'day' => rand(-15, 15) / 100, // Less variation for hourly data
                'week' => rand(-25, 25) / 100, // Medium variation for daily data
                'month' => rand(-35, 35) / 100, // More variation for monthly data
                default => rand(-20, 20) / 100
            };
            
            $cpu = max(0, min(100, $baseCpu + ($baseCpu * $variation)));
            $memory = max(0, min(100, $baseMemory + ($baseMemory * $variation)));
            
            // Add realistic patterns based on time range
            if ($timeRange === 'day') {
                // Hourly patterns (higher usage during business hours)
                $hour = $timestamp->hour;
                if ($hour >= 9 && $hour <= 17) {
                    $cpu = min(100, $cpu * 1.2);
                    $memory = min(100, $memory * 1.1);
                } else if ($hour >= 22 || $hour <= 6) {
                    $cpu = max(0, $cpu * 0.7);
                    $memory = max(0, $memory * 0.8);
                }
            } elseif ($timeRange === 'week') {
                // Weekly patterns (higher usage on weekdays)
                $dayOfWeek = $timestamp->dayOfWeek;
                if ($dayOfWeek >= 1 && $dayOfWeek <= 5) { // Monday to Friday
                    $cpu = min(100, $cpu * 1.15);
                    $memory = min(100, $memory * 1.05);
                } else { // Weekend
                    $cpu = max(0, $cpu * 0.8);
                    $memory = max(0, $memory * 0.85);
                }
            } elseif ($timeRange === 'month') {
                // Monthly patterns (slight seasonal variation)
                $month = $timestamp->month;
                if ($month >= 3 && $month <= 5) { // Spring
                    $cpu = min(100, $cpu * 1.05);
                    $memory = min(100, $memory * 1.02);
                } elseif ($month >= 6 && $month <= 8) { // Summer
                    $cpu = min(100, $cpu * 1.1);
                    $memory = min(100, $memory * 1.08);
                } elseif ($month >= 9 && $month <= 11) { // Fall
                    $cpu = min(100, $cpu * 1.03);
                    $memory = min(100, $memory * 1.01);
                } else { // Winter
                    $cpu = max(0, $cpu * 0.95);
                    $memory = max(0, $memory * 0.98);
                }
            }
            
            // Create appropriate labels based on time range
            $label = $this->getLabelForTimeRange($timestamp, $timeRange);
            
            $data[] = [
                'label' => $label,
                'cpu' => round($cpu, 1),
                'memory' => round($memory, 1)
            ];
        }
        
        return $data;
    }
    
    private function getTimestampForInterval($i, $timeRange)
    {
        switch ($timeRange) {
            case 'day':
                return now()->subHours($i);
            case 'week':
                return now()->subDays($i);
            case 'month':
                return now()->subMonths($i);
            default:
                return now()->subHours($i);
        }
    }

    private function getLabelForTimeRange($timestamp, $timeRange)
    {
        switch ($timeRange) {
            case 'day':
                return $timestamp->format('H:i'); // Hour:Minute (14:30, 15:30, etc.)
            case 'week':
                return $timestamp->format('l'); // Full day name (Monday, Tuesday, etc.)
            case 'month':
                return $timestamp->format('M'); // Month name (Jan, Feb, etc.)
            default:
                return $timestamp->format('H:i');
        }
    }

    private function getTimeRange()
    {
        switch ($this->selectedTimeRange) {
            case 'week':
                return now()->subWeek();
            case 'month':
                return now()->subMonth();
            default:
                return now()->subDay();
        }
    }

    private function getIntervals()
    {
        switch ($this->selectedTimeRange) {
            case 'week':
                return 7; // 7 days of the week
            case 'month':
                return 12; // 12 months of the year
            default:
                return 24; // 24 hours of the day
        }
    }

    public function refreshData()
    {
        $this->dispatch('$refresh');
    }

    public function refreshChartData()
    {
        \Log::info('Manual chart refresh triggered');
        $this->dispatch('$refresh');
    }
    
    public function regenerateChartData()
    {
        \Log::info('Regenerating chart data for time range: ' . $this->selectedTimeRange);
        
        // Force a complete re-render of the component
        $this->dispatch('$refresh');
        
        // Emit chart data regenerated event
        $this->dispatch('chartDataRegenerated', $this->selectedTimeRange);
        
        \Log::info('Chart data regeneration completed');
    }

    public function testMethod()
    {
        \Log::info('=== testMethod CALLED ===', [
            'timestamp' => now()->toISOString(),
            'memory_usage' => memory_get_usage(true)
        ]);
        
        \Log::info('testMethod called successfully!');
        $this->dispatch('testEvent', 'Test method works!');
    }

    public function simpleTest()
    {
        \Log::info('simpleTest called - this should work');
        $this->dispatch('simpleTestEvent', 'Simple test works!');
    }

    public function verySimpleTest()
    {
        \Log::info('verySimpleTest called - just logging');
        // No emit, just logging
    }

    public function changeTestProperty()
    {
        \Log::info('changeTestProperty called');
        $this->testProperty = 'Changed at ' . now()->format('H:i:s');
        \Log::info('Test property changed to: ' . $this->testProperty);
    }

    public function testSelectedServersCount()
    {
        \Log::info('testSelectedServersCount called', [
            'selectedServers' => $this->selectedServers,
            'selectedServersCount' => count($this->selectedServers),
            'selectedServersTypes' => array_map('gettype', $this->selectedServers),
            'selectedServerId' => $this->selectedServerId,
            'totalServers' => Server::count()
        ]);
        
        // Show an alert with the current selection
        $this->dispatch('showAlert', message: 'Selected servers: ' . json_encode($this->selectedServers));
    }

    public function refreshComponent()
    {
        \Log::info('refreshComponent called');
        $this->dispatch('$refresh');
    }

    public function refreshSelectedServers()
    {
        \Log::info('refreshSelectedServers called', [
            'selectedServers' => $this->selectedServers,
            'selectedServersListCount' => $this->selectedServersList->count()
        ]);
        
        // Force a re-render of the component
        $this->dispatch('$refresh');
    }

    public function forceUpdate()
    {
        \Log::info('forceUpdate called - method executed');
        // Force a complete re-render
        $this->dispatch('$refresh');
        // Also emit chart update
        $this->dispatch('chartDataUpdated');
        \Log::info('forceUpdate completed');
    }

    public function updateServerCards()
    {
        \Log::info('updateServerCards called - method executed', [
            'selectedServers' => $this->selectedServers,
            'selectedServersListCount' => $this->selectedServersList->count()
        ]);
        
        // Force the component to re-render
        $this->render();
        
        // Emit an event to notify the frontend
        $this->dispatch('serverCardsUpdated', $this->selectedServers);
        \Log::info('updateServerCards completed');
    }

    public function updatedSelectedServers()
{
    \Log::info('selectedServers updated', ['data' => $this->selectedServers]);

    // Fix: Always refetch the list based on latest selectedServers
    $this->selectedServersList = Server::whereIn('id', $this->selectedServers)->get();

    \Log::info('Refreshed selectedServersList', [
        'count' => $this->selectedServersList->count(),
        'ids' => $this->selectedServersList->pluck('id')->toArray()
    ]);
}


    public function testAddServer($serverId = 1)
    {
        \Log::info('testAddServer called', ['serverId' => $serverId]);
        
        if (!is_array($this->selectedServers)) {
            $this->selectedServers = [];
        }
        
        if (!in_array($serverId, $this->selectedServers)) {
            $this->selectedServers[] = (int) $serverId;
            \Log::info('Server added via test method', ['serverId' => $serverId, 'newSelection' => $this->selectedServers]);
        }
        
        // Force a re-render
        $this->dispatch('$refresh');
        
        // Show an alert
        $this->dispatch('showAlert', message: 'Test: Server ' . $serverId . ' added. Selected: ' . json_encode($this->selectedServers));
    }

    public function render()
    {
        
        $this->selectedServersList = Server::whereIn('id', $this->selectedServers)->get();

        return view('livewire.dashboard', [
            'totalServers' => $this->totalServers,
            'activeServers' => $this->activeServers,
            'offlineServers' => $this->offlineServers,
            'totalLogs' => $this->totalLogs,
            'healthScore' => $this->healthScore,
            'recentLogs' => $this->recentLogs,
            'serverStatuses' => $this->serverStatuses,
            'logLevels' => $this->logLevels,
            'topServersByLogs' => $this->topServersByLogs,
            'alertThresholds' => $this->alertThresholds,
            'offlineServersList' => $this->offlineServersList,
            'criticalLogs' => $this->criticalLogs,
            'servers' => Server::all(), // full list
            'selectedServers' => $this->selectedServers,
            'selectedServersList' => $this->selectedServersList,
            'serverPerformanceChartData' => $this->serverPerformanceChartData,
        ]);
    }
} 