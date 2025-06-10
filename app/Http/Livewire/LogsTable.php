<?php

namespace App\Http\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Log;
use App\Models\Server;

class LogsTable extends Component
{
    use WithPagination;

    protected $listeners = ['refreshLogs' => '$refresh'];

    public $search = '';
    public $selectedLevel = '';
    public $selectedServer = '';
    public $perPage = 20;
    public $autoRefresh = true;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedLevel' => ['except' => '', 'as' => 'level'],
        'selectedServer' => ['except' => '', 'as' => 'server'],
        'perPage' => ['except' => 20],
    ];

    public function render()
    {
        $query = Log::with('server')
            ->when($this->search, fn ($q) => $q->where('message', 'like', "%{$this->search}%"))
            ->when($this->selectedLevel, fn ($q) => $q->where('level', $this->selectedLevel))
            ->when($this->selectedServer, fn ($q) => $q->where('server_id', $this->selectedServer))
            ->orderBy('created_at', 'desc');

        return view('livewire.logs-table', [
            'logs' => $query->paginate($this->perPage),
            'servers' => Server::all(),
            'stats' => [
                'total' => Log::count(),
                'errors' => Log::where('level', 'error')->count(),
                'warnings' => Log::where('level', 'warning')->count(),
            ],
            'autoRefresh' => $this->autoRefresh, // <--- pass this for the blade to use
        ]);
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->selectedLevel = '';
        $this->selectedServer = '';
        $this->resetPage();
    }

    public function exportCsv()
    {
        $query = Log::with('server')
            ->when($this->search, fn ($q) => $q->where('message', 'like', "%{$this->search}%"))
            ->when($this->selectedLevel, fn ($q) => $q->where('level', $this->selectedLevel))
            ->when($this->selectedServer, fn ($q) => $q->where('server_id', $this->selectedServer))
            ->orderBy('created_at', 'desc');

        $logs = $query->get();

        $filename = 'logs_export_' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($logs) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Timestamp', 'Level', 'Server', 'Message']);
            foreach ($logs as $log) {
                fputcsv($handle, [
                    $log->created_at,
                    $log->level,
                    $log->server->name ?? '-',
                    $log->message,
                ]);
            }
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
