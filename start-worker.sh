#!/bin/bash
# Queue Worker for joala-portfolio
# Run this via supervisor or background process

cd /home/joalacom/public_html

# Start the queue worker to process emails
php artisan queue:work --sleep=3 --tries=3 --max-time=3600 >> /home/joalacom/public_html/storage/logs/queue.log 2>&1