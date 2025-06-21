@extends('layouts.app')

@section('title', 'System Analysis Report - Log #' . $log->id)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Sticky Top Navigation Bar -->
    <div class="sticky top-0 z-50 bg-white border-b border-gray-200 shadow-sm print:hidden">
        <div class="max-w-6xl mx-auto px-4 py-4">
            <div class="flex items-center justify-between">
                <!-- Breadcrumb Navigation -->
                <nav class="flex items-center space-x-2 text-sm">
                    <a href="{{ route('logs.index') }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        <i class="fas fa-list mr-1"></i> All Logs
                    </a>
                    <span class="text-gray-400">→</span>
                    <a href="{{ route('logs.show', $log) }}" class="text-blue-600 hover:text-blue-800 font-medium">
                        <i class="fas fa-eye mr-1"></i> Log Details
                    </a>
                    <span class="text-gray-400">→</span>
                    <span class="text-gray-600 font-medium">
                        <i class="fas fa-file-alt mr-1"></i> System Analysis Report
                    </span>
                </nav>

                <!-- Action Buttons -->
                <div class="flex items-center space-x-3">
                    <div class="flex bg-gray-100 rounded-lg p-1">
                        <button onclick="window.print()" 
                                class="flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white rounded-md shadow-sm hover:bg-gray-50 transition-colors">
                            <i class="fas fa-print mr-2"></i>
                            Print
                        </button>
                        <a href="{{ route('logs.download', $log) }}" 
                           class="flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700 transition-colors ml-1">
                            <i class="fas fa-file-pdf mr-2"></i>
                            PDF
                        </a>
                    </div>
                    
                    <!-- Back to Details Button -->
                    <a href="{{ route('logs.show', $log) }}" 
                       class="flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Details
                    </a>
                </div>
            </div>
        </div>    </div>

    <!-- Google Docs-style Layout -->
    <div class="flex bg-gray-50 min-h-screen">
        <!-- Fixed Left Sidebar Navigation -->
        <aside class="fixed left-0 top-0 h-full w-64 bg-white border-r border-gray-200 shadow-sm z-40 print:hidden">
            <!-- Sidebar Header -->
            <div class="p-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-list-ul text-blue-600 mr-2"></i>
                    Quick Navigation
                </h3>
            </div>
            
            <!-- Navigation Menu -->
            <nav class="h-full overflow-y-auto py-4">
                <div class="px-3 space-y-1">
                    <a href="#executive-summary" class="nav-link group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                        <i class="fas fa-clipboard-list w-4 h-4 mr-3 text-gray-400 group-hover:text-blue-500"></i>
                        <span>Executive Summary</span>
                    </a>
                    <a href="#alert-details" class="nav-link group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                        <i class="fas fa-server w-4 h-4 mr-3 text-gray-400 group-hover:text-blue-500"></i>
                        <span>Technical Analysis</span>
                    </a>
                    <a href="#system-metrics" class="nav-link group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                        <i class="fas fa-chart-line w-4 h-4 mr-3 text-gray-400 group-hover:text-blue-500"></i>
                        <span>System Metrics</span>
                    </a>
                    <a href="#log-message" class="nav-link group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                        <i class="fas fa-envelope w-4 h-4 mr-3 text-gray-400 group-hover:text-blue-500"></i>
                        <span>Log Message</span>
                    </a>
                    <a href="#context-data" class="nav-link group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                        <i class="fas fa-code w-4 h-4 mr-3 text-gray-400 group-hover:text-blue-500"></i>
                        <span>Context Data</span>
                    </a>
                    <a href="#related-events" class="nav-link group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                        <i class="fas fa-link w-4 h-4 mr-3 text-gray-400 group-hover:text-blue-500"></i>
                        <span>Related Events</span>
                    </a>
                    <a href="#recommendations" class="nav-link group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                        <i class="fas fa-lightbulb w-4 h-4 mr-3 text-gray-400 group-hover:text-blue-500"></i>
                        <span>Recommendations</span>
                    </a>
                    <a href="#incident-timeline" class="nav-link group flex items-center px-3 py-2 text-sm font-medium rounded-md text-gray-700 hover:bg-blue-50 hover:text-blue-700 transition-colors">
                        <i class="fas fa-clock w-4 h-4 mr-3 text-gray-400 group-hover:text-blue-500"></i>
                        <span>Timeline</span>
                    </a>
                </div>
            </nav>
        </aside>        <!-- Main Document Area -->
        <main class="flex-1 ml-64 print:ml-0">
            <!-- Document Container -->
            <div class="max-w-4xl mx-auto px-8 py-6">
                <!-- Document Paper-like Container -->
                <div class="bg-white shadow-sm border border-gray-200 rounded-lg min-h-screen print:shadow-none print:border-none">
                    <!-- Document Content with proper padding -->
                    <div class="p-8 print:p-6">
            
            <!-- Report Header -->
            <div class="border-b border-gray-200 pb-4 mb-6">
                <div class="row align-items-start">
                    <div class="col-md-8">
                        <h1 class="h2 text-primary mb-2">System Analysis Report</h1>
                        <div class="bg-light border rounded p-3 mb-3">
                            <p class="mb-1 fw-semibold">Incident ID: #{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }}</p>
                            <p class="mb-0 text-muted small">Generated on {{ now()->format('F j, Y \a\t g:i A T') }}</p>
                        </div>
                    </div>
                    <div class="col-md-4 text-end">
                        <div class="bg-white border rounded p-3">
                            <div class="fw-bold">ServerPulse Monitoring</div>
                            <div class="text-sm text-gray-600">Infrastructure Analysis System</div>
                            <div class="text-xs text-gray-500 mt-1">{{ config('app.url', 'monitoring.local') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Alert Level Banner -->
                <div class="bg-gradient-to-r 
                    {{ $log->level == 'error' ? 'from-red-500 to-red-600' : ($log->level == 'warning' ? 'from-yellow-500 to-yellow-600' : 'from-blue-500 to-blue-600') }} 
                    text-white rounded-lg p-6 mb-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-4">
                            <div class="p-3 bg-white bg-opacity-20 rounded-full">
                                @if($log->level == 'error')
                                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                                @elseif($log->level == 'warning')
                                    <i class="fas fa-exclamation-circle text-2xl"></i>
                                @else
                                    <i class="fas fa-info-circle text-2xl"></i>
                                @endif
                            </div>
                            <div>
                                <h2 class="text-2xl font-bold">{{ ucfirst($log->level) }} Level Alert</h2>
                                <p class="text-lg opacity-90">{{ $log->created_at->format('F j, Y \a\t g:i:s A') }}</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm opacity-80">Server</div>
                            <div class="text-xl font-bold">{{ $log->server->name ?? 'Unknown' }}</div>
                            @if($log->server)
                                <div class="text-sm opacity-80">{{ $log->server->ip_address }}</div>
                            @endif
                        </div>
                    </div>
                </div>            </div>            <!-- Executive Summary -->
            <div id="executive-summary" class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-clipboard-list text-blue-600 mr-3"></i>
                    Executive Summary
                </h2>
                <div class="bg-gray-50 border border-gray-200 rounded-lg p-6">
                    <div class="prose max-w-none">
                        <p class="text-lg text-gray-800 leading-relaxed mb-4">
                            @if($log->level == 'error')
                                <strong class="text-red-600">CRITICAL SYSTEM ALERT:</strong> Our monitoring infrastructure has detected a critical error condition on server 
                                <strong>{{ $log->server->name ?? 'Unknown' }}</strong> ({{ $log->server->ip_address ?? 'IP not available' }}) at {{ $log->created_at->format('g:i A \o\n F j, Y') }}. 
                                This incident has been classified as high-priority and requires immediate technical intervention to prevent potential service disruption and maintain system reliability.
                                
                                <br><br>The error originated from the {{ ucfirst($log->source) }} subsystem and was automatically detected by our continuous monitoring protocols. 
                                Based on preliminary analysis, this condition may impact system performance and could potentially affect user experience if left unresolved.
                            @elseif($log->level == 'warning')
                                <strong class="text-yellow-600">PERFORMANCE ADVISORY:</strong> Our monitoring system has identified a warning condition on server 
                                <strong>{{ $log->server->name ?? 'Unknown' }}</strong> ({{ $log->server->ip_address ?? 'IP not available' }}) at {{ $log->created_at->format('g:i A \o\n F j, Y') }}. 
                                While this condition does not pose an immediate threat to system operations, it indicates a potential area of concern that warrants monitoring and possible optimization.
                                
                                <br><br>The alert was triggered by the {{ ucfirst($log->source) }} monitoring component and suggests that system metrics have crossed predetermined threshold levels. 
                                Proactive investigation and corrective measures may prevent this condition from escalating to a more serious state.
                            @else
                                <strong class="text-blue-600">SYSTEM STATUS UPDATE:</strong> An informational event has been recorded for server 
                                <strong>{{ $log->server->name ?? 'Unknown' }}</strong> ({{ $log->server->ip_address ?? 'IP not available' }}) at {{ $log->created_at->format('g:i A \o\n F j, Y') }}. 
                                This event represents normal system operations and has been logged for audit trail, compliance, and analytical purposes.
                                
                                <br><br>The information was captured by the {{ ucfirst($log->source) }} monitoring system as part of routine operational logging. 
                                This data contributes to our comprehensive system health assessment and helps maintain detailed records of infrastructure performance patterns.
                            @endif
                        </p>
                        
                        <div class="bg-white border border-gray-200 rounded-lg p-4 mt-6">
                            <h4 class="font-semibold text-gray-900 mb-3 flex items-center">
                                <i class="fas fa-analytics text-blue-600 mr-2"></i>
                                Impact Assessment & Key Findings
                            </h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <h5 class="font-medium text-gray-800 mb-2">Technical Details:</h5>
                                    <ul class="list-disc list-inside space-y-1 text-gray-700 text-sm">
                                        <li><strong>Event Source:</strong> {{ ucfirst($log->source) }} monitoring system</li>
                                        <li><strong>Severity Classification:</strong> {{ ucfirst($log->level) }} level incident</li>
                                        <li><strong>Detection Time:</strong> {{ $log->created_at->diffForHumans() }}</li>
                                        <li><strong>Server Status:</strong> {{ $log->server ? ucfirst($log->server->status) : 'Unknown' }}</li>
                                        @if($log->context && (isset($log->context['cpu_usage']) || isset($log->context['memory_usage']) || isset($log->context['disk_usage'])))
                                            <li><strong>Performance Data:</strong> System metrics captured at event time</li>
                                        @endif
                                    </ul>
                                </div>
                                <div>
                                    <h5 class="font-medium text-gray-800 mb-2">Business Impact:</h5>
                                    <ul class="list-disc list-inside space-y-1 text-gray-700 text-sm">
                                        @if($log->level == 'error')
                                            <li>Potential service disruption or degraded performance</li>
                                            <li>Immediate escalation required to on-call team</li>
                                            <li>Customer experience may be impacted</li>
                                            <li>SLA compliance may be at risk</li>
                                        @elseif($log->level == 'warning')
                                            <li>Performance optimization opportunity identified</li>
                                            <li>Preventive action recommended within 24 hours</li>
                                            <li>No immediate customer impact expected</li>
                                            <li>Trend monitoring advised to prevent escalation</li>
                                        @else
                                            <li>No business impact - informational event</li>
                                            <li>Contributes to operational awareness</li>
                                            <li>Supports capacity planning and trend analysis</li>
                                            <li>Maintains audit trail for compliance</li>
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!-- Technical Details -->
            <div id="alert-details" class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-server text-blue-600 mr-3"></i>
                    Technical Analysis
                </h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Primary Event Details -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Event Details</h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Event ID</dt>
                                <dd class="mt-1 text-lg font-mono text-gray-900">#{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Timestamp</dt>
                                <dd class="mt-1 text-lg text-gray-900">{{ $log->created_at->format('M j, Y g:i:s A T') }}</dd>
                                <dd class="text-sm text-gray-500">{{ $log->created_at->diffForHumans() }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Severity Level</dt>
                                <dd class="mt-1">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                        {{ $log->level == 'error' ? 'bg-red-100 text-red-800 border border-red-200' : ($log->level == 'warning' ? 'bg-yellow-100 text-yellow-800 border border-yellow-200' : 'bg-blue-100 text-blue-800 border border-blue-200') }}">
                                        {{ ucfirst($log->level) }}
                                    </span>
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Source Component</dt>
                                <dd class="mt-1 text-lg text-gray-900">{{ ucfirst($log->source) }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Server Information -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Server Information</h3>
                        @if($log->server)
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Server Name</dt>
                                    <dd class="mt-1 text-lg text-gray-900">{{ $log->server->name }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">IP Address</dt>
                                    <dd class="mt-1 text-lg font-mono text-gray-900">{{ $log->server->ip_address }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Current Status</dt>
                                    <dd class="mt-1">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                            {{ $log->server->status == 'online' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' }}">
                                            {{ ucfirst($log->server->status) }}
                                        </span>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500 uppercase tracking-wider">Server ID</dt>
                                    <dd class="mt-1 text-lg font-mono text-gray-900">{{ $log->server->id }}</dd>
                                </div>
                            </dl>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-server text-gray-400 text-3xl mb-2"></i>
                                <p class="text-gray-500">Server information not available</p>
                            </div>
                        @endif
                    </div>
                </div>                <!-- Event Message -->
                <div id="log-message" class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Event Message</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <pre class="text-sm text-gray-900 whitespace-pre-wrap font-mono leading-relaxed">{{ $log->message }}</pre>
                    </div>
                </div>

                @if($log->context)
                <div id="context-data" class="mt-6 bg-white border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Context Data</h3>
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <pre class="text-sm text-gray-900 whitespace-pre-wrap font-mono">{{ json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
                @endif
            </div>

            <!-- Performance Metrics -->
            @if($log->context && (isset($log->context['cpu_usage']) || isset($log->context['memory_usage']) || isset($log->context['disk_usage'])))
            <div id="system-metrics" class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-chart-line text-blue-600 mr-3"></i>
                    Performance Metrics at Time of Event
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @if(isset($log->context['cpu_usage']))
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">CPU Usage</h3>
                            <i class="fas fa-microchip text-blue-500 text-xl"></i>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold mb-2 {{ $log->context['cpu_usage'] > 80 ? 'text-red-600' : ($log->context['cpu_usage'] > 60 ? 'text-yellow-600' : 'text-green-600') }}">
                                {{ $log->context['cpu_usage'] }}%
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                <div class="h-3 rounded-full {{ $log->context['cpu_usage'] > 80 ? 'bg-red-500' : ($log->context['cpu_usage'] > 60 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                                     style="width: {{ $log->context['cpu_usage'] }}%"></div>
                            </div>
                            <span class="text-sm font-medium {{ $log->context['cpu_usage'] > 80 ? 'text-red-600' : ($log->context['cpu_usage'] > 60 ? 'text-yellow-600' : 'text-green-600') }}">
                                {{ $log->context['cpu_usage'] > 80 ? 'CRITICAL' : ($log->context['cpu_usage'] > 60 ? 'WARNING' : 'NORMAL') }}
                            </span>
                        </div>
                    </div>
                    @endif

                    @if(isset($log->context['memory_usage']))
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Memory Usage</h3>
                            <i class="fas fa-memory text-blue-500 text-xl"></i>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold mb-2 {{ $log->context['memory_usage'] > 90 ? 'text-red-600' : ($log->context['memory_usage'] > 70 ? 'text-yellow-600' : 'text-green-600') }}">
                                {{ $log->context['memory_usage'] }}%
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                <div class="h-3 rounded-full {{ $log->context['memory_usage'] > 90 ? 'bg-red-500' : ($log->context['memory_usage'] > 70 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                                     style="width: {{ $log->context['memory_usage'] }}%"></div>
                            </div>
                            <span class="text-sm font-medium {{ $log->context['memory_usage'] > 90 ? 'text-red-600' : ($log->context['memory_usage'] > 70 ? 'text-yellow-600' : 'text-green-600') }}">
                                {{ $log->context['memory_usage'] > 90 ? 'CRITICAL' : ($log->context['memory_usage'] > 70 ? 'WARNING' : 'NORMAL') }}
                            </span>
                        </div>
                    </div>
                    @endif

                    @if(isset($log->context['disk_usage']))
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Disk Usage</h3>
                            <i class="fas fa-hdd text-blue-500 text-xl"></i>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold mb-2 {{ $log->context['disk_usage'] > 85 ? 'text-red-600' : ($log->context['disk_usage'] > 70 ? 'text-yellow-600' : 'text-green-600') }}">
                                {{ $log->context['disk_usage'] }}%
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3 mb-2">
                                <div class="h-3 rounded-full {{ $log->context['disk_usage'] > 85 ? 'bg-red-500' : ($log->context['disk_usage'] > 70 ? 'bg-yellow-500' : 'bg-green-500') }}" 
                                     style="width: {{ $log->context['disk_usage'] }}%"></div>
                            </div>
                            <span class="text-sm font-medium {{ $log->context['disk_usage'] > 85 ? 'text-red-600' : ($log->context['disk_usage'] > 70 ? 'text-yellow-600' : 'text-green-600') }}">
                                {{ $log->context['disk_usage'] > 85 ? 'CRITICAL' : ($log->context['disk_usage'] > 70 ? 'WARNING' : 'NORMAL') }}
                            </span>
                        </div>
                    </div>
                    @endif
                </div>
            </div>            @endif
            <!-- Related Events -->
            @if($relatedLogs->count() > 0)
            <div id="related-events" class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-link text-blue-600 mr-3"></i>
                    Related Events Timeline (±30 minutes)
                </h2>
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Severity</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Event Message</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Correlation</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($relatedLogs as $relatedLog)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-mono text-gray-900">
                                        {{ $relatedLog->created_at->format('H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium 
                                            {{ $relatedLog->level == 'error' ? 'bg-red-100 text-red-800' : ($relatedLog->level == 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ ucfirst($relatedLog->level) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $relatedLog->source }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        <div class="max-w-xs truncate" title="{{ $relatedLog->message }}">
                                            {{ \Illuminate\Support\Str::limit($relatedLog->message, 60) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-xs text-gray-500">
                                        @if($relatedLog->created_at < $log->created_at)
                                            <span class="text-blue-600">Before</span>
                                        @else
                                            <span class="text-green-600">After</span>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>            @endif            <!-- Recommendations and Action Items -->
            <div id="recommendations" class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-lightbulb text-blue-600 mr-3"></i>
                    Recommendations & Strategic Action Plan
                </h2>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Immediate Actions -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-2"></i>
                            Immediate Response Protocol
                        </h3>
                        <div class="space-y-4">
                            @if($log->level == 'error')
                                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                                    <h4 class="font-medium text-red-800 mb-2">Critical Priority Actions (0-30 minutes):</h4>
                                    <ul class="space-y-2">
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-red-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-red-700"><strong>Escalate immediately:</strong> Contact on-call engineer and incident response team</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-red-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-red-700"><strong>Service health check:</strong> Verify user-facing services and customer impact</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-red-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-red-700"><strong>Emergency procedures:</strong> Activate incident management protocol and stakeholder notification</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-red-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-red-700"><strong>Containment strategy:</strong> Implement failover procedures if available</span>
                                        </li>
                                    </ul>
                                </div>
                            @elseif($log->level == 'warning')
                                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                                    <h4 class="font-medium text-yellow-800 mb-2">Priority Actions (Within 2-4 hours):</h4>
                                    <ul class="space-y-2">
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-yellow-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-yellow-700"><strong>Assignment:</strong> Schedule investigation with appropriate technical team</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-yellow-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-yellow-700"><strong>Enhanced monitoring:</strong> Implement additional alerting for similar conditions</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-yellow-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-yellow-700"><strong>Threshold analysis:</strong> Review alert sensitivity and adjust if necessary</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-yellow-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-yellow-700"><strong>Trend evaluation:</strong> Analyze historical patterns for similar warnings</span>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                    <h4 class="font-medium text-blue-800 mb-2">Standard Monitoring Actions:</h4>
                                    <ul class="space-y-2">
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-blue-700"><strong>No immediate action required:</strong> Event logged for informational purposes</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-blue-700"><strong>Continuous monitoring:</strong> Maintain regular observation schedule</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-blue-700"><strong>Data collection:</strong> Archive event data for analytical purposes</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-blue-700"><strong>Compliance logging:</strong> Ensure audit trail requirements are met</span>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Strategic Recommendations -->
                    <div class="bg-white border border-gray-200 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-chart-line text-blue-500 mr-2"></i>
                            Strategic Improvement Plan
                        </h3>
                        <div class="space-y-4">
                            @if($log->level == 'error')
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-800 mb-2">Long-term Resilience (1-4 weeks):</h4>
                                    <ul class="space-y-2">
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-gray-700"><strong>Root cause analysis:</strong> Conduct comprehensive investigation to identify underlying causes</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-gray-700"><strong>Infrastructure hardening:</strong> Implement redundancy and failover mechanisms</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-gray-700"><strong>Process enhancement:</strong> Update incident response procedures and team training</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-gray-700"><strong>Documentation update:</strong> Revise troubleshooting guides and operational runbooks</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-gray-700"><strong>Monitoring optimization:</strong> Enhance early warning systems and alert granularity</span>
                                        </li>
                                    </ul>
                                </div>
                            @else
                                <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                    <h4 class="font-medium text-gray-800 mb-2">Continuous Improvement (Ongoing):</h4>
                                    <ul class="space-y-2">
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-gray-700"><strong>Trend analysis:</strong> Establish baseline patterns and identify optimization opportunities</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-gray-700"><strong>Capacity planning:</strong> Use performance data for future resource allocation decisions</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-gray-700"><strong>Process automation:</strong> Implement automated responses for routine events</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-gray-700"><strong>Performance optimization:</strong> Fine-tune system configurations based on usage patterns</span>
                                        </li>
                                        <li class="flex items-start">
                                            <span class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full mt-2 mr-3"></span>
                                            <span class="text-gray-700"><strong>Knowledge management:</strong> Document insights and learnings for team knowledge base</span>
                                        </li>
                                    </ul>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Success Metrics and Follow-up -->
                <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-blue-900 mb-4 flex items-center">
                        <i class="fas fa-target text-blue-600 mr-2"></i>
                        Success Metrics & Follow-up Protocol
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <h4 class="font-medium text-blue-800 mb-2">Key Performance Indicators:</h4>
                            <ul class="text-sm text-blue-700 space-y-1">
                                @if($log->level == 'error')
                                    <li>• Time to resolution (Target: < 1 hour for critical issues)</li>
                                    <li>• Service availability restoration (Target: 99.9% uptime)</li>
                                    <li>• Customer impact assessment completion</li>
                                    <li>• Post-incident review and documentation</li>
                                @else
                                    <li>• Event response time (Target: < 4 hours for warnings)</li>
                                    <li>• Trend analysis completion within 24 hours</li>
                                    <li>• Proactive optimization implementation</li>
                                    <li>• Continuous monitoring effectiveness assessment</li>
                                @endif
                            </ul>
                        </div>
                        <div>
                            <h4 class="font-medium text-blue-800 mb-2">Follow-up Schedule:</h4>
                            <ul class="text-sm text-blue-700 space-y-1">
                                <li>• <strong>24 hours:</strong> Initial response verification and status update</li>
                                <li>• <strong>1 week:</strong> Implementation progress review</li>
                                <li>• <strong>1 month:</strong> Effectiveness assessment and fine-tuning</li>
                                <li>• <strong>Quarterly:</strong> Strategic review and continuous improvement evaluation</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>            <!-- Report Footer -->
            <div class="border-t border-gray-200 pt-6 print:border-gray-400">
                <!-- Conclusion Section -->
                <div class="mb-6 bg-gradient-to-r from-gray-50 to-blue-50 border border-gray-200 rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        Report Conclusion
                    </h3>
                    <div class="prose max-w-none">
                        <p class="text-gray-800 leading-relaxed mb-4">
                            @if($log->level == 'error')
                                This comprehensive analysis of incident #{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }} has identified a critical system condition that requires immediate technical intervention. 
                                Our monitoring infrastructure successfully detected and escalated this issue, demonstrating the effectiveness of our proactive surveillance systems. 
                                
                                <br><br>The immediate response protocol outlined in this report should be executed without delay to minimize service impact and restore normal operations. 
                                Additionally, the strategic recommendations provide a roadmap for strengthening system resilience and preventing similar incidents in the future.
                                
                                <br><br>This incident serves as an important learning opportunity to enhance our infrastructure stability and operational procedures. 
                                Regular review and implementation of the suggested improvements will contribute to our overall system reliability and customer satisfaction.
                            @elseif($log->level == 'warning')
                                This detailed examination of warning event #{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }} has revealed a performance condition that, while not immediately critical, 
                                represents an important opportunity for system optimization and preventive maintenance. Our monitoring system's ability to detect this condition before 
                                it escalated demonstrates the value of comprehensive performance tracking.
                                
                                <br><br>The recommended actions outlined in this report provide a balanced approach to addressing the underlying condition while maintaining system stability. 
                                Implementing these suggestions within the specified timeframes will help prevent potential escalation and optimize overall system performance.
                                
                                <br><br>This proactive identification and analysis exemplifies best practices in infrastructure management, contributing to our continuous improvement 
                                objectives and operational excellence goals.
                            @else
                                This thorough documentation of informational event #{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }} contributes to our comprehensive operational 
                                awareness and audit trail maintenance. While no immediate action is required, this event provides valuable insights into normal system operations 
                                and helps establish baseline performance patterns.
                                
                                <br><br>The systematic logging and analysis of such events supports our data-driven approach to infrastructure management and capacity planning. 
                                This information will be valuable for trend analysis, compliance reporting, and strategic decision-making processes.
                                
                                <br><br>Continued monitoring and documentation of operational events ensures we maintain visibility into system health and performance characteristics, 
                                supporting our commitment to proactive infrastructure management and service reliability.
                            @endif
                        </p>
                        
                        <div class="bg-white border border-gray-200 rounded-lg p-4 mt-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h4 class="font-semibold text-gray-900 mb-1">Next Review Date:</h4>
                                    <p class="text-sm text-gray-600">
                                        @if($log->level == 'error')
                                            {{ now()->addDays(7)->format('F j, Y') }} (Weekly follow-up required)
                                        @elseif($log->level == 'warning')
                                            {{ now()->addDays(30)->format('F j, Y') }} (Monthly progress review)
                                        @else
                                            {{ now()->addDays(90)->format('F j, Y') }} (Quarterly trend analysis)
                                        @endif
                                    </p>
                                </div>
                                <div class="text-right">
                                    <h4 class="font-semibold text-gray-900 mb-1">Report Status:</h4>
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium 
                                        {{ $log->level == 'error' ? 'bg-red-100 text-red-800' : ($log->level == 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800') }}">
                                        {{ $log->level == 'error' ? 'Action Required' : ($log->level == 'warning' ? 'Monitor & Review' : 'Informational Complete') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>                <div class="flex justify-between items-center text-sm text-gray-600">
                    <div>
                        <p><strong>Report Classification:</strong> {{ $log->level == 'error' ? 'Critical' : ($log->level == 'warning' ? 'Standard' : 'Informational') }}</p>
                        <p><strong>Retention Period:</strong> 7 years as per compliance requirements</p>
                        <p><strong>Distribution:</strong> Infrastructure Team, Management, Compliance Archive</p>
                    </div>
                    <div class="text-right">
                        <p><strong>Generated by:</strong> ServerPulse Monitoring System v2.0</p>
                        <p><strong>Report ID:</strong> RPT-{{ date('Ymd') }}-{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }}</p>
                        <p><strong>Generated on:</strong> {{ now()->format('F j, Y \a\t g:i A T') }}</p>                    </div>
                </div>            </div>

            <!-- Incident Timeline -->
            <div id="incident-timeline" class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-6 flex items-center">
                    <i class="fas fa-clock text-blue-600 mr-3"></i>
                    Incident Timeline
                </h2>
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="space-y-4">
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-flag text-blue-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900">Incident Detected</h4>
                                    <span class="text-sm text-gray-500">{{ $log->created_at->format('M j, Y \a\t g:i:s A') }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">{{ ucfirst($log->level) }} level event detected on {{ $log->server->name ?? 'Unknown Server' }}</p>
                            </div>
                        </div>
                        
                        <div class="flex items-start space-x-4">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-file-alt text-green-600 text-sm"></i>
                                </div>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center justify-between">
                                    <h4 class="text-sm font-medium text-gray-900">Report Generated</h4>
                                    <span class="text-sm text-gray-500">{{ now()->format('M j, Y \a\t g:i:s A') }}</span>
                                </div>
                                <p class="text-sm text-gray-600 mt-1">Comprehensive analysis report created for incident #{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Section for Easy Access -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-8 print:hidden">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-semibold text-blue-900 mb-2">Need to take action?</h3>
                        <p class="text-blue-700 text-sm">Quickly navigate to related sections or download this report for offline analysis.</p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('logs.index') }}" 
                           class="flex items-center px-4 py-2 text-sm font-medium text-blue-600 bg-white border border-blue-300 rounded-lg hover:bg-blue-50 transition-colors">
                            <i class="fas fa-table mr-2"></i>
                            View All Logs
                        </a>
                        <a href="{{ route('logs.show', $log) }}" 
                           class="flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                            <i class="fas fa-cog mr-2"></i>
                            Manage This Log                        </a>
                    </div>
                </div>
            </div>                    </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

<style>
@media print {
    body { 
        background: white !important; 
        -webkit-print-color-adjust: exact;
        color-adjust: exact;
    }
    .print\\:hidden { display: none !important; }
    .print\\:shadow-none { box-shadow: none !important; }
    .print\\:border-none { border: none !important; }
    .print\\:border-gray-400 { border-color: #9ca3af !important; }
    
    /* Ensure colors print correctly */
    .bg-red-500, .bg-red-600 { background-color: #ef4444 !important; }
    .bg-yellow-500, .bg-yellow-600 { background-color: #eab308 !important; }
    .bg-blue-500, .bg-blue-600 { background-color: #3b82f6 !important; }
    .bg-green-500, .bg-green-600 { background-color: #22c55e !important; }
    
    /* Page breaks */
    .mb-8 { page-break-inside: avoid; }    h2 { page-break-after: avoid; }
}

/* Google Docs-style Layout */
body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Smooth scroll behavior */
html {
    scroll-behavior: smooth;
}

/* Navigation Styles */
.nav-link.active {
    background-color: #dbeafe;
    color: #1d4ed8;
    border-left: 3px solid #3b82f6;
    padding-left: 9px; /* Adjust for border */
}

.nav-link.active i {
    color: #3b82f6 !important;
}

/* Document container styling */
.bg-white {
    background-color: #ffffff;
}

/* Enhanced sidebar styling */
aside {
    backdrop-filter: blur(8px);
    border-right: 1px solid #e5e7eb;
}

/* Document paper effect */
main .bg-white {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

/* Responsive design */
@media (max-width: 1024px) {
    aside {
        transform: translateX(-100%);
        transition: transform 0.3s ease-in-out;
    }
    
    aside.mobile-open {
        transform: translateX(0);
    }
    
    main {
        margin-left: 0 !important;
    }
}

/* Print styles */
@media print {
    aside {
        display: none !important;
    }
    
    main {
        margin-left: 0 !important;
    }
    
    .print\:shadow-none {
        box-shadow: none !important;
    }
    
    .print\:border-none {
        border: none !important;
    }
    
    .print\:p-6 {
        padding: 1.5rem !important;
    }
}
</style>

<!-- Back to Top Button -->
<button id="backToTop" 
        class="fixed bg-blue-600 text-white rounded-full p-3 hover:bg-blue-700 transition-all duration-300 opacity-0 pointer-events-none print:hidden shadow-lg"
        style="bottom: 30px; right: 30px; z-index: 999; transform: scale(0.8);"
        onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
        title="Back to Top">
    <i class="fas fa-chevron-up"></i>
</button>

<script>
// Enhanced Report Navigation JavaScript
document.addEventListener('DOMContentLoaded', function() {
    // Report Navigation Toggle
    const reportNavToggle = document.getElementById('report-nav-toggle');
    const reportNavContent = document.getElementById('report-nav-content');
    const reportNavArrow = document.getElementById('report-nav-arrow');
    
    // Load saved state from localStorage
    const isReportNavCollapsed = localStorage.getItem('report-nav-collapsed') === 'true';
    
    if (isReportNavCollapsed) {
        reportNavContent.style.maxHeight = '0px';
        reportNavArrow.style.transform = 'rotate(-90deg)';
    }
    
    if (reportNavToggle) {
        reportNavToggle.addEventListener('click', function() {
            const isCurrentlyCollapsed = reportNavContent.style.maxHeight === '0px';
            
            if (isCurrentlyCollapsed) {
                reportNavContent.style.maxHeight = reportNavContent.scrollHeight + 'px';
                reportNavArrow.style.transform = 'rotate(0deg)';
                localStorage.setItem('report-nav-collapsed', 'false');
            } else {
                reportNavContent.style.maxHeight = '0px';
                reportNavArrow.style.transform = 'rotate(-90deg)';
                localStorage.setItem('report-nav-collapsed', 'true');
            }
        });
    }    // Enhanced Smooth Scrolling for Report Navigation
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href').substring(1);
            const targetElement = document.getElementById(targetId);
            
            if (targetElement) {
                const navHeight = 120;
                const targetPosition = targetElement.offsetTop - navHeight;
                
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
                
                // Highlight active nav item
                document.querySelectorAll('.nav-link').forEach(l => {
                    l.classList.remove('active', 'bg-blue-50', 'text-blue-700');
                    l.classList.add('text-gray-700');
                });
                this.classList.remove('text-gray-700');
                this.classList.add('active', 'bg-blue-50', 'text-blue-700');
            }
        });
    });

    // Advanced Scroll Spy for Report
    let ticking = false;
    
    function updateActiveSection() {
        const sections = ['executive-summary', 'alert-details', 'system-metrics', 'log-message', 'context-data', 'related-events', 'recommendations', 'incident-timeline'];
        const navHeight = 100;
        
        sections.forEach(sectionId => {
            const section = document.getElementById(sectionId);
            if (section) {
                const rect = section.getBoundingClientRect();
                const navLink = document.querySelector(`a[href="#${sectionId}"]`);
                  if (rect.top <= navHeight && rect.bottom >= navHeight) {
                    document.querySelectorAll('.nav-link').forEach(l => {
                        l.classList.remove('active', 'bg-blue-50', 'text-blue-700');
                        l.classList.add('text-gray-700');
                    });
                    if (navLink) {
                        navLink.classList.remove('text-gray-700');
                        navLink.classList.add('active', 'bg-blue-50', 'text-blue-700');
                    }
                }
            }
        });
        ticking = false;
    }

    window.addEventListener('scroll', function() {
        if (!ticking) {
            requestAnimationFrame(updateActiveSection);
            ticking = true;
        }
    });

    // Show/hide back to top button based on scroll position
    const backToTop = document.getElementById('backToTop');
    if (backToTop) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTop.style.opacity = '1';
                backToTop.style.pointerEvents = 'auto';
                backToTop.style.transform = 'scale(1)';
            } else {
                backToTop.style.opacity = '0';
                backToTop.style.pointerEvents = 'none';
                backToTop.style.transform = 'scale(0.8)';
            }
        });
    }

    // Smooth scroll behavior for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Auto-collapse navigation on mobile after click
    if (window.innerWidth <= 1024) {
        document.querySelectorAll('.report-nav-link').forEach(link => {
            link.addEventListener('click', function() {
                setTimeout(() => {
                    if (reportNavContent && reportNavContent.style.maxHeight !== '0px') {
                        reportNavContent.style.maxHeight = '0px';
                        reportNavArrow.style.transform = 'rotate(-90deg)';
                        localStorage.setItem('report-nav-collapsed', 'true');
                    }
                }, 1000);
            });
        });
    }
});
</script>

@endsection
