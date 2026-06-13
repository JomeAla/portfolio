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
    print("Testing download URLs:")
    
    urls = [
        '/downloads/free-wordpress-starter-kit',
        '/downloads/wordpress-starter-kit',
        '/downloads/wordpress-starter-kit.zip',
    ]
    
    for url in urls:
        stdin, stdout, stderr = ssh.exec_command(f'curl -sI https://joala.com.ng{url} | head -3')
        output = stdout.read().decode('utf-8')
        print(f"{url}: {output.split(chr(10))[0]}")
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")