@extends('layouts.app')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="mx-auto max-w-7xl">
        <h1 class="text-2xl font-bold text-gray-800 mb-6">Settings</h1>
        
        <div class="bg-white rounded-lg shadow-sm p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- General Settings -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h2 class="text-lg font-medium text-gray-800 mb-4">General Settings</h2>
                    
                    <form class="space-y-4">
                        <div>
                            <label for="app_name" class="block text-sm font-medium text-gray-700">Application Name</label>
                            <input type="text" name="app_name" id="app_name" value="ServerPulse" 
                                   class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        </div>
                        
                        <div>
                            <label for="timezone" class="block text-sm font-medium text-gray-700">Timezone</label>
                            <select id="timezone" name="timezone" 
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <option>UTC</option>
                                <option>America/New_York</option>
                                <option>Europe/London</option>
                                <option>Asia/Tokyo</option>
                            </select>
                        </div>
                        
                        <div>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
                
                <!-- Notification Settings -->
                <div class="bg-white rounded-lg border border-gray-200 p-5">
                    <h2 class="text-lg font-medium text-gray-800 mb-4">Notification Settings</h2>
                    
                    <form class="space-y-4">
                        <div class="flex items-center justify-between">
                            <label for="email_notifications" class="text-sm font-medium text-gray-700">Email Notifications</label>
                            <div class="relative inline-block w-10 mr-2 align-middle select-none">
                                <input type="checkbox" id="email_notifications" name="email_notifications" checked
                                       class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer">
                                <label for="email_notifications" 
                                       class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                            </div>
                        </div>
                        
                        <div class="flex items-center justify-between">
                            <label for="slack_notifications" class="text-sm font-medium text-gray-700">Slack Notifications</label>
                            <div class="relative inline-block w-10 mr-2 align-middle select-none">
                                <input type="checkbox" id="slack_notifications" name="slack_notifications"
                                       class="toggle-checkbox absolute block w-6 h-6 rounded-full bg-white border-4 appearance-none cursor-pointer">
                                <label for="slack_notifications" 
                                       class="toggle-label block overflow-hidden h-6 rounded-full bg-gray-300 cursor-pointer"></label>
                            </div>
                        </div>
                        
                        <div>
                            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Custom styles for toggle switches -->
<style>
    .toggle-checkbox:checked {
        right: 0;
        border-color: #3B82F6;
    }
    .toggle-checkbox:checked + .toggle-label {
        background-color: #3B82F6;
    }
</style>
@endsection
