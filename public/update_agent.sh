#!/bin/bash
# Script to copy updated ServerPulse agent to Linux VM
# Run this on your Linux VM

echo "🔄 Updating ServerPulse Agent..."

# Backup current agent
sudo cp /opt/serverpulse-agent/serverpulse_agent.py /opt/serverpulse-agent/serverpulse_agent.py.backup.$(date +%Y%m%d_%H%M%S)

# Download updated agent from Windows machine
curl -o /tmp/serverpulse_agent.py http://192.168.0.101:8080/serverpulse_agent.py
if [ $? -eq 0 ]; then
    echo "✅ Downloaded updated agent"
    sudo cp /tmp/serverpulse_agent.py /opt/serverpulse-agent/serverpulse_agent.py
    sudo chown root:root /opt/serverpulse-agent/serverpulse_agent.py
    sudo chmod 755 /opt/serverpulse-agent/serverpulse_agent.py
    echo "✅ Agent updated successfully"
else
    echo "❌ Failed to download updated agent"
    exit 1
fi

# Restart the service
echo "🔄 Restarting agent service..."
sudo systemctl restart serverpulse-agent
echo "✅ Agent restarted"

echo "📋 Checking agent status..."
sudo systemctl status serverpulse-agent --no-pager
