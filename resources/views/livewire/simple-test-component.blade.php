<div class="p-4 bg-blue-100 border border-blue-400 rounded mt-4">
    <h3 class="font-bold">Livewire Test Component</h3>
    <p>{{ $message }}</p>
    <p>Counter: {{ $counter }}</p>
    
    <div class="mt-2 space-x-2">
        <button wire:click="increment" class="px-3 py-1 bg-blue-500 text-white rounded text-sm">
            Increment (+)
        </button>
        <button wire:click="testMethod" class="px-3 py-1 bg-green-500 text-white rounded text-sm">
            Test Method
        </button>
    </div>
    
    <div class="mt-2 text-xs text-gray-600">
        <p>If these buttons work, Livewire is functioning correctly.</p>
        <p>Check the browser console and Laravel logs for debugging info.</p>
    </div>
</div>
