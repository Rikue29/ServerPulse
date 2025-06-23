# ServerPulse Agent Installation Guide

## Quick Start

The ServerPulse agent allows you to monitor Linux servers remotely. It sends metrics every 30 seconds and heartbeats every 60 seconds to your ServerPulse dashboard.

### 🚀 Installation

1. **Install on Ubuntu/Debian server:**
   ```bash
   wget https://raw.githubusercontent.com/shane-kennedy-se/serverpulse-agent/main/install-your-agent.sh
   sudo bash install-your-agent.sh
   ```

2. **Add server in ServerPulse web interface:**
   - Go to http://serverpulse.test:8080/servers
   - Click "Add Server"
   - Use the IP address shown in the install script output
   - Fill in server name and details

3. **Start the agent:**
   ```bash
   sudo systemctl enable serverpulse-agent
   sudo systemctl start serverpulse-agent
   ```

### 📊 What the Agent Monitors

- **System Metrics:**
  - CPU usage and load average
  - Memory usage
  - Disk usage for all mounted filesystems
  - Network traffic (bytes sent/received)

- **Services:**
  - SSH, Nginx, Apache, MySQL, Docker, PostgreSQL
  - Custom services can be configured

- **Alerts:**
  - CPU usage > 80%
  - Memory usage > 85%
  - Disk usage > 90%
  - System load > 5.0

### 🔧 Configuration

The agent configuration is stored in `/etc/serverpulse-agent/config.yml`:

```yaml
server:
  endpoint: "http://serverpulse.test:8080"
  auth_token: "WILL_BE_GENERATED_AFTER_REGISTRATION"
  agent_id: "WILL_BE_GENERATED_AFTER_REGISTRATION"

collection:
  interval: 30  # Data collection interval in seconds

alerts:
  cpu_threshold: 80      # CPU usage percentage
  memory_threshold: 85   # Memory usage percentage
  disk_threshold: 90     # Disk usage percentage
  load_threshold: 5.0    # System load average
```

### 📋 Management Commands

```bash
# Check agent status
sudo systemctl status serverpulse-agent

# View real-time logs
sudo journalctl -u serverpulse-agent -f

# View agent log file
sudo tail -f /var/log/serverpulse-agent/agent.log

# Restart agent
sudo systemctl restart serverpulse-agent

# Stop agent
sudo systemctl stop serverpulse-agent
```

### 🔍 Troubleshooting

**Agent not connecting:**
1. Ensure ServerPulse is running on http://serverpulse.test:8080
2. Check if the server was added in the ServerPulse web interface
3. Verify network connectivity: `curl http://serverpulse.test:8080`

**No metrics showing:**
1. Check agent logs: `sudo journalctl -u serverpulse-agent -f`
2. Verify the server IP matches what was added in ServerPulse
3. Restart the agent: `sudo systemctl restart serverpulse-agent`

**Permission errors:**
1. Ensure the serverpulse user has proper permissions
2. Check log file permissions: `ls -la /var/log/serverpulse-agent/`

### 🎯 Requirements

- **Operating System:** Ubuntu 18.04+ or Debian 10+
- **Python:** 3.6+ (installed automatically)
- **Network:** HTTP access to ServerPulse server
- **Privileges:** Root/sudo access for installation

### 📁 File Locations

- **Agent:** `/opt/serverpulse-agent/`
- **Configuration:** `/etc/serverpulse-agent/config.yml`
- **Logs:** `/var/log/serverpulse-agent/agent.log`
- **Service:** `/etc/systemd/system/serverpulse-agent.service`

### 🔒 Security

- Agent runs as non-privileged `serverpulse` user
- Configuration file has restricted permissions (600)
- Uses HTTP(S) for secure communication
- Auto-generates authentication tokens

### 📱 Real-time Dashboard

Once installed, you'll see real-time updates in your ServerPulse dashboard:
- Server status (online/offline)
- Live metrics graphs
- Service status indicators
- Alert notifications
- Historical performance data

The dashboard auto-refreshes every 30 seconds to show the latest data from your monitored servers.
