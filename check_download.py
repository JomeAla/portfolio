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
    
    # Test the download URL
    print("Testing download URL:")
    stdin, stdout, stderr = ssh.exec_command('curl -sI https://joala.com.ng/downloads/free-wordpress-starter-kit | head -10')
    output = stdout.read().decode('utf-8')
    print(output)
    
    # Check if lead magnet file exists
    print("\nChecking lead magnet files:")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/www/storage/app/public/lead-magnets/')
    output2 = stdout.read().decode('utf-8')
    print(output2)
    
    # Check routes for downloads
    print("\nChecking downloads route:")
    stdin, stdout, stderr = ssh.exec_command('grep -n "downloads" /home/joalacom/www/routes/web.php | head -20')
    output3 = stdout.read().decode('utf-8')
    print(output3)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")