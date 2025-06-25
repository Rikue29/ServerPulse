<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Simple Alert Test</title>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .toast { background: green; color: white; padding: 1rem; margin: 1rem; border-radius: 0.5rem; }
        .alert-row { background: #f9f9f9; padding: 1rem; margin: 0.5rem; border: 1px solid #ddd; }
        .resolved { opacity: 0.5; background: #e5e5e5; }
        button { background: green; color: white; padding: 0.5rem 1rem; border: none; border-radius: 0.25rem; cursor: pointer; }
        button:hover { background: darkgreen; }
        button:disabled { opacity: 0.5; cursor: not-allowed; }
    </style>
    @livewireStyles
</head>
<body>
    <div class="container" style="max-width: 1200px; margin: 0 auto; padding: 2rem;">
        <h1>Simple Alert Test</h1>
        
        <!-- Test Alert Directly -->
        <div style="margin: 2rem 0;">
            <h2>Direct AJAX Test</h2>
            <button onclick="testDirectResolve(1)">Test Direct Resolve Alert #1</button>
            <button onclick="testToast()">Test Toast</button>
        </div>
        
        <!-- Livewire Component -->
        <div style="margin: 2rem 0;">
            <h2>Livewire Component Test</h2>
            @livewire('alerts-table')
        </div>
    </div>
    
    <!-- Simple Toast System -->
    <div id="toast-container" style="position: fixed; bottom: 1rem; right: 1rem; z-index: 1000;"></div>
    
    @livewireScripts
    
    <script>
        // Simple toast function
        function showToast(type, title, message) {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast';
            toast.innerHTML = `<strong>${title}</strong><br>${message}`;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.remove();
            }, 5000);
        }
        
        // Test direct resolve via AJAX
        function testDirectResolve(alertId) {
            console.log('Testing direct resolve for alert:', alertId);
            
            fetch(`/test-resolve/${alertId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                console.log('Response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Response data:', data);
                if (data.success) {
                    showToast('success', 'Success', data.message);
                } else {
                    showToast('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Error', 'Network error occurred');
            });
        }
        
        function testToast() {
            showToast('info', 'Test Toast', 'This is a test toast message');
        }
        
        // Livewire event listeners
        document.addEventListener('livewire:initialized', () => {
            console.log('Livewire initialized');
            
            // Listen for Livewire toast events
            Livewire.on('show-toast', (event) => {
                console.log('Livewire toast event:', event);
                showToast(event.type, event.title, event.message);
            });
        });
        
        // Global debugging
        window.addEventListener('error', (e) => {
            console.error('JavaScript error:', e.error);
        });
    </script>
</body>
</html>
