import paramiko

host = 'joala.com.ng'
username = 'joalacom'
password = '4fu359TgAMi-O+'

try:
    ssh = paramiko.SSHClient()
    ssh.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    ssh.connect(host, 22, username, password)
    
    print("=== Check storage symlink ===")
    stdin, stdout, stderr = ssh.exec_command('ls -la /home/joalacom/public_html/public/storage 2>/dev/null || echo "No symlink"')
    print(stdout.read().decode())
    
    print("\n=== Try direct path in route ===")
    # Test if path exists using direct path
    stdin, stdout, stderr = ssh.exec_command('test -f /home/joalacom/public_html/storage/app/public/uploads/products/files/wordpress-starter-kit-premium.zip && echo "File exists" || echo "File not found"')
    print(stdout.read().decode())
    
    ssh.close()
except Exception as e:
    print(f"Error: {e}")