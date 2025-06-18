@extends('layouts.app')

@section('title', 'Log Details - #' . $log->id)

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header Section -->
    <div class="bg-white shadow-sm border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center space-x-4">
                    <div class="flex-shrink-0">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-file-alt text-blue-600 text-xl"></i>
                        </div>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Log Details</h1>
                        <nav class="flex mt-1" aria-label="Breadcrumb">
                            <ol class="flex items-center space-x-2 text-sm text-gray-500">
                                <li><a href="{{ route('logs.index') }}" class="hover:text-gray-700 transition-colors">Logs</a></li>
                                <li><i class="fas fa-chevron-right text-xs"></i></li>
                                <li class="text-gray-900 font-medium">Log #{{ $log->id }}</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                <div class="mt-4 sm:mt-0 flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('logs.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm bg-white text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Back to Logs
                    </a>
                    <a href="{{ route('logs.report', $log) }}" 
                       class="inline-flex items-center px-4 py-2 border border-emerald-300 rounded-md shadow-sm bg-emerald-50 text-emerald-700 hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-chart-line mr-2"></i>
                        View Report
                    </a>
                    <a href="{{ route('logs.download', $log) }}" 
                       class="inline-flex items-center px-4 py-2 border border-blue-300 rounded-md shadow-sm bg-blue-600 text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                        <i class="fas fa-download mr-2"></i>
                        Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Alert Banner -->
        <div class="mb-8">
            <div class="rounded-lg border-l-4 p-4 
                @if($log->level === 'error' || $log->level === 'critical') 
                    bg-red-50 border-red-400
                @elseif($log->level === 'warning') 
                    bg-yellow-50 border-yellow-400
                @else 
                    bg-blue-50 border-blue-400
                @endif">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        @if($log->level === 'error' || $log->level === 'critical')
                            <i class="fas fa-exclamation-triangle text-red-400 text-lg"></i>
                        @elseif($log->level === 'warning')
                            <i class="fas fa-exclamation-circle text-yellow-400 text-lg"></i>
                        @else
                            <i class="fas fa-info-circle text-blue-400 text-lg"></i>
                        @endif
                    </div>
                    <div class="ml-3 flex-1">
                        <h3 class="text-lg font-medium 
                            @if($log->level === 'error' || $log->level === 'critical') 
                                text-red-800
                            @elseif($log->level === 'warning') 
                                text-yellow-800
                            @else 
                                text-blue-800
                            @endif">
                            {{ ucfirst($log->level) }} Level Log Entry
                        </h3>
                        <div class="mt-1 text-sm 
                            @if($log->level === 'error' || $log->level === 'critical') 
                                text-red-700
                            @elseif($log->level === 'warning') 
                                text-yellow-700
                            @else 
                                text-blue-700
                            @endif">
                            <p class="flex flex-wrap items-center gap-4">
                                <span><strong>ID:</strong> #{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }}</span>
                                <span><strong>Server:</strong> {{ $log->server->name ?? 'Unknown' }}</span>
                                <span><strong>Time:</strong> {{ $log->created_at->format('M j, Y \a\t g:i A') }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">            <!-- Log Information Card -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-info-circle text-blue-500 mr-2"></i>
                        Log Information
                    </h2>
                </div>
                <div class="px-6 py-4 space-y-6">
                    <!-- Primary Log Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-4 border border-blue-200">
                            <dt class="text-sm font-medium text-blue-700 mb-2 flex items-center">
                                <i class="fas fa-hashtag text-blue-500 mr-2"></i>
                                Entry ID
                            </dt>
                            <dd class="text-2xl font-bold text-blue-900">#{{ str_pad($log->id, 6, '0', STR_PAD_LEFT) }}</dd>
                        </div>
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <dt class="text-sm font-medium text-gray-600 mb-2 flex items-center">
                                <i class="fas fa-layer-group text-gray-500 mr-2"></i>
                                Severity Level
                            </dt>
                            <dd>
                                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-sm font-semibold
                                    @if($log->level === 'error' || $log->level === 'critical') 
                                        bg-red-100 text-red-800 border border-red-200
                                    @elseif($log->level === 'warning') 
                                        bg-yellow-100 text-yellow-800 border border-yellow-200
                                    @else 
                                        bg-green-100 text-green-800 border border-green-200
                                    @endif">
                                    @if($log->level === 'error' || $log->level === 'critical')
                                        <i class="fas fa-exclamation-triangle mr-1"></i>
                                    @elseif($log->level === 'warning')
                                        <i class="fas fa-exclamation-circle mr-1"></i>
                                    @else
                                        <i class="fas fa-check-circle mr-1"></i>
                                    @endif
                                    {{ ucfirst($log->level) }}
                                </span>
                            </dd>
                        </div>
                    </div>

                    <!-- Timestamp Details -->
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200">
                        <dt class="text-sm font-medium text-gray-600 mb-3 flex items-center">
                            <i class="fas fa-clock text-gray-500 mr-2"></i>
                            Timestamp Information
                        </dt>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div>
                                <dd class="text-lg font-semibold text-gray-900">{{ $log->created_at->format('M j, Y') }}</dd>
                                <dd class="text-xs text-gray-600">Date</dd>
                            </div>
                            <div>
                                <dd class="text-lg font-semibold text-gray-900">{{ $log->created_at->format('g:i:s A') }}</dd>
                                <dd class="text-xs text-gray-600">Time</dd>
                            </div>
                            <div>
                                <dd class="text-lg font-semibold text-gray-900">{{ $log->created_at->diffForHumans() }}</dd>
                                <dd class="text-xs text-gray-600">Relative Time</dd>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Details -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <dt class="text-sm font-medium text-gray-600 mb-2 flex items-center">
                                <i class="fas fa-code text-gray-500 mr-2"></i>
                                Log Source
                            </dt>
                            <dd class="text-base font-semibold text-gray-900">{{ $log->source ?? 'System Generated' }}</dd>
                        </div>
                        @if($log->server)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                            <dt class="text-sm font-medium text-gray-600 mb-2 flex items-center">
                                <i class="fas fa-server text-gray-500 mr-2"></i>
                                Server Status
                            </dt>
                            <dd>
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                    @if($log->server->status === 'online') 
                                        bg-green-100 text-green-800
                                    @elseif($log->server->status === 'offline') 
                                        bg-red-100 text-red-800
                                    @else 
                                        bg-yellow-100 text-yellow-800
                                    @endif">
                                    <span class="w-2 h-2 rounded-full mr-2
                                        @if($log->server->status === 'online') 
                                            bg-green-500
                                        @elseif($log->server->status === 'offline') 
                                            bg-red-500
                                        @else 
                                            bg-yellow-500
                                        @endif"></span>
                                    {{ ucfirst($log->server->status ?? 'Unknown') }}
                                </span>
                            </dd>
                        </div>
                        @endif
                    </div>
                </div>
            </div><!-- Server Information Card -->
            <div class="bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-server text-green-500 mr-2"></i>
                        Server Information
                    </h2>
                </div>
                <div class="px-6 py-4">
                    @if($log->server)
                    <!-- Server Header -->
                    <div class="flex items-center space-x-4 mb-6 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg">
                        <div class="flex-shrink-0">
                            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-blue-600 rounded-lg flex items-center justify-center shadow-md">
                                <i class="fas fa-server text-white text-xl"></i>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <h3 class="text-xl font-bold text-gray-900 truncate">{{ $log->server->name }}</h3>
                            @if($log->server->ip_address)
                            <p class="text-sm text-gray-600 mt-1 flex items-center">
                                <i class="fas fa-network-wired mr-2 text-gray-400"></i>
                                <span class="font-mono">{{ $log->server->ip_address }}</span>
                            </p>
                            @endif
                            <div class="mt-2 flex items-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($log->server->status === 'online') 
                                        bg-green-100 text-green-800
                                    @elseif($log->server->status === 'offline') 
                                        bg-red-100 text-red-800
                                    @else 
                                        bg-yellow-100 text-yellow-800
                                    @endif">
                                    <span class="w-1.5 h-1.5 rounded-full mr-1.5
                                        @if($log->server->status === 'online') 
                                            bg-green-400
                                        @elseif($log->server->status === 'offline') 
                                            bg-red-400
                                        @else 
                                            bg-yellow-400
                                        @endif"></span>
                                    {{ ucfirst($log->server->status ?? 'Unknown') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Server Details Grid -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <dt class="text-sm font-medium text-gray-500 flex items-center">
                                    <i class="fas fa-tag text-gray-400 mr-2"></i>
                                    Server ID
                                </dt>
                            </div>
                            <dd class="text-lg font-semibold text-gray-900">#{{ str_pad($log->server->id, 4, '0', STR_PAD_LEFT) }}</dd>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <dt class="text-sm font-medium text-gray-500 flex items-center">
                                    <i class="fas fa-heartbeat text-gray-400 mr-2"></i>
                                    Health Status
                                </dt>
                            </div>
                            <dd class="text-lg font-semibold 
                                @if($log->server->status === 'online') 
                                    text-green-600
                                @elseif($log->server->status === 'offline') 
                                    text-red-600
                                @else 
                                    text-yellow-600
                                @endif">
                                {{ ucfirst($log->server->status ?? 'Unknown') }}
                            </dd>
                        </div>

                        @if($log->server->created_at)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <dt class="text-sm font-medium text-gray-500 flex items-center">
                                    <i class="fas fa-plus-circle text-gray-400 mr-2"></i>
                                    Added
                                </dt>
                            </div>
                            <dd class="text-sm font-semibold text-gray-900">{{ $log->server->created_at->format('M j, Y') }}</dd>
                            <dd class="text-xs text-gray-600">{{ $log->server->created_at->diffForHumans() }}</dd>
                        </div>
                        @endif

                        @if($log->server->updated_at)
                        <div class="bg-gray-50 rounded-lg p-4 border border-gray-100">
                            <div class="flex items-center justify-between mb-2">
                                <dt class="text-sm font-medium text-gray-500 flex items-center">
                                    <i class="fas fa-sync-alt text-gray-400 mr-2"></i>
                                    Last Updated
                                </dt>
                            </div>
                            <dd class="text-sm font-semibold text-gray-900">{{ $log->server->updated_at->format('M j, Y') }}</dd>
                            <dd class="text-xs text-gray-600">{{ $log->server->updated_at->diffForHumans() }}</dd>
                        </div>
                        @endif
                    </div>                    <!-- Additional Server Actions -->
                    <div class="mt-6 pt-4 border-t border-gray-200">
                        <div class="flex flex-wrap gap-2">
                            @if(Route::has('servers.show'))
                            <a href="{{ route('servers.show', $log->server) }}" 
                               class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <i class="fas fa-external-link-alt mr-1.5 text-xs"></i>
                                View Server Details
                            </a>
                            @endif
                            <a href="{{ route('logs.index', ['server' => $log->server->id]) }}" 
                               class="inline-flex items-center px-3 py-1.5 border border-blue-300 shadow-sm text-sm font-medium rounded-md text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors">
                                <i class="fas fa-list mr-1.5 text-xs"></i>
                                All Logs from Server
                            </a>
                        </div>
                    </div>
                    @else
                    <!-- No Server Information -->
                    <div class="text-center py-8">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-server text-gray-400 text-2xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Server Information</h3>
                        <p class="text-sm text-gray-500">Server details are not available for this log entry.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Log Message Card -->
        <div class="mt-8 bg-white shadow-sm rounded-lg border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                    <i class="fas fa-envelope text-yellow-500 mr-2"></i>
                    Log Message
                </h2>
            </div>
            <div class="px-6 py-4">
                <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                    <pre class="text-green-400 text-sm font-mono whitespace-pre-wrap">{{ $log->message }}</pre>
                </div>
            </div>
        </div>

        @if($log->context)
            @php
                $context = is_string($log->context) ? json_decode($log->context, true) : $log->context;
                $metrics = $context['all_metrics'] ?? [];
            @endphp

            @if(!empty($metrics))
            <!-- Server Metrics Card -->
            <div class="mt-8 bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-chart-line text-indigo-500 mr-2"></i>
                        Server Metrics
                    </h2>
                </div>                <div class="px-6 py-4">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        @if(isset($metrics['cpu_usage']))
                        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg p-6 text-center">
                            <div class="w-12 h-12 bg-blue-500 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-microchip text-white"></i>
                            </div>
                            <p class="text-2xl font-bold text-blue-600 mb-1">{{ number_format($metrics['cpu_usage'], 1) }}%</p>
                            <p class="text-sm text-gray-600 mb-3">CPU Usage</p>
                            <div class="w-full bg-blue-200 rounded-full h-2">
                                <div class="bg-blue-500 h-2 rounded-full transition-all duration-300 metric-bar" style="width: {{ $metrics['cpu_usage'] }}%"></div>
                            </div>
                        </div>
                        @endif
                        
                        @if(isset($metrics['ram_usage']))
                        <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-lg p-6 text-center">
                            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-memory text-white"></i>
                            </div>
                            <p class="text-2xl font-bold text-green-600 mb-1">{{ number_format($metrics['ram_usage'], 1) }}%</p>
                            <p class="text-sm text-gray-600 mb-3">Memory Usage</p>
                            <div class="w-full bg-green-200 rounded-full h-2">
                                <div class="bg-green-500 h-2 rounded-full transition-all duration-300 metric-bar" style="width: {{ $metrics['ram_usage'] }}%"></div>
                            </div>                        </div>
                        @endif
                        
                        @if(isset($metrics['disk_usage']))
                        <div class="bg-gradient-to-br from-purple-50 to-purple-100 rounded-lg p-6 text-center">
                            <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-hard-drive text-white"></i>
                            </div>
                            <p class="text-2xl font-bold text-purple-600 mb-1">{{ number_format($metrics['disk_usage'], 1) }}%</p>
                            <p class="text-sm text-gray-600 mb-3">Disk Usage</p>
                            <div class="w-full bg-purple-200 rounded-full h-2">
                                <div class="bg-purple-500 h-2 rounded-full transition-all duration-300 metric-bar" style="width: {{ $metrics['disk_usage'] }}%"></div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            <!-- Context Data Card -->
            <div class="mt-8 bg-white shadow-sm rounded-lg border border-gray-200">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-code text-red-500 mr-2"></i>
                        Context Data
                    </h2>
                </div>
                <div class="px-6 py-4">
                    <div class="bg-gray-900 rounded-lg p-4 overflow-x-auto">
                        <pre class="text-green-400 text-sm font-mono"><code>{{ is_array($log->context) ? json_encode($log->context, JSON_PRETTY_PRINT) : $log->context }}</code></pre>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Back to Top Button -->
<button id="backToTop" 
        class="fixed bottom-6 right-6 bg-blue-600 text-white p-3 rounded-full shadow-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-300 opacity-0 pointer-events-none z-50"
        onclick="window.scrollTo({top: 0, behavior: 'smooth'})"
        title="Back to Top">
    <i class="fas fa-chevron-up"></i>
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide back to top button
    const backToTopButton = document.getElementById('backToTop');
    
    function toggleBackToTopButton() {
        if (window.pageYOffset > 300) {
            backToTopButton.classList.remove('opacity-0', 'pointer-events-none');
        } else {
            backToTopButton.classList.add('opacity-0', 'pointer-events-none');
        }
    }
    
    window.addEventListener('scroll', toggleBackToTopButton);
    toggleBackToTopButton();

    // Add smooth scroll to anchor links
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

    // Add copy functionality to code blocks
    const codeBlocks = document.querySelectorAll('pre');
    codeBlocks.forEach(block => {
        // Create copy button
        const copyButton = document.createElement('button');
        copyButton.innerHTML = '<i class="fas fa-copy"></i>';
        copyButton.className = 'absolute top-2 right-2 p-2 text-gray-400 hover:text-gray-200 transition-colors duration-200 opacity-0 group-hover:opacity-100';
        copyButton.title = 'Copy to clipboard';
        
        // Make the pre block relative and add group class for hover
        block.style.position = 'relative';
        block.classList.add('group');
        block.appendChild(copyButton);
        
        // Add copy functionality
        copyButton.addEventListener('click', async function() {
            const text = block.textContent;
            try {
                await navigator.clipboard.writeText(text);
                copyButton.innerHTML = '<i class="fas fa-check"></i>';
                copyButton.className = copyButton.className.replace('text-gray-400', 'text-green-400');
                setTimeout(() => {
                    copyButton.innerHTML = '<i class="fas fa-copy"></i>';
                    copyButton.className = copyButton.className.replace('text-green-400', 'text-gray-400');
                }, 2000);
            } catch (err) {
                console.error('Failed to copy text: ', err);
            }
        });
    });

    // Add progress indicators for metric bars
    const metricBars = document.querySelectorAll('[style*="width:"]');
    metricBars.forEach(bar => {
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.transition = 'width 1s ease-in-out';
                    observer.unobserve(entry.target);
                }
            });
        });
        observer.observe(bar);
    });

    // Auto-refresh functionality (optional)
    let autoRefreshInterval;
    const enableAutoRefresh = false; // Set to true to enable auto-refresh
    
    if (enableAutoRefresh) {
        autoRefreshInterval = setInterval(() => {
            // Check if the page is still visible
            if (!document.hidden) {
                window.location.reload();
            }
        }, 30000); // Refresh every 30 seconds
    }

    // Cleanup on page unload
    window.addEventListener('beforeunload', function() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    });
});
</script>

<style>
@media print {
    #backToTop {
        display: none !important;
    }
    
    .bg-gray-50 {
        background-color: white !important;
    }
    
    .shadow-sm,
    .shadow-lg {
        box-shadow: none !important;
    }
    
    .border-gray-200,
    .border-gray-100 {
        border-color: #d1d5db !important;
    }
    
    .bg-gradient-to-br,
    .bg-gradient-to-r {
        background: white !important;
    }
    
    .text-white {
        color: black !important;
    }
    
    .bg-gray-900 {
        background: white !important;
        border: 1px solid #d1d5db !important;
    }
    
    .text-green-400 {
        color: black !important;
    }
}

/* Custom scrollbar for code blocks */
pre::-webkit-scrollbar {
    height: 8px;
}

pre::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
    border-radius: 4px;
}

pre::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
}

pre::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
}

/* Enhanced focus states for accessibility */
.focus\:ring-2:focus {
    ring-offset-width: 2px;
    ring-width: 2px;
}

/* Smooth transitions for interactive elements */
.transition-colors {
    transition-property: color, background-color, border-color;
    transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
    transition-duration: 150ms;
}

/* Loading animation for metric bars */
@keyframes slideIn {
    from {
        width: 0%;
    }
}

.metric-bar {
    animation: slideIn 1s ease-out;
}

/* Responsive text scaling */
@media (max-width: 640px) {
    .text-2xl {
        font-size: 1.5rem;
        line-height: 2rem;
    }
    
    .text-xl {
        font-size: 1.25rem;
        line-height: 1.75rem;
    }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
    .bg-gray-50 {
        background-color: white;
    }
    
    .text-gray-600,
    .text-gray-500 {
        color: #374151;
    }
    
    .border-gray-200 {
        border-color: #6b7280;
    }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
    .transition-all,
    .transition-colors {
        transition: none;
    }
    
    .metric-bar {
        animation: none;
    }
}
</style>
@endsection
