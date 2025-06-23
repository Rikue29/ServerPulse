# ServerPulse UI Troubleshooting Guide

## Common UI Issues and Solutions

### 1. Sidebar Toggle Button Not Working

**Symptoms:**
- Sidebar minimize/maximize button doesn't respond
- UI looks different than expected
- Navigation elements don't work properly

**Solutions:**

#### Option A: Clear Browser Cache
1. Press `Ctrl+Shift+R` (Windows/Linux) or `Cmd+Shift+R` (Mac) to hard refresh
2. Or clear browser cache completely:
   - Chrome: Settings → Privacy → Clear browsing data
   - Firefox: Options → Privacy → Clear Data
   - Safari: Preferences → Privacy → Manage Website Data

#### Option B: Check Console for Errors
1. Press `F12` to open Developer Tools
2. Go to Console tab
3. Look for any red error messages
4. If you see "Alpine.js not loaded" warnings, the fallback JavaScript should handle it

#### Option C: Rebuild Assets (For Developers)
If you're running the project locally:
```bash
npm install
npm run build
```

### 2. Assets Not Loading

**Symptoms:**
- Page looks unstyled (no CSS)
- JavaScript functionality missing
- Font Awesome icons not showing

**Solutions:**

#### Check Network Tab
1. Press `F12` → Network tab
2. Refresh the page
3. Look for failed requests (red entries)
4. Check if CSS and JS files are loading

#### Verify File Permissions
Ensure the `public/build/assets/` directory exists and is readable.

### 3. Different OS Compatibility

**The application should work on:**
- ✅ Windows 10/11
- ✅ macOS (all versions)
- ✅ Linux (Ubuntu, CentOS, etc.)
- ✅ Any modern browser (Chrome, Firefox, Safari, Edge)

**Browser Requirements:**
- Modern browser with ES6+ support
- JavaScript enabled
- CSS Grid and Flexbox support

### 4. Docker Environment Issues

If running in Docker:
```bash
# Rebuild the Docker containers
docker-compose down
docker-compose build --no-cache
docker-compose up -d

# Rebuild assets inside container
docker-compose exec php npm run build
```

**Important: Always run composer install after container rebuilds**
```bash
# After rebuilding containers, always run:
docker-compose exec php composer install

# If you get dependency errors, try:
docker-compose exec php composer install --no-dev
docker-compose exec php php artisan cache:clear
docker-compose exec php php artisan config:clear
docker-compose exec php php artisan view:clear
```

**Common Docker Issues:**
- **Missing dependencies**: Run `composer install` after container rebuilds
- **Volume mounting problems**: Check if vendor directory is properly mounted
- **Permission issues**: Ensure proper file permissions in Docker volumes
- **Cache conflicts**: Clear Laravel cache after dependency changes

### 4.1 Laragon Environment Issues

If running in Laragon:
```bash
# Navigate to your project directory
cd C:\laragon\www\ServerPulse

# Install/Update Node.js dependencies
npm install

# Rebuild assets
npm run build

# Clear Laravel cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Restart Laragon services
# Right-click Laragon tray icon → Apache → Restart
# Right-click Laragon tray icon → MySQL → Restart
```

**Laragon-Specific Tips:**
- Ensure Laragon is running as Administrator
- Check if Apache and MySQL services are started in Laragon
- Verify the project is in the correct directory: `C:\laragon\www\ServerPulse`
- Make sure Node.js is installed and accessible from command line

### 5. Production vs Development

**Development Environment:**
- Uses Vite for hot reloading
- Assets served directly from source
- **Laragon**: Local development with Apache/MySQL
- **Docker**: Containerized development environment

**Production Environment:**
- Uses compiled assets from `public/build/`
- Automatic asset filename detection

### 5.1 Laragon Development Setup

**Prerequisites:**
1. Install Laragon (Full version with Node.js)
2. Ensure Node.js is included in Laragon installation
3. Place project in `C:\laragon\www\ServerPulse`

**Initial Setup:**
```bash
# Open Laragon Terminal (Right-click Laragon → Terminal)
cd C:\laragon\www\ServerPulse

# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install

# Build assets
npm run build

# Run database migrations
php artisan migrate

# Start development server (optional)
php artisan serve
```

**Common Laragon Issues:**
- **Port conflicts**: Laragon uses ports 80/443 by default, change if needed
- **Node.js not found**: Ensure Node.js is installed and in PATH
- **Permission issues**: Run Laragon as Administrator
- **Database connection**: Check MySQL credentials in `.env` file

### 6. Alpine.js Fallback

The application includes a fallback JavaScript system that activates if Alpine.js fails to load. This ensures the sidebar toggle and other interactive elements work even if there are CDN issues.

### 7. Font Awesome Icons

If icons are not showing:
1. Check if `https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css` is accessible
2. Try a different CDN or download Font Awesome locally

### 8. Performance Issues

**Slow Loading:**
- Check internet connection
- Clear browser cache
- Disable browser extensions temporarily

**High CPU Usage:**
- Close other browser tabs
- Check for infinite loops in console
- Restart the application

## Getting Help

If issues persist:
1. Check the browser console for specific error messages
2. Note your browser version and OS
3. Try a different browser
4. Contact the development team with specific error details

**For Laragon Users:**
- Include Laragon version in your report
- Mention if you're using Laragon Full or Lite
- Note any error messages from Laragon's log files
- Check Laragon's Apache and MySQL error logs

## Environment Variables

Ensure these are set correctly:
- `APP_ENV=production` (for production)
- `APP_DEBUG=false` (for production)
- `ASSET_URL` (if using CDN for assets)

**Laragon Environment:**
- `APP_ENV=local` (for development)
- `APP_DEBUG=true` (for development)
- `DB_HOST=localhost` (Laragon MySQL)
- `DB_PORT=3306` (default MySQL port)
- `DB_DATABASE=serverpulse` (your database name)
- `DB_USERNAME=root` (Laragon default)
- `DB_PASSWORD=` (Laragon default is empty) 