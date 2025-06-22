import './bootstrap';

console.log('ServerPulse app.js loaded!');

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Helper to humanize seconds into a d/h/m/s format
function humanizeSeconds(seconds) {
    seconds = parseInt(seconds, 10);
    if (isNaN(seconds) || seconds < 1) return '0s';
    const d = Math.floor(seconds / 86400);
    const h = Math.floor((seconds % 86400) / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);
    return [d ? d + 'd' : '', h ? h + 'h' : '', m ? m + 'm' : '', s ? s + 's' : ''].filter(Boolean).join(' ') || '0s';
}

// Global object to hold uptime counters
window.serverUptimeTrackers = {};


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
});

console.log('Echo initialized:', window.Echo);

// Function to update the server status on the SERVERS page
function updateServerStatus(event) {
    const serverId = event.server_id;
    const serverRow = document.getElementById(`server-row-${serverId}`);
    if (!serverRow) return;

    const statusBadge = serverRow.querySelector('.status-badge');
    const uptimeInfo = serverRow.querySelector('.server-uptime-info');

    if (event.status === 'online') {
        // If we are not already tracking this server with a live timer, start one.
        if (!window.serverUptimeTrackers[serverId]) {
            console.log(`[Servers Page] Starting new uptime tracker for server ${serverId}.`);
            statusBadge.textContent = 'Online';
            statusBadge.className = 'status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
            uptimeInfo.classList.remove('text-red-600');
            uptimeInfo.classList.add('text-gray-600');

            if (typeof event.current_uptime === 'number') {
                const initialUptime = event.current_uptime;
                const tracker = {
                    startedAt: Date.now(),
                    initialUptime: initialUptime,
                    interval: setInterval(() => {
                        const elapsedSeconds = Math.floor((Date.now() - tracker.startedAt) / 1000);
                        uptimeInfo.textContent = `Uptime: ${humanizeSeconds(tracker.initialUptime + elapsedSeconds)}`;
                    }, 1000)
                };
                window.serverUptimeTrackers[serverId] = tracker;
            } else {
                uptimeInfo.textContent = `Uptime: ${event.system_uptime || '--'}`;
            }
        }
        // If a tracker already exists, we do nothing, allowing it to continue counting uninterrupted.

    } else { // Server is offline
        // If a timer was running for this server, stop it.
        if (window.serverUptimeTrackers[serverId]) {
            console.log(`[Servers Page] Clearing uptime tracker for offline server ${serverId}.`);
            clearInterval(window.serverUptimeTrackers[serverId].interval);
            delete window.serverUptimeTrackers[serverId];
        }

        statusBadge.textContent = 'Offline';
        statusBadge.className = 'status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
        uptimeInfo.textContent = `Downtime: ${event.formatted_downtime || '0s'}`;
        uptimeInfo.classList.remove('text-gray-600');
        uptimeInfo.classList.add('text-red-600');
    }
}

// Function to update the ANALYTICS page
function updateAnalyticsPage(event) {
    const serverSelector = document.getElementById('server_id');
    const selectedServerId = serverSelector ? parseInt(serverSelector.value, 10) : null;
    if (selectedServerId !== event.server_id) return;

    console.log('Received analytics update for selected server:', event);

    const statusCard = document.getElementById('status-card');
    if (statusCard) {
        const statusText = statusCard.querySelector('.text-3xl');
        statusText.textContent = event.status === 'online' ? 'Online' : 'Offline';
        statusText.className = `text-3xl font-bold ${event.status === 'online' ? 'text-green-500' : 'text-red-500'}`;
    }

    const timeCard = Array.from(document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6'))
        .find(card => {
            const title = card.querySelector('h3');
            return title && (title.textContent.includes('System Downtime') || title.textContent.includes('System Uptime'));
        });

    if (timeCard) {
        const title = timeCard.querySelector('h3');
        const value = timeCard.querySelector('.text-3xl.font-bold');
        const trackerKey = `analytics-${event.server_id}`;

        if (event.status === 'online') {
            title.textContent = 'System Uptime';
            // If we are not already tracking this server on the analytics page, start a timer.
            if (!window.serverUptimeTrackers[trackerKey]) {
                console.log(`[Analytics Page] Starting new uptime tracker for server ${event.server_id}.`);
                if (typeof event.current_uptime === 'number') {
                    const initialUptime = event.current_uptime;
                    const tracker = {
                        startedAt: Date.now(),
                        initialUptime: initialUptime,
                        interval: setInterval(() => {
                            const elapsedSeconds = Math.floor((Date.now() - tracker.startedAt) / 1000);
                            value.textContent = humanizeSeconds(tracker.initialUptime + elapsedSeconds);
                        }, 1000)
                    };
                    window.serverUptimeTrackers[trackerKey] = tracker;
                } else {
                    value.textContent = event.system_uptime || '--';
                }
            }
        } else { // Server is offline
            title.textContent = 'System Downtime';
            // If a timer was running, stop it.
            if (window.serverUptimeTrackers[trackerKey]) {
                console.log(`[Analytics Page] Clearing uptime tracker for offline server ${event.server_id}.`);
                clearInterval(window.serverUptimeTrackers[trackerKey].interval);
                delete window.serverUptimeTrackers[trackerKey];
            }
            value.textContent = event.formatted_downtime || '0s';
        }
    }

    if (window.performanceChart && event.performance_data) {
        window.performanceChart.data.labels = event.performance_data.labels;
        window.performanceChart.data.datasets.forEach((dataset, index) => {
            if (event.performance_data.datasets[index]) {
                dataset.data = event.performance_data.datasets[index].data;
            }
        });
        window.performanceChart.update();
    }
}

// Listen for server status updates
window.Echo.channel('server-status')
    .listen('ServerStatusUpdated', (e) => {
        updateServerStatus(e);
        updateAnalyticsPage(e);
    });

console.log("Listening for 'ServerStatusUpdated' on 'server-status' channel.");