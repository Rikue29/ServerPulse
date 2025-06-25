<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Simple Alert Test</title>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        body { font-family: Arial, sans-serif; margin: 2rem; }
        .container { max-width: 800px; margin: 0 auto; }
        .alert-item { padding: 1rem; border: 1px solid #ddd; margin: 0.5rem 0; border-radius: 0.25rem; display: flex; justify-content: space-between; align-items: center; }
        .btn { background: #10b981; color: white; padding: 0.5rem 1rem; border: none; border-radius: 0.25rem; cursor: pointer; }
        .btn:hover { background: #059669; }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .status { background: #dbeafe; padding: 1rem; border-radius: 0.25rem; margin-bottom: 1rem; }
        .toast { position: fixed; bottom: 1rem; right: 1rem; background: #10b981; color: white; padding: 1rem; border-radius: 0.25rem; z-index: 1000; }
    </style>
    @livewireStyles
</head>
<body>
    <div class="container">
        <h1>Simple Alert Test</h1>
        @livewire('simple-alert-test')
    </div>
    
    <!-- Simple Toast Container -->
    <div id="toast-container"></div>
    
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
            }, 3000);
        }
        
        // Livewire event listeners
        document.addEventListener('livewire:initialized', () => {
            console.log('Livewire initialized in simple test');
            
            Livewire.on('show-toast', (event) => {
                console.log('Toast event received in simple test:', event);
                showToast(event.type, event.title, event.message);
            });
        });
        
        // Debug
        console.log('Simple test page loaded');
    </script>
</body>
</html>
