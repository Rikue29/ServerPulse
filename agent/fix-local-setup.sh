#!/bin/bash
# ServerPulse Agent Local IP Fix Script
# Run this on your Ubuntu VM

echo "🔧 Fixing ServerPulse Agent Configuration for Local Development"
echo "=============================================================="

IP="192.168.81.1"
CONFIG_FILE="/etc/serverpulse-agent/config.yml"

echo "1. Testing connectivity to ServerPulse server..."
echo "   Pinging $IP..."
if ping -c 2 $IP > /dev/null 2>&1; then
    echo "   ✅ Server is reachable"
else
    echo "   ❌ Cannot reach server. Check network connection."
    exit 1
fi

echo ""
echo "2. Testing ServerPulse application..."
HTTP_STATUS=$(curl -s -o /dev/null -w "%{http_code}" http://$IP/ 2>/dev/null || echo "000")
if [ "$HTTP_STATUS" = "200" ] || [ "$HTTP_STATUS" = "302" ]; then
    echo "   ✅ ServerPulse is running (HTTP $HTTP_STATUS)"
else
    echo "   ⚠️  ServerPulse may not be running (HTTP $HTTP_STATUS)"
    echo "   Make sure to start ServerPulse with: php artisan serve --host=0.0.0.0"
fi

echo ""
echo "3. Updating agent configuration..."
if [ -f "$CONFIG_FILE" ]; then
    echo "   Current endpoint:"
    grep "endpoint:" "$CONFIG_FILE" | head -1
    
    sudo sed -i "s|endpoint: \".*\"|endpoint: \"http://$IP\"|" "$CONFIG_FILE"
    
    echo "   New endpoint:"
    grep "endpoint:" "$CONFIG_FILE" | head -1
    echo "   ✅ Configuration updated"
else
    echo "   ❌ Config file not found: $CONFIG_FILE"
    exit 1
fi

echo ""
echo "4. Fixing permissions..."
sudo mkdir -p /var/log/serverpulse-agent
sudo chown serverpulse:serverpulse /var/log/serverpulse-agent
sudo chmod 755 /var/log/serverpulse-agent
echo "   ✅ Permissions fixed"

echo ""
echo "5. Restarting agent service..."
sudo systemctl restart serverpulse-agent
sleep 2

echo ""
echo "6. Checking service status..."
if sudo systemctl is-active --quiet serverpulse-agent; then
    echo "   ✅ Agent is running"
else
    echo "   ⚠️  Agent may have issues"
fi

echo ""
echo "7. Recent logs:"
sudo journalctl -u serverpulse-agent -n 5 --no-pager

echo ""
echo "🎯 Next Steps:"
echo "1. Make sure ServerPulse is running: php artisan serve --host=0.0.0.0"
echo "2. Add this server in ServerPulse web interface: http://$IP/servers"
echo "3. Monitor logs: sudo journalctl -u serverpulse-agent -f"
echo ""
echo "✨ Configuration complete!"
