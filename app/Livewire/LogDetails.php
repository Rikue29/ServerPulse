<?php

namespace App\Http\Livewire;

use App\Models\Log;
use Livewire\Component;
use Livewire\Attributes\On;

class LogDetails extends Component
{
    public Log $log;

    public function mount($logId)
    {
        $this->log = Log::with('server')->findOrFail($logId);
    }

    #[On('print-log')]
    public function printLog()
    {
        // This will be handled by a client-side script
        $this->dispatch('do-print');
    }

    public function copyToClipboard()
    {
        $json = json_encode($this->log->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->dispatch('copy-to-clipboard', content: $json);
    }

    public function render()
    {
        return view('livewire.log-details');
    }
}

