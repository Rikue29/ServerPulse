<!-- Dashboard Layout -->
<div x-data="{ sidebarOpen: false }" class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}"
         class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-200 transform lg:translate-x-0 lg:static lg:inset-0 transition duration-300 ease-in-out">
        <!-- Logo -->
        <div class="flex items-center h-16 px-6 border-b border-gray-200">
            <img src="{{ asset('images/serverpulse-logo.svg') }}" alt="ServerPulse" class="h-8 w-8">
            <span class="ml-3 font-bold text-xl text-gray-800">
                Server<span class="text-blue-600">PuLse</span>
            </span>
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-4 space-y-1 overflow-y-auto">
            <a href="{{ route('servers.index') }}" 
               class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('servers.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-900 hover:bg-gray-100' }}">
                <i class="fas fa-server text-lg w-5"></i>
                <span class="ml-3">Servers</span>
            </a>

            <a href="{{ route('logs.index') }}" 
               class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('logs.*') ? 'bg-blue-50 text-blue-700' : 'text-gray-900 hover:bg-gray-100' }}">
                <i class="fas fa-list-alt text-lg w-5"></i>
                <span class="ml-3">Logs</span>
            </a>

            <a href="#" 
               class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-gray-900 hover:bg-gray-100">
                <i class="fas fa-bell text-lg w-5"></i>
                <span class="ml-3">Alerts</span>
            </a>

            <a href="#" 
               class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-gray-900 hover:bg-gray-100">
                <i class="fas fa-chart-bar text-lg w-5"></i>
                <span class="ml-3">Analytics</span>
            </a>

            <a href="#" 
               class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-gray-900 hover:bg-gray-100">
                <i class="fas fa-users text-lg w-5"></i>
                <span class="ml-3">Users</span>
            </a>

            <a href="#" 
               class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-gray-900 hover:bg-gray-100">
                <i class="fas fa-cog text-lg w-5"></i>
                <span class="ml-3">Settings</span>
            </a>

            <a href="#" 
               class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg text-gray-900 hover:bg-gray-100">
                <i class="fas fa-question-circle text-lg w-5"></i>
                <span class="ml-3">Help & Support</span>
            </a>
        </nav>
    </div>

    <!-- Mobile Sidebar Overlay -->
    <div x-show="sidebarOpen"
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 z-20 bg-gray-600 bg-opacity-75 lg:hidden"
         @click="sidebarOpen = false">
    </div>

    <!-- Main Content -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden lg:ml-64">
        <!-- Top Bar -->
        <div class="flex-shrink-0">
            <header class="bg-white border-b border-gray-200">
                <div class="flex items-center justify-between h-16 px-4">
                    <!-- Hamburger Menu (Mobile Only) -->
                    <button @click="sidebarOpen = !sidebarOpen"
                            class="lg:hidden p-2 rounded-md text-gray-600 hover:bg-gray-100 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>

                    <!-- Right Side Items -->
                    <div class="flex items-center space-x-4 ml-auto">
                        <!-- Notifications -->
                        <button class="p-2 rounded-lg text-gray-600 hover:bg-gray-100 focus:outline-none relative">
                            <i class="fas fa-bell text-xl"></i>
                            <span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full"></span>
                        </button>

                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                    class="flex items-center space-x-2 p-2 rounded-lg hover:bg-gray-100 focus:outline-none">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <i class="fas fa-chevron-down text-gray-600"></i>
                            </button>

                            <!-- Dropdown Menu -->
                            <div x-show="open"
                                 @click.away="open = false"
                                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1">
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
            </header>
        </div>

        <!-- Page Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto bg-gray-100 p-6">
            @if(isset($slot))
                {{ $slot }}
            @else
                @yield('content')
            @endif
        </main>
    </div>
</div>



