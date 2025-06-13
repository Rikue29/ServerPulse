@extends('layouts.app')

@section('content')
<div class="sidebar-margin min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
                <p class="text-sm text-gray-500 mt-1">Server monitoring and management overview</p>
            </div>
            <div class="flex items-center space-x-2">
                <div class="flex items-center text-sm text-green-600">
                    <div class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                    {{ $servers->count() }} servers online
                </div>
            </div>
        </div>
    </div>

    <!-- Content Area -->
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .glass-effect {
            background: rgba(255, 255, 255, 0.25);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }
        .sidebar-margin {
            margin-left: 16rem;
        }
        @media (max-width: 1024px) {
            .sidebar-margin {
                margin-left: 0;
            }
        }
        .pulse-animation {
            animation: pulse-glow 2s infinite;
        }
        @keyframes pulse-glow {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.7;
                transform: scale(1.1);
            }
        }
        .fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
            opacity: 0;
            transform: translateY(20px);
        }
        
        @keyframes fadeInUp {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .hover-scale {
            transition: transform 0.2s ease-in-out;
        }
        
        .hover-scale:hover {
            transform: scale(1.02);
</head>
<body class="bg-gray-50 min-h-screen">
    <!-- Include Sidebar Navigation -->
    @include('layouts.navigation')
    
    <!-- Main Content with Sidebar Margin -->
    <div class="sidebar-margin min-h-screen">
        <!-- Header -->
        <div class="bg-white border-b border-gray-200 px-6 py-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-gray-900">Dashboard</h1>
                    <p class="text-sm text-gray-500 mt-1">Monitor your servers and their performance</p>
                </div>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2 text-green-600">
                        <div class="w-2 h-2 bg-green-500 rounded-full animate-pulse"></div>
                        <span class="text-sm font-medium">Live Monitoring</span>
                    </div>
                    <a href="{{ route('servers.create') }}" 
                       class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                        <i class="fas fa-plus mr-2"></i>Add Server
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6">
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200 fade-in-up">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Total Servers</p>
                            <p class="text-2xl font-bold text-gray-900">{{ \App\Models\Server::count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-server text-blue-600 text-xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover:shadow-md transition-shadow duration-200 fade-in-up" style="animation-delay: 0.1s">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-600">Active Alerts</p>
                            <p class="text-2xl font-bold text-red-600">{{ \App\Models\Log::where('level', 'error')->count() }}</p>
                        </div>
                        <div class="w-12 h-12 bg-red-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover-scale fade-in-up" style="animation-delay: 0.3s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Warnings</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ \App\Models\Log::where('level', 'warning')->count() }}</p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-exclamation-circle text-yellow-600 text-xl"></i>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 hover-scale fade-in-up" style="animation-delay: 0.4s">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Uptime</p>
                        <p class="text-2xl font-bold text-green-600">99.9%</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Server Management -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 fade-in-up" style="animation-delay: 0.5s">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-server text-blue-600 mr-2"></i>
                        Server Management
                    </h3>
                    <a href="{{ route('servers.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-medium">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <div class="space-y-4">
                    <a href="{{ route('servers.create') }}" 
                       class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200 group">
                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center group-hover:bg-blue-200 transition-colors duration-200">
                            <i class="fas fa-plus text-blue-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Add New Server</p>
                            <p class="text-xs text-gray-500">Configure a new server for monitoring</p>
                        </div>
                    </a>
                    
                    <a href="{{ route('servers.index') }}" 
                       class="flex items-center p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200 group">
                        <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center group-hover:bg-green-200 transition-colors duration-200">
                            <i class="fas fa-cogs text-green-600"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">Manage Servers</p>
                            <p class="text-xs text-gray-500">View and configure existing servers</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Activity -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 fade-in-up" style="animation-delay: 0.6s">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                        <i class="fas fa-clock text-purple-600 mr-2"></i>
                        Recent Activity
                    </h3>
                    <a href="{{ route('logs') }}" class="text-purple-600 hover:text-purple-700 text-sm font-medium">
                        View All <i class="fas fa-arrow-right ml-1"></i>
                    </a>
                </div>
                
                <div class="space-y-4">
                    @php
                        $recentLogs = \App\Models\Log::with('server')->orderBy('created_at', 'desc')->limit(5)->get();
                    @endphp
                    
                    @forelse($recentLogs as $log)
                        <div class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg">
                            <div class="w-2 h-2 mt-2 rounded-full 
                                @if($log->level == 'critical') bg-red-500
                                @elseif($log->level == 'warning') bg-yellow-500
                                @elseif($log->level == 'error') bg-red-400
                                @else bg-blue-500 @endif
                            "></div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-900">
                                    {{ $log->server ? $log->server->name : 'Unknown Server' }}
                                </p>
                                <p class="text-xs text-gray-500 truncate">
                                    {{ Str::limit($log->message, 60) }}
                                </p>
                                <p class="text-xs text-gray-400 mt-1">
                                    {{ $log->created_at ? $log->created_at->diffForHumans() : 'Unknown time' }}
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8">
                            <i class="fas fa-inbox text-gray-400 text-3xl mb-3"></i>
                            <p class="text-gray-500">No recent activity</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- System Status -->
        <div class="mt-8 bg-white rounded-xl shadow-sm border border-gray-100 p-6 fade-in-up" style="animation-delay: 0.7s">
            <h3 class="text-lg font-semibold text-gray-900 mb-6 flex items-center">
                <i class="fas fa-chart-line text-indigo-600 mr-2"></i>
                System Status Overview
            </h3>
            
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="text-center">
                    <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-shield-alt text-green-600 text-xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900">Security</h4>
                    <p class="text-sm text-gray-600">All systems secure</p>
                    <div class="mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Healthy
                        </span>
                    </div>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-network-wired text-blue-600 text-xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900">Network</h4>
                    <p class="text-sm text-gray-600">Connectivity optimal</p>
                    <div class="mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            Optimal
                        </span>
                    </div>
                </div>
                
                <div class="text-center">
                    <div class="w-16 h-16 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-tachometer-alt text-purple-600 text-xl"></i>
                    </div>
                    <h4 class="text-lg font-semibold text-gray-900">Performance</h4>
                    <p class="text-sm text-gray-600">Running smoothly</p>
                    <div class="mt-2">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                            Excellent
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Auto-refresh page every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>
