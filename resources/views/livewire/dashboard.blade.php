<div class="p-6" wire:poll.{{ $refreshInterval }}s>
    <!-- Page Header with Controls -->
    <div class="mb-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>
                <p class="mt-2 text-gray-600">System overview and monitoring status</p>
            </div>
            
            <!-- Dashboard Controls -->
            <div class="mt-4 sm:mt-0 flex flex-wrap gap-3">
                <!-- Server Selector -->
                <select wire:model.live="selectedServerId" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    @foreach($servers as $server)
                        <option value="{{ $server->id }}" {{ $selectedServerId == $server->id ? 'selected' : '' }}>
                            {{ $server->name }} ({{ $server->ip_address }})
                        </option>
                    @endforeach
                </select>
                
                <!-- Time Range Selector -->
                <select wire:model.live="selectedTimeRange" class="px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-blue-500 focus:border-blue-500">
                    <option value="day">Last 24 Hours</option>
                    <option value="week">Last Week</option>
                    <option value="month">Last Month</option>
                </select>
                
                <!-- Refresh Button -->
                <button wire:click="refreshData" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors duration-200 text-sm">
                    <i class="fas fa-sync-alt mr-2"></i>Refresh
                </button>
                
            </div>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Servers -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-server text-blue-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Servers</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($totalServers) }}</p>
                </div>
            </div>
        </div>

        <!-- Active Servers -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active Servers</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($activeServers) }}</p>
                </div>
            </div>
        </div>

        <!-- Offline Servers -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow duration-200">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-red-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-times-circle text-red-600"></i>
                    </div>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Offline Servers</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($offlineServers) }}</p>
                </div>
            </div>
        </div>

    </div>


    <!-- Server Performance Chart -->
    @if($showCharts && !empty($selectedServers))
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        System Health: 
                        @if(count($selectedServers) === 1)
                            @php
                                $selectedServer = $this->selectedServer;
                            @endphp
                            {{ $selectedServer ? $selectedServer->name : 'Selected Server' }}
                        @elseif(count($selectedServers) === $servers->count())
                            All Servers (Average)
                        @else
                            {{ count($selectedServers) }} Selected Servers (Average)
                        @endif
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        @if(count($selectedServers) > 1)
                            Average 
                        @endif
                        CPU and Memory Usage - {{ ucfirst($selectedTimeRange) }} View
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    @if(count($selectedServers) === 1)
                        @php
                            $selectedServer = $this->selectedServer;
                        @endphp
                        @if($selectedServer)
                            <span class="text-xs px-2 py-1 rounded-full {{ $selectedServer->status === 'online' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($selectedServer->status) }}
                            </span>
                        @endif
                    @else
                        @php
                            $selectedServerList = $this->selectedServersList;
                            $onlineCount = $selectedServerList->where('status', 'online')->count();
                            $totalCount = $selectedServerList->count();
                            $statusColor = $onlineCount === $totalCount ? 'bg-green-100 text-green-800' : ($onlineCount > $totalCount / 2 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
                            $statusText = $onlineCount === $totalCount ? 'All Online' : ($onlineCount . '/' . $totalCount . ' Online');
                        @endphp
                        <span class="text-xs px-2 py-1 rounded-full {{ $statusColor }}">
                            {{ $statusText }}
                        </span>
                    @endif
                    <button onclick="refreshChart()" class="px-2 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200">
                        Refresh Chart
                    </button>
                    <button wire:click="refreshChartData" class="px-2 py-1 text-xs bg-blue-100 text-blue-600 rounded hover:bg-blue-200">
                        Force Refresh
                    </button>
                    <button wire:click="regenerateChartData" class="px-2 py-1 text-xs bg-green-100 text-green-600 rounded hover:bg-green-200">
                        Regenerate Data
                    </button>
                </div>
            </div>
        </div>
        <div class="p-6">
            <!-- Chart temporarily disabled for testing -->
            <div class="text-center p-8">
                <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
                <p class="text-gray-500">Chart functionality temporarily disabled for server selection testing.</p>
            </div>
        </div>
    </div>
    @elseif($servers->count() > 0)
    <!-- Fallback Chart Section -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
        <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900">
                        Server Performance Chart
                    </h3>
                    <p class="text-sm text-gray-600 mt-1">
                        Select a server and enable charts to view performance data
                    </p>
                </div>
                <div class="flex items-center space-x-2">
                    <button wire:click="toggleCharts" class="px-3 py-1 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">
                        Enable Charts
                    </button>
                </div>
            </div>
        </div>
        <div class="p-6 text-center">
            <i class="fas fa-chart-line text-gray-400 text-4xl mb-4"></i>
            <p class="text-gray-500">Charts are currently disabled. Click "Enable Charts" to view server performance data.</p>
        </div>
    </div>
    @else
    <!-- Debug Information - Remove this after testing -->
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-8">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-exclamation-triangle text-yellow-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-yellow-800">Chart Debug Info</h3>
                <div class="mt-2 text-sm text-yellow-700">
                    <p>showCharts: {{ $showCharts ? 'true' : 'false' }}</p>
                    <p>selectedServerId: {{ $selectedServerId ?? 'null' }}</p>
                    @php
                        $selectedServer = $this->selectedServer;
                    @endphp
                    <p>selectedServer: {{ $selectedServer ? $selectedServer->name : 'null' }}</p>
                    <p>Total servers: {{ $servers->count() }}</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Server List Section -->
    <div class="mt-8">        
        <!-- Filter Panel -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold text-gray-900">Server Filter</h3>
                <div class="flex items-center space-x-2">
                    <div class="text-sm text-gray-500">
                        {{ count($selectedServers) }} of {{ $servers->count() }} servers selected
                    </div>
                    <button wire:click="testMethod" class="px-3 py-1 text-xs bg-red-100 text-red-600 rounded hover:bg-red-200" onclick="console.log('Test button clicked!')">
                        Test Livewire
                    </button>
                    <button wire:click="verySimpleTest" class="px-3 py-1 text-xs bg-indigo-100 text-indigo-600 rounded hover:bg-indigo-200">
                        Very Simple Test
                    </button>
                    <button wire:click="selectAllServers" class="px-3 py-1 text-xs bg-blue-100 text-blue-600 rounded hover:bg-blue-200">
                        Select All
                    </button>
                    <button wire:click="clearServerSelection" class="px-3 py-1 text-xs bg-gray-100 text-gray-600 rounded hover:bg-gray-200">
                        Clear All
                    </button>
                    <button wire:click="testSelectedServersCount" class="px-3 py-1 text-xs bg-purple-100 text-purple-600 rounded hover:bg-purple-200">
                        Test Count
                    </button>
                    <button wire:click="forceUpdate" class="px-3 py-1 text-xs bg-orange-100 text-orange-600 rounded hover:bg-orange-200">
                        Force Update
                    </button>
                </div>
            </div>
            
            <!-- Vertical Filter Options -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
    @foreach($servers as $server)
    @php
        $isSelected = in_array((int) $server->id, $selectedServers);
    @endphp
    <div 
        class="flex items-center p-3 border border-gray-200 rounded-lg cursor-pointer hover:bg-gray-50 transition-colors duration-200 {{ $isSelected ? 'border-blue-500 bg-blue-50' : '' }}"
        wire:click="toggleServerSelection({{ $server->id }})"
    >
        <div class="w-4 h-4 border-2 rounded mr-3 {{ $isSelected ? 'bg-blue-500 border-blue-700' : 'border-gray-300' }}"></div>
        
        <div class="flex items-center space-x-2 flex-1">
            <div class="w-2 h-2 rounded-full {{ $server->status === 'online' ? 'bg-green-500' : ($server->status === 'maintenance' ? 'bg-yellow-500' : 'bg-red-500') }}"></div>
            <div class="flex-1">
                <div class="font-medium text-gray-900">{{ $server->name }}</div>
                <div class="text-sm text-gray-500">{{ $server->ip_address }}</div>
            </div>
        </div>
    </div>
    @endforeach
</div>

        </div>
        <div class="bg-yellow-100 p-2 text-sm">
    Selected Servers: {{ json_encode($selectedServers) }}<br>
    Selected List Count: {{ $selectedServersList->count() }}<br>
    Server Names: {{ $selectedServersList->pluck('name')->join(', ') }}
</div>


        <!-- Server Cards Display -->
        @if(count($selectedServers) > 0)
            
            <!-- Show Selected Servers -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($selectedServersList as $server)
                    @include('livewire.partials.server-card', ['server' => $server])
                @endforeach
            </div>
        @else
            <!-- Show All Servers when none selected -->
            <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded text-sm">
                <strong>No servers selected - showing all servers:</strong><br>
                Selected Servers Array: {{ json_encode($selectedServers) }}<br>
                Selected Servers Count: {{ count($selectedServers) }}<br>
                Total Servers Available: {{ $servers->count() }}<br>
                Selected Servers Types: {{ json_encode(array_map('gettype', $selectedServers)) }}<br>
                Last Update: {{ now()->format('H:i:s') }}<br>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($servers as $server)
                    @include('livewire.partials.server-card', ['server' => $server])
                @endforeach
            </div>
        @endif
    </div>

    <!-- Loading Indicator -->
    <div wire:loading class="fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span class="text-gray-700">Updating dashboard...</span>
        </div>
    </div>
</div>

<!-- Chart.js Script -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Chart functionality temporarily disabled for server selection testing
console.log('Chart functionality disabled for testing server selection');

// Only keep the Livewire debugging functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOMContentLoaded - Chart functionality disabled for testing');
    console.log('Livewire available:', typeof Livewire !== 'undefined');
    
    // Test if Livewire is working
    if (typeof Livewire !== 'undefined') {
        console.log('Livewire is loaded!');
        console.log('Livewire version:', Livewire.version);
        
        // Test if we can find any Livewire components
        const livewireElements = document.querySelectorAll('[wire\\:id]');
        console.log('Found Livewire elements:', livewireElements.length);
        livewireElements.forEach((el, index) => {
            console.log(`Livewire element ${index}:`, el.getAttribute('wire:id'));
        });
    } else {
        console.error('Livewire is not loaded!');
    }
    
    // Debug: Check if checkboxes exist
    const checkboxes = document.querySelectorAll('input[type="checkbox"][wire\\:click*="toggleServerSelection"]');
    console.log('Found checkboxes:', checkboxes.length);
    checkboxes.forEach((checkbox, index) => {
        console.log(`Checkbox ${index}:`, checkbox.getAttribute('wire:click'), 'checked:', checkbox.checked);
    });
    
    // Debug: Check if server filter divs exist
    const serverFilterDivs = document.querySelectorAll('[wire\\:click*="toggleServerSelection"]');
    console.log('Found server filter divs:', serverFilterDivs.length);
    serverFilterDivs.forEach((div, index) => {
        console.log(`Server filter div ${index}:`, div.getAttribute('wire:click'));
    });
    
    // Debug: Listen for all clicks to see if checkboxes are being clicked
    document.addEventListener('click', function(e) {
        if (e.target.matches('input[type="checkbox"][wire\\:click*="toggleServerSelection"]')) {
            console.log('Checkbox clicked:', e.target);
            console.log('wire:click attribute:', e.target.getAttribute('wire:click'));
            console.log('checked state before click:', e.target.checked);
            console.log('server ID from wire:click:', e.target.getAttribute('wire:click').match(/toggleServerSelection\((\d+)\)/)?.[1]);
        }
        
        // Test if any Livewire buttons are being clicked
        if (e.target.matches('[wire\\:click]')) {
            console.log('Livewire button clicked:', e.target);
            console.log('wire:click attribute:', e.target.getAttribute('wire:click'));
        }
        
        // Test if server filter divs are being clicked
        if (e.target.closest('[wire\\:click*="toggleServerSelection"]')) {
            const div = e.target.closest('[wire\\:click*="toggleServerSelection"]');
            console.log('Server filter div clicked:', div);
            console.log('wire:click attribute:', div.getAttribute('wire:click'));
            console.log('server ID from wire:click:', div.getAttribute('wire:click').match(/toggleServerSelection\((\d+)\)/)?.[1]);
            console.log('=== SENDING LIVEWIRE REQUEST ===');
            
            // Test if we can manually trigger the Livewire call
            setTimeout(() => {
                console.log('Checking if Livewire request was sent...');
                if (typeof Livewire !== 'undefined') {
                    console.log('Livewire is available, checking component...');
                    const livewireElements = document.querySelectorAll('[wire\\:id]');
                    console.log('Found Livewire elements:', livewireElements.length);
                    livewireElements.forEach((el, index) => {
                        console.log(`Livewire element ${index}:`, el.getAttribute('wire:id'));
                    });
                }
            }, 100);
        }
    });
    
    // Also listen for Livewire events if available
    if (typeof Livewire !== 'undefined') {
        // Add debugging hooks for Livewire requests
        Livewire.hook('message.sent', (message, component) => {
            console.log('=== LIVEWIRE MESSAGE SENT ===', {
                message: message,
                component: component.fingerprint.name,
                timestamp: new Date().toISOString()
            });
        });
        
        Livewire.hook('message.received', (message, component) => {
            console.log('=== LIVEWIRE MESSAGE RECEIVED ===', {
                message: message,
                component: component.fingerprint.name,
                timestamp: new Date().toISOString()
            });
        });
        
        Livewire.hook('message.processed', (message, component) => {
            console.log('=== LIVEWIRE MESSAGE PROCESSED ===', {
                message: message,
                component: component.fingerprint.name,
                timestamp: new Date().toISOString()
            });
        });
        
        // Listen for test event
        Livewire.on('testEvent', function(message) {
            console.log('Test event received:', message);
            alert('Livewire is working! Message: ' + message);
        });
        
        // Listen for simple test event
        Livewire.on('simpleTestEvent', function(message) {
            console.log('Simple test event received:', message);
            alert('Simple test works! Message: ' + message);
        });
        
        // Listen for showAlert event
        Livewire.on('showAlert', function(message) {
            console.log('Alert event received:', message);
            alert(message);
        });
    }
});
</script> 