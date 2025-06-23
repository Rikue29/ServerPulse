#!/usr/bin/env python3
"""
ServerPulse Monitoring Agent
A comprehensive monitoring agent that collects system metrics and sends them to ServerPulse.
"""

import json
import time
import sys
import os
import psutil
import requests
import yaml
import socket
import platform
import subprocess
import threading
from datetime import datetime, timezone
import logging
import signal
from pathlib import Path

class ServerPulseAgent:
    def __init__(self, config_file):
        self.config_file = config_file
        self.config = self.load_config(config_file)
        self.agent_id = None
        self.auth_token = None
        self.server_id = None
        self.running = True
        self.setup_logging()
        
        # Setup signal handlers
        signal.signal(signal.SIGTERM, self.signal_handler)
        signal.signal(signal.SIGINT, self.signal_handler)
        
        self.logger.info("ServerPulse Agent initialized")
    
    def setup_logging(self):
        log_level = getattr(logging, self.config.get('logging', {}).get('level', 'INFO'))
        log_file = self.config.get('logging', {}).get('file', '/var/log/serverpulse-agent/agent.log')
        
        handlers = [logging.StreamHandler()]  # Always have console output
        
        # Try to setup file logging, fall back to console only if it fails
        try:
            # Create log directory if it doesn't exist
            os.makedirs(os.path.dirname(log_file), exist_ok=True)
            handlers.append(logging.FileHandler(log_file))
            print(f"✅ Logging to file: {log_file}")
        except (PermissionError, OSError) as e:
            print(f"⚠️  Could not setup file logging: {e}")
            print(f"📝 Logging to console only")
        
        logging.basicConfig(
            level=log_level,
            format='%(asctime)s - %(name)s - %(levelname)s - %(message)s',
            handlers=handlers,
            force=True  # Override any existing logging config
        )
        self.logger = logging.getLogger('serverpulse-agent')
    
    def signal_handler(self, signum, frame):
        self.logger.info(f"Received signal {signum}, shutting down gracefully...")
        self.running = False
    
    def load_config(self, config_file):
        try:
            with open(config_file, 'r') as f:
                return yaml.safe_load(f)
        except Exception as e:
            print(f"Error loading config: {e}")
            sys.exit(1)
    
    def save_config(self):
        """Save updated config with agent credentials"""
        try:
            with open(self.config_file, 'w') as f:
                yaml.dump(self.config, f, default_flow_style=False)
        except Exception as e:
            self.logger.error(f"Failed to save config: {e}")
    
    def get_system_info(self):
        """Get detailed system information"""
        try:
            return {
                'hostname': socket.gethostname(),
                'platform': platform.platform(),
                'architecture': platform.architecture()[0],
                'processor': platform.processor() or 'Unknown',
                'python_version': platform.python_version(),
                'os': platform.system(),
                'os_release': platform.release(),
                'cpu_count': psutil.cpu_count(),
                'memory_total': psutil.virtual_memory().total
            }
        except Exception as e:
            self.logger.error(f"Error getting system info: {e}")
            return {'error': str(e)}
    
    def get_local_ip(self):
        """Get the local IP address"""
        try:
            # Try to connect to a remote server to get local IP
            s = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
            s.connect(("8.8.8.8", 80))
            local_ip = s.getsockname()[0]
            s.close()
            return local_ip
        except Exception:
            try:
                # Fallback: get IP from hostname
                return socket.gethostbyname(socket.gethostname())
            except Exception:
                return "127.0.0.1"
    def register_agent(self):
        """Register this agent with the ServerPulse server"""
        endpoint = f"{self.config['server']['endpoint'].rstrip('/')}/api/v1/agents/register"
        
        local_ip = self.get_local_ip()
        system_info = self.get_system_info()
        
        registration_data = {
            'server_ip': local_ip,
            'hostname': socket.gethostname(),
            'agent_version': '1.0.0',
            'system_info': system_info
        }
        
        self.logger.info(f"Registering agent for server IP: {local_ip}")
        self.logger.info(f"Registration endpoint: {endpoint}")
        
        max_retries = 4
        for attempt in range(1, max_retries + 1):
            try:
                self.logger.info(f"Registration attempt {attempt}/{max_retries}")
                response = requests.post(endpoint, json=registration_data, timeout=30)
                self.logger.info(f"Registration response: {response.status_code}")
                
                if response.status_code == 200:
                    data = response.json()
                    if data.get('success'):
                        self.agent_id = data['agent_id']
                        self.auth_token = data['auth_token']
                        self.server_id = data.get('server_id')
                        
                        # Update config
                        self.config['server']['agent_id'] = self.agent_id
                        self.config['server']['auth_token'] = self.auth_token
                        self.save_config()
                        
                        self.logger.info(f"🎉 Successfully registered with ServerPulse!")
                        self.logger.info(f"   Agent ID: {self.agent_id}")
                        self.logger.info(f"   Server ID: {self.server_id}")
                        self.logger.info(f"   Collection interval: {data.get('config', {}).get('collection_interval', 30)}s")
                        return True
                    else:
                        self.logger.error(f"Registration failed: {data.get('error', 'Unknown error')}")
                        return False
                else:
                    self.logger.error(f"Registration failed: {response.status_code} - {response.text}")
                    return False
                    
            except requests.exceptions.ConnectionError as e:
                self.logger.warning(f"Connection error (attempt {attempt}/{max_retries}): {e}")
                if attempt < max_retries:
                    wait_time = 2 ** attempt  # Exponential backoff
                    self.logger.info(f"Waiting {wait_time} seconds before retry...")
                    time.sleep(wait_time)
                else:
                    self.logger.error(f"All retry attempts failed for: {endpoint}")
                    return False
            except Exception as e:
                self.logger.error(f"Registration error: {e}")
                return False
        
        self.logger.error("Failed to register agent: No response")
        return False
    
    def collect_metrics(self):
        """Collect comprehensive system metrics"""
        try:
            # CPU metrics
            cpu_percent = psutil.cpu_percent(interval=1)
            
            # Memory metrics
            memory = psutil.virtual_memory()
            
            # Disk metrics (root partition)
            disk = psutil.disk_usage('/')
            
            # Network metrics
            network = psutil.net_io_counters()
            
            # Disk I/O metrics
            disk_io = psutil.disk_io_counters()
            
            # System uptime
            uptime = time.time() - psutil.boot_time()
            
            # Load average (Linux only)
            try:
                load_avg = os.getloadavg()[0]
            except (OSError, AttributeError):
                load_avg = 0.0
            
            metrics = {
                'cpu_usage': round(cpu_percent, 2),
                'memory_usage': round(memory.percent, 2),
                'disk_usage': round(disk.percent, 2),
                'uptime': int(uptime),
                'load_average': round(load_avg, 2),
                'network_rx': network.bytes_recv if network else 0,
                'network_tx': network.bytes_sent if network else 0,
                'disk_io_read': disk_io.read_bytes if disk_io else 0,
                'disk_io_write': disk_io.write_bytes if disk_io else 0,
                'response_time': self.measure_response_time()
            }
            
            self.logger.debug(f"Collected metrics: {metrics}")
            return metrics
            
        except Exception as e:
            self.logger.error(f"Error collecting metrics: {e}")
            return None
    
    def measure_response_time(self):
        """Measure response time to the ServerPulse server"""
        try:
            start_time = time.time()
            response = requests.get(f"{self.config['server']['endpoint']}/", timeout=5)
            end_time = time.time()
            return round((end_time - start_time) * 1000, 2)  # Convert to milliseconds
        except Exception:
            return 999.9  # High value indicates poor connectivity
    
    def collect_services(self):
        """Collect service status information"""
        services = self.config.get('monitoring', {}).get('services', [])
        service_status = []
        
        # Find systemctl command
        systemctl_path = None
        for path in ['/usr/bin/systemctl', '/bin/systemctl', '/sbin/systemctl']:
            if os.path.exists(path):
                systemctl_path = path
                break
        
        if not systemctl_path:
            self.logger.warning("systemctl not found - service monitoring disabled")
            return service_status
        
        for service in services:
            try:
                # Check if service exists first
                check_exists = subprocess.run([systemctl_path, 'list-unit-files', f'{service}.service'], 
                                            capture_output=True, text=True)
                
                if service in check_exists.stdout:
                    # Service exists, check its status
                    result = subprocess.run([systemctl_path, 'is-active', service], 
                                          capture_output=True, text=True)
                    status = 'active' if result.returncode == 0 else 'inactive'
                else:
                    status = 'not_found'
                
                service_status.append({'name': service, 'status': status})
                
            except Exception as e:
                self.logger.warning(f"Could not check service {service}: {e}")
                service_status.append({'name': service, 'status': 'unknown'})
        
        return service_status
    
    def send_metrics(self, metrics, services):
        """Send metrics to ServerPulse"""
        if not self.agent_id or not self.auth_token:
            self.logger.error("Agent not registered, cannot send metrics")
            return False
        
        endpoint = f"{self.config['server']['endpoint'].rstrip('/')}/api/v1/agents/{self.agent_id}/metrics"
        
        data = {
            'timestamp': datetime.now(timezone.utc).isoformat(),
            'metrics': metrics,
            'services': services
        }
        
        headers = {
            'Authorization': f'Bearer {self.auth_token}',
            'Content-Type': 'application/json'
        }
        
        try:
            response = requests.post(endpoint, json=data, headers=headers, timeout=30)
            if response.status_code == 200:
                self.logger.debug("Metrics sent successfully")
                return True
            elif response.status_code == 401:
                self.logger.warning("Authentication failed - agent credentials may be invalid. Need to re-register.")
                # Clear credentials to force re-registration on next startup
                self.agent_id = None
                self.auth_token = None
                self.config['server']['agent_id'] = 'WILL_BE_GENERATED_AFTER_REGISTRATION'
                self.config['server']['auth_token'] = 'WILL_BE_GENERATED_AFTER_REGISTRATION'
                self.save_config()
                return False
            else:
                self.logger.error(f"Failed to send metrics: {response.status_code} - {response.text}")
                return False
        except Exception as e:
            self.logger.error(f"Error sending metrics: {e}")
            return False
            self.logger.error(f"Error sending metrics: {e}")
            return False
    
    def send_heartbeat(self):
        """Send heartbeat to ServerPulse"""
        if not self.agent_id or not self.auth_token:
            self.logger.debug("No agent credentials for heartbeat")
            return False
        
        endpoint = f"{self.config['server']['endpoint'].rstrip('/')}/api/v1/agents/{self.agent_id}/heartbeat"
        
        headers = {
            'Authorization': f'Bearer {self.auth_token}',
            'Content-Type': 'application/json'
        }
        
        try:
            response = requests.post(endpoint, json={}, headers=headers, timeout=30)
            if response.status_code == 200:
                self.logger.debug("Heartbeat sent successfully")
                return True
            else:
                self.logger.warning(f"Heartbeat failed: {response.status_code}")
                return False
        except Exception as e:
            self.logger.error(f"Error sending heartbeat: {e}")
            return False
    
    def send_alert(self, alert_type, message, severity='warning'):
        """Send alert to ServerPulse"""
        if not self.agent_id or not self.auth_token:
            return False
        
        endpoint = f"{self.config['server']['endpoint'].rstrip('/')}/api/v1/agents/{self.agent_id}/alerts"
        
        alert_data = {
            'alerts': [{
                'type': alert_type,
                'message': message,
                'severity': severity,
                'timestamp': datetime.now(timezone.utc).isoformat()
            }]
        }
        
        headers = {
            'Authorization': f'Bearer {self.auth_token}',
            'Content-Type': 'application/json'
        }
        
        try:
            response = requests.post(endpoint, json=alert_data, headers=headers, timeout=30)
            return response.status_code == 200
        except Exception as e:
            self.logger.error(f"Error sending alert: {e}")
            return False
    
    def check_thresholds(self, metrics):
        """Check if metrics exceed configured thresholds"""
        alerts = self.config.get('alerts', {})
        
        # CPU threshold check
        cpu_threshold = alerts.get('cpu_threshold', 90)
        if metrics['cpu_usage'] > cpu_threshold:
            self.send_alert('high_cpu', 
                          f"CPU usage ({metrics['cpu_usage']}%) exceeded threshold ({cpu_threshold}%)",
                          'warning' if metrics['cpu_usage'] < cpu_threshold * 1.2 else 'error')
        
        # Memory threshold check
        memory_threshold = alerts.get('memory_threshold', 90)
        if metrics['memory_usage'] > memory_threshold:
            self.send_alert('high_memory',
                          f"Memory usage ({metrics['memory_usage']}%) exceeded threshold ({memory_threshold}%)",
                          'warning' if metrics['memory_usage'] < memory_threshold * 1.2 else 'error')
        
        # Disk threshold check
        disk_threshold = alerts.get('disk_threshold', 95)
        if metrics['disk_usage'] > disk_threshold:
            self.send_alert('high_disk',
                          f"Disk usage ({metrics['disk_usage']}%) exceeded threshold ({disk_threshold}%)",
                          'error')
        
        # Load average threshold check
        load_threshold = alerts.get('load_threshold', 5.0)
        if metrics['load_average'] > load_threshold:
            self.send_alert('high_load',
                          f"Load average ({metrics['load_average']}) exceeded threshold ({load_threshold})",
                          'warning')
    
    def run(self):
        """Main agent loop"""
        self.logger.info("Starting ServerPulse Agent...")
        
        # Try to use existing credentials from config
        existing_agent_id = self.config['server'].get('agent_id')
        existing_token = self.config['server'].get('auth_token')
        
        if (existing_agent_id and existing_token and 
            existing_agent_id != 'WILL_BE_GENERATED_AFTER_REGISTRATION'):
            self.agent_id = existing_agent_id
            self.auth_token = existing_token
            self.logger.info("Using existing agent credentials")
        else:
            # Register if not already registered
            self.logger.info("Registering agent...")
            if not self.register_agent():
                self.logger.error("Failed to register agent, exiting")
                return
        
        collection_interval = self.config.get('collection', {}).get('interval', 30)
        heartbeat_interval = 60  # Send heartbeat every minute
        last_heartbeat = 0
        
        self.logger.info(f"Agent started. Collection interval: {collection_interval}s, Heartbeat: {heartbeat_interval}s")
        
        while self.running:
            try:
                current_time = time.time()
                
                # Collect and send metrics
                metrics = self.collect_metrics()
                services = self.collect_services()
                
                if metrics:
                    # Check thresholds and send alerts if needed
                    self.check_thresholds(metrics)
                    
                    # Send metrics
                    if self.send_metrics(metrics, services):
                        self.logger.debug(f"Metrics sent: CPU {metrics['cpu_usage']}%, RAM {metrics['memory_usage']}%, Disk {metrics['disk_usage']}%")
                
                # Send heartbeat if needed
                if current_time - last_heartbeat >= heartbeat_interval:
                    if self.send_heartbeat():
                        self.logger.debug("Heartbeat sent")
                    last_heartbeat = current_time
                
                # Wait for next collection
                time.sleep(collection_interval)
                
            except KeyboardInterrupt:
                self.logger.info("Received interrupt signal, shutting down...")
                break
            except Exception as e:
                self.logger.error(f"Unexpected error in main loop: {e}")
                time.sleep(10)  # Wait before retrying
        
        self.logger.info("ServerPulse Agent stopped")

    def get_serverpulse_endpoint(self):
        """Return the default ServerPulse endpoint for validation purposes."""
        return "http://serverpulse.test:8080"

def main():
    if len(sys.argv) != 2:
        print("Usage: python3 serverpulse_agent.py <config_file>")
        sys.exit(1)
    
    config_file = sys.argv[1]
    if not os.path.exists(config_file):
        print(f"Config file not found: {config_file}")
        sys.exit(1)
    
    agent = ServerPulseAgent(config_file)
    agent.run()

if __name__ == '__main__':
    main()
