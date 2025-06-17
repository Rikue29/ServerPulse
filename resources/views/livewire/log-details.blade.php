<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Infrastructure Alert Analysis</h1>
                    <p class="text-gray-600 mt-2">Log ID: {{ $log->id }} • {{ $log->created_at->format('F j, Y \a\t g:i A') }}</p>
                </div>
                <div class="flex space-x-3">
                    <button wire:click="copyToClipboard" 
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 flex items-center space-x-2">
                        <i class="fas fa-copy"></i>
                        <span>Copy Data</span>
                    </button>
                    <button onclick="window.print()" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center space-x-2">
                        <i class="fas fa-print"></i>
                        <span>Print Report</span>
                    </button>
                    <a href="{{ route('logs.index') }}" 
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 flex items-center space-x-2">
                        <i class="fas fa-arrow-left"></i>
                        <span>Back to Logs</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Alert Overview -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <div class="lg:col-span-2">
                <!-- Main Alert Card -->
                <div class="bg-white rounded-xl shadow-lg border-l-4 @if($log->level === 'error') border-red-500 @elseif($log->level === 'warning') border-yellow-500 @else border-blue-500 @endif p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-4">
                                @if($log->level === 'error')
                                    <div class="p-3 bg-red-100 rounded-full">
                                        <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                                    </div>
                                    <span class="px-3 py-1 bg-red-100 text-red-800 text-sm font-semibold rounded-full">CRITICAL ALERT</span>
                                @elseif($log->level === 'warning')
                                    <div class="p-3 bg-yellow-100 rounded-full">
                                        <i class="fas fa-exclamation-circle text-yellow-600 text-xl"></i>
                                    </div>
                                    <span class="px-3 py-1 bg-yellow-100 text-yellow-800 text-sm font-semibold rounded-full">WARNING</span>
                                @else
                                    <div class="p-3 bg-blue-100 rounded-full">
                                        <i class="fas fa-info-circle text-blue-600 text-xl"></i>
                                    </div>
                                    <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-semibold rounded-full">INFORMATION</span>
                                @endif
                            </div>
                            <h2 class="text-xl font-semibold text-gray-900 mb-2">{{ $log->message }}</h2>
                            <p class="text-gray-600 mb-4">Server: <span class="font-medium">{{ $log->server->name ?? 'Unknown' }}</span></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Risk Assessment -->
            @php $impact = $this->getImpactAnalysis(); @endphp
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Risk Assessment</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Overall Risk:</span>
                        <span class="px-3 py-1 rounded-full text-sm font-medium @if($impact['overall_risk'] === 'critical') bg-red-100 text-red-800 @elseif($impact['overall_risk'] === 'high') bg-yellow-100 text-yellow-800 @else bg-green-100 text-green-800 @endif">
                            {{ ucfirst($impact['overall_risk']) }}
                        </span>
                    </div>
                    @if(!empty($impact['affected_systems']))
                        <div>
                            <span class="text-gray-600 text-sm">Affected Systems:</span>
                            <ul class="mt-1 space-y-1">
                                @foreach($impact['affected_systems'] as $system)
                                    <li class="text-sm text-gray-800">• {{ $system }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Metrics Analysis -->
        @php $metrics = $this->getMetricsData(); @endphp
        @if(array_filter($metrics))
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-6">Performance Metrics at Time of Alert</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @if($metrics['cpu_usage'])
                    <div class="text-center">
                        <div class="relative inline-flex items-center justify-center w-20 h-20 mb-3">
                            <svg class="w-20 h-20 transform -rotate-90">
                                <circle cx="40" cy="40" r="32" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                                <circle cx="40" cy="40" r="32" stroke="@if($metrics['cpu_usage'] >= 85) #ef4444 @elseif($metrics['cpu_usage'] >= 70) #f59e0b @else #10b981 @endif" 
                                    stroke-width="8" fill="none" stroke-dasharray="{{ 2 * pi() * 32 }}" 
                                    stroke-dashoffset="{{ 2 * pi() * 32 * (1 - $metrics['cpu_usage'] / 100) }}"/>
                            </svg>
                            <span class="absolute text-sm font-bold">{{ $metrics['cpu_usage'] }}%</span>
                        </div>
                        <h4 class="font-medium text-gray-900">CPU Usage</h4>
                        @if($metrics['cpu_usage'] >= 85)
                            <p class="text-xs text-red-600 mt-1">Critical Level</p>
                        @elseif($metrics['cpu_usage'] >= 70)
                            <p class="text-xs text-yellow-600 mt-1">Warning Level</p>
                        @endif
                    </div>
                @endif

                @if($metrics['memory_usage'])
                    <div class="text-center">
                        <div class="relative inline-flex items-center justify-center w-20 h-20 mb-3">
                            <svg class="w-20 h-20 transform -rotate-90">
                                <circle cx="40" cy="40" r="32" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                                <circle cx="40" cy="40" r="32" stroke="@if($metrics['memory_usage'] >= 90) #ef4444 @elseif($metrics['memory_usage'] >= 75) #f59e0b @else #10b981 @endif" 
                                    stroke-width="8" fill="none" stroke-dasharray="{{ 2 * pi() * 32 }}" 
                                    stroke-dashoffset="{{ 2 * pi() * 32 * (1 - $metrics['memory_usage'] / 100) }}"/>
                            </svg>
                            <span class="absolute text-sm font-bold">{{ $metrics['memory_usage'] }}%</span>
                        </div>
                        <h4 class="font-medium text-gray-900">Memory Usage</h4>
                        @if($metrics['memory_usage'] >= 90)
                            <p class="text-xs text-red-600 mt-1">Critical Level</p>
                        @elseif($metrics['memory_usage'] >= 75)
                            <p class="text-xs text-yellow-600 mt-1">Warning Level</p>
                        @endif
                    </div>
                @endif

                @if($metrics['disk_usage'])
                    <div class="text-center">
                        <div class="relative inline-flex items-center justify-center w-20 h-20 mb-3">
                            <svg class="w-20 h-20 transform -rotate-90">
                                <circle cx="40" cy="40" r="32" stroke="#e5e7eb" stroke-width="8" fill="none"/>
                                <circle cx="40" cy="40" r="32" stroke="@if($metrics['disk_usage'] >= 95) #ef4444 @elseif($metrics['disk_usage'] >= 80) #f59e0b @else #10b981 @endif" 
                                    stroke-width="8" fill="none" stroke-dasharray="{{ 2 * pi() * 32 }}" 
                                    stroke-dashoffset="{{ 2 * pi() * 32 * (1 - $metrics['disk_usage'] / 100) }}"/>
                            </svg>
                            <span class="absolute text-sm font-bold">{{ $metrics['disk_usage'] }}%</span>
                        </div>
                        <h4 class="font-medium text-gray-900">Disk Usage</h4>
                        @if($metrics['disk_usage'] >= 95)
                            <p class="text-xs text-red-600 mt-1">Critical Level</p>
                        @elseif($metrics['disk_usage'] >= 80)
                            <p class="text-xs text-yellow-600 mt-1">Warning Level</p>
                        @endif
                    </div>
                @endif

                @if($metrics['load_average'])
                    <div class="text-center">
                        <div class="relative inline-flex items-center justify-center w-20 h-20 mb-3">
                            <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center">
                                <span class="text-sm font-bold">{{ $metrics['load_average'] }}</span>
                            </div>
                        </div>
                        <h4 class="font-medium text-gray-900">Load Average</h4>
                        @if($metrics['load_average'] >= 4.0)
                            <p class="text-xs text-red-600 mt-1">Critical Level</p>
                        @elseif($metrics['load_average'] >= 2.0)
                            <p class="text-xs text-yellow-600 mt-1">Warning Level</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Threshold Violations & Recommendations -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Threshold Violations -->
            @php $violations = $this->analyzeThresholdViolations(); @endphp
            @if(!empty($violations))
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-chart-line text-red-500 mr-2"></i>
                    Threshold Violations
                </h3>
                <div class="space-y-3">
                    @foreach($violations as $violation)
                        <div class="p-4 border-l-4 @if($violation['severity'] === 'critical') border-red-500 bg-red-50 @else border-yellow-500 bg-yellow-50 @endif rounded-r-lg">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-medium @if($violation['severity'] === 'critical') text-red-800 @else text-yellow-800 @endif">
                                        {{ ucwords(str_replace('_', ' ', $violation['metric'])) }}
                                    </h4>
                                    <p class="text-sm @if($violation['severity'] === 'critical') text-red-600 @else text-yellow-600 @endif">
                                        Current: {{ $violation['value'] }}{{ in_array($violation['metric'], ['cpu_usage', 'memory_usage', 'disk_usage']) ? '%' : '' }}
                                        | Threshold: {{ $violation['threshold'] }}{{ in_array($violation['metric'], ['cpu_usage', 'memory_usage', 'disk_usage']) ? '%' : '' }}
                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium rounded @if($violation['severity'] === 'critical') bg-red-200 text-red-800 @else bg-yellow-200 text-yellow-800 @endif">
                                    {{ ucfirst($violation['severity']) }}
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Recommendations -->
            @php $recommendations = $this->getRecommendations(); @endphp
            @if(!empty($recommendations))
            <div class="bg-white rounded-xl shadow-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-lightbulb text-blue-500 mr-2"></i>
                    Recommended Actions
                </h3>
                <div class="space-y-3">
                    @foreach($recommendations as $rec)
                        <div class="p-4 border-l-4 @if($rec['priority'] === 'high') border-red-500 bg-red-50 @else border-blue-500 bg-blue-50 @endif rounded-r-lg">
                            <div class="flex justify-between items-start mb-2">
                                <h4 class="font-medium @if($rec['priority'] === 'high') text-red-800 @else text-blue-800 @endif">
                                    {{ $rec['action'] }}
                                </h4>
                                <span class="px-2 py-1 text-xs font-medium rounded @if($rec['priority'] === 'high') bg-red-200 text-red-800 @else bg-blue-200 text-blue-800 @endif">
                                    {{ ucfirst($rec['priority']) }} Priority
                                </span>
                            </div>
                            <p class="text-sm @if($rec['priority'] === 'high') text-red-600 @else text-blue-600 @endif">{{ $rec['details'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Impact Analysis -->
        @if(!empty($impact['predicted_issues']) || !empty($impact['immediate_actions']))
        <div class="bg-white rounded-xl shadow-lg p-6 mb-8">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-exclamation-triangle text-orange-500 mr-2"></i>
                Impact Analysis & Immediate Actions
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                @if(!empty($impact['predicted_issues']))
                    <div>
                        <h4 class="font-medium text-orange-800 mb-3">Predicted Issues</h4>
                        <ul class="space-y-2">
                            @foreach($impact['predicted_issues'] as $issue)
                                <li class="flex items-start">
                                    <i class="fas fa-exclamation-circle text-orange-500 mt-1 mr-2 flex-shrink-0"></i>
                                    <span class="text-gray-700">{{ $issue }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if(!empty($impact['immediate_actions']))
                    <div>
                        <h4 class="font-medium text-green-800 mb-3">Immediate Actions Required</h4>
                        <ul class="space-y-2">
                            @foreach($impact['immediate_actions'] as $action)
                                <li class="flex items-start">
                                    <i class="fas fa-check-circle text-green-500 mt-1 mr-2 flex-shrink-0"></i>
                                    <span class="text-gray-700">{{ $action }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>
        @endif

        <!-- Raw Log Data -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                <i class="fas fa-code text-gray-500 mr-2"></i>
                Technical Details
            </h3>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Log Information</h4>
                    <div class="bg-gray-50 rounded-lg p-4 text-sm space-y-2">
                        <div><strong>ID:</strong> {{ $log->id }}</div>
                        <div><strong>Timestamp:</strong> {{ $log->created_at->format('Y-m-d H:i:s T') }}</div>
                        <div><strong>Level:</strong> {{ strtoupper($log->level) }}</div>
                        <div><strong>Server:</strong> {{ $log->server->name ?? 'N/A' }}</div>
                        @if($log->server)
                            <div><strong>Server IP:</strong> {{ $log->server->ip_address ?? 'N/A' }}</div>
                        @endif
                    </div>
                </div>
                @if($log->context)
                <div>
                    <h4 class="font-medium text-gray-700 mb-2">Context Data</h4>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <pre class="text-xs text-gray-600 whitespace-pre-wrap">{{ json_encode(is_string($log->context) ? json_decode($log->context, true) : $log->context, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Copy to Clipboard Script -->
    <script>
        window.addEventListener('copy-to-clipboard', event => {
            navigator.clipboard.writeText(event.detail.content).then(() => {
                alert('Log data copied to clipboard!');
            });
        });
    </script>
</div>
