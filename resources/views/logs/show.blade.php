@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto py-10">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
            <div class="mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 mb-2">Log Details</h1>
                <p class="text-sm text-gray-500">Detailed information about this log entry</p>
            </div>

            <!-- Server Metrics Section -->
            <div class="mb-8">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Server Metrics</h2>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <!-- CPU Usage -->
                    <div class="bg-gray-50 shadow-sm rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500 uppercase">CPU</span>
                            <span class="text-sm font-semibold {{ $log->context && isset($log->context['cpu_usage']) && $log->context['cpu_usage'] > 80 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $log->context && isset($log->context['cpu_usage']) ? $log->context['cpu_usage'] . '%' : 'N/A' }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $log->context && isset($log->context['cpu_usage']) ? $log->context['cpu_usage'] . '%' : '0%' }}"></div>
                        </div>
                    </div>

                    <!-- Memory Usage -->
                    <div class="bg-gray-50 shadow-sm rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500 uppercase">Memory</span>
                            <span class="text-sm font-semibold {{ $log->context && isset($log->context['ram_usage']) && $log->context['ram_usage'] > 80 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $log->context && isset($log->context['ram_usage']) ? $log->context['ram_usage'] . '%' : 'N/A' }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-green-600 h-2 rounded-full" style="width: {{ $log->context && isset($log->context['ram_usage']) ? $log->context['ram_usage'] . '%' : '0%' }}"></div>
                        </div>
                    </div>

                    <!-- Disk Usage -->
                    <div class="bg-gray-50 shadow-sm rounded-lg border border-gray-200 p-4">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs font-medium text-gray-500 uppercase">Disk</span>
                            <span class="text-sm font-semibold {{ $log->context && isset($log->context['disk_usage']) && $log->context['disk_usage'] > 90 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $log->context && isset($log->context['disk_usage']) ? $log->context['disk_usage'] . '%' : 'N/A' }}
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-purple-600 h-2 rounded-full" style="width: {{ $log->context && isset($log->context['disk_usage']) ? $log->context['disk_usage'] . '%' : '0%' }}"></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Log Details Section -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Log Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</span>
                        <p class="font-mono text-sm text-gray-900 mt-1">
                            {{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A' }}
                        </p>
                    </div>

                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Level</span>
                        <div class="mt-1">
                            @if($log->level === 'error')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    Error
                                </span>
                            @elseif($log->level === 'warning')
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    <i class="fas fa-exclamation-circle mr-1"></i>
                                    Warning
                                </span>
                            @else
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    {{ ucfirst($log->level ?? 'Info') }}
                                </span>
                            @endif
                        </div>
                    </div>

                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Server</span>
                        <p class="text-sm text-gray-900 mt-1">{{ $log->server->name ?? 'Unknown Server' }}</p>
                        @if($log->server && $log->server->ip_address)
                            <p class="text-xs text-gray-500">{{ $log->server->ip_address }}</p>
                        @endif
                    </div>

                    <div>
                        <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Source</span>
                        <p class="text-sm text-gray-900 mt-1">{{ $log->source ?? 'System' }}</p>
                    </div>
                </div>
            </div>

            <!-- Message Section -->
            <div class="mb-6">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Message</span>
                <div class="mt-2 p-4 bg-gray-50 rounded-lg border-l-4 border-blue-500">
                    <p class="text-sm text-gray-900 leading-relaxed">{{ $log->message ?? 'No message available' }}</p>
                </div>
            </div>

            <!-- Context Section -->
            @if($log->context)
            <div class="mb-6">
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Context Data</span>
                <div class="mt-2">
                    <pre class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-auto text-xs font-mono">{{ is_array($log->context) ? json_encode($log->context, JSON_PRETTY_PRINT) : $log->context }}</pre>
                </div>
            </div>
            @endif

            <!-- Troubleshooting Tips -->
            @if(in_array($log->level, ['error', 'critical']))
            <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-lg">
                <h3 class="text-sm font-medium text-red-800 mb-2">
                    <i class="fas fa-exclamation-triangle mr-1"></i>
                    Troubleshooting Tips
                </h3>
                <ul class="list-disc list-inside text-xs text-red-700 space-y-1">
                    <li>Check the server status and resource usage</li>
                    <li>Review recent deployments or configuration changes</li>
                    <li>Consult the context data above for technical details</li>
                    <li>Escalate to your infrastructure team if the issue persists</li>
                </ul>
            </div>
            @endif

            <!-- Actions -->
            <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                <a href="{{ route('logs.index') }}" class="inline-flex items-center px-6 py-3 text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-blue-50 transition-colors duration-200 font-medium">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Logs
                </a>

                <div class="flex space-x-3">
                    <a href="{{ route('logs.report', $log) }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium">
                        <i class="fas fa-file-alt mr-2"></i>
                        View Report
                    </a>

                    <a href="{{ route('logs.download', $log) }}" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium">
                        <i class="fas fa-file-pdf mr-2"></i>
                        Download PDF
                    </a>
                    
                    <button onclick="window.print()" class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 font-medium">
                        <i class="fas fa-print mr-2"></i>
                        Print
                    </button>

                    <button onclick="copyToClipboard()" class="inline-flex items-center px-6 py-3 text-blue-600 bg-white border border-blue-600 rounded-lg hover:bg-blue-50 transition-colors duration-200 font-medium">
                        <i class="fas fa-copy mr-2"></i>
                        Copy Data
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    const logData = {
        id: {{ $log->id }},
        timestamp: '{{ $log->created_at ? $log->created_at->format('Y-m-d H:i:s') : 'N/A' }}',
        level: '{{ $log->level }}',
        server: '{{ $log->server->name ?? 'Unknown' }}',
        message: '{{ addslashes($log->message ?? '') }}',
        @if($log->context)
        context: @json($log->context)
        @endif
    };

    navigator.clipboard.writeText(JSON.stringify(logData, null, 2)).then(() => {
        alert('Log data copied to clipboard!');
    }).catch(() => {
        alert('Failed to copy to clipboard');
    });
}
</script>
@endsection
