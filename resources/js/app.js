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
            const badge = statusCell.querySelector('span');
            if (badge) {
                badge.textContent = (eventData.status || 'offline').charAt(0).toUpperCase() + (eventData.status || 'offline').slice(1);
                badge.className = 'px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' +
                    (eventData.status === 'online'
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800');
            }
            // Remove any previous uptime/downtime info
            let info = statusCell.querySelector('.server-uptime-info');
            if (info) info.remove();

            // Add new uptime/downtime info
            info = document.createElement('span');
            info.className = 'server-uptime-info text-xs text-gray-500 block';
            if (eventData.status === 'online' && eventData.current_uptime) {
                info.textContent = 'Uptime: ' + humanizeSeconds(eventData.current_uptime);
            } else if (eventData.status !== 'online' && eventData.current_downtime) {
                info.textContent = 'Downtime: ' + humanizeSeconds(eventData.current_downtime);
            }
            statusCell.appendChild(info);
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
    } else {
        console.warn('Could not find row for server_id:', eventData.server_id);
    }
});

console.log('Event listener for ServerStatusUpdated attached.');
