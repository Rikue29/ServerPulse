#!/bin/bash
# This script helps set up a cron job for Laravel's scheduler on Linux systems

# Colors for better readability
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${YELLOW}Setting up Laravel Scheduler for ServerPulse${NC}"
echo "======================================"

# Get the current directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" &> /dev/null && pwd )"

# Create the crontab entry
CRON_ENTRY="* * * * * cd $SCRIPT_DIR && php artisan schedule:run >> /dev/null 2>&1"

# Check if the cron job already exists
EXISTING_CRON=$(crontab -l 2>/dev/null | grep -F "$SCRIPT_DIR && php artisan schedule:run")

if [ -n "$EXISTING_CRON" ]; then
    echo -e "${GREEN}Laravel scheduler cron job already exists!${NC}"
else
    # Add the new cron job
    (crontab -l 2>/dev/null; echo "$CRON_ENTRY") | crontab -

    # Verify the cron job was added
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}Laravel scheduler cron job has been added successfully!${NC}"
        echo "The scheduler will run every minute, checking for tasks to execute."
    else
        echo -e "${YELLOW}Failed to add cron job. Please add it manually:${NC}"
        echo "$CRON_ENTRY"
    fi
fi

echo -e "\n${YELLOW}Current crontab:${NC}"
crontab -l

echo -e "\n${GREEN}Setup complete!${NC}"
echo "Your ServerPulse monitoring will now update in real-time."
