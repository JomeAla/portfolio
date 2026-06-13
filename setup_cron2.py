import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    client = transport.open_session()
    
    # Create a new crontab file with the schedule
    cron_content = '''MAILTO=""
SHELL="/bin/bash"
* * * * * cd /home/joalacom/www && php artisan schedule:run >> /dev/null 2>&1
0 * * * * curl joala.com.ng/process-cart-abandonment.php
'''
    
    # Write to a temp file and load it
    stdin = client.makefile_stdin()
    stdin.write(cron_content)
    stdin.flush()
    stdin.channel.shutdown_write()
    
    result = client.makefile().read().decode('utf-8', errors='ignore')
    print(result)
    
    # Verify
    client.exec_command('crontab -l')
    new_cron = client.makefile().read().decode('utf-8', errors='ignore')
    print("\nCurrent crontab:")
    print(new_cron)
    
    client.close()
    transport.close()
except Exception as e:
    print(f"Error: {e}")