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

            <a href="#" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-900 hover:bg-gray-100 transition-colors duration-200"
               :class="{ 'justify-center': sidebarMinimized }">
                <i class="fas fa-bell text-lg w-5"></i>
                <span :class="{ 'lg:hidden': sidebarMinimized }" class="ml-3 transition-opacity duration-300">Alerts</span>
            </a>

            <a href="#" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-900 hover:bg-gray-100 transition-colors duration-200"
               :class="{ 'justify-center': sidebarMinimized }">
                <i class="fas fa-chart-bar text-lg w-5"></i>
                <span :class="{ 'lg:hidden': sidebarMinimized }" class="ml-3 transition-opacity duration-300">Analytics</span>
            </a>

            <a href="#" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-900 hover:bg-gray-100 transition-colors duration-200"
               :class="{ 'justify-center': sidebarMinimized }">
                <i class="fas fa-users text-lg w-5"></i>
                <span :class="{ 'lg:hidden': sidebarMinimized }" class="ml-3 transition-opacity duration-300">Users</span>
            </a>

            <a href="#" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-900 hover:bg-gray-100 transition-colors duration-200"
               :class="{ 'justify-center': sidebarMinimized }">
                <i class="fas fa-cog text-lg w-5"></i>
                <span :class="{ 'lg:hidden': sidebarMinimized }" class="ml-3 transition-opacity duration-300">Settings</span>
            </a>

            <a href="#" 
               class="flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-gray-900 hover:bg-gray-100 transition-colors duration-200"
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
                    <!-- Notifications -->
                    <button class="p-1.5 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none relative">
                        <i class="fas fa-bell text-xl"></i>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

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
</script>



