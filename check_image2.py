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
    
    # Test if image URL is accessible
    stdin, stdout, stderr = ssh.exec_command('curl -sI https://joala.com.ng/uploads/products/wordpress-starter-kit-cover.jpg | head -5')
    output = stdout.read().decode('utf-8')
    print("Image URL test:")
    print(output)
    
    # Check Laravel log for recent errors
    stdin, stdout, stderr = ssh.exec_command('tail -20 /home/joalacom/www/storage/logs/laravel.log')
    output2 = stdout.read().decode('utf-8')
    print("\nRecent Laravel log:")
    print(output2[-2000:])
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")