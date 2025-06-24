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
use Livewire\Attributes\Computed;
use Illuminate\Support\Collection as SupportCollection;

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

    protected $listeners = [
        'echo:server-status,ServerStatusUpdated' => '$refresh',
        'echo:log-created,LogCreated' => '$refresh',
        'refreshChartData' => 'refreshChartData'
    ];

    protected $queryString = ['selectedServers'];

    public function mount()
    {
        // Get selected servers from session
        $this->selectedServers = session('selected_servers', []);
        
        // Force convert to integers
        $this->selectedServers = array_map('intval', $this->selectedServers);
        
        // No longer auto-select all servers if empty
        // This allows for explicitly having no servers selected
    }

    #[Computed]
    public function selectedServersList()
    {
        return empty($this->selectedServers)
            ? new Collection()
            : Server::whereIn('id', $this->selectedServers)->get();
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
        
        // If server is already selected, remove it
        if (($key = array_search($serverId, $this->selectedServers)) !== false) {
            unset($this->selectedServers[$key]);
            $this->selectedServers = array_values($this->selectedServers);
        } 
        // Otherwise add it
        else {
            $this->selectedServers[] = $serverId;
        }
    }

    public function selectAllServers()
    {
        $this->selectedServers = Server::pluck('id')->toArray();
    }

    public function clearServerSelection()
    {
        $this->selectedServers = [];
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
        return Server::query()->select('id', 'name', 'ip_address', 'status')->get();
    }

    public function getSelectedServerProperty()
    {
        if ($this->selectedServerId && $this->selectedServerId !== 'all' && $this->selectedServerId !== 'multiple') {
            return Server::find($this->selectedServerId);
        }
        return null;
    }

    public function getAllChartData(): array
    {
        $labels = $this->getChartLabels();

        $cpuData = $this->getAggregatedPerformanceData('cpu_usage', $labels);
        $ramData = $this->getAggregatedPerformanceData('ram_usage', $labels);
        $diskData = $this->getAggregatedPerformanceData('disk_usage', $labels);
        $networkData = $this->getAggregatedPerformanceData('network_tx', $labels);

        return [
            'performance' => [
                'labels' => $labels,
                'datasets' => [
                    [
                        'label' => 'Avg CPU Usage %',
                        'data' => $cpuData,
                        'borderColor' => '#3B82F6',
                        'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                        'tension' => 0.1,
                        'fill' => true
                    ],
                    [
                        'label' => 'Avg RAM Usage %',
                        'data' => $ramData,
                        'borderColor' => '#10B981',
                        'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                        'tension' => 0.1,
                        'fill' => true
                    ]
                ]
            ],
            'disk' => ['labels' => $labels, 'datasets' => [['label' => 'Avg Disk Usage %', 'data' => $diskData, 'borderColor' => '#F59E0B', 'backgroundColor' => 'rgba(245, 158, 11, 0.1)', 'tension' => 0.1, 'fill' => true]]],
            'network' => ['labels' => $labels, 'datasets' => [['label' => 'Avg Bandwidth (TX)', 'data' => $networkData, 'borderColor' => '#6366F1', 'backgroundColor' => 'rgba(99, 102, 241, 0.1)', 'tension' => 0.1, 'fill' => true]]],
        ];
    }
    
    private function getAggregatedPerformanceData(string $metric, array $labels): array
    {
        if (empty($this->selectedServers) || !in_array($metric, ['cpu_usage', 'ram_usage', 'disk_usage', 'network_tx', 'network_rx'])) {
            return array_fill(0, count($labels), 0);
        }

        $data = PerformanceLog::whereIn('server_id', $this->selectedServers)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m-%d %H:00') as hour"),
                DB::raw("AVG(`{$metric}`) as avg_value")
            )
            ->groupBy('hour')
            ->orderBy('hour', 'asc')
            ->pluck('avg_value', 'hour')
            ->toArray();

        return array_map(fn($label) => $data[$label] ?? 0, $labels);
    }

    private function getChartLabels(): array
    {
        $labels = [];
        for ($i = 23; $i >= 0; $i--) {
            $labels[] = now()->subHours($i)->format('Y-m-d H:00');
        }
        return $labels;
    }

    public function render()
    {
        // Get all servers for the filter
        $servers = Server::all();
        
        // Get the most up-to-date selection from session
        $this->selectedServers = session('selected_servers', []);
        
        // Force convert to integers
        $this->selectedServers = array_map('intval', $this->selectedServers);
        
        // Get selected servers data
        $selectedServersList = Server::whereIn('id', $this->selectedServers)->get();
        
        return view('livewire.dashboard', [
            'servers' => $servers,
            'selectedServersList' => $selectedServersList,
        ])->layout('layouts.app');
    }

    public function getSelectedServersListProperty()
    {
        // Get the most up-to-date selection from session
        $this->selectedServers = session('selected_servers', []);
        
        // Force convert to integers
        $this->selectedServers = array_map('intval', $this->selectedServers);
        
        return \App\Models\Server::whereIn('id', $this->selectedServers)->get();
    }
} 