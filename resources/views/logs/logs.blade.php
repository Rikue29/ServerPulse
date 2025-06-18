@extends('layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="pt-1">
    <div class="max-w-7xl mx-auto px-4">
        <div class="mb-3">
            <h1 class="text-2xl font-semibold text-gray-900">System Logs</h1>
            <p class="text-sm text-gray-500 mt-1">Monitor and analyze system events</p>
        </div>

        <livewire:logs-table />
    </div>
</div>
@endsection