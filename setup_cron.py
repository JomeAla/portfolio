import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    client = transport.open_session()
    
    # Check existing crontab
    client.exec_command('crontab -l')
    existing = client.makefile().read().decode('utf-8', errors='ignore')
    print("Current crontab:")
    print(existing if existing.strip() else "(empty)")
    
    # Add the cron job
    cron_entry = "* * * * * cd /home/joalacom/www && php artisan schedule:run >> /dev/null 2>&1\n"
    
    # Append to crontab
    stdin = client.makefile_stdin()
    stdin.write(cron_entry)
    stdin.flush()
    stdin.channel.shutdown_write()
    
    # Verify
    client.exec_command('crontab -l')
    new_cron = client.makefile().read().decode('utf-8', errors='ignore')
    print("\nNew crontab:")
    print(new_cron)
    
    client.close()
    transport.close()
    print("\nCron job added successfully!")
except Exception as e:
    print(f"Error: {e}")