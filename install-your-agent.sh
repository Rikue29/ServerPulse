#!/bin/bash
# ServerPulse Agent Installation Script for Ubuntu
# Supports modern Ubuntu with externally managed Python environments

set -e  # Exit on any error

echo "🚀 ServerPulse Agent Installation Script"
echo "========================================"

# Detect OS
if [[ -f /etc/os-release ]]; then
    . /etc/os-release
    OS=$NAME
    VER=$VERSION_ID
else
    echo "❌ Cannot detect OS. This script is for Ubuntu/Debian systems."
    exit 1
fi

echo "✅ Detected OS: $OS $VER"

# Check if running as root or with sudo
if [ "$EUID" -ne 0 ]; then
    echo "❌ Please run this script with sudo"
    echo "Usage: sudo bash install-your-agent.sh"
    exit 1
fi

# Update system packages
echo ""
echo "📦 Updating system packages..."
apt update -qq

# Install required system packages including python3-full to avoid externally managed environment error
echo "🔧 Installing required system packages..."
apt install -y python3 python3-pip python3-venv python3-full curl wget unzip git systemd

# Create application and config directories
echo "📁 Creating directories..."
mkdir -p /opt/serverpulse-agent
mkdir -p /etc/serverpulse-agent
mkdir -p /var/log/serverpulse-agent

# Download agent from GitHub
echo ""
echo "⬇️ Downloading ServerPulse agent from GitHub..."
cd /tmp
rm -rf serverpulse-agent-main* 2>/dev/null || true
wget -q https://github.com/shane-kennedy-se/serverpulse-agent/archive/main.zip
unzip -q main.zip
cd serverpulse-agent-main

# Create virtual environment to avoid external environment error
echo "🐍 Creating Python virtual environment..."
python3 -m venv /opt/serverpulse-agent/venv

# Install Python dependencies in virtual environment
echo "📋 Installing Python dependencies..."
/opt/serverpulse-agent/venv/bin/pip install -q --upgrade pip

# Install dependencies from requirements.txt
if [ -f "requirements.txt" ]; then
    /opt/serverpulse-agent/venv/bin/pip install -q -r requirements.txt
else
    # Install common dependencies if requirements.txt is missing
    /opt/serverpulse-agent/venv/bin/pip install -q requests psutil pyyaml
fi

# Copy agent files
echo "📄 Copying agent files..."
cp -r * /opt/serverpulse-agent/
chmod +x /opt/serverpulse-agent/*.py

# Create agent user
echo "👤 Creating serverpulse user..."
useradd -r -s /bin/false serverpulse 2>/dev/null || true

# Set permissions
echo "🔐 Setting permissions..."
chown -R serverpulse:serverpulse /opt/serverpulse-agent
chown -R serverpulse:serverpulse /etc/serverpulse-agent
chown -R serverpulse:serverpulse /var/log/serverpulse-agent

# Install systemd service
echo "⚙️ Installing systemd service..."
if [ -f "/opt/serverpulse-agent/serverpulse-agent.service" ]; then
    # Use existing service file but ensure it uses the virtual environment
    sed 's|ExecStart=.*|ExecStart=/opt/serverpulse-agent/venv/bin/python /opt/serverpulse-agent/serverpulse_agent.py /etc/serverpulse-agent/config.yml|' \
        /opt/serverpulse-agent/serverpulse-agent.service > /etc/systemd/system/serverpulse-agent.service
else
    # Create a basic service file
    tee /etc/systemd/system/serverpulse-agent.service > /dev/null <<EOF
[Unit]
Description=ServerPulse Monitoring Agent
After=network.target
Wants=network.target

[Service]
Type=simple
User=serverpulse
Group=serverpulse
WorkingDirectory=/opt/serverpulse-agent
Environment=PATH=/opt/serverpulse-agent/venv/bin
ExecStart=/opt/serverpulse-agent/venv/bin/python /opt/serverpulse-agent/serverpulse_agent.py /etc/serverpulse-agent/config.yml
Restart=always
RestartSec=10
StandardOutput=journal
StandardError=journal
SyslogIdentifier=serverpulse-agent

[Install]
WantedBy=multi-user.target
EOF
fi

# Get server IP
SERVER_IP=$(hostname -I | awk '{print $1}')

# Create configuration file
echo "📝 Creating configuration file..."
if [ -f "/opt/serverpulse-agent/config.yml.example" ]; then
    cp /opt/serverpulse-agent/config.yml.example /etc/serverpulse-agent/config.yml
    # Ensure the endpoint is set to serverpulse.test:8080
    sed -i 's|endpoint: ".*"|endpoint: "http://serverpulse.test:8080"|' /etc/serverpulse-agent/config.yml
else
    tee /etc/serverpulse-agent/config.yml > /dev/null <<EOF
server:
  endpoint: "http://serverpulse.test:8080"
  auth_token: "WILL_BE_GENERATED_AFTER_REGISTRATION"
  agent_id: "WILL_BE_GENERATED_AFTER_REGISTRATION"

collection:
  interval: 30  # Data collection interval in seconds
  metrics:
    - system_stats
    - disk_usage
    - network_stats
    - process_list

monitoring:
  services:
    - ssh
    - nginx
    - apache2
    - mysql
    - docker
    - postgresql

alerts:
  cpu_threshold: 80      # CPU usage percentage
  memory_threshold: 85   # Memory usage percentage
  disk_threshold: 90     # Disk usage percentage
  load_threshold: 5.0    # System load average

logging:
  level: INFO
  file: /var/log/serverpulse-agent/agent.log
EOF
fi

# Set config permissions
chown serverpulse:serverpulse /etc/serverpulse-agent/config.yml
chmod 600 /etc/serverpulse-agent/config.yml

# Reload systemd
echo "🔄 Reloading systemd..."
systemctl daemon-reload

# Clean up
cd /
rm -rf /tmp/serverpulse-agent-main*

echo ""
echo "✅ ServerPulse Agent installation completed successfully!"
echo "========================================================"
echo ""
echo "📋 Installation Summary:"
echo "   • Agent installed in: /opt/serverpulse-agent"
echo "   • Configuration file: /etc/serverpulse-agent/config.yml"
echo "   • Log file: /var/log/serverpulse-agent/agent.log"
echo "   • Endpoint configured: http://serverpulse.test:8080"
echo "   • Server IP detected: $SERVER_IP"
echo ""
echo "🚀 Next Steps:"
echo ""
echo "1. Add this server to ServerPulse web interface:"
echo "   → Open: http://serverpulse.test:8080/servers"
echo "   → Click 'Add Server'"
echo "   → Use IP address: $SERVER_IP"
echo "   → Fill in server name and details"
echo ""
echo "2. Start the monitoring agent:"
echo "   sudo systemctl enable serverpulse-agent"
echo "   sudo systemctl start serverpulse-agent"
echo ""
echo "3. Monitor the agent:"
echo "   • Check status: sudo systemctl status serverpulse-agent"
echo "   • View logs: sudo journalctl -u serverpulse-agent -f"
echo "   • View agent log: sudo tail -f /var/log/serverpulse-agent/agent.log"
echo ""
echo "� The agent will:"
echo "   • Send metrics every 30 seconds"
echo "   • Send heartbeat every 60 seconds"
echo "   • Automatically register when the server is added to ServerPulse"
echo "   • Monitor CPU, memory, disk, network, and services"
echo ""
echo "🔍 Troubleshooting:"
echo "   • Ensure ServerPulse is running on http://serverpulse.test:8080"
echo "   • Add the server in ServerPulse BEFORE starting the agent"
echo "   • Check network connectivity between this server and ServerPulse"
echo ""
echo "🎉 Installation complete! Your server will appear in the ServerPulse dashboard."
