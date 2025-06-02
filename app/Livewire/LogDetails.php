<?php

namespace App\Livewire;

use App\Models\Log;
use Livewire\Component;
use Livewire\Attributes\On;

#[On('print-log')]
class LogDetails extends Component
{
    public $log;

    public function mount(Log $log)
    {
        $this->log = $log->load('server');
    }

    public function copyToClipboard()
    {
        $json = json_encode($this->log->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        $this->dispatchBrowserEvent('copy-to-clipboard', ['content' => $json]);
    }

    public function getMetricsData()
    {
        // Handle both string and array context formats
        $context = $this->log->context;
        if (is_string($context)) {
            $context = json_decode($context, true) ?? [];
        } elseif (!is_array($context)) {
            $context = [];
        }
        
        return [
            'cpu_usage' => $context['cpu_usage'] ?? null,
            'memory_usage' => $context['memory_usage'] ?? null,
            'disk_usage' => $context['disk_usage'] ?? null,
            'load_average' => $context['load_average'] ?? null,
            'network_io' => $context['network_io'] ?? null,
        ];
    }

    public function analyzeThresholdViolations()
    {
        $metrics = $this->getMetricsData();
        $violations = [];

        // Define critical thresholds for server infrastructure
        $thresholds = [
            'cpu_usage' => ['warning' => 70, 'critical' => 85],
            'memory_usage' => ['warning' => 75, 'critical' => 90],
            'disk_usage' => ['warning' => 80, 'critical' => 95],
            'load_average' => ['warning' => 2.0, 'critical' => 4.0],
        ];

        foreach ($metrics as $metric => $value) {
            if ($value !== null && isset($thresholds[$metric])) {
                if ($value >= $thresholds[$metric]['critical']) {
                    $violations[] = [
                        'metric' => $metric,
                        'value' => $value,
                        'severity' => 'critical',
                        'threshold' => $thresholds[$metric]['critical']
                    ];
                } elseif ($value >= $thresholds[$metric]['warning']) {
                    $violations[] = [
                        'metric' => $metric,
                        'value' => $value,
                        'severity' => 'warning',
                        'threshold' => $thresholds[$metric]['warning']
                    ];
                }
            }
        }

        return $violations;
    }

    public function getImpactAnalysis()
    {
        $violations = $this->analyzeThresholdViolations();
        $metrics = $this->getMetricsData();
        
        $analysis = [
            'overall_risk' => 'low',
            'affected_systems' => [],
            'predicted_issues' => [],
            'immediate_actions' => []
        ];

        foreach ($violations as $violation) {
            switch ($violation['metric']) {
                case 'cpu_usage':
                    if ($violation['severity'] === 'critical') {
                        $analysis['overall_risk'] = 'critical';
                        $analysis['affected_systems'][] = 'Application Performance';
                        $analysis['predicted_issues'][] = 'Service timeouts and unresponsiveness';
                        $analysis['immediate_actions'][] = 'Scale CPU resources or optimize processes';
                    }
                    break;
                case 'memory_usage':
                    if ($violation['severity'] === 'critical') {
                        $analysis['overall_risk'] = 'critical';
                        $analysis['affected_systems'][] = 'System Stability';
                        $analysis['predicted_issues'][] = 'Process kills and system crashes';
                        $analysis['immediate_actions'][] = 'Free memory or add RAM capacity';
                    }
                    break;
                case 'disk_usage':
                    if ($violation['severity'] === 'critical') {
                        $analysis['overall_risk'] = 'critical';
                        $analysis['affected_systems'][] = 'Data Storage';
                        $analysis['predicted_issues'][] = 'Application failures and data loss';
                        $analysis['immediate_actions'][] = 'Clean up disk space or expand storage';
                    }
                    break;
            }
        }

        return $analysis;
    }

    public function getRecommendations()
    {
        $violations = $this->analyzeThresholdViolations();
        $recommendations = [];

        foreach ($violations as $violation) {
            switch ($violation['metric']) {
                case 'cpu_usage':
                    $recommendations[] = [
                        'type' => 'optimization',
                        'priority' => $violation['severity'] === 'critical' ? 'high' : 'medium',
                        'action' => 'Analyze CPU-intensive processes and consider load balancing',
                        'details' => 'Current CPU usage: ' . $violation['value'] . '%'
                    ];
                    break;
                case 'memory_usage':
                    $recommendations[] = [
                        'type' => 'resource',
                        'priority' => $violation['severity'] === 'critical' ? 'high' : 'medium',
                        'action' => 'Monitor memory leaks and consider RAM upgrade',
                        'details' => 'Current memory usage: ' . $violation['value'] . '%'
                    ];
                    break;
                case 'disk_usage':
                    $recommendations[] = [
                        'type' => 'maintenance',
                        'priority' => $violation['severity'] === 'critical' ? 'high' : 'medium',
                        'action' => 'Implement log rotation and cleanup policies',
                        'details' => 'Current disk usage: ' . $violation['value'] . '%'
                    ];
                    break;
            }
        }

        return $recommendations;
    }

    public function render()
    {
        return view('livewire.log-details');
    }
}

