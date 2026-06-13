import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    
    # Try using tee to a temp file then crontab
    cmd = "echo '* * * * * cd /home/joalacom/www && php artisan schedule:run >> /dev/null 2>&1' > /tmp/mycrontab && crontab /tmp/mycrontab"
    
    client = transport.open_session()
    client.exec_command(cmd)
    
    # Wait a bit
    import time
    time.sleep(0.5)
    
    # Check result
    client2 = transport.open_session()
    client2.exec_command('crontab -l')
    output = client2.makefile().read().decode('utf-8', errors='ignore')
    print("Crontab:")
    print(output)
    
    client.close()
    client2.close()
    transport.close()
    print("Done!")
except Exception as e:
    print(f"Error: {e}")