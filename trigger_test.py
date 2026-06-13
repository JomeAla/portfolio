import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    transport = paramiko.Transport((host, 22))
    transport.connect(username=username, password=password)
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, username=username, password=password)
    
    # Check recent leads
    print("Recent leads:")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan db:query "SELECT id, email, sequence_id, created_at FROM leads ORDER BY created_at DESC LIMIT 5"')
    output = stdout.read().decode('utf-8', errors='replace')
    print(output)
    
    # Process email queue manually
    print("\nProcessing email queue...")
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan queue:work --once 2>&1 | head -20')
    output2 = stdout.read().decode('utf-8', errors='replace')
    print(output2)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")