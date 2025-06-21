#!/usr/bin/env python3
"""
Test script to verify ServerPulse agent functionality
Run this to test the agent before deploying to production
"""

import sys
import os
import json
import time
import requests

# Add the current directory to Python path
sys.path.insert(0, os.path.dirname(os.path.abspath(__file__)))

# Import our agent
from serverpulse_agent import ServerPulseAgent

def test_agent():
    print("🧪 Testing ServerPulse Agent...")
    
    # Create a test config
    test_config = {
        'server': {
            'endpoint': 'http://serverpulse.test:8080',
            'auth_token': 'WILL_BE_GENERATED_AFTER_REGISTRATION',
            'agent_id': 'WILL_BE_GENERATED_AFTER_REGISTRATION'
        },
        'collection': {
            'interval': 30
        },
        'monitoring': {
            'services': ['ssh', 'systemd']
        },
        'alerts': {
            'cpu_threshold': 80,
            'memory_threshold': 85,
            'disk_threshold': 90,
            'load_threshold': 5.0
        },
        'logging': {
            'level': 'INFO',
            'file': '/tmp/test-agent.log'
        }
    }
      # Save test config
    import tempfile
    temp_dir = tempfile.gettempdir()
    config_path = os.path.join(temp_dir, 'test_config.yml')
    log_path = os.path.join(temp_dir, 'test-agent.log')
    
    test_config['logging']['file'] = log_path
    
    with open(config_path, 'w') as f:
        import yaml
        yaml.dump(test_config, f)
    
    try:        # Initialize agent
        agent = ServerPulseAgent(config_path)
        
        # Test system info collection
        print("📊 Testing system info collection...")
        system_info = agent.get_system_info()
        print(f"   ✓ System: {system_info.get('os')} {system_info.get('os_release')}")
        print(f"   ✓ CPU cores: {system_info.get('cpu_count')}")
        print(f"   ✓ Hostname: {system_info.get('hostname')}")
        
        # Test metrics collection
        print("\n📈 Testing metrics collection...")
        metrics = agent.collect_metrics()
        if metrics:
            print(f"   ✓ CPU: {metrics['cpu_usage']}%")
            print(f"   ✓ Memory: {metrics['memory_usage']}%")
            print(f"   ✓ Disk: {metrics['disk_usage']}%")
            print(f"   ✓ Load: {metrics['load_average']}")
            print(f"   ✓ Uptime: {metrics['uptime']} seconds")
        else:
            print("   ✗ Failed to collect metrics")
            return False
        
        # Test service collection
        print("\n🔧 Testing service collection...")
        services = agent.collect_services()
        for service in services:
            print(f"   ✓ {service['name']}: {service['status']}")
        
        # Test connectivity
        print("\n🌐 Testing connectivity to ServerPulse...")
        try:
            response = requests.get('http://serverpulse.test:8080', timeout=5)
            print(f"   ✓ ServerPulse reachable (HTTP {response.status_code})")
        except Exception as e:
            print(f"   ✗ Cannot reach ServerPulse: {e}")
            print("   ℹ️  Make sure ServerPulse is running on http://serverpulse.test:8080")
        
        print("\n✅ Agent test completed successfully!")
        print("\n📋 Next steps:")
        print("1. Ensure ServerPulse is running on http://serverpulse.test:8080")
        print("2. Add a server entry in ServerPulse with this machine's IP")
        print("3. Run the agent with: python3 serverpulse_agent.py config.yml.example")
        
        return True
          except Exception as e:
        print(f"❌ Test failed: {e}")
        return False
    
    finally:
        # Clean up
        try:
            if 'config_path' in locals():
                os.remove(config_path)
            if 'log_path' in locals():
                os.remove(log_path)
        except:
            pass

if __name__ == '__main__':
    test_agent()
