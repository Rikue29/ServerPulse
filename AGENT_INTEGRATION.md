# ServerPulse Agent Integration

This document describes how the ServerPulse Linux agent integrates with the main ServerPulse Laravel application.

## Overview

The ServerPulse agent is a Python-based monitoring tool that runs on Linux servers and continuously sends metrics and status updates to the main ServerPulse application via REST API endpoints.

## Integration Features

- **Agent Registration**: Automatic registration of agents with servers in ServerPulse
- **Real-time Metrics**: Continuous monitoring of CPU, memory, disk, network, and system metrics
- **Service Monitoring**: Track status of system services (SSH, Nginx, MySQL, Docker, etc.)
- **Alert Integration**: Automatic alert generation based on configurable thresholds
- **Performance Logging**: Historical performance data storage for trends and analysis
- **Secure Communication**: Token-based authentication for all agent-server communication

## API Endpoints

The following API endpoints are available for agent communication:

### Agent Registration
```
POST /api/v1/agents/register
```
Registers a new agent with a server. The server must already exist in ServerPulse.

**Request Body:**
```json
{
    "server_ip": "192.168.1.100",
    "hostname": "production-server-01",
    "agent_version": "1.0.0",
    "system_info": {
        "os": "Ubuntu 20.04",
        "kernel": "5.4.0-74-generic",
        "arch": "x86_64"
    }
}
```

**Response:**
```json
{
    "success": true,
    "agent_id": "uuid-here",
    "auth_token": "secure-token-here",
    "server_id": 123,
    "config": {
        "collection_interval": 30,
        "heartbeat_interval": 60,
        "metrics_enabled": true
    }
}
```

### Send Metrics
```
POST /api/v1/agents/{agentId}/metrics
Authorization: Bearer {auth_token}
```

**Request Body:**
```json
{
    "timestamp": "2025-06-21T20:30:00Z",
    "metrics": {
        "cpu_usage": 45.2,
        "memory_usage": 67.8,
        "disk_usage": 23.4,
        "uptime": 123456,
        "load_average": 1.5,
        "network_rx": 1024000,
        "network_tx": 512000,
        "disk_io_read": 2048000,
        "disk_io_write": 1024000
    },
    "services": [
        {"name": "ssh", "status": "active"},
        {"name": "nginx", "status": "active"},
        {"name": "mysql", "status": "failed"}
    ]
}
```

### Send Heartbeat
```
POST /api/v1/agents/{agentId}/heartbeat
Authorization: Bearer {auth_token}
```

### Send Alerts
```
POST /api/v1/agents/{agentId}/alerts
Authorization: Bearer {auth_token}
```

**Request Body:**
```json
{
    "alerts": [
        {
            "type": "service_failure",
            "message": "MySQL service has stopped",
            "severity": "error",
            "timestamp": "2025-06-21T20:30:00Z"
        }
    ]
}
```

### Get Commands
```
GET /api/v1/agents/{agentId}/commands
Authorization: Bearer {auth_token}
```

## Database Schema Changes

The following fields were added to the `servers` table to support agent integration:

- `agent_enabled` (boolean): Whether agent monitoring is enabled for this server
- `agent_id` (string): Unique identifier for the agent
- `agent_token` (string): Hashed authentication token for the agent
- `agent_last_heartbeat` (timestamp): Last time the agent sent a heartbeat
- `agent_status` (enum): Current agent status (inactive, active, disconnected)
- `agent_version` (string): Version of the agent software
- `agent_config` (json): Agent configuration and system information
- `last_metrics` (json): Last received metrics from the agent

A new `performance_logs` table stores historical performance data:

- `server_id`: Foreign key to servers table
- `cpu_usage`: CPU usage percentage
- `ram_usage`: Memory usage percentage
- `disk_usage`: Disk usage percentage
- `network_rx`: Network bytes received
- `network_tx`: Network bytes transmitted
- `disk_io_read`: Disk bytes read
- `disk_io_write`: Disk bytes written
- `response_time`: Server response time in milliseconds

## Agent Installation Process

When a server is added to ServerPulse, the system generates installation instructions:

1. **Add Server**: Create a new server entry in ServerPulse
2. **Get Install Script**: The system provides a bash script for agent installation
3. **Deploy Agent**: Run the installation script on the target Linux server
4. **Agent Registration**: The agent automatically registers with ServerPulse on first run
5. **Start Monitoring**: The agent begins sending metrics every 30 seconds

## Example Agent Installation Script

```bash
#!/bin/bash
# ServerPulse Agent Installation Script

# Download and install the agent
wget https://github.com/shane-kennedy-se/serverpulse-agent/archive/main.zip
unzip main.zip
cd serverpulse-agent-main
sudo chmod +x install.sh
sudo ./install.sh

# Configure the agent
sudo tee /etc/serverpulse-agent/config.yml > /dev/null <<EOF
server:
  endpoint: "https://your-serverpulse-domain.com"
  auth_token: "GENERATED_AFTER_REGISTRATION"
  agent_id: "GENERATED_AFTER_REGISTRATION"

collection:
  interval: 30
  metrics:
    - system_stats
    - disk_usage
    - network_stats
    - process_list

monitoring:
  services:
    - ssh
    - nginx
    - mysql
    - docker

alerts:
  cpu_threshold: 80
  memory_threshold: 85
  disk_threshold: 90
  load_threshold: 5.0
EOF

# Start the agent service
sudo systemctl enable serverpulse-agent
sudo systemctl start serverpulse-agent

echo "Agent installed! Check status with: sudo systemctl status serverpulse-agent"
echo "View logs with: sudo journalctl -u serverpulse-agent -f"
```

## Security

- **Token Authentication**: All API requests require a Bearer token
- **Token Hashing**: Tokens are stored as SHA-256 hashes in the database
- **HTTPS Communication**: All agent-server communication should use HTTPS
- **Minimal Privileges**: The agent runs with minimal system privileges

## Alert Integration

The agent integration works with ServerPulse's existing alert threshold system:

1. **Threshold Configuration**: Set CPU, memory, disk, and load thresholds when creating a server
2. **Real-time Monitoring**: Agent metrics are checked against thresholds on each update
3. **Alert Generation**: Alerts are automatically created when thresholds are exceeded
4. **Notification Channels**: Alerts can be sent via web notifications, email, or other channels

## Monitoring Dashboard

All agent data is seamlessly integrated into the existing ServerPulse dashboard:

- **Server List**: Shows agent status alongside traditional monitoring data
- **Real-time Metrics**: CPU, memory, and disk usage updated every 30 seconds
- **Performance Trends**: Historical data for capacity planning and trend analysis
- **Service Status**: Monitor critical services and receive failure notifications
- **Log Integration**: Agent alerts and system events appear in the unified log view

## Troubleshooting

### Agent Not Registering
1. Verify the server exists in ServerPulse with the correct IP address
2. Check network connectivity between agent and ServerPulse server
3. Verify the ServerPulse URL is accessible from the agent server
4. Check agent logs: `sudo journalctl -u serverpulse-agent -f`

### Authentication Errors
1. Verify the agent token is correctly configured
2. Check that the agent ID matches the registered agent
3. Ensure the server hasn't been deleted from ServerPulse

### Missing Metrics
1. Verify the agent service is running: `sudo systemctl status serverpulse-agent`
2. Check agent configuration file: `/etc/serverpulse-agent/config.yml`
3. Review API endpoints in ServerPulse logs
4. Verify database permissions for metrics storage

## Testing

Use the included test script to verify agent API functionality:

```bash
php test_agent_api.php
```

This script tests:
- Agent registration
- Heartbeat functionality
- Metrics submission
- Authentication

## Future Enhancements

- **Remote Commands**: Execute commands on agents remotely
- **Custom Metrics**: Support for application-specific metrics
- **Agent Updates**: Automatic agent software updates
- **Configuration Management**: Remote agent configuration updates
- **Multi-Agent Support**: Multiple agents per server for redundancy
