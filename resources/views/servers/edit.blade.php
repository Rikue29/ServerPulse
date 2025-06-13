@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-900">Edit Server</h1>
                <p class="text-sm text-gray-500 mt-1">Update server configuration and monitoring settings</p>
            </div>
            <a href="{{ route('servers.index') }}" 
               class="inline-flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Servers
            </a>
        </div>
    </div>

    <!-- Content Area -->
    <div class="p-6">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-edit text-blue-600 mr-2"></i>
                        Server Configuration
                    </h3>
                </div>
                
                <form action="{{ route('servers.update', $server->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="p-6 space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Server Name</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="{{ old('name', $server->name) }}" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="ip_address" class="block text-sm font-medium text-gray-700">IP Address</label>
                            <input type="text" name="ip_address" id="ip_address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="{{ old('ip_address', $server->ip_address) }}" required>
                            @error('ip_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                            <input type="text" name="location" id="location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="{{ old('location', $server->location) }}">
                            @error('location')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="environment" class="block text-sm font-medium text-gray-700">Server Type</label>
                            <select name="environment" id="environment" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="prod" {{ old('environment', $server->environment) == 'prod' ? 'selected' : '' }}>Production</option>
                                <option value="staging" {{ old('environment', $server->environment) == 'staging' ? 'selected' : '' }}>Staging</option>
                                <option value="dev" {{ old('environment', $server->environment) == 'dev' ? 'selected' : '' }}>Development</option>
                            </select>
                            @error('environment')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 flex items-center justify-end space-x-3">
                        <a href="{{ route('servers.index') }}" 
                           class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-times mr-2"></i>
                            Cancel
                        </a>
                        <button type="submit" 
                                class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                            <i class="fas fa-save mr-2"></i>
                            Update Server
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 