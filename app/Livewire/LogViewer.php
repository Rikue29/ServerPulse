<?php

namespace App\Livewire;

use App\Models\Log;
use Livewire\Component;
use Livewire\WithPagination;

class LogViewer extends Component
{
    use WithPagination;

    public $search = '';
    public $type = '';
    public $status = '';
    public $server = '';

    protected $queryString = [
        'search' => ['except' => ''],
        'type' => ['except' => ''],
        'status' => ['except' => ''],
        'server' => ['except' => ''],
    ];

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function getStatsProperty()
    {
        return [
            'total_logs' => Log::count(),
            'critical_logs' => Log::where('status', 'Critical')->count(),
            'warning_logs' => Log::where('status', 'Warning')->count(),
            'info_logs' => Log::where('status', 'Info')->count(),
            'recent_logs' => Log::where('timestamp', '>=', now()->subDay())->count(),
        ];
    }

    public function resetFilters()
    {
        $this->search = '';
        $this->type = '';
        $this->status = '';
        $this->server = '';
        $this->resetPage();
    }

    public function render()
    {
        $logs = Log::query()
            ->when($this->search, fn($query) => 
                $query->where('message', 'like', '%' . $this->search . '%')
            )
            ->when($this->type, fn($query) => 
                $query->where('type', $this->type)
            )
            ->when($this->status, fn($query) => 
                $query->where('status', $this->status)
            )
            ->when($this->server, fn($query) => 
                $query->where('server', $this->server)
            )
            ->orderBy('timestamp', 'desc')
            ->paginate(10);

        return view('livewire.log-viewer', [
            'logs' => $logs,
            'stats' => $this->stats,
        ]);
    }
} 