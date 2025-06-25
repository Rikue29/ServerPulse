<!-- Toast Container -->
<div x-data="toastManager()" x-show="toasts.length > 0" class="fixed bottom-4 right-4 z-[99999] space-y-2 shadow-2xl" x-init="window.toastManager = $data">
    <template x-for="toast in toasts" :key="toast.id">
        <div x-show="toast.show"
             x-transition:enter="transform ease-out duration-300 transition"
             x-transition:enter-start="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
             x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
             x-transition:leave="transition ease-in duration-100"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden">
            <div class="p-4">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <template x-if="toast.type === 'success'">
                            <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </template>
                        <template x-if="toast.type === 'error'">
                            <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </template>
                        <template x-if="toast.type === 'warning'">
                            <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
                            </svg>
                        </template>
                        <template x-if="toast.type === 'info'">
                            <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </template>
                    </div>
                    <div class="ml-3 w-0 flex-1 pt-0.5">
                        <p class="text-sm font-medium text-gray-900" x-text="toast.title"></p>
                        <p class="mt-1 text-sm text-gray-500" x-text="toast.message"></p>
                    </div>
                    <div class="ml-4 flex-shrink-0 flex">
                        <button @click="removeToast(toast.id)" class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <span class="sr-only">Close</span>
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Progress bar -->
            <div class="bg-gray-200 h-1">
                <div class="h-1 bg-gradient-to-r transition-all duration-100 ease-linear"
                     :class="{
                         'from-green-400 to-green-600': toast.type === 'success',
                         'from-red-400 to-red-600': toast.type === 'error',
                         'from-yellow-400 to-yellow-600': toast.type === 'warning',
                         'from-blue-400 to-blue-600': toast.type === 'info'
                     }"
                     :style="`width: ${toast.progress}%`"></div>
            </div>
        </div>
    </template>
</div>

<script>
function toastManager() {
    return {
        toasts: [],
        nextId: 1,
        
        addToast(type, title, message, duration = 5000) {
            const id = this.nextId++;
            const toast = {
                id,
                type,
                title,
                message,
                show: true,
                progress: 100
            };
            
            this.toasts.push(toast);
            
            // Start progress animation
            const interval = setInterval(() => {
                toast.progress -= (100 / duration) * 100;
                if (toast.progress <= 0) {
                    clearInterval(interval);
                    this.removeToast(id);
                }
            }, 100);
            
            return id;
        },
        
        removeToast(id) {
            const index = this.toasts.findIndex(toast => toast.id === id);
            if (index > -1) {
                this.toasts[index].show = false;
                setTimeout(() => {
                    this.toasts.splice(index, 1);
                }, 100);
            }
        },
        
        success(title, message) {
            return this.addToast('success', title, message);
        },
        
        error(title, message) {
            return this.addToast('error', title, message);
        },
        
        warning(title, message) {
            return this.addToast('warning', title, message);
        },
        
        info(title, message) {
            return this.addToast('info', title, message);
        }
    }
}

// Global toast function
window.showToast = function(type, title, message) {
    if (window.toastManager) {
        window.toastManager[type](title, message);
    }
};

// Ensure Livewire and Alpine are available before setting up listeners
if (window.Livewire && window.Alpine) {
    Livewire.on('show-toast', (event) => {
        let data = event;
        if (Array.isArray(event)) data = event[0];
        if (window.toastManager && data && data.type) {
            window.toastManager[data.type](data.title, data.message);
        }
    });
}

// Fallback for older Livewire versions
document.addEventListener('DOMContentLoaded', function() {
    if (window.Livewire) {
        console.log('Setting up Livewire listeners on DOMContentLoaded');
        window.Livewire.on('show-toast', (event) => {
            console.log('Toast event received (fallback):', event);
            const { type, title, message } = event;
            window.showToast(type, title, message);
        });
    }
});
</script>
