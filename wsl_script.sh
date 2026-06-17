#!/bin/bash
sshpass -p 'bUrHpY4GzOIi0M' ssh -o StrictHostKeyChecking=no -o PreferredAuthentications=password root@joala.com.ng "cd /home/joala/public_html && git pull origin master" 2>&1
