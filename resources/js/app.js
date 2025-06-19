import './bootstrap';

console.log('ServerPulse app.js loaded!');

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Laravel Echo + Pusher for real-time server status updates
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';
window.Pusher = Pusher;

console.log('Pusher object available:', window.Pusher);

if (!import.meta.env.VITE_PUSHER_APP_KEY) {
    console.error('VITE_PUSHER_APP_KEY is not set. Make sure it is in your .env file and you have run npm run dev.');
}
if (!import.meta.env.VITE_PUSHER_APP_CLUSTER) {
    console.error('VITE_PUSHER_APP_CLUSTER is not set. Make sure it is in your .env file and you have run npm run dev.');
}

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || '9de0f03e2175961b83d0', // Fallback only for extreme cases
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap1', // Fallback only for extreme cases
    forceTLS: true,
    // wsHost: import.meta.env.VITE_PUSHER_HOST || `ws-${import.meta.env.VITE_PUSHER_APP_CLUSTER}.pusher.com`,
    // wsPort: import.meta.env.VITE_PUSHER_PORT || 80,
    // wssPort: import.meta.env.VITE_PUSHER_PORT || 443,
    // enabledTransports: ['ws', 'wss'], // You might need to specify transports
});

console.log('Echo initialized:', window.Echo);

window.Echo.connector.pusher.connection.bind('state_change', function(states) {
    // states = {previous: 'oldState', current: 'newState'}
    console.log("Pusher connection state changed from", states.previous, "to", states.current);
});

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('Pusher connected successfully!');
});

window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('Pusher connection error:', err);
    if (err.error && err.error.data && err.error.data.code === 4004) {
        console.error('Pusher error 4004: App key not found or invalid. Check VITE_PUSHER_APP_KEY.');
    } else if (err.error && err.error.data && err.error.data.code >= 4000 && err.error.data.code <= 4099) {
        console.error('Pusher authentication/configuration error. Check your Pusher App ID, Key, and Secret, and Cluster in .env and config/broadcasting.php.');
    }
});


const channel = window.Echo.channel('server-status');
console.log('Subscribing to server-status channel:', channel);

channel.subscribed(() => {
    console.log('Successfully subscribed to server-status channel!');

    // Add a catch-all event listener directly on the Pusher channel object
    if (window.Echo && window.Echo.connector && window.Echo.connector.pusher) {
        const pusherChannel = window.Echo.connector.pusher.channel('server-status');
        if (pusherChannel) {
            pusherChannel.bind_global((eventName, data) => {
                console.log('[CATCH-ALL] Event received on server-status:', eventName, 'Data:', data);
            });
            console.log('[CATCH-ALL] Global event listener bound to Pusher channel object.');
        } else {
            console.error('[CATCH-ALL] Could not get Pusher channel object from Echo.');
        }
    } else {
        console.error('[CATCH-ALL] Echo or Pusher connector not available for global listener.');
    }
})
.error((error) => {
    console.error('Error subscribing to server-status channel:', error);
})
.listen('.server.status.updated', (e) => { // Ensure leading dot for exact match
    console.log('EVENT RECEIVED via Echo.listen for .server.status.updated. Full event object (e):', JSON.parse(JSON.stringify(e)));

    const eventData = e.status; // Directly access e.status based on CATCH-ALL log

    console.log('Attempting to use data for UI update (from e.status):', JSON.parse(JSON.stringify(eventData)));

    if (!eventData || typeof eventData !== 'object') {
        console.error('Processed event data is missing or not an object!', eventData);
        return;
    }

    if (!eventData.server_id) {
        console.error('server_id is missing in processed event data!', eventData);
        return;
    }

    // Check if we're on the analytics page
    const isAnalyticsPage = window.location.pathname.includes('/analytics');
    
    if (isAnalyticsPage) {
        // Update analytics page summary cards
        updateAnalyticsPage(eventData);
    } else {
        // Update servers page server rows
        updateServersPage(eventData);
    }
});

// Global variables to track last update for throughput calculation
let lastUpdateTime = {};
let lastNetworkBytes = {};

// Function to update analytics page summary cards
function updateAnalyticsPage(eventData) {
    console.log('🔄 Updating analytics page for server_id:', eventData.server_id);
    console.log('📊 Event data received:', eventData);
    
    const serverId = eventData.server_id;
    const currentTime = Date.now();
    const currentTotalBytes = (eventData.network_rx || 0) + (eventData.network_tx || 0);
    
    // Calculate actual network throughput if we have previous data
    let actualThroughput = 0;
    if (lastUpdateTime[serverId] && lastNetworkBytes[serverId]) {
        const timeDiff = (currentTime - lastUpdateTime[serverId]) / 1000; // seconds
        const bytesDiff = Math.max(0, currentTotalBytes - lastNetworkBytes[serverId]);
        actualThroughput = timeDiff > 0 ? (bytesDiff / timeDiff / 1024) : 0; // KB/s
    }
    
    // Store current values for next calculation
    lastUpdateTime[serverId] = currentTime;
    lastNetworkBytes[serverId] = currentTotalBytes;
    
    // Update CPU Usage card
    const cpuCard = document.querySelector('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6');
    if (cpuCard && cpuCard.querySelector('h3').textContent.includes('CPU Usage')) {
        const cpuValue = cpuCard.querySelector('.text-3xl.font-bold.text-gray-900');
        if (cpuValue) {
            const newCpuValue = parseFloat(eventData.cpu_usage || 0).toFixed(1) + '%';
            cpuValue.textContent = newCpuValue;
            console.log('✅ Updated CPU Usage to:', newCpuValue);
        }
    }
    
    // Update Memory Usage card
    const memoryCards = document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6');
    memoryCards.forEach(card => {
        const title = card.querySelector('h3');
        if (title && title.textContent.includes('Memory Usage')) {
            const memoryValue = card.querySelector('.text-3xl.font-bold.text-gray-900');
            if (memoryValue) {
                const newMemoryValue = parseFloat(eventData.ram_usage || 0).toFixed(1) + '%';
                memoryValue.textContent = newMemoryValue;
                console.log('✅ Updated Memory Usage to:', newMemoryValue);
            }
        }
    });
    
    // Update Storage Usage card
    memoryCards.forEach(card => {
        const title = card.querySelector('h3');
        if (title && title.textContent.includes('Storage Usage')) {
            const storageValue = card.querySelector('.text-3xl.font-bold.text-gray-900');
            if (storageValue) {
                const newStorageValue = parseFloat(eventData.disk_usage || 0).toFixed(1) + '%';
                storageValue.textContent = newStorageValue;
                console.log('✅ Updated Storage Usage to:', newStorageValue);
            }
        }
    });
    
    // Update Network Activity card
    memoryCards.forEach(card => {
        const title = card.querySelector('h3');
        if (title && title.textContent.includes('Network Activity')) {
            const networkValue = card.querySelector('.text-3xl.font-bold.text-gray-900');
            const networkBar = card.querySelector('.bg-green-500.h-2.rounded-full');
            if (networkValue) {
                // Calculate network activity level (0-100)
                const networkActivity = calculateNetworkActivity(eventData);
                networkValue.textContent = networkActivity;
                if (networkBar) {
                    networkBar.style.width = networkActivity + '%';
                }
                console.log('✅ Updated Network Activity to:', networkActivity);
            }
        }
    });
    
    // Update Response Time card
    memoryCards.forEach(card => {
        const title = card.querySelector('h3');
        if (title && title.textContent.includes('Response Time')) {
            const responseValue = card.querySelector('.text-3xl.font-bold.text-gray-900');
            if (responseValue) {
                const newResponseValue = parseFloat(eventData.response_time || 0).toFixed(1);
                responseValue.textContent = newResponseValue;
                console.log('✅ Updated Response Time to:', newResponseValue + 'ms');
            }
        }
    });
    
    // Update Network Throughput card
    memoryCards.forEach(card => {
        const title = card.querySelector('h3');
        if (title && title.textContent.includes('Network Throughput')) {
            const throughputValue = card.querySelector('.text-3xl.font-bold.text-gray-900');
            if (throughputValue) {
                // Use actual calculated throughput
                const throughputKBps = actualThroughput.toFixed(1);
                throughputValue.textContent = throughputKBps;
                console.log('✅ Updated Network Throughput to:', throughputKBps + ' KB/s (actual)');
            }
        }
    });
    
    // Update System Uptime/Downtime card
    memoryCards.forEach(card => {
        const title = card.querySelector('h3');
        if (title && (title.textContent.includes('System Uptime') || title.textContent.includes('System Downtime'))) {
            const uptimeValue = card.querySelector('.text-3xl.font-bold');
            const uptimeLabel = card.querySelector('.text-xs.text-gray-500.mt-1');
            const icon = card.querySelector('i');
            
            if (eventData.status === 'online') {
                title.textContent = 'System Uptime';
                if (uptimeValue) {
                    const uptimeText = humanizeSeconds(eventData.current_uptime || 0);
                    uptimeValue.textContent = uptimeText;
                    uptimeValue.className = 'text-3xl font-bold text-gray-900';
                    console.log('✅ Updated System Uptime to:', uptimeText);
                }
                if (uptimeLabel) uptimeLabel.textContent = 'Current Uptime';
                if (icon) icon.className = 'fas fa-server text-blue-500';
            } else {
                title.textContent = 'System Downtime';
                if (uptimeValue) {
                    const downtimeText = humanizeSeconds(eventData.current_downtime || 0);
                    uptimeValue.textContent = downtimeText;
                    uptimeValue.className = 'text-3xl font-bold text-red-900';
                    console.log('✅ Updated System Downtime to:', downtimeText);
                }
                if (uptimeLabel) uptimeLabel.textContent = 'Current Downtime';
                if (icon) icon.className = 'fas fa-server text-red-500';
            }
        }
    });
    
    console.log('🎉 Analytics page updated successfully for server_id:', eventData.server_id);
}

// Function to update servers page server rows
function updateServersPage(eventData) {
    const row = document.getElementById('server-row-' + eventData.server_id);
    if (row) {
        console.log('Found row for server_id:', eventData.server_id, row);
        
        // Update CPU
        const cpuCell = row.querySelector('[data-col="cpu"]');
        if (cpuCell) {
            console.log('Updating CPU for server_id:', eventData.server_id, 'to', eventData.cpu_usage);
            const cpuBar = cpuCell.querySelector('.bg-blue-600');
            const cpuText = cpuCell.querySelector('span');
            if (cpuBar) cpuBar.style.width = eventData.cpu_usage + '%';
            if (cpuText) cpuText.textContent = parseFloat(eventData.cpu_usage).toFixed(1) + '%';
        } else {
            console.warn('CPU cell not found for server_id:', eventData.server_id);
        }
        
        // Update RAM
        const ramCell = row.querySelector('[data-col="ram"]');
        if (ramCell) {
            console.log('Updating RAM for server_id:', eventData.server_id, 'to', eventData.ram_usage);
            const ramBar = ramCell.querySelector('.bg-blue-600');
            const ramText = ramCell.querySelector('span');
            if (ramBar) ramBar.style.width = eventData.ram_usage + '%';
            if (ramText) ramText.textContent = parseFloat(eventData.ram_usage).toFixed(1) + '%';
        } else {
            console.warn('RAM cell not found for server_id:', eventData.server_id);
        }
        
        // Update Disk
        const diskCell = row.querySelector('[data-col="disk"]');
        if (diskCell) {
            console.log('Updating Disk for server_id:', eventData.server_id, 'to', eventData.disk_usage);
            const diskBar = diskCell.querySelector('.bg-blue-600');
            const diskText = diskCell.querySelector('span');
            if (diskBar) diskBar.style.width = eventData.disk_usage + '%';
            if (diskText) diskText.textContent = parseFloat(eventData.disk_usage).toFixed(1) + '%';
        } else {
            console.warn('Disk cell not found for server_id:', eventData.server_id);
        }
        
        // Update Status and Uptime/Downtime
        const statusCell = row.querySelector('[data-col="status"]');
        if (statusCell) {
            // Update status badge
            const badge = statusCell.querySelector('span:not(.server-uptime-info)');
            if (badge) {
                badge.textContent = (eventData.status || 'offline').charAt(0).toUpperCase() + (eventData.status || 'offline').slice(1);
                badge.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' +
                    (eventData.status === 'online'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800');
            }

            // Update Uptime/Downtime info
            const infoDiv = statusCell.querySelector('.server-uptime-info');
            if(infoDiv) {
                if (eventData.status === 'online') {
                    // Use current_uptime for online servers
                    infoDiv.textContent = 'Uptime: ' + humanizeSeconds(eventData.current_uptime);
                } else {
                    // For offline servers, calculate and display the downtime.
                    infoDiv.textContent = 'Downtime: ' + humanizeSeconds(eventData.current_downtime);
                }
            }
        }
    } else {
        console.warn('Could not find row for server_id:', eventData.server_id);
    }
}

// Helper function to calculate network activity level
function calculateNetworkActivity(eventData) {
    // Calculate network activity based on network bytes
    const totalBytes = (eventData.network_rx || 0) + (eventData.network_tx || 0);
    
    console.log('🔍 Network Activity Calculation:', {
        network_rx: eventData.network_rx,
        network_tx: eventData.network_tx,
        totalBytes: totalBytes
    });
    
    if (totalBytes > 1000000) { // > 1MB
        return 100;
    } else if (totalBytes > 100000) { // > 100KB
        return 60;
    } else if (totalBytes > 10000) { // > 10KB
        return 30;
    } else {
        return 0;
    }
}

// Helper to humanize seconds (simple version)
function humanizeSeconds(seconds) {
    seconds = parseInt(seconds, 10);
    if (isNaN(seconds) || seconds < 1) return '0s';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return [
        h ? h + 'h' : '',
        m ? m + 'm' : '',
        s ? s + 's' : ''
    ].filter(Boolean).join(' ');
}

console.log('Event listener for ServerStatusUpdated attached.');
