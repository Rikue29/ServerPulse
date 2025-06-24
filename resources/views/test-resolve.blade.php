<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Alert Resolution</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8">
        <h1 class="text-2xl font-bold mb-6">Test Alert Resolution</h1>
        
        <div id="alerts-list" class="space-y-4">
            @foreach(App\Models\Alert::with('server')->unresolved()->get() as $alert)
            <div id="alert-{{ $alert->id }}" class="bg-white p-4 rounded-lg shadow">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-medium">{{ $alert->alert_message }}</h3>
                        <p class="text-sm text-gray-600">Server: {{ $alert->server->name ?? 'Unknown' }}</p>
                        <p class="text-sm text-gray-600">Value: {{ $alert->metric_value }}%</p>
                    </div>
                    <button 
                        onclick="resolveAlert({{ $alert->id }})"
                        class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700 transition-colors"
                    >
                        Resolve
                    </button>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <script>
        function resolveAlert(alertId) {
            const button = event.target;
            const originalText = button.textContent;
            
            button.textContent = 'Resolving...';
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
                    // Remove the alert element
                    const alertElement = document.getElementById(`alert-${alertId}`);
                    if (alertElement) {
                        alertElement.style.transition = 'all 0.3s ease';
                        alertElement.style.opacity = '0';
                        alertElement.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            alertElement.remove();
                        }, 300);
                    }
                    
                    alert('Alert resolved successfully!');
                } else {
                    alert('Error: ' + (data.message || 'Failed to resolve alert'));
                    button.textContent = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Network error occurred');
                button.textContent = originalText;
                button.disabled = false;
            });
        }
    </script>
</body>
</html>
