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
    
    # Test if image URL is accessible now
    stdin, stdout, stderr = ssh.exec_command('curl -sI https://joala.com.ng/uploads/products/wordpress-starter-kit-cover.jpg | head -5')
    output = stdout.read().decode('utf-8')
    print("Image URL test:")
    print(output)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")