import './bootstrap';

console.log('ServerPulse app.js loaded!');

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

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
    console.log('🎉 Event data (e.status):', e.status);
    
    const eventData = e.status;

    if (!eventData || typeof eventData !== 'object') {
        console.error('❌ Processed event data is missing or not an object!', eventData);
        return;
    }

    if (!eventData.server_id) {
        console.error('❌ server_id is missing in processed event data!', eventData);
        return;
    }

    console.log('✅ Processing event for server_id:', eventData.server_id);

    // Check if we're on the analytics page
    const isAnalyticsPage = window.location.pathname.includes('/analytics');
    console.log('🔧 Debug: Current page is analytics?', isAnalyticsPage);
    
    if (isAnalyticsPage) {
        console.log('🔄 Updating analytics page for server_id:', eventData.server_id);
        // Update analytics page summary cards
        updateAnalyticsPage(eventData);
    } else {
        console.log('🔄 Updating servers page for server_id:', eventData.server_id);
        // Update servers page server rows
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

// Function to update analytics page summary cards
function updateAnalyticsPage(eventData) {
    console.log('🔄 updateAnalyticsPage called with data:', eventData);
    
    // Check if this update is for the currently selected server
    const serverSelector = document.getElementById('server_id');
    if (!serverSelector) {
        console.log('⚠️ Server selector not found, skipping update');
        return;
    }
    
    const selectedServerId = parseInt(serverSelector.value);
    console.log('🔧 Debug: Selected server ID:', selectedServerId, 'Event server ID:', eventData.server_id);
    
    if (eventData.server_id !== selectedServerId) {
        console.log(`⏭️ Skipping update - broadcast for server ${eventData.server_id}, but selected server is ${selectedServerId}`);
        return;
    }
    
    console.log(`✅ Updating cards for selected server ${selectedServerId}`);
    
    const serverId = eventData.server_id;
    const currentTime = new Date().toLocaleTimeString('en-GB', { 
        hour12: false, 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    });
    const currentTotalBytes = (eventData.network_rx || 0) + (eventData.network_tx || 0);
    
    // Calculate actual network throughput if we have previous data
    let actualThroughput = 0;
    if (lastUpdateTime[serverId] && lastNetworkBytes[serverId]) {
        const timeDiff = (currentTime - lastUpdateTime[serverId]) / 1000;
        const bytesDiff = Math.max(0, currentTotalBytes - lastNetworkBytes[serverId]);
        actualThroughput = timeDiff > 0 ? (bytesDiff / timeDiff / 1024) : 0;
    }
    
    // Store current values for next calculation
    lastUpdateTime[serverId] = currentTime;
    lastNetworkBytes[serverId] = currentTotalBytes;
    
    console.log('🔄 Updating summary cards...');
    // Update summary cards
    updateSummaryCards(eventData, actualThroughput);
    
    console.log('🔄 Updating performance chart...');
    // Update performance chart
    updatePerformanceChart(eventData, actualThroughput);
    
    console.log('✅ Analytics page update completed');
}

// Function to update servers page server rows
function updateServersPage(eventData) {
    console.log('🔄 updateServersPage called with data:', eventData);
    
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
            
            // Update Uptime/Downtime in status column (servers page)
            const uptimeInfoDiv = statusCell.querySelector('.server-uptime-info');
            if (uptimeInfoDiv) {
                if (eventData.status === 'online' && eventData.current_uptime !== null) {
                    // Server is online - show current uptime
                    const uptimeSeconds = eventData.current_uptime;
                    const formattedUptime = formatUptime(uptimeSeconds);
                    uptimeInfoDiv.textContent = 'Uptime: ' + formattedUptime;
                    console.log('✅ Updated Status Uptime to:', formattedUptime, '(from current_uptime:', uptimeSeconds, 'seconds)');
                } else if (eventData.status === 'offline' && eventData.current_downtime !== null) {
                    // Server is offline - show current downtime
                    const downtimeSeconds = eventData.current_downtime;
                    const formattedDowntime = formatUptime(downtimeSeconds);
                    uptimeInfoDiv.textContent = 'Downtime: ' + formattedDowntime;
                    console.log('✅ Updated Status Downtime to:', formattedDowntime, '(from current_downtime:', downtimeSeconds, 'seconds)');
                } else {
                    // Fallback to system_uptime if available
                    uptimeInfoDiv.textContent = eventData.status === 'online' ? 'Uptime: ' + (eventData.system_uptime || 'N/A') : 'Downtime: ' + (eventData.system_uptime || 'N/A');
                    console.log('✅ Updated Status Uptime/Downtime to:', eventData.system_uptime || 'N/A', '(fallback)');
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
        
        // Update Uptime
        const uptimeCell = row.querySelector('[data-col="uptime"]');
        if (uptimeCell) {
            const uptimeText = uptimeCell.querySelector('span');
            if (uptimeText) {
                if (eventData.status === 'online' && eventData.current_uptime !== null) {
                    // Server is online - show current uptime
                    const uptimeSeconds = eventData.current_uptime;
                    const formattedUptime = formatUptime(uptimeSeconds);
                    uptimeText.textContent = formattedUptime;
                    console.log('✅ Updated Uptime to:', formattedUptime, '(from current_uptime:', uptimeSeconds, 'seconds)');
                } else if (eventData.status === 'offline' && eventData.current_downtime !== null) {
                    // Server is offline - show current downtime
                    const downtimeSeconds = eventData.current_downtime;
                    const formattedDowntime = formatUptime(downtimeSeconds);
                    uptimeText.textContent = formattedDowntime;
                    console.log('✅ Updated Downtime to:', formattedDowntime, '(from current_downtime:', downtimeSeconds, 'seconds)');
                } else {
                    // Fallback to system_uptime if available
                    uptimeText.textContent = eventData.system_uptime || 'N/A';
                    console.log('✅ Updated Uptime/Downtime to:', eventData.system_uptime || 'N/A', '(fallback)');
                }
            }
        }
    } else {
        console.warn('❌ Could not find row for server_id:', eventData.server_id);
    }
}

// Function to calculate network activity level
function calculateNetworkActivity(eventData) {
    const rx = eventData.network_rx || 0;
    const tx = eventData.network_tx || 0;
    const total = rx + tx;
    
    if (total > 1000000) {
        return 100; // high
    } else if (total > 100000) {
        return 50; // medium
    } else {
        return 10; // low
    }
}

// Function to get network activity text
function getNetworkActivityText(eventData) {
    const rx = eventData.network_rx || 0;
    const tx = eventData.network_tx || 0;
    const total = rx + tx;
    
    if (total > 1000000) {
        return 'high';
    } else if (total > 100000) {
        return 'medium';
    } else {
        return 'low';
    }
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
        
        // Reset chart data if available
        if (window.performanceChart) {
            const datasets = window.performanceChart.data.datasets;
            datasets.forEach(dataset => {
                dataset.data = [];
            });
            window.performanceChart.update('none');
            console.log('🔄 Chart data cleared for new server');
        }
    }
}

// Function to update performance chart
function updatePerformanceChart(eventData, actualThroughput) {
    if (!window.performanceChart) {
        console.log('⚠️ Chart not initialized yet');
        return;
    }
    
    const serverId = eventData.server_id;
    const currentTime = new Date().toLocaleTimeString('en-GB', { 
        hour12: false, 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit' 
    });
    
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
    
    // Calculate disk I/O throughput
    let diskIOThroughput = 0;
    const currentDiskIORead = eventData.disk_io_read || 0;
    const currentDiskIOWrite = eventData.disk_io_write || 0;
    
    if (lastDiskIORead[serverId] !== undefined && lastDiskIOWrite[serverId] !== undefined) {
        const timeDiff = (Date.now() - lastUpdateTime[serverId]) / 1000;
        const readDiff = Math.max(0, currentDiskIORead - lastDiskIORead[serverId]);
        const writeDiff = Math.max(0, currentDiskIOWrite - lastDiskIOWrite[serverId]);
        const totalDiff = readDiff + writeDiff;
        diskIOThroughput = timeDiff > 0 ? (totalDiff / timeDiff / 1024 / 1024) : 0; // MB/s
    }
    
    lastDiskIORead[serverId] = currentDiskIORead;
    lastDiskIOWrite[serverId] = currentDiskIOWrite;
    
    // Update chart data
    const chart = window.performanceChart;
    const labels = chart.data.labels;
    const datasets = chart.data.datasets;
    
    // Add new time label
    labels.push(currentTime);
    if (labels.length > 50) {
        labels.shift();
    }
    
    // Update datasets
    if (datasets[0]) datasets[0].data.push(eventData.cpu_usage || 0); // CPU
    if (datasets[1]) datasets[1].data.push(eventData.ram_usage || 0); // Memory
    if (datasets[2]) datasets[2].data.push(calculateNetworkActivity(eventData)); // Network Activity
    if (datasets[3]) datasets[3].data.push(diskIOThroughput); // Disk I/O
    if (datasets[4]) datasets[4].data.push(eventData.disk_usage || 0); // Disk Usage
    if (datasets[5]) datasets[5].data.push(actualThroughput); // Network Throughput
    if (datasets[6]) datasets[6].data.push(eventData.response_time || 0); // Response Time
    
    // Remove old data points
    datasets.forEach(dataset => {
        if (dataset.data.length > 50) {
            dataset.data.shift();
        }
    });
    
    // Update chart
    chart.update('none');
    console.log('✅ Chart updated with new data');
}

// Function to update summary cards
function updateSummaryCards(eventData, actualThroughput) {
    console.log('🔄 Updating summary cards with data:', eventData);
    
    // Update CPU Usage card
    const cpuCard = document.querySelector('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6:nth-child(1) .text-3xl');
    if (cpuCard) {
        cpuCard.textContent = parseFloat(eventData.cpu_usage || 0).toFixed(1) + '%';
        console.log('✅ Updated CPU card to:', eventData.cpu_usage);
    }
    
    // Update Network Activity card
    const networkActivityCard = document.querySelector('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6:nth-child(2) .text-3xl');
    if (networkActivityCard) {
        // Calculate network activity as a numeric value (0-100 scale or throughput)
        // Use the same logic as the graph for consistency
        const activityValue = calculateNetworkActivity(eventData);
        // Optionally, format as percentage or throughput (Kbps/Mbps)
        networkActivityCard.textContent = activityValue + '%';
        
        // Update progress bar
        const progressBar = networkActivityCard.closest('.bg-white').querySelector('.bg-green-500');
        if (progressBar) {
            progressBar.style.width = activityValue + '%';
        }
        console.log('✅ Updated Network Activity card to:', activityValue);
    }
    
    // Update Storage Usage card
    const storageCard = document.querySelector('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6:nth-child(3) .text-3xl');
    if (storageCard) {
        storageCard.textContent = parseFloat(eventData.disk_usage || 0).toFixed(1) + '%';
        console.log('✅ Updated Storage card to:', eventData.disk_usage);
    }
    
    // Update Memory Usage card
    const memoryCard = document.querySelector('.bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6:nth-child(4) .text-3xl');
    if (memoryCard) {
        memoryCard.textContent = parseFloat(eventData.ram_usage || 0).toFixed(1) + '%';
        console.log('✅ Updated Memory card to:', eventData.ram_usage);
    }
    
    // Update additional metrics cards
    const additionalCards = document.querySelectorAll('.grid.grid-cols-1.md\\:grid-cols-2.lg\\:grid-cols-4.gap-6.mb-6:nth-child(2) .bg-white.rounded-lg.shadow-sm.border.border-gray-200.p-6');
    
    // Disk Usage card
    if (additionalCards[0]) {
        const diskUsageText = additionalCards[0].querySelector('.text-3xl');
        if (diskUsageText) {
            diskUsageText.textContent = parseFloat(eventData.disk_usage || 0).toFixed(1) + '%';
            console.log('✅ Updated additional Disk Usage card to:', eventData.disk_usage);
        }
    }
    
    // Network Throughput card
    if (additionalCards[1]) {
        const throughputText = additionalCards[1].querySelector('.text-3xl');
        if (throughputText) {
            throughputText.textContent = parseFloat(actualThroughput).toFixed(1);
            console.log('✅ Updated Network Throughput card to:', actualThroughput);
        }
    }
    
    // Response Time card
    if (additionalCards[2]) {
        const responseTimeText = additionalCards[2].querySelector('.text-3xl');
        if (responseTimeText) {
            responseTimeText.textContent = parseFloat(eventData.response_time || 0).toFixed(1);
            console.log('✅ Updated Response Time card to:', eventData.response_time);
        }
    }
    
    // System Uptime card
    if (additionalCards[3]) {
        const uptimeText = additionalCards[3].querySelector('.text-3xl');
        if (uptimeText) {
            if (eventData.status === 'online' && eventData.current_uptime !== null) {
                // Server is online - show current uptime
                const uptimeSeconds = eventData.current_uptime;
                const formattedUptime = formatUptime(uptimeSeconds);
                uptimeText.textContent = formattedUptime;
                console.log('✅ Updated Analytics Uptime to:', formattedUptime, '(from current_uptime:', uptimeSeconds, 'seconds)');
            } else if (eventData.status === 'offline' && eventData.current_downtime !== null) {
                // Server is offline - show current downtime
                const downtimeSeconds = eventData.current_downtime;
                const formattedDowntime = formatUptime(downtimeSeconds);
                uptimeText.textContent = formattedDowntime;
                console.log('✅ Updated Analytics Downtime to:', formattedDowntime, '(from current_downtime:', downtimeSeconds, 'seconds)');
            } else {
                // Fallback to system_uptime if available
                uptimeText.textContent = eventData.system_uptime || 'N/A';
                console.log('✅ Updated Analytics Uptime/Downtime to:', eventData.system_uptime || 'N/A', '(fallback)');
            }
        }
    }
    
    console.log('✅ All summary cards updated successfully');
}

// Add event listener for server selection change
document.addEventListener('DOMContentLoaded', function() {
    console.log('🔧 Debug: DOM loaded, setting up event listeners');
    const serverSelector = document.getElementById('server_id');
    if (serverSelector) {
        serverSelector.addEventListener('change', handleServerSelectionChange);
        console.log('✅ Server selection change listener attached');
    } else {
        console.log('⚠️ Server selector not found on this page');
    }
});

console.log('🎉 Real-time update system initialized!');
