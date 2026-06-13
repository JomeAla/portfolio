#!/bin/bash
# Laravel Scheduler for joala-portfolio
# Run this via cron: * * * * * /home/joalacom/public_html/start-scheduler.sh >> /dev/null 2>&1

cd /home/joalacom/public_html

# Run the scheduler
php artisan schedule:run