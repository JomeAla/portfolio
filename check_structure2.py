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
    
    # Check directory structure
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/')
    output = stdout.read().decode('utf-8')
    print("Home directory:")
    print(output)
    
    # Check what's in public_html
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/')
    output2 = stdout.read().decode('utf-8')
    print("\nPublic_html:")
    print(output2)
    
    ssh.close()
    transport.close()
    
except Exception as e:
    print(f"Error: {e}")