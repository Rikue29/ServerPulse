<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Alert;

class SimpleAlertTest extends Component
{
    public $message = 'Ready to test';
      public function resolveAlert($id)
    {
        $this->message = "Attempting to resolve alert {$id}...";
        
        try {
            $alert = Alert::findOrFail($id);
            $alert->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => 1,
            ]);
            
            $this->message = "Alert {$id} resolved successfully at " . now()->format('H:i:s');
            
            $this->dispatch('show-toast', [
                'type' => 'success', 
                'title' => 'Success', 
                'message' => "Alert {$id} resolved successfully."
            ]);
            
        } catch (\Exception $e) {
            $this->message = "Error resolving alert {$id}: " . $e->getMessage();
            
            $this->dispatch('show-toast', [
                'type' => 'error', 
                'title' => 'Error', 
                'message' => 'Failed to resolve alert: ' . $e->getMessage()
            ]);
        }
    }
    
    public function render()
    {
        $alerts = Alert::where('status', 'triggered')->limit(5)->get();
        return view('livewire.simple-alert-test', compact('alerts'));
    }
}
