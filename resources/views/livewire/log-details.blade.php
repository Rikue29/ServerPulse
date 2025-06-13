<div class="min-h-screen bg-gray-50 px-4 py-8">
    <div class="max-w-5xl mx-auto bg-white rounded-2xl shadow-lg p-8 space-y-8">

        <!-- Title -->
        <div>
            <h1 class="text-3xl font-semibold text-gray-900 mb-1">📄 Log Details</h1>
            <p class="text-sm text-gray-500">View technical insights, system context, and server metrics.</p>
        </div>

        <!-- Metrics -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            @foreach (['cpu_usage' => 'CPU', 'ram_usage' => 'Memory', 'disk_usage' => 'Disk'] as $key => $label)
                <div class="bg-gray-50 border border-gray-200 rounded-xl p-4 shadow-sm">
                    <div class="flex justify-between text-sm font-medium text-gray-500">
                        <span>{{ $label }}</span>
                        <span class="{{ $log->context[$key] ?? 0 > 80 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $log->context[$key] ?? 'N/A' }}%
                        </span>
                    </div>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="h-2 rounded-full bg-indigo-500" style="width: {{ $log->context[$key] ?? 0 }}%"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Log Metadata -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <h2 class="text-xs text-gray-500 uppercase tracking-wider">Timestamp</h2>
                <p class="text-sm mt-1 font-mono">{{ $log->created_at?->format('Y-m-d H:i:s') ?? 'N/A' }}</p>
            </div>
            <div>
                <h2 class="text-xs text-gray-500 uppercase tracking-wider">Level</h2>
                <p class="mt-1">
                    @if($log->level === 'error')
                        <span class="inline-flex items-center text-xs px-3 py-1 rounded-full bg-red-100 text-red-800">
                            ❌ Error
                        </span>
                    @elseif($log->level === 'warning')
                        <span class="inline-flex items-center text-xs px-3 py-1 rounded-full bg-yellow-100 text-yellow-800">
                            ⚠️ Warning
                        </span>
                    @else
                        <span class="inline-flex items-center text-xs px-3 py-1 rounded-full bg-blue-100 text-blue-800">
                            ℹ️ {{ ucfirst($log->level) }}
                        </span>
                    @endif
                </p>
            </div>
            <div>
                <h2 class="text-xs text-gray-500 uppercase tracking-wider">Server</h2>
                <p class="mt-1 text-sm font-medium text-gray-800">{{ $log->server->name ?? 'Unknown' }}</p>
                <p class="text-xs text-gray-500">{{ $log->server->ip_address ?? 'No IP' }}</p>
            </div>
            <div>
                <h2 class="text-xs text-gray-500 uppercase tracking-wider">Source</h2>
                <p class="mt-1 text-sm text-gray-800">{{ $log->source ?? 'System' }}</p>
            </div>
        </div>

        <!-- Log Message -->
        <div>
            <h2 class="text-xs text-gray-500 uppercase tracking-wider mb-1">Message</h2>
            <div class="bg-blue-50 border-l-4 border-blue-500 rounded p-4 text-sm text-gray-800 leading-relaxed">
                {{ $log->message ?? 'No message available' }}
            </div>
        </div>

        <!-- Context -->
        @if($log->context)
            <div>
                <h2 class="text-xs text-gray-500 uppercase tracking-wider mb-1">Context</h2>
                <pre class="bg-gray-900 text-green-300 text-xs p-4 rounded-xl overflow-x-auto font-mono">
{{ json_encode($log->context, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}
                </pre>
            </div>
        @endif

        <!-- Tips -->
        @if(in_array($log->level, ['error', 'critical']))
            <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded-xl">
                <h3 class="text-sm font-semibold text-red-700 mb-2">🔧 Troubleshooting Tips</h3>
                <ul class="list-disc list-inside text-xs text-red-600 space-y-1">
                    <li>Check CPU/memory usage on the server</li>
                    <li>Validate recent changes or deployments</li>
                    <li>Review stack trace or context data</li>
                </ul>
            </div>
        @endif

        <!-- Actions -->
        <div class="flex justify-between items-center border-t pt-6">
            <a href="{{ route('logs.index') }}" class="text-sm text-gray-600 hover:text-gray-800 inline-flex items-center">
                ← Back to logs
            </a>
            <div class="flex space-x-2">
                <button wire:click="$dispatch('print-log')" class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm hover:bg-blue-700">
                    Print
                </button>
                <button wire:click="copyToClipboard" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">
                    Copy JSON
                </button>
            </div>
        </div>
    </div>

    <script>
    window.addEventListener('copy-to-clipboard', event => {
        navigator.clipboard.writeText(event.detail.content)
            .then(() => alert('Copied to clipboard!'))
            .catch(() => alert('Failed to copy'));
    });
    </script>

    <!-- Script to handle browser printing -->
    <script>
        document.addEventListener('print-log', () => window.print());
    </script>
</div>
