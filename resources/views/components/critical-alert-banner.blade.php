<div id="critical-alert-banner" 
     style="display:none; position:fixed; top:20px; left:50%; transform:translateX(-50%); z-index:9999; max-width:600px; width:90%;" 
     class="bg-gradient-to-r from-red-600 to-red-700 text-white rounded-xl shadow-2xl border border-red-500">
    <div class="px-6 py-4 flex items-center justify-between">
        <div class="flex items-center">
            <svg class="w-6 h-6 text-red-200 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
            </svg>
            <div>
                <p class="font-semibold text-sm">Critical System Alert</p>
                <p id="critical-alert-banner-message" class="text-red-100 text-sm">Critical alert detected!</p>
            </div>
        </div>
        <button onclick="document.getElementById('critical-alert-banner').style.display='none'" 
                class="ml-4 bg-white/20 hover:bg-white/30 text-white rounded-lg px-3 py-1.5 text-sm font-medium transition-colors duration-200 focus:outline-none focus:ring-2 focus:ring-white/50">
            Dismiss
        </button>
    </div>
</div>
