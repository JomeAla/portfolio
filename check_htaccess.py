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
    
    # Check if uploads is accessible at different paths
    print("Testing different paths:")
    
    paths = [
        '/uploads/products/wordpress-starter-kit-cover.jpg',
        '/public/uploads/products/wordpress-starter-kit-cover.jpg', 
        '/storage/app/public/uploads/products/wordpress-starter-kit-cover.jpg',
    ]
    
    for path in paths:
        stdin, stdout, stderr = ssh.exec_command(f'curl -sI https://joala.com.ng{path} | head -2')
        output = stdout.read().decode('utf-8')
        print(f"{path}: {output.strip()}")
    
    # Check where other images are
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/www/public_html/uploads/products/ | head -10')
    print("\nExisting uploads:")
    print(stdout.read().decode())
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")