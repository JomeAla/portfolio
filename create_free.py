import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Create free version folder on server ===")
    stdin, stdout, stderr = ssh.exec_command('mkdir -p /home/joalacom/public_html/storage/app/public/uploads/products/files/wordpress-starter-kit-free')
    print("Created folder")
    
    print("\n=== Check current files in storage ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/storage/app/public/uploads/products/files/')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")