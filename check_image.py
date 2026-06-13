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
    
    # Check if image exists in public folder
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/www/public/uploads/products/wordpress-starter-kit-cover.jpg')
    output = stdout.read().decode('utf-8')
    print("Image check:")
    print(output)
    
    # Also check if there's an image in storage
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/www/storage/app/public/uploads/products/')
    output2 = stdout.read().decode('utf-8')
    print("\nStorage products folder:")
    print(output2)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")