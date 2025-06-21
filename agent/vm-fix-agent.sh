#!/bin/bash
# Quick fix script for ServerPulse Agent on VM

echo "🔧 Fixing ServerPulse Agent Configuration and Files"
echo "================================================="

# 1. Update config endpoint
echo "1. Updating configuration..."
sudo sed -i 's|endpoint: ".*"|endpoint: "http://192.168.0.101:8080"|' /etc/serverpulse-agent/config.yml
sudo sed -i 's|/var/log/serverpulse-agent.log|/var/log/serverpulse-agent/agent.log|' /etc/serverpulse-agent/config.yml

echo "   Current endpoint:"
sudo grep "endpoint:" /etc/serverpulse-agent/config.yml

# 2. Fix permissions
echo ""
echo "2. Fixing permissions..."
sudo mkdir -p /var/log/serverpulse-agent
sudo chown serverpulse:serverpulse /var/log/serverpulse-agent
sudo chmod 755 /var/log/serverpulse-agent

# 3. Test connectivity
echo ""
echo "3. Testing connectivity to ServerPulse..."
curl -I http://192.168.0.101:8080 2>/dev/null && echo "✅ Connection successful" || echo "❌ Connection failed"

# 4. Restart agent
echo ""
echo "4. Restarting agent..."
sudo systemctl restart serverpulse-agent
sleep 3

# 5. Check status
echo ""
echo "5. Agent status:"
sudo systemctl status serverpulse-agent --no-pager -l

echo ""
echo "6. Recent logs:"
sudo journalctl -u serverpulse-agent -n 10 --no-pager

echo ""
echo "✅ Fix complete! Monitor with: sudo journalctl -u serverpulse-agent -f"
