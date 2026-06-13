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
    
    # Check lead magnet folder contents
    print("Lead magnet folder:")
    stdin, stdout, stderr = ssh.exec_command('ls -laR /home/joalacom/www/storage/app/public/lead-magnets/wordpress-starter-kit/')
    output = stdout.read().decode('utf-8')
    print(output)
    
    # Check downloads route mapping
    print("\nDownload route mapping:")
    stdin, stdout, stderr = ssh.exec_command('grep -A 30 "Route::get.*/downloads" /home/joalacom/www/routes/web.php | head -40')
    output2 = stdout.read().decode('utf-8')
    print(output2)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")