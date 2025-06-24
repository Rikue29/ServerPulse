<?php

namespace App\Livewire;

use App\Models\Server;
use App\Models\Log;
use App\Models\AlertThreshold;
use App\Models\PerformanceLog;
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

        // Initialize from session
    $this->selectedServers = session('selected_servers', []);
    $this->selectedServerId = session('selected_server_id');
        
        // Initialize selectedServersList safely
        $this->selectedServersList = new Collection();
        if (!empty($this->selectedServers)) {
    $this->selectedServersList = Server::whereIn('id', $this->selectedServers)->get();
        }
        
        // Initialize selectedServers array
        if ($this->selectedServerId && $this->selectedServerId !== 'all') {
            $this->selectedServers = [$this->selectedServerId];
        }
        \Log::info('Selected servers initialized:', ['selectedServers' => $this->selectedServers]);
        
        // Only try to set default server if we have any servers
        if ((!$this->selectedServerId && empty($this->selectedServers)) && Server::count() > 0) {
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

        // Update selected list safely
        if (!empty($this->selectedServers)) {
        $this->selectedServersList = Server::whereIn('id', $this->selectedServers)->get();
        }
        
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
        
        $endDate = now();
        $startDate = match($this->selectedTimeRange) {
            'day' => $endDate->copy()->subDay(),
            'week' => $endDate->copy()->subWeek(),
            'month' => $endDate->copy()->subMonth(),
            default => $endDate->copy()->subDay(),
        };

        $logs = PerformanceLog::whereIn('server_id', $this->selectedServers)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->orderBy('created_at', 'asc')
            ->get();

        if ($logs->isEmpty()) {
            return [
                'labels' => [],
                'datasets' => []
            ];
        }

        $groupedLogs = $logs->groupBy(function($log) {
            $format = match($this->selectedTimeRange) {
                'day' => 'Y-m-d H:00',
                'week' => 'Y-m-d',
                'month' => 'Y-m-d',
                default => 'Y-m-d H:00',
            };
            return $log->created_at->format($format);
        });
        
        $labels = [];
        $avgCpuData = [];
        $avgMemoryData = [];
        
        foreach ($groupedLogs as $key => $group) {
            $labels[] = $key;
            $avgCpuData[] = round($group->avg('cpu_usage'), 2);
            $avgMemoryData[] = round($group->avg('ram_usage'), 2);
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
        $endDate = now();
        $startDate = match($timeRange) {
            'day' => $endDate->copy()->subDay(),
            'week' => $endDate->copy()->subWeek(),
            'month' => $endDate->copy()->subMonth(),
            default => $endDate->copy()->subDay(),
        };

        $query = PerformanceLog::where('server_id', $serverId)
            ->whereBetween('created_at', [$startDate, $endDate]);

        $logs = $query->orderBy('created_at', 'asc')->get();

        if ($logs->isEmpty()) {
            return [];
                }

        // Group logs by time intervals (e.g., hourly for 'day' view)
        $groupedLogs = $logs->groupBy(function($log) use ($timeRange) {
            $format = match($timeRange) {
                'day' => 'Y-m-d H:00', // Group by hour for the day
                'week' => 'Y-m-d',     // Group by day for the week
                'month' => 'Y-m-d',    // Group by day for the month
                default => 'Y-m-d H:00',
            };
            return $log->created_at->format($format);
        });

        $data = [];
        foreach ($groupedLogs as $key => $group) {
            $data[] = [
                'label' => $key,
                'cpu' => round($group->avg('cpu_usage'), 2),
                'memory' => round($group->avg('ram_usage'), 2),
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