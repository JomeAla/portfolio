import paramiko
import time

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    
    # First, add the Laravel scheduler cron
    client = transport.open_session()
    client.exec_command('crontab -r 2>/dev/null; echo "* * * * * cd /home/joalacom/www && php artisan schedule:run >> /dev/null 2>&1" | crontab -')
    time.sleep(1)
    
    client.exec_command('crontab -l')
    output = client.makefile().read().decode('utf-8', errors='ignore')
    print("Current crontab:")
    print(output)
    
    client.close()
    transport.close()
    print("Done!")
except Exception as e:
    print(f"Error: {e}")