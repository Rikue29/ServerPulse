<?php

namespace App\Livewire;

use App\Models\Log;
use App\Models\Server;
use Livewire\Component;
use Livewire\WithPagination;

class LogsTable extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public $autoRefresh = true;
    public $search = '';
    public $selectedLevel = '';
    public $selectedServer = '';
    public $perPage = 10;

    protected $queryString = [
        'search'         => ['except' => ''],
        'selectedLevel'  => ['except' => '', 'as' => 'level'],
        'selectedServer' => ['except' => '', 'as' => 'server'],
        'perPage'        => ['except' => 10],
    ];

    public function mount()
    {
        $this->search = request()->query('search', '');
        $this->selectedLevel = request()->query('level', '');
        $this->selectedServer = request()->query('server', '');
    }

    public function render()
    {
        $query = Log::query()
            ->with('server')
            ->when($this->search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('message', 'like', "%{$search}%")
                        ->orWhere('level', 'like', "%{$search}%")
                        ->orWhereHas('server', function($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($this->selectedLevel, function ($query, $level) {
                if ($level === 'error') {
                    $query->whereIn('level', ['error', 'critical']);
                } elseif ($level === 'warning') {
                    $query->whereIn('level', ['warning', 'warn']);
                } elseif ($level === 'info') {
                    $query->whereIn('level', ['info', 'information', 'notice']);
                } else {
                    $query->where('level', $level);
                }
            })
            ->when($this->selectedServer, function ($query, $serverId) {
                $query->where('server_id', $serverId);
            })
            ->orderBy('id', 'desc');

        $logs = $query->paginate($this->perPage);
        $servers = Server::all(['id', 'name']); // Only select needed columns

        // Cache stats for better performance
        $stats = cache()->remember('log_stats', 60, function () {
            return [
                'total'    => Log::count(),
                'errors'   => Log::whereIn('level', ['error', 'critical'])->count(),
                'warnings' => Log::whereIn('level', ['warning', 'warn'])->count(),
            ];
        });

        return view('livewire.logs-table', [
            'logs'        => $logs,
            'servers'     => $servers,
            'stats'       => $stats,
            'autoRefresh' => $this->autoRefresh,
        ]);
    }

    public function updatingSearch() { $this->resetPage(); }
    public function updatingSelectedLevel() { $this->resetPage(); }
    public function updatingSelectedServer() { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedLevel = '';
        $this->selectedServer = '';
        $this->resetPage();
    }

    public function toggleAutoRefresh()
    {
        $this->autoRefresh = ! $this->autoRefresh;
    }

    public function refreshLogs()
    {
        // This method will be triggered by the Livewire.emit('refreshLogs') call
        $this->render();
    }
}
