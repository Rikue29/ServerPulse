<!-- Dashboard Layout -->
<div x-data="{ 
    sidebarOpen: true, 
    sidebarMinimized: localStorage.getItem('sidebarMinimized') === 'true',
    toggleSidebar() {
        this.sidebarMinimized = !this.sidebarMinimized;
        localStorage.setItem('sidebarMinimized', this.sidebarMinimized);
    }
}" class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div :class="{
            'translate-x-0 lg:w-64': (!sidebarMinimized && (sidebarOpen || window.innerWidth >= 1024)),
            'translate-x-0 lg:w-16': sidebarMinimized,
            '-translate-x-full': (!sidebarOpen && window.innerWidth < 1024)
         }"
         class="fixed inset-y-0 left-0 z-30 bg-white border-r border-gray-200 transform lg:static lg:inset-0 transition-all duration-300 ease-in-out">
        <!-- Logo -->
        <div class="flex items-center justify-between h-14 px-4 border-b border-gray-200">
            <div class="flex items-center">
                <span :class="{ 'lg:hidden': sidebarMinimized }" class="font-bold text-xl text-gray-800 transition-opacity duration-300">
                    Server<span class="text-blue-600">PuLse</span>
                </span>
            </div>
            <!-- Sidebar Toggle -->
            <button @click="toggleSidebar()"
                    class="hidden lg:block p-2 rounded-md text-gray-600 hover:bg-gray-100 focus:outline-none"
                    title="Toggle Sidebar">
                <i class="fas" :class="{ 'fa-chevron-left': !sidebarMinimized, 'fa-chevron-right': sidebarMinimized }"></i>
            </button>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-2 py-2 space-y-1 overflow-y-auto">
            <a href="{{ route('dashboard') }}" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-900 hover:bg-gray-100' }}"
               :class="{ 'justify-center': sidebarMinimized }">
                <i class="fas fa-dashboard text-lg w-5"></i>
                <span :class="{ 'lg:hidden': sidebarMinimized }" class="ml-3 transition-opacity duration-300">Dashboard</span>
            </a>

            <a href="{{ route('servers.index') }}" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('servers.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-900 hover:bg-gray-100' }}"
               :class="{ 'justify-center': sidebarMinimized }">
                <i class="fas fa-server text-lg w-5"></i>
                <span :class="{ 'lg:hidden': sidebarMinimized }" class="ml-3 transition-opacity duration-300">Servers</span>
            </a>

            <a href="{{ route('logs.index') }}" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('logs.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-900 hover:bg-gray-100' }}"
               :class="{ 'justify-center': sidebarMinimized }">
                <i class="fas fa-list-alt text-lg w-5"></i>
                <span :class="{ 'lg:hidden': sidebarMinimized }" class="ml-3 transition-opacity duration-300">Logs</span>
            </a>

            <a href="{{route('alerts.index')}}" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('alerts.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-900 hover:bg-gray-100' }}"
               :class="{ 'justify-center': sidebarMinimized }">
                <i class="fas fa-bell text-lg w-5"></i>
                <span :class="{ 'lg:hidden': sidebarMinimized }" class="ml-3 transition-opacity duration-300">Alerts</span>
            </a>

            <a href="{{ route('analytics') }}" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('analytics') ? 'bg-blue-50 text-blue-700' : 'text-gray-900 hover:bg-gray-100' }}"
               :class="{ 'justify-center': sidebarMinimized }">
                <i class="fas fa-chart-bar text-lg w-5"></i>
                <span :class="{ 'lg:hidden': sidebarMinimized }" class="ml-3 transition-opacity duration-300">Analytics</span>
            </a>

            <a href="{{ route('user') }}" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('user') ? 'bg-blue-50 text-blue-700' : 'text-gray-900 hover:bg-gray-100' }}"
               :class="{ 'justify-center': sidebarMinimized }">
                <i class="fas fa-users text-lg w-5"></i>
                <span :class="{ 'lg:hidden': sidebarMinimized }" class="ml-3 transition-opacity duration-300">Users</span>
            </a>

            <a href="{{ route('settings') }}" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('settings') ? 'bg-blue-50 text-blue-700' : 'text-gray-900 hover:bg-gray-100' }}"
               :class="{ 'justify-center': sidebarMinimized }">
                <i class="fas fa-cog text-lg w-5"></i>
                <span :class="{ 'lg:hidden': sidebarMinimized }" class="ml-3 transition-opacity duration-300">Settings</span>
            </a>

            <a href="{{ route('help.index') }}" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg transition-colors duration-200 {{ request()->routeIs('help.index') ? 'bg-blue-50 text-blue-700' : 'text-gray-900 hover:bg-gray-100' }}"
               :class="{ 'justify-center': sidebarMinimized }">
                <i class="fas fa-question-circle text-lg w-5"></i>
                <span :class="{ 'lg:hidden': sidebarMinimized }" class="ml-3 transition-opacity duration-300">Help & Support</span>
            </a>
        </nav>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen && window.innerWidth < 1024"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-20 bg-gray-600 bg-opacity-75"
         @click="sidebarOpen = false">
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Top Bar -->
        <div class="flex-shrink-0 bg-white border-b border-gray-200">
            <div class="flex items-center justify-between h-14 px-4">
                <!-- Mobile Menu Button -->
                <button @click="sidebarOpen = !sidebarOpen"
                        class="lg:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100 focus:outline-none">
                    <i class="fas fa-bars text-xl"></i>
                </button>

                <!-- Right Side Items -->
                <div class="flex items-center space-x-4 ml-auto">
                    <!-- Notifications Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <!-- Bell Icon -->
                        <button @click="open = !open" class="p-1.5 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none relative">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>

                        <!-- Dropdown Panel -->
                        <div
                            x-show="open"
                            @click.away="open = false"
                            class="absolute right-0 mt-2 w-96 bg-white border border-gray-200 rounded-lg shadow-lg z-50 alerts-dropdown"
                        >
                            <div class="p-3 font-semibold border-b flex items-center justify-between">
                                <span>Recent Alerts</span>
                                <a href="{{ route('alerts.index') }}" class="text-sm text-blue-600 hover:text-blue-800">View All</a>
                            </div>
                            <ul class="max-h-64 overflow-y-auto divide-y">
                                @forelse($recentAlerts as $alert)
                                    <li class="px-4 py-3 text-sm">
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center space-x-2">
                                                    <svg class="h-4 w-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                                    </svg>
                                                    <span class="font-semibold text-red-600 uppercase text-xs">
                                                        {{ $alert->alert_type }}
                                                    </span>
                                                    <span class="px-2 py-1 text-xs rounded-full {{ $alert->severity_color }}">
                                                        {{ ucfirst($alert->severity) }}
                                                    </span>
                                                </div>
                                                <div class="mt-1">
                                                    <div class="text-gray-900 font-medium">{{ $alert->server->name ?? 'Server #' . $alert->server_id }}</div>
                                                    <div class="text-gray-600">{{ $alert->alert_message }}</div>
                                                    <div class="text-xs text-gray-400 mt-1">{{ $alert->alert_time->diffForHumans() }}</div>
                                                </div>
                                            </div>
                                            <button 
                                                onclick="resolveAlertFromDropdown({{ $alert->id }})"
                                                class="ml-2 px-3 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-700 transition-colors duration-200 flex items-center"
                                            >
                                                Resolve
                                            </button>
                                        </div>
                                    </li>
                                @empty
                                    <li class="px-4 py-3 text-sm text-gray-500 text-center">
                                        <svg class="inline h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                        </svg>
                                        No recent alerts
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open"
                                class="flex items-center space-x-2 p-1.5 rounded-lg hover:bg-gray-100 focus:outline-none">
                            <div class="w-7 h-7 bg-blue-100 rounded-full flex items-center justify-center">
                                <span class="text-blue-600 font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                            </div>
                            <i class="fas fa-chevron-down text-gray-600"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div x-show="open"
                             @click.away="open = false"
                             class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>
                            <hr class="my-1">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto">
            @yield('content')
        </main>
    </div>
</div>

<!-- Initialize Alpine.js with window resize listener -->
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('layout', () => ({
        init() {
            // Initialize sidebarOpen based on screen size
            this.sidebarOpen = window.innerWidth >= 1024;
            
            // Handle window resize
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    this.sidebarOpen = true;
                }
            });
        }
    }));
});

// Initialize global toast manager
document.addEventListener('DOMContentLoaded', function() {
    if (typeof Alpine !== 'undefined') {
        window.toastManager = Alpine.data('toastManager')();
    }
});

// Function to resolve alert from dropdown
function resolveAlertFromDropdown(alertId) {
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<div class="inline-block animate-spin rounded-full h-3 w-3 border-b-2 border-white"></div>';
    button.disabled = true;
    
    fetch(`/alerts/${alertId}/resolve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success toast
            if (window.toastManager) {
                window.toastManager.success('Success', 'Alert resolved successfully');
            }
            
            // Remove the alert from the dropdown immediately
            const alertElement = button.closest('li');
            if (alertElement) {
                alertElement.style.transition = 'all 0.3s ease';
                alertElement.style.opacity = '0';
                alertElement.style.transform = 'translateX(100%)';
                setTimeout(() => {
                    alertElement.remove();
                    
                    // Check if dropdown is empty
                    const dropdown = document.querySelector('.alerts-dropdown ul');
                    if (dropdown && dropdown.children.length === 0) {
                        dropdown.innerHTML = '<li class="px-4 py-3 text-sm text-gray-500 text-center"><svg class="inline h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>No recent alerts</li>';
                    }
                }, 300);
            }
            
            // Dispatch Livewire events to refresh components
            if (typeof Livewire !== 'undefined') {
                Livewire.dispatch('alert-resolved', { alertId: alertId });
                Livewire.dispatch('alertResolvedFromDropdown', { alertId: alertId });
                Livewire.dispatch('refresh-alerts');
            }
            
            // Dispatch custom DOM event
            document.dispatchEvent(new CustomEvent('alert-resolved-globally', {
                detail: { alertId: alertId }
            }));
            
            // Call global refresh function if available
            if (typeof window.refreshAlertsTable === 'function') {
                window.refreshAlertsTable();
            }
            
        } else {
            // Show error toast
            if (window.toastManager) {
                window.toastManager.error('Error', data.message || 'Failed to resolve alert');
            }
            
            // Restore button
            button.innerHTML = originalText;
            button.disabled = false;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        // Show error toast
        if (window.toastManager) {
            window.toastManager.error('Error', 'Network error occurred while resolving alert');
        }
        
        // Restore button
        button.innerHTML = originalText;
        button.disabled = false;
    });
}
</script>



