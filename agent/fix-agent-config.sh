#!/bin/bash
# Quick fix script to update ServerPulse agent configuration
# This script updates the endpoint IP address

IP_ADDRESS="192.168.81.1"
CONFIG_FILE="/etc/serverpulse-agent/config.yml"

echo "🔧 Updating ServerPulse Agent Configuration"
echo "==========================================="
echo "Target IP: $IP_ADDRESS"
echo "Config file: $CONFIG_FILE"

# Check if config file exists
if [ ! -f "$CONFIG_FILE" ]; then
    echo "❌ Config file not found: $CONFIG_FILE"
    echo "Please make sure the agent is installed first."
    exit 1
fi

echo "📝 Current configuration:"
grep -n "endpoint:" "$CONFIG_FILE" || echo "No endpoint found in config"

echo ""
echo "🔄 Updating endpoint to use IP address..."

# Update the endpoint in the config file
sudo sed -i "s|endpoint: \".*\"|endpoint: \"http://$IP_ADDRESS\"|" "$CONFIG_FILE"

echo "✅ Configuration updated!"
echo ""
echo "📝 New configuration:"
grep -n "endpoint:" "$CONFIG_FILE"

echo ""
echo "🔄 Restarting serverpulse-agent service..."
sudo systemctl restart serverpulse-agent

echo ""
echo "⏳ Waiting 3 seconds for service to start..."
sleep 3

echo ""
echo "📊 Service status:"
sudo systemctl status serverpulse-agent --no-pager -l

echo ""
echo "🔍 Testing connectivity to ServerPulse..."
curl -s -o /dev/null -w "HTTP Status: %{http_code}\nResponse Time: %{time_total}s\n" "http://$IP_ADDRESS/" || echo "❌ Could not connect to ServerPulse server"

echo ""
echo "📋 Recent logs (last 10 lines):"
sudo journalctl -u serverpulse-agent -n 10 --no-pager

echo ""
echo "🎯 Next steps:"
echo "1. Monitor logs: sudo journalctl -u serverpulse-agent -f"
echo "2. Check agent status: sudo systemctl status serverpulse-agent"
echo "3. Add server in ServerPulse web interface: http://$IP_ADDRESS/servers"
echo ""
echo "✨ Configuration update complete!"
