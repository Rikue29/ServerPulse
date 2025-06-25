@extends('layouts.app')

@section('content')
    <!-- Header Section -->
    <div class="bg-white shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Help & Support</h1>
                    <p class="mt-2 text-gray-600">Find answers and get assistance with ServerPulse</p>
                </div>
                <div class="flex items-center space-x-2">
                    <div class="flex items-center text-sm text-blue-600">
                        <i class="fas fa-question-circle mr-2"></i>
                        Available 24/7
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Content Section -->
    <div class="min-h-screen bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Quick Actions Card -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-8">
                <h2 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                    <i class="fas fa-rocket text-blue-600 mr-2"></i>
                    Quick Actions
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('servers.create') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-blue-300 hover:bg-blue-50 transition-colors duration-200">
                        <i class="fas fa-plus-circle text-blue-600 mr-3 text-lg"></i>
                        <span class="font-medium text-gray-700">Add New Server</span>
                    </a>
                    <a href="{{ route('alerts.index') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-orange-300 hover:bg-orange-50 transition-colors duration-200">
                        <i class="fas fa-bell text-orange-600 mr-3 text-lg"></i>
                        <span class="font-medium text-gray-700">View Alerts</span>
                    </a>
                    <a href="{{ route('dashboard') }}" class="flex items-center p-4 border border-gray-200 rounded-lg hover:border-green-300 hover:bg-green-50 transition-colors duration-200">
                        <i class="fas fa-dashboard text-green-600 mr-3 text-lg"></i>
                        <span class="font-medium text-gray-700">Dashboard</span>
                    </a>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-medium text-gray-900 flex items-center">
                        <i class="fas fa-question-circle text-blue-600 mr-2"></i>
                        Frequently Asked Questions
                    </h2>
                </div>
                
                <div class="divide-y divide-gray-200">
                    <!-- FAQ Item 1 -->
                    <div class="px-6 py-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-server text-blue-500 mr-2"></i>
                            How do I add a new server?
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            To add a new server, navigate to the <span class="font-medium text-blue-600">Servers</span> page from the sidebar and click the <span class="font-medium text-blue-600">Add Server</span> button. You'll need to provide the server's name, IP address, and monitoring port. The system will automatically start monitoring once configured.
                        </p>
                    </div>

                    <!-- FAQ Item 2 -->
                    <div class="px-6 py-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-bell text-orange-500 mr-2"></i>
                            How do I resolve an alert?
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            Alerts can be resolved from the <span class="font-medium text-orange-600">Alerts</span> page. Click the <span class="font-medium text-orange-600">Resolve</span> button on the alert you wish to resolve. The alert will be removed from the active alerts list and marked as resolved in the system logs.
                        </p>
                    </div>

                    <!-- FAQ Item 3 -->
                    <div class="px-6 py-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-chart-line text-green-500 mr-2"></i>
                            How do I set up monitoring thresholds?
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            Monitoring thresholds are configured when adding a new server. You can set CPU, memory, and disk usage limits that will trigger alerts when exceeded. These can be modified later from the server details page.
                        </p>
                    </div>

                    <!-- FAQ Item 4 -->
                    <div class="px-6 py-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                            <i class="fas fa-envelope text-purple-500 mr-2"></i>
                            How can I contact support?
                        </h3>
                        <p class="text-gray-600 leading-relaxed">
                            For further assistance, please email our support team at 
                            <a href="mailto:support@serverpulse.com" class="text-purple-600 hover:text-purple-800 font-medium underline">support@serverpulse.com</a>. 
                            We typically respond within 24 hours during business days.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Contact Support Card -->
            <div class="mt-8 bg-gradient-to-r from-blue-600 to-purple-600 rounded-lg shadow-lg overflow-hidden">
                <div class="px-6 py-8 text-white">
                    <h2 class="text-2xl font-bold mb-2">Still need help?</h2>
                    <p class="text-blue-100 mb-4">Our support team is here to assist you with any questions or issues.</p>
                    <a href="mailto:support@serverpulse.com" 
                       class="inline-flex items-center px-6 py-3 bg-white text-blue-600 rounded-lg font-medium hover:bg-gray-50 transition-colors duration-200">
                        <i class="fas fa-envelope mr-2"></i>
                        Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
