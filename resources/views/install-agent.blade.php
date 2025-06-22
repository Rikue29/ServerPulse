<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServerPulse Agent Installation</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-4xl font-bold text-gray-900 mb-2">
                    <i class="fas fa-server text-blue-600"></i>
                    ServerPulse Agent
                </h1>
                <p class="text-xl text-gray-600">Enhanced Real-time Server Monitoring</p>
            </div>

            <!-- Installation Methods -->
            <div class="grid md:grid-cols-2 gap-8 mb-8">
                <!-- One-Line Installation -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-rocket text-green-600"></i>
                        Quick Install
                    </h2>
                    <p class="text-gray-600 mb-4">Install the enhanced agent with auto-registration in one command:</p>
                    
                    <div class="bg-gray-900 text-green-400 p-4 rounded-lg font-mono text-sm mb-4">
                        <div class="flex items-center justify-between">
                            <span>curl -sSL {{ request()->getSchemeAndHttpHost() }}/agent/install.sh | sudo bash</span>
                            <button onclick="copyToClipboard(this)" class="text-blue-400 hover:text-blue-300 ml-2">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                    </div>

                    <div class="space-y-2 text-sm text-gray-600">
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Auto-detects system information</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Installs dependencies automatically</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Registers with ServerPulse dashboard</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-check text-green-500 mr-2"></i>
                            <span>Starts monitoring immediately</span>
                        </div>
                    </div>
                </div>

                <!-- Manual Installation -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-2xl font-semibold text-gray-900 mb-4">
                        <i class="fas fa-cogs text-blue-600"></i>
                        Manual Install
                    </h2>
                    <p class="text-gray-600 mb-4">For custom setups or when you need more control:</p>
                    
                    <div class="space-y-3">
                        <div>
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Step 1</span>
                            <a href="{{ request()->getSchemeAndHttpHost() }}/agent/install.sh" 
                               class="text-blue-600 hover:text-blue-800 ml-2">Download installation script</a>
                        </div>
                        <div>
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Step 2</span>
                            <a href="{{ request()->getSchemeAndHttpHost() }}/agent/download" 
                               class="text-blue-600 hover:text-blue-800 ml-2">Download agent</a>
                        </div>
                        <div>
                            <span class="inline-block bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded">Step 3</span>
                            <span class="ml-2 text-gray-600">Customize and run</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Enhanced Features -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-8">
                <h2 class="text-2xl font-semibold text-gray-900 mb-6">
                    <i class="fas fa-chart-line text-purple-600"></i>
                    Enhanced Monitoring Features
                </h2>
                
                <div class="grid md:grid-cols-3 gap-6">
                    <div class="text-center">
                        <div class="bg-blue-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-microchip text-2xl text-blue-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Real-time CPU Metrics</h3>
                        <p class="text-sm text-gray-600">Per-core usage, load averages, and temperature monitoring</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-green-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-memory text-2xl text-green-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Memory & Swap</h3>
                        <p class="text-sm text-gray-600">Detailed RAM usage, swap utilization, and memory pressure</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-purple-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-hdd text-2xl text-purple-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Disk I/O</h3>
                        <p class="text-sm text-gray-600">Read/write speeds, IOPS, and storage utilization</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-orange-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-network-wired text-2xl text-orange-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Network Traffic</h3>
                        <p class="text-sm text-gray-600">Bandwidth usage, packet rates, and error monitoring</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-red-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-clock text-2xl text-red-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">5-Second Updates</h3>
                        <p class="text-sm text-gray-600">Real-time dashboard updates every 5 seconds</p>
                    </div>
                    
                    <div class="text-center">
                        <div class="bg-indigo-100 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-cog text-2xl text-indigo-600"></i>
                        </div>
                        <h3 class="font-semibold text-gray-900 mb-2">Service Monitoring</h3>
                        <p class="text-sm text-gray-600">Track SSH, web servers, databases, and custom services</p>
                    </div>
                </div>
            </div>

            <!-- Requirements -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
                <h3 class="text-lg font-semibold text-yellow-800 mb-3">
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                    System Requirements
                </h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm text-yellow-700">
                    <div>
                        <strong>Operating System:</strong>
                        <ul class="mt-1 ml-4 list-disc">
                            <li>Ubuntu 18.04+ or Debian 10+</li>
                            <li>CentOS 7+ or RHEL 7+</li>
                            <li>Amazon Linux 2</li>
                        </ul>
                    </div>
                    <div>
                        <strong>Requirements:</strong>
                        <ul class="mt-1 ml-4 list-disc">
                            <li>Python 3.6+</li>
                            <li>Root/sudo access</li>
                            <li>Internet connectivity</li>
                            <li>curl or wget</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Dashboard Link -->
            <div class="text-center mt-8">
                <a href="{{ request()->getSchemeAndHttpHost() }}/dashboard" 
                   class="inline-flex items-center px-6 py-3 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 transition">
                    <i class="fas fa-chart-bar mr-2"></i>
                    View Dashboard
                </a>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard(button) {
            const text = button.parentElement.querySelector('span').textContent;
            navigator.clipboard.writeText(text).then(() => {
                const icon = button.querySelector('i');
                icon.className = 'fas fa-check';
                button.classList.add('text-green-400');
                
                setTimeout(() => {
                    icon.className = 'fas fa-copy';
                    button.classList.remove('text-green-400');
                }, 2000);
            });
        }
    </script>
</body>
</html>
