#!/usr/bin/env python3
"""
Agent validation script - checks if all agent files are properly configured
and validates the agent functionality locally.
"""

import os
import sys
import yaml
import subprocess
import json
from pathlib import Path

def check_file_exists(filepath, description):
    """Check if a file exists"""
    if os.path.exists(filepath):
        print(f"✅ {description}: {filepath}")
        return True
    else:
        print(f"❌ {description} missing: {filepath}")
        return False

def check_python_dependencies():
    """Check if required Python packages can be imported"""
    required_packages = ['requests', 'psutil', 'yaml']
    
    print("\n🐍 Checking Python dependencies:")
    all_ok = True
    
    for package in required_packages:
        try:
            if package == 'yaml':
                import yaml
            else:
                __import__(package)
            print(f"✅ {package}")
        except ImportError:
            print(f"❌ {package} - run: pip install {package}")
            all_ok = False
    
    return all_ok

def validate_config():
    """Validate the configuration file"""
    config_file = "config.yml.example"
    
    print(f"\n📝 Validating configuration file: {config_file}")
    
    if not os.path.exists(config_file):
        print(f"❌ Config file not found: {config_file}")
        return False
    
    try:
        with open(config_file, 'r') as f:
            config = yaml.safe_load(f)
        
        # Check required sections
        required_sections = ['server', 'collection', 'monitoring', 'alerts', 'logging']
        for section in required_sections:
            if section in config:
                print(f"✅ Section '{section}' found")
            else:
                print(f"❌ Section '{section}' missing")
                return False
        
        # Check endpoint
        endpoint = config.get('server', {}).get('endpoint', '')
        if '192.168.81.1' in endpoint:
            print(f"✅ Endpoint correctly configured: {endpoint}")
        else:
            print(f"⚠️  Endpoint: {endpoint} (should contain 192.168.81.1)")
        
        return True
        
    except yaml.YAMLError as e:
        print(f"❌ Invalid YAML format: {e}")
        return False

def validate_agent_script():
    """Validate the agent Python script"""
    agent_file = "serverpulse_agent.py"
    
    print(f"\n🤖 Validating agent script: {agent_file}")
    
    if not os.path.exists(agent_file):
        print(f"❌ Agent script not found: {agent_file}")
        return False
    
    # Check if it's executable
    if os.access(agent_file, os.X_OK):
        print("✅ Agent script is executable")
    else:
        print("⚠️  Agent script is not executable (will be fixed during installation)")
    
    # Check for required imports and functions
    try:
        with open(agent_file, 'r') as f:
            content = f.read()
        required_elements = [
            'class ServerPulseAgent',
            'def register',
            'def send_metrics',
            'def send_heartbeat',
            'def send_alert',
            'api/v1/agents'  # Check for API endpoint structure instead
        ]
        
        for element in required_elements:
            if element in content:
                print(f"✅ Found: {element}")
            else:
                print(f"❌ Missing: {element}")
                return False
        
        return True
        
    except Exception as e:
        print(f"❌ Error reading agent script: {e}")
        return False

def validate_install_script():
    """Validate the installation script"""
    install_file = "install-your-agent.sh"
    
    print(f"\n📦 Validating installation script: {install_file}")
    
    if not os.path.exists(install_file):
        print(f"❌ Install script not found: {install_file}")
        return False
    try:
        with open(install_file, 'r', encoding='utf-8') as f:
            content = f.read()
        
        required_elements = [
            'python3-venv',
            'systemctl',
            '/opt/serverpulse-agent',
            'SCRIPT_DIR='
        ]
        
        for element in required_elements:
            if element in content:
                print(f"✅ Found: {element}")
            else:
                print(f"❌ Missing: {element}")
                return False
        
        return True
        
    except Exception as e:
        print(f"❌ Error reading install script: {e}")
        return False

def main():
    print("🔍 ServerPulse Agent Validation Script")
    print("=" * 50)
    
    # Check current directory
    print(f"📁 Current directory: {os.getcwd()}")
    
    # Check all required files
    files_ok = True
    required_files = [
        ("serverpulse_agent.py", "Agent Python script"),
        ("config.yml.example", "Configuration example"),
        ("requirements.txt", "Python requirements"),
        ("install-your-agent.sh", "Installation script"),
        ("serverpulse-agent.service", "Systemd service file"),
        ("AGENT_INSTALL_GUIDE.md", "Installation guide")
    ]
    
    print("\n📋 Checking required files:")
    for filepath, description in required_files:
        if not check_file_exists(filepath, description):
            files_ok = False
    
    # Check Python dependencies
    deps_ok = check_python_dependencies()
    
    # Validate configuration
    config_ok = validate_config()
    
    # Validate agent script
    agent_ok = validate_agent_script()
    
    # Validate install script
    install_ok = validate_install_script()
    
    # Summary
    print("\n" + "=" * 50)
    print("📊 Validation Summary:")
    print(f"   Files: {'✅' if files_ok else '❌'}")
    print(f"   Dependencies: {'✅' if deps_ok else '❌'}")
    print(f"   Configuration: {'✅' if config_ok else '❌'}")
    print(f"   Agent Script: {'✅' if agent_ok else '❌'}")
    print(f"   Install Script: {'✅' if install_ok else '❌'}")
    
    if all([files_ok, deps_ok, config_ok, agent_ok, install_ok]):
        print("\n🎉 All validations passed! Agent is ready for deployment.")
        print("\n🚀 Next steps:")
        print("1. Push these files to GitHub")
        print("2. Test installation on a Linux VM")
        print("3. Add servers in ServerPulse web interface")
        return 0
    else:
        print("\n❌ Some validations failed. Please fix the issues above.")
        return 1

if __name__ == "__main__":
    sys.exit(main())
