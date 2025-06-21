# ServerPulse Agent

A comprehensive Linux monitoring agent that integrates with your ServerPulse Laravel application.

## Features

- ✅ **Real-time System Monitoring**: CPU, memory, disk, network metrics
- ✅ **Service Management**: Monitor systemd services for failures
- ✅ **Automatic Registration**: Self-registers with ServerPulse server
- ✅ **Heartbeat Monitoring**: 60-second heartbeat intervals
- ✅ **Metrics Collection**: 30-second metric collection intervals
- ✅ **Alert Integration**: Automatic threshold-based alerting
- ✅ **Secure Communication**: Token-based authentication

## Quick Start

### 1. Add Server to ServerPulse

Before installing the agent, add your server to ServerPulse:

1. Go to `http://serverpulse.test:8080/servers`
2. Click "Add Server"
3. Enter your server details (use the server's actual IP address)
4. Set appropriate alert thresholds

### 2. Install Agent on Ubuntu Server

Copy the installation script to your Ubuntu server and run:

```bash
# Download and run the installation script
wget https://raw.githubusercontent.com/shane-kennedy-se/serverpulse-agent/main/install-your-agent.sh
chmod +x install-your-agent.sh
sudo ./install-your-agent.sh
```

### 3. Start the Agent

```bash
# Enable and start the service
sudo systemctl enable serverpulse-agent
sudo systemctl start serverpulse-agent

# Check status
sudo systemctl status serverpulse-agent

# View logs
sudo journalctl -u serverpulse-agent -f
```

## Configuration

The agent is configured via `/etc/serverpulse-agent/config.yml`:

```yaml
server:
  endpoint: "http://serverpulse.test:8080"
  auth_token: "AUTO_GENERATED"
  agent_id: "AUTO_GENERATED"

collection:
  interval: 30  # seconds

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
```

## API Integration

The agent communicates with ServerPulse using these endpoints:

- `POST /api/v1/agents/register` - Initial registration
- `POST /api/v1/agents/{id}/metrics` - Send metrics (every 30s)
- `POST /api/v1/agents/{id}/heartbeat` - Send heartbeat (every 60s)
- `POST /api/v1/agents/{id}/alerts` - Send alerts

## Monitoring

### Collected Metrics

- **System**: CPU usage, memory usage, disk usage, system uptime
- **Network**: Bytes received/transmitted
- **Disk I/O**: Read/write operations
- **Load**: System load average
- **Services**: Status of monitored systemd services
- **Connectivity**: Response time to ServerPulse server

### Alert Thresholds

- CPU usage > 80% (configurable)
- Memory usage > 85% (configurable)  
- Disk usage > 90% (configurable)
- Load average > 5.0 (configurable)

## Troubleshooting

### Check Agent Status
```bash
sudo systemctl status serverpulse-agent
```

### View Logs
```bash
# Service logs
sudo journalctl -u serverpulse-agent -f

# Agent log file
sudo tail -f /var/log/serverpulse-agent/agent.log
```

### Test Agent Functionality
```bash
# Run test script
python3 test_agent.py
```

### Common Issues

1. **Registration Failed**: Ensure the server exists in ServerPulse with correct IP
2. **Connection Error**: Check network connectivity to ServerPulse
3. **Permission Denied**: Ensure proper file permissions
4. **Service Won't Start**: Check configuration file syntax

### Debug Mode
```bash
sudo systemctl stop serverpulse-agent
sudo -u serverpulse /opt/serverpulse-agent/venv/bin/python /opt/serverpulse-agent/serverpulse_agent.py /etc/serverpulse-agent/config.yml
```

## File Structure

```
/opt/serverpulse-agent/
├── serverpulse_agent.py       # Main agent script
├── requirements.txt           # Python dependencies
├── config.yml.example        # Example configuration
├── test_agent.py             # Test script
└── venv/                     # Python virtual environment

/etc/serverpulse-agent/
└── config.yml               # Agent configuration

/var/log/serverpulse-agent/
└── agent.log                # Agent logs

/etc/systemd/system/
└── serverpulse-agent.service # Systemd service
```

## Development

### Testing Locally
```bash
# Test agent functionality
python3 test_agent.py

# Run agent in debug mode
python3 serverpulse_agent.py config.yml.example
```

### Requirements
- Python 3.6+
- systemd
- Network access to ServerPulse server

## License

MIT License - see LICENSE file for details.
