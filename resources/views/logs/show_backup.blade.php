@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900 mb-2">Log Details</h1>
                    <p class="text-gray-600">Log Entry #{{ $log->id }}</p>
                </div>
                <div class="flex space-x-2">
                    <a href="{{ route('logs.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left mr-2"></i>Back to Logs
                    </a>
                    <button onclick="window.print()" class="btn btn-primary">
                        <i class="fas fa-print mr-2"></i>Print
                    </button>
                    <a href="{{ route('logs.download', $log) }}" class="btn btn-primary">
                        <i class="fas fa-download mr-2"></i>Download PDF
                    </a>
                </div>
            </div>
        </div>

        <!-- Log Information -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Log Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-500">Timestamp</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $log->created_at }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Level</label>
                    <p class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                            @if($log->level === 'error' || $log->level === 'critical') bg-red-100 text-red-800
                            @elseif($log->level === 'warning') bg-yellow-100 text-yellow-800
                            @else bg-blue-100 text-blue-800 @endif">
                            {{ ucfirst($log->level) }}
                        </span>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Server</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $log->server->name ?? 'Unknown' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-500">Source</label>
                    <p class="mt-1 text-sm text-gray-900">{{ $log->source ?? 'System' }}</p>
                </div>
            </div>
        </div>

        <!-- Message -->
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Message</h2>
            <div class="bg-gray-50 rounded-md p-4">
                <pre class="text-sm text-gray-900 whitespace-pre-wrap">{{ $log->message }}</pre>
            </div>
        </div>

        <!-- Server Metrics (if available) -->
        @if($log->context)
        @php
            $context = is_string($log->context) ? json_decode($log->context, true) : $log->context;
            $metrics = $context['all_metrics'] ?? [];
        @endphp

        @if(!empty($metrics))
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Server Metrics</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                @if(isset($metrics['cpu_usage']))
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($metrics['cpu_usage'], 1) }}%</div>
                    <div class="text-sm text-gray-500">CPU Usage</div>
                </div>
                @endif
                
                @if(isset($metrics['ram_usage']))
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($metrics['ram_usage'], 1) }}%</div>
                    <div class="text-sm text-gray-500">Memory Usage</div>
                </div>
                @endif
                
                @if(isset($metrics['disk_usage']))
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ number_format($metrics['disk_usage'], 1) }}%</div>
                    <div class="text-sm text-gray-500">Disk Usage</div>
                </div>
                @endif
            </div>
        </div>
        @endif
        @endif

        <!-- Context Data -->
        @if($log->context)
        <div class="bg-white rounded-lg shadow-md p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Context Data</h2>
            <div class="bg-gray-900 rounded-md p-4">
                <pre class="text-green-400 text-xs overflow-auto"><code>{{ is_array($log->context) ? json_encode($log->context, JSON_PRETTY_PRINT) : $log->context }}</code></pre>
            </div>
        </div>
        @endif

        <!-- Actions -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Actions</h2>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('logs.report', $log) }}" class="btn btn-outline-primary">
                    <i class="fas fa-file-alt mr-2"></i>View Report
                </a>
                <button onclick="copyToClipboard()" class="btn btn-outline-secondary">
                    <i class="fas fa-copy mr-2"></i>Copy Log Data
                </button>
                <button onclick="window.print()" class="btn btn-outline-secondary">
                    <i class="fas fa-print mr-2"></i>Print
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function copyToClipboard() {
    const logData = {
        id: {{ $log->id }},
        timestamp: '{{ $log->created_at }}',
        level: '{{ $log->level }}',
        server: '{{ $log->server->name ?? "Unknown" }}',
        message: {{ json_encode($log->message) }},
        @if($log->context)
        context: @json($log->context)
        @endif
    };
    
    navigator.clipboard.writeText(JSON.stringify(logData, null, 2)).then(function() {
        alert('Log data copied to clipboard!');
    });
}
</script>

<style>
.btn {
    @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2;
}

.btn-primary {
    @apply text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500;
}

.btn-secondary {
    @apply text-gray-700 bg-gray-200 hover:bg-gray-300 focus:ring-gray-500;
}

.btn-outline-primary {
    @apply text-blue-600 bg-white border-blue-600 hover:bg-blue-50 focus:ring-blue-500;
}

.btn-outline-secondary {
    @apply text-gray-600 bg-white border-gray-300 hover:bg-gray-50 focus:ring-gray-500;
}
</style>
@endsection
