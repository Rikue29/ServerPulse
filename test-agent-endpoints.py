#!/usr/bin/env python3
"""
Test script to verify ServerPulse agent endpoints are working
This script tests the agent API endpoints locally.
"""

import requests
import json
import time
import sys
from datetime import datetime

# ServerPulse endpoint
BASE_URL = "http://serverpulse.test:8080"

def test_endpoint(endpoint, method='GET', data=None, description=""):
    """Test an API endpoint"""
    url = f"{BASE_URL}{endpoint}"
    
    try:
        print(f"\n🔍 Testing {description}")
        print(f"   {method} {url}")
        
        if method == 'GET':
            response = requests.get(url, timeout=10)
        elif method == 'POST':
            headers = {'Content-Type': 'application/json'}
            response = requests.post(url, json=data, headers=headers, timeout=10)
        
        print(f"   Status: {response.status_code}")
        
        if response.status_code == 200:
            print(f"   ✅ Success")
            if response.text:
                try:
                    result = response.json()
                    print(f"   Response: {json.dumps(result, indent=2)[:200]}...")
                except:
                    print(f"   Response: {response.text[:200]}...")
        else:
            print(f"   ❌ Failed: {response.text[:200]}")
            
        return response.status_code == 200
        
    except requests.exceptions.RequestException as e:
        print(f"   ❌ Connection error: {e}")
        return False

def main():
    print("🚀 ServerPulse Agent Endpoint Test")
    print("=" * 50)
    
    # Check if ServerPulse is accessible
    if not test_endpoint("/", description="ServerPulse homepage"):
        print("\n❌ Cannot reach ServerPulse at http://serverpulse.test:8080")
        print("   Make sure ServerPulse is running and accessible.")
        sys.exit(1)    # Test agent registration endpoint
    sample_server_data = {
        "server_ip": "192.168.1.100",
        "hostname": "test-server.local",
        "agent_version": "1.0.0",
        "system_info": {
            "os": "Ubuntu 22.04",
            "architecture": "x86_64",
            "kernel": "5.15.0",
            "cpu_count": 4,
            "memory_total": 8000000000
        }
    }
    
    test_endpoint(
        "/api/v1/agents/register",
        method='POST',
        data=sample_server_data,
        description="Agent registration endpoint"
    )
    
    # Test metrics endpoint (need agent_id from registration)
    sample_metrics = {
        "server_ip": "192.168.1.100",
        "metrics": {
            "cpu_usage": 45.2,
            "memory_usage": 67.8,
            "disk_usage": 23.1,
            "load_average": 1.25,
            "network_rx": 1024,
            "network_tx": 2048
        },
        "timestamp": datetime.now().isoformat()
    }
    
    test_endpoint(
        "/api/v1/agents/test-agent-123/metrics",
        method='POST',
        data=sample_metrics,
        description="Metrics submission endpoint"
    )
    
    # Test heartbeat endpoint
    sample_heartbeat = {
        "server_ip": "192.168.1.100",
        "agent_id": "test-agent-123",
        "timestamp": datetime.now().isoformat()
    }
    
    test_endpoint(
        "/api/v1/agents/test-agent-123/heartbeat",
        method='POST',
        data=sample_heartbeat,
        description="Heartbeat endpoint"
    )
    
    # Test alert endpoint
    sample_alert = {
        "server_ip": "192.168.1.100",
        "alert_type": "cpu_high",
        "message": "CPU usage is 95%",
        "severity": "warning",
        "timestamp": datetime.now().isoformat()
    }
    
    test_endpoint(
        "/api/v1/agents/test-agent-123/alerts",
        method='POST',
        data=sample_alert,
        description="Alert endpoint"
    )
    
    print("\n" + "=" * 50)
    print("🎉 Endpoint testing complete!")
    print("\nNext steps:")
    print("1. Add a server manually in ServerPulse web interface")
    print("2. Install the agent on a Linux VM using:")
    print("   wget https://raw.githubusercontent.com/shane-kennedy-se/serverpulse-agent/main/install-your-agent.sh")
    print("   sudo bash install-your-agent.sh")

if __name__ == "__main__":
    main()
