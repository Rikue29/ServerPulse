# ServerPulse
**Know Before It Breaks** - Real-time Server Monitoring & Alert System

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel 12.x">
  <img src="https://img.shields.io/badge/Python-3.x-3776AB?style=flat&logo=python&logoColor=white" alt="Python 3.x">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=flat&logo=tailwind-css&logoColor=white" alt="TailwindCSS">
  <img src="https://img.shields.io/badge/Chart.js-4.x-FF6384?style=flat&logo=chart.js&logoColor=white" alt="Chart.js">
  <img src="https://img.shields.io/badge/Livewire-3.x-FB70A9?style=flat&logo=livewire&logoColor=white" alt="Livewire">
</p>

ServerPulse is a comprehensive real-time server monitoring solution that helps you proactively monitor your infrastructure before issues become critical. Built with Laravel and featuring both web dashboard and standalone monitoring agents, ServerPulse provides instant alerts, detailed analytics, and beautiful visualizations of your server performance.

## 🚀 Key Features

### 📊 Real-time Monitoring
- **Live Dashboard**: Real-time updates using WebSocket connections
- **System Metrics**: CPU, Memory, Disk usage, Network I/O
- **Service Monitoring**: Track critical services (nginx, apache, mysql, etc.)
- **Custom Thresholds**: Configurable alert thresholds for all metrics

### 🛡️ Intelligent Alerting
- **Proactive Alerts**: Get notified before problems become critical
- **Multi-channel Notifications**: Email, Slack, Discord support
- **Alert Escalation**: Automatic escalation for unresolved issues
- **Smart Filtering**: Reduce noise with intelligent alert grouping

### 📈 Analytics & Reporting
- **Historical Data**: Track performance trends over time
- **Performance Charts**: Beautiful visualizations with Chart.js
- **Downtime Tracking**: Monitor uptime/downtime statistics
- **PDF Reports**: Generate detailed performance reports

### 🔧 Multi-Platform Agent Support
- **Python Agent**: Lightweight monitoring agent for Linux/Unix servers
- **Windows Integration**: Native Windows batch scripts for monitoring
- **Docker Support**: Containerized deployment options
- **Auto-Registration**: Agents automatically register with the main server

### 🎛️ Management Features
- **User Management**: Role-based access control
- **Server Grouping**: Organize servers by environment or function
- **Bulk Operations**: Manage multiple servers simultaneously
- **Configuration Management**: Centralized agent configuration

## 🏗️ Architecture

ServerPulse consists of two main components:

1. **Web Dashboard** (Laravel-based)
   - Real-time monitoring interface
   - Alert management system
   - Historical data analysis
   - User management

2. **Monitoring Agents** (Python/Shell scripts)
   - Lightweight system metric collection
   - Service status monitoring
   - Automatic registration and authentication
   - Configurable collection intervals

## 🛠️ Technology Stack

### Backend
- **Laravel 12.x** - Web framework
- **PHP 8.2+** - Programming language
- **MySQL/PostgreSQL** - Database
- **Pusher/WebSockets** - Real-time communication
- **Queue System** - Background job processing

### Frontend
- **Livewire 3.x** - Dynamic components
- **TailwindCSS** - Styling framework
- **Chart.js** - Data visualization
- **Alpine.js** - JavaScript reactivity
- **Inertia.js** - SPA-like experience

### Monitoring Agents
- **Python 3.x** - Agent development
- **psutil** - System metrics collection
- **PyYAML** - Configuration management
- **requests** - HTTP communication

## 📋 Requirements

### Web Dashboard
- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ & npm
- MySQL 8.0+ or PostgreSQL 13+
- Web server (Apache/Nginx)

### Monitoring Agents
- Python 3.6+
- pip package manager
- systemd (for Linux service management)

## 🚀 Quick Start

### 1. Clone the Repository
```bash
git clone https://github.com/Rikue29/ServerPulse.git
cd serverpulse
```

### 2. Install Dependencies
```bash
# PHP dependencies
composer install

# Node.js dependencies
npm install
```

### 3. Environment Setup
```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Configure your database and other settings in .env
```

### 4. Database Setup
```bash
# Run migrations
php artisan migrate

# (Optional) Seed with sample data
php artisan db:seed
```

### 5. Build Assets
```bash
# Build frontend assets
npm run build

# Or for development
npm run dev
```

### 6. Start the Application
```bash
# Start the web server
php artisan serve

# In another terminal, start the queue worker
php artisan queue:work

# For real-time updates, start the monitoring
php artisan monitor:server
```

## 🔧 Agent Installation

### Linux/Unix Systems
```bash
# Copy agent files
cp agent/serverpulse_agent.py /opt/serverpulse/
cp agent/config.yml.example /opt/serverpulse/config.yml

# Install Python dependencies
pip3 install -r agent/requirements.txt

# Configure the agent
nano /opt/serverpulse/config.yml

# Install as system service
sudo cp agent/serverpulse-agent.service /etc/systemd/system/
sudo systemctl enable serverpulse-agent
sudo systemctl start serverpulse-agent
```

### Windows Systems
```batch
# Run the installation script
install-your-agent.bat

# Or set up auto-start monitoring
setup-autostart.bat
```

For detailed installation instructions, see:
- [Linux Agent Setup](SETUP-LINUX.md)
- [Windows Setup](SETUP-WINDOWS.md)
- [macOS Setup](SETUP-MACOS.md)

## 📖 Documentation

- [**Agent Installation Guide**](AGENT_INSTALL_GUIDE.md) - Complete agent setup instructions
- [**Monitoring Setup**](MONITORING-SETUP.md) - Configure real-time monitoring
- [**Integration Guide**](AGENT_INTEGRATION.md) - API integration documentation
- [**Troubleshooting**](TROUBLESHOOTING.md) - Common issues and solutions

## 🎯 Use Cases

### Infrastructure Teams
- Monitor production servers 24/7
- Get instant alerts on performance issues
- Track resource utilization trends
- Generate compliance reports

### DevOps Engineers
- Integrate with CI/CD pipelines
- Monitor deployment environments
- Automate incident response
- Track application performance

### System Administrators
- Centralized server management
- Proactive maintenance scheduling
- Historical performance analysis
- Capacity planning support

## 🔌 API Integration

ServerPulse provides a RESTful API for integration with external systems:

```bash
# Register a new agent
POST /api/v1/agents/register

# Send metrics data
POST /api/v1/agents/{id}/metrics

# Send heartbeat
POST /api/v1/agents/{id}/heartbeat

# Send alerts
POST /api/v1/agents/{id}/alerts
```

For complete API documentation, see [AGENT_INTEGRATION.md](AGENT_INTEGRATION.md).

## 🤝 Contributing

We welcome contributions! Please see our [Contributing Guidelines](CONTRIBUTING.md) for details.

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## 📄 License

ServerPulse is open-sourced software licensed under the [MIT license](LICENSE).

## 🆘 Support

- **Documentation**: Check our comprehensive docs
- **Issues**: Report bugs on GitHub Issues
- **Discussions**: Join our community discussions
- **Email**: support@serverpulse.com

## 🙏 Acknowledgments

Built with ❤️ using:
- [Laravel](https://laravel.com) - The PHP Framework
- [Livewire](https://livewire.laravel.com) - Full-stack framework for Laravel
- [TailwindCSS](https://tailwindcss.com) - Utility-first CSS framework
- [Chart.js](https://chartjs.org) - Simple yet flexible charting
- [psutil](https://psutil.readthedocs.io) - Cross-platform system monitoring

---

**ServerPulse** - Know Before It Breaks 🚀
