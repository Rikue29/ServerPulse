<div class="p-6">
    <h2 class="text-xl font-bold mb-4">Simple Alert Test</h2>
    
    <div class="mb-4 p-4 bg-blue-100 rounded">
        Status: {{ $message }}
    </div>
    
    <div class="space-y-2">
        @foreach($alerts as $alert)
        <div class="flex items-center justify-between p-3 border rounded">
            <div>
                <strong>Alert #{{ $alert->id }}</strong>
                <span class="text-gray-600">- {{ $alert->alert_message }}</span>
            </div>
            <button 
                wire:click="resolveAlert({{ $alert->id }})"
                class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700"
                wire:loading.attr="disabled"
            >
                <span wire:loading.remove wire:target="resolveAlert({{ $alert->id }})">Resolve</span>
                <span wire:loading wire:target="resolveAlert({{ $alert->id }})">Resolving...</span>
            </button>
        </div>
        @endforeach
        
        @if($alerts->count() === 0)
        <p class="text-gray-500">No triggered alerts found.</p>
        @endif
    </div>
</div>
