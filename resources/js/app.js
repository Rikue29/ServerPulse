import './bootstrap';

console.log('ServerPulse app.js loaded!');

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Cleanup any duplicate/legacy banners on page load
document.addEventListener('DOMContentLoaded', function() {
    console.log('🧹 Cleaning up any duplicate/legacy banners');

    // Define all possible banner IDs
    const bannerIds = ['downtime-banner', 'offline-status-banner'];

    // For each server, ensure there's only one banner (keep the most recent one)
    // This logic seems a bit off if banners are server-specific. Re-evaluate if necessary.
    bannerIds.forEach((id) => {
        const banners = document.querySelectorAll(`#${id}`);
        if (banners.length > 1) {
            console.warn(`Found ${banners.length} banners with ID ${id} - cleaning up duplicates`);

            // Keep only the last one (most recently added)
            for (let i = 0; i < banners.length - 1; i++) {
                banners[i].remove();
            }
        }
    });

    console.log('✅ Banner cleanup complete');
});

// Helper to humanize seconds
function humanizeSeconds(seconds) {
    seconds = parseInt(seconds, 10);
    if (isNaN(seconds) || seconds < 1) return '0s';
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = seconds % 60;
    return [h ? h + 'h' : '', m ? m + 'm' : '', s ? s + 's' : ''].filter(Boolean).join(' ');
}

// Function to save downtime data to localStorage
function storeDowntimeData(serverId, startedAt, lastDownAt, serverName) {
    const downtimeData = {
        serverId: serverId,
        startedAt: startedAt,
        lastDownAt: lastDownAt,
        serverName: serverName,
        timestamp: Date.now(), // When it was last saved
    };
    try {
        localStorage.setItem('serverPulse_downtime', JSON.stringify(downtimeData));
        // Add a backup just in case primary gets corrupted
        localStorage.setItem(`serverPulse_downtime_backup_${serverId}_${Date.now()}`, JSON.stringify(downtimeData));
        console.log('💾 Downtime data saved to localStorage:', downtimeData);
    } catch (e) {
        console.error('❌ Error saving downtime data to localStorage:', e);
    }
}

// Function to load downtime data from localStorage
function loadDowntimeData() {
    try {
        const data = localStorage.getItem('serverPulse_downtime');
        if (data) {
            const parsedData = JSON.parse(data);
            // Basic validation
            if (parsedData && parsedData.serverId && parsedData.startedAt) {
                console.log('📂 Downtime data loaded from localStorage:', parsedData);
                return parsedData;
            }
        }
    } catch (e) {
        console.error('❌ Error loading downtime data from localStorage:', e);
        // Attempt to load from backups if primary is corrupted
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key && key.startsWith('serverPulse_downtime_backup_')) {
                try {
                    const backupData = JSON.parse(localStorage.getItem(key));
                    if (backupData && backupData.serverId && backupData.startedAt) {
                        console.warn('⚠️ Recovered downtime data from backup:', backupData);
                        // Save to primary and return
                        localStorage.setItem('serverPulse_downtime', JSON.stringify(backupData));
                        return backupData;
                    }
                } catch (backupError) {
                    console.error('❌ Error loading backup downtime data:', backupError);
                }
            }
        }
    }
    return null;
}

// Function to aggressively force chart values to zero for offline servers
function forceChartsToZeroForOfflineServer() {
    if (!window.performanceChart) {
        console.log('⚠️ Chart not initialized, cannot force to zero.');
        return;
    }

    console.log('📉 AGGRESSIVELY forcing performance chart data to zero for offline server.');

    try {
        // Create an array of zeros matching the current chart's data length
        const zeroData = new Array(window.performanceChart.data.labels.length).fill(0);
        
        // Force all chart datasets to zero values with no exceptions
        window.performanceChart.data.datasets.forEach((dataset, index) => {
            if (dataset.label && !dataset.label.includes('Uptime')) {
                // Overwrite completely with new array
                dataset.data = [...zeroData]; // Create new reference
                
                // Visual indicators for offline status
                if (dataset.backgroundColor && typeof dataset.backgroundColor === 'string') {
                    dataset.originalBackgroundColor = dataset.backgroundColor;
                    dataset.backgroundColor = 'rgba(220, 220, 220, 0.5)';
                }
                
                // Add "OFFLINE" prefix to label if not already there
                if (dataset.label && !dataset.label.includes('OFFLINE')) {
                    dataset.originalLabel = dataset.label;
                    dataset.label = `${dataset.label} (OFFLINE)`;
                }
            }
        });

        // Force chart update with more aggressive options
        window.performanceChart.update({
            duration: 0,
            lazy: false,
            easing: 'linear'
        });
        
        console.log('✅ Performance chart data forcefully zeroed.');
        
        // Create a backup check to ensure zeros stuck
        setTimeout(() => {
            // Verify zeros were applied
            let allZero = true;
            window.performanceChart.data.datasets.forEach(dataset => {
                if (dataset.label && !dataset.label.includes('Uptime')) {
                    const hasNonZero = dataset.data.some(value => value > 0);
                    if (hasNonZero) {
                        console.log(`⚠️ Dataset "${dataset.label}" still has non-zero values. Forcing zeros again.`);
                        dataset.data = new Array(window.performanceChart.data.labels.length).fill(0);
                        allZero = false;
                    }
                }
            });
            
            if (!allZero) {
                window.performanceChart.update({
                    duration: 0,
                    lazy: false
                });
                console.log('🔄 Chart re-zeroed after verification.');
            }
        }, 500);
    } catch (err) {
        console.error('❌ Error zeroing chart:', err);
    }

    // Add an overlay to visually indicate the chart is not active
    let overlay = document.getElementById('offline-chart-overlay');
    if (!overlay) {
        const chartContainer = document.querySelector('.chart-container'); // Adjust selector as needed
        if (chartContainer) {
            overlay = document.createElement('div');
            overlay.id = 'offline-chart-overlay';
            overlay.className = 'absolute inset-0 bg-gray-200 bg-opacity-75 flex items-center justify-center text-gray-700 font-bold text-lg pointer-events-none z-10 rounded-lg';
            overlay.innerHTML = '<span class="relative flex h-3 w-3 mr-2"><span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span><span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span></span>Server Offline';
            chartContainer.style.position = 'relative'; // Ensure container is relative for absolute overlay
            chartContainer.appendChild(overlay);
            window.offlineChartOverlay = overlay;
            console.log('➕ Chart offline overlay added.');
        }
    }
}

// Function to start or update the downtime counter for a specific server (primarily analytics page)
window.startDowntimeCounter = function(eventData) {
    if (!eventData || eventData.status === 'online') {
        console.log('⛔ Not starting downtime counter: Server is online or eventData is invalid.');
        if (window.downtimeInterval) {
            clearInterval(window.downtimeInterval);
            window.downtimeInterval = null;
            console.log('🆑 Cleared analytics page downtime interval.');
        }
        return;
    }

    const serverId = eventData.server_id;
    const initialDowntimeSeconds = eventData.current_downtime || Math.floor((Date.now() - window.downtimeStartedAt) / 1000);
    let currentDowntimeSeconds = initialDowntimeSeconds;

    console.log(`⏱️ Initializing downtime counter for server ${serverId}. Start: ${initialDowntimeSeconds}s`);

    // Clear any existing interval to prevent duplicates
    if (window.downtimeInterval) {
        clearInterval(window.downtimeInterval);
    }

    // Set a new interval
    window.downtimeInterval = setInterval(() => {
        currentDowntimeSeconds++;
        const formattedDowntime = humanizeSeconds(currentDowntimeSeconds);

        // Update the downtime card
        const downtimeCard = Array.from(document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6'))
            .find(card => {
                const title = card.querySelector('h3');
                return title && title.textContent.includes('System Downtime');
            });

        if (downtimeCard) {
            const downtimeValue = downtimeCard.querySelector('.text-3xl.font-bold');
            if (downtimeValue) {
                downtimeValue.textContent = formattedDowntime;
                downtimeValue.className = 'text-3xl font-bold text-red-900'; // Ensure color
            }
            // Ensure this specific card remains prominent
            downtimeCard.classList.remove('opacity-75');
            downtimeCard.classList.add('border-red-300', 'shadow-md', 'downtime-pulse'); // Add pulse
        }

        // Update the downtime banner
        const downtimeBanner = document.getElementById('downtime-banner');
        if (downtimeBanner) {
            const bannerText = downtimeBanner.querySelector('.downtime-text');
            if (bannerText) {
                bannerText.textContent = `Current downtime: ${formattedDowntime}`;
            }
        }
    }, 1000);
    console.log(`✅ Started dedicated downtime counter for analytics page for server ${serverId}.`);
};

// Function to update all downtime displays AND ensure metrics are zeroed for offline servers
window.updateAllDowntimeDisplays = function() {
    const rows = document.querySelectorAll('[id^="server-row-"]');
    rows.forEach(row => {
        const serverId = parseInt(row.id.replace('server-row-', ''), 10);
        const statusBadge = row.querySelector('.status-badge');
        const infoDiv = row.querySelector('.server-uptime-info');

        if (!statusBadge || !infoDiv) return;
        
        // Check if we're on the analytics page with this server selected
        const isAnalyticsPage = window.location.pathname.includes('/analytics');
        const serverSelector = document.getElementById('server_id');
        const selectedServerId = serverSelector ? parseInt(serverSelector.value) : null;

        // Check the status from localStorage or inferred from UI
        const isOffline = statusBadge.textContent.toLowerCase().includes('offline');

        if (isOffline) {
            // Check if this server is the one actively being tracked for downtime
            const savedDowntime = loadDowntimeData();
            let currentDowntimeSeconds = 0;

            if (savedDowntime && savedDowntime.serverId === serverId) {
                currentDowntimeSeconds = Math.floor((Date.now() - savedDowntime.startedAt) / 1000);
                infoDiv.textContent = `Downtime: ${humanizeSeconds(currentDowntimeSeconds)}`;
                infoDiv.classList.remove('text-gray-600');
                infoDiv.classList.add('text-red-600');
                
                // If we're on analytics page with this offline server selected,
                // ensure all metrics are zeroed and charts show offline state
                if (isAnalyticsPage && selectedServerId === serverId) {
                    // Create zeroed event data
                    const zeroEventData = {
                        server_id: serverId,
                        name: savedDowntime.serverName || `Server ${serverId}`,
                        status: 'offline',
                        cpu_usage: 0,
                        ram_usage: 0,
                        disk_usage: 0,
                        response_time: 0,
                        network_rx: 0,
                        network_tx: 0,
                        system_uptime: '0s',
                        current_downtime: currentDowntimeSeconds
                    };
                    
                    // Force metrics to zero on every update
                    updateSummaryCards(zeroEventData, 0, false);
                    
                    // Make sure charts show zero state
                    forceChartsToZeroForOfflineServer();
                    
                    console.log(`🧹 Forced analytics metrics to zero for offline server ${serverId}`);
                }
            } else {
                // If not the actively tracked one, but still marked offline in UI
                // We might not have precise start time, so display N/A or a default
                infoDiv.textContent = 'Downtime: N/A'; // Or keep last known value
                infoDiv.classList.remove('text-gray-600');
                infoDiv.classList.add('text-red-600');
            }
            statusBadge.className = statusBadge.className
                .replace(/bg-\w+-\d+/g, 'bg-red-100')
                .replace(/text-\w+-\d+/g, 'text-red-800');
        } else {
            // Server is online, display uptime
            statusBadge.className = statusBadge.className
                .replace(/bg-\w+-\d+/g, 'bg-green-100')
                .replace(/text-\w+-\d+/g, 'text-green-800');
            infoDiv.classList.remove('text-red-600');
            infoDiv.classList.add('text-gray-600');
            // Assuming uptime is updated by regular Echo events or on page load for online servers
        }
    });
};

// Set up a resilient downtime monitoring system for offline servers (general purpose)
window.setupOfflineServerMonitor = function() {
    console.log('🔄 Setting up global offline server monitor');

    // Clear any existing monitor
    if (window.offlineServerMonitor) {
        clearInterval(window.offlineServerMonitor);
    }

    // This monitor primarily handles updating the _display_ of downtime on the servers page
    // and ensuring the analytics page is zeroed out if it's viewing an offline server.
    window.offlineServerMonitor = setInterval(() => {
        const savedDowntime = loadDowntimeData();
        const isAnalyticsPage = window.location.pathname.includes('/analytics');
        const serverSelector = document.getElementById('server_id');
        const selectedServerId = serverSelector ? parseInt(serverSelector.value) : null;

        if (savedDowntime) {
            const currentTime = Date.now();
            const elapsedMs = currentTime - savedDowntime.startedAt;
            const currentDowntimeSeconds = Math.floor(elapsedMs / 1000);

            // Update on analytics page if it's the selected server
            if (isAnalyticsPage && selectedServerId === savedDowntime.serverId) {
                // Every second, update the UI for the analytics page
                if (window.downtimeInterval && currentDowntimeSeconds > 0) {
                    // The dedicated downtimeInterval handles this
                } else if (!window.downtimeInterval) {
                    // If no interval is running, start it
                    console.log(`⏱️ [MONITOR] Restarting analytics downtime counter for server ${savedDowntime.serverId}`);
                    startDowntimeCounter({
                        server_id: savedDowntime.serverId,
                        status: 'offline',
                        current_downtime: currentDowntimeSeconds,
                        last_down_at: savedDowntime.lastDownAt,
                        name: savedDowntime.serverName,
                    });
                }
                // Ensure charts are zeroed and UI state is correct
                forceChartsToZeroForOfflineServer();
                updateOfflineUIState({
                    server_id: savedDowntime.serverId,
                    status: 'offline',
                    current_downtime: currentDowntimeSeconds,
                    formatted_downtime: humanizeSeconds(currentDowntimeSeconds),
                    name: savedDowntime.serverName,
                });
            }

            // Update on servers page (all rows) every second
            window.updateAllDowntimeDisplays();
        } else {
            // If no saved downtime, clear any running intervals for downtime display
            if (window.downtimeInterval) {
                clearInterval(window.downtimeInterval);
                window.downtimeInterval = null;
                console.log('🆑 Cleared analytics page downtime interval.');
            }
            if (window.globalDowntimeInterval) {
                clearInterval(window.globalDowntimeInterval);
                window.globalDowntimeInterval = null;
                console.log('🆑 Cleared global downtime interval.');
            }
            // Ensure no offline banners or chart overlays remain if server is online
            const downtimeBanner = document.getElementById('downtime-banner');
            if (downtimeBanner) downtimeBanner.remove();
            const offlineBanner = document.getElementById('offline-status-banner');
            if (offlineBanner) offlineBanner.remove();
            if (window.offlineChartOverlay) {
                window.offlineChartOverlay.remove();
                window.offlineChartOverlay = null;
            }
            // Also ensure cards are back to normal state
            document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6').forEach(card => {
                card.classList.remove('opacity-75', 'opacity-90', 'bg-gray-50', 'border-red-300', 'shadow-md', 'downtime-pulse', 'offline-transition');
                const indicator = card.querySelector('.offline-indicator');
                if (indicator) indicator.remove();
            });
        }
    }, 1000); // Run every second
};

// Start the monitor when the document is ready
document.addEventListener('DOMContentLoaded', window.setupOfflineServerMonitor);

// Set up document ready handler to load downtime data when page loads
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔄 Document loaded, checking for offline servers to track');

    const savedDowntime = loadDowntimeData();
    if (savedDowntime) {
        console.log('📂 Found saved downtime data in localStorage:', savedDowntime);

        // Get the server ID from the URL or dropdown if on analytics page
        const isAnalyticsPage = window.location.pathname.includes('/analytics');
        const serverSelector = document.getElementById('server_id');
        const selectedServerId = serverSelector ? parseInt(serverSelector.value) : null;

        // If on analytics page and the selected server is the saved offline server
        if (isAnalyticsPage && selectedServerId === savedDowntime.serverId) {
            console.log('⚡ Selected server is offline - updating UI immediately.');
            const fakeEventData = {
                server_id: savedDowntime.serverId,
                status: 'offline',
                current_downtime: Math.floor((Date.now() - savedDowntime.startedAt) / 1000),
                last_down_at: savedDowntime.lastDownAt,
                name: savedDowntime.serverName,
            };
            // Call the analytics update function which will handle UI and charts
            updateAnalyticsPage(fakeEventData);
            // Ensure the dedicated downtime counter for analytics page is running
            startDowntimeCounter(fakeEventData);
        } else if (!isAnalyticsPage) {
            // On servers page or a different analytics page, ensure global display updates
            console.log('📊 Starting global downtime display updates for servers page.');
            window.updateAllDowntimeDisplays(); // Immediate update
        }
    } else {
        console.log('✅ No offline servers to track on load.');
    }
});

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
    console.log('Pusher connection state changed from', states.previous, 'to', states.current);
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

channel
    .subscribed(() => {
        console.log('Successfully subscribed to server-status channel!');

        console.log('%c✓ Real-time monitoring ACTIVE', 'color: green; font-weight: bold; font-size: 14px');

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
    .listen('.server.status.updated', (e) => {
        console.log('🚨 EVENT RECEIVED: .server.status.updated. Full event object:', JSON.parse(JSON.stringify(e)));

        let eventData = e.status; // Directly access e.status based on CATCH-ALL log

        console.log('📊 Event data for UI update:', JSON.parse(JSON.stringify(eventData)));

        if (!eventData || typeof eventData !== 'object') {
            console.error('❌ Processed event data is missing or not an object!', eventData);
            return;
        }

        if (!eventData.server_id) {
            console.error('❌ server_id is missing in processed event data!', eventData);
            return;
        }

        const serverId = eventData.server_id;
        const statusField = eventData.status || '';
        const isOffline = statusField !== 'online';

        localStorage.setItem(`serverPulse_status_${serverId}`, isOffline ? 'offline' : 'online');

        console.log(`🔌 Server ${serverId} status: ${isOffline ? '🔴 OFFLINE' : '🟢 ONLINE'} - Status field: "${statusField}"`);

        if (!window.lastServerEvents) window.lastServerEvents = {};
        window.lastServerEvents[serverId] = {
            timestamp: Date.now(),
            status: isOffline ? 'offline' : 'online',
        };

        if (isOffline) {
            console.log('⚠️ OFFLINE SERVER DETECTED - INITIATING AGGRESSIVE ZEROING PROCEDURE');

            const zeroMetricFields = [
                'cpu_usage',
                'ram_usage',
                'disk_usage',
                'response_time',
                'network_rx',
                'network_tx',
                'network_activity',
                'network_speed',
                'disk_io_read',
                'disk_io_write',
                'load_average',
            ];

            zeroMetricFields.forEach((field) => {
                if (eventData[field] !== 0) {
                    console.log(`🧹 Forcing ${field} from ${eventData[field]} to 0`);
                    eventData[field] = 0;
                }
            });

            eventData.system_uptime = '0s';

            console.log('🧹 Reset all metrics to zero for offline server');

            let downtimeSeconds = 0;
            if (eventData.current_downtime && !isNaN(parseInt(eventData.current_downtime, 10))) {
                downtimeSeconds = parseInt(eventData.current_downtime, 10);
                console.log(`📊 Using server-provided downtime: ${downtimeSeconds} seconds`);
            } else if (eventData.last_down_at) {
                const lastDownAt = new Date(eventData.last_down_at);
                if (!isNaN(lastDownAt.getTime())) {
                    downtimeSeconds = Math.floor((Date.now() - lastDownAt.getTime()) / 1000);
                    console.log(`📊 Calculated downtime from last_down_at: ${downtimeSeconds} seconds`);
                }
            }

            downtimeSeconds = Math.max(0, downtimeSeconds);

            const downtimeStartedAt = Date.now() - downtimeSeconds * 1000;

            // These globals are managed by setupOfflineServerMonitor and loadDowntimeData
            // window.downtimeStartedAt = downtimeStartedAt;
            // window.currentOfflineServerId = eventData.server_id;

            console.log(`⏱️ OFFLINE EVENT - Storing downtime tracking for server ${eventData.server_id}`);
            console.log(`⏱️ Initial downtime: ${downtimeSeconds} seconds (${humanizeSeconds(downtimeSeconds)})`);

            storeDowntimeData(
                eventData.server_id,
                downtimeStartedAt,
                eventData.last_down_at,
                eventData.name || `Server ${eventData.server_id}`,
            );

            console.log(`📌 Updated global downtime tracking for server ${eventData.server_id}.`);

            // AGGRESSIVELY handle offline server updates on any page
            const isAnalyticsPage = window.location.pathname.includes('/analytics');
            
            // 1. Force all charts to zero IMMEDIATELY
            forceChartsToZeroForOfflineServer();
            
            // 2. Force immediate UI updates for offline state
            updateOfflineUIState(eventData);
            
            // 3. Immediately start downtime counter
            startDowntimeCounter(eventData);
            
            // 4. Update page-specific elements
            if (isAnalyticsPage) {
                const serverSelector = document.getElementById('server_id');
                const selectedServerId = serverSelector ? parseInt(serverSelector.value) : null;
                if (!selectedServerId || selectedServerId === eventData.server_id) {
                    // Multiple updates with decreasing delay to ensure UI is updated
                    updateAnalyticsPage(eventData); // First update
                    
                    // Schedule follow-up updates to ensure metrics stay at zero
                    [100, 500, 2000].forEach(delay => {
                        setTimeout(() => {
                            console.log(`⏱️ Follow-up zero metrics update after ${delay}ms`);
                            updateAnalyticsPage(eventData);
                            forceChartsToZeroForOfflineServer();
                            window.updateAllDowntimeDisplays();
                        }, delay);
                    });
                } else {
                    console.log(`📌 Ignoring analytics page update for server ${eventData.server_id}, currently viewing ${selectedServerId}`);
                }
            }
            updateServersPage(eventData); // Always update servers page
            window.updateAllDowntimeDisplays(); // Ensure all downtime displays are updated
        } else {
            // Server is now online
            if (loadDowntimeData() && loadDowntimeData().serverId === eventData.server_id) {
                console.log(`📈 Server ${eventData.server_id} is back ONLINE - clearing downtime tracking`);

                localStorage.removeItem('serverPulse_downtime');
                Object.keys(localStorage)
                    .filter((key) => key.startsWith('serverPulse_downtime_backup_'))
                    .forEach((key) => localStorage.removeItem(key));

                if (window.downtimeInterval) {
                    clearInterval(window.downtimeInterval);
                    window.downtimeInterval = null;
                }
                if (window.offlineServerMonitor) { // This might clear the general monitor, be careful
                    clearInterval(window.offlineServerMonitor);
                    window.offlineServerMonitor = null;
                    // Re-setup if needed
                    window.setupOfflineServerMonitor();
                }

                const downtimeBanner = document.getElementById('downtime-banner');
                if (downtimeBanner) downtimeBanner.remove();
                const offlineBanner = document.getElementById('offline-status-banner');
                if (offlineBanner) offlineBanner.remove();
                if (window.offlineChartOverlay) {
                    window.offlineChartOverlay.remove();
                    window.offlineChartOverlay = null;
                }

                document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6').forEach((card) => {
                    card.classList.remove('opacity-75', 'opacity-90', 'bg-gray-50', 'border-red-300', 'shadow-md', 'downtime-pulse', 'offline-transition');
                    const indicator = card.querySelector('.offline-indicator');
                    if (indicator) indicator.remove();
                    // Also restore original bar colors if they were changed
                    const bar = card.querySelector('.progress-bar');
                    if (bar && bar.className.includes('bg-gray-300')) {
                        bar.className = bar.className.replace('bg-gray-300', 'bg-green-500'); // Example, adjust as needed
                    }
                });
            }

            const isAnalyticsPage = window.location.pathname.includes('/analytics');
            if (isAnalyticsPage) {
                const serverSelector = document.getElementById('server_id');
                const selectedServerId = serverSelector ? parseInt(serverSelector.value) : null;
                if (!selectedServerId || selectedServerId === eventData.server_id) {
                    updateAnalyticsPage(eventData); // This will restore normal metrics
                }
            }
            updateServersPage(eventData); // Always update servers page
            window.updateAllDowntimeDisplays(); // Ensure all uptime displays are updated
        }
    });

// Global variables for real-time updates
let lastUpdateTime = {};
let lastNetworkBytes = {};
let performanceChart = null; // Global chart instance
let networkThroughputHistory = {}; // Store network throughput history for each server

// Function to update analytics page summary cards
function updateAnalyticsPage(eventData) {
    console.log('🔄 Updating analytics page for server_id:', eventData.server_id);
    console.log('📊 Event data received:', eventData);

    const serverSelector = document.getElementById('server_id');
    if (!serverSelector) {
        console.log('⚠️ Server selector not found, skipping update');
        return;
    }

    const selectedServerId = parseInt(serverSelector.value);
    if (eventData.server_id !== selectedServerId) {
        console.log(`⏭️ Skipping update - broadcast for server ${eventData.server_id}, but selected server is ${selectedServerId}`);
        return;
    }

    console.log(`✅ Updating cards for selected server ${selectedServerId}`);

    const isServerOnline = eventData.status === 'online';
    if (!isServerOnline) {
        console.log('🔴 DOUBLE-CHECKING: Server is OFFLINE, forcing metrics to zero');
        eventData.cpu_usage = 0;
        eventData.ram_usage = 0;
        eventData.disk_usage = 0;
        eventData.response_time = 0;
        eventData.network_rx = 0;
        eventData.network_tx = 0;
        eventData.system_uptime = '0s'; // Explicitly set uptime to 0 for offline
    }

    console.log(`🔌 Server status: ${isServerOnline ? '🟢 ONLINE' : '🔴 OFFLINE'}`);

    const serverId = eventData.server_id;
    const currentTime = Date.now();

    if (isServerOnline) {
        const currentTotalBytes = (eventData.network_rx || 0) + (eventData.network_tx || 0);

        let actualThroughput = 0;
        if (lastUpdateTime[serverId] && lastNetworkBytes[serverId]) {
            const timeDiff = (currentTime - lastUpdateTime[serverId]) / 1000;
            const bytesDiff = Math.max(0, currentTotalBytes - lastNetworkBytes[serverId]);
            actualThroughput = timeDiff > 0 ? bytesDiff / timeDiff / 1024 : 0;
        }

        lastUpdateTime[serverId] = currentTime;
        lastNetworkBytes[serverId] = currentTotalBytes;

        updateSummaryCards(eventData, actualThroughput, isServerOnline);
        updatePerformanceChart(eventData, actualThroughput);

        // Remove any offline UI elements when server comes online
        const downtimeBanner = document.getElementById('downtime-banner');
        if (downtimeBanner) downtimeBanner.remove();
        const offlineBanner = document.getElementById('offline-status-banner');
        if (offlineBanner) offlineBanner.remove();
        if (window.offlineChartOverlay) {
            window.offlineChartOverlay.remove();
            window.offlineChartOverlay = null;
        }
        document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6').forEach(card => {
            card.classList.remove('opacity-75', 'opacity-90', 'bg-gray-50', 'border-red-300', 'shadow-md', 'downtime-pulse', 'offline-transition');
            const indicator = card.querySelector('.offline-indicator');
            if (indicator) indicator.remove();
            const bar = card.querySelector('.progress-bar');
            if (bar && bar.className.includes('bg-gray-300')) {
                bar.className = bar.className.replace('bg-gray-300', 'bg-green-500');
            }
        });
    } else {
        console.log('🚨 OFFLINE SERVER UPDATE FLOW TRIGGERED');

        delete lastUpdateTime[serverId];
        delete lastNetworkBytes[serverId];

        // Force all these updates to happen in sequence for offline servers
        console.log('1️⃣ Updating summary cards to zero values');
        updateSummaryCards(eventData, 0, false);

        console.log('2️⃣ Updating UI elements for offline state');
        updateOfflineUIState(eventData);

        console.log('3️⃣ Setting all chart values to zero');
        forceChartsToZeroForOfflineServer();

        console.log('4️⃣ Starting/updating downtime counter');
        startDowntimeCounter(eventData);

        setTimeout(() => {
            console.log('5️⃣ Double-check: forcing zero values again');
            forceChartsToZeroForOfflineServer();
        }, 500);
    }
}

// Function to update UI elements for offline servers
function updateOfflineUIState(eventData) {
    console.log('🔴 AGGRESSIVE UI UPDATE FOR OFFLINE SERVER:', eventData.server_id);

    const serverName = eventData.name || `Server ${eventData.server_id}`;

    const metricCards = document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6');

    metricCards.forEach((card) => {
        const title = card.querySelector('h3');
        if (title && !title.textContent.includes('System Downtime')) {
            card.classList.add('opacity-90', 'bg-gray-50', 'border-gray-300', 'offline-transition');
            setTimeout(() => {
                card.classList.remove('offline-transition');
            }, 3000);
        } else if (title && title.textContent.includes('System Downtime')) {
            card.classList.add('border-red-300', 'shadow-md', 'downtime-pulse');
        }
    });

    startDowntimeCounter(eventData);

    forceChartsToZeroForOfflineServer(); // Call directly once

    const updateTimes = [250, 500, 1000, 2000, 5000];
    updateTimes.forEach((delay) => {
        setTimeout(forceChartsToZeroForOfflineServer, delay);
    });
    console.log('📊 Scheduled multiple follow-up chart updates');

    metricCards.forEach((card) => {
        const title = card.querySelector('h3');
        const valueEl = card.querySelector('.text-3xl.font-bold');

        if (!title || !valueEl) return;

        if (title.textContent.includes('CPU Usage')) {
            valueEl.textContent = '0.0%';
            console.log('✓ Zeroed CPU Usage');
        } else if (title.textContent.includes('Memory Usage')) {
            valueEl.textContent = '0.0%';
            console.log('✓ Zeroed Memory Usage');
        } else if (title.textContent.includes('Disk Usage') || title.textContent.includes('Storage Usage')) {
            valueEl.textContent = '0.0%';
            console.log('✓ Zeroed Disk Usage');
        } else if (title.textContent.includes('Response Time')) {
            valueEl.textContent = '0.0';
            console.log('✓ Zeroed Response Time');
        } else if (title.textContent.includes('Network Throughput')) {
            valueEl.textContent = '0.0 KB/s';
            console.log('✓ Zeroed Network Throughput');
        } else if (title.textContent.includes('Network Activity')) {
            valueEl.textContent = '0';
            console.log('✓ Zeroed Network Activity');
            const bar = card.querySelector('.bg-green-500.h-2.rounded-full, .progress-bar');
            if (bar) {
                bar.style.width = '0%';
                bar.className = bar.className.replace('bg-green-500', 'bg-gray-300');
                console.log('✓ Reset activity bar width to 0% and changed color');
            }
        }
        // Gray out values that are zeroed out due to being offline
        if (!title.textContent.includes('System Downtime')) {
            valueEl.classList.remove('text-gray-900');
            valueEl.classList.add('text-gray-500'); // Make text slightly lighter
        }

        // Add a subtle offline indicator icon to each metric card
        if (!card.querySelector('.offline-indicator')) {
            const indicator = document.createElement('i');
            indicator.className = 'fas fa-unlink text-red-500 text-sm ml-2 offline-indicator';
            title.appendChild(indicator);
        }
    });

    // Update status badge prominently
    const statusBadge = document.querySelector('.server-status-badge');
    if (statusBadge) {
        statusBadge.textContent = 'Offline';
        statusBadge.className = 'server-status-badge px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 animate-pulse';
        console.log('✓ Updated status badge to Offline (pulsing)');
    }

    // Add/Update prominent downtime banner
    let downtimeBanner = document.getElementById('downtime-banner');
    if (!downtimeBanner) {
        const analyticsContainer = document.querySelector('.analytics-container');
        if (analyticsContainer) {
            downtimeBanner = document.createElement('div');
            downtimeBanner.id = 'downtime-banner';
            downtimeBanner.className = 'bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4';
            downtimeBanner.role = 'alert';

            const flexContainer = document.createElement('div');
            flexContainer.className = 'flex items-center';

            const iconDiv = document.createElement('div');
            iconDiv.className = 'py-1 mr-3';
            const icon = document.createElement('i');
            icon.className = 'fas fa-exclamation-triangle text-red-500 text-lg';
            iconDiv.appendChild(icon);

            const strongEl = document.createElement('strong');
            strongEl.className = 'font-bold mr-2';
            strongEl.textContent = `${serverName} is Offline!`;

            const spanEl = document.createElement('span');
            spanEl.className = 'block sm:inline downtime-text';
            spanEl.textContent = `Current downtime: ${humanizeSeconds(eventData.current_downtime || 0)}`;

            flexContainer.appendChild(iconDiv);
            flexContainer.appendChild(strongEl);
            flexContainer.appendChild(spanEl);
            downtimeBanner.appendChild(flexContainer);

            analyticsContainer.insertBefore(downtimeBanner, analyticsContainer.firstChild);
            console.log('✓ Created downtime banner.');
        }
    } else {
        const downtimeText = downtimeBanner.querySelector('.downtime-text');
        if (downtimeText) {
            downtimeText.textContent = `Current downtime: ${humanizeSeconds(eventData.current_downtime || 0)}`;
        }
        const serverNameEl = downtimeBanner.querySelector('strong');
        if (serverNameEl) {
            serverNameEl.textContent = `${serverName} is Offline!`;
        }
        console.log('✓ Updated existing downtime banner.');
    }
}

// Function to update servers page server rows
function updateServersPage(eventData) {
    const row = document.getElementById('server-row-' + eventData.server_id);
    if (row) {
        console.log('Found row for server_id:', eventData.server_id, row);

        // Update CPU
        const cpuCell = row.querySelector('[data-col="cpu"]');
        if (cpuCell) {
            const cpuBar = cpuCell.querySelector('.bg-blue-600');
            const cpuText = cpuCell.querySelector('span');
            if (cpuBar) cpuBar.style.width = eventData.cpu_usage + '%';
            if (cpuText) cpuText.textContent = parseFloat(eventData.cpu_usage).toFixed(1) + '%';
            console.log('Updating CPU for server_id:', eventData.server_id, 'to', eventData.cpu_usage);
        } else {
            console.warn('CPU cell not found for server_id:', eventData.server_id);
        }

        // Update RAM
        const ramCell = row.querySelector('[data-col="ram"]');
        if (ramCell) {
            const ramBar = ramCell.querySelector('.bg-blue-600');
            const ramText = ramCell.querySelector('span');
            if (ramBar) ramBar.style.width = eventData.ram_usage + '%';
            if (ramText) ramText.textContent = parseFloat(eventData.ram_usage).toFixed(1) + '%';
            console.log('Updating RAM for server_id:', eventData.server_id, 'to', eventData.ram_usage);
        } else {
            console.warn('RAM cell not found for server_id:', eventData.server_id);
        }

        // Update Disk
        const diskCell = row.querySelector('[data-col="disk"]');
        if (diskCell) {
            const diskBar = diskCell.querySelector('.bg-blue-600');
            const diskText = diskCell.querySelector('span');
            if (diskBar) diskBar.style.width = eventData.disk_usage + '%';
            if (diskText) diskText.textContent = parseFloat(eventData.disk_usage).toFixed(1) + '%';
            console.log('Updating Disk for server_id:', eventData.server_id, 'to', eventData.disk_usage);
        } else {
            console.warn('Disk cell not found for server_id:', eventData.server_id);
        }

        // Update Status and Uptime/Downtime
        const statusCell = row.querySelector('[data-col="status"]');
        if (statusCell) {
            const badge = statusCell.querySelector('span:not(.server-uptime-info)');
            if (badge) {
                badge.textContent = (eventData.status || 'offline').charAt(0).toUpperCase() + (eventData.status || 'offline').slice(1);
                badge.className =
                    'px-2 inline-flex text-xs leading-5 font-semibold rounded-full ' +
                    (eventData.status === 'online' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800');
            }

            const infoDiv = statusCell.querySelector('.server-uptime-info');
            if (infoDiv) {
                if (eventData.status === 'online') {
                    infoDiv.textContent = 'Uptime: ' + humanizeSeconds(eventData.current_uptime || eventData.system_uptime); // Prioritize current_uptime
                    infoDiv.classList.remove('text-red-600');
                    infoDiv.classList.add('text-gray-600');
                } else {
                    infoDiv.textContent = 'Downtime: ' + humanizeSeconds(eventData.current_downtime);
                    infoDiv.classList.remove('text-gray-600');
                    infoDiv.classList.add('text-red-600');
                }
            }
        }
    } else {
        console.warn('Could not find row for server_id:', eventData.server_id);
    }
}

// Helper function to calculate network activity level
function calculateNetworkActivity(eventData) {
    // ALWAYS return 0 for offline servers - check multiple indicators
    if (eventData.status === 'offline' || 
        eventData.network_rx === 0 && eventData.network_tx === 0 ||
        localStorage.getItem(`serverPulse_status_${eventData.server_id}`) === 'offline') {
        console.log('🔍 Server is OFFLINE - Network Activity forced to 0');
        return 0;
    }
    
    const totalBytes = (eventData.network_rx || 0) + (eventData.network_tx || 0);

    console.log('🔍 Network Activity Calculation:', {
        network_rx: eventData.network_rx,
        network_tx: eventData.network_tx,
        totalBytes: totalBytes,
        status: eventData.status
    });

    if (totalBytes > 1000000) {
        return 100;
    } else if (totalBytes > 100000) {
        return 60;
    } else if (totalBytes > 10000) {
        return 30;
    } else {
        return 0;
    }
}

console.log('Event listener for ServerStatusUpdated attached.');

// Function to handle server selection change on analytics page
function handleServerSelectionChange() {
    const serverSelector = document.getElementById('server_id');
    if (!serverSelector) return;

    const selectedServerId = parseInt(serverSelector.value);
    console.log(`🔄 Server selection changed to: ${selectedServerId}`);

    delete lastUpdateTime[selectedServerId];
    delete lastNetworkBytes[selectedServerId];
    delete networkThroughputHistory[selectedServerId];

    const throughputCard = document.getElementById('network-throughput-card');
    if (throughputCard) {
        const throughputValue = throughputCard.querySelector('.text-2xl');
        if (throughputValue) {
            throughputValue.textContent = '0 KB/s';
        }
    }

    // Check if the newly selected server is offline based on saved data
    const savedDowntime = loadDowntimeData();
    if (savedDowntime && savedDowntime.serverId === selectedServerId) {
        console.log(`⚡ Newly selected server ${selectedServerId} is OFFLINE from saved data.`);
        const fakeEventData = {
            server_id: selectedServerId,
            status: 'offline',
            current_downtime: Math.floor((Date.now() - savedDowntime.startedAt) / 1000),
            last_down_at: savedDowntime.lastDownAt,
            name: savedDowntime.serverName,
        };
        // Trigger the offline update flow
        updateAnalyticsPage(fakeEventData);
    } else {
        // If not offline or no data, ensure UI is reset to online state
        const downtimeBanner = document.getElementById('downtime-banner');
        if (downtimeBanner) downtimeBanner.remove();
        const offlineBanner = document.getElementById('offline-status-banner');
        if (offlineBanner) offlineBanner.remove();
        if (window.offlineChartOverlay) {
            window.offlineChartOverlay.remove();
            window.offlineChartOverlay = null;
        }
        document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6').forEach(card => {
            card.classList.remove('opacity-75', 'opacity-90', 'bg-gray-50', 'border-red-300', 'shadow-md', 'downtime-pulse', 'offline-transition');
            const indicator = card.querySelector('.offline-indicator');
            if (indicator) indicator.remove();
            const bar = card.querySelector('.progress-bar');
            if (bar && bar.className.includes('bg-gray-300')) {
                bar.className = bar.className.replace('bg-gray-300', 'bg-green-500');
            }
            // Restore text color if changed
            const valueEl = card.querySelector('.text-3xl.font-bold');
            if (valueEl) {
                valueEl.classList.remove('text-gray-500');
                valueEl.classList.add('text-gray-900');
            }
        });
        // You might need to trigger a data fetch for the new online server if not done automatically
    }

    console.log(`🧹 Cleared previous data for server ${selectedServerId}`);
}

document.addEventListener('DOMContentLoaded', function() {
    const serverSelector = document.getElementById('server_id');
    if (serverSelector) {
        serverSelector.addEventListener('change', handleServerSelectionChange);
        console.log('🎯 Server selection change listener attached');
    }
});

// Function to update performance chart with real-time data
function updatePerformanceChart(eventData, actualThroughput) {
    if (!window.performanceChart) {
        console.log('⚠️ Chart not initialized yet');
        return;
    }

    console.log('📈 Fetching fresh chart data for real-time update');

    const serverId = eventData.server_id;
    const url = `/analytics?server_id=${serverId}&ajax=1`;

    fetch(url)
        .then((response) => response.json())
        .then((data) => {
            if (data.chart_data) {
                window.performanceChart.data.labels = data.chart_data.labels;
                window.performanceChart.data.datasets[0].data = data.chart_data.cpu_load;
                window.performanceChart.data.datasets[1].data = data.chart_data.memory_usage;
                window.performanceChart.data.datasets[2].data = data.chart_data.network_activity;
                window.performanceChart.data.datasets[3].data = data.chart_data.disk_io;
                window.performanceChart.data.datasets[4].data = data.chart_data.disk_usage;
                window.performanceChart.data.datasets[6].data = data.chart_data.response_time;

                console.log('📊 Using actualThroughput from metrics card:', actualThroughput);

                if (!networkThroughputHistory[serverId]) {
                    networkThroughputHistory[serverId] = [];
                }

                networkThroughputHistory[serverId].push(actualThroughput);

                console.log('📊 Network Throughput History:', {
                    serverId: serverId,
                    historyLength: networkThroughputHistory[serverId].length,
                    latestValue: actualThroughput,
                    last5Values: networkThroughputHistory[serverId].slice(-5),
                });

                if (networkThroughputHistory[serverId].length > 200) {
                    networkThroughputHistory[serverId] = networkThroughputHistory[serverId].slice(-200);
                }

                const historyLength = networkThroughputHistory[serverId].length;
                const chartLength = window.performanceChart.data.labels.length;

                let throughputData = [];
                if (historyLength >= chartLength) {
                    throughputData = networkThroughputHistory[serverId].slice(-chartLength);
                } else {
                    const padding = new Array(chartLength - historyLength).fill(0);
                    throughputData = [...padding, ...networkThroughputHistory[serverId]];
                }

                console.log('📈 Chart Data Debug:', {
                    historyLength: historyLength,
                    chartLength: chartLength,
                    throughputDataLength: throughputData.length,
                    throughputDataLast5: throughputData.slice(-5),
                });

                window.performanceChart.data.datasets[5].data = throughputData;

                window.performanceChart.update('none');

                console.log('✅ Chart updated with real-time network throughput history. Latest:', actualThroughput.toFixed(2) + ' KB/s');

                // Remove offline overlay if it exists
                if (window.offlineChartOverlay) {
                    window.offlineChartOverlay.remove();
                    window.offlineChartOverlay = null;
                    console.log('➖ Chart offline overlay removed.');
                }
            }
        })
        .catch((error) => {
            console.error('❌ Error fetching fresh chart data:', error);
        });
}

// Function to update summary cards
function updateSummaryCards(eventData, actualThroughput, isServerOnline = true) {
    const metricCards = document.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6');

    metricCards.forEach((card) => {
        const title = card.querySelector('h3');
        const valueEl = card.querySelector('.text-3xl.font-bold');

        if (!title || !valueEl) return;

        // Reset general card appearance for online status
        if (isServerOnline) {
            card.classList.remove('opacity-90', 'bg-gray-50', 'border-gray-300', 'offline-transition', 'downtime-pulse', 'shadow-md', 'border-red-300');
            valueEl.classList.remove('text-gray-500', 'text-red-900');
            valueEl.classList.add('text-gray-900');
            const indicator = card.querySelector('.offline-indicator');
            if (indicator) indicator.remove();
            const bar = card.querySelector('.progress-bar');
            if (bar && bar.className.includes('bg-gray-300')) {
                bar.className = bar.className.replace('bg-gray-300', 'bg-green-500');
            }
        }

        if (title.textContent.includes('CPU Usage')) {
            const newCpuValue = isServerOnline ? parseFloat(eventData.cpu_usage || 0).toFixed(1) + '%' : '0.0%';
            valueEl.textContent = newCpuValue;
            console.log('✅ Updated CPU Usage to:', newCpuValue);
        } else if (title.textContent.includes('Memory Usage')) {
            const newMemoryValue = isServerOnline ? parseFloat(eventData.ram_usage || 0).toFixed(1) + '%' : '0.0%';
            valueEl.textContent = newMemoryValue;
            console.log('✅ Updated Memory Usage to:', newMemoryValue);
        } else if (title.textContent.includes('Storage Usage') || title.textContent.includes('Disk Usage')) {
            // Always zero out disk usage for offline servers too
            const newStorageValue = isServerOnline ? parseFloat(eventData.disk_usage || 0).toFixed(1) + '%' : '0.0%';
            valueEl.textContent = newStorageValue;
            console.log('✅ Updated Disk Usage to:', newStorageValue);
        } else if (title.textContent.includes('Network Activity')) {
            const networkBar = card.querySelector('.bg-green-500.h-2.rounded-full, .progress-bar');
            if (isServerOnline) {
                const networkActivity = calculateNetworkActivity(eventData);
                valueEl.textContent = networkActivity;
                if (networkBar) {
                    networkBar.style.width = networkActivity + '%';
                    networkBar.className = networkBar.className.replace('bg-gray-300', 'bg-green-500');
                }
                console.log('✅ Updated Network Activity to:', networkActivity);
            } else {
                valueEl.textContent = '0';
                if (networkBar) {
                    networkBar.style.width = '0%';
                    networkBar.className = networkBar.className.replace('bg-green-500', 'bg-gray-300');
                }
                console.log('✅ Updated Network Activity to 0 (server offline)');
            }
        } else if (title.textContent.includes('Response Time')) {
            const newResponseValue = isServerOnline ? parseFloat(eventData.response_time || 0).toFixed(1) : '0.0';
            valueEl.textContent = newResponseValue;
            console.log('✅ Updated Response Time to:', isServerOnline ? newResponseValue + 'ms' : newResponseValue);
        } else if (title.textContent.includes('Network Throughput')) {
            if (isServerOnline) {
                const throughputKBps = actualThroughput.toFixed(1);
                valueEl.textContent = throughputKBps;
                console.log('✅ Updated Network Throughput to:', throughputKBps + ' KB/s (actual)');
            } else {
                valueEl.textContent = '0.0';
                console.log('✅ Updated Network Throughput to 0 (server offline)');
            }
        } else if (title.textContent.includes('System Uptime') || title.textContent.includes('System Downtime')) {
            const uptimeLabel = card.querySelector('.text-xs.text-gray-500.mt-1');
            const icon = card.querySelector('i');

            if (isServerOnline) {
                title.textContent = 'System Uptime';
                const uptimeText = eventData.system_uptime || '0s';
                valueEl.textContent = uptimeText;
                valueEl.classList.remove('text-red-900');
                valueEl.classList.add('text-gray-900');
                console.log('✅ Updated System Uptime to:', uptimeText);
                if (uptimeLabel) uptimeLabel.textContent = 'Current Uptime';
                if (icon) icon.className = 'fas fa-server text-blue-500';
            } else {
                title.textContent = 'System Downtime';
                const downtimeText = humanizeSeconds(eventData.current_downtime || 0);
                valueEl.textContent = downtimeText;
                valueEl.classList.remove('text-gray-900');
                valueEl.classList.add('text-red-900');
                console.log('✅ Updated System Downtime to:', downtimeText);
                if (uptimeLabel) uptimeLabel.textContent = 'Current Downtime';
                if (icon) icon.className = 'fas fa-server text-red-500';
                // This card should remain prominent for offline
                card.classList.remove('opacity-75');
                card.classList.add('border-red-300', 'shadow-md', 'downtime-pulse');
            }
        }
    });

    // Update server status badge
    const statusBadge = document.querySelector('.server-status-badge');
    if (statusBadge) {
        statusBadge.textContent = isServerOnline ? 'Online' : 'Offline';
        const badgeClass = isServerOnline ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800 animate-pulse';
        statusBadge.className = `server-status-badge px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full ${badgeClass}`;
    }
}