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

// Global variables for real-time updates
let lastUpdateTime = {};
let lastNetworkBytes = {};
let performanceChart = null; // Global chart instance
let networkThroughputHistory = {}; // Store network throughput history for each server
let lastDiskIORead = {};
let lastDiskIOWrite = {};

// Function to update analytics page summary cards
function updateAnalyticsPage(eventData) {
    console.log('🔄 Updating analytics page for server_id:', eventData.server_id);
    console.log('📊 Event data received:', eventData);
    
    // Check if this update is for the currently selected server
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
    
    // Update summary cards
    updateSummaryCards(eventData, actualThroughput);
    
    // Update performance chart
    updatePerformanceChart(eventData, actualThroughput);
}

// Function to update servers page server rows
function updateServersPage(eventData) {
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

// Function to handle server selection change on analytics page
function handleServerSelectionChange() {
    const serverSelector = document.getElementById('server_id');
    if (!serverSelector) return;
    
    const selectedServerId = parseInt(serverSelector.value);
    console.log(`🔄 Server selection changed to: ${selectedServerId}`);
    
    // Clear previous data for the new server
    delete lastUpdateTime[selectedServerId];
    delete lastNetworkBytes[selectedServerId];
    delete networkThroughputHistory[selectedServerId];
    
    // Reset network throughput display
    const throughputCard = document.getElementById('network-throughput-card');
    if (throughputCard) {
        const throughputValue = throughputCard.querySelector('.text-2xl');
        if (throughputValue) {
            throughputValue.textContent = '0 KB/s';
        }
    }
    
    console.log(`🧹 Cleared previous data for server ${selectedServerId}`);
}

// Add event listener for server selection change on analytics page
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
    
    // Fetch fresh chart data from the database to ensure consistency
    const serverId = eventData.server_id;
    const url = `/analytics?server_id=${serverId}&ajax=1`;
    
    fetch(url)
        .then(response => response.json())
        .then(data => {
            if (data.chart_data) {
                // Update chart with fresh data from database
                window.performanceChart.data.labels = data.chart_data.labels;
                window.performanceChart.data.datasets[0].data = data.chart_data.cpu_load;
                window.performanceChart.data.datasets[1].data = data.chart_data.memory_usage;
                window.performanceChart.data.datasets[2].data = data.chart_data.network_activity;
                window.performanceChart.data.datasets[3].data = data.chart_data.disk_io;
                window.performanceChart.data.datasets[4].data = data.chart_data.disk_usage;
                window.performanceChart.data.datasets[6].data = data.chart_data.response_time;

                // Use the actualThroughput passed from updateAnalyticsPage (same as metrics card)
                console.log('📊 Using actualThroughput from metrics card:', actualThroughput);
                
                // Initialize history for this server if it doesn't exist
                if (!networkThroughputHistory[serverId]) {
                    networkThroughputHistory[serverId] = [];
                }
                
                // Add the new throughput value to history
                networkThroughputHistory[serverId].push(actualThroughput);
                
                console.log('📊 Network Throughput History:', {
                    serverId: serverId,
                    historyLength: networkThroughputHistory[serverId].length,
                    latestValue: actualThroughput,
                    last5Values: networkThroughputHistory[serverId].slice(-5)
                });
                
                // Keep only the last 200 values to match the chart length
                if (networkThroughputHistory[serverId].length > 200) {
                    networkThroughputHistory[serverId] = networkThroughputHistory[serverId].slice(-200);
                }
                
                // Fill the network throughput dataset with the history
                // If we don't have enough history, pad with zeros
                const historyLength = networkThroughputHistory[serverId].length;
                const chartLength = window.performanceChart.data.labels.length;
                
                let throughputData = [];
                if (historyLength >= chartLength) {
                    // Use the last chartLength values from history
                    throughputData = networkThroughputHistory[serverId].slice(-chartLength);
                } else {
                    // Pad with zeros at the beginning, then add history
                    const padding = new Array(chartLength - historyLength).fill(0);
                    throughputData = [...padding, ...networkThroughputHistory[serverId]];
                }
                
                console.log('📈 Chart Data Debug:', {
                    historyLength: historyLength,
                    chartLength: chartLength,
                    throughputDataLength: throughputData.length,
                    throughputDataLast5: throughputData.slice(-5)
                });
                
                window.performanceChart.data.datasets[5].data = throughputData;

                // Update the chart
                window.performanceChart.update('none');

                console.log('✅ Chart updated with real-time network throughput history. Latest:', actualThroughput.toFixed(2) + ' KB/s');
            }
        })
        .catch(error => {
            console.error('❌ Error fetching fresh chart data:', error);
        });
}

// Function to update summary cards
function updateSummaryCards(eventData, actualThroughput) {
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
        if (title && (title.textContent.includes('Storage Usage') || title.textContent.includes('Disk Usage'))) {
            const storageValue = card.querySelector('.text-3xl.font-bold.text-gray-900');
            if (storageValue) {
                const newStorageValue = parseFloat(eventData.disk_usage || 0).toFixed(1) + '%';
                storageValue.textContent = newStorageValue;
                console.log('✅ Updated Disk Usage to:', newStorageValue);
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
                    // Use system_uptime instead of current_uptime to match initial page load
                    const uptimeText = eventData.system_uptime || '0s';
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
    
    console.log('🎉 Summary cards updated successfully for server_id:', eventData.server_id);
}
