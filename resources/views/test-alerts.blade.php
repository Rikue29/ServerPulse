<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Alert Testing - ServerPulse</title>
    @vite(['resources/js/app.js', 'resources/css/app.css'])
    @livewireStyles
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation Header -->
        <nav class="bg-white shadow-sm border-b border-gray-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <i class="fas fa-bell text-blue-600 text-xl mr-3"></i>
                        <h1 class="text-xl font-semibold text-gray-900">Alert Testing Center</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="{{ route('dashboard') }}" class="text-gray-600 hover:text-blue-600 transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Back to Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Test Controls -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-4">🧪 Alert Testing Tools</h2>
                <p class="text-gray-600 mb-6">Use these tools to test the alert system functionality. Emails will be sent to the configured admin email address.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Simulate Critical Alert -->
                    <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-exclamation-triangle text-red-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-red-900">Simulate Critical Alert</h3>
                        </div>
                        <p class="text-red-700 text-sm mb-4">Triggers a high-priority alert that will send an immediate email notification.</p>
                        <button 
                            onclick="simulateAlert('critical')"
                            class="w-full bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors"
                        >
                            Trigger Critical Alert
                        </button>
                    </div>

                    <!-- Monitor All Servers -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-search text-blue-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-blue-900">Monitor All Servers</h3>
                        </div>
                        <p class="text-blue-700 text-sm mb-4">Checks all servers against their thresholds and triggers alerts if needed.</p>
                        <button 
                            onclick="monitorServers()"
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-lg hover:bg-blue-700 transition-colors"
                        >
                            Run Monitoring Check
                        </button>
                    </div>

                    <!-- Manual Alert -->
                    <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                        <div class="flex items-center mb-3">
                            <i class="fas fa-plus-circle text-green-600 text-xl mr-3"></i>
                            <h3 class="text-lg font-semibold text-green-900">Manual Test Alert</h3>
                        </div>
                        <p class="text-green-700 text-sm mb-4">Create a custom test alert with specific parameters.</p>
                        <button 
                            onclick="showManualAlert()"
                            class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors"
                        >
                            Create Test Alert
                        </button>
                    </div>
                </div>

                <!-- Status Display -->
                <div id="status-display" class="mt-6 p-4 rounded-lg hidden">
                    <div class="flex items-center">
                        <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600 mr-3"></div>
                        <span class="text-gray-700">Processing request...</span>
                    </div>
                </div>

                <!-- Results Display -->
                <div id="results-display" class="mt-6 hidden">
                    <!-- Results will be displayed here -->
                </div>
            </div>

            <!-- Email Configuration Info -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mb-8">
                <div class="flex items-start">
                    <i class="fas fa-envelope text-yellow-600 text-xl mr-3 mt-1"></i>
                    <div>
                        <h3 class="text-lg font-semibold text-yellow-900 mb-2">📧 Email Configuration</h3>
                        <p class="text-yellow-800 text-sm mb-3">
                            <strong>Important:</strong> To receive email alerts, please update your email configuration in the <code>.env</code> file:
                        </p>
                        <pre class="bg-yellow-100 text-yellow-900 p-3 rounded text-xs overflow-x-auto"><code>MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
ADMIN_EMAIL=your-email@gmail.com</code></pre>
                        <p class="text-yellow-800 text-sm mt-3">
                            Current admin email: <strong>{{ config('mail.admin_email', 'Not configured') }}</strong>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Alerts Table -->
            @livewire('alerts-table')
        </div>
    </div>

    @livewireScripts

    <script>
        async function simulateAlert(type) {
            showStatus('Simulating ' + type + ' alert...');
            
            try {
                const response = await fetch('/test-alerts/simulate', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ type: type })
                });
                
                const result = await response.json();
                showResults(result, response.ok);
            } catch (error) {
                showResults({ error: 'Failed to simulate alert: ' + error.message }, false);
            }
        }

        async function monitorServers() {
            showStatus('Monitoring all servers...');
            
            try {
                const response = await fetch('/test-alerts/monitor', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    }
                });
                
                const result = await response.json();
                showResults(result, response.ok);
            } catch (error) {
                showResults({ error: 'Failed to monitor servers: ' + error.message }, false);
            }
        }

        function showManualAlert() {
            // For now, just use the simulate function
            simulateAlert('manual');
        }

        function showStatus(message) {
            const statusDiv = document.getElementById('status-display');
            const resultsDiv = document.getElementById('results-display');
            
            statusDiv.querySelector('span').textContent = message;
            statusDiv.classList.remove('hidden');
            resultsDiv.classList.add('hidden');
        }

        function showResults(result, success) {
            const statusDiv = document.getElementById('status-display');
            const resultsDiv = document.getElementById('results-display');
            
            statusDiv.classList.add('hidden');
            
            const bgColor = success ? 'bg-green-50 border-green-200' : 'bg-red-50 border-red-200';
            const textColor = success ? 'text-green-800' : 'text-red-800';
            const icon = success ? 'fas fa-check-circle text-green-600' : 'fas fa-exclamation-circle text-red-600';
            
            resultsDiv.innerHTML = `
                <div class="rounded-lg border p-4 ${bgColor}">
                    <div class="flex items-start">
                        <i class="${icon} text-xl mr-3 mt-1"></i>
                        <div class="flex-1">
                            <h4 class="font-semibold ${textColor} mb-2">
                                ${success ? 'Success!' : 'Error'}
                            </h4>
                            <pre class="${textColor} text-sm whitespace-pre-wrap">${JSON.stringify(result, null, 2)}</pre>
                        </div>
                    </div>
                </div>
            `;
            
            resultsDiv.classList.remove('hidden');
            
            // Refresh the alerts table
            setTimeout(() => {
                Livewire.dispatch('refresh');
            }, 1000);
        }

        // Add CSRF token to page
        document.addEventListener('DOMContentLoaded', function() {
            if (!document.querySelector('meta[name="csrf-token"]')) {
                const meta = document.createElement('meta');
                meta.name = 'csrf-token';
                meta.content = '{{ csrf_token() }}';
                document.head.appendChild(meta);
            }
        });
    </script>
</body>
</html>
