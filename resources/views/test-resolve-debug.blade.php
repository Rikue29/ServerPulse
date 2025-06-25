<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Test Resolve</title>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    @livewireStyles
</head>
<body>
    <div class="p-8">
        <h1>Test Alert Resolve</h1>
        
        <!-- Test Button -->
        <button onclick="testResolve()">Test Resolve Alert #1</button>
        
        <!-- Test Toast -->
        <button onclick="testToast()">Test Toast</button>
        
        <!-- Livewire Component -->
        @livewire('alerts-table')
    </div>
    
    <!-- Toast Component -->
    @include('components.toast')
    
    @livewireScripts
    
    <script>
        function testToast() {
            window.showToast('success', 'Test', 'This is a test toast message');
        }
        
        function testResolve() {
            fetch('/alerts/1/resolve', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                console.log('Response:', data);
                if (data.success) {
                    window.showToast('success', 'Success', data.message);
                } else {
                    window.showToast('error', 'Error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                window.showToast('error', 'Error', 'Network error occurred');
            });
        }
        
        // Debug Livewire
        document.addEventListener('livewire:initialized', () => {
            console.log('Livewire initialized');
        });
        
        document.addEventListener('livewire:navigating', () => {
            console.log('Livewire navigating');
        });
        
        document.addEventListener('livewire:navigated', () => {
            console.log('Livewire navigated');
        });
    </script>
</body>
</html>
