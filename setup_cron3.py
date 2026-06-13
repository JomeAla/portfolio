import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    client = transport.open_session()
    
    # Use echo and pipe to crontab
    cmd = 'echo "MAILTO=\\"\\"\nSHELL=\\"/bin/bash\\"\n* * * * * cd /home/joalacom/www && php artisan schedule:run >> /dev/null 2>&1\n0 * * * * curl -s joala.com.ng/process-cart-abandonment.php" | crontab -'
    
    client.exec_command(cmd)
    result = client.makefile().read().decode('utf-8', errors='ignore')
    error = client.makefile_stderr().read().decode('utf-8', errors='ignore')
    
    print("Result:", result)
    print("Error:", error)
    
    # Verify
    client.exec_command('crontab -l')
    new_cron = client.makefile().read().decode('utf-8', errors='ignore')
    print("\nCurrent crontab:")
    print(new_cron)
    
    client.close()
    transport.close()
    print("\nDone!")
except Exception as e:
    print(f"Error: {e}")