<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Add New Server') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form action="{{ route('servers.store') }}" method="POST">
                    @csrf
                    <div class="p-6 space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Server Name</label>
                            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="{{ old('name') }}" required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="ip_address" class="block text-sm font-medium text-gray-700">IP Address</label>
                            <input type="text" name="ip_address" id="ip_address" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="{{ old('ip_address') }}" required>
                            @error('ip_address')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="location" class="block text-sm font-medium text-gray-700">Location</label>
                            <input type="text" name="location" id="location" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm" value="{{ old('location') }}">
                            @error('location')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="environment" class="block text-sm font-medium text-gray-700">Server Type</label>
                            <select name="environment" id="environment" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="prod" {{ old('environment') == 'prod' ? 'selected' : '' }}>Production</option>
                                <option value="staging" {{ old('environment') == 'staging' ? 'selected' : '' }}>Staging</option>
                                <option value="dev" {{ old('environment') == 'dev' ? 'selected' : '' }}>Development</option>
                            </select>
                            @error('environment')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6 border-t border-gray-200 pt-6">
                            <h3 class="text-lg font-medium text-gray-900">SSH Connection Details</h3>
                            <p class="mt-1 text-sm text-gray-500">Required for remote server monitoring.</p>
                            
                            <div class="mt-6 grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-6">
                                <div class="sm:col-span-3">
                                    <label for="ssh_user" class="block text-sm font-medium text-gray-700">SSH Username</label>
                                    <input type="text" name="ssh_user" id="ssh_user" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                           value="{{ old('ssh_user') }}">
                                    @error('ssh_user')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="ssh_port" class="block text-sm font-medium text-gray-700">SSH Port</label>
                                    <input type="number" name="ssh_port" id="ssh_port" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                           value="{{ old('ssh_port', 22) }}">
                                    @error('ssh_port')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="ssh_password" class="block text-sm font-medium text-gray-700">SSH Password</label>
                                    <input type="password" name="ssh_password" id="ssh_password" 
                                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                           autocomplete="new-password">
                                    @error('ssh_password')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div class="sm:col-span-6">
                                    <label for="ssh_key" class="block text-sm font-medium text-gray-700">SSH Private Key</label>
                                    <textarea name="ssh_key" id="ssh_key" rows="4"
                                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm"
                                              placeholder="Paste your private key here (optional)">{{ old('ssh_key') }}</textarea>
                                    <p class="mt-2 text-sm text-gray-500">Either provide a password or a private key for SSH authentication.</p>
                                    @error('ssh_key')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 flex items-center justify-end space-x-3">
                        <a href="{{ route('servers.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Cancel
                        </a>
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Add Server
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout> 