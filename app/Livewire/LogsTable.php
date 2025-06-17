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
                        ->orWhere('level', 'like', "%{$search}%");
                });
            })
            ->when($this->selectedLevel, function ($query, $level) {
                $query->where('level', $level);
            })
            ->when($this->selectedServer, function ($query, $serverId) {
                $query->where('server_id', $serverId);
            })
            ->orderBy('created_at', 'desc');

        $logs = $query->paginate($this->perPage);
        $servers = Server::all();

        $stats = [
            'total'    => Log::count(),
            'errors'   => Log::where('level', 'error')->count(),
            'warnings' => Log::where('level', 'warning')->count(),
        ];

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
