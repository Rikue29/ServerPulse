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
        <h1 class="text-3xl font-bold mb-6 text-gray-900">Test Alert Resolution</h1>
        
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Server</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($alerts as $alert)
                    <tr id="alert-row-{{ $alert->id }}" class="{{ $alert->status === 'resolved' ? 'bg-gray-100 opacity-60' : '' }}">
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $alert->status === 'resolved' ? 'text-gray-500' : 'text-gray-900' }}">
                            {{ $alert->id }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $alert->status === 'resolved' ? 'text-gray-500' : 'text-gray-900' }}">
                            {{ $alert->server->name ?? 'Unknown' }}
                        </td>
                        <td class="px-6 py-4 text-sm {{ $alert->status === 'resolved' ? 'text-gray-500 line-through' : 'text-gray-900' }}">
                            {{ $alert->alert_message }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $alert->status === 'resolved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ ucfirst($alert->status) }}
                                @if($alert->status === 'resolved')
                                    ✓
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm {{ $alert->status === 'resolved' ? 'text-gray-500' : 'text-gray-900' }}">
                            {{ number_format($alert->metric_value, 1) }}%
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($alert->status === 'resolved')
                                <span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-xs rounded-md">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Resolved
                                </span>
                            @else
                                <button 
                                    onclick="resolveAlert({{ $alert->id }})"
                                    class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-xs rounded-md hover:bg-green-700 transition-colors duration-200"
                                    id="resolve-btn-{{ $alert->id }}"
                                >
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                    </svg>
                                    Resolve
                                </button>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $alerts->links() }}
        </div>

        <!-- Toast Notification -->
        <div id="toast" class="fixed top-4 right-4 z-50 hidden bg-white shadow-lg rounded-lg p-4 border">
            <div id="toast-content" class="flex items-center">
                <div id="toast-icon" class="mr-3"></div>
                <div id="toast-message"></div>
            </div>
        </div>
    </div>

    <script>
        function resolveAlert(alertId) {
            const button = document.getElementById(`resolve-btn-${alertId}`);
            const originalText = button.innerHTML;
            
            // Show loading state
            button.innerHTML = '<svg class="animate-spin h-3 w-3 mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 714 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>Resolving...';
            button.disabled = true;
            
            fetch(`/test-resolve/${alertId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the row to show resolved state
                    const row = document.getElementById(`alert-row-${alertId}`);
                    row.classList.add('bg-gray-100', 'opacity-60');
                    
                    // Update all text elements to gray
                    const textElements = row.querySelectorAll('td');
                    textElements.forEach(td => {
                        if (td.classList.contains('text-gray-900')) {
                            td.classList.remove('text-gray-900');
                            td.classList.add('text-gray-500');
                        }
                    });
                    
                    // Update message to have line-through
                    const messageCell = textElements[2];
                    messageCell.classList.add('line-through');
                    
                    // Update status badge
                    const statusCell = textElements[3];
                    statusCell.innerHTML = '<span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Resolved ✓</span>';
                    
                    // Replace button with resolved badge
                    const actionCell = textElements[5];
                    actionCell.innerHTML = '<span class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 text-xs rounded-md"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Resolved</span>';
                    
                    showToast('success', 'Alert resolved successfully!');
                } else {
                    showToast('error', data.message || 'Failed to resolve alert');
                    
                    // Restore button
                    button.innerHTML = originalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('error', 'Network error occurred while resolving alert');
                
                // Restore button
                button.innerHTML = originalText;
                button.disabled = false;
            });
        }
        
        function showToast(type, message) {
            const toast = document.getElementById('toast');
            const toastIcon = document.getElementById('toast-icon');
            const toastMessage = document.getElementById('toast-message');
            
            // Set icon and colors based on type
            if (type === 'success') {
                toastIcon.innerHTML = '<svg class="h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                toast.classList.add('border-green-200');
            } else {
                toastIcon.innerHTML = '<svg class="h-5 w-5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
                toast.classList.add('border-red-200');
            }
            
            toastMessage.textContent = message;
            toast.classList.remove('hidden');
            
            // Auto hide after 3 seconds
            setTimeout(() => {
                toast.classList.add('hidden');
                toast.classList.remove('border-green-200', 'border-red-200');
            }, 3000);
        }
    </script>
</body>
</html>
