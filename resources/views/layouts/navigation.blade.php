<!-- Top Navigation Bar -->
<nav x-data="{ open: false, userDropdown: false }" class="bg-white shadow-lg border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <!-- Left Side - Logo and Navigation -->
            <div class="flex">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <div class="flex items-center space-x-3">
                        <div class="w-8 h-8 bg-blue-600 rounded-lg flex items-center justify-center">
                            <span class="text-white font-bold text-sm">SP</span>
                        </div>
                        <span class="font-semibold text-xl text-gray-900">Server Pulse</span>
                    </div>
                </div>

                <!-- Navigation Links (Desktop) -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <!-- Servers -->
                    <a href="{{ route('servers.index') }}" 
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200 {{ request()->routeIs('servers.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-server mr-2"></i>
                        Servers
                    </a>

                    <!-- Logs -->
                    <a href="{{ route('logs.index') }}" 
                       class="inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium transition-colors duration-200 {{ request()->routeIs('logs.*') ? 'border-blue-500 text-gray-900' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        <i class="fas fa-list-alt mr-2"></i>
                        Logs
                    </a>
                </div>
            </div>

            <!-- Right Side - User Menu -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <!-- User Dropdown -->
                <div class="ml-3 relative" x-data="{ open: false }">
                    <div>
                        <button @click="open = !open" class="bg-white flex text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-all duration-200">
                            <span class="sr-only">Open user menu</span>
                            <div class="flex items-center space-x-3 px-3 py-2 rounded-lg hover:bg-gray-50">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <span class="text-blue-600 font-medium text-sm">{{ substr(Auth::user()->name, 0, 1) }}</span>
                                </div>
                                <div class="hidden md:block text-left">
                                    <div class="font-medium text-gray-900 text-sm">{{ Auth::user()->name }}</div>
                                    <div class="text-xs text-gray-500">{{ Auth::user()->email }}</div>
                                </div>
                                <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                            </div>
                        </button>
                    </div>

                    <!-- Dropdown Menu -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="transform opacity-0 scale-95" x-transition:enter-end="transform opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="transform opacity-100 scale-100" x-transition:leave-end="transform opacity-0 scale-95" class="origin-top-right absolute right-0 mt-2 w-48 rounded-md shadow-lg py-1 bg-white ring-1 ring-black ring-opacity-5 focus:outline-none z-50" @click.away="open = false">
                        <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                            <i class="fas fa-user mr-3 text-gray-400"></i>
                            Profile Settings
                        </a>
                        <div class="border-t border-gray-100"></div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full flex items-center px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-sign-out-alt mr-3 text-gray-400"></i>
                                Log Out
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="-mr-2 flex items-center sm:hidden">
                <button @click="open = !open" class="bg-white inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-blue-500">
                    <span class="sr-only">Open main menu</span>
                    <i class="fas fa-bars" x-show="!open"></i>
                    <i class="fas fa-times" x-show="open"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile Navigation Menu -->
    <div x-show="open" class="sm:hidden">
        <div class="pt-2 pb-3 space-y-1 border-t border-gray-200">
            <!-- Servers -->
            <a href="{{ route('servers.index') }}" 
               class="flex items-center pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200 {{ request()->routeIs('servers.*') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                <i class="fas fa-server mr-3"></i>
                Servers
            </a>

            <!-- Logs -->
            <a href="{{ route('logs.index') }}" 
               class="flex items-center pl-3 pr-4 py-2 border-l-4 text-base font-medium transition-colors duration-200 {{ request()->routeIs('logs.*') ? 'bg-blue-50 border-blue-500 text-blue-700' : 'border-transparent text-gray-600 hover:text-gray-800 hover:bg-gray-50 hover:border-gray-300' }}">
                <i class="fas fa-list-alt mr-3"></i>
                Logs
            </a>
        </div>

        <!-- Mobile User Section -->
        <div class="pt-4 pb-3 border-t border-gray-200">
            <div class="flex items-center px-4">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 font-medium">{{ substr(Auth::user()->name, 0, 1) }}</span>
                </div>
                <div class="ml-3">
                    <div class="text-base font-medium text-gray-800">{{ Auth::user()->name }}</div>
                    <div class="text-sm font-medium text-gray-500">{{ Auth::user()->email }}</div>
                </div>
            </div>
            <div class="mt-3 space-y-1">
                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                    <i class="fas fa-user mr-3"></i>
                    Profile Settings
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-2 text-base font-medium text-gray-500 hover:text-gray-800 hover:bg-gray-100">
                        <i class="fas fa-sign-out-alt mr-3"></i>
                        Log Out
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>



