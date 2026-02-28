@extends('layouts.app')

@section('content')
<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Livewire Test</h1>
    
    @livewire('dashboard')
    
    <div class="mt-4 p-4 bg-gray-100 rounded">
        <h2 class="font-bold mb-2">Test Instructions:</h2>
        <ol class="list-decimal list-inside space-y-1">
            <li>Click "Test Livewire" button - should show alert</li>
            <li>Click "Test Add Server 17" button - should add server to selection</li>
            <li>Click on server rows - should toggle selection</li>
            <li>Check browser console for debugging info</li>
        </ol>
    </div>
</div>
@endsection

