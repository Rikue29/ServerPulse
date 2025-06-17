<?php

namespace App\Livewire;

use App\Models\Log;
use App\Models\Server;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Response;

class LogsTable extends Component
{
    use WithPagination;

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

    public function exportCsv()
    {
        $query = Log::query()
            ->with('server')
            ->when($this->search, fn($q) => $q->where('message', 'like', "%{$this->search}%"))
            ->when($this->selectedLevel, fn($q) => $q->where('level', $this->selectedLevel))
            ->when($this->selectedServer, fn($q) => $q->where('server_id', $this->selectedServer))
            ->orderBy('created_at', 'desc');

        $logs = $query->get();
        $filename = 'logs_export_' . now()->format('Ymd_His') . '.csv';

        // Output CSV to a string
        $handle = fopen('php://temp', 'w+');
        fputcsv($handle, ['Timestamp', 'Level', 'Server', 'Message']);
        foreach ($logs as $log) {
            fputcsv($handle, [
                $log->created_at,
                $log->level,
                $log->server->name ?? '-',
                $log->message,
            ]);
        }
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return Response::streamDownload(function () use ($csv) {
            echo $csv;
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function refreshLogs()
    {
        // This method will be triggered by the Livewire.emit('refreshLogs') call
        $this->render();
    }
}
