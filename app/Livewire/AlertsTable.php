<?php

namespace App\Livewire;

use App\Models\Alert;
use Livewire\Component;
use Illuminate\View\View;

class AlertsTable extends Component
{
    public $alerts; 
    public $showResolved = false;

    public function mount()
    {
        $this->loadAlerts();
    }

    public function loadAlerts()
    {
        $this->alerts = Alert::where('status', $this->showResolved ? 'resolved' : 'triggered')
            ->orderBy('alert_time', 'desc')
            ->get();
    }

    public function resolveAlert($id)
    {
        $alert = Alert::findOrFail($id);
        $alert->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);

        $this->loadAlerts(); // Refresh list
        session()->flash('message', 'Alert resolved successfully.');
    }

    public function toggleResolved($value)
    {
        $this->showResolved = $value;
        $this->loadAlerts();
    }

    public function render(): View
    {
        return view('livewire.alerts-table')
            ->layout('layouts.app'); // or whatever layout you're using
    }
}
