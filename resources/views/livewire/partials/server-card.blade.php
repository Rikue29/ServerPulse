<div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
    <!-- Server Header -->
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center space-x-3">
            <div class="w-3 h-3 rounded-full {{ $server->status === 'online' ? 'bg-green-500' : ($server->status === 'maintenance' ? 'bg-yellow-500' : 'bg-red-500') }}"></div>
            <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $server->name }}</h3>
                <p class="text-sm text-gray-500">{{ $server->ip_address }}</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <span class="text-xs px-2 py-1 rounded-full {{ $server->status === 'online' ? 'bg-green-100 text-green-800' : ($server->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">
                {{ ucfirst($server->status) }}
            </span>
            <button class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-ellipsis-v"></i>
            </button>
        </div>
    </div>

    <!-- Server Metrics -->
    <div class="space-y-4">
        <!-- Uptime -->
        <div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Uptime</span>
                <span class="text-sm text-gray-500">{{ $server->uptime ?? 'N/A' }}</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                @php
                    $uptimePercentage = $server->uptime_percentage ?? 95;
                    $uptimeColor = $uptimePercentage >= 99 ? 'bg-green-500' : ($uptimePercentage >= 95 ? 'bg-yellow-500' : 'bg-red-500');
                @endphp
                <div class="{{ $uptimeColor }} h-2 rounded-full" style="width: {{ $uptimePercentage }}%"></div>
            </div>
        </div>

        <!-- CPU Usage -->
        <div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">CPU Usage</span>
                <span class="text-sm text-gray-500">{{ $server->cpu_usage ?? 0 }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                @php
                    $cpuUsage = $server->cpu_usage ?? 0;
                    $cpuColor = $cpuUsage < 70 ? 'bg-green-500' : ($cpuUsage < 90 ? 'bg-yellow-500' : 'bg-red-500');
                @endphp
                <div class="{{ $cpuColor }} h-2 rounded-full" style="width: {{ $cpuUsage }}%"></div>
            </div>
        </div>

        <!-- Memory Usage -->
        <div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Memory Usage</span>
                <span class="text-sm text-gray-500">{{ $server->ram_usage ?? 0 }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                @php
                    $ramUsage = $server->ram_usage ?? 0;
                    $ramColor = $ramUsage < 70 ? 'bg-green-500' : ($ramUsage < 90 ? 'bg-yellow-500' : 'bg-red-500');
                @endphp
                <div class="{{ $ramColor }} h-2 rounded-full" style="width: {{ $ramUsage }}%"></div>
            </div>
        </div>

        <!-- Disk Usage -->
        <div>
            <div class="flex justify-between items-center mb-2">
                <span class="text-sm font-medium text-gray-700">Disk Usage</span>
                <span class="text-sm text-gray-500">{{ $server->disk_usage ?? 0 }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
                @php
                    $diskUsage = $server->disk_usage ?? 0;
                    $diskColor = $diskUsage < 70 ? 'bg-green-500' : ($diskUsage < 90 ? 'bg-yellow-500' : 'bg-red-500');
                @endphp
                <div class="{{ $diskColor }} h-2 rounded-full" style="width: {{ $diskUsage }}%"></div>
            </div>
        </div>
    </div>

    <!-- Additional Info -->
    <div class="mt-4 pt-4 border-t border-gray-200">
        <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
                <span class="text-gray-500">Last Check:</span>
                <span class="text-gray-900 ml-1">{{ $server->last_checked_at ? $server->last_checked_at->diffForHumans() : 'Never' }}</span>
            </div>
            <div>
                <span class="text-gray-500">Location:</span>
                <span class="text-gray-900 ml-1">{{ $server->location ?? 'Unknown' }}</span>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-4 flex space-x-2">
        <button class="flex-1 px-3 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200">
            <i class="fas fa-eye mr-1"></i>View Details
        </button>
        <button class="px-3 py-2 text-sm bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200 transition-colors duration-200">
            <i class="fas fa-cog"></i>
        </button>
    </div>
</div> 