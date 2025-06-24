<?php

namespace App\Livewire;

use App\Models\Alert;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class AlertsTable extends Component
{
    use WithPagination;

    public $showResolved = false;
    public $filterSeverity = '';
    public $filterType = '';
    public $search = '';
    public $sortBy = 'alert_time';
    public $sortDirection = 'desc';

    protected $paginationTheme = 'tailwind';

    protected $listeners = [
        'alert-resolved' => 'handleAlertResolved',
        'refresh-alerts' => '$refresh',
        'alertResolvedFromDropdown' => 'handleAlertResolved'
    ];

    protected $queryString = [
        'showResolved' => ['except' => false],
        'filterSeverity' => ['except' => ''],
        'filterType' => ['except' => ''],
        'search' => ['except' => ''],
    ];

    public function mount()
    {
        // Initialize component
    }

    public function handleAlertResolved($alertId = null)
    {
        // Clear ALL cached properties to force fresh data
        $this->resetPage();
        unset($this->alerts);
        unset($this->recentAlerts);
        unset($this->alertStats);
        
        // Force complete re-render
        $this->render();
        
        // Dispatch refresh to force update
        $this->dispatch('$refresh');
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingFilterSeverity()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
    {
        $this->resetPage();
    }

    public function updatingShowResolved()
    {
        $this->resetPage();
    }

    public function resolveAlert($id)
    {
        try {
            $alert = Alert::findOrFail($id);
            
            if ($alert->status === 'resolved') {
                $this->dispatch('show-toast', [
                    'type' => 'warning', 
                    'title' => 'Warning', 
                    'message' => 'Alert is already resolved.'
                ]);
                return;
            }

            $alert->update([
                'status' => 'resolved',
                'resolved_at' => now(),
                'resolved_by' => Auth::id(),
            ]);

            // Clear ALL cached properties to force refresh
            unset($this->alerts);
            unset($this->recentAlerts);  
            unset($this->alertStats);
            
            // Force refresh
            $this->dispatch('$refresh');

            $this->dispatch('show-toast', [
                'type' => 'success', 
                'title' => 'Success', 
                'message' => 'Alert resolved successfully.'
            ]);
            
            // Dispatch events for other components
            $this->dispatch('alert-resolved', ['alertId' => $id]);
            
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error', 
                'title' => 'Error', 
                'message' => 'Failed to resolve alert: ' . $e->getMessage()
            ]);
        }
    }

    public function bulkResolve($alertIds)
    {
        try {
            $count = Alert::whereIn('id', $alertIds)
                ->where('status', 'triggered')
                ->update([
                    'status' => 'resolved',
                    'resolved_at' => now(),
                    'resolved_by' => Auth::id(),
                ]);

            // Force refresh of computed properties
            unset($this->alerts);
            unset($this->recentAlerts);
            unset($this->alertStats);
            
            // Reset pagination if needed
            if ($this->getAlertsProperty()->count() === 0 && $this->getPage() > 1) {
                $this->setPage(1);
            }

            $this->dispatch('show-toast', [
                'type' => 'success', 
                'title' => 'Success', 
                'message' => "Resolved {$count} alerts successfully."
            ]);
            
            // Dispatch event to refresh recent alerts
            $this->dispatch('alerts-bulk-resolved', ['count' => $count]);
            
        } catch (\Exception $e) {
            $this->dispatch('show-toast', [
                'type' => 'error', 
                'title' => 'Error', 
                'message' => 'Failed to resolve alerts: ' . $e->getMessage()
            ]);
        }
    }

    public function sortBy($field)
    {
        if ($this->sortBy === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function getAlertsProperty()
    {
        $query = Alert::with(['server', 'threshold', 'resolvedBy']);

        // Filter by status - if showResolved is false, show both resolved and unresolved
        // if showResolved is true, show only resolved
        if ($this->showResolved) {
            $query->resolved();
        }
        // If showResolved is false, we show all alerts (both resolved and unresolved)

        // Search functionality
        if ($this->search) {
            $query->where(function($q) {
                $q->whereHas('server', function($serverQuery) {
                    $serverQuery->where('name', 'like', '%' . $this->search . '%');
                })
                ->orWhere('alert_message', 'like', '%' . $this->search . '%')
                ->orWhere('alert_type', 'like', '%' . $this->search . '%');
            });
        }

        // Filter by severity
        if ($this->filterSeverity) {
            $query->where(function($q) {
                switch($this->filterSeverity) {
                    case 'critical':
                        $q->where('metric_value', '>=', 90);
                        break;
                    case 'high':
                        $q->where('metric_value', '>=', 80)->where('metric_value', '<', 90);
                        break;
                    case 'medium':
                        $q->where('metric_value', '>=', 70)->where('metric_value', '<', 80);
                        break;
                    case 'low':
                        $q->where('metric_value', '<', 70);
                        break;
                }
            });
        }

        // Filter by alert type
        if ($this->filterType) {
            $query->where('alert_type', $this->filterType);
        }

        // Sorting - resolved alerts should appear at the bottom
        $query->orderByRaw("CASE WHEN status = 'resolved' THEN 1 ELSE 0 END")
              ->orderBy($this->sortBy, $this->sortDirection);

        return $query->paginate(15);
    }

    public function getRecentAlertsProperty()
    {
        return Alert::with(['server', 'threshold'])
            ->recent(24)
            ->unresolved()
            ->orderBy('alert_time', 'desc')
            ->limit(5)
            ->get();
    }

    public function getAlertStatsProperty()
    {
        return [
            'total_unresolved' => Alert::unresolved()->count(),
            'critical' => Alert::unresolved()->where('metric_value', '>=', 90)->count(),
            'high' => Alert::unresolved()->where('metric_value', '>=', 80)->where('metric_value', '<', 90)->count(),
            'medium' => Alert::unresolved()->where('metric_value', '>=', 70)->where('metric_value', '<', 80)->count(),
            'low' => Alert::unresolved()->where('metric_value', '<', 70)->count(),
        ];
    }

    public function refresh()
    {
        // Clear all cached properties
        unset($this->alerts);
        unset($this->recentAlerts);
        unset($this->alertStats);
        
        // Re-render the component
        $this->render();
    }

    public function render(): View
    {
        return view('livewire.alerts-table', [
            'alerts' => $this->alerts,
            'recentAlerts' => $this->recentAlerts,
            'stats' => $this->alertStats,
        ]);
    }
}
