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
    
    # Check if sequences table has proper IDs
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && mysql joalacom_joala -e "SELECT id, name FROM email_sequences WHERE id IN (5,6,67,68);" 2>/dev/null')
    output = stdout.read().decode('utf-8', errors='replace')
    print("Sequences:")
    print(output)
    
    # Try to update with sequence 67 - first check if it exists
    stdin, stdout, stderr = ssh.exec_command('cd /home/joalacom/www && php artisan db:seed --force --class=Illuminate\\\\Database\\\\Seeders\\\\EmailSequence 2>&1 | head -5')
    output2 = stdout.read().decode('utf-8')
    print("\nSeed result:")
    print(output2)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")