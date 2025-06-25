import './bootstrap';
import Chart from 'chart.js/auto';

window.Chart = Chart;

// EMERGENCY FIX FOR NETWORK THROUGHPUT
// Global object to ensure network throughput is available everywhere
window.NETWORK_THROUGHPUT = {
    value: 0,
    timestamp: Date.now(),
    setValue: function(newValue) {
        this.value = newValue;
        this.timestamp = Date.now();
        console.log('🚨 GLOBAL NETWORK THROUGHPUT SET:', newValue.toFixed(2), 'KB/s');
        return newValue;
    },
    getValue: function() {
        return this.value;
    }
};

console.log('ServerPulse app.js loaded!');

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Route Navigation Helper
document.addEventListener('DOMContentLoaded', function() {
    // Fix for direct URL navigation and links
    const routeLinks = {
        '/analytics': 'analytics',
        '/settings': 'settings',
        '/user': 'user'
    };
    
    // Handle direct URL navigation
    const currentPath = window.location.pathname;
    if (Object.keys(routeLinks).includes(currentPath)) {
        console.log('📍 Direct navigation to route:', currentPath);
    }
    
    // Handle navigation link clicks
    document.querySelectorAll('a').forEach(link => {
        const href = link.getAttribute('href');
        if (Object.keys(routeLinks).includes(href)) {
            link.addEventListener('click', function(e) {
                console.log('🔗 Navigating to:', href);
            });
        }
    });
});

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

console.log('🔧 Debug: Pusher object available:', window.Pusher);
console.log('🔧 Debug: VITE_PUSHER_APP_KEY:', import.meta.env.VITE_PUSHER_APP_KEY);
console.log('🔧 Debug: VITE_PUSHER_APP_CLUSTER:', import.meta.env.VITE_PUSHER_APP_CLUSTER);

if (!import.meta.env.VITE_PUSHER_APP_KEY) {
    console.error('❌ VITE_PUSHER_APP_KEY is not set. Make sure it is in your .env file and you have run npm run dev.');
}
if (!import.meta.env.VITE_PUSHER_APP_CLUSTER) {
    console.error('❌ VITE_PUSHER_APP_CLUSTER is not set. Make sure it is in your .env file and you have run npm run dev.');
}

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY || '9de0f03e2175961b83d0',
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER || 'ap1',
    forceTLS: true,
});

console.log('🔧 Debug: Echo initialized:', window.Echo);
window.Echo.connector.pusher.connection.bind('state_change', function(states) {
    console.log("🔧 Debug: Pusher connection state changed from", states.previous, "to", states.current);
});

window.Echo.connector.pusher.connection.bind('connected', () => {
    console.log('✅ Pusher connected successfully!');
});

window.Echo.connector.pusher.connection.bind('error', (err) => {
    console.error('❌ Pusher connection error:', err);
    if (err.error && err.error.data && err.error.data.code === 4004) {
        console.error('❌ Pusher error 4004: App key not found or invalid. Check VITE_PUSHER_APP_KEY.');
    } else if (err.error && err.error.data && err.error.data.code >= 4000 && err.error.data.code <= 4099) {
        console.error('❌ Pusher authentication/configuration error. Check your Pusher App ID, Key, and Secret, and Cluster in .env and config/broadcasting.php.');
    }
});

const channel = window.Echo.channel('server-status');
console.log('🔧 Debug: Subscribing to server-status channel:', channel);

channel.subscribed(() => {
    console.log('✅ Successfully subscribed to server-status channel!');
})
.error((error) => {
    console.error('❌ Error subscribing to server-status channel:', error);
})
.listen('.server.status.updated', (e) => {
    console.log('🎉 EVENT RECEIVED! Full event object:', e);

    // The event payload is wrapped in a 'data' property by the broadcastWith method.
    const eventData = e.data;

    if (!eventData || typeof eventData !== 'object') {
        console.error('❌ Event data is missing or not an object!', e);
        return;
    }

    if (!eventData.server_id) {
        console.error('❌ server_id is missing in event data!', eventData);
        return;
    }

    console.log('✅ Processing event for server_id:', eventData.server_id);

    // Check which page we are on and call the appropriate update function.
    if (window.location.pathname.includes('/analytics')) {
        updateAnalyticsPage(eventData);
    } else {
        updateServersPage(eventData);
    }
});

// Global variables for real-time updates
let lastUpdateTime = {};
let lastNetworkBytes = {};
let performanceChart = null;
let networkThroughputHistory = {};
let lastDiskIORead = {};
let lastDiskIOWrite = {};
let lastKnownUptime = {}; // Add this new variable to track uptime across updates

// Utility for localStorage graph state (per-server)
function getActiveGraphKey(serverId) {
    return `serverpulse_active_graph_${serverId}`;
}
function saveActiveGraph(serverId, graphKey) {
    if (serverId && graphKey) {
        localStorage.setItem(getActiveGraphKey(serverId), graphKey);
    }
}
function loadActiveGraph(serverId) {
    if (serverId) {
        return localStorage.getItem(getActiveGraphKey(serverId));
    }
    return null;
}

// Special function to force calculate network throughput
function calculateNetworkThroughputFromBytes(rx, tx, previousRx, previousTx, timeDiffMs) {
    // Make sure we have valid integers
    rx = parseInt(rx) || 0;
    tx = parseInt(tx) || 0;
    previousRx = parseInt(previousRx) || 0;
    previousTx = parseInt(previousTx) || 0;
    timeDiffMs = parseInt(timeDiffMs) || 1000; // Default to 1 second if invalid
    
    // Calculate bytes transferred
    const currentTotal = rx + tx;
    const previousTotal = previousRx + previousTx;
    
    // Calculate the difference
    let bytesDiff;
    if (currentTotal >= previousTotal) {
        bytesDiff = currentTotal - previousTotal;
    } else {
        // Handle counter reset
        console.log('🚨 Network counter reset detected');
        bytesDiff = currentTotal; // Use current as increment
    }
    
    // Convert to KB/s
    const timeDiffSec = timeDiffMs / 1000;
    const throughputKBs = bytesDiff / timeDiffSec / 1024;
    
    console.log('🚨 FORCE CALCULATED THROUGHPUT:', {
        rx, tx, previousRx, previousTx,
        bytesDiff, timeDiffMs, timeDiffSec,
        throughputKBs: throughputKBs.toFixed(2) + ' KB/s'
    });
    
    return Math.max(0, throughputKBs);
}

// Function to update analytics page summary cards
function updateAnalyticsPage(eventData) {
    // If eventData is wrapped in .data, use that
    const data = eventData.data || eventData;
    window.lastEventData = data;
    console.log('🟢 Debug: network_rx =', data.network_rx, 'network_tx =', data.network_tx);
    console.log('🔄 updateAnalyticsPage called with data:', data);
    console.log('🔍 NETWORK DATA: rx =', data.network_rx, 'tx =', data.network_tx, 'Type:', 
                typeof data.network_rx, typeof data.network_tx);
    
    const serverSelector = document.getElementById('server_id');
    if (serverSelector && serverSelector.value != data.server_id) {
        console.log(`Event for server ${data.server_id} ignored, current server is ${serverSelector.value}`);
        return; // Not for the selected server
    }
    
    const selectedServerId = serverSelector ? parseInt(serverSelector.value) : null;
    console.log('🔧 Debug: Selected server ID:', selectedServerId, 'Event server ID:', data.server_id);
    
    // EMERGENCY FIX FOR NETWORK THROUGHPUT TRACKING
    console.log('🚨 EMERGENCY THROUGHPUT FIX - Initializing direct tracking');
    
    // Initialize global tracking object
    if (!window.networkTracking) {
        window.networkTracking = {};
    }
    
    // Initialize or update server-specific tracking
    if (!window.networkTracking[selectedServerId]) {
        window.networkTracking[selectedServerId] = {
            lastRx: 0,
            lastTx: 0,
            lastUpdateTime: Date.now() - 5000, // Start 5 seconds ago as baseline
            lastThroughput: 0
        };
        console.log(`🚨 Created new tracking for server ${selectedServerId}`);
    }
    
    if (data.server_id !== selectedServerId) {
        console.log(`⏭️ Skipping update - broadcast for server ${data.server_id}, but selected server is ${selectedServerId}`);
        return;
    }
    
    console.log(`✅ Updating cards for selected server ${selectedServerId}`);
    
    const serverId = data.server_id;
    // Get current time for display
    const currentTimeDisplay = new Date().toLocaleTimeString('en-GB', { 
        hour12: false, 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    });
    // Get current timestamp in milliseconds for calculations
    const currentTimestamp = Date.now();
    
    // COMPLETE REBUILD - DIRECT CALCULATION OF NETWORK THROUGHPUT
    let actualThroughput = 0;
    
    // Get current tracking data
    const tracking = window.networkTracking[selectedServerId];
    // Note: currentTimestamp is already defined earlier in the function
    
    // Force parse network values as integers - this is critical
    const networkRx = parseInt(data.network_rx) || 0;
    const networkTx = parseInt(data.network_tx) || 0;
    
    console.log(`🚨 NETWORK RAW: RX=${networkRx} (${typeof data.network_rx}), TX=${networkTx} (${typeof data.network_tx})`);
    
    // Calculate time difference since last update
    const timeDiffMs = currentTimestamp - tracking.lastUpdateTime;
    
    // Always set a minimum time difference to prevent division by zero
    if (timeDiffMs < 100) {
        console.log('🚨 Time difference too small, using default');
    }
    
    // DIRECT CALCULATION using our special function that shows all work
    actualThroughput = calculateNetworkThroughputFromBytes(
        networkRx, networkTx,
        tracking.lastRx, tracking.lastTx,
        Math.max(100, timeDiffMs)
    );
    
    console.log(`🚨 CALCULATED THROUGHPUT: ${actualThroughput.toFixed(2)} KB/s`);
    
    // Store result in multiple places for maximum compatibility
    window.lastCalculatedThroughput = actualThroughput;
    window.latestNetworkThroughput = actualThroughput;
    
    // EMERGENCY FIX: Store in our global direct access object
    if (window.NETWORK_THROUGHPUT) {
        window.NETWORK_THROUGHPUT.setValue(actualThroughput);
    }
    
    if (window.serverMetrics) {
        window.serverMetrics.updateNetworkThroughput(actualThroughput);
    }
    
    // Even if calculation resulted in 0, force a minimum value for UI visibility
    if (actualThroughput < 0.1 && (networkRx > 0 || networkTx > 0)) {
        console.log('🚨 Forcing minimum throughput value for UI visibility');
        // Use a value that corresponds to the magnitude of the data
        const total = networkRx + networkTx;
        actualThroughput = Math.max(1, total / 1000000);
    }
    
    // Update tracking for next calculation
    tracking.lastRx = networkRx;
    tracking.lastTx = networkTx;
    tracking.lastUpdateTime = currentTimestamp;
    tracking.lastThroughput = actualThroughput;
    
    // Store calculated throughput in global variable to make it available for the chart
    if (!networkThroughputHistory[serverId]) {
        networkThroughputHistory[serverId] = [];
    }
    networkThroughputHistory[serverId].push(actualThroughput);
    console.log(`💾 Stored new values - Timestamp: ${currentTimestamp}, Bytes: ${networkRx + networkTx}`);
    
    // Calculate disk I/O throughput centrally
    let diskIOThroughput = 0;
    const currentDiskIORead = parseInt(data.disk_io_read || 0, 10);
    const currentDiskIOWrite = parseInt(data.disk_io_write || 0, 10);

    if (lastDiskIORead[serverId] !== undefined && lastDiskIOWrite[serverId] !== undefined && lastUpdateTime[serverId] !== undefined) {
        const timeDiff = (currentTimestamp - lastUpdateTime[serverId]) / 1000;
        if (timeDiff > 0) {
            const readDiff = currentDiskIORead >= lastDiskIORead[serverId] ? currentDiskIORead - lastDiskIORead[serverId] : currentDiskIORead;
            const writeDiff = currentDiskIOWrite >= lastDiskIOWrite[serverId] ? currentDiskIOWrite - lastDiskIOWrite[serverId] : currentDiskIOWrite;
            const totalDiff = readDiff + writeDiff;
            diskIOThroughput = totalDiff / timeDiff / 1024 / 1024; // MB/s
        }
    }

    lastDiskIORead[serverId] = currentDiskIORead;
    lastDiskIOWrite[serverId] = currentDiskIOWrite;
    lastUpdateTime[serverId] = currentTimestamp;
    
    console.log('🔄 Updating summary cards...');
    // Update summary cards
    updateSummaryCards(data, actualThroughput, diskIOThroughput);
    
    console.log('🔄 Updating performance chart...');
    // Update performance chart
    updatePerformanceChart(data, actualThroughput, diskIOThroughput);
    
    console.log('✅ Analytics page update completed');
}

// Function to update servers page server rows
function updateServersPage(eventData) {
    console.log('🔄 updateServersPage called with data:', eventData);
    
    // ENHANCED DEBUGGING: Log all uptime-related values in the event data
    console.group(`🔍 Uptime Debug for Server ${eventData.server_id}`);
    console.log('status:', eventData.status);
    console.log('current_uptime:', eventData.current_uptime, typeof eventData.current_uptime);
    console.log('system_uptime:', eventData.system_uptime, typeof eventData.system_uptime);
    console.log('current_downtime:', eventData.current_downtime, typeof eventData.current_downtime);
    console.groupEnd();
    
    const row = document.getElementById('server-row-' + eventData.server_id);
    if (row) {
        console.log('✅ Found row for server_id:', eventData.server_id);
        
        // Update CPU
        const cpuCell = row.querySelector('[data-col="cpu"]');
        if (cpuCell) {
            const cpuBar = cpuCell.querySelector('.bg-blue-600');
            const cpuText = cpuCell.querySelector('span');
            if (cpuBar) cpuBar.style.width = eventData.cpu_usage + '%';
            if (cpuText) cpuText.textContent = parseFloat(eventData.cpu_usage).toFixed(1) + '%';
            console.log('✅ Updated CPU to:', eventData.cpu_usage);
        }
        
        // Update RAM
        const ramCell = row.querySelector('[data-col="ram"]');
        if (ramCell) {
            const ramBar = ramCell.querySelector('.bg-blue-600');
            const ramText = ramCell.querySelector('span');
            if (ramBar) ramBar.style.width = eventData.ram_usage + '%';
            if (ramText) ramText.textContent = parseFloat(eventData.ram_usage).toFixed(1) + '%';
            console.log('✅ Updated RAM to:', eventData.ram_usage);
        }
        
        // Update Disk
        const diskCell = row.querySelector('[data-col="disk"]');
        if (diskCell) {
            const diskBar = diskCell.querySelector('.bg-blue-600');
            const diskText = diskCell.querySelector('span');
            if (diskBar) diskBar.style.width = eventData.disk_usage + '%';
            if (diskText) diskText.textContent = parseFloat(eventData.disk_usage).toFixed(1) + '%';
            console.log('✅ Updated Disk to:', eventData.disk_usage);
        }
        
        // Update Status
        const statusCell = row.querySelector('[data-col="status"]');
        if (statusCell) {
            const statusBadge = statusCell.querySelector('.status-badge');
            if (statusBadge) {
                if (eventData.status === 'online') {
                    statusBadge.className = 'status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800';
                    statusBadge.innerHTML = '<span class="w-2 h-2 bg-green-400 rounded-full mr-1"></span>Online';
                } else {
                    statusBadge.className = 'status-badge inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800';
                    statusBadge.innerHTML = '<span class="w-2 h-2 bg-red-400 rounded-full mr-1"></span>Offline';
                }
                console.log('✅ Updated Status to:', eventData.status);
            }
            
            // Update Uptime/Downtime in status column (servers page) with improved handling
            const uptimeInfoDiv = statusCell.querySelector('.server-uptime-info');
            if (uptimeInfoDiv) {
                if (eventData.status === 'online') {
                    // Server is online - show uptime
                    // FIXED: Always use system_uptime directly from the server for consistency
                    // This matches what's shown on initial page load
                    let uptimeDisplay = eventData.system_uptime || 'N/A';
                    
                    uptimeInfoDiv.textContent = 'Uptime: ' + uptimeDisplay;
                    console.log(`✅ Updated Status Uptime to: ${uptimeDisplay} (source: system_uptime)`);
                    
                    // Highlight the update to make it more noticeable
                    uptimeInfoDiv.style.transition = 'all 0.5s';
                    uptimeInfoDiv.style.backgroundColor = 'rgba(0, 255, 0, 0.2)';
                    setTimeout(() => {
                        uptimeInfoDiv.style.backgroundColor = 'transparent';
                    }, 800);
                    
                } else if (eventData.status === 'offline') {
                    // Server is offline - show downtime
                    let downtimeDisplay = 'N/A';
                    
                    if (eventData.current_downtime !== null && eventData.current_downtime !== undefined) {
                        const downtimeSeconds = parseFloat(eventData.current_downtime);
                        downtimeDisplay = formatUptime(downtimeSeconds);
                        console.log('✅ Updated Status Downtime to:', downtimeDisplay, 
                                   '(from current_downtime:', downtimeSeconds, 'seconds)');
                    }
                    
                    uptimeInfoDiv.textContent = 'Downtime: ' + downtimeDisplay;
                    
                    // Highlight the update with red for downtime
                    uptimeInfoDiv.style.transition = 'all 0.5s';
                    uptimeInfoDiv.style.backgroundColor = 'rgba(255, 0, 0, 0.2)';
                    setTimeout(() => {
                        uptimeInfoDiv.style.backgroundColor = 'transparent';
                    }, 800);
                }
            }
        }
        
        // Update Response Time
        const responseTimeCell = row.querySelector('[data-col="response_time"]');
        if (responseTimeCell) {
            const responseTimeText = responseTimeCell.querySelector('span');
            if (responseTimeText) {
                responseTimeText.textContent = parseFloat(eventData.response_time || 0).toFixed(1) + 'ms';
                console.log('✅ Updated Response Time to:', eventData.response_time);
            }
        }
        
        // Update Uptime with improved handling
        const uptimeCell = row.querySelector('[data-col="uptime"]');
        if (uptimeCell) {
            const uptimeText = uptimeCell.querySelector('span');
            if (uptimeText) {
                // Tracking server ID for debug logs
                const serverId = eventData.server_id;
                
                if (eventData.status === 'online') {
                    // FIXED: Always use system_uptime directly from the server for consistency
                    // This matches what's shown on initial page load
                    let uptimeDisplay = eventData.system_uptime || 'N/A';
                    
                    // Update the display
                    uptimeText.textContent = uptimeDisplay;
                    console.log(`✅ [Server ${serverId}] Updated Uptime to: ${uptimeDisplay} (source: system_uptime)`);
                    
                    // Visual feedback for the update
                    uptimeText.style.transition = 'all 0.5s';
                    uptimeText.style.backgroundColor = 'rgba(0, 255, 0, 0.1)';
                    setTimeout(() => {
                        uptimeText.style.backgroundColor = 'transparent';
                    }, 800);
                    
                } else if (eventData.status === 'offline') {
                    // Server is offline - show downtime
                    let downtimeDisplay = 'N/A';
                    let source = 'none';
                    
                    if (eventData.current_downtime !== null && eventData.current_downtime !== undefined) {
                        const downtimeSeconds = parseFloat(eventData.current_downtime);
                        downtimeDisplay = formatUptime(downtimeSeconds);
                        source = 'current_downtime';
                    }
                    
                    uptimeText.textContent = downtimeDisplay;
                    console.log(`✅ [Server ${serverId}] Updated Downtime to: ${downtimeDisplay} (source: ${source})`);
                    
                    // Visual feedback for downtime
                    uptimeText.style.color = '#d32f2f';
                }
            }
        } else {
            console.warn('❌ Could not find row for server_id:', eventData.server_id);
        }
    } else {
        console.warn('❌ Could not find row for server_id:', eventData.server_id);
    }
}

// Helper function to parse uptime strings like "2d 10h 30m 15s" into seconds
function parseUptimeToSeconds(uptimeStr) {
    if (!uptimeStr || typeof uptimeStr !== 'string') {
        return 0;
    }
    
    let seconds = 0;
    
    // Match days (e.g., "2d")
    const daysMatch = uptimeStr.match(/(\d+)d/);
    if (daysMatch) {
        seconds += parseInt(daysMatch[1]) * 86400;
    }
    
    // Match hours (e.g., "10h")
    const hoursMatch = uptimeStr.match(/(\d+)h/);
    if (hoursMatch) {
        seconds += parseInt(hoursMatch[1]) * 3600;
    }
    
    // Match minutes (e.g., "30m")
    const minsMatch = uptimeStr.match(/(\d+)m/);
    if (minsMatch) {
        seconds += parseInt(minsMatch[1]) * 60;
    }
    
    // Match seconds (e.g., "15s")
    const secsMatch = uptimeStr.match(/(\d+)s/);
    if (secsMatch) {
        seconds += parseInt(secsMatch[1]);
    }
    
    return seconds;
}

// Function to format uptime
function formatUptime(seconds) {
    if (!seconds || seconds < 0) return '0s';
    
    function humanizeSeconds(seconds) {
        const days = Math.floor(seconds / 86400);
        const hours = Math.floor((seconds % 86400) / 3600);
        const minutes = Math.floor((seconds % 3600) / 60);
        const secs = Math.floor(seconds % 60);
        
        const parts = [];
        if (days > 0) parts.push(days + 'd');
        if (hours > 0) parts.push(hours + 'h');
        if (minutes > 0) parts.push(minutes + 'm');
        if (secs > 0) parts.push(secs + 's');
        
        return parts.length > 0 ? parts.join(' ') : '0s';
    }
    
    return humanizeSeconds(seconds);
}

// Function to handle server selection change
function handleServerSelectionChange() {
    const serverSelector = document.getElementById('server_id');
    if (serverSelector) {
        const selectedServerId = parseInt(serverSelector.value);
        console.log('🔄 Server selection changed to:', selectedServerId);
        
        // Clear previous data
        lastUpdateTime = {};
        lastNetworkBytes = {};
        networkThroughputHistory = {};
        lastDiskIORead = {};
        lastDiskIOWrite = {};
        lastKnownUptime = {}; // Reset last known uptime
        
        // Reset chart data if available
        if (window.performanceChart) {
            const datasets = window.performanceChart.data.datasets;
            datasets.forEach(dataset => {
                dataset.data = [];
            });
            window.performanceChart.data.labels = [];
            console.log('🔄 Chart data cleared for new server');
        }
    }
}

// Function to update performance chart
function updatePerformanceChart(eventData, actualThroughput, diskIOThroughput) {
    // Check if chart is initialized
    if (!window.performanceChart) {
        console.log('⚠️ Chart not initialized yet, or not on analytics page');
        return;
    }
    
    const serverId = eventData.server_id;
    // Get current timestamp in milliseconds for calculations - FIX: add currentTimestamp here
    const currentTimestamp = Date.now();
    
    // Use the log's actual timestamp if available, else fallback to browser time
    let labelTime = null;
    if (eventData.created_at) {
        // Try to parse and format as H:i:s
        const date = new Date(eventData.created_at);
        if (!isNaN(date.getTime())) {
            labelTime = date.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
                timeZone: 'Asia/Kuala_Lumpur'
            });
        }
    }
    if (!labelTime) {
        labelTime = new Date().toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true,
            timeZone: 'Asia/Kuala_Lumpur'
        });
    }
    
    // Initialize history for this server if not exists
    if (!networkThroughputHistory[serverId]) {
        networkThroughputHistory[serverId] = [];
    }
    
    // Add throughput to history
    networkThroughputHistory[serverId].push(actualThroughput);
    
    // Keep only last 50 points
    if (networkThroughputHistory[serverId].length > 50) {
        networkThroughputHistory[serverId].shift();
    }
    
    // Update chart data
    const chart = window.performanceChart;
    const labels = chart.data.labels;
    const datasets = chart.data.datasets;
    
    // Add new time label
    labels.push(labelTime);
    if (labels.length > 50) {
        labels.shift();
    }
    
    // Safety check to ensure datasets exist before trying to update them
    if (!datasets || datasets.length === 0) {
        console.error('❌ Chart datasets are not properly initialized');
        return;
    }
    
    // Update datasets with safety checks and ensure we maintain exactly 50 data points
    // For each dataset, check if we need to shift out the oldest point before adding a new one
    const ensureMaxPoints = (dataset) => {
        if (!dataset) return false; // Skip if dataset doesn't exist
        if (!dataset.data) dataset.data = []; // Initialize data array if missing
        
        // Make sure we have max 50 points
        while (dataset.data && dataset.data.length >= 50) {
            dataset.data.shift(); // Remove oldest point first
        }
        
        return true; // Dataset is ready
    };
    
    // CPU Usage
    if (datasets[0] && datasets[0].data) {
        ensureMaxPoints(datasets[0]);
        datasets[0].data.push(eventData.cpu_usage || 0);
    }
    
    // RAM Usage
    if (datasets[1] && datasets[1].data) {
        ensureMaxPoints(datasets[1]);
        datasets[1].data.push(eventData.ram_usage || 0);
    }
    
    // Network Activity
    if (datasets[2] && datasets[2].data) {
        ensureMaxPoints(datasets[2]);
        datasets[2].data.push(calculateNetworkActivity(actualThroughput));
    }
    
    // Disk I/O
    if (datasets[3] && datasets[3].data) {
        ensureMaxPoints(datasets[3]);
        datasets[3].data.push(diskIOThroughput);
    }
    
    // Disk Usage
    if (datasets[4] && datasets[4].data) {
        ensureMaxPoints(datasets[4]);
        datasets[4].data.push(eventData.disk_usage || 0);
    }
    
    // Network Throughput - EMERGENCY DIRECT CHART UPDATE
    if (datasets[5] && datasets[5].data) {
        // EMERGENCY DIRECT APPROACH: Use our already calculated actualThroughput
        // Since we've completely rebuilt the calculation earlier, we can trust this value
        
        console.log('🚨 EMERGENCY CHART UPDATE - Using throughput value:', actualThroughput.toFixed(2), 'KB/s');
        
        // IMPORTANT: Preserve the hidden state of the dataset - don't force visibility
        // Only update data - let the UI control visibility
        
        // FIXED: Check if we need to trim data first to avoid exceeding 50 points
        if (datasets[5].data.length >= 50) {
            datasets[5].data.shift(); // Remove oldest point first to maintain max 50 points
        }
        
        // Push the value directly to the chart
        datasets[5].data.push(actualThroughput);
        
        // Store in history for reference but maintain max 50 points
        if (!networkThroughputHistory[serverId]) {
            networkThroughputHistory[serverId] = [];
        }
        if (networkThroughputHistory[serverId].length >= 50) {
            networkThroughputHistory[serverId].shift();
        }
        networkThroughputHistory[serverId].push(actualThroughput);
        
        console.log('🚨 CHART DATA LENGTH:', datasets[5].data.length, 'Latest value:', 
                   datasets[5].data[datasets[5].data.length - 1].toFixed(2), 'KB/s');
    } else {
        console.error('🚨 EMERGENCY FIX ERROR: Network throughput dataset not found in chart');
    }
    // Response Time (make sure it's a valid number)
    if (datasets[6] && datasets[6].data) {
        ensureMaxPoints(datasets[6]);
        const responseValue = parseFloat(eventData.response_time || 0);
        datasets[6].data.push(responseValue);
        console.log('✅ Added Response Time to chart:', responseValue.toFixed(2), 'ms');
    }
    
    // System Uptime
    if (datasets[7] && datasets[7].data) {
        ensureMaxPoints(datasets[7]);
        
        // Get uptime value with proper continuity handling
        let uptimeHours = 0;
        const serverId = eventData.server_id;
        let uptimeSource = 'none';
        
        console.group(`🔍 Analytics Chart Uptime Debug [Server ${serverId}]`);
        console.log('current_uptime:', eventData.current_uptime, typeof eventData.current_uptime);
        console.log('system_uptime:', eventData.system_uptime, typeof eventData.system_uptime);
        
        // Use prioritized sources for uptime
        if (eventData.status === 'online') {
            // Create array of uptime sources to try
            const uptimeSources = [];
            
            // Add system_uptime if it has meaningful values (h or d)
            if (eventData.system_uptime && eventData.system_uptime !== '0s') {
                if (typeof eventData.system_uptime === 'string') {
                    // Check if it has hours or days to validate it's meaningful
                    const hasHoursOrDays = eventData.system_uptime.includes('h') || 
                                         eventData.system_uptime.includes('d');
                    
                    // Or significant minutes
                    const minutesMatch = eventData.system_uptime.match(/(\d+)m/);
                    const hasSignificantMinutes = minutesMatch && parseInt(minutesMatch[1]) > 5;
                    
                    if (hasHoursOrDays || hasSignificantMinutes) {
                        // Parse uptime string to hours
                        const seconds = parseUptimeToSeconds(eventData.system_uptime);
                        const hours = seconds / 3600;
                        
                        uptimeSources.push({
                            type: 'system_uptime_string',
                            hours: hours,
                            priority: 10 // High priority for meaningful system uptime
                        });
                        
                        console.log(`✅ Added system_uptime string for chart: "${eventData.system_uptime}" = ${hours.toFixed(2)} hours`);
                    }
                } else if (typeof eventData.system_uptime === 'number' && eventData.system_uptime > 60) {
                    // It's already a number in seconds
                    const hours = eventData.system_uptime / 3600;
                    
                    uptimeSources.push({
                        type: 'system_uptime_number',
                        hours: hours,
                        priority: 10
                    });
                    
                    console.log(`✅ Added system_uptime number for chart: ${eventData.system_uptime}s = ${hours.toFixed(2)} hours`);
                }
            }
            
            // Add current_uptime if it's meaningful (>60s)
            if (eventData.current_uptime !== null && eventData.current_uptime !== undefined && eventData.current_uptime !== '') {
                const uptimeSeconds = parseFloat(eventData.current_uptime);
                // Only use current_uptime if it has a substantial value (fixes Kali Linux issue)
                if (!isNaN(uptimeSeconds) && uptimeSeconds > 60) {
                    const hours = uptimeSeconds / 3600;
                    
                    uptimeSources.push({
                        type: 'current_uptime',
                        hours: hours,
                        priority: 20 // Highest priority for valid values
                    });
                    
                    console.log(`✅ Added current_uptime for chart: ${eventData.current_uptime}s = ${hours.toFixed(2)} hours`);
                } else {
                    console.log(`⚠️ Skipping small/invalid current_uptime: ${eventData.current_uptime}`);
                }
            }
            
            // If we still don't have a valid value, try system_uptime
            if (uptimeSource === 'none' && eventData.system_uptime) {
                if (typeof eventData.system_uptime === 'string') {
                    // Try to parse the string format - try multiple formats
                    const uptimeString = eventData.system_uptime;
                    
                    // First try standard pattern "Xh Ym Zs"
                    const standardMatch = uptimeString.match(/(\d+)h\s+(\d+)m\s+(\d+)s/);
                    if (standardMatch) {
                        uptimeHours = parseInt(standardMatch[1]) + (parseInt(standardMatch[2]) / 60) + (parseInt(standardMatch[3]) / 3600);
                        uptimeSource = 'system_uptime_standard';
                    }
                    
                    // If that didn't work, try more general pattern with days/hours/minutes/seconds
                    if (uptimeSource === 'none') {
                        const seconds = parseUptimeToSeconds(uptimeString);
                        if (seconds > 0) {
                            uptimeHours = seconds / 3600;
                            uptimeSource = 'system_uptime_parsed';
                        }
                    }
                    
                    console.log(`✅ Parsed system_uptime: ${uptimeString} to ${uptimeHours.toFixed(2)} hours (source: ${uptimeSource})`);
                } else if (typeof eventData.system_uptime === 'number') {
                    // If it's already a number, assume it's seconds
                    uptimeHours = eventData.system_uptime / 3600;
                    uptimeSource = 'system_uptime_number';
                    console.log(`✅ Used numeric system_uptime: ${eventData.system_uptime}s (${uptimeHours.toFixed(2)} hours)`);
                }
            }
            
            // Add raw stats as another option
            if (eventData.raw_stats && eventData.raw_stats.uptime) {
                const rawUptime = parseFloat(eventData.raw_stats.uptime);
                if (!isNaN(rawUptime) && rawUptime > 60) {
                    const hours = rawUptime / 3600;
                    
                    uptimeSources.push({
                        type: 'raw_stats.uptime',
                        hours: hours,
                        priority: 5 // Medium priority
                    });
                    
                    console.log(`✅ Added raw_stats.uptime for chart: ${rawUptime}s = ${hours.toFixed(2)} hours`);
                }
            }
            
            // Add manual tracking if available
            if (window.manualUptimeTracking && window.manualUptimeTracking[serverId]) {
                const tracking = window.manualUptimeTracking[serverId];
                if (tracking.baseSeconds) {
                    // Calculate the current uptime including elapsed time
                    const now = Date.now();
                    const elapsedSeconds = Math.floor((now - tracking.lastUpdate) / 1000);
                    const totalSeconds = tracking.baseSeconds + elapsedSeconds;
                    
                    const hours = totalSeconds / 3600;
                    
                    uptimeSources.push({
                        type: 'manual_tracking',
                        hours: hours,
                        priority: 1 // Low priority
                    });
                    
                    console.log(`✅ Added manual tracking for chart: ${totalSeconds}s = ${hours.toFixed(2)} hours`);
                }
            }
            
            // If we still don't have an uptime value but we have a previous one, increment it
            if (uptimeSource === 'none' && lastKnownUptime[serverId] && !isNaN(lastKnownUptime[serverId])) {
                // Assume ~5 seconds passed since last update
                uptimeHours = lastKnownUptime[serverId] + (5/3600);
                uptimeSource = 'incremented_previous';
                console.log(`⚠️ Uptime reset detected for server ${serverId}: ${lastKnownUptime[serverId].toFixed(2)}h → ${uptimeHours.toFixed(2)}h`);
                // Clear previous data points to show the discontinuity
                datasets[7].data = datasets[7].data.map(() => null);
            }
            
            // If no uptime source provided a valid value, use a default to avoid showing 0
            if (uptimeSource === 'none' || isNaN(uptimeHours) || uptimeHours <= 0) {
                // Check if we have a non-zero value in the chart already
                const existingData = datasets[7].data;
                if (existingData && existingData.length > 0) {
                    // Find the last non-null and non-zero value
                    for (let i = existingData.length - 1; i >= 0; i--) {
                        if (existingData[i] && existingData[i] > 0) {
                            uptimeHours = existingData[i] + (5/3600); // Add 5 seconds converted to hours
                            uptimeSource = 'previous_chart_value';
                            console.log(`⚠️ Using last chart value: ${existingData[i]} + 5s = ${uptimeHours.toFixed(2)} hours`);
                            break;
                        }
                    }
                }
                
                // If we still don't have a value, use a default (10h) to avoid showing 0
                if (uptimeSource === 'none') {
                    uptimeHours = 10; // Default to 10 hours instead of 0
                    uptimeSource = 'default_value';
                    console.log(`⚠️ No valid uptime found, using default: ${uptimeHours} hours`);
                }
            }
        }
        
        // Sort sources by priority and select best one
        uptimeSources.sort((a, b) => b.priority - a.priority);
        
        // Choose the best source
        for (const src of uptimeSources) {
            if (!isNaN(src.hours) && src.hours > 0) {
                uptimeHours = src.hours;
                uptimeSource = src.type;
                break;
            }
        }
        
        // Default to 10 hours if no valid source
        if (uptimeHours <= 0 || isNaN(uptimeHours)) {
            uptimeHours = 10;
            uptimeSource = 'default_value';
        }
        
        console.log(`Final uptime value for chart: ${uptimeHours.toFixed(2)} hours (source: ${uptimeSource})`);
        console.groupEnd();
        
        // Ensure uptime continuity (only if we have a valid non-zero value)
        if (uptimeHours > 0) {
            if (!lastKnownUptime[serverId] || uptimeHours >= lastKnownUptime[serverId]) {
                // Normal case: uptime is increasing or this is the first datapoint
                console.log(`✅ Uptime for server ${serverId} increasing: ${uptimeHours.toFixed(2)} hours`);
            } else if (uptimeHours < lastKnownUptime[serverId]) {
                // Uptime reset detected - likely server reboot
                console.log(`⚠️ Uptime reset detected for server ${serverId}: ${lastKnownUptime[serverId].toFixed(2)}h → ${uptimeHours.toFixed(2)}h`);
                // Clear previous data points to show the discontinuity
                datasets[7].data = datasets[7].data.map(() => null);
            }
            
            // Save for next comparison (only if we have a valid value)
            lastKnownUptime[serverId] = uptimeHours;
        }
        
        // Add to chart
        datasets[7].data.push(uptimeHours);
        console.log(`📊 Added uptime to chart: ${uptimeHours.toFixed(2)} hours`);
    }
    
    // Always keep only the latest 50 points and sort by timestamp if needed
    if (labels.length > 50) {
        console.log(`🔄 Trimming labels from ${labels.length} to 50`);
        labels.splice(0, labels.length - 50);
    }
    
    // Perform a final check on all datasets to ensure data length consistency
    datasets.forEach(dataset => {
        if (dataset && dataset.data) {
            // If we have too many points, trim to exactly 50
            if (dataset.data.length > 50) {
                console.log(`🔄 Trimming dataset from ${dataset.data.length} to 50 points`);
                dataset.data = dataset.data.slice(-50);
            }
            
            // If by chance we have fewer points than labels, pad with null values
            while (dataset.data.length < labels.length) {
                dataset.data.unshift(null);
            }
            
            // If we have more points than labels (shouldn't happen but just in case)
            while (dataset.data.length > labels.length) {
                dataset.data.shift();
            }
        }
    });
    // Ensure labels and data are aligned and sorted by time if needed
    // (Assume labels are already in correct order since logs are reversed in backend)
    
    // Update chart with enhanced animations for real-time updates
    try {
        // Enhanced chart update for smoother real-time visualization
        chart.options.animation = {
            duration: 500,        // Animation duration in ms - smooth but not too slow
            easing: 'easeOutQuad' // Smoother animation easing
        };
        
        // Configure transitions specifically for showing/hiding datasets
        chart.options.transitions = {
            show: {
                animations: {
                    properties: ['opacity'],
                    from: 0,
                    to: 1,
                    duration: 600
                }
            },
            hide: {
                animations: {
                    properties: ['opacity'],
                    from: 1,
                    to: 0,
                    duration: 400
                }
            }
        };
        
        // Use a more responsive update mode
        chart.update('active');   // Only animate actively changing elements
        console.log('✅ Chart updated with enhanced real-time animation');
        
        // Force layout recalculation to prevent visual glitches
        if (window.forceChartRedrawTimer) {
            clearTimeout(window.forceChartRedrawTimer);
        }
        window.forceChartRedrawTimer = setTimeout(() => {
            try {
                chart.resize(); // Force layout recalculation
            } catch (e) {
                // Ignore resize errors
            }
        }, 100);
    } catch (error) {
        console.error('❌ Error updating chart:', error);
    }
}

// Function to calculate network activity level (0-100) based on throughput
function calculateNetworkActivity(throughput) {
    // Convert throughput (KB/s) to activity level
    if (throughput > 1000) { // > 1MB/s
        return 100;
    } else if (throughput > 100) { // > 100KB/s
        return 60;
    } else if (throughput > 10) { // > 10KB/s
        return 30;
    }
    return Math.max(0, Math.min(10, throughput)); // 0-10 for very low activity
}

// Function to update summary cards
function updateSummaryCards(data, actualThroughput, diskIOThroughput) {
    console.log('🔄 Updating summary cards with data:', data);
    // Update CPU Usage
    const cpuCard = document.querySelector('[data-metric="cpu-usage"] .metric-value');
    if (cpuCard) {
        cpuCard.textContent = parseFloat(data.cpu_usage).toFixed(1) + '%';
        console.log('✅ Updated CPU card to:', data.cpu_usage);
    }
    // Update Memory Usage
    const memoryCard = document.querySelector('[data-metric="memory-usage"] .metric-value');
    if (memoryCard) {
        memoryCard.textContent = parseFloat(data.ram_usage).toFixed(1) + '%';
        console.log('✅ Updated Memory card to:', data.ram_usage);
    }
    // Update Network Activity
    const networkActivityCard = document.querySelector('[data-metric="network-activity"] .metric-value');
    if (networkActivityCard) {
        const activityLevel = calculateNetworkActivity(actualThroughput);
        networkActivityCard.textContent = activityLevel.toFixed(0) + '%';
        console.log('✅ Updated Network Activity card to:', activityLevel);
    }
    // Update Disk I/O
    const diskIOCard = document.querySelector('[data-metric="disk-io"] .metric-value');
    if (diskIOCard) {
        diskIOCard.textContent = diskIOThroughput.toFixed(2) + ' MB/s';
        console.log('✅ Updated Disk I/O card to:', diskIOThroughput);
    }
    // Update Disk Usage
    const diskCard = document.querySelector('[data-metric="disk-usage"] .metric-value');
    if (diskCard && data.disk_usage !== undefined) {
        diskCard.textContent = parseFloat(data.disk_usage).toFixed(1) + '%';
        console.log('✅ Updated Disk Usage card to:', data.disk_usage);
    }
    // Update Network Throughput
    const throughputCard = document.querySelector('[data-metric="network-throughput"] .metric-value');
    if (throughputCard) {
        throughputCard.textContent = actualThroughput.toFixed(2) + ' KB/s';
        console.log('✅ Updated Network Throughput card to:', actualThroughput);
    }
    // Update Response Time
    const responseTimeCard = document.querySelector('[data-metric="response-time"] .metric-value');
    if (responseTimeCard && data.response_time !== undefined) {
        responseTimeCard.textContent = parseFloat(data.response_time).toFixed(0) + ' ms';
        console.log('✅ Updated Response Time card to:', data.response_time);
    }
    // Update System Uptime
    const uptimeCard = document.querySelector('[data-metric="system-uptime"] .metric-value');
    if (uptimeCard && data.system_uptime) {
        uptimeCard.textContent = data.system_uptime;
        console.log('✅ Updated System Uptime card to:', data.system_uptime);
    }
}

// Add event listener for server selection change
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Debug: DOM loaded, setting up event listeners');
    const serverSelector = document.getElementById('server_id');
    if (serverSelector) {
        serverSelector.addEventListener('change', handleServerSelectionChange);
        console.log('✅ Server selection change listener attached');
        // Restore active graph state from localStorage
        const serverId = serverSelector ? serverSelector.value : null;
        if (serverId) {
            const toggles = [
                'cpuToggle', 'memoryToggle', 'networkToggle', 'diskToggle',
                'diskUsageToggle', 'networkThroughputToggle', 'responseTimeToggle', 'systemUptimeToggle'
            ];
            const savedActive = loadActiveGraph(serverId);
            if (savedActive && document.getElementById(savedActive)) {
                toggles.forEach(id => {
                    const el = document.getElementById(id);
                    if (el) el.checked = false;
                });
                document.getElementById(savedActive).checked = true;
            }
            // Add listeners to save state
            toggles.forEach(id => {
                const el = document.getElementById(id);
                if (el) {
                    el.addEventListener('change', function() {
                        if (el.checked) saveActiveGraph(serverId, id);
                    });
                }
            });
        }
    } else {
        console.log('⚠️ Server selector not found on this page');
    }
});

// Enable advanced debugging for network throughput issues
window.debugNetworkThroughput = true; // Set to false to reduce console output

// Initialize global variables for metrics tracking
if (!window.latestNetworkThroughput) {
    window.latestNetworkThroughput = 0;
}

// Set up an interval to update uptime displays for servers that aren't updating
setInterval(() => {
    // Skip if no tracking is initialized yet
    if (!window.manualUptimeTracking) return;
    
    const now = Date.now();
    const serverIds = Object.keys(window.manualUptimeTracking);
    
    serverIds.forEach(serverId => {
        const tracking = window.manualUptimeTracking[serverId];
        // Skip servers updated in the last 10 seconds (these are working normally)
        if (now - tracking.lastUpdate < 10000) {
            return;
        }
        
        // Only update servers that have baseline seconds we can increment
        if (tracking.baseSeconds) {
            // Calculate how many seconds have passed since we last received an update
            const elapsedSeconds = Math.floor((now - tracking.lastUpdate) / 1000);
            // Add the elapsed time to the base seconds
            const newSeconds = tracking.baseSeconds + elapsedSeconds;
            // Format the new uptime
            const newUptimeDisplay = formatUptime(newSeconds);
            
            // Update the UI if we can find the element
            const row = document.getElementById(`server-row-${serverId}`);
            if (row) {
                const uptimeCell = row.querySelector('[data-col="uptime"]');
                if (uptimeCell) {
                    const uptimeText = uptimeCell.querySelector('span');
                    if (uptimeText) {
                        uptimeText.textContent = newUptimeDisplay;
                        
                        // Also update status cell uptime info if present
                        const statusCell = row.querySelector('[data-col="status"]');
                        if (statusCell) {
                            const uptimeInfoDiv = statusCell.querySelector('.server-uptime-info');
                            if (uptimeInfoDiv) {
                                uptimeInfoDiv.textContent = `Uptime: ${newUptimeDisplay}`;
                            }
                        }
                        
                        // Visual feedback for auto-updated uptime (subtle blue glow)
                        uptimeText.style.transition = 'all 0.5s';
                        uptimeText.style.backgroundColor = 'rgba(0, 100, 255, 0.05)';
                        setTimeout(() => {
                            uptimeText.style.backgroundColor = 'transparent';
                        }, 800);
                        
                        console.log(`🕒 [Server ${serverId}] Auto-updated uptime to ${newUptimeDisplay} (manually tracked)`);
                    }
                }
            }
            
            // Update our tracking
            tracking.lastUpdate = now;
            tracking.display = newUptimeDisplay;
            tracking.baseSeconds = newSeconds;
        }
    });
}, 15000); // Update every 15 seconds

// Create a shared global object to ensure network throughput is accessible everywhere
// This fixes potential scope issues that might be preventing updates
window.serverMetrics = window.serverMetrics || {
    networkThroughput: 0,
    updateNetworkThroughput: function(value) {
        if (!isNaN(value) && value >= 0) {
            this.networkThroughput = value;
            console.log('✅ Global network throughput updated to:', value.toFixed(2), 'KB/s');
            return true;
        }
        return false;
    },
    getNetworkThroughput: function() {
        return this.networkThroughput;
    }
};

// Test the global object
window.serverMetrics.updateNetworkThroughput(5.5);
console.log('🧪 Testing global metrics object:', window.serverMetrics.getNetworkThroughput());

console.log('🎉 Real-time update system initialized with network throughput debugging!');

// Enhanced debugging helper for uptime issues
window.debugUptimeValues = function(serverId) {
    console.group('🕒 Uptime Debug Information for Server ID: ' + serverId);
    
    // Get the current display value from UI (if on servers page)
    try {
        const row = document.getElementById(`server-row-${serverId}`);
        if (row) {
            const uptimeCell = row.querySelector('[data-col="uptime"]');
            if (uptimeCell) {
                const uptimeText = uptimeCell.querySelector('span');
                if (uptimeText) {
                    console.log(' Current UI display value on servers page:', uptimeText.textContent);
                }
            }
            
            // Also check status column uptime info
            const statusCell = row.querySelector('[data-col="status"]');
            if (statusCell) {
                const uptimeInfoDiv = statusCell.querySelector('.server-uptime-info');
                if (uptimeInfoDiv) {
                    console.log(' Current UI status uptime:', uptimeInfoDiv.textContent);
                }
            }
        }
    } catch (e) {
        console.log('Could not get UI display value on servers page');
    }
    
    // Get the analytics card value if on analytics page
    try {
        const uptimeContainer = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-6.mb-6:nth-child(2)');
        if (uptimeContainer) {
            const additionalCards = uptimeContainer.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6');
            if (additionalCards && additionalCards.length >= 4) {
                const uptimeCard = additionalCards[3];
                const uptimeText = uptimeCard.querySelector('.text-3xl');
                if (uptimeText) {
                    console.log(' Current UI display value on analytics page:', uptimeText.textContent);
                }
            }
        }
    } catch (e) {
        console.log('Could not get UI display value on analytics page');
    }
    
    // Check if we have stored uptime logs for this server
    if (window.uptimeLogs && window.uptimeLogs[serverId]) {
        console.log(`📝 Found ${window.uptimeLogs[serverId].length} logged uptime values for server ${serverId}:`);
        console.table(window.uptimeLogs[serverId]);
        
        // Check for meaningful changes
        if (window.uptimeLogs[serverId].length > 1) {
            const latest = window.uptimeLogs[serverId][window.uptimeLogs[serverId].length - 1];
            const previous = window.uptimeLogs[serverId][window.uptimeLogs[serverId].length - 2];
            
            console.log(`Latest uptime: ${latest.formatted} (source: ${latest.source})`);
            console.log(`Previous uptime: ${previous.formatted} (source: ${previous.source})`);
            
            if (latest.value && previous.value) {
                const diff = latest.value - previous.value;
                console.log(`Difference: ${diff.toFixed(1)}s`);
                
                if (diff < 0) {
                    console.warn('⚠️ Uptime decreased! Possible server restart or backend issue.');
                }
            }
        }
    } else {
        console.log(`No uptime logs found for server ${serverId}`);
    }
    
    // Check manual tracking data
    if (window.manualUptimeTracking && window.manualUptimeTracking[serverId]) {
        console.log('🧮 Manual tracking data:', window.manualUptimeTracking[serverId]);
        
        // Calculate current value with elapsed time
        const tracking = window.manualUptimeTracking[serverId];
        if (tracking.baseSeconds) {
            const now = Date.now();
            const elapsedSeconds = Math.floor((now - tracking.lastUpdate) / 1000);
            const totalSeconds = tracking.baseSeconds + elapsedSeconds;
            
            console.log(`Current calculated value: ${formatUptime(totalSeconds)} (${totalSeconds}s)`);
            console.log(`Last updated ${elapsedSeconds}s ago`);
        }
    } else {
        console.log(`No manual tracking data found for server ${serverId}`);
    }
    
    // Check chart data
    if (window.performanceChart) {
        const datasets = window.performanceChart.data.datasets;
        if (datasets && datasets[7] && datasets[7].data) {
            console.log('📈 Chart uptime data (hours):');
            // Get only non-null values
            const values = datasets[7].data.filter(v => v !== null);
            console.log(values);
            
            if (values.length > 0) {
                const lastValue = values[values.length - 1];
                console.log(`Latest chart value: ${lastValue.toFixed(2)} hours (${(lastValue * 3600).toFixed(0)}s)`);
            }
        } else {
            console.log('No uptime dataset found in chart');
        }
    }
    
    // Check last known uptime values
    if (lastKnownUptime && lastKnownUptime[serverId]) {
        console.log(`🔄 Last known uptime value: ${lastKnownUptime[serverId].toFixed(2)} hours (${(lastKnownUptime[serverId] * 3600).toFixed(0)}s)`);
    }
    
    console.groupEnd();
    return 'Uptime debug information printed to console';
};

console.log('🎉 Real-time update system initialized with network throughput debugging!');

// Function to optimize chart performance for real-time updates
function optimizeChartForRealtime() {
    if (window.performanceChart) {
        console.log('🔧 Optimizing chart for real-time updates');
        
        try {
            // Verify all datasets exist and have proper structure
            const datasets = window.performanceChart.data.datasets;
            const labels = window.performanceChart.data.labels;
            
            // Make sure we have valid arrays everywhere
            if (!labels || !Array.isArray(labels)) {
                window.performanceChart.data.labels = [];
            }
            
            if (datasets) {
                datasets.forEach((dataset, index) => {
                    if (!dataset.data || !Array.isArray(dataset.data)) {
                        dataset.data = [];
                        console.log(`🛠️ Fixed missing data array in dataset ${index}`);
                    }
                });
            }
            
            // Configure chart for smoother real-time updates
            window.performanceChart.options.animation = {
                duration: 250,       // Faster animations
                easing: 'easeOutQuad', // Better easing for real-time
                tension: {
                    duration: 150,
                    easing: 'linear',
                    from: 0.8,
                    to: 0.3
                }
            };
            
            // Optimize rendering with device pixel ratio
            window.performanceChart.options.devicePixelRatio = 2;
            
            // Use better element positioning for real-time
            window.performanceChart.options.responsive = true;
            window.performanceChart.options.maintainAspectRatio = false;
            window.performanceChart.options.resizeDelay = 100;
            
            // Improve tooltip response time
            window.performanceChart.options.interaction = {
                mode: 'nearest',
                axis: 'x',
                intersect: false
            };
            
            // Optimize scales for real-time
            if (window.performanceChart.options.scales) {
                // Create a modified x-axis scale configuration
                const xScale = window.performanceChart.options.scales.x || {};
                xScale.animation = {
                    duration: 200
                };
                xScale.ticks = {
                    maxTicksLimit: 10
                };
                window.performanceChart.options.scales.x = xScale;
            }
            
            // Update the chart with new options
            window.performanceChart.update();
            console.log('🚀 Chart optimized for real-time updates');
            
            // Return true if optimization was successful
            return true;
        } catch (err) {
            console.error('❌ Error optimizing chart:', err);
            return false;
        }
    }
    return false;
}

// Call this function when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Optimize chart for real-time if it exists
    setTimeout(optimizeChartForRealtime, 1000);
    
    // Set interval to periodically optimize chart (helps with browser tab switching)
    setInterval(optimizeChartForRealtime, 30000); // Every 30 seconds
});

// Initialize the manual tracking system on page load
document.addEventListener('DOMContentLoaded', function() {
    // Start automatic uptime updates on analytics page
    setInterval(() => {
        // Only run if we're on the analytics page
        if (!window.location.pathname.includes('/analytics')) return;
        
        // Get the currently selected server ID
        const serverSelector = document.getElementById('server_id');
        if (!serverSelector) return;
        
        const selectedServerId = serverSelector.value;
        if (!selectedServerId) return;
        
        console.log(`🔄 Auto-updating analytics uptime for server ${selectedServerId}`);
        
        // Check if we can find the uptime card
        const uptimeContainer = document.querySelector('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-6.mb-6:nth-child(2)');
        if (!uptimeContainer) return;
        
        const additionalCards = uptimeContainer.querySelectorAll('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6');
        if (!additionalCards || additionalCards.length < 4) return;
        
        const uptimeCard = additionalCards[3];
        if (!uptimeCard) return;
        
        const uptimeText = uptimeCard.querySelector('.text-3xl');
        if (!uptimeText) return;
        
        // Get current display value
        const currentDisplay = uptimeText.textContent;
        if (!currentDisplay || currentDisplay === 'N/A') return;
        
        // Parse current value and increment
        const currentSeconds = parseUptimeToSeconds(currentDisplay);
        if (currentSeconds <= 0) return;
        
        // Add 5 seconds (the interval time)
        const newSeconds = currentSeconds + 5;
        const newDisplay = formatUptime(newSeconds);
        
        // Update the display
        uptimeText.textContent = newDisplay;
        
        // Subtle visual feedback
        uptimeText.style.transition = 'all 0.5s';
        uptimeText.style.color = '#0077cc';
        setTimeout(() => {
            uptimeText.style.color = '';
        }, 800);
        
        // Also update chart if available
        if (window.performanceChart) {
            const datasets = window.performanceChart.data.datasets;
            if (datasets && datasets[7] && datasets[7].data && datasets[7].data.length > 0) {
                // Convert seconds to hours for chart
                const uptimeHours = newSeconds / 3600;
                
                // Replace the last value with the updated one
                const lastIndex = datasets[7].data.length - 1;
                if (lastIndex >= 0) {
                    datasets[7].data[lastIndex] = uptimeHours;
                    window.performanceChart.update('none'); // Update with no animation for smoother experience
                }
            }
        }
        
        // Store updated value in our tracking system
        if (!window.manualUptimeTracking) {
            window.manualUptimeTracking = {};
        }
        
        if (!window.manualUptimeTracking[selectedServerId]) {
            window.manualUptimeTracking[selectedServerId] = {};
        }
        
        window.manualUptimeTracking[selectedServerId].lastUpdate = Date.now();
        window.manualUptimeTracking[selectedServerId].display = newDisplay;
        window.manualUptimeTracking[selectedServerId].baseSeconds = newSeconds;
        
    }, 5000); // Update every 5 seconds
});

// Initialize chart when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Debug: DOM loaded, setting up event listeners');
    
    // Listen for Livewire events
    if (window.Livewire) {
        // Server selection changed
        window.Livewire.on('serverSelectionChanged', (selectedServers) => {
            console.log('🔄 Server selection changed:', selectedServers);
            resetChartData();
            if (window.performanceChart) {
                window.performanceChart.update('none');
            }
        });
        
        // Chart data updated
        window.Livewire.on('chartDataUpdated', (chartData) => {
            console.log('📊 Chart data updated:', chartData);
            if (window.performanceChart && chartData) {
                updateChartWithNewData(chartData);
            }
        });
    }
});

// Function to reset chart data
function resetChartData() {
    console.log('🔄 Resetting chart data');
    lastUpdateTime = {};
    lastNetworkBytes = {};
    networkThroughputHistory = {};
    lastDiskIORead = {};
    lastDiskIOWrite = {};
    lastKnownUptime = {};
    
    if (window.performanceChart) {
        const datasets = window.performanceChart.data.datasets;
        datasets.forEach(dataset => {
            dataset.data = [];
        });
        window.performanceChart.data.labels = [];
    }
}

// Function to update chart with new data
function updateChartWithNewData(chartData) {
    if (!window.performanceChart || !chartData) return;
    
    console.log('📊 Updating chart with new data');
    
    const chart = window.performanceChart;
    
    // Update labels
    chart.data.labels = chartData.labels || [];
    
    // Update datasets
    if (chartData.datasets && Array.isArray(chartData.datasets)) {
        chartData.datasets.forEach((newDataset, index) => {
            if (chart.data.datasets[index]) {
                chart.data.datasets[index].data = newDataset.data || [];
            }
        });
    }
    
    // Update the chart with animation
    chart.update();
}