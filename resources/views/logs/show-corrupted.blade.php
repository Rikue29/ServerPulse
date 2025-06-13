@extends('layouts.app')

@section('content')
<div class="sidebar-margin min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Log Details</h1>
                <p class="text-sm text-gray-500 mt-1">Detailed information about this log entry</p>
            </div>
            <div class="flex items-center space-x-2">
                <a href="{{ route('logs.index') }}" 
                   class="inline-flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Back to Logs
                </a>
            </div>
        </div>
    </div>

    <!-- Content Area -->
    <div class="p-6">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Log Overview Card -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-8 fade-in-up card-hover">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-info-circle text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold text-gray-900">Log Entry #{{ $log->id }}</h2>
                        <p class="text-gray-500">{{ $log->created_at ? $log->created_at->format('F j, Y \a\t g:i:s A') : 'N/A' }}</p>
                    </div>
                </div>
                
                <!-- Level Badge -->
                @if($log->level == 'critical')
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gradient-to-r from-red-100 to-red-200 text-red-800 border border-red-300 shadow-sm">
                        <i class="fas fa-exclamation-triangle mr-2 pulse-animation"></i>
                        Critical
                    </span>
                @elseif($log->level == 'error')
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gradient-to-r from-red-100 to-red-200 text-red-800 border border-red-300 shadow-sm">
                        <i class="fas fa-times-circle mr-2"></i>
                        Error
                    </span>
                @elseif($log->level == 'warning')
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gradient-to-r from-yellow-100 to-yellow-200 text-yellow-800 border border-yellow-300 shadow-sm">
                        <i class="fas fa-exclamation-circle mr-2"></i>
                        Warning
                    </span>
                @elseif($log->level == 'info')
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gradient-to-r from-blue-100 to-blue-200 text-blue-800 border border-blue-300 shadow-sm">
                        <i class="fas fa-info-circle mr-2"></i>
                        Info
                    </span>
                @else
                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-semibold bg-gradient-to-r from-gray-100 to-gray-200 text-gray-800 border border-gray-300 shadow-sm">
                        <i class="fas fa-circle mr-2"></i>
                        {{ ucfirst($log->level ?? 'Unknown') }}
                    </span>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Details -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Message Card -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden fade-in-up card-hover" style="animation-delay: 0.1s">
                    <div class="bg-gradient-to-r from-primary-50 to-primary-100 px-6 py-4 border-b border-primary-200">
                        <h3 class="text-lg font-semibold text-primary-900 flex items-center">
                            <i class="fas fa-comment-alt text-primary-600 mr-2"></i>
                            Log Message
                        </h3>
                    </div>
                    <div class="p-6">
                        <div class="bg-gray-50 rounded-lg p-4 border-l-4 border-primary-500">
                            <p class="text-gray-900 leading-relaxed">{{ $log->message ?? 'No message available' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Context Data (if available) -->
                @if($log->context)
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden fade-in-up card-hover" style="animation-delay: 0.2s">
                    <div class="bg-gradient-to-r from-purple-50 to-purple-100 px-6 py-4 border-b border-purple-200">
                        <h3 class="text-lg font-semibold text-purple-900 flex items-center">
                            <i class="fas fa-code text-purple-600 mr-2"></i>
                            Context Data
                        </h3>
                    </div>
                    <div class="p-6">
                        <pre class="bg-gray-900 text-green-400 p-4 rounded-lg overflow-auto text-sm font-mono">{{ is_string($log->context) ? $log->context : json_encode($log->context, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Server Information -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden fade-in-up card-hover" style="animation-delay: 0.3s">
                    <div class="bg-gradient-to-r from-green-50 to-green-100 px-6 py-4 border-b border-green-200">
                        <h3 class="text-lg font-semibold text-green-900 flex items-center">
                            <i class="fas fa-server text-green-600 mr-2"></i>
                            Server Information
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        @if($log->server)
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-green-500 rounded-full pulse-animation"></div>
                                <div>
                                    <p class="font-semibold text-gray-900">{{ $log->server->name }}</p>
                                    <p class="text-sm text-gray-500">{{ $log->server->ip_address }}</p>
                                </div>
                            </div>
                            
                            <div class="border-t border-gray-200 pt-4">
                                <a href="{{ route('servers.show', $log->server->id) }}" 
                                   class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-300 transform hover:scale-105 shadow-sm hover:shadow-md w-full justify-center">
                                    <i class="fas fa-external-link-alt mr-2"></i>
                                    View Server Details
                                </a>
                            </div>
                        @else
                            <div class="flex items-center space-x-3">
                                <div class="w-3 h-3 bg-gray-400 rounded-full"></div>
                                <div>
                                    <p class="font-semibold text-gray-500">Unknown Server</p>
                                    <p class="text-sm text-gray-400">Server information not available</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Log Metadata -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden fade-in-up card-hover" style="animation-delay: 0.4s">
                    <div class="bg-gradient-to-r from-gray-50 to-gray-100 px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                            <i class="fas fa-info text-gray-600 mr-2"></i>
                            Metadata
                        </h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">Log ID</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900">#{{ $log->id }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">Source</label>
                                <p class="mt-1 text-lg font-semibold text-gray-900">{{ $log->source ?? 'System' }}</p>
                            </div>
                        </div>
                        
                        <div class="border-t border-gray-200 pt-4">
                            <label class="block text-sm font-medium text-gray-500 uppercase tracking-wider">Created</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $log->created_at ? $log->created_at->diffForHumans() : 'Unknown' }}</p>
                            @if($log->created_at)
                                <p class="text-xs text-gray-500">{{ $log->created_at->format('l, F j, Y \a\t g:i:s A T') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden fade-in-up card-hover" style="animation-delay: 0.5s">
                    <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 px-6 py-4 border-b border-indigo-200">
                        <h3 class="text-lg font-semibold text-indigo-900 flex items-center">
                            <i class="fas fa-tools text-indigo-600 mr-2"></i>
                            Quick Actions
                        </h3>
                    </div>
                    <div class="p-6 space-y-3">
                        <button class="w-full px-4 py-2 bg-gradient-to-r from-blue-600 to-blue-700 text-white rounded-lg hover:from-blue-700 hover:to-blue-800 transition-all duration-300 transform hover:scale-105 shadow-sm hover:shadow-md flex items-center justify-center">
                            <i class="fas fa-download mr-2"></i>
                            Export Log
                        </button>
                        
                        <button class="w-full px-4 py-2 bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg hover:from-purple-700 hover:to-purple-800 transition-all duration-300 transform hover:scale-105 shadow-sm hover:shadow-md flex items-center justify-center">
                            <i class="fas fa-share mr-2"></i>
                            Share Log
                        </button>
                        
                        @if($log->level == 'critical' || $log->level == 'error')
                        <button class="w-full px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-300 transform hover:scale-105 shadow-sm hover:shadow-md flex items-center justify-center">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Create Alert
                        </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Add smooth animations
        document.addEventListener('DOMContentLoaded', function() {
            // Add click animations for buttons
            const buttons = document.querySelectorAll('button, a[class*="bg-"]');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    // Create ripple effect
                    const ripple = document.createElement('span');
                    const rect = this.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;
                    
                    ripple.style.cssText = `
                        position: absolute;
                        border-radius: 50%;
                        transform: scale(0);
                        animation: ripple 0.6s linear;
                        background-color: rgba(255, 255, 255, 0.5);
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        pointer-events: none;
                    `;
                    
                    this.style.position = 'relative';
                    this.style.overflow = 'hidden';
                    this.appendChild(ripple);
                    
                    setTimeout(() => {
                        ripple.remove();
                    }, 600);
                });
            });
        });

        // Add CSS for ripple animation
        const style = document.createElement('style');
        style.textContent = `
            @keyframes ripple {
                to {
                    transform: scale(4);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
    </script>
</body>
</html>
                                </p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Log ID</label>
                                <p class="mt-1 text-sm text-gray-900 font-mono">{{ $log->id }}</p>
                            </div>

                            @if($log->server)
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Server Environment</label>
                                    <p class="mt-1 text-sm text-gray-900">
                                        <span class="px-2 py-1 text-xs rounded 
                                            @if($log->server->environment == 'prod') bg-red-100 text-red-800
                                            @elseif($log->server->environment == 'staging') bg-yellow-100 text-yellow-800
                                            @else bg-green-100 text-green-800 @endif">
                                            {{ ucfirst($log->server->environment) }}
                                        </span>
                                    </p>
                                </div>

                                @if($log->server->location)
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Server Location</label>
                                        <p class="mt-1 text-sm text-gray-900">{{ $log->server->location }}</p>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>

                    <div class="mt-6 border-t pt-6">
                        <label class="block text-sm font-medium text-gray-700">Message</label>
                        <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $log->message ?? 'No message provided' }}</p>
                        </div>
                    </div>

                    @if($log->context && !empty($log->context))
                        <div class="mt-6 border-t pt-6">
                            <label class="block text-sm font-medium text-gray-700">Context Data</label>
                            <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                                <pre class="text-sm text-gray-900 overflow-x-auto">{{ json_encode($log->context, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                        </div>
                    @endif

                    <div class="mt-6 border-t pt-6">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-500">
                                Created {{ $log->created_at ? $log->created_at->diffForHumans() : 'unknown' }}
                                @if($log->updated_at && $log->updated_at != $log->created_at)
                                    • Updated {{ $log->updated_at->diffForHumans() }}
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
