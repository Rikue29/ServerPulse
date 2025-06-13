@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Add New Server</h1>
                <p class="text-sm text-gray-500 mt-1">Configure a new server for monitoring</p>
            </div>
            <a href="{{ route('servers.index') }}" 
               class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Back to Servers
            </a>
        </div>
    </div>

    <!-- Content -->
    <div class="p-6">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <form action="{{ route('servers.store') }}" method="POST" class="space-y-6">
                    @csrf
                    <!-- Server Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Server Name</label>
                        <input type="text" name="name" id="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ old('name') }}" placeholder="Enter server name" required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- IP Address -->
                    <div>
                        <label for="ip_address" class="block text-sm font-medium text-gray-700 mb-2">IP Address</label>
                        <input type="text" name="ip_address" id="ip_address" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ old('ip_address') }}" placeholder="192.168.1.100" required>
                        @error('ip_address')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Location -->
                    <div>
                        <label for="location" class="block text-sm font-medium text-gray-700 mb-2">Location</label>
                        <input type="text" name="location" id="location" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ old('location') }}" placeholder="Data Center / Office">
                        @error('location')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Environment -->
                    <div>
                        <label for="environment" class="block text-sm font-medium text-gray-700 mb-2">Environment</label>
                        <select name="environment" id="environment" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <option value="prod" {{ old('environment') == 'prod' ? 'selected' : '' }}>Production</option>
                            <option value="staging" {{ old('environment') == 'staging' ? 'selected' : '' }}>Staging</option>
                            <option value="dev" {{ old('environment') == 'dev' ? 'selected' : '' }}>Development</option>
                        </select>
                        @error('environment')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- SSH User -->
                    <div>
                        <label for="ssh_user" class="block text-sm font-medium text-gray-700 mb-2">SSH Username</label>
                        <input type="text" name="ssh_user" id="ssh_user" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ old('ssh_user') }}" placeholder="ubuntu, root, admin">
                        @error('ssh_user')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- SSH Port -->
                    <div>
                        <label for="ssh_port" class="block text-sm font-medium text-gray-700 mb-2">SSH Port</label>
                        <input type="number" name="ssh_port" id="ssh_port" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ old('ssh_port', 22) }}" min="1" max="65535">
                        @error('ssh_port')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- SSH Password -->
                    <div>
                        <label for="ssh_password" class="block text-sm font-medium text-gray-700 mb-2">SSH Password</label>
                        <input type="password" name="ssh_password" id="ssh_password" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" autocomplete="new-password" placeholder="Enter SSH password">
                        @error('ssh_password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- SSH Key -->
                    <div>
                        <label for="ssh_key" class="block text-sm font-medium text-gray-700 mb-2">SSH Private Key (Alternative)</label>
                        <textarea name="ssh_key" id="ssh_key" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" placeholder="-----BEGIN PRIVATE KEY-----">{{ old('ssh_key') }}</textarea>
                        @error('ssh_key')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- CPU Threshold -->
                    <div>
                        <label for="cpu_threshold" class="block text-sm font-medium text-gray-700 mb-2">CPU Usage Threshold (%)</label>
                        <input type="number" name="cpu_threshold" id="cpu_threshold" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ old('cpu_threshold', 80) }}" min="1" max="100" step="1">
                        @error('cpu_threshold')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Memory Threshold -->
                    <div>
                        <label for="memory_threshold" class="block text-sm font-medium text-gray-700 mb-2">Memory Usage Threshold (%)</label>
                        <input type="number" name="memory_threshold" id="memory_threshold" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ old('memory_threshold', 85) }}" min="1" max="100" step="1">
                        @error('memory_threshold')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Disk Threshold -->
                    <div>
                        <label for="disk_threshold" class="block text-sm font-medium text-gray-700 mb-2">Disk Usage Threshold (%)</label>
                        <input type="number" name="disk_threshold" id="disk_threshold" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ old('disk_threshold', 90) }}" min="1" max="100" step="1">
                        @error('disk_threshold')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Load Threshold -->
                    <div>
                        <label for="load_threshold" class="block text-sm font-medium text-gray-700 mb-2">Load Average Threshold</label>
                        <input type="number" name="load_threshold" id="load_threshold" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500" value="{{ old('load_threshold', 2.0) }}" min="0.1" max="10" step="0.1">
                        @error('load_threshold')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-4 pt-6">
                        <a href="{{ route('servers.index') }}" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 transition-colors duration-200">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors duration-200">
                            <i class="fas fa-plus mr-2"></i>Add Server
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection